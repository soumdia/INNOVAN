<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>INNOVAN-CI — Documentation Base de Données</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10pt; color:#1a1a2e; line-height:1.55; }

/* ── PAGE DE COUVERTURE ── */
.cover {
  min-height:270mm; background:linear-gradient(160deg,#0a1628 0%,#1a3a1a 60%,#0f2010 100%);
  color:#fff; text-align:center; padding:60pt 40pt 40pt; position:relative;
}
.cover-icon { width:70pt; height:70pt; background:#1a9e3f; border-radius:18pt;
  margin:0 auto 24pt; line-height:70pt; font-size:28pt; font-weight:900; color:#fff; }
.cover-badge { display:inline-block; background:rgba(26,158,63,.25);
  border:1pt solid rgba(26,158,63,.5); border-radius:20pt; padding:5pt 18pt;
  font-size:8pt; color:#4ade80; font-weight:700; letter-spacing:.08em;
  text-transform:uppercase; margin-bottom:20pt; }
.cover-title { font-size:28pt; font-weight:900; color:#fff; margin-bottom:6pt; }
.cover-title span { color:#4ade80; }
.cover-sub { font-size:13pt; color:rgba(255,255,255,.6); margin-bottom:6pt; }
.cover-desc { font-size:10pt; color:rgba(255,255,255,.4); margin-bottom:36pt; font-style:italic; }

.cover-stats { display:table; width:100%; margin:24pt 0; }
.cover-stat  { display:table-cell; text-align:center; padding:14pt;
  border:1pt solid rgba(255,255,255,.1); }
.cover-stat-val { font-size:20pt; font-weight:900; color:#4ade80; }
.cover-stat-lbl { font-size:7pt; color:rgba(255,255,255,.45); text-transform:uppercase;
  letter-spacing:.06em; margin-top:3pt; }

.cover-footer { position:absolute; bottom:24pt; left:40pt; right:40pt;
  border-top:1pt solid rgba(255,255,255,.1); padding-top:12pt;
  display:table; width:calc(100% - 80pt); }
.cover-footer-left  { display:table-cell; text-align:left;  font-size:8pt; color:rgba(255,255,255,.35); }
.cover-footer-right { display:table-cell; text-align:right; font-size:8pt; color:rgba(255,255,255,.35); }

/* ── SAUT DE PAGE ── */
.page-break { page-break-after:always; }

/* ── EN-TÊTE INTERNE ── */
.doc-header { display:table; width:100%; padding:10pt 0 8pt;
  border-bottom:2pt solid #1a9e3f; margin-bottom:18pt; }
.doc-header-left  { display:table-cell; font-size:8pt; color:#666; vertical-align:middle; }
.doc-header-right { display:table-cell; text-align:right; font-size:8pt;
  color:#1a9e3f; font-weight:700; vertical-align:middle; }

.footer { position:fixed; bottom:0; left:0; right:0; height:26pt;
  background:#f6faf6; border-top:1pt solid #d4e8d4; display:table; width:100%; padding:0 24pt; }
.footer-left  { display:table-cell; font-size:7pt; color:#888; vertical-align:middle; }
.footer-right { display:table-cell; text-align:right; font-size:7pt; color:#1a9e3f;
  font-weight:700; vertical-align:middle; }

/* ── TITRES ── */
h1 { font-size:15pt; font-weight:900; color:#0a1628; margin-bottom:14pt;
  padding-bottom:6pt; border-bottom:2pt solid #1a9e3f; }
h2 { font-size:11.5pt; font-weight:800; color:#0a1628; margin:18pt 0 8pt;
  padding-left:10pt; border-left:3pt solid #1a9e3f; }
h3 { font-size:10.5pt; font-weight:700; color:#1a3a1a; margin:12pt 0 5pt; }
p  { margin-bottom:8pt; font-size:10pt; }

/* ── TABLES SQL ── */
table { width:100%; border-collapse:collapse; margin-bottom:14pt; font-size:9pt; }
th { background:#0a1628; color:#fff; padding:6pt 9pt; text-align:left;
  font-size:8pt; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
td { padding:5pt 9pt; border-bottom:1pt solid #e8f0e8; vertical-align:top; }
tr:nth-child(even) td { background:#f6faf6; }
tr:last-child td { border-bottom:none; }
.pk  { color:#e65100; font-weight:700; font-size:8pt; }
.fk  { color:#1565c0; font-weight:700; font-size:8pt; }
.col { font-family:"Courier New",monospace; font-size:8.5pt; color:#1a3a1a; font-weight:700; }
.type{ font-size:8pt; color:#555; font-family:"Courier New",monospace; }
.req { color:#ef4444; font-weight:700; font-size:7.5pt; }
.opt { color:#94a3b8; font-size:7.5pt; }

/* ── BOÎTES INFO ── */
.box { padding:11pt 15pt; border-radius:8pt; margin-bottom:12pt; }
.box-green  { background:#e8f5e9; border-left:4pt solid #1a9e3f; }
.box-orange { background:#fff3e0; border-left:4pt solid #ff6600; }
.box-blue   { background:#e3f2fd; border-left:4pt solid #1565c0; }
.box-title  { font-weight:800; font-size:9.5pt; margin-bottom:3pt; }

/* ══════════════════════════════════════
   MCD — Entités et Relations
═══════════════════════════════════════ */

/* Grille du MCD */
.mcd-page { padding:0; }

/* Entité */
.entity {
  border:2pt solid #0a1628; border-radius:0;
  font-size:8pt; background:#fff;
  display:inline-block; min-width:110pt;
}
.entity-name {
  background:#0a1628; color:#fff;
  padding:5pt 8pt; font-weight:900;
  font-size:9pt; text-align:center;
  text-transform:uppercase; letter-spacing:.04em;
}
.entity-name.green  { background:#1a7a30; }
.entity-name.orange { background:#c15000; }
.entity-name.blue   { background:#1255a0; }
.entity-name.purple { background:#5b21b6; }
.entity-name.teal   { background:#0f766e; }
.entity-attr {
  padding:3pt 8pt; border-bottom:0.5pt solid #e0e0e0;
  display:table; width:100%;
}
.entity-attr:last-child { border-bottom:none; }
.attr-icon { display:table-cell; width:14pt; color:#999; font-size:7.5pt; }
.attr-name { display:table-cell; font-weight:600; color:#1a2e1a; }
.attr-type { display:table-cell; text-align:right; color:#94a3b8; font-size:7pt; font-style:italic; }

/* Ligne de relation (trait horizontal/vertical avec texte) */
.rel-line {
  display:table; width:100%;
  margin-bottom:8pt;
  font-size:7.5pt; color:#555;
}
.rel-left   { display:table-cell; text-align:right; width:40%; vertical-align:middle; font-weight:700; color:#0a1628; }
.rel-center { display:table-cell; text-align:center; padding:0 6pt; vertical-align:middle; }
.rel-right  { display:table-cell; text-align:left;  width:40%; vertical-align:middle; font-weight:700; color:#0a1628; }
.rel-label  { font-size:7.5pt; font-weight:700; color:#1a9e3f; background:#e8f5e9;
  padding:2pt 6pt; border-radius:4pt; display:inline-block; }
.cardinality { font-size:8pt; font-weight:900; color:#ff6600; }

/* Layout entités 2 colonnes */
.mcd-grid { display:table; width:100%; margin-bottom:12pt; }
.mcd-col  { display:table-cell; vertical-align:top; padding:0 4pt; }

/* Séparateur de section */
hr { border:none; border-top:1pt solid #e0e0e0; margin:14pt 0; }

/* Badge */
.badge { display:inline-block; padding:1.5pt 7pt; border-radius:10pt; font-size:7.5pt; font-weight:700; }
.b-green  { background:#e8f5e9; color:#1a7a30; }
.b-orange { background:#fff3e0; color:#c15000; }
.b-blue   { background:#e3f2fd; color:#1255a0; }
.b-red    { background:#fce4ec; color:#c62828; }
.b-gray   { background:#f1f5f9; color:#475569; }
</style>
</head>
<body>

{{-- ═══════════════════ COUVERTURE ═══════════════════ --}}
<div class="cover">
  <div class="cover-icon">DB</div>
  <div class="cover-badge">Documentation Technique — Juin 2026</div>
  <div class="cover-title">Base de Données<br><span>INNOVAN-CI</span></div>
  <div class="cover-sub">Modèle Conceptuel &amp; Description Complète</div>
  <div class="cover-desc">Système de prédiction intelligente des coupures électriques<br>Compagnie Ivoirienne d'Électricité (CIE)</div>

  <table class="cover-stats">
    <tr>
      <td class="cover-stat">
        <div class="cover-stat-val">10</div>
        <div class="cover-stat-lbl">Tables</div>
      </td>
      <td class="cover-stat">
        <div class="cover-stat-val">{{ $stats['users'] }}</div>
        <div class="cover-stat-lbl">Utilisateurs</div>
      </td>
      <td class="cover-stat">
        <div class="cover-stat-val">{{ $stats['postes'] }}</div>
        <div class="cover-stat-lbl">Postes électriques</div>
      </td>
      <td class="cover-stat">
        <div class="cover-stat-val">{{ $stats['alerts'] }}</div>
        <div class="cover-stat-lbl">Alertes générées</div>
      </td>
      <td class="cover-stat">
        <div class="cover-stat-val">{{ $stats['consumption_logs'] }}</div>
        <div class="cover-stat-lbl">Logs consommation</div>
      </td>
    </tr>
  </table>

  <div class="cover-footer">
    <div class="cover-footer-left">INNOVAN-CI v8 — SQLite — Confidentiel</div>
    <div class="cover-footer-right">Documentation BDD</div>
  </div>
</div>

<div class="page-break"></div>

{{-- ═══════════════════ PIED DE PAGE ═══════════════════ --}}
<div class="footer">
  <div class="footer-left">INNOVAN-CI — Base de Données — Documentation 2026</div>
  <div class="footer-right">⚡ INNOVAN-CI</div>
</div>

{{-- ═══════════ PAGE 1 : PRÉSENTATION + MCD ═══════════ --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Documentation Base de Données</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1>1. Présentation de la base de données</h1>

<div class="box box-green">
  <div class="box-title">Technologie : SQLite 3.39.2</div>
  Fichier unique <code>tutore2/basecie</code> — zéro configuration serveur, idéal pour le développement
  et les démos. En production, migration vers PostgreSQL ou MySQL recommandée.
</div>

<p>La base de données INNOVAN-CI centralise toutes les données du système de prédiction :
utilisateurs (admin, agents, clients), alertes prédites par l'IA, état en temps réel des
296 postes électriques, incidents terrain, maintenances et consommation individuelle des clients.</p>

<h2>1.1 Vue d'ensemble des tables</h2>

<table>
  <tr>
    <th>Table</th><th>Rôle fonctionnel</th><th>Lignes</th><th>Relations</th>
  </tr>
  <tr>
    <td><span class="col">users</span></td>
    <td>Comptes utilisateurs (admin, agent terrain, client abonné)</td>
    <td>{{ $stats['users'] }}</td>
    <td>Référencée par 4 tables</td>
  </tr>
  <tr>
    <td><span class="col">alerts</span></td>
    <td>Alertes de coupure prédites par le modèle IA RandomForest</td>
    <td>{{ $stats['alerts'] }}</td>
    <td>Liée à poste_donnees (zone)</td>
  </tr>
  <tr>
    <td><span class="col">poste_donnees</span></td>
    <td>État temps réel des 296 postes électriques (réseau, météo, hydraulique)</td>
    <td>{{ $stats['postes'] }}</td>
    <td>Référencée par incidents, maintenances</td>
  </tr>
  <tr>
    <td><span class="col">incidents</span></td>
    <td>Incidents déclarés (agents terrain + signalements clients)</td>
    <td>{{ $stats['incidents'] }}</td>
    <td>FK users, FK poste_donnees</td>
  </tr>
  <tr>
    <td><span class="col">maintenances</span></td>
    <td>Travaux de maintenance enregistrés sur les postes</td>
    <td>{{ $stats['maintenances'] }}</td>
    <td>FK users, FK poste_donnees</td>
  </tr>
  <tr>
    <td><span class="col">consumption_logs</span></td>
    <td>Historique de consommation horaire + facturation prépayée</td>
    <td>{{ $stats['consumption_logs'] }}</td>
    <td>FK users (clients)</td>
  </tr>
  <tr>
    <td><span class="col">sessions</span></td>
    <td>Sessions web Laravel (authentification)</td>
    <td>—</td>
    <td>Indépendante</td>
  </tr>
  <tr>
    <td><span class="col">cache / cache_locks</span></td>
    <td>Cache applicatif (traductions Groq, requêtes fréquentes)</td>
    <td>—</td>
    <td>Indépendante</td>
  </tr>
  <tr>
    <td><span class="col">jobs / job_batches / failed_jobs</span></td>
    <td>File de tâches asynchrones Laravel</td>
    <td>—</td>
    <td>Indépendante</td>
  </tr>
  <tr>
    <td><span class="col">password_reset_tokens</span></td>
    <td>Jetons de réinitialisation de mot de passe</td>
    <td>—</td>
    <td>Liée à users (email)</td>
  </tr>
</table>

<div class="page-break"></div>

{{-- ═══════════ PAGE 2 : MCD ═══════════ --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Modèle Conceptuel de Données</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1>2. Modèle Conceptuel de Données (MCD)</h1>

{{-- ── ENTITÉS ── --}}
<div class="mcd-grid">

  {{-- Colonne gauche --}}
  <div class="mcd-col" style="width:48%">

    {{-- USERS --}}
    <div class="entity">
      <div class="entity-name green">UTILISATEUR</div>
      <div class="entity-attr"><span class="attr-icon">🔑</span><span class="attr-name">id</span><span class="attr-type">INT PK</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">name</span><span class="attr-type">VARCHAR</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">email</span><span class="attr-type">VARCHAR unique</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">password</span><span class="attr-type">VARCHAR (bcrypt)</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">role</span><span class="attr-type">admin|agent|client</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">zone</span><span class="attr-type">VARCHAR nullable</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">meter_number</span><span class="attr-type">VARCHAR unique</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">phone</span><span class="attr-type">VARCHAR nullable</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">language</span><span class="attr-type">fr|dioula|baoule...</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">balance</span><span class="attr-type">DECIMAL(12,2)</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">daily_kwh</span><span class="attr-type">DECIMAL(6,2)</span></div>
    </div>

    <br>

    {{-- CONSUMPTION_LOG --}}
    <div class="entity">
      <div class="entity-name teal">CONSUMPTION_LOG</div>
      <div class="entity-attr"><span class="attr-icon">🔑</span><span class="attr-name">id</span><span class="attr-type">INT PK</span></div>
      <div class="entity-attr"><span class="attr-icon">🔗</span><span class="attr-name">user_id</span><span class="attr-type">FK → users</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">log_date</span><span class="attr-type">DATE</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">kwh</span><span class="attr-type">DECIMAL(6,2)</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">amount_fcfa</span><span class="attr-type">DECIMAL(10,2)</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">balance_before</span><span class="attr-type">DECIMAL(12,2)</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">balance_after</span><span class="attr-type">DECIMAL(12,2)</span></div>
    </div>

    <br>

    {{-- MAINTENANCE --}}
    <div class="entity">
      <div class="entity-name orange">MAINTENANCE</div>
      <div class="entity-attr"><span class="attr-icon">🔑</span><span class="attr-name">id</span><span class="attr-type">INT PK</span></div>
      <div class="entity-attr"><span class="attr-icon">🔗</span><span class="attr-name">poste_id</span><span class="attr-type">FK → poste_donnees</span></div>
      <div class="entity-attr"><span class="attr-icon">🔗</span><span class="attr-name">performed_by</span><span class="attr-type">FK → users</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">zone</span><span class="attr-type">VARCHAR</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">performed_at</span><span class="attr-type">DATETIME</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">type</span><span class="attr-type">preventive|corrective|urgence</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">notes</span><span class="attr-type">TEXT nullable</span></div>
    </div>

  </div>{{-- /col gauche --}}

  {{-- Colonne droite --}}
  <div class="mcd-col" style="width:4%;text-align:center;padding-top:40pt">
    {{-- Liens visuels (traits) --}}
    <div style="height:40pt;border-left:1.5pt dashed #94a3b8;margin-left:50%"></div>
    <div style="font-size:7pt;color:#ff6600;font-weight:900">1,N</div>
    <div style="height:30pt;border-left:1.5pt dashed #94a3b8;margin-left:50%"></div>
    <div style="font-size:7pt;color:#ff6600;font-weight:900">1,N</div>
    <div style="height:30pt;border-left:1.5pt dashed #94a3b8;margin-left:50%"></div>
    <div style="font-size:7pt;color:#ff6600;font-weight:900">1,N</div>
  </div>

  <div class="mcd-col" style="width:48%">

    {{-- ALERT --}}
    <div class="entity">
      <div class="entity-name" style="background:#b91c1c">ALERT</div>
      <div class="entity-attr"><span class="attr-icon">🔑</span><span class="attr-name">id</span><span class="attr-type">INT PK</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">zone</span><span class="attr-type">VARCHAR</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">quartier</span><span class="attr-type">VARCHAR nullable</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">poste_id</span><span class="attr-type">VARCHAR nullable</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">cluster</span><span class="attr-type">A|B|C</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">start_at</span><span class="attr-type">DATETIME</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">end_at</span><span class="attr-type">DATETIME nullable</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">probability</span><span class="attr-type">TINYINT (0-100)</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">cause_probable</span><span class="attr-type">VARCHAR</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">reason</span><span class="attr-type">VARCHAR</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">status</span><span class="attr-type">predicted|active|resolved</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">prediction_mode</span><span class="attr-type">3h|5j</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">agents_notified_at</span><span class="attr-type">DATETIME nullable</span></div>
    </div>

    <br>

    {{-- POSTE_DONNEE --}}
    <div class="entity">
      <div class="entity-name blue">POSTE_DONNEE</div>
      <div class="entity-attr"><span class="attr-icon">🔑</span><span class="attr-name">id</span><span class="attr-type">INT PK</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">poste_id</span><span class="attr-type">VARCHAR unique</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">zone</span><span class="attr-type">VARCHAR</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">cluster</span><span class="attr-type">A|B|C</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">charge_mw</span><span class="attr-type">DECIMAL</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">capacite_mw</span><span class="attr-type">DECIMAL</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">temperature_c</span><span class="attr-type">DECIMAL</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">humidite_pct</span><span class="attr-type">DECIMAL</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">niveau_barrage_pct</span><span class="attr-type">DECIMAL</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">age_cable_ans</span><span class="attr-type">DECIMAL</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">nb_incidents_7j</span><span class="attr-type">INT</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">maintenance_recente</span><span class="attr-type">BOOLEAN</span></div>
    </div>

    <br>

    {{-- INCIDENT --}}
    <div class="entity">
      <div class="entity-name" style="background:#7c3aed">INCIDENT</div>
      <div class="entity-attr"><span class="attr-icon">🔑</span><span class="attr-name">id</span><span class="attr-type">INT PK</span></div>
      <div class="entity-attr"><span class="attr-icon">🔗</span><span class="attr-name">poste_id</span><span class="attr-type">FK → poste_donnees</span></div>
      <div class="entity-attr"><span class="attr-icon">🔗</span><span class="attr-name">declared_by</span><span class="attr-type">FK → users</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">zone</span><span class="attr-type">VARCHAR</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">quartier</span><span class="attr-type">VARCHAR nullable</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">declared_at</span><span class="attr-type">DATETIME</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">source_type</span><span class="attr-type">agent|client</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">type</span><span class="attr-type">technique|meteo|vandalisme...</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">severity</span><span class="attr-type">mineur|normal|critique</span></div>
      <div class="entity-attr"><span class="attr-icon"> </span><span class="attr-name">resolved</span><span class="attr-type">BOOLEAN</span></div>
    </div>

  </div>{{-- /col droite --}}
</div>{{-- /mcd-grid --}}

{{-- ── RELATIONS ── --}}
<h2>2.1 Relations entre entités</h2>

<table style="font-size:8.5pt">
  <tr><th>Entité 1</th><th>Cardinalité</th><th>Entité 2</th><th>Clé de jointure</th><th>Description</th></tr>
  <tr>
    <td><strong>UTILISATEUR</strong></td>
    <td style="text-align:center;color:#ff6600;font-weight:900">1,N</td>
    <td><strong>CONSUMPTION_LOG</strong></td>
    <td><span class="col">user_id</span></td>
    <td>Un client génère N logs de consommation (un par jour)</td>
  </tr>
  <tr>
    <td><strong>UTILISATEUR</strong></td>
    <td style="text-align:center;color:#ff6600;font-weight:900">1,N</td>
    <td><strong>INCIDENT</strong></td>
    <td><span class="col">declared_by</span></td>
    <td>Un agent ou client peut déclarer N incidents</td>
  </tr>
  <tr>
    <td><strong>UTILISATEUR</strong></td>
    <td style="text-align:center;color:#ff6600;font-weight:900">1,N</td>
    <td><strong>MAINTENANCE</strong></td>
    <td><span class="col">performed_by</span></td>
    <td>Un agent peut enregistrer N maintenances</td>
  </tr>
  <tr>
    <td><strong>POSTE_DONNEE</strong></td>
    <td style="text-align:center;color:#ff6600;font-weight:900">1,N</td>
    <td><strong>INCIDENT</strong></td>
    <td><span class="col">poste_id</span></td>
    <td>Un poste peut avoir N incidents déclarés</td>
  </tr>
  <tr>
    <td><strong>POSTE_DONNEE</strong></td>
    <td style="text-align:center;color:#ff6600;font-weight:900">1,N</td>
    <td><strong>MAINTENANCE</strong></td>
    <td><span class="col">poste_id</span></td>
    <td>Un poste peut avoir N maintenances effectuées</td>
  </tr>
  <tr>
    <td><strong>POSTE_DONNEE</strong></td>
    <td style="text-align:center;color:#3b82f6;font-weight:900">1,N*</td>
    <td><strong>ALERT</strong></td>
    <td><span class="col">zone</span> (logique)</td>
    <td>Les alertes sont liées aux postes par la zone géographique (*lien logique, pas FK)</td>
  </tr>
</table>

<div class="page-break"></div>

{{-- ═══════════ PAGE 3 : DICTIONNAIRE DES DONNÉES ═══════════ --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Dictionnaire des données</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

<h1>3. Dictionnaire des données</h1>

{{-- TABLE USERS --}}
<h2>3.1 Table <code>users</code></h2>
<div class="box box-green">
  <div class="box-title">Rôle</div>
  Centralise tous les utilisateurs du système : administrateurs CIE, agents terrain et clients abonnés.
  Le rôle détermine l'accès et les fonctionnalités disponibles.
</div>
<table>
  <tr><th>Colonne</th><th>Type</th><th>Contrainte</th><th>Description</th></tr>
  <tr><td><span class="col pk">id</span></td><td><span class="type">INTEGER</span></td><td><span class="req">PK AUTO</span></td><td>Identifiant unique</td></tr>
  <tr><td><span class="col">name</span></td><td><span class="type">VARCHAR(255)</span></td><td><span class="req">NOT NULL</span></td><td>Nom complet</td></tr>
  <tr><td><span class="col">email</span></td><td><span class="type">VARCHAR(255)</span></td><td><span class="req">NOT NULL UNIQUE</span></td><td>Adresse email (identifiant de connexion)</td></tr>
  <tr><td><span class="col">password</span></td><td><span class="type">VARCHAR(255)</span></td><td><span class="req">NOT NULL</span></td><td>Mot de passe haché (bcrypt)</td></tr>
  <tr><td><span class="col">role</span></td><td><span class="type">VARCHAR(20)</span></td><td><span class="req">NOT NULL</span></td><td><code>admin</code> | <code>agent</code> | <code>client</code></td></tr>
  <tr><td><span class="col">zone</span></td><td><span class="type">VARCHAR(100)</span></td><td><span class="opt">nullable</span></td><td>Zone géographique assignée (agent) ou de résidence (client)</td></tr>
  <tr><td><span class="col">meter_number</span></td><td><span class="type">VARCHAR(20)</span></td><td><span class="opt">nullable UNIQUE</span></td><td>Numéro de compteur CIE (clients uniquement)</td></tr>
  <tr><td><span class="col">phone</span></td><td><span class="type">VARCHAR(25)</span></td><td><span class="opt">nullable</span></td><td>Numéro de téléphone</td></tr>
  <tr><td><span class="col">address</span></td><td><span class="type">VARCHAR(255)</span></td><td><span class="opt">nullable</span></td><td>Adresse physique de l'installation</td></tr>
  <tr><td><span class="col">language</span></td><td><span class="type">VARCHAR(10)</span></td><td>DEFAULT <code>'fr'</code></td><td>Langue des notifications : <code>fr</code>, <code>dioula</code>, <code>baoule</code>, <code>bete</code>, <code>nouchi</code></td></tr>
  <tr><td><span class="col">balance</span></td><td><span class="type">DECIMAL(12,2)</span></td><td>DEFAULT <code>0</code></td><td>Solde du compteur prépayé en FCFA</td></tr>
  <tr><td><span class="col">daily_kwh</span></td><td><span class="type">DECIMAL(6,2)</span></td><td><span class="opt">nullable</span></td><td>Consommation journalière individuelle (kWh/jour, généré à l'inscription)</td></tr>
</table>

{{-- TABLE ALERTS --}}
<h2>3.2 Table <code>alerts</code></h2>
<div class="box box-blue">
  <div class="box-title">Rôle</div>
  Stocke toutes les alertes de coupure générées par le modèle IA RandomForest.
  Deux modes : prédiction 3H (fenêtre glissante) et prédiction 5J (horizon long terme).
</div>
<table>
  <tr><th>Colonne</th><th>Type</th><th>Contrainte</th><th>Description</th></tr>
  <tr><td><span class="col pk">id</span></td><td><span class="type">INTEGER</span></td><td><span class="req">PK AUTO</span></td><td>Identifiant unique</td></tr>
  <tr><td><span class="col">zone</span></td><td><span class="type">VARCHAR(100)</span></td><td><span class="req">NOT NULL</span></td><td>Zone géographique concernée (ex: Cocody, Abobo)</td></tr>
  <tr><td><span class="col">quartier</span></td><td><span class="type">VARCHAR(100)</span></td><td><span class="opt">nullable</span></td><td>Quartier précis dans la zone</td></tr>
  <tr><td><span class="col">poste_id</span></td><td><span class="type">VARCHAR(20)</span></td><td><span class="opt">nullable</span></td><td>Identifiant du poste source (ex: PST_0001)</td></tr>
  <tr><td><span class="col">cluster</span></td><td><span class="type">VARCHAR(1)</span></td><td><span class="opt">nullable</span></td><td>Cluster géographique : <code>A</code> (urbain), <code>B</code> (secondaire), <code>C</code> (rural)</td></tr>
  <tr><td><span class="col">start_at</span></td><td><span class="type">DATETIME</span></td><td><span class="req">NOT NULL</span></td><td>Début prévu de la coupure</td></tr>
  <tr><td><span class="col">end_at</span></td><td><span class="type">DATETIME</span></td><td><span class="opt">nullable</span></td><td>Fin prévue de la coupure</td></tr>
  <tr><td><span class="col">probability</span></td><td><span class="type">TINYINT(0-100)</span></td><td><span class="req">NOT NULL</span></td><td>Probabilité de coupure en % (seuil opérateur : 38,5%)</td></tr>
  <tr><td><span class="col">cause_probable</span></td><td><span class="type">VARCHAR(50)</span></td><td><span class="opt">nullable</span></td><td>Cause IA : <code>surcharge_réseau</code>, <code>vieillissement_équipement</code>, <code>intempérie_pluie</code>, <code>défaut_câble_ht</code>, <code>vandalisme_vol_câble</code></td></tr>
  <tr><td><span class="col">reason</span></td><td><span class="type">VARCHAR(255)</span></td><td><span class="opt">nullable</span></td><td>Libellé lisible en français de la cause</td></tr>
  <tr><td><span class="col">status</span></td><td><span class="type">VARCHAR(20)</span></td><td>DEFAULT <code>'predicted'</code></td><td><code>predicted</code> | <code>active</code> | <code>resolved</code></td></tr>
  <tr><td><span class="col">prediction_mode</span></td><td><span class="type">VARCHAR(3)</span></td><td>DEFAULT <code>'5j'</code></td><td><code>3h</code> = fenêtre glissante 3 heures | <code>5j</code> = horizon 5 jours</td></tr>
  <tr><td><span class="col">agents_notified_at</span></td><td><span class="type">DATETIME</span></td><td><span class="opt">nullable</span></td><td>Horodatage de la notification aux agents terrain</td></tr>
</table>

<div class="page-break"></div>

{{-- PAGE 4 : suite dictionnaire --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Dictionnaire des données (suite)</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

{{-- TABLE POSTE_DONNEES --}}
<h2>3.3 Table <code>poste_donnees</code></h2>
<div class="box box-orange">
  <div class="box-title">Rôle</div>
  Source de vérité pour l'état en temps réel des 296 postes électriques ivoiriens.
  Ces données alimentent directement le modèle IA pour les prédictions 3H.
  Mises à jour manuellement par les agents, automatiquement via l'API météo.
</div>
<table>
  <tr><th>Colonne</th><th>Type</th><th>Source</th><th>Description</th></tr>
  <tr><td><span class="col pk">id</span></td><td><span class="type">INTEGER</span></td><td>—</td><td>PK auto-incrémenté</td></tr>
  <tr><td><span class="col">poste_id</span></td><td><span class="type">VARCHAR(20)</span></td><td>CSV</td><td>Code unique du poste (ex: PST_0001) — UNIQUE</td></tr>
  <tr><td><span class="col">zone</span></td><td><span class="type">VARCHAR(100)</span></td><td>CSV</td><td>Zone géographique (30 zones)</td></tr>
  <tr><td><span class="col">quartier</span></td><td><span class="type">VARCHAR(100)</span></td><td>CSV</td><td>Quartier précis (231 quartiers)</td></tr>
  <tr><td><span class="col">cluster</span></td><td><span class="type">VARCHAR(1)</span></td><td>CSV</td><td>A / B / C — classification infrastructure</td></tr>
  <tr><td><span class="col">latitude / longitude</span></td><td><span class="type">DECIMAL(9,6)</span></td><td>CSV</td><td>Coordonnées GPS du poste</td></tr>
  <tr><td><span class="col">charge_mw</span></td><td><span class="type">DECIMAL(6,2)</span></td><td>Agent/SCADA</td><td>Charge actuelle en mégawatts</td></tr>
  <tr><td><span class="col">capacite_mw</span></td><td><span class="type">DECIMAL(6,2)</span></td><td>Config</td><td>Capacité nominale installée</td></tr>
  <tr><td><span class="col">temperature_c</span></td><td><span class="type">DECIMAL(5,2)</span></td><td>API météo</td><td>Température ambiante en °C</td></tr>
  <tr><td><span class="col">temp_max_24h</span></td><td><span class="type">DECIMAL(5,2)</span></td><td>API météo</td><td>Température maximale des 24 dernières heures</td></tr>
  <tr><td><span class="col">humidite_pct</span></td><td><span class="type">DECIMAL(5,2)</span></td><td>API météo</td><td>Humidité relative en %</td></tr>
  <tr><td><span class="col">pluviometrie_norm</span></td><td><span class="type">DECIMAL(5,4)</span></td><td>API météo</td><td>Pluie normalisée 0→1 (0.5 = 25 mm)</td></tr>
  <tr><td><span class="col">niveau_barrage_pct</span></td><td><span class="type">DECIMAL(5,2)</span></td><td>Manuel</td><td>Niveau barrage hydraulique Kossou/Ayamé (0-100%)</td></tr>
  <tr><td><span class="col">age_cable_ans</span></td><td><span class="type">DECIMAL(5,2)</span></td><td>Inventaire</td><td>Âge moyen des câbles du poste en années</td></tr>
  <tr><td><span class="col">maintenance_recente</span></td><td><span class="type">BOOLEAN</span></td><td>Auto</td><td>1 si maintenance effectuée dans les 7 derniers jours</td></tr>
  <tr><td><span class="col">derniere_maintenance_at</span></td><td><span class="type">DATETIME</span></td><td>Auto</td><td>Date de la dernière maintenance</td></tr>
  <tr><td><span class="col">nb_incidents_7j</span></td><td><span class="type">INTEGER</span></td><td>Auto calculé</td><td>Nombre d'incidents signalés sur 7 jours (recalculé à chaque incident)</td></tr>
  <tr><td><span class="col">incidents_moy_3j</span></td><td><span class="type">DECIMAL(5,2)</span></td><td>Auto calculé</td><td>Moyenne d'incidents sur 3 jours</td></tr>
  <tr><td><span class="col">source_meteo</span></td><td><span class="type">VARCHAR(10)</span></td><td>Système</td><td><code>api</code> (OpenWeatherMap) | <code>estime</code> (saisonnier) | <code>manuel</code></td></tr>
  <tr><td><span class="col">donnees_updated_at</span></td><td><span class="type">DATETIME</span></td><td>Auto</td><td>Dernière mise à jour globale du poste</td></tr>
</table>

{{-- TABLE INCIDENTS --}}
<h2>3.4 Table <code>incidents</code></h2>
<table>
  <tr><th>Colonne</th><th>Type</th><th>Contrainte</th><th>Description</th></tr>
  <tr><td><span class="col pk">id</span></td><td><span class="type">INTEGER</span></td><td><span class="req">PK AUTO</span></td><td>Identifiant</td></tr>
  <tr><td><span class="col fk">poste_id</span></td><td><span class="type">VARCHAR(20)</span></td><td><span class="opt">nullable</span></td><td>Poste concerné (FK optionnelle → poste_donnees)</td></tr>
  <tr><td><span class="col fk">declared_by</span></td><td><span class="type">INTEGER</span></td><td><span class="opt">FK → users</span></td><td>Utilisateur ayant déclaré (agent ou client)</td></tr>
  <tr><td><span class="col">zone</span></td><td><span class="type">VARCHAR(100)</span></td><td><span class="req">NOT NULL</span></td><td>Zone géographique de l'incident</td></tr>
  <tr><td><span class="col">quartier</span></td><td><span class="type">VARCHAR(100)</span></td><td><span class="opt">nullable</span></td><td>Quartier précis (renseigné par les clients)</td></tr>
  <tr><td><span class="col">source_type</span></td><td><span class="type">VARCHAR(10)</span></td><td>DEFAULT <code>'agent'</code></td><td><code>agent</code> = terrain | <code>client</code> = signalement abonné</td></tr>
  <tr><td><span class="col">declared_at</span></td><td><span class="type">DATETIME</span></td><td><span class="req">NOT NULL</span></td><td>Date/heure de la déclaration</td></tr>
  <tr><td><span class="col">type</span></td><td><span class="type">VARCHAR(20)</span></td><td>DEFAULT <code>'technique'</code></td><td><code>technique</code> | <code>vandalisme</code> | <code>meteo</code> | <code>autre</code></td></tr>
  <tr><td><span class="col">severity</span></td><td><span class="type">VARCHAR(10)</span></td><td>DEFAULT <code>'normal'</code></td><td><code>mineur</code> | <code>normal</code> | <code>critique</code></td></tr>
  <tr><td><span class="col">description</span></td><td><span class="type">TEXT</span></td><td><span class="opt">nullable</span></td><td>Description libre de l'incident</td></tr>
  <tr><td><span class="col">resolved</span></td><td><span class="type">BOOLEAN</span></td><td>DEFAULT <code>false</code></td><td>Incident résolu par l'admin</td></tr>
  <tr><td><span class="col">resolved_at</span></td><td><span class="type">DATETIME</span></td><td><span class="opt">nullable</span></td><td>Date de résolution</td></tr>
</table>

<div class="page-break"></div>

{{-- PAGE 5 : fin dictionnaire + index --}}
<div class="doc-header">
  <div class="doc-header-left"><strong>INNOVAN-CI</strong> — Dictionnaire des données (fin)</div>
  <div class="doc-header-right">Juin 2026</div>
</div>

{{-- TABLE MAINTENANCES --}}
<h2>3.5 Table <code>maintenances</code></h2>
<table>
  <tr><th>Colonne</th><th>Type</th><th>Contrainte</th><th>Description</th></tr>
  <tr><td><span class="col pk">id</span></td><td><span class="type">INTEGER</span></td><td><span class="req">PK AUTO</span></td><td>Identifiant</td></tr>
  <tr><td><span class="col fk">poste_id</span></td><td><span class="type">VARCHAR(20)</span></td><td><span class="req">NOT NULL</span></td><td>Poste sur lequel la maintenance a été effectuée</td></tr>
  <tr><td><span class="col fk">performed_by</span></td><td><span class="type">INTEGER</span></td><td><span class="opt">FK → users</span></td><td>Agent ayant effectué la maintenance</td></tr>
  <tr><td><span class="col">zone</span></td><td><span class="type">VARCHAR(100)</span></td><td><span class="req">NOT NULL</span></td><td>Zone géographique</td></tr>
  <tr><td><span class="col">performed_at</span></td><td><span class="type">DATETIME</span></td><td><span class="req">NOT NULL</span></td><td>Date et heure de la maintenance</td></tr>
  <tr><td><span class="col">type</span></td><td><span class="type">VARCHAR(20)</span></td><td><span class="req">NOT NULL</span></td><td><code>preventive</code> | <code>corrective</code> | <code>urgence</code></td></tr>
  <tr><td><span class="col">notes</span></td><td><span class="type">TEXT</span></td><td><span class="opt">nullable</span></td><td>Notes libres sur les travaux effectués</td></tr>
</table>

{{-- TABLE CONSUMPTION_LOGS --}}
<h2>3.6 Table <code>consumption_logs</code></h2>
<div class="box box-green">
  <div class="box-title">Rôle</div>
  Historique de la consommation individuelle de chaque client abonné.
  Un enregistrement par client par jour. La simulation horaire décremente le solde (<code>balance</code>)
  et crée/met à jour l'entrée du jour.
</div>
<table>
  <tr><th>Colonne</th><th>Type</th><th>Contrainte</th><th>Description</th></tr>
  <tr><td><span class="col pk">id</span></td><td><span class="type">INTEGER</span></td><td><span class="req">PK AUTO</span></td><td>Identifiant</td></tr>
  <tr><td><span class="col fk">user_id</span></td><td><span class="type">INTEGER</span></td><td><span class="req">FK → users CASCADE</span></td><td>Client concerné</td></tr>
  <tr><td><span class="col">log_date</span></td><td><span class="type">DATE</span></td><td><span class="req">NOT NULL</span></td><td>Date du log (contrainte UNIQUE avec user_id)</td></tr>
  <tr><td><span class="col">kwh</span></td><td><span class="type">DECIMAL(6,2)</span></td><td><span class="req">NOT NULL</span></td><td>Kilowattheures consommés ce jour</td></tr>
  <tr><td><span class="col">amount_fcfa</span></td><td><span class="type">DECIMAL(10,2)</span></td><td><span class="req">NOT NULL</span></td><td>Montant déduit en FCFA (kWh × 35 FCFA)</td></tr>
  <tr><td><span class="col">balance_before</span></td><td><span class="type">DECIMAL(12,2)</span></td><td><span class="req">NOT NULL</span></td><td>Solde avant déduction</td></tr>
  <tr><td><span class="col">balance_after</span></td><td><span class="type">DECIMAL(12,2)</span></td><td><span class="req">NOT NULL</span></td><td>Solde après déduction</td></tr>
</table>
<p style="font-size:8.5pt;color:#555">
  <strong>Index unique :</strong> <code>(user_id, log_date)</code> — garantit un seul log par client par journée.<br>
  <strong>Tarif :</strong> 35 FCFA/kWh (Côte d'Ivoire, tarif résidentiel de base).<br>
  <strong>Simulation :</strong> <code>php artisan simulate:consumption</code> — tourne toutes les heures via le scheduler Laravel.
</p>

<h1>4. Index et contraintes notables</h1>

<table>
  <tr><th>Table</th><th>Index / Contrainte</th><th>Colonnes</th><th>Objectif</th></tr>
  <tr><td><code>users</code></td><td>UNIQUE</td><td><code>email</code></td><td>Identifiant de connexion unique</td></tr>
  <tr><td><code>users</code></td><td>UNIQUE</td><td><code>meter_number</code></td><td>Numéro compteur unique par client</td></tr>
  <tr><td><code>poste_donnees</code></td><td>UNIQUE</td><td><code>poste_id</code></td><td>Code poste unique</td></tr>
  <tr><td><code>consumption_logs</code></td><td>UNIQUE</td><td><code>(user_id, log_date)</code></td><td>Un log max par client par jour</td></tr>
  <tr><td><code>incidents</code></td><td>INDEX</td><td><code>(poste_id, declared_at)</code></td><td>Lookups rapides des incidents récents par poste</td></tr>
  <tr><td><code>maintenances</code></td><td>INDEX</td><td><code>(poste_id, performed_at)</code></td><td>Historique des maintenances par poste</td></tr>
  <tr><td><code>alerts</code></td><td>INDEX recommandé</td><td><code>(zone, start_at)</code></td><td>Filtrage rapide des alertes par zone et date</td></tr>
</table>

<br>
<hr>
<div style="text-align:center;font-size:8pt;color:#999;margin-top:10pt">
  Documentation générée automatiquement — INNOVAN-CI v8 — Juin 2026<br>
  Base de données SQLite 3.39.2 — Laravel 12 — PHP 8.2
</div>

</body>
</html>
