# INNOVAN-CI — Système de Prédiction des Coupures Électriques

> Projet Tutoré 2 · Hackathon CIE · Côte d'Ivoire  
> Plateforme intelligente de prévision et gestion des coupures électriques pour la CIE (Compagnie Ivoirienne d'Électricité)

---

## Présentation

INNOVAN-CI est une application web complète couplée à un modèle d'intelligence artificielle (Random Forest) permettant de :

- **Prédire** les coupures électriques par poste et par quartier jusqu'à 5 jours à l'avance
- **Identifier** la cause probable (surcharge, vieillissement, intempérie, défaut câble, vandalisme)
- **Alerter** les clients en temps réel avec notification vocale multilingue (Français, Dioula, Baoulé, Bété)
- **Piloter** les équipes terrain via un tableau de bord agent complet

---

## Architecture générale

```
┌─────────────────────────────────────────────────────────────┐
│                      NAVIGATEUR WEB                         │
│              (Dashboard Admin / Espace Client)              │
└─────────────────────┬───────────────────────────────────────┘
                      │ HTTP
┌─────────────────────▼───────────────────────────────────────┐
│              APPLICATION LARAVEL 12 (PHP 8.2)               │
│  ┌────────────┐  ┌───────────────┐  ┌────────────────────┐  │
│  │   Auth     │  │ AdminController│  │ ClientController   │  │
│  │  (login/   │  │  (dashboard,  │  │  (alertes,         │  │
│  │  register) │  │   alertes,    │  │   notification     │  │
│  └────────────┘  │   clients,    │  │   vocale)          │  │
│                  │   rapports…)  │  └────────────────────┘  │
│                  └───────┬───────┘                          │
│                          │ HTTP POST                        │
│  ┌───────────────────────▼───────────────────────────────┐  │
│  │            API FLASK INNOVAN-CI (port 5001)           │  │
│  │   POST /predict     → features synthétiques par zone  │  │
│  │   POST /predict-csv → prédiction sur dataset uploadé  │  │
│  │   GET  /health      → statut des modèles              │  │
│  │                                                       │  │
│  │   Modèles ML (scikit-learn 1.6.1)                     │  │
│  │   ├─ RandomForestClassifier (binaire) — 600 arbres   │  │
│  │   └─ RandomForestClassifier (causes) — 400 arbres    │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
│  Base de données : SQLite (database/basecie)                │
└─────────────────────────────────────────────────────────────┘
```

---

## Stack technique

| Composant | Technologie | Version |
|---|---|---|
| Backend web | Laravel | 12.x |
| Langage backend | PHP | 8.2+ |
| API ML | Flask + Flask-CORS | 3.x |
| Langage ML | Python | 3.13 |
| Modèle IA | scikit-learn RandomForest | 1.6.1 |
| Base de données | SQLite | — |
| Frontend | Blade + Bootstrap 5 + Chart.js | — |
| CSS | Custom (variables CSS, Inter font) | — |
| Icons | Font Awesome 6 | — |

---

## Modèle IA — INNOVAN-CI v8

### Dataset
- **1 944 720 observations** sur 3 ans (2022–2024)
- **296 postes sources** répartis sur **30 zones** et **231 quartiers** de Côte d'Ivoire
- Taux de coupure observé : **23.9%**

### Modèle Binaire (coupure / pas de coupure)
| Métrique | Valeur |
|---|---|
| ROC-AUC | 0.600 |
| Recall (seuil citoyen) | 99.9% |
| F1 (seuil opérateur) | 0.398 |
| OOB score | 0.719 |
| Arbres | 600 |

### Modèle Multiclasse (cause probable)
| Cause | Part |
|---|---|
| Vieillissement équipements | 28.3% |
| Surcharge réseau | 24.8% |
| Intempérie / Pluies | 21.6% |
| Défaut câble HT | 16.4% |
| Vandalisme / Vol câble | 8.8% |

### 31 features utilisées
- **Temporelles** : heure (sin/cos), mois (sin/cos), week-end, saison CI
- **Hydrauliques** : niveau barrage, pluviométrie, stress hydraulique
- **Réseau** : charge MW, capacité MW, taux d'utilisation
- **Météo** : température, humidité, temp max 24h
- **Infrastructure** : âge câble, maintenance, incidents 7j
- **Spatiales** : cluster (A/B/C), zone encodée
- **Interactions** : âge×taux, temp×taux, âge×incidents

---

## Structure du projet

```
tutore_2/
├── api_innovan.py          # API Flask INNOVAN-CI
├── start_api.bat           # Démarrage rapide de l'API (Windows)
├── check_models.py         # Script de vérification des modèles
├── exemple_dataset.csv     # Dataset de démonstration (20 postes)
├── requirements_api.txt    # Dépendances Python
│
├── innovan/
│   ├── models/             # Modèles ML sauvegardés (.pkl)
│   │   ├── model_coupure_v8.pkl
│   │   ├── model_cause_v8.pkl
│   │   ├── le_cluster_v8.pkl
│   │   ├── le_saison_v8.pkl
│   │   ├── le_cause_v8.pkl
│   │   ├── le_zone_v8.pkl
│   │   ├── seuil_operateur_v8.pkl
│   │   └── feature_names_v8.pkl
│   └── Data/
│       └── poste_metadata.csv   # 296 postes avec zone/quartier/cluster
│
└── tutore2/                # Application Laravel
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   │   ├── AdminController.php   # Dashboard, alertes, clients, agents, rapports
    │   │   │   ├── ClientController.php  # Espace client + notification vocale
    │   │   │   └── AuthController.php    # Authentification
    │   │   └── Middleware/
    │   │       ├── EnsureAgent.php       # Protection routes admin
    │   │       └── EnsureClient.php      # Protection routes client
    │   └── Models/
    │       ├── Alert.php                 # Modèle alerte (zone, quartier, cause…)
    │       └── User.php                  # Utilisateur (client / agent / admin)
    ├── database/
    │   ├── migrations/                   # Schéma BDD
    │   └── seeders/
    │       ├── DatabaseSeeder.php        # 12 comptes de test
    │       └── AlertSeeder.php           # Alertes de démo
    ├── resources/views/
    │   ├── admin/
    │   │   ├── dashboard.blade.php       # Tableau de bord principal
    │   │   ├── alerts.blade.php          # Liste des alertes
    │   │   ├── prediction.blade.php      # Résultats prédiction IA
    │   │   ├── zones.blade.php           # Zones & Communes
    │   │   ├── coupures.blade.php        # Coupures en cours
    │   │   ├── clients.blade.php         # Gestion clients
    │   │   ├── agents.blade.php          # Gestion agents
    │   │   └── rapports.blade.php        # Rapports & Statistiques
    │   ├── client/
    │   │   ├── dashboard.blade.php       # Espace client
    │   │   └── alerts_page.blade.php     # Alertes client
    │   └── auth/
    │       ├── login.blade.php
    │       └── register.blade.php
    └── routes/web.php                    # Toutes les routes
```

---

## Installation

### Prérequis
- PHP 8.2+
- Composer
- Node.js + npm
- Python 3.10+

### ⚠️ Fichiers non inclus dans le dépôt

Certains fichiers dépassent la limite de 100 Mo de GitHub et ne sont **pas versionnés** (voir `.gitignore`) :

| Fichier | Taille | Rôle |
|---|---|---|
| `innovan/models/model_coupure_v8.pkl` | ~553 Mo | Modèle binaire (coupure oui/non) — **requis par l'API** |
| `innovan/models/model_cause_v8.pkl` | ~489 Mo | Modèle multiclasse (cause) — **requis par l'API** |
| `innovan/Data/data_poste.csv` | ~330 Mo | Données brutes par poste |
| `innovan/Data/dataset2_observations*.csv` | 40–71 Mo | Dataset d'entraînement |

Récupérez-les auprès de l'équipe et copiez-les aux mêmes emplacements. Sans les deux modèles `.pkl`, l'application Laravel bascule automatiquement sur la **simulation**.

### 1. Cloner et installer Laravel

```powershell
cd C:\Hackathon\tutore_2\tutore2
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
```

### 2. Installer les dépendances Python

```powershell
cd C:\Hackathon\tutore_2
py -m pip install flask flask-cors numpy pandas scikit-learn joblib
```

### 3. Vérifier les modèles

```powershell
py check_models.py
```

Résultat attendu :
```
[OK] Modeles charges -- 296 postes, seuil=0.385
```

---

## Démarrage

### Terminal 1 — API Flask INNOVAN-CI

```powershell
# Option A : double-cliquer sur start_api.bat
# Option B : en ligne de commande
cd C:\Hackathon\tutore_2
set INNOVAN_MODELS_DIR=C:\Hackathon\tutore_2\innovan\models
set INNOVAN_DATA_DIR=C:\Hackathon\tutore_2\innovan\Data
py api_innovan.py
```

Vérification : `http://127.0.0.1:5001/health`

### Terminal 2 — Serveur Laravel

```powershell
cd C:\Hackathon\tutore_2\tutore2
php artisan serve
```

Application disponible sur : **http://127.0.0.1:8000**

---

## Comptes de test

### Interface Admin / Agent

| Email | Mot de passe | Rôle | Zone |
|---|---|---|---|
| `agent@cie.local` | `password` | Admin général | Toutes zones |
| `agent.abobo@cie.local` | `password` | Agent terrain | Abobo |
| `agent.yopougon@cie.local` | `password` | Agent terrain | Yopougon |
| `agent.cocody@cie.local` | `password` | Agent terrain | Cocody |
| `agent.marcory@cie.local` | `password` | Agent terrain | Marcory |
| `agent.koumassi@cie.local` | `password` | Agent terrain | Koumassi |

### Interface Client

| Email | Mot de passe | Zone | Langue |
|---|---|---|---|
| `client@demo.local` | `password` | Cocody | Français |
| `konan.jean@gmail.com` | `password` | Cocody | Français |
| `trabi.marie@gmail.com` | `password` | Abobo | Baoulé |
| `coulibaly.ibou@gmail.com` | `password` | Yopougon | Dioula |
| `gnangnan.sophie@gmail.com` | `password` | Marcory | Français |
| `toure.aminata@gmail.com` | `password` | Koumassi | Dioula |

---

## Fonctionnalités détaillées

### Espace Admin (`/admin`)

| Page | URL | Description |
|---|---|---|
| Tableau de bord | `/admin` | KPIs, graphiques 7 jours, causes, clusters |
| Alertes prédites | `/admin/alerts` | Tableau filtrable avec cause IA + quartier |
| Zones & Communes | `/admin/zones` | Classement zones par risque + nb clients |
| Coupures en cours | `/admin/coupures` | Actives maintenant + à venir 48h |
| Prédiction IA | `/admin/prediction` | Timeline 5 jours après prédiction |
| Clients | `/admin/clients` | Liste abonnés, zones, langues |
| Agents | `/admin/agents` | Équipe terrain, zones assignées |
| Rapports | `/admin/rapports` | Statistiques complètes imprimables |

### Flux de prédiction

```
1. Charger les données  →  Upload CSV (15 colonnes requises)
2. Lancer la prédiction →  Laravel → API Flask /predict-csv
                           ↓
                       Calcul des 31 features
                       RandomForest binaire → probabilité coupure
                       RandomForest multiclasse → cause probable
                           ↓
3. Résultats affichés   →  Zone, Quartier, Probabilité %, Cause, Horaires
4. Alertes créées       →  Clients notifiables via notification vocale
```

### Fallback automatique
Si l'API Flask est indisponible, la prédiction bascule automatiquement sur une **simulation** (données synthétiques) sans interruption de service.

### Format CSV pour l'upload

```csv
poste_id,zone,quartier,cluster,timestamp,charge_mw,capacite_mw,temperature_c,
humidite_pct,pluviometrie_norm,niveau_barrage_pct,age_cable_ans,
maintenance_recente,nb_incidents_7j,incidents_moy_3j
PST_0001,Yopougon,Sicogi,A,2026-06-08 14:00,17.5,25.0,27.3,74.0,0.25,62.0,13.5,0,3,1.5
```

---

### Espace Client (`/client`)

- **Vue d'ensemble** : alertes à venir dans sa zone, statut réseau
- **Notification vocale** : bannière audio avec lecture TTS ou fichier MP3
  - Langues : Français, Dioula, Baoulé, Bété
  - Niveaux : Urgent (≥80%), Probable (≥60%), Possible (<60%)
- **Consommation** : graphique 7 jours + historique mensuel
- **Facture** : détail + modes de paiement (Mobile Money, Carte, Virement)

---

## API Flask — Endpoints

### `GET /health`
Vérifie l'état de l'API et le chargement des modèles.

```json
{
  "status": "ok",
  "models_loaded": true,
  "postes": 296,
  "seuil_op": 0.385
}
```

### `POST /predict`
Prédiction avec features synthétiques (sans dataset).

```json
// Corps de la requête
{
  "horizon_days": 5,
  "min_probability": 40,
  "top_per_day": 4,
  "peak_hour": 14
}

// Réponse
{
  "predictions": [
    {
      "zone": "Abobo",
      "quartier": "Abobo Baoulé",
      "poste_id": "PST_0042",
      "cluster": "A",
      "probability": 82,
      "cause_probable": "surcharge_réseau",
      "reason": "Surcharge du réseau",
      "start_at": "2026-06-08 14:00:00",
      "end_at": "2026-06-08 19:00:00"
    }
  ],
  "total": 20
}
```

### `POST /predict-csv`
Prédiction sur un dataset CSV uploadé.

```json
// Corps de la requête
{
  "rows": [
    {
      "poste_id": "PST_0001",
      "zone": "Yopougon",
      "quartier": "Sicogi",
      "cluster": "A",
      "timestamp": "2026-06-08 14:00",
      "charge_mw": 17.5,
      "capacite_mw": 25.0,
      "temperature_c": 27.3,
      "humidite_pct": 74.0,
      "pluviometrie_norm": 0.25,
      "niveau_barrage_pct": 62.0,
      "age_cable_ans": 13.5,
      "maintenance_recente": 0,
      "nb_incidents_7j": 3,
      "incidents_moy_3j": 1.5
    }
  ]
}

// Réponse
{
  "predictions": [...],
  "all_rows": 20,
  "alertes": 14,
  "source": "csv_dataset"
}
```

---

## Schéma de la base de données

### Table `users`
| Colonne | Type | Description |
|---|---|---|
| id | integer | Clé primaire |
| name | string | Nom complet |
| email | string | Email unique |
| role | string | `admin`, `agent`, `client` |
| zone | string | Zone assignée (agents) ou zone de résidence (clients) |
| phone | string | Téléphone |
| meter_number | string | Numéro de compteur (clients) |
| language | string | Langue de notification (`fr`, `dioula`, `baoule`, `bete`) |

### Table `alerts`
| Colonne | Type | Description |
|---|---|---|
| id | integer | Clé primaire |
| zone | string | Commune concernée |
| quartier | string | Quartier précis |
| poste_id | string | Identifiant du poste source |
| cluster | string | Cluster du poste (A/B/C) |
| start_at | datetime | Début prévu de la coupure |
| end_at | datetime | Fin prévue de la coupure |
| reason | string | Libellé français de la cause |
| cause_probable | string | Cause brute du modèle IA |
| probability | tinyint | Probabilité de coupure (0-100) |
| status | string | `predicted`, `active`, `resolved` |

---

## Variables d'environnement

```env
# Application Laravel
APP_NAME=Laravel
APP_ENV=local
APP_URL=http://localhost
DB_CONNECTION=sqlite
DB_DATABASE=basecie

# API Flask INNOVAN-CI
INNOVAN_API_URL=http://127.0.0.1:5001
```

```env
# API Flask (variables système ou dans start_api.bat)
INNOVAN_MODELS_DIR=C:\Hackathon\tutore_2\innovan\models
INNOVAN_DATA_DIR=C:\Hackathon\tutore_2\innovan\Data
INNOVAN_PORT=5001
```

---

## Clusters géographiques CI

| Cluster | Zones | Caractéristiques |
|---|---|---|
| **A** (178 postes) | Abidjan (Yopougon, Cocody, Abobo…) | Urbain dense, forte capacité, câbles récents |
| **B** (83 postes) | Bouaké, Yamoussoukro, San-Pédro… | Villes secondaires, capacité moyenne |
| **C** (35 postes) | Korhogo, Man, Téngrela, Odienné… | Zones éloignées, infrastructure vieillissante |

---

## Équipe

Projet Tutoré 2 — Hackathon INNOVAN-CI  
Système de gestion intelligente des coupures électriques pour la CIE  
Côte d'Ivoire · 2026

---

*Plateforme de gestion intelligente des coupures v2.0 — Propulsé par INNOVAN-CI*
