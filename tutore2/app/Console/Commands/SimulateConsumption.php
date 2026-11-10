<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ConsumptionLog;

class SimulateConsumption extends Command
{
    protected $signature   = 'simulate:consumption {--backfill=0 : Nombre de jours à rétro-simuler}';
    protected $description = 'Génère la consommation horaire pour chaque client et déduit du solde';

    private const RATE_FCFA = 35;

    public function handle(): int
    {
        // Assigner daily_kwh aux clients qui n'en ont pas encore
        User::where('role', 'client')->whereNull('daily_kwh')->each(function ($u) {
            $u->update(['daily_kwh' => round(mt_rand(80, 220) / 10, 1)]);
        });

        $clients  = User::where('role', 'client')->whereNotNull('daily_kwh')->get();
        $backfill = (int) $this->option('backfill');

        foreach ($clients as $client) {
            if ($backfill > 0) {
                $this->backfillDays($client, $backfill);
            } else {
                $this->processHour($client);
            }
        }

        $this->info("Simulation terminée pour {$clients->count()} client(s).");
        return 0;
    }

    // ── Déduction horaire (cron toutes les heures) ──────────────────────────
    private function processHour(User $client): void
    {
        $variation = 1 + (mt_rand(-8, 8) / 100);
        $kwh       = round(($client->daily_kwh / 24) * $variation, 3);
        $amount    = round($kwh * self::RATE_FCFA, 2);

        $client->refresh();
        $balanceBefore = (float) $client->balance;
        $balanceAfter  = max(0.0, $balanceBefore - $amount);

        // Accumule dans le log du jour (crée si absent)
        $today = today();
        $log   = ConsumptionLog::where('user_id', $client->id)->where('log_date', $today)->first();

        if ($log) {
            $log->update([
                'kwh'          => round((float) $log->kwh + $kwh, 3),
                'amount_fcfa'  => round((float) $log->amount_fcfa + $amount, 2),
                'balance_after'=> $balanceAfter,
            ]);
        } else {
            ConsumptionLog::create([
                'user_id'        => $client->id,
                'log_date'       => $today,
                'kwh'            => $kwh,
                'amount_fcfa'    => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
            ]);
        }

        $client->update(['balance' => $balanceAfter]);
    }

    // ── Rétro-simulation journalière (--backfill=N) ─────────────────────────
    private function backfillDays(User $client, int $days): void
    {
        // Pour un backfill propre, on recalcule depuis un solde de départ virtuel
        $balance = (float) mt_rand(8000, 25000);

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = today()->subDays($i);

            if (ConsumptionLog::where('user_id', $client->id)->where('log_date', $date)->exists()) {
                // Récupère le solde final de ce jour pour continuer
                $existing = ConsumptionLog::where('user_id', $client->id)->where('log_date', $date)->first();
                $balance = (float) $existing->balance_after;
                continue;
            }

            $variation = 1 + (mt_rand(-15, 15) / 100);
            $kwh       = round($client->daily_kwh * $variation, 2);
            $amount    = round($kwh * self::RATE_FCFA, 2);
            $before    = $balance;
            $balance   = max(0.0, $balance - $amount);

            ConsumptionLog::create([
                'user_id'        => $client->id,
                'log_date'       => $date,
                'kwh'            => $kwh,
                'amount_fcfa'    => $amount,
                'balance_before' => $before,
                'balance_after'  => $balance,
            ]);
        }

        $client->update(['balance' => $balance]);
    }
}
