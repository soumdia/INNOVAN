<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Alert;
use App\Models\User;
use App\Models\PosteDonnee;

class PredictNow extends Command
{
    protected $signature   = 'predict:3h';
    protected $description = 'Lance la prédiction fenêtre 3H sur les données réelles des postes';

    public function handle(): int
    {
        $apiUrl = env('INNOVAN_API_URL', 'http://127.0.0.1:5001');

        // ── Charger les données réelles ──────────────────────────
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

        $this->info('[predict:3h] ' . count($postesData) . ' postes chargés.');

        $predictions = [];
        $source      = 'simulation_3h';

        // ── Appel API Flask ──────────────────────────────────────
        if (!empty($postesData)) {
            try {
                $response = Http::timeout(60)->post("{$apiUrl}/predict-3h", [
                    'postes'          => $postesData,
                    'min_probability' => 40,
                ]);

                if ($response->successful()) {
                    $data   = $response->json();
                    $source = $data['source'] ?? 'predict_3h';
                    foreach ($data['predictions'] ?? [] as $p) {
                        $predictions[] = $this->map($p);
                    }
                    $this->info("[predict:3h] API OK — {$data['alertes']} alertes, fenêtre {$data['window_start']} → {$data['window_end']}");
                }
            } catch (\Exception $e) {
                $this->warn('[predict:3h] API indisponible : ' . $e->getMessage() . ' → simulation');
            }
        }

        // ── Fallback simulation ──────────────────────────────────
        if (empty($predictions)) {
            $predictions = $this->simulate();
            $source      = 'simulation_3h';
            $this->warn('[predict:3h] Mode simulation — ' . count($predictions) . ' alertes générées.');
        }

        // ── Persister en base ─────────────────────────────────────
        $count = 0;
        DB::transaction(function () use ($predictions, &$count) {
            Alert::where('prediction_mode', '3h')
                ->where('start_at', '>=', now()->subMinutes(10))
                ->delete();

            foreach ($predictions as $p) {
                Alert::create(array_merge($p, [
                    'status'          => 'predicted',
                    'prediction_mode' => '3h',
                ]));
                $count++;
            }
        });

        $this->info("[predict:3h] {$count} alertes enregistrées (source: {$source}).");
        return 0;
    }

    private function map(array $p): array
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
            'prediction_mode' => '3h',
            'status'          => 'predicted',
        ];
    }

    private function simulate(): array
    {
        // Utilise les zones où des clients sont réellement abonnés
        $clientZones = User::where('role', 'client')
            ->whereNotNull('zone')
            ->distinct()
            ->pluck('zone')
            ->toArray();

        if (empty($clientZones)) {
            $clientZones = ['Abobo', 'Yopougon', 'Cocody', 'Marcory', 'Koumassi'];
        }

        $reasons = [
            ['cause' => 'surcharge_réseau',          'reason' => 'Surcharge du réseau'],
            ['cause' => 'vieillissement_équipement', 'reason' => 'Vieillissement des équipements'],
            ['cause' => 'intempérie_pluie',          'reason' => 'Intempérie / Fortes pluies'],
            ['cause' => 'défaut_câble_ht',           'reason' => 'Défaut câble haute tension'],
        ];

        $results  = [];
        $slotH    = (int)(floor(now()->hour / 3) * 3);
        $start    = now()->setTime($slotH, 0, 0, 0);
        $end      = $start->copy()->addHours(3);
        $nbZones  = min(rand(2, 4), count($clientZones));
        $selected = array_slice($clientZones, 0, $nbZones);

        foreach ($selected as $zone) {
            $r = $reasons[array_rand($reasons)];
            $results[] = [
                'zone'            => $zone,
                'quartier'        => null,
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
        return $results;
    }
}
