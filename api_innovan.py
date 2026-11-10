#!/usr/bin/env python3
"""
API Flask — INNOVAN-CI v8
Prédiction de coupures électriques par poste/quartier (Côte d'Ivoire).

Endpoints :
  GET  /health       → statut de l'API et des modèles
  POST /predict-3h   → prédiction fenêtre glissante [now, now+3h] via data_poste.csv
  POST /predict-csv  → prédictions sur dataset CSV uploadé (legacy)
"""

import os
import warnings
warnings.filterwarnings('ignore')

import numpy as np
import pandas as pd
import joblib
from datetime import datetime, timedelta
from flask import Flask, jsonify, request
from flask_cors import CORS

# ── Chemins (surchargeables via variables d'environnement) ────────
MODELS_DIR = os.environ.get('INNOVAN_MODELS_DIR', r'C:\Hackathon\tutore_2\innovan\models')
DATA_DIR   = os.environ.get('INNOVAN_DATA_DIR',   r'C:\Hackathon\tutore_2\innovan\Data')

app = Flask(__name__)
CORS(app)

# ── Chargement des modèles au démarrage ───────────────────────────
MODELS_OK   = False
model_bin   = None
model_cause = None
le_cluster  = le_saison = le_cause = le_zone = None
seuil_op    = 0.385
FEATURES    = []
poste_meta  = None

# ── Données CSV 3H ────────────────────────────────────────────────
poste_data_df     = None   # data_poste.csv chargé en mémoire
POSTE_DATA_OK     = False

# Colonnes utiles de data_poste.csv
_CSV_COLS = [
    'timestamp', 'poste_id', 'zone', 'quartier', 'cluster',
    'heure', 'mois', 'saison', 'saison_seche_bin', 'est_weekend',
    'heure_sin', 'heure_cos', 'mois_sin', 'mois_cos',
    'charge_mw', 'capacite_mw', 'taux_utilisation',
    'temperature_c', 'temp_max_24h', 'humidite_pct', 'pluviometrie_norm',
    'niveau_barrage_pct', 'age_cable_ans', 'maintenance_recente',
    'nb_incidents_7j', 'incidents_moy_3j', 'incidents_critiques',
    'cable_vieux_sans_maint', 'barrage_critique', 'stress_hydraulique',
]

def _load_models():
    global MODELS_OK, model_bin, model_cause
    global le_cluster, le_saison, le_cause, le_zone
    global seuil_op, FEATURES, poste_meta

    try:
        model_bin   = joblib.load(os.path.join(MODELS_DIR, 'model_coupure_v8.pkl'))
        model_cause = joblib.load(os.path.join(MODELS_DIR, 'model_cause_v8.pkl'))
        le_cluster  = joblib.load(os.path.join(MODELS_DIR, 'le_cluster_v8.pkl'))
        le_saison   = joblib.load(os.path.join(MODELS_DIR, 'le_saison_v8.pkl'))
        le_cause    = joblib.load(os.path.join(MODELS_DIR, 'le_cause_v8.pkl'))
        le_zone     = joblib.load(os.path.join(MODELS_DIR, 'le_zone_v8.pkl'))
        seuil_op    = float(joblib.load(os.path.join(MODELS_DIR, 'seuil_operateur_v8.pkl')))
        FEATURES    = joblib.load(os.path.join(MODELS_DIR, 'feature_names_v8.pkl'))
        poste_meta  = pd.read_csv(os.path.join(DATA_DIR, 'poste_metadata.csv'))
        MODELS_OK   = True
        print(f'[OK] Modeles charges — {len(poste_meta)} postes, seuil={seuil_op:.3f}')
    except Exception as exc:
        print(f'[ERREUR] Chargement modeles : {exc}')

def _load_poste_data():
    global poste_data_df, POSTE_DATA_OK
    csv_path = os.path.join(DATA_DIR, 'data_poste.csv')
    if not os.path.exists(csv_path):
        print(f'[WARN] data_poste.csv introuvable : {csv_path}')
        return
    try:
        poste_data_df = pd.read_csv(csv_path, usecols=_CSV_COLS, low_memory=False)
        poste_data_df['heure'] = poste_data_df['heure'].astype(int)
        poste_data_df['mois']  = poste_data_df['mois'].astype(int)
        # Index de lookup rapide : (heure, mois)
        poste_data_df = poste_data_df.set_index(['heure', 'mois'], drop=False)
        poste_data_df.sort_index(inplace=True)
        POSTE_DATA_OK = True
        print(f'[OK] data_poste.csv chargé — {len(poste_data_df):,} lignes indexées par (heure, mois)')
    except Exception as exc:
        print(f'[ERREUR] Chargement data_poste.csv : {exc}')

_load_models()
_load_poste_data()

# ── Libellés français des causes ──────────────────────────────────
CAUSE_LABELS = {
    'vieillissement_équipement': 'Vieillissement des équipements',
    'surcharge_réseau':          'Surcharge du réseau',
    'intempérie_pluie':          'Intempérie / Fortes pluies',
    'défaut_câble_ht':           'Défaut câble haute tension',
    'vandalisme_vol_câble':      'Vandalisme / Vol de câble',
    'aucune':                    'Réseau stable',
}

# ── Saisons CI ────────────────────────────────────────────────────
def _get_saison(month: int):
    if month in (12, 1, 2, 3):   return 'harmattan', 1
    elif month in (4, 5, 6):     return 'grande_saison_pluies', 0
    elif month in (7, 8):        return 'petite_saison_seche', 1
    else:                        return 'petite_saison_pluies', 0

def _safe_encode(encoder, value, fallback=0):
    try:
        return int(encoder.transform([value])[0])
    except Exception:
        return fallback

# ── Feature vector depuis data_poste.csv ─────────────────────────
def _build_row_from_data_poste(row, dt: datetime) -> list:
    """
    Construit le vecteur de 31 features depuis une ligne de data_poste.csv.
    Les features temporelles cycliques sont déjà précalculées dans le CSV.
    On recalcule seulement les interactions et les encodages.
    """
    # Temporel (précalculé dans le CSV)
    heure_sin = float(row['heure_sin'])
    heure_cos = float(row['heure_cos'])
    mois_sin  = float(row['mois_sin'])
    mois_cos  = float(row['mois_cos'])
    # est_weekend adapté au JOUR ACTUEL (pas celui du CSV historique)
    est_weekend = 1 if dt.weekday() >= 5 else 0

    # Saison du mois actuel
    saison_str, _ = _get_saison(dt.month)
    saison_seche_bin = int(float(row.get('saison_seche_bin', 0)))
    saison_enc  = _safe_encode(le_saison,  row.get('saison', saison_str), 0)
    cluster_enc = _safe_encode(le_cluster, str(row.get('cluster', 'A')),  0)
    zone_enc    = _safe_encode(le_zone,    str(row.get('zone', '')),       0)

    # Mesures réelles du poste
    pluvio   = float(row['pluviometrie_norm'])
    barrage  = float(row['niveau_barrage_pct'])
    stress   = float(row['stress_hydraulique'])
    bq_crit  = int(float(row['barrage_critique']))
    charge   = float(row['charge_mw'])
    capacite = float(row['capacite_mw'])
    taux     = float(row['taux_utilisation'])
    temp     = float(row['temperature_c'])
    hum      = float(row['humidite_pct'])
    temp_max = float(row['temp_max_24h'])
    age      = float(row['age_cable_ans'])
    maint    = int(float(row['maintenance_recente']))
    nb_inc   = float(row['nb_incidents_7j'])
    old_nm   = int(float(row['cable_vieux_sans_maint']))
    inc_cr   = int(float(row['incidents_critiques']))
    inc_moy  = float(row['incidents_moy_3j'])

    # Interactions (calculées)
    bq_x_temp   = stress * max(0.0, temp - 28.0)
    bq_x_sais   = stress * saison_seche_bin
    age_x_taux  = age * taux
    temp_x_taux = temp * taux
    age_x_inc   = age * nb_inc

    return [
        heure_sin, heure_cos, mois_sin, mois_cos, est_weekend,
        saison_enc, saison_seche_bin,
        pluvio, barrage, stress,
        bq_crit, bq_x_temp, bq_x_sais,
        charge, capacite, taux, taux ** 2,
        temp, hum, temp_max,
        age, maint, nb_inc,
        old_nm, inc_cr, inc_moy,
        age_x_taux, temp_x_taux, age_x_inc,
        cluster_enc, zone_enc,
    ]

# ── Feature vector depuis données brutes (fallback) ───────────────
def _build_row_from_csv(row: dict, dt: datetime = None) -> list:
    if dt is None:
        try:
            dt = pd.to_datetime(row.get('timestamp', datetime.now().isoformat()))
        except Exception:
            dt = datetime.now()

    h, m     = dt.hour, dt.month
    zone_str = str(row.get('zone', ''))
    cluster  = str(row.get('cluster', 'A'))

    heure_sin = np.sin(2 * np.pi * h / 24)
    heure_cos = np.cos(2 * np.pi * h / 24)
    mois_sin  = np.sin(2 * np.pi * m / 12)
    mois_cos  = np.cos(2 * np.pi * m / 12)
    est_weekend = 1 if dt.weekday() >= 5 else 0
    saison_str, saison_seche_bin = _get_saison(m)

    saison_enc  = _safe_encode(le_saison,  saison_str, 0)
    cluster_enc = _safe_encode(le_cluster, cluster,    0)
    zone_enc    = _safe_encode(le_zone,    zone_str,   0)

    charge   = float(row.get('charge_mw',          15.0))
    capacite = float(row.get('capacite_mw',         24.0))
    taux     = charge / capacite if capacite > 0 else 0.5
    temp     = float(row.get('temperature_c',       27.0))
    hum      = float(row.get('humidite_pct',        74.0))
    pluvio   = float(row.get('pluviometrie_norm',    0.3))
    barrage  = float(row.get('niveau_barrage_pct',  60.0))
    age      = float(row.get('age_cable_ans',        14.0))
    maint    = int(float(row.get('maintenance_recente', 0)))
    nb_inc   = float(row.get('nb_incidents_7j',      3.0))
    inc_moy  = float(row.get('incidents_moy_3j',     1.5))

    stress   = (100.0 - barrage) / 100.0
    bq_crit  = 1 if barrage < 30 else 0
    temp_max = float(row.get('temp_max_24h', temp + 2.0))
    old_nm   = 1 if age > 18 else 0
    inc_cr   = int(nb_inc > 5)

    bq_x_temp   = stress * max(0.0, temp - 28.0)
    bq_x_sais   = stress * saison_seche_bin
    age_x_taux  = age * taux
    temp_x_taux = temp * taux
    age_x_inc   = age * nb_inc

    return [
        heure_sin, heure_cos, mois_sin, mois_cos, est_weekend,
        saison_enc, saison_seche_bin,
        pluvio, barrage, stress,
        bq_crit, bq_x_temp, bq_x_sais,
        charge, capacite, taux, taux ** 2,
        temp, hum, temp_max,
        age, maint, nb_inc,
        old_nm, inc_cr, inc_moy,
        age_x_taux, temp_x_taux, age_x_inc,
        cluster_enc, zone_enc,
    ]


# ════════════════════════════════════════════════════════════════════
# ENDPOINTS
# ════════════════════════════════════════════════════════════════════

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status':            'ok' if MODELS_OK else 'degraded',
        'models_loaded':     MODELS_OK,
        'poste_data_loaded': POSTE_DATA_OK,
        'postes':            len(poste_meta) if poste_meta is not None else 0,
        'seuil_op':          seuil_op,
        'endpoints':         ['/health', '/predict-3h', '/predict-csv'],
    })


@app.route('/predict-3h', methods=['POST'])
def predict_3h():
    """
    Prédiction fenêtre glissante 3H.

    Le modèle se base sur data_poste.csv : il sélectionne les observations
    correspondant au SLOT ACTUEL (heure arrondie à 3H) et au MOIS actuel,
    représentant le comportement typique de chaque poste à ce moment.

    Retourne les prédictions pour la fenêtre [maintenant, maintenant+3H].
    Les slots 3H sont : 00:00, 03:00, 06:00, 09:00, 12:00, 15:00, 18:00, 21:00
    """
    if not MODELS_OK:
        return jsonify({'error': 'Modèles non disponibles. Vérifiez INNOVAN_MODELS_DIR.'}), 503

    body     = request.get_json(force=True, silent=True) or {}
    min_prob = int(body.get('min_probability', 40))

    now        = datetime.now()
    slot_hour  = (now.hour // 3) * 3        # 0, 3, 6, 9, 12, 15, 18, 21
    cur_month  = now.month
    win_start  = now.replace(hour=slot_hour, minute=0, second=0, microsecond=0)
    win_end    = win_start + timedelta(hours=3)

    predictions = []
    source      = 'fallback'

    # ── Source 1 : data_poste.csv (prioritaire) ────────────────────
    if POSTE_DATA_OK and poste_data_df is not None:
        try:
            # Lookup direct par (heure, mois)
            try:
                slot_df = poste_data_df.loc[(slot_hour, cur_month)].copy()
            except KeyError:
                slot_df = pd.DataFrame()

            if len(slot_df) > 0:
                feat_rows = [
                    _build_row_from_data_poste(row, now)
                    for _, row in slot_df.iterrows()
                ]
                X        = pd.DataFrame(feat_rows, columns=FEATURES)
                probas   = model_bin.predict_proba(X)[:, 1]
                prob_pct = (probas * 100).clip(0, 100).astype(int)

                mask = prob_pct >= min_prob
                coup_idx = np.where(mask)[0]

                causes = []
                if len(coup_idx) > 0:
                    causes_enc = model_cause.predict(X.iloc[coup_idx])
                    causes     = le_cause.inverse_transform(causes_enc)

                for ii, idx in enumerate(coup_idx):
                    r    = slot_df.iloc[idx]
                    prob = int(prob_pct[idx])
                    caus = str(causes[ii])
                    predictions.append({
                        'poste_id':       str(r['poste_id']),
                        'zone':           str(r['zone']),
                        'quartier':       str(r['quartier']),
                        'cluster':        str(r['cluster']),
                        'probability':    prob,
                        'cause_probable': caus,
                        'reason':         CAUSE_LABELS.get(caus, caus),
                        'start_at':       win_start.strftime('%Y-%m-%d %H:%M:%S'),
                        'end_at':         win_end.strftime('%Y-%m-%d %H:%M:%S'),
                        'charge_mw':      float(r['charge_mw']),
                        'taux_utilisation': float(r['taux_utilisation']),
                    })
                source = 'data_poste_csv'
                print(f'[3H] slot={slot_hour}h mois={cur_month} → {len(slot_df)} postes analysés, {len(predictions)} alertes')
        except Exception as exc:
            print(f'[WARN] Erreur analyse data_poste.csv : {exc}')

    # ── Source 2 : poste_donnees envoyés par Laravel (fallback) ───
    if not predictions:
        postes = body.get('postes', [])
        for i, poste in enumerate(postes):
            try:
                feat_vec = _build_row_from_csv(poste, now)
                X        = pd.DataFrame([feat_vec], columns=FEATURES)
                proba    = float(model_bin.predict_proba(X)[0, 1])
                prob_pct = int(proba * 100)

                if prob_pct >= min_prob:
                    cause     = 'aucune'
                    cause_enc = model_cause.predict(X)[0]
                    cause     = str(le_cause.inverse_transform([cause_enc])[0])
                    predictions.append({
                        'poste_id':       str(poste.get('poste_id', f'poste_{i}')),
                        'zone':           str(poste.get('zone', '')),
                        'quartier':       str(poste.get('quartier', '')),
                        'cluster':        str(poste.get('cluster', '')),
                        'probability':    prob_pct,
                        'cause_probable': cause,
                        'reason':         CAUSE_LABELS.get(cause, cause),
                        'start_at':       win_start.strftime('%Y-%m-%d %H:%M:%S'),
                        'end_at':         win_end.strftime('%Y-%m-%d %H:%M:%S'),
                        'charge_mw':      float(poste.get('charge_mw', 0)),
                        'taux_utilisation': round(
                            float(poste.get('charge_mw', 0)) /
                            max(float(poste.get('capacite_mw', 1)), 0.01), 2
                        ),
                    })
            except Exception as exc:
                print(f'[WARN] poste {i} : {exc}')
        if predictions:
            source = 'poste_donnees'

    # ── Dédoublonnage : meilleur poste par zone ────────────────────
    zone_best = {}
    for p in sorted(predictions, key=lambda x: -x['probability']):
        if p['zone'] not in zone_best:
            zone_best[p['zone']] = p

    top_alertes = sorted(zone_best.values(), key=lambda x: -x['probability'])

    return jsonify({
        'predictions':   top_alertes,
        'slot_hour':     slot_hour,
        'window_start':  win_start.strftime('%Y-%m-%d %H:%M:%S'),
        'window_end':    win_end.strftime('%Y-%m-%d %H:%M:%S'),
        'alertes':       len(top_alertes),
        'all_postes':    len(predictions),
        'generated_at':  now.isoformat(),
        'source':        source,
    })


@app.route('/predict-csv', methods=['POST'])
def predict_csv():
    """Prédiction sur un dataset CSV uploadé (conservé pour compatibilité)."""
    if not MODELS_OK:
        return jsonify({'error': 'Modèles non disponibles'}), 503

    body = request.get_json(force=True, silent=True) or {}
    rows = body.get('rows', [])
    if not rows:
        return jsonify({'error': 'Aucune ligne reçue.'}), 400

    now         = datetime.now()
    win_start   = now.replace(minute=0, second=0, microsecond=0)
    win_end     = win_start + timedelta(hours=3)
    predictions = []
    errors      = []

    for i, row in enumerate(rows):
        try:
            feat_vec = _build_row_from_csv(row, now)
            X        = pd.DataFrame([feat_vec], columns=FEATURES)
            proba    = float(model_bin.predict_proba(X)[0, 1])
            prob_pct = int(proba * 100)

            cause = 'aucune'
            if prob_pct >= 40:
                cause_enc = model_cause.predict(X)[0]
                cause     = str(le_cause.inverse_transform([cause_enc])[0])

            predictions.append({
                'poste_id':       str(row.get('poste_id', f'row_{i}')),
                'zone':           str(row.get('zone', '')),
                'quartier':       str(row.get('quartier', '')),
                'cluster':        str(row.get('cluster', '')),
                'probability':    prob_pct,
                'cause_probable': cause,
                'reason':         CAUSE_LABELS.get(cause, cause),
                'start_at':       win_start.strftime('%Y-%m-%d %H:%M:%S'),
                'end_at':         win_end.strftime('%Y-%m-%d %H:%M:%S'),
                'coupure_pred':   1 if prob_pct >= int(seuil_op * 100) else 0,
            })
        except Exception as exc:
            errors.append({'row': i, 'error': str(exc)})

    alertes = [p for p in predictions if p['probability'] >= 40]
    zone_best = {}
    for p in sorted(alertes, key=lambda x: -x['probability']):
        if p['zone'] not in zone_best:
            zone_best[p['zone']] = p

    top_alertes = sorted(zone_best.values(), key=lambda x: -x['probability'])

    return jsonify({
        'predictions':  top_alertes,
        'all_rows':     len(predictions),
        'alertes':      len(top_alertes),
        'errors':       errors,
        'window_start': win_start.strftime('%Y-%m-%d %H:%M:%S'),
        'window_end':   win_end.strftime('%Y-%m-%d %H:%M:%S'),
        'generated_at': now.isoformat(),
        'source':       'csv_dataset',
    })


if __name__ == '__main__':
    port = int(os.environ.get('INNOVAN_PORT', 5001))
    print(f'[START] API INNOVAN-CI v8 — http://0.0.0.0:{port}')
    app.run(host='0.0.0.0', port=port, debug=False)
