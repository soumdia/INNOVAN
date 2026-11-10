<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>INNOVAN-CI — Rapport de Projet 2026</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }

  body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 10.5pt;
    color: #1a1a2e;
    line-height: 1.6;
  }

  /* ── PAGE DE COUVERTURE ── */
  .cover {
    width: 100%;
    height: 100%;
    min-height: 270mm;
    background: linear-gradient(160deg, #0a1628 0%, #1a3a1a 60%, #0f2010 100%);
    color: #fff;
    text-align: center;
    padding: 50pt 40pt 40pt;
    position: relative;
  }
  .cover-logo {
    width: 70pt;
    height: 70pt;
    background: #1a9e3f;
    border-radius: 18pt;
    margin: 0 auto 20pt;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32pt;
    font-weight: 900;
    color: #fff;
    line-height: 70pt;
    text-align: center;
  }
  .cover-badge {
    display: inline-block;
    background: rgba(26,158,63,0.25);
    border: 1pt solid rgba(26,158,63,0.5);
    border-radius: 20pt;
    padding: 5pt 16pt;
    font-size: 8pt;
    color: #4ade80;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 20pt;
  }
  .cover-title {
    font-size: 30pt;
    font-weight: 900;
    color: #fff;
    letter-spacing: -0.5pt;
    margin-bottom: 6pt;
  }
  .cover-title span { color: #4ade80; }
  .cover-subtitle {
    font-size: 13pt;
    color: rgba(255,255,255,0.65);
    margin-bottom: 30pt;
  }
  .cover-tagline {
    font-size: 12pt;
    color: rgba(255,255,255,0.5);
    font-style: italic;
    margin-bottom: 40pt;
  }
  .cover-stats {
    display: table;
    width: 100%;
    margin: 30pt auto;
  }
  .cover-stat {
    display: table-cell;
    text-align: center;
    padding: 16pt 10pt;
    border: 1pt solid rgba(255,255,255,0.1);
  }
  .cover-stat-val {
    font-size: 22pt;
    font-weight: 900;
    color: #4ade80;
  }
  .cover-stat-lbl {
    font-size: 7.5pt;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-top: 4pt;
  }
  .cover-footer {
    position: absolute;
    bottom: 24pt;
    left: 40pt;
    right: 40pt;
    display: table;
    width: calc(100% - 80pt);
    border-top: 1pt solid rgba(255,255,255,0.1);
    padding-top: 12pt;
  }
  .cover-footer-left  { display: table-cell; text-align: left;  font-size: 8pt; color: rgba(255,255,255,0.4); }
  .cover-footer-right { display: table-cell; text-align: right; font-size: 8pt; color: rgba(255,255,255,0.4); }

  /* ── SAUT DE PAGE ── */
  .page-break { page-break-after: always; }

  /* ── EN-TÊTE INTERNE ── */
  .doc-header {
    display: table;
    width: 100%;
    padding: 10pt 0 8pt;
    border-bottom: 2pt solid #1a9e3f;
    margin-bottom: 18pt;
  }
  .doc-header-left  { display: table-cell; font-size: 8pt; color: #666; vertical-align: middle; }
  .doc-header-right { display: table-cell; text-align: right; font-size: 8pt; color: #1a9e3f; font-weight: 700; vertical-align: middle; }

  /* ── TITRES DE SECTIONS ── */
  .section-num {
    display: inline-block;
    background: #1a9e3f;
    color: #fff;
    font-size: 8pt;
    font-weight: 900;
    padding: 2pt 8pt;
    border-radius: 4pt;
    margin-right: 8pt;
    vertical-align: middle;
  }
  h1 {
    font-size: 16pt;
    font-weight: 900;
    color: #0a1628;
    margin-bottom: 14pt;
    padding-bottom: 6pt;
    border-bottom: 2pt solid #1a9e3f;
  }
  h2 {
    font-size: 12pt;
    font-weight: 800;
    color: #0a1628;
    margin: 18pt 0 8pt;
    padding-left: 10pt;
    border-left: 3pt solid #1a9e3f;
  }
  h3 {
    font-size: 10.5pt;
    font-weight: 700;
    color: #1a3a1a;
    margin: 12pt 0 6pt;
  }
  p { margin-bottom: 8pt; }

  /* ── TABLEAUX ── */
  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14pt;
    font-size: 9.5pt;
  }
  th {
    background: #0a1628;
    color: #fff;
    padding: 7pt 10pt;
    text-align: left;
    font-size: 8.5pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }
  td {
    padding: 6pt 10pt;
    border-bottom: 1pt solid #e8f0e8;
    vertical-align: top;
  }
  tr:nth-child(even) td { background: #f6faf6; }
  tr:last-child td { border-bottom: none; }

  /* ── BOÎTES INFO ── */
  .box {
    padding: 12pt 16pt;
    border-radius: 8pt;
    margin-bottom: 14pt;
  }
  .box-green  { background: #e8f5e9; border-left: 4pt solid #1a9e3f; }
  .box-orange { background: #fff3e0; border-left: 4pt solid #ff6600; }
  .box-blue   { background: #e3f2fd; border-left: 4pt solid #1565c0; }
  .box-red    { background: #fce4ec; border-left: 4pt solid #c62828; }
  .box-dark   { background: #1a2a3a; color: #fff; border-radius: 8pt; padding: 14pt 18pt; }
  .box-dark code { color: #4ade80; font-size: 9pt; }

  .box-title { font-weight: 800; font-size: 10pt; margin-bottom: 4pt; }

  /* ── GRILLE KPI ── */
  .kpi-row { display: table; width: 100%; margin-bottom: 16pt; }
  .kpi-cell { display: table-cell; padding: 14pt 10pt; text-align: center; background: #f6faf6; border: 1pt solid #d4e8d4; border-radius: 8pt; }
  .kpi-cell + .kpi-cell { margin-left: 8pt; }
  .kpi-val { font-size: 20pt; font-weight: 900; color: #1a9e3f; }
  .kpi-lbl { font-size: 7.5pt; color: #555; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 2pt; }

  /* ── COLONNES ── */
  .col2 { display: table; width: 100%; margin-bottom: 14pt; }
  .col2-left  { display: table-cell; width: 48%; vertical-align: top; padding-right: 8pt; }
  .col2-right { display: table-cell; width: 48%; vertical-align: top; padding-left: 8pt; }

  /* ── BADGES ── */
  .badge {
    display: inline-block;
    padding: 2pt 8pt;
    border-radius: 10pt;
    font-size: 8pt;
    font-weight: 700;
  }
  .badge-green  { background: #e8f5e9; color: #1a9e3f; }
  .badge-orange { background: #fff3e0; color: #e65100; }
  .badge-blue   { background: #e3f2fd; color: #1565c0; }
  .badge-red    { background: #fce4ec; color: #c62828; }

  /* ── ARCHITECTURE SCHEMA ── */
  .arch-box {
    background: #f8fffe;
    border: 1pt solid #c8e6c9;
    border-radius: 6pt;
    padding: 10pt 14pt;
    font-family: "Courier New", monospace;
    font-size: 8.5pt;
    color: #1a3a1a;
    margin-bottom: 12pt;
    white-space: pre-wrap;
  }

  /* ── PIED DE PAGE ── */
  .footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 28pt;
    background: #f6faf6;
    border-top: 1pt solid #d4e8d4;
    display: table;
    width: 100%;
    padding: 0 24pt;
  }
  .footer-left  { display: table-cell; font-size: 7.5pt; color: #888; vertical-align: middle; }
  .footer-right { display: table-cell; text-align: right; font-size: 7.5pt; color: #1a9e3f; font-weight: 700; vertical-align: middle; }

  /* ── LISTE STYLÉE ── */
  ul.styled { padding-left: 18pt; margin-bottom: 10pt; }
  ul.styled li { margin-bottom: 4pt; font-size: 10pt; }
  ul.styled li::marker { color: #1a9e3f; }

  /* ── QUOTE ── */
  blockquote {
    background: #f0f7f0;
    border-left: 4pt solid #1a9e3f;
    padding: 10pt 16pt;
    margin: 12pt 0;
    font-style: italic;
    font-size: 11pt;
    color: #2e4a2e;
    border-radius: 0 6pt 6pt 0;
  }

  /* ── LIGNE DE SÉPARATION ── */
  hr { border: none; border-top: 1pt solid #e0e0e0; margin: 14pt 0; }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════
     PAGE DE COUVERTURE
═══════════════════════════════════════════════════ --}}
<div class="cover">

  <div class="cover-logo">⚡</div>

  <div class="cover-badge">Hackathon CIE — Côte d'Ivoire — 2026</div>

  <div class="cover-title">INNOVAN<span>-CI</span></div>
  <div class="cover-subtitle">Système Intelligent de Prédiction des Coupures Électriques</div>
  <div class="cover-tagline">"Anticiper. Alerter. Protéger."</div>

  <table class="cover-stats">
    <tr>
      <td class="cover-stat">
        <div class="cover-stat-val">296</div>
        <div class="cover-stat-lbl">Postes électriques</div>
      </td>
      <td class="cover-stat">
        <div class="cover-stat-val">1.9M</div>
        <div class="cover-stat-lbl">Observations d'entraînement</div>
      </td>
      <td class="cover-stat">
        <div class="cover-stat-val">99.9%</div>
        <div class="cover-stat-lbl">Recall du modèle IA</div>
      </td>
      <td class="cover-stat">
        <div class="cover-stat-val">5</div>
        <div class="cover-stat-lbl">Langues supportées</div>
      </td>
    </tr>
  </table>

  <div style="margin-top:30pt;color:rgba(255,255,255,0.35);font-size:8.5pt;line-height:1.8">
    Compagnie Ivoirienne d'Électricité (CIE)<br>
    Projet tutoré — Intelligence Artificielle &amp; Gestion du Réseau<br>
    Juin 2026
  </div>

  <div class="cover-footer">
    <div class="cover-footer-left">INNOVAN-CI v8 — Confidentiel</div>
    <div class="cover-footer-right">Rapport Technique Complet</div>
  </div>
</div>

<div class="page-break"></div>

{{-- ═══════════════════════════════════════════════════
     EN-TÊTE POUR TOUTES LES PAGES SUIVANTES
═══════════════════════════════════════════════════ --}}
<div class="footer">
  <div class="footer-left">INNOVAN-CI — Rapport de Projet 2026 — Confidentiel CIE</div>
  <div class="footer-right">⚡ INNOVAN-CI v8</div>
</div>

{{-- PAGE 1 — INTRO + THÈME --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Rapport Technique Complet</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1><span class="section-num">01</span> Introduction &amp; Contexte</h1>

<p>
<strong>INNOVAN-CI</strong> est une plateforme intelligente de <strong>prédiction et gestion des coupures électriques</strong> développée pour la <strong>Compagnie Ivoirienne d'Électricité (CIE)</strong>. Ce projet a été réalisé dans le cadre d'un Hackathon national ayant pour objectif de moderniser la gestion du réseau électrique ivoirien grâce à l'intelligence artificielle et au traitement des données en temps réel.
</p>

<div class="box box-green">
  <div class="box-title">Mission du projet</div>
  Permettre à la CIE d'anticiper les pannes électriques, d'en informer proactivement les populations dans leurs langues locales, et d'optimiser les interventions des équipes terrain grâce aux nouvelles technologies.
</div>

<h2>1.1 Thème</h2>

<blockquote>
  "Intelligence Artificielle au service de la Gestion Prédictive du Réseau Électrique en Côte d'Ivoire"
</blockquote>

<p>Le thème s'articule autour de <strong>trois axes majeurs</strong> :</p>

<table>
  <tr><th>Axe</th><th>Description</th></tr>
  <tr><td><span class="badge badge-green">Prédiction IA</span></td><td>Anticipation des coupures jusqu'à 5 jours à l'avance grâce au Machine Learning (Random Forest)</td></tr>
  <tr><td><span class="badge badge-orange">Inclusion numérique</span></td><td>Notifications multilingues en français, dioula, baoulé, bété et nouchi via l'IA générative (Groq LLaMA 3.3)</td></tr>
  <tr><td><span class="badge badge-blue">Gestion opérationnelle</span></td><td>Tableau de bord centralisé pour agents terrain, administrateurs et clients abonnés</td></tr>
</table>

<h2>1.2 Problématique</h2>

<p>La Côte d'Ivoire fait face à des défis majeurs dans la gestion de son réseau électrique :</p>

<div class="col2">
  <div class="col2-left">
    <div class="box box-red">
      <div class="box-title">Du côté des populations</div>
      <ul class="styled">
        <li>Coupures sans préavis, prenant les citoyens au dépourvu</li>
        <li>Alertes officielles trop tardives ou inexistantes</li>
        <li>Barrière de la langue (population non francophone)</li>
        <li>Aucun suivi de consommation ni de solde prépayé</li>
      </ul>
    </div>
  </div>
  <div class="col2-right">
    <div class="box box-orange">
      <div class="box-title">Du côté de la CIE &amp; terrain</div>
      <ul class="styled">
        <li>Aucun outil centralisé pour coordonner les interventions</li>
        <li>Gestion purement réactive (après la panne)</li>
        <li>Absence d'analyse des causes pour la maintenance</li>
        <li>296 postes électriques sans centralisation</li>
      </ul>
    </div>
  </div>
</div>

<div class="box box-blue">
  <div class="box-title">Énoncé de la problématique centrale</div>
  Comment anticiper les coupures électriques sur le réseau ivoirien, communiquer efficacement avec les populations dans leurs langues locales, et optimiser les interventions des équipes terrain grâce aux nouvelles technologies ?
</div>

<div class="page-break"></div>

{{-- PAGE 2 — SOLUTION + ARCHITECTURE --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Rapport Technique Complet</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1><span class="section-num">02</span> Solution Proposée &amp; Architecture</h1>

<h2>2.1 Les 4 piliers de la solution</h2>

<table>
  <tr>
    <th>Pilier</th><th>Description</th><th>Technologie clé</th>
  </tr>
  <tr>
    <td><strong>🤖 Prédiction IA</strong></td>
    <td>Anticipe les coupures jusqu'à 5 jours avant, identifie la cause probable</td>
    <td>Random Forest (scikit-learn)</td>
  </tr>
  <tr>
    <td><strong>🌍 Multilinguisme</strong></td>
    <td>Génère des alertes contextuelles en 5 langues locales ivoiriennes</td>
    <td>Groq API (LLaMA 3.3-70B)</td>
  </tr>
  <tr>
    <td><strong>📊 Gestion terrain</strong></td>
    <td>Dashboard centralisé pour admin et agents avec filtrage par zone</td>
    <td>Laravel 12 (PHP 8.2)</td>
  </tr>
  <tr>
    <td><strong>💳 Facturation</strong></td>
    <td>Suivi de consommation horaire individuel et solde prépayé rechargeable</td>
    <td>Artisan Command (cron)</td>
  </tr>
</table>

<h2>2.2 Architecture générale</h2>

<div class="arch-box">NAVIGATEUR (Admin / Agent / Client)
         │ HTTP
         ▼
┌────────────────────────────────────────┐
│        LARAVEL 12 (PHP 8.2)            │
│  AuthController  │  AdminController    │
│  ClientController│  DatasetController  │
│                                        │
│  Models: User, Alert, PosteDonnee,     │
│          Incident, Maintenance,        │
│          ConsumptionLog                │
│                                        │
│  TranslationService ──► Groq LLaMA     │
│  SimulateConsumption (cron horaire)    │
│  SQLite Database (basecie)             │
└──────────────┬─────────────────────────┘
               │ POST /predict (JSON)
               ▼
┌────────────────────────────────────────┐
│   FLASK API — INNOVAN-CI v8 (:5001)    │
│                                        │
│  RandomForest Binaire  (600 arbres)    │
│  RandomForest Multiclasse (400 arbres) │
│  31 features │ 296 postes │ 30 zones   │
│  Seuil opérateur : 0.385               │
└────────────────────────────────────────┘</div>

<h2>2.3 Flux de données : Prédiction</h2>

<div class="arch-box">[Admin uploade CSV] ──► [Flask /predict-csv] ──► [Alertes créées en BDD]
      │ si API down                                            │
      └──► [Flask /predict] ──► si down ──► [Simulation]      │
                                                               ▼
                                              [Agents notifiés par zone]
                                              [Clients informés en temps réel]
                                                               │
                                              [TranslationService → Groq API]
                                              [Texte en langue choisie + Audio]</div>

<div class="page-break"></div>

{{-- PAGE 3 — OUTILS & TECHNOLOGIES --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Rapport Technique Complet</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1><span class="section-num">03</span> Outils &amp; Technologies</h1>

<table>
  <tr><th>Couche</th><th>Technologie</th><th>Version</th><th>Rôle</th></tr>
  <tr><td><strong>Framework Web</strong></td><td>Laravel</td><td>12.x</td><td>Routage, ORM Eloquent, sessions, middleware</td></tr>
  <tr><td><strong>Langage Backend</strong></td><td>PHP</td><td>8.2+</td><td>Logique métier, contrôleurs</td></tr>
  <tr><td><strong>API ML</strong></td><td>Flask + Flask-CORS</td><td>3.x</td><td>Serveur de modèles Machine Learning</td></tr>
  <tr><td><strong>Langage ML</strong></td><td>Python</td><td>3.13</td><td>Preprocessing, feature engineering, inférence</td></tr>
  <tr><td><strong>Modèle IA</strong></td><td>scikit-learn RandomForest</td><td>1.6.1</td><td>Classification binaire + multiclasse</td></tr>
  <tr><td><strong>Base de données</strong></td><td>SQLite</td><td>3.39.2</td><td>Persistance locale (données, sessions, cache)</td></tr>
  <tr><td><strong>Frontend UI</strong></td><td>Blade + Bootstrap 5</td><td>—</td><td>Rendu côté serveur, composants UI</td></tr>
  <tr><td><strong>Graphiques</strong></td><td>Chart.js</td><td>—</td><td>Tableaux de bord analytiques interactifs</td></tr>
  <tr><td><strong>Icônes</strong></td><td>Font Awesome</td><td>6.4.0</td><td>Éléments visuels</td></tr>
  <tr><td><strong>Traduction IA</strong></td><td>Groq API (LLaMA 3.3-70B)</td><td>—</td><td>Génération de textes d'alerte multilingues</td></tr>
  <tr><td><strong>Météo</strong></td><td>OpenWeatherMap API</td><td>—</td><td>Données météo en temps réel (optionnel)</td></tr>
  <tr><td><strong>PDF</strong></td><td>DomPDF (Laravel)</td><td>v3.1</td><td>Génération de rapports PDF</td></tr>
</table>

<h2>3.1 Modèles IA pré-entraînés</h2>

<table>
  <tr><th>Fichier</th><th>Type</th><th>Arbres</th><th>Rôle</th></tr>
  <tr><td><code>model_coupure_v8.pkl</code></td><td>RandomForest Binaire</td><td>600</td><td>Prédit si coupure (oui/non)</td></tr>
  <tr><td><code>model_cause_v8.pkl</code></td><td>RandomForest Multiclasse</td><td>400</td><td>Identifie la cause de la panne</td></tr>
  <tr><td><code>seuil_operateur_v8.pkl</code></td><td>Seuil de décision</td><td>—</td><td>0.385 (opérateur) / 0.40 (citoyen)</td></tr>
  <tr><td><code>le_zone_v8.pkl</code></td><td>LabelEncoder</td><td>—</td><td>Encode les 30 zones géographiques</td></tr>
  <tr><td><code>le_cluster_v8.pkl</code></td><td>LabelEncoder</td><td>—</td><td>Encode les clusters A/B/C</td></tr>
  <tr><td><code>le_saison_v8.pkl</code></td><td>LabelEncoder</td><td>—</td><td>Encode les 4 saisons ivoiriennes</td></tr>
</table>

<h2>3.2 Performances du modèle</h2>

<table>
  <tr><th>Métrique</th><th>Valeur</th><th>Signification</th></tr>
  <tr>
    <td><strong>Recall</strong></td>
    <td><span class="badge badge-green">99,9%</span></td>
    <td>Quasi aucune vraie coupure n'est ratée (priorité citoyenne)</td>
  </tr>
  <tr>
    <td><strong>ROC-AUC</strong></td>
    <td>0.600</td>
    <td>Discrimination modérée (dataset déséquilibré 23,9%)</td>
  </tr>
  <tr>
    <td><strong>OOB Score</strong></td>
    <td>0.719</td>
    <td>Bonne généralisation sur données non vues</td>
  </tr>
  <tr>
    <td><strong>F1-score</strong></td>
    <td>0.398</td>
    <td>Équilibre précision/rappel au seuil opérateur</td>
  </tr>
</table>

<div class="box box-orange">
  <div class="box-title">Choix stratégique : priorité au Recall</div>
  Un Recall de 99,9% signifie que le système alerte pour presque toutes les vraies coupures. Le coût d'une fausse alerte (légère gêne) est bien inférieur au coût d'une coupure non annoncée (pertes économiques, insécurité).
</div>

<div class="page-break"></div>

{{-- PAGE 4 — MODÈLE IA DÉTAIL --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Rapport Technique Complet</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1><span class="section-num">04</span> Modèle d'Intelligence Artificielle</h1>

<h2>4.1 Données d'entraînement</h2>

<table>
  <tr><th>Paramètre</th><th>Valeur</th></tr>
  <tr><td>Observations totales</td><td><strong>1 944 720</strong></td></tr>
  <tr><td>Période couverte</td><td>2022 – 2024 (3 ans, résolution horaire)</td></tr>
  <tr><td>Postes électriques</td><td>296 postes sur tout le territoire ivoirien</td></tr>
  <tr><td>Zones géographiques</td><td>30 zones / 231 quartiers</td></tr>
  <tr><td>Taux de coupures (classe positive)</td><td>23,9% des observations</td></tr>
</table>

<h2>4.2 Les 31 features d'entrée</h2>

<table>
  <tr><th>Catégorie</th><th>Features</th></tr>
  <tr>
    <td><strong>Temporelles (7)</strong></td>
    <td>heure_sin, heure_cos, mois_sin, mois_cos, est_weekend, saison_enc, saison_seche_bin</td>
  </tr>
  <tr>
    <td><strong>Hydrauliques (4)</strong></td>
    <td>pluvio (pluie normalisée), barrage (niveau barrage %), stress_hydraulique, bq_critique</td>
  </tr>
  <tr>
    <td><strong>Réseau (4)</strong></td>
    <td>charge_mw, capacite_mw, taux_utilisation, taux_squared</td>
  </tr>
  <tr>
    <td><strong>Météo (3)</strong></td>
    <td>temperature_c, humidite_pct, temp_max_24h</td>
  </tr>
  <tr>
    <td><strong>Infrastructure (6)</strong></td>
    <td>age_cable_ans, maintenance_recente, nb_incidents_7j, old_network, incident_critique, incidents_moy_3j</td>
  </tr>
  <tr>
    <td><strong>Spatiales (2)</strong></td>
    <td>cluster_enc (A/B/C), zone_enc (30 zones)</td>
  </tr>
  <tr>
    <td><strong>Interactions (5)</strong></td>
    <td>age×taux, temp×taux, barrage×temp, barrage×saison, age×incidents</td>
  </tr>
</table>

<h2>4.3 Classification des causes (5 classes)</h2>

<table>
  <tr><th>Cause</th><th>Fréquence</th><th>Facteurs déclencheurs</th></tr>
  <tr><td><strong>Vieillissement équipements</strong></td><td><span class="badge badge-red">28,3%</span></td><td>Câbles, transformateurs vétustes (âge &gt; 18 ans)</td></tr>
  <tr><td><strong>Surcharge réseau</strong></td><td><span class="badge badge-orange">24,8%</span></td><td>Demande dépasse la capacité (taux &gt; 80%)</td></tr>
  <tr><td><strong>Intempérie / Pluies</strong></td><td><span class="badge badge-blue">21,6%</span></td><td>Fortes pluies, orages, inondations (saison des pluies)</td></tr>
  <tr><td><strong>Défaut câble HT</strong></td><td><span class="badge badge-orange">16,4%</span></td><td>Défauts d'isolation, soudures défectueuses</td></tr>
  <tr><td><strong>Vandalisme / Vol câble</strong></td><td><span class="badge badge-red">8,8%</span></td><td>Vols de cuivre, sabotages (zones reculées)</td></tr>
</table>

<h2>4.4 Clusters géographiques</h2>

<table>
  <tr><th>Cluster</th><th>Zones</th><th>Postes</th><th>Profil infrastructure</th></tr>
  <tr>
    <td><span class="badge badge-green"><strong>A — Urbain</strong></span></td>
    <td>Abidjan (Yopougon, Cocody, Abobo, Adjamé…)</td>
    <td>178</td>
    <td>Réseau moderne, câbles ~13 ans, forte demande (70% utilisation)</td>
  </tr>
  <tr>
    <td><span class="badge badge-orange"><strong>B — Secondaire</strong></span></td>
    <td>Bouaké, Yamoussoukro, Daloa, San-Pédro</td>
    <td>83</td>
    <td>Infrastructure moyenne, câbles ~15,5 ans, instrumentation limitée</td>
  </tr>
  <tr>
    <td><span class="badge badge-red"><strong>C — Rural/Isolé</strong></span></td>
    <td>Korhogo, Man, Téngrela, zones isolées</td>
    <td>35</td>
    <td>Infrastructure dégradée, câbles ~17 ans, vandalisme élevé</td>
  </tr>
</table>

<div class="page-break"></div>

{{-- PAGE 5 — BASE DE DONNÉES --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Rapport Technique Complet</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1><span class="section-num">05</span> Base de Données</h1>

<h2>5.1 Tables principales (13 migrations)</h2>

<table>
  <tr><th>Table</th><th>Lignes (démo)</th><th>Rôle</th></tr>
  <tr><td><code>users</code></td><td>12+</td><td>Authentification, profils (admin / agent / client)</td></tr>
  <tr><td><code>alerts</code></td><td>Variable (5–30 / prédiction)</td><td>Alertes prédites avec probabilité et cause</td></tr>
  <tr><td><code>poste_donnees</code></td><td>296</td><td>Données centralisées des postes électriques (réseau, météo, hydraulique)</td></tr>
  <tr><td><code>incidents</code></td><td>Variable</td><td>Incidents déclarés par les agents terrain</td></tr>
  <tr><td><code>maintenances</code></td><td>Variable</td><td>Travaux de maintenance enregistrés par zone</td></tr>
  <tr><td><code>consumption_logs</code></td><td>~210 (30j × 7 clients)</td><td>Historique de consommation horaire + facturation</td></tr>
</table>

<h2>5.2 Structure de la table <code>users</code></h2>

<table>
  <tr><th>Colonne</th><th>Type</th><th>Description</th></tr>
  <tr><td>name, email, password</td><td>string</td><td>Identité et authentification (bcrypt)</td></tr>
  <tr><td>role</td><td>enum</td><td>admin | agent | client</td></tr>
  <tr><td>zone</td><td>string nullable</td><td>Zone assignée (agent) ou résidence (client)</td></tr>
  <tr><td>meter_number</td><td>string unique</td><td>Numéro de compteur CIE (clients uniquement)</td></tr>
  <tr><td>language</td><td>string</td><td>fr | dioula | baoule | bete | nouchi</td></tr>
  <tr><td>balance</td><td>decimal(12,2)</td><td>Solde prépayé en FCFA</td></tr>
  <tr><td>daily_kwh</td><td>decimal(6,2)</td><td>Consommation journalière individuelle (kWh)</td></tr>
</table>

<h2>5.3 Structure de la table <code>alerts</code></h2>

<table>
  <tr><th>Colonne</th><th>Type</th><th>Description</th></tr>
  <tr><td>zone, quartier</td><td>string</td><td>Zone géographique concernée</td></tr>
  <tr><td>poste_id, cluster</td><td>string</td><td>Poste source et cluster (A/B/C)</td></tr>
  <tr><td>probability</td><td>tinyint (0-100)</td><td>Probabilité de coupure en %</td></tr>
  <tr><td>cause_probable</td><td>string</td><td>Cause identifiée par le ML</td></tr>
  <tr><td>reason</td><td>string</td><td>Label lisible en français</td></tr>
  <tr><td>start_at, end_at</td><td>datetime</td><td>Fenêtre temporelle prédite</td></tr>
  <tr><td>agents_notified_at</td><td>datetime nullable</td><td>Horodatage de la notification agents</td></tr>
</table>

<div class="page-break"></div>

{{-- PAGE 6 — FONCTIONNALITÉS --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Rapport Technique Complet</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1><span class="section-num">06</span> Fonctionnalités Détaillées</h1>

<h2>6.1 Espace Administrateur</h2>

<table>
  <tr><th>Fonctionnalité</th><th>Description</th></tr>
  <tr>
    <td><strong>Dashboard KPI</strong></td>
    <td>Nombre de clients, alertes urgentes (≥80%), zones affectées, probabilité moyenne, graphique 7 jours, répartition des causes</td>
  </tr>
  <tr>
    <td><strong>Prédiction IA</strong></td>
    <td>Upload CSV → envoi Flask API → inférence RandomForest → création des alertes en BDD + résumé</td>
  </tr>
  <tr>
    <td><strong>Gestion alertes</strong></td>
    <td>Liste filtrée, bouton "Notifier les agents" (avec horodatage agents_notified_at)</td>
  </tr>
  <tr>
    <td><strong>Dataset centralisé</strong></td>
    <td>Consultation et mise à jour des 296 postes (charge, météo, barrage, âge câble)</td>
  </tr>
  <tr>
    <td><strong>Incidents &amp; Maintenance</strong></td>
    <td>Déclaration d'incidents par zone, saisie des maintenances, recalcul automatique des features</td>
  </tr>
  <tr>
    <td><strong>Rapports</strong></td>
    <td>Statistiques par zone, cause, cluster — imprimables pour les réunions de direction</td>
  </tr>
  <tr>
    <td><strong>Gestion équipes</strong></td>
    <td>Roster agents/admins, formulaire d'ajout d'agent avec assignation de zone et rôle</td>
  </tr>
</table>

<h2>6.2 Espace Agent Terrain</h2>

<div class="box box-blue">
  L'agent accède au même dashboard administrateur, mais avec un <strong>filtre automatique sur sa zone assignée</strong> : il ne voit que les alertes, postes et données de sa commune. Il peut déclarer des incidents et des maintenances dans son secteur.
</div>

<h2>6.3 Espace Client</h2>

<table>
  <tr><th>Fonctionnalité</th><th>Description</th></tr>
  <tr>
    <td><strong>Alertes personnalisées</strong></td>
    <td>5 prochaines coupures dans la zone du client, avec code couleur (Rouge ≥80% / Orange 60–79% / Bleu &lt;60%)</td>
  </tr>
  <tr>
    <td><strong>Notification vocale</strong></td>
    <td>Bannière automatique + bouton "Écouter" : MP3 pré-enregistré ou synthèse vocale navigateur</td>
  </tr>
  <tr>
    <td><strong>5 langues</strong></td>
    <td>Français, Dioula, Baoulé, Bété, Nouchi — sélectable à tout moment</td>
  </tr>
  <tr>
    <td><strong>Consommation réelle</strong></td>
    <td>kWh ce mois (unique par client), graphique 7 jours, historique 6 mois, variation vs mois précédent</td>
  </tr>
  <tr>
    <td><strong>Solde prépayé</strong></td>
    <td>Affichage du solde, autonomie estimée (jours restants), alerte si solde faible</td>
  </tr>
  <tr>
    <td><strong>Rechargement en ligne</strong></td>
    <td>Orange Money, MTN MoMo, Moov Money, Carte bancaire — montants de 500 à 500 000 FCFA</td>
  </tr>
</table>

<h2>6.4 Système de facturation prépayée (simulation)</h2>

<div class="col2">
  <div class="col2-left">
    <div class="box box-green">
      <div class="box-title">Fonctionnement</div>
      <ul class="styled">
        <li>Chaque client a un <code>daily_kwh</code> unique (8–22 kWh/j)</li>
        <li>Déduction horaire : <code>daily_kwh ÷ 24 × 35 FCFA</code></li>
        <li>Variation réaliste : ±8% par heure</li>
        <li>30 jours d'historique générés à l'inscription</li>
      </ul>
    </div>
  </div>
  <div class="col2-right">
    <div class="box box-orange">
      <div class="box-title">Automatisation</div>
      <ul class="styled">
        <li>Commande Artisan : <code>simulate:consumption</code></li>
        <li>Planification : toutes les heures (cron)</li>
        <li>Option rétro-simulation : <code>--backfill=N</code></li>
        <li>Taux : 35 FCFA / kWh</li>
      </ul>
    </div>
  </div>
</div>

<div class="page-break"></div>

{{-- PAGE 7 — MULTILINGUISME + SÉCURITÉ --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Rapport Technique Complet</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1><span class="section-num">07</span> Multilinguisme &amp; Traduction IA</h1>

<h2>7.1 Les 5 langues supportées</h2>

<table>
  <tr><th>Code</th><th>Langue</th><th>Région cible</th><th>Prompt IA</th></tr>
  <tr><td><code>fr</code></td><td>Français</td><td>Standard national</td><td>Professionnel, style CIE officiel</td></tr>
  <tr><td><code>dioula</code></td><td>Dioula (Bambara CI)</td><td>Nord CI : Bouaké, Korhogo</td><td>Mots courants, mélange français si technique</td></tr>
  <tr><td><code>baoule</code></td><td>Baoulé</td><td>Centre CI : Yamoussoukro, Bouaflé</td><td>Baoulé simple, termes techniques en français</td></tr>
  <tr><td><code>bete</code></td><td>Bété</td><td>Centre-Ouest CI : Gagnoa, Daloa</td><td>Bété accessible, mots techniques en français</td></tr>
  <tr><td><code>nouchi</code></td><td>Nouchi (argot abidjanais)</td><td>Jeunes d'Abidjan</td><td>Dynamique, court, compréhensible</td></tr>
</table>

<h2>7.2 Architecture TranslationService</h2>

<div class="arch-box">Client demande alerte → ClientController::latestAlert()
                         │
                         ▼
              TranslationService::translateAlert(alert, lang)
                         │
              ┌──────────┴──────────┐
              │                     │
         Cache hit ?           Cache miss
         (1h TTL)                    │
              │                      ▼
              │           POST Groq API (LLaMA 3.3-70B)
              │           temperature=0.3 | max_tokens=120
              │                      │
              │              Groq indisponible ?
              │                      │
              │              Texte statique fallback
              │                      │
              └──────────────────────┘
                         │
                    Texte traduit retourné
                    (MP3 ou Web Speech API)</div>

<h2>7.3 Exemple de sorties par langue</h2>

<table>
  <tr><th>Langue</th><th>Exemple de message généré</th></tr>
  <tr><td>Français</td><td>"Attention ! Coupure d'électricité prévue dans votre zone Cocody le 08/06 à 14h00. Cause : Surcharge du réseau."</td></tr>
  <tr><td>Dioula</td><td>"Attention ! Courant bena diya Cocody la, 08/06 à 14h."</td></tr>
  <tr><td>Baoulé</td><td>"Attention ! Courant bé yiè Cocody, 08/06 à 14h."</td></tr>
  <tr><td>Bété</td><td>"Coupure courant Cocody le 08/06 à 14h."</td></tr>
  <tr><td>Nouchi</td><td>"Wôh ! Courant va dja à Cocody le 08/06, djo !"</td></tr>
</table>

<h1><span class="section-num">08</span> Sécurité &amp; Contrôle d'Accès</h1>

<h2>8.1 Middleware de rôles</h2>

<table>
  <tr><th>Middleware</th><th>Rôles autorisés</th><th>Routes protégées</th></tr>
  <tr><td><code>EnsureAgent</code></td><td>admin, agent</td><td><code>/admin/*</code></td></tr>
  <tr><td><code>EnsureClient</code></td><td>client</td><td><code>/client/*</code></td></tr>
</table>

<h2>8.2 Mesures de sécurité</h2>

<table>
  <tr><th>Mesure</th><th>Implémentation</th></tr>
  <tr><td>Hachage des mots de passe</td><td>bcrypt via Laravel Authenticatable</td></tr>
  <tr><td>Protection CSRF</td><td>Token sur tous les formulaires POST</td></tr>
  <tr><td>Validation des entrées</td><td><code>$request-&gt;validate()</code> avec règles strictes</td></tr>
  <tr><td>Filtrage par zone (agents)</td><td>Clause Eloquent <code>where('zone', $user-&gt;zone)</code> automatique</td></tr>
  <tr><td>Numéro de compteur unique</td><td>Contrainte <code>unique:users,meter_number</code></td></tr>
  <tr><td>Requêtes ORM uniquement</td><td>Eloquent protège contre les injections SQL</td></tr>
</table>

<div class="page-break"></div>

{{-- PAGE 8 — RÉSILIENCE + COMPTES + CONCLUSION --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Rapport Technique Complet</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1><span class="section-num">09</span> Résilience &amp; Démarrage</h1>

<h2>9.1 Système de fallback (zéro downtime)</h2>

<table>
  <tr><th>Composant</th><th>Niveau 1</th><th>Niveau 2</th><th>Niveau 3</th></tr>
  <tr>
    <td><strong>API ML</strong></td>
    <td>Flask /predict-csv (CSV réel)</td>
    <td>Flask /predict (features synthétiques)</td>
    <td>Simulation locale (aléatoire réaliste)</td>
  </tr>
  <tr>
    <td><strong>Traduction</strong></td>
    <td>Groq LLaMA 3.3 (texte naturel)</td>
    <td>—</td>
    <td>Textes statiques prédéfinis (5 langues)</td>
  </tr>
  <tr>
    <td><strong>Audio alertes</strong></td>
    <td>MP3 pré-enregistré</td>
    <td>Web Speech API (TTS navigateur)</td>
    <td>Texte affiché uniquement</td>
  </tr>
</table>

<h2>9.2 Comptes de test disponibles</h2>

<table>
  <tr><th>Rôle</th><th>Email</th><th>Mot de passe</th><th>Zone</th></tr>
  <tr><td><span class="badge badge-red">Administrateur</span></td><td>agent@cie.local</td><td>password</td><td>Toutes</td></tr>
  <tr><td><span class="badge badge-blue">Agent terrain</span></td><td>agent.cocody@cie.local</td><td>password</td><td>Cocody</td></tr>
  <tr><td><span class="badge badge-blue">Agent terrain</span></td><td>agent.abobo@cie.local</td><td>password</td><td>Abobo</td></tr>
  <tr><td><span class="badge badge-blue">Agent terrain</span></td><td>agent.yopougon@cie.local</td><td>password</td><td>Yopougon</td></tr>
  <tr><td><span class="badge badge-green">Client</span></td><td>client@demo.local</td><td>password</td><td>Cocody</td></tr>
  <tr><td><span class="badge badge-green">Client</span></td><td>konan.jean@gmail.com</td><td>password</td><td>Cocody</td></tr>
  <tr><td><span class="badge badge-green">Client</span></td><td>coulibaly.ibou@gmail.com</td><td>password</td><td>Yopougon</td></tr>
</table>

<h2>9.3 Procédure de démarrage</h2>

<div class="box box-dark">
<code><strong># Terminal 1 — API Machine Learning</strong>
cd C:\Hackathon\tutore_2
py api_innovan.py
# → http://127.0.0.1:5001/health

# Terminal 2 — Application Web Laravel
cd C:\Hackathon\tutore_2\tutore2
php artisan serve
# → http://127.0.0.1:8000

# Simuler une heure de consommation (démo)
php artisan simulate:consumption

# Générer l'historique 30 jours
php artisan simulate:consumption --backfill=30</code>
</div>

<div class="page-break"></div>

{{-- PAGE 9 — CONCLUSION --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Rapport Technique Complet</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1><span class="section-num">10</span> Conclusion &amp; Perspectives</h1>

<h2>10.1 Résumé des apports</h2>

<table>
  <tr><th>Défi initial</th><th>Solution apportée par INNOVAN-CI</th></tr>
  <tr><td>Coupures sans préavis</td><td>Prédiction jusqu'à 5 jours avant avec probabilité (Recall 99,9%)</td></tr>
  <tr><td>Barrière de la langue</td><td>5 langues locales + génération IA contextuelle (Groq LLaMA)</td></tr>
  <tr><td>Gestion réactive</td><td>Dashboard centralisé + alertes proactives aux agents par zone</td></tr>
  <tr><td>Pas de suivi consommation</td><td>Consommation horaire individuelle unique + solde prépayé</td></tr>
  <tr><td>Aucune analyse causale</td><td>5 causes identifiées par ML → planification de maintenance ciblée</td></tr>
  <tr><td>Infrastructure dispersée</td><td>296 postes centralisés dans une seule base de données</td></tr>
</table>

<h2>10.2 Points forts techniques</h2>

<ul class="styled">
  <li><strong>Recall 99,9%</strong> — Presque aucune vraie coupure n'est manquée</li>
  <li><strong>Dataset de 1,94 million d'observations</strong> sur 3 ans de données ivoiriennes</li>
  <li><strong>Architecture résiliente</strong> — 3 niveaux de fallback (API → simulation → statique)</li>
  <li><strong>Multilingue natif</strong> — Groq LLaMA pour des traductions contextuelles et naturelles</li>
  <li><strong>Facturation intégrée</strong> — Système prépayé avec simulation horaire automatique</li>
  <li><strong>Multi-rôle complet</strong> — Admin, Agent terrain, Client avec accès différenciés et sécurisés</li>
  <li><strong>Zéro downtime</strong> — Le système continue de fonctionner même si l'API ML est hors ligne</li>
</ul>

<h2>10.3 Améliorations futures recommandées</h2>

<table>
  <tr><th>Priorité</th><th>Amélioration</th></tr>
  <tr><td><span class="badge badge-red">Haute</span></td><td>Intégration vrai gateway de paiement (Orange Money API, MTN MoMo)</td></tr>
  <tr><td><span class="badge badge-red">Haute</span></td><td>Envoi SMS automatiques aux clients (Twilio / opérateur ivoirien)</td></tr>
  <tr><td><span class="badge badge-red">Haute</span></td><td>Migration SQLite → PostgreSQL pour la scalabilité en production</td></tr>
  <tr><td><span class="badge badge-orange">Moyenne</span></td><td>Application mobile Flutter pour les agents terrain</td></tr>
  <tr><td><span class="badge badge-orange">Moyenne</span></td><td>Carte géographique interactive (Leaflet) des zones à risque</td></tr>
  <tr><td><span class="badge badge-orange">Moyenne</span></td><td>Pipeline de ré-entraînement automatique mensuel du modèle</td></tr>
  <tr><td><span class="badge badge-green">Basse</span></td><td>Valeurs SHAP pour l'explicabilité des prédictions individuelles</td></tr>
  <tr><td><span class="badge badge-green">Basse</span></td><td>API publique RESTful pour partenaires institutionnels</td></tr>
</table>

<br>

<div class="box box-green" style="text-align:center;padding:20pt 24pt;">
  <div style="font-size:14pt;font-weight:900;color:#0a5e0a;margin-bottom:8pt;">
    INNOVAN-CI — Prêt pour une Phase Pilote
  </div>
  <div style="color:#2e4a2e;font-size:10pt;">
    Le système démontre qu'il est possible, avec des outils accessibles et un dataset bien construit,<br>
    de développer une solution IA à fort impact social pour le secteur énergétique en Afrique de l'Ouest.<br><br>
    <strong>Première étape recommandée :</strong> Déploiement pilote sur les zones d'Abidjan (Cluster A)<br>
    avec collecte de données réelles pour affiner le modèle.
  </div>
</div>

<br>
<hr>
<div style="text-align:center;font-size:8pt;color:#999;margin-top:10pt;">
  Rapport généré automatiquement — INNOVAN-CI v8 — Hackathon CIE Côte d'Ivoire — Juin 2026<br>
  Compagnie Ivoirienne d'Électricité | Projet tutoré — Intelligence Artificielle &amp; Réseau Électrique
</div>

</body>
</html>
