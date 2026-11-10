<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\Alert;
use App\Models\User;
use App\Models\PosteDonnee;

class AdminController extends Controller
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

    public function index()
    {
        $this->guard();

        $user      = Auth::user();
        $isAgent   = $user->role === 'agent' && $user->zone;
        $zoneScope = fn($q) => $isAgent ? $q->where('zone', $user->zone) : $q;

        // ══════════════════════════════════════════════
        // PRÉDICTION 3H — Source de vérité du dashboard
        // Tournent automatiquement toutes les heures via le scheduler
        // ══════════════════════════════════════════════

        // ── Calcul des fenêtres temporelles ──────────────────────────
        $slotHour       = (int)(floor(now()->hour / 3) * 3);
        $current3hStart = now()->copy()->setTime($slotHour, 0, 0, 0);
        $current3hEnd   = $current3hStart->copy()->addHours(3);
        $nextSlotStart  = $current3hEnd->copy();          // slot suivant = fin du slot courant
        $nextSlotEnd    = $nextSlotStart->copy()->addHours(3);
        $nextSlotHour   = (int) $nextSlotStart->format('H');
        $today          = now()->startOfDay();
        $todayEnd       = $today->copy()->endOfDay();

        // ── Alertes slot ACTUEL (en cours — générées au run précédent) ──
        $alerts3h = Alert::where('prediction_mode', '3h')
            ->where('start_at', '>=', $current3hStart)
            ->where('start_at', '<',  $current3hEnd)
            ->forZone($isAgent ? $user->zone : null)
            ->orderByDesc('probability')
            ->get();

        // ── Alertes slot SUIVANT (dernière prédiction 3H) ────────────
        // C'est ici que predict3h écrit ses résultats → toujours frais
        $alertsNextSlot = Alert::where('prediction_mode', '3h')
            ->where('start_at', '>=', $nextSlotStart)
            ->where('start_at', '<',  $nextSlotEnd)
            ->forZone($isAgent ? $user->zone : null)
            ->orderByDesc('probability')
            ->get();

        // ── KPIs basés sur le slot SUIVANT (ce qui est prédit) ───────
        $clientCount    = User::where('role', 'client')->count();
        $highRiskCount  = $alertsNextSlot->where('probability', '>=', 80)->count();
        $zonesAffected  = $alertsNextSlot->pluck('zone')->unique()->count();
        $avgProbability = $alertsNextSlot->count()
            ? (int) round($alertsNextSlot->avg('probability'))
            : 0;
        $totalAlertes3h = $alertsNextSlot->count();

        // ── Alertes aujourd'hui (historique — pour causes/clusters) ──
        $alertsAujourdhui = Alert::where('prediction_mode', '3h')
            ->whereBetween('start_at', [$today, $todayEnd])
            ->forZone($isAgent ? $user->zone : null)
            ->orderBy('start_at')
            ->get();

        // ── Graphique GLISSANT : 8 slots à partir du slot courant ─────
        // slot 0 = en cours, slot 1 = prédit (slot suivant), slots 2-7 = horizon
        $slots3hData = [];
        for ($s = 0; $s < 8; $s++) {
            $sStart = $current3hStart->copy()->addHours($s * 3);
            $sEnd   = $sStart->copy()->addHours(3);
            $cnt    = Alert::where('prediction_mode', '3h')
                ->where('start_at', '>=', $sStart)
                ->where('start_at', '<',  $sEnd)
                ->forZone($isAgent ? $user->zone : null)->count();
            $avgP   = (int) round(Alert::where('prediction_mode', '3h')
                ->where('start_at', '>=', $sStart)
                ->where('start_at', '<',  $sEnd)
                ->forZone($isAgent ? $user->zone : null)->avg('probability') ?? 0);
            $slots3hData[] = [
                'label'      => $sStart->format('H:i') . ($sStart->isToday() ? '' : ' +1j'),
                'count'      => $cnt,
                'avg_prob'   => $avgP,
                'is_current' => ($s === 0),
                'is_next'    => ($s === 1),
            ];
        }

        // ── Classement zones — slot suivant (prédit) ─────────────────
        $statsByZone = Alert::select('zone', DB::raw('count(*) as cuts'), DB::raw('avg(probability) as avg_prob'))
            ->where('prediction_mode', '3h')
            ->where('start_at', '>=', $nextSlotStart)
            ->where('start_at', '<',  $nextSlotEnd)
            ->forZone($isAgent ? $user->zone : null)
            ->groupBy('zone')->orderByDesc('cuts')->get();

        // ── Distribution des causes — fenêtre 24h glissante ──────────
        $causeStats = Alert::select('cause_probable', DB::raw('count(*) as total'))
            ->whereNotNull('cause_probable')
            ->where('cause_probable', '!=', 'aucune')
            ->where('prediction_mode', '3h')
            ->where('start_at', '>=', $current3hStart)
            ->where('start_at', '<',  $current3hStart->copy()->addHours(24))
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->groupBy('cause_probable')->orderByDesc('total')->get();

        // ── Stats par cluster — fenêtre 24h glissante ────────────────
        $clusterStats = Alert::select('cluster', DB::raw('count(*) as total'), DB::raw('round(avg(probability)) as avg_prob'))
            ->whereNotNull('cluster')
            ->where('prediction_mode', '3h')
            ->where('start_at', '>=', $current3hStart)
            ->where('start_at', '<',  $current3hStart->copy()->addHours(24))
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->groupBy('cluster')->orderBy('cluster')->get();

        // ── Signalements clients non résolus ──────────────────────────
        $clientSignalements = \App\Models\Incident::where('source_type', 'client')
            ->where('resolved', false)->count();

        $lastPrediction = session('last_prediction');
        $dernierePred5j = Alert::where('prediction_mode', '5j')
            ->where('start_at', '>=', now())
            ->orderByDesc('created_at')
            ->limit(1)->value('created_at');

        $weeklyLabels = array_column($slots3hData, 'label');
        $weeklyTotals = array_column($slots3hData, 'count');
        $weeklyProbs  = array_column($slots3hData, 'avg_prob');
        $avgProb3h    = $alerts3h->count() ? (int) round($alerts3h->avg('probability')) : 0;

        return view('admin.dashboard', compact(
            'alerts3h', 'alertsAujourdhui', 'alertsNextSlot',
            'clientCount', 'statsByZone',
            'lastPrediction', 'dernierePred5j',
            'highRiskCount', 'zonesAffected', 'avgProbability', 'avgProb3h',
            'weeklyLabels', 'weeklyTotals', 'weeklyProbs',
            'causeStats', 'clusterStats', 'slots3hData',
            'current3hStart', 'current3hEnd', 'slotHour',
            'nextSlotStart', 'nextSlotEnd', 'nextSlotHour',
            'clientSignalements', 'totalAlertes3h'
        ));
    }

    public function alerts()
    {
        $this->guard();
        $user    = Auth::user();
        $isAgent = $user->role === 'agent' && $user->zone;

        // Alertes 3H automatiques — en cours ou à venir
        $alerts3h = Alert::where('prediction_mode', '3h')
            ->futures()
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->orderBy('start_at')
            ->get();

        // Prédictions manuelles 5J — futures uniquement
        $alerts5j = Alert::where('prediction_mode', '5j')
            ->where('start_at', '>=', now())
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->orderBy('start_at')
            ->get();

        return view('admin.alerts', compact('alerts3h', 'alerts5j'));
    }

    /* ─────────────────────────────────────────────────────────
     | Notifier les agents d'une zone pour une alerte donnée
    ───────────────────────────────────────────────────────── */
    public function notifyAgents(int $alertId)
    {
        $this->guard();

        $alert  = Alert::findOrFail($alertId);
        $agents = User::whereIn('role', ['admin', 'agent'])
            ->where(fn($q) => $q->where('zone', $alert->zone)->orWhereNull('zone'))
            ->get(['id', 'name', 'email', 'phone', 'zone', 'role']);

        $alert->update(['agents_notified_at' => now()]);

        return response()->json([
            'ok'      => true,
            'zone'    => $alert->zone,
            'agents'  => $agents->map(fn($a) => [
                'name'  => $a->name,
                'phone' => $a->phone ?? 'N/A',
                'email' => $a->email,
                'role'  => $a->role === 'admin' ? 'Admin général' : 'Agent terrain',
            ]),
            'notified_at' => now()->format('d/m/Y à H:i'),
        ]);
    }

    public function zones()
    {
        $this->guard();
        $user    = Auth::user();
        $isAgent = $user->role === 'agent' && $user->zone;

        $zones = Alert::select(
                'zone',
                DB::raw('count(*) as total_alertes'),
                DB::raw('round(avg(probability)) as avg_prob'),
                DB::raw('sum(case when probability >= 80 then 1 else 0 end) as urgentes'),
                DB::raw('max(cluster) as cluster')
            )
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->groupBy('zone')
            ->orderByDesc('total_alertes')
            ->get()
            ->map(function ($z) {
                $z->nb_clients = User::where('zone', $z->zone)->where('role', 'client')->count();
                return $z;
            });

        return view('admin.zones', compact('zones'));
    }

    public function coupures()
    {
        $this->guard();
        $user    = Auth::user();
        $isAgent = $user->role === 'agent' && $user->zone;

        // ── 1. Coupures 3H en cours (slot actif maintenant) ──────────
        $enCours = Alert::where('prediction_mode', '3h')
            ->where('start_at', '<=', now())
            ->where(fn($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()))
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->orderByDesc('probability')->get();

        // ── 2. Prochains slots 3H — fenêtre 24h (évite le bug du wrap minuit) ──
        $slotHour = (int)(floor(now()->hour / 3) * 3);
        $avenir3h = Alert::where('prediction_mode', '3h')
            ->where('start_at', '>', now())
            ->where('start_at', '<=', now()->addHours(24))
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->orderBy('start_at')->get();

        // ── 3. Prédictions manuelles futures (5J — déclenchées manuellement) ──
        $avenir5j = Alert::where('prediction_mode', '5j')
            ->where('start_at', '>', now())
            ->where('start_at', '<=', now()->addDays(2))
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->orderBy('start_at')->get();

        return view('admin.coupures', compact('enCours', 'avenir3h', 'avenir5j', 'slotHour'));
    }

    public function clients()
    {
        $this->guard();
        $user    = Auth::user();
        $isAgent = $user->role === 'agent' && $user->zone;

        $clients = User::where('role', 'client')
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->orderBy('zone')->orderBy('name')->get()
            ->map(function ($c) {
                $c->alert_count = Alert::where('zone', $c->zone)
                    ->where('start_at', '>=', now())->count();
                return $c;
            });

        $totalClients = $clients->count();
        $zonesStats   = $clients->groupBy('zone')->map(fn($g) => $g->count());

        return view('admin.clients', compact('clients', 'totalClients', 'zonesStats'));
    }

    public function storeAgent(Request $request)
    {
        $this->guardAdmin();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:8'],
            'zone'     => ['nullable', 'string', 'max:100'],
            'role'     => ['required', 'in:agent,admin'],
        ], [
            'email.unique'    => 'Cet e-mail est déjà utilisé.',
            'password.min'    => 'Le mot de passe doit contenir au moins 8 caractères.',
            'name.required'   => 'Le nom est obligatoire.',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => $data['password'],
            'role'     => $data['role'],
            'zone'     => $data['zone'] ?? null,
        ]);

        return redirect()->route('admin.agents')
            ->with('agent_created', 'Agent « ' . $data['name'] . ' » créé avec succès.');
    }

    // ── Signalements clients ──────────────────────────────────────
    public function signalements()
    {
        $this->guard();
        $user    = Auth::user();
        $isAgent = $user->role === 'agent' && $user->zone;

        $signalements = \App\Models\Incident::with('declarant')
            ->where('source_type', 'client')
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->orderByDesc('declared_at')
            ->get();

        return view('admin.signalements', compact('signalements'));
    }

    public function resolveSignalement(\Illuminate\Http\Request $request, int $id)
    {
        $this->guard();
        $incident = \App\Models\Incident::findOrFail($id);
        $incident->update(['resolved' => true, 'resolved_at' => now()]);
        return back()->with('success', '✅ Signalement marqué comme résolu.');
    }

    public function agents()
    {
        $this->guardAdmin();

        $agents = User::whereIn('role', ['agent', 'admin'])
            ->orderBy('role')->orderBy('zone')->get()
            ->map(function ($a) {
                $a->alert_count = $a->zone
                    ? Alert::where('zone', $a->zone)->where('start_at', '>=', now())->count()
                    : Alert::where('start_at', '>=', now())->count();
                return $a;
            });

        return view('admin.agents', compact('agents'));
    }

    public function rapports()
    {
        $this->guard();
        $user    = Auth::user();
        $isAgent = $user->role === 'agent' && $user->zone;

        // ── Fenêtres temporelles (identique au dashboard) ────────────
        $slotHour       = (int)(floor(now()->hour / 3) * 3);
        $current3hStart = now()->copy()->setTime($slotHour, 0, 0, 0);
        $current3hEnd   = $current3hStart->copy()->addHours(3);
        $nextSlotStart  = $current3hEnd->copy();
        $nextSlotEnd    = $nextSlotStart->copy()->addHours(3);

        // ── Alertes slot actuel & slot suivant ────────────────────────
        $alerts3h = Alert::where('prediction_mode', '3h')
            ->where('start_at', '>=', $current3hStart)
            ->where('start_at', '<',  $current3hEnd)
            ->forZone($isAgent ? $user->zone : null)
            ->orderByDesc('probability')->get();

        $alertsNextSlot = Alert::where('prediction_mode', '3h')
            ->where('start_at', '>=', $nextSlotStart)
            ->where('start_at', '<',  $nextSlotEnd)
            ->forZone($isAgent ? $user->zone : null)
            ->orderByDesc('probability')->get();

        // ── Graphique rolling 8 slots ─────────────────────────────────
        $slots3hData = [];
        for ($s = 0; $s < 8; $s++) {
            $sStart = $current3hStart->copy()->addHours($s * 3);
            $sEnd   = $sStart->copy()->addHours(3);
            $cnt    = Alert::where('prediction_mode', '3h')
                ->where('start_at', '>=', $sStart)->where('start_at', '<', $sEnd)
                ->forZone($isAgent ? $user->zone : null)->count();
            $avgP   = (int) round(Alert::where('prediction_mode', '3h')
                ->where('start_at', '>=', $sStart)->where('start_at', '<', $sEnd)
                ->forZone($isAgent ? $user->zone : null)->avg('probability') ?? 0);
            $slots3hData[] = [
                'label'      => $sStart->format('H:i') . ($sStart->isToday() ? '' : ' +1j'),
                'count'      => $cnt,
                'avg_prob'   => $avgP,
                'is_current' => ($s === 0),
                'is_next'    => ($s === 1),
            ];
        }

        // ── KPIs généraux ─────────────────────────────────────────────
        $totalAlertes   = Alert::when($isAgent, fn($q) => $q->where('zone', $user->zone))->count();
        $alertesFutures = Alert::where('start_at', '>=', now())
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))->count();
        $urgentes       = Alert::where('probability', '>=', 80)
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))->count();
        $avgProb        = (int) round(Alert::when($isAgent, fn($q) => $q->where('zone', $user->zone))->avg('probability') ?? 0);
        $clientCount    = User::where('role', 'client')->count();

        // ── Distribution cause/cluster/zone/jour ─────────────────────
        $parZone = Alert::select('zone', DB::raw('count(*) as total'), DB::raw('round(avg(probability)) as avg_prob'))
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->groupBy('zone')->orderByDesc('total')->get();

        $parCause = Alert::select('cause_probable', DB::raw('max(reason) as reason'), DB::raw('count(*) as total'))
            ->whereNotNull('cause_probable')->where('cause_probable', '!=', 'aucune')
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->groupBy('cause_probable')->orderByDesc('total')->get();

        $parCluster = Alert::select('cluster', DB::raw('count(*) as total'), DB::raw('round(avg(probability)) as avg_prob'))
            ->whereNotNull('cluster')
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->groupBy('cluster')->orderBy('cluster')->get();

        $parJour = Alert::selectRaw("date(start_at) as jour, count(*) as total")
            ->where('start_at', '>=', now()->subDays(7))
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->groupBy('jour')->orderBy('jour')->get();

        // ── Signalements clients (7 derniers jours) ───────────────────
        $signalements7j = \App\Models\Incident::where('source_type', 'client')
            ->where('declared_at', '>=', now()->subDays(7))
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->count();
        $sigResolus = \App\Models\Incident::where('source_type', 'client')
            ->where('resolved', true)
            ->when($isAgent, fn($q) => $q->where('zone', $user->zone))
            ->count();

        return view('admin.rapports', compact(
            'totalAlertes', 'alertesFutures', 'urgentes', 'avgProb', 'clientCount',
            'parZone', 'parCause', 'parCluster', 'parJour',
            'slots3hData', 'alerts3h', 'alertsNextSlot',
            'current3hStart', 'current3hEnd', 'nextSlotStart', 'nextSlotEnd',
            'signalements7j', 'sigResolus'
        ));
    }

    /* ─────────────────────────────────────────
     | Upload dataset CSV/Excel
    ───────────────────────────────────────── */
    public function uploadDataset(Request $request)
    {
        $this->guardAdmin();

        $request->validate([
            'dataset' => ['required', 'file', 'max:51200'],
        ], [
            'dataset.required' => 'Veuillez sélectionner un fichier.',
            'dataset.max'      => 'Taille maximale : 50 Mo.',
        ]);

        $ext = strtolower($request->file('dataset')->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            return back()->withErrors(['dataset' => 'Format accepté : CSV uniquement.']);
        }

        $file = $request->file('dataset');
        $path = $file->storeAs('datasets', 'dataset_' . now()->format('Ymd_His') . '.csv', 'local');

        session([
            'dataset_path'    => $path,
            'dataset_name'    => $file->getClientOriginalName(),
            'dataset_rows'    => $this->countRows($path),
            'dataset_uploaded_at' => now()->format('d/m/Y H:i'),
        ]);

        return back()->with('success', '✅ Dataset "' . $file->getClientOriginalName() . '" chargé avec succès. Vous pouvez maintenant lancer la prédiction.');
    }

    /* ─────────────────────────────────────────
     | Lancer la prédiction via l'API Flask INNOVAN-CI
     | Fallback automatique sur la simulation si l'API est indisponible
    ───────────────────────────────────────── */
    public function runPrediction()
    {
        $this->guardAdmin();

        $datasetPath = session('dataset_path');
        $datasetName = session('dataset_name', 'Données temps-réel INNOVAN-CI');
        $apiUrl      = env('INNOVAN_API_URL', 'http://127.0.0.1:5001');
        $predictions = [];
        $source      = 'simulation';

        // ── 1. CSV uploadé → API Flask /predict-csv ──
        if ($datasetPath && Storage::disk('local')->exists($datasetPath)) {
            try {
                $rows     = $this->readCsvRows($datasetPath);
                $response = Http::timeout(60)->post("{$apiUrl}/predict-csv", ['rows' => $rows]);

                if ($response->successful()) {
                    $data = $response->json();
                    foreach ($data['predictions'] ?? [] as $p) {
                        $predictions[] = $this->mapPrediction($p);
                    }
                    $source = 'innovan_ia_csv';
                }
            } catch (\Exception $e) {
                // API indisponible → fallback
            }
        }

        // ── 2. Pas de CSV → API Flask /predict (features synthétiques) ──
        if (empty($predictions)) {
            try {
                $response = Http::timeout(30)->post("{$apiUrl}/predict", [
                    'horizon_days'    => 5,
                    'min_probability' => 40,
                    'top_per_day'     => 4,
                    'peak_hour'       => 14,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    foreach ($data['predictions'] ?? [] as $p) {
                        $predictions[] = $this->mapPrediction($p);
                    }
                    $source      = 'innovan_ia';
                    $datasetName = 'Données temps-réel INNOVAN-CI';
                }
            } catch (\Exception $e) {
                // API indisponible → fallback simulation
            }
        }

        // ── 3. Fallback : simulation ──
        if (empty($predictions)) {
            $zones   = ['Abobo', 'Yopougon', 'Cocody', 'Marcory', 'Koumassi',
                        'Port-Bouët', 'Adjamé', 'Treichville', 'Bouaké', 'Yamoussoukro'];
            $reasons = [
                'Surcharge du réseau',
                'Vieillissement des équipements',
                'Intempérie / Fortes pluies',
                'Défaut câble haute tension',
                'Vandalisme / Vol de câble',
            ];
            $predictions = $this->generateSimulatedPredictions($zones, $reasons);
            $source = 'simulation';
        }

        // ── Persister les alertes dans une transaction ──
        $createdAlerts = [];
        $totalClients  = 0;

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
            'dataset_name'     => $datasetName,
            'source'           => $source,
        ];

        session(['last_prediction' => $summary]);

        return redirect()->route('admin.prediction')
                         ->with('prediction_done', true)
                         ->with('summary', $summary);
    }

    /* ─────────────────────────────────────────
     | Prédiction fenêtre glissante 3H
     | Lit l'état actuel de poste_donnees → Flask /predict-3h → alertes [now, now+3h]
    ───────────────────────────────────────── */
    public function predict3h()
    {
        $this->guardAdmin();

        // ── Calcul du SLOT SUIVANT (objectif : prédire à l'avance) ──────────
        // La prédiction 3H cible toujours le slot suivant, jamais le slot actuel.
        // Exemple : si on est à 21:30, on prédit 00:00→03:00 du lendemain.
        $slotHour  = (int)(floor(now()->hour / 3) * 3);
        $nextStart = now()->copy()->setTime($slotHour, 0, 0)->addHours(3);
        $nextEnd   = $nextStart->copy()->addHours(3);

        $apiUrl      = env('INNOVAN_API_URL', 'http://127.0.0.1:5001');
        $predictions = [];
        $source      = 'simulation_3h';

        // ── 1. Lire les données réelles de tous les postes ──
        $postesData = PosteDonnee::all()->map(fn($p) => [
            'poste_id'           => $p->poste_id,
            'zone'               => $p->zone,
            'quartier'           => $p->quartier ?? '',
            'cluster'            => $p->cluster,
            'charge_mw'          => (float) $p->charge_mw,
            'capacite_mw'        => (float) $p->capacite_mw,
            'temperature_c'      => (float) $p->temperature_c,
            'humidite_pct'       => (float) $p->humidite_pct,
            'pluviometrie_norm'  => (float) $p->pluviometrie_norm,
            'temp_max_24h'       => (float) ($p->temp_max_24h ?? ($p->temperature_c + 2)),
            'niveau_barrage_pct' => (float) $p->niveau_barrage_pct,
            'age_cable_ans'      => (float) $p->age_cable_ans,
            'maintenance_recente'=> $p->maintenance_recente ? 1 : 0,
            'nb_incidents_7j'    => (float) $p->nb_incidents_7j,
            'incidents_moy_3j'   => (float) $p->incidents_moy_3j,
        ])->values()->toArray();

        // ── 2. Appel Flask /predict-3h avec le slot cible ──
        if (!empty($postesData)) {
            try {
                $response = Http::timeout(60)->post("{$apiUrl}/predict-3h", [
                    'postes'          => $postesData,
                    'min_probability' => 40,
                    'slot_start'      => $nextStart->format('Y-m-d H:i:s'),
                    'slot_end'        => $nextEnd->format('Y-m-d H:i:s'),
                ]);

                if ($response->successful()) {
                    // Index des postes par poste_id pour enrichir le quartier si Flask ne le retourne pas
                    $postesIndex = PosteDonnee::all()->keyBy('poste_id');

                    foreach ($response->json('predictions', []) as $p) {
                        $mapped = $this->mapPrediction($p, '3h');
                        $mapped['start_at'] = $nextStart->copy();
                        $mapped['end_at']   = $nextEnd->copy();

                        // Si Flask retourne le poste_id mais pas le quartier → l'enrichir depuis la DB
                        if (empty($mapped['quartier']) && !empty($mapped['poste_id'])) {
                            $poste = $postesIndex[$mapped['poste_id']] ?? null;
                            if ($poste) {
                                $mapped['quartier'] = $poste->quartier ?: $this->defaultQuartierForZone($poste->zone);
                                $mapped['cluster']  = $mapped['cluster'] ?? $poste->cluster;
                            }
                        }

                        // Si Flask retourne seulement la zone sans poste_id → prendre le poste le plus à risque
                        if (empty($mapped['quartier']) && !empty($mapped['zone'])) {
                            $worst = PosteDonnee::where('zone', $mapped['zone'])
                                ->orderByRaw('(charge_mw / NULLIF(capacite_mw,0)) DESC')
                                ->first();
                            if ($worst) {
                                $mapped['quartier'] = $worst->quartier ?: $this->defaultQuartierForZone($mapped['zone']);
                                $mapped['poste_id'] = $mapped['poste_id'] ?? $worst->poste_id;
                                $mapped['cluster']  = $mapped['cluster'] ?? $worst->cluster;
                            }
                        }

                        // Dernier recours : fallback par zone
                        if (empty($mapped['quartier']) && !empty($mapped['zone'])) {
                            $mapped['quartier'] = $this->defaultQuartierForZone($mapped['zone']);
                        }

                        $predictions[] = $mapped;
                    }
                    $source = $response->json('source', 'predict_3h');
                }
            } catch (\Exception) {
                // API indisponible → fallback
            }
        }

        // ── 3. Fallback simulation 3H (slot suivant) ──
        if (empty($predictions)) {
            $predictions = $this->generateSimulated3hPredictions($nextStart, $nextEnd);
            $source      = 'simulation_3h';
        }

        // ── 4. Persister : remplace les alertes 3H du slot suivant ──
        $createdAlerts = [];
        $totalClients  = 0;

        DB::transaction(function () use ($predictions, $nextStart, $nextEnd, &$createdAlerts, &$totalClients) {
            Alert::where('prediction_mode', '3h')
                ->where('start_at', '>=', $nextStart->copy()->subMinutes(5))
                ->where('start_at', '<=', $nextEnd)
                ->delete();

            foreach ($predictions as $p) {
                $alert = Alert::create(array_merge($p, ['prediction_mode' => '3h']));
                $createdAlerts[] = $alert;
                $totalClients   += User::where('zone', $p['zone'])->where('role', 'client')->count();
            }
        });

        $summary = [
            'alerts_created'   => count($createdAlerts),
            'zones_affected'   => collect($createdAlerts)->pluck('zone')->unique()->count(),
            'clients_notified' => $totalClients,
            'ran_at'           => now()->format('d/m/Y à H:i'),
            'dataset_name'     => 'Données temps-réel postes (' . count($postesData) . ' postes)',
            'source'           => $source,
            'mode'             => '3h',
            'window_start'     => $nextStart->format('H:i'),
            'window_end'       => $nextEnd->format('H:i'),
        ];

        session(['last_prediction' => $summary]);

        return redirect()->route('admin.prediction')
            ->with('prediction_done', true)
            ->with('summary', $summary);
    }

    public function showPrediction()
    {
        $this->guardAdmin();

        $alerts      = Alert::where('start_at', '>=', now())->orderBy('start_at')->get();
        $summary     = session('summary');
        $clientCount = User::where('role', 'client')->count();
        $statsByZone = Alert::select('zone', DB::raw('count(*) as cuts'))
                            ->groupBy('zone')->orderByDesc('cuts')->get();

        return view('admin.prediction', compact('alerts', 'summary', 'clientCount', 'statsByZone'));
    }

    /* ─────────────────────────────────────────
     | Helpers privés
    ───────────────────────────────────────── */

    private static array $ZONE_DEFAULT_QUARTIER = [
        'Cocody'       => 'Angré',
        'Abobo'        => 'Abobo Baoulé',
        'Yopougon'     => 'Yopougon Selmer',
        'Marcory'      => 'Zone 4',
        'Koumassi'     => 'Koumassi Remblais',
        'Adjamé'       => 'Adjamé Blingué',
        'Attécoubé'    => 'Attécoubé Centre',
        'Treichville'  => 'Treichville Centre',
        'Plateau'      => 'Plateau',
        'Port-Bouët'   => 'Port-Bouët Centre',
        'Bouaké'       => 'Koko',
        'Yamoussoukro' => 'Habitat',
        'San-Pédro'    => 'Cité',
        'Daloa'        => 'Lobia',
        'Korhogo'      => 'Korhogo Centre',
        'Man'          => 'Man Centre',
    ];

    private function defaultQuartierForZone(string $zone): ?string
    {
        return self::$ZONE_DEFAULT_QUARTIER[$zone] ?? null;
    }

    private function mapPrediction(array $p, string $mode = '5j'): array
    {
        return [
            'zone'            => $p['zone'],
            'quartier'        => $p['quartier']       ?? null,
            'poste_id'        => $p['poste_id']       ?? null,
            'cluster'         => $p['cluster']        ?? null,
            'start_at'        => \Carbon\Carbon::parse($p['start_at']),
            'end_at'          => \Carbon\Carbon::parse($p['end_at']),
            'reason'          => $p['reason'],
            'cause_probable'  => $p['cause_probable'] ?? null,
            'probability'     => (int) $p['probability'],
            'prediction_mode' => $mode,
        ];
    }

    private function generateSimulated3hPredictions(\Carbon\Carbon $start, \Carbon\Carbon $end): array
    {
        $reasons = [
            ['cause' => 'surcharge_réseau',          'reason' => 'Surcharge du réseau'],
            ['cause' => 'vieillissement_équipement', 'reason' => 'Vieillissement des équipements'],
            ['cause' => 'intempérie_pluie',          'reason' => 'Intempérie / Fortes pluies'],
            ['cause' => 'défaut_câble_ht',           'reason' => 'Défaut câble haute tension'],
        ];

        $results = [];

        // ── Utiliser les vrais postes (zone + quartier) si disponibles ──
        $postes = PosteDonnee::all();

        if ($postes->count() > 0) {
            // Grouper par (zone, quartier) — un seul résultat par quartier
            $groupes = $postes->groupBy(fn($p) => $p->zone . '|' . ($p->quartier ?? ''));

            // Sélectionner aléatoirement 30-50% des groupes
            $nbSelect = max(2, (int) ceil($groupes->count() * (rand(30, 50) / 100)));
            $selection = $groupes->random(min($nbSelect, $groupes->count()));

            foreach ($selection as $groupe) {
                // Prendre le poste le plus "à risque" dans le groupe
                $poste = $groupe->sortByDesc(fn($p) =>
                    ($p->charge_mw / max(1, $p->capacite_mw)) +
                    ($p->age_cable_ans / 50) +
                    ($p->nb_incidents_7j / 10)
                )->first();

                // Calculer une probabilité simulée basée sur les données réelles
                $taux     = $poste->capacite_mw > 0
                    ? ($poste->charge_mw / $poste->capacite_mw)
                    : 0.5;
                $vieillo  = min(1, $poste->age_cable_ans / 40);
                $incidents = min(1, $poste->nb_incidents_7j / 5);
                $meteo    = $poste->pluviometrie_norm ?? 0;
                $barrage  = $poste->niveau_barrage_pct < 30 ? 0.2 : 0;

                $rawScore = ($taux * 40) + ($vieillo * 25) + ($incidents * 20) + ($meteo * 10) + ($barrage * 5);
                $prob     = min(95, max(45, (int) round($rawScore + rand(-5, 5))));

                // Cause la plus probable selon les données
                $r = $reasons[0]; // défaut surcharge
                if ($vieillo > 0.6)  $r = $reasons[1]; // vieillissement dominant
                if ($meteo > 0.4)    $r = $reasons[2]; // pluie dominante
                if ($incidents > 0.5) $r = $reasons[3]; // câble HT
                if ($prob >= 75)      $r = $reasons[array_rand($reasons)];

                $results[] = [
                    'zone'            => $poste->zone,
                    'quartier'        => $poste->quartier ?: $this->defaultQuartierForZone($poste->zone),
                    'poste_id'        => $poste->poste_id,
                    'cluster'         => $poste->cluster ?? 'A',
                    'start_at'        => $start,
                    'end_at'          => $end,
                    'reason'          => $r['reason'],
                    'cause_probable'  => $r['cause'],
                    'probability'     => $prob,
                    'status'          => 'predicted',
                    'prediction_mode' => '3h',
                ];
            }
        } else {
            // Fallback si aucun poste en base (démo vide)
            $fallbackZones = [
                ['zone'=>'Abobo',     'quartier'=>'Abobo Baoulé'],
                ['zone'=>'Yopougon',  'quartier'=>'Yopougon Selmer'],
                ['zone'=>'Cocody',    'quartier'=>'Angré'],
                ['zone'=>'Marcory',   'quartier'=>'Zone 4'],
                ['zone'=>'Adjamé',    'quartier'=>'Adjamé Blingué'],
                ['zone'=>'Bouaké',    'quartier'=>'Koko'],
            ];
            $sample = array_slice($fallbackZones, 0, rand(2, 4));
            foreach ($sample as $item) {
                $r = $reasons[array_rand($reasons)];
                $results[] = [
                    'zone'            => $item['zone'],
                    'quartier'        => $item['quartier'],
                    'poste_id'        => null,
                    'cluster'         => 'A',
                    'start_at'        => $start,
                    'end_at'          => $end,
                    'reason'          => $r['reason'],
                    'cause_probable'  => $r['cause'],
                    'probability'     => rand(55, 88),
                    'status'          => 'predicted',
                    'prediction_mode' => '3h',
                ];
            }
        }

        return $results;
    }

    private function readCsvRows(string $path): array
    {
        $content = Storage::disk('local')->get($path);
        $lines   = array_filter(array_map('trim', explode("\n", $content)));
        if (count($lines) < 2) return [];

        $header = array_map('trim', str_getcsv(array_shift($lines)));
        $rows   = [];

        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $values = array_map('trim', str_getcsv($line));
            if (count($values) !== count($header)) continue;
            $rows[] = array_combine($header, $values);
        }

        return $rows;
    }

    private function countRows(string $path): int
    {
        $content = Storage::disk('local')->get($path);
        return max(0, substr_count($content, "\n") - 1);
    }

    private function parseCsvPredictions(string $path, array $reasons): array
    {
        $content = Storage::disk('local')->get($path);
        $lines   = array_filter(explode("\n", trim($content)));
        $header  = array_map('trim', str_getcsv(array_shift($lines)));

        $results = [];
        foreach ($lines as $line) {
            $row = array_combine($header, array_map('trim', str_getcsv($line)));
            if (!$row) continue;

            $zone        = $row['zone']        ?? null;
            $probability = (int)($row['probability'] ?? rand(55, 92));
            $reason      = $row['reason']      ?? $reasons[array_rand($reasons)];
            $startAt     = isset($row['date'])
                ? \Carbon\Carbon::parse($row['date'])
                : now()->addDays(rand(1, 5))->setHour(rand(7, 18));

            if (!$zone || $probability < 40) continue;

            $results[] = [
                'zone'        => $zone,
                'start_at'    => $startAt,
                'end_at'      => $startAt->copy()->addHours(rand(2, 6)),
                'reason'      => $reason,
                'probability' => $probability,
            ];
        }

        return $results;
    }

    private function generateSimulatedPredictions(array $zones, array $reasons): array
    {
        $results = [];
        shuffle($zones);
        $selectedZones = array_slice($zones, 0, rand(4, 6));

        for ($day = 1; $day <= 5; $day++) {
            $dayZones = array_slice($selectedZones, 0, rand(2, 3));
            foreach ($dayZones as $zone) {
                $startHour = rand(6, 20);
                $startAt   = now()->addDays($day)->setHour($startHour)->setMinute(0)->setSecond(0);
                $results[] = [
                    'zone'        => $zone,
                    'start_at'    => $startAt,
                    'end_at'      => $startAt->copy()->addHours(rand(2, 6)),
                    'reason'      => $reasons[array_rand($reasons)],
                    'probability' => rand(58, 95),
                ];
            }
        }

        return $results;
    }
}
