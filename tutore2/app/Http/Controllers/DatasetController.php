<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\PosteDonnee;
use App\Models\Incident;
use App\Models\Maintenance;
use App\Models\Alert;
use App\Models\User;

class DatasetController extends Controller
{
    private function guard(): void
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'agent'])) {
            abort(403);
        }
    }

    private function guardAdmin(): void
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Accès réservé à l\'administrateur INNOVAN-CI.');
        }
    }

    /* ─────────────────────────────────────────────────────────
     | Vue principale — état du dataset centralisé
    ───────────────────────────────────────────────────────── */
    public function index()
    {
        $this->guard();
        $user    = Auth::user();
        $isAgent = $user->role === 'agent' && $user->zone;

        $postes = PosteDonnee::when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->orderBy('zone')->orderBy('poste_id')->get();

        // Stats globales
        $stats = [
            'total_postes'      => $postes->count(),
            'meteo_ok'          => $postes->where('source_meteo', 'api')->count(),
            'avec_incidents'    => $postes->where('nb_incidents_7j', '>', 0)->count(),
            'maintenance_faite' => $postes->where('maintenance_recente', true)->count(),
            'derniere_maj'      => $postes->max('donnees_updated_at'),
        ];

        $zones     = $postes->pluck('zone')->unique()->sort()->values();
        $clusters  = ['A' => 0, 'B' => 0, 'C' => 0];
        foreach ($postes as $p) {
            if (isset($clusters[$p->cluster])) $clusters[$p->cluster]++;
        }

        return view('admin.dataset', compact('postes', 'stats', 'zones', 'clusters'));
    }

    /* ─────────────────────────────────────────────────────────
     | Rafraîchir la météo — OpenWeatherMap (par zone)
    ───────────────────────────────────────────────────────── */
    public function refreshWeather()
    {
        $this->guardAdmin();
        $apiKey = env('OPENWEATHER_API_KEY');

        if (!$apiKey) {
            // Sans clé API : estimation basée sur la saison actuelle
            $this->estimateWeather();
            return back()->with('success', 'Météo estimée mise à jour (pas de clé OpenWeatherMap configurée).');
        }

        // Grouper par zone et prendre un représentant (latitude/longitude)
        $zones = PosteDonnee::select('zone', DB::raw('avg(latitude) as lat'), DB::raw('avg(longitude) as lon'))
            ->whereNotNull('latitude')
            ->groupBy('zone')->get();

        $updated = 0;
        foreach ($zones as $z) {
            try {
                $resp = Http::timeout(5)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'lat'   => $z->lat,
                    'lon'   => $z->lon,
                    'appid' => $apiKey,
                    'units' => 'metric',
                ]);

                if ($resp->successful()) {
                    $w    = $resp->json();
                    $rain = ($w['rain']['1h'] ?? 0);
                    $pluv = min(1.0, round($rain / 50.0, 4));

                    PosteDonnee::where('zone', $z->zone)->update([
                        'temperature_c'     => round($w['main']['temp'] ?? 27, 2),
                        'humidite_pct'      => $w['main']['humidity'] ?? 74,
                        'pluviometrie_norm' => $pluv,
                        'temp_max_24h'      => round($w['main']['temp_max'] ?? 29, 2),
                        'source_meteo'      => 'api',
                        'meteo_updated_at'  => now(),
                        'donnees_updated_at'=> now(),
                    ]);
                    $updated++;
                }
            } catch (\Exception $e) {
                // Continuer avec les autres zones
            }
        }

        return back()->with('success', "Météo API rafraîchie pour $updated zone(s) sur {$zones->count()}.");
    }

    /* ─────────────────────────────────────────────────────────
     | Estimation météo sans API (basée sur saison CI)
    ───────────────────────────────────────────────────────── */
    private function estimateWeather(): void
    {
        $month = now()->month;

        // Saisons CI : Harmattan (sèche) Dec-Mar, Pluies Avr-Jul, Petite sèche Juil-Sep, Pluies Sep-Nov
        $isRainy   = in_array($month, [4, 5, 6, 9, 10, 11]);
        $isDry     = in_array($month, [12, 1, 2, 3, 7, 8]);

        $clusterMeteo = [
            'A' => ['temp' => 26.5 + ($isDry ? 1 : -0.5), 'hum' => $isRainy ? 82 : 72, 'rain' => $isRainy ? 0.35 : 0.08],
            'B' => ['temp' => 28.0 + ($isDry ? 1.5 : 0),  'hum' => $isRainy ? 78 : 68, 'rain' => $isRainy ? 0.30 : 0.06],
            'C' => ['temp' => 30.0 + ($isDry ? 2 : 0),    'hum' => $isRainy ? 72 : 60, 'rain' => $isRainy ? 0.25 : 0.04],
        ];

        foreach ($clusterMeteo as $cluster => $m) {
            PosteDonnee::where('cluster', $cluster)->update([
                'temperature_c'     => $m['temp'],
                'humidite_pct'      => $m['hum'],
                'pluviometrie_norm' => $m['rain'],
                'temp_max_24h'      => $m['temp'] + 2.5,
                'source_meteo'      => 'estime',
                'meteo_updated_at'  => now(),
                'donnees_updated_at'=> now(),
            ]);
        }
    }

    /* ─────────────────────────────────────────────────────────
     | Mettre à jour les données d'un poste (réseau / barrage)
    ───────────────────────────────────────────────────────── */
    public function updatePoste(Request $request, string $posteId)
    {
        $this->guard();

        $poste = PosteDonnee::where('poste_id', $posteId)->firstOrFail();

        // Un agent ne peut modifier que sa propre zone
        $user = Auth::user();
        if ($user->role === 'agent' && $user->zone && $poste->zone !== $user->zone) {
            abort(403, 'Vous ne pouvez modifier que les postes de votre zone.');
        }

        $data = $request->validate([
            'charge_mw'          => ['nullable', 'numeric', 'min:0', 'max:500'],
            'niveau_barrage_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'age_cable_ans'      => ['nullable', 'numeric', 'min:0', 'max:80'],
        ]);

        $update = array_filter($data, fn($v) => $v !== null);
        if (isset($update['charge_mw']))          $update['source_reseau']  = 'manuel';
        if (isset($update['niveau_barrage_pct'])) $update['source_barrage'] = 'manuel';
        $update['donnees_updated_at'] = now();

        $poste->update($update);
        $poste->verifierMaintenanceRecente();

        return back()->with('success', "Poste {$posteId} mis à jour.");
    }

    /* ─────────────────────────────────────────────────────────
     | Déclarer un incident sur un poste
    ───────────────────────────────────────────────────────── */
    public function declareIncident(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'poste_id'    => ['required', 'string'],
            'type'        => ['required', 'in:technique,vandalisme,meteo,autre'],
            'severity'    => ['required', 'in:mineur,normal,critique'],
            'description' => ['nullable', 'string', 'max:300'],
        ]);

        $poste = PosteDonnee::where('poste_id', $data['poste_id'])->firstOrFail();

        $user = Auth::user();
        if ($user->role === 'agent' && $user->zone && $poste->zone !== $user->zone) {
            abort(403, 'Vous ne pouvez déclarer des incidents que sur les postes de votre zone.');
        }

        Incident::create([
            'poste_id'    => $data['poste_id'],
            'zone'        => $poste->zone,
            'declared_by' => Auth::id(),
            'declared_at' => now(),
            'source_type' => 'agent',
            'type'        => $data['type'],
            'severity'    => $data['severity'],
            'description' => $data['description'] ?? null,
        ]);

        // Recalculer les compteurs d'incidents pour ce poste
        $poste->recalculerIncidents();
        $poste->update(['donnees_updated_at' => now()]);

        return back()->with('success', "Incident déclaré sur {$data['poste_id']}. Compteurs mis à jour.");
    }

    /* ─────────────────────────────────────────────────────────
     | Déclarer une maintenance effectuée sur un poste
    ───────────────────────────────────────────────────────── */
    public function declareMaintenance(Request $request)
    {
        $this->guard();

        $data = $request->validate([
            'poste_id' => ['required', 'string'],
            'type'     => ['required', 'in:preventive,corrective,urgence'],
            'notes'    => ['nullable', 'string', 'max:300'],
        ]);

        $poste = PosteDonnee::where('poste_id', $data['poste_id'])->firstOrFail();

        $user = Auth::user();
        if ($user->role === 'agent' && $user->zone && $poste->zone !== $user->zone) {
            abort(403, 'Vous ne pouvez déclarer des maintenances que sur les postes de votre zone.');
        }

        Maintenance::create([
            'poste_id'     => $data['poste_id'],
            'zone'         => $poste->zone,
            'performed_by' => Auth::id(),
            'performed_at' => now(),
            'type'         => $data['type'],
            'notes'        => $data['notes'] ?? null,
        ]);

        $poste->update([
            'maintenance_recente'     => true,
            'derniere_maintenance_at' => now(),
            'donnees_updated_at'      => now(),
        ]);

        return back()->with('success', "Maintenance enregistrée sur {$data['poste_id']}. Le poste est marqué 'maintenance récente' pendant 7 jours.");
    }

    /* ─────────────────────────────────────────────────────────
     | Mettre à jour le barrage globalement (admin)
    ───────────────────────────────────────────────────────── */
    public function updateBarrage(Request $request)
    {
        $this->guardAdmin();
        $request->validate(['niveau_barrage_pct' => ['required', 'numeric', 'min:0', 'max:100']]);

        $niveau = (float) $request->niveau_barrage_pct;
        PosteDonnee::query()->update([
            'niveau_barrage_pct' => $niveau,
            'source_barrage'     => 'manuel',
            'donnees_updated_at' => now(),
        ]);

        return back()->with('success', "Niveau du barrage mis à jour à {$niveau}% pour tous les postes.");
    }

    /* ─────────────────────────────────────────────────────────
     | Construire le dataset et lancer la prédiction IA
    ───────────────────────────────────────────────────────── */
    public function buildAndPredict(Request $request)
    {
        $this->guardAdmin();
        set_time_limit(300); // Autoriser 5 minutes pour le traitement complet

        // 0. Vérifier rapidement si l'API Flask est disponible (timeout court)
        $apiUrl    = env('INNOVAN_API_URL', 'http://127.0.0.1:5001');
        $apiOnline = false;
        try {
            $health = Http::timeout(4)->get("{$apiUrl}/health");
            $apiOnline = $health->successful() && ($health->json()['models_loaded'] ?? false);
        } catch (\Exception) {
            // API indisponible → on ira directement en simulation
        }

        // 1. Vérifier la maintenance récente (auto-reset si >7j)
        PosteDonnee::where('maintenance_recente', true)->each(fn($p) => $p->verifierMaintenanceRecente());

        // 2. Construire les rows depuis poste_donnees
        $timestamp  = now()->addDay()->format('Y-m-d') . ' 14:00';
        $postes     = PosteDonnee::orderBy('poste_id')->get();
        $rows       = $postes->map(fn($p) => [
            'poste_id'           => $p->poste_id,
            'zone'               => $p->zone,
            'quartier'           => $p->quartier ?? '',
            'cluster'            => $p->cluster ?? 'A',
            'timestamp'          => $timestamp,
            'charge_mw'          => $p->charge_mw,
            'capacite_mw'        => $p->capacite_mw,
            'temperature_c'      => $p->temperature_c,
            'humidite_pct'       => $p->humidite_pct,
            'pluviometrie_norm'  => $p->pluviometrie_norm,
            'niveau_barrage_pct' => $p->niveau_barrage_pct,
            'age_cable_ans'      => $p->age_cable_ans,
            'maintenance_recente'=> $p->maintenance_recente ? 1 : 0,
            'nb_incidents_7j'    => $p->nb_incidents_7j,
            'incidents_moy_3j'   => $p->incidents_moy_3j,
        ])->values()->toArray();

        // 3. Envoyer à l'API Flask (seulement si elle est disponible)
        $predictions = [];
        $source      = 'simulation';

        if ($apiOnline) try {
            $response = Http::timeout(90)->post("{$apiUrl}/predict-csv", ['rows' => $rows]);
            if ($response->successful()) {
                $data = $response->json();
                foreach ($data['predictions'] ?? [] as $p) {
                    $predictions[] = [
                        'zone'           => $p['zone'],
                        'quartier'       => $p['quartier']       ?? null,
                        'poste_id'       => $p['poste_id']       ?? null,
                        'cluster'        => $p['cluster']        ?? null,
                        'start_at'       => \Carbon\Carbon::parse($p['start_at']),
                        'end_at'         => \Carbon\Carbon::parse($p['end_at']),
                        'reason'         => $p['reason'],
                        'cause_probable' => $p['cause_probable'] ?? null,
                        'probability'    => (int) $p['probability'],
                    ];
                }
                $source = 'innovan_ia_dataset';
            }
        } catch (\Exception $e) {
            // Fallback simulation
        }

        // Fallback simulation si API indisponible
        if (empty($predictions)) {
            $zones   = $postes->pluck('zone')->unique()->shuffle()->take(5)->values();
            $reasons = ['Surcharge du réseau','Vieillissement des équipements','Intempérie / Fortes pluies'];
            foreach ($zones as $zone) {
                $poste   = $postes->where('zone', $zone)->first();
                $startAt = now()->addDay()->setHour(14)->setMinute(0)->setSecond(0);
                $predictions[] = [
                    'zone'           => $zone,
                    'quartier'       => $poste?->quartier,
                    'poste_id'       => $poste?->poste_id,
                    'cluster'        => $poste?->cluster,
                    'start_at'       => $startAt,
                    'end_at'         => $startAt->copy()->addHours(4),
                    'reason'         => $reasons[array_rand($reasons)],
                    'cause_probable' => null,
                    'probability'    => rand(55, 85),
                ];
            }
            $source = 'simulation';
        }

        // 4. Sauvegarder les alertes dans une transaction
        $totalClients  = 0;
        $createdAlerts = [];

        \Illuminate\Support\Facades\DB::transaction(function () use ($predictions, &$createdAlerts, &$totalClients) {
            Alert::futures()->delete();

            foreach ($predictions as $p) {
                $alert = Alert::create([
                    'zone'           => $p['zone'],
                    'quartier'       => $p['quartier']       ?? null,
                    'poste_id'       => $p['poste_id']       ?? null,
                    'cluster'        => $p['cluster']        ?? null,
                    'start_at'       => $p['start_at'],
                    'end_at'         => $p['end_at'],
                    'reason'         => $p['reason'],
                    'cause_probable' => $p['cause_probable'] ?? null,
                    'probability'    => $p['probability'],
                    'status'         => 'predicted',
                ]);
                $createdAlerts[] = $alert;
                $totalClients   += User::where('zone', $p['zone'])->where('role', 'client')->count();
            }
        });

        $summary = [
            'alerts_created'   => count($createdAlerts),
            'zones_affected'   => collect($createdAlerts)->pluck('zone')->unique()->count(),
            'clients_notified' => $totalClients,
            'ran_at'           => now()->format('d/m/Y à H:i'),
            'dataset_name'     => "Dataset centralisé ({$postes->count()} postes)",
            'source'           => $source,
        ];

        session(['last_prediction' => $summary]);

        return redirect()->route('admin.prediction')
            ->with('prediction_done', true)
            ->with('summary', $summary)
            ->with('success', "Prediction IA terminee : {$summary['alerts_created']} alertes generees sur {$summary['zones_affected']} zones. {$summary['clients_notified']} client(s) notifie(s) automatiquement.");
    }
}
