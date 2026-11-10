<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Alert;
use App\Models\ConsumptionLog;
use App\Models\Incident;
use App\Models\PosteDonnee;
use App\Services\TranslationService;

class ClientController extends Controller
{
    public function __construct(private TranslationService $translator) {}

    public function index()
    {
        if (!Auth::check()) return redirect('/login');
        $user = Auth::user();
        if ($user->role !== 'client') return redirect('/admin');

        // Alertes 3H : slots automatiques en cours ou à venir — priorité absolue
        $alerts3h = $user->zone
            ? Alert::where('zone', $user->zone)
                ->where('prediction_mode', '3h')
                ->where('probability', '>=', 70)
                ->futures()
                ->orderBy('start_at', 'asc')
                ->orderByDesc('probability')
                ->get()
            : collect();

        // Prédictions 5J : uniquement si aucune alerte 3H disponible
        $alerts5j = ($alerts3h->count() === 0 && $user->zone)
            ? Alert::where('zone', $user->zone)
                ->where('probability', '>=', 70)
                ->where('start_at', '>', now())
                ->orderBy('start_at', 'asc')
                ->orderByDesc('probability')
                ->limit(5)
                ->get()
            : collect();

        // $alerts = union pour les composants qui utilisent encore la variable générique
        $alerts = $alerts3h->count() > 0 ? $alerts3h : $alerts5j;

        [$monthlyKwh, $lastMonthKwh, $weeklyChartData, $monthlyHistory] = $this->consumptionStats($user->id);

        return view('client.dashboard', compact(
            'alerts', 'alerts3h', 'alerts5j',
            'user', 'monthlyKwh', 'lastMonthKwh', 'weeklyChartData', 'monthlyHistory'
        ));
    }

    public function alerts()
    {
        if (!Auth::check()) return redirect('/login');
        $user = Auth::user();
        if ($user->role !== 'client') return redirect('/admin');

        $alerts = $user->zone
            ? $this->alertsForClient($user->zone)
            : collect();

        return view('client.alerts_page', compact('alerts'));
    }

    public function recharge()
    {
        if (!Auth::check()) return redirect('/login');
        $user = Auth::user();
        if ($user->role !== 'client') return redirect('/admin');
        return view('client.recharge', compact('user'));
    }

    public function processRecharge(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:500', 'max:500000'],
            'method' => ['required', 'in:orange_money,mtn_momo,moov,carte,virement'],
            'phone'  => ['nullable', 'string', 'max:25'],
        ], [
            'amount.min' => 'Le montant minimum est de 500 FCFA.',
            'amount.max' => 'Le montant maximum est de 500 000 FCFA.',
        ]);

        Auth::user()->increment('balance', (float) $request->amount);

        return back()->with('recharge_success', number_format($request->amount, 0, '.', ' ') . ' FCFA crédités sur votre compteur.');
    }

    // API JSON : prochaine alerte pour notification vocale
    // On notifie sur le SLOT SUIVANT (start_at futur), pas le slot en cours
    /**
     * Transcription vocale → français via Groq Whisper + LLM.
     * Accepte les langues : français, dioula, baoulé, bété, nouchi.
     */
    public function transcribeVoice(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $request->validate([
            'audio' => ['required', 'file', 'max:10240'],
        ], [
            'audio.required' => 'Aucun fichier audio reçu.',
            'audio.max'      => 'Enregistrement trop long (max 10 Mo).',
        ]);

        $apiKey = env('GROQ_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'Service vocal non disponible — clé Groq manquante.'], 503);
        }

        $file = $request->file('audio');

        try {
            // ── 1. Transcription Groq Whisper (multilingue) ──────────
            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->timeout(30)
                ->attach('file', file_get_contents($file->getRealPath()), 'audio.webm')
                ->post('https://api.groq.com/openai/v1/audio/transcriptions', [
                    'model'           => 'whisper-large-v3',
                    'response_format' => 'json',
                    'language'        => 'fr',
                ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'Erreur Whisper : ' . $response->body()], 500);
            }

            $original = trim($response->json('text', ''));

            if (empty($original)) {
                return response()->json(['error' => 'Aucun texte détecté dans l\'enregistrement.'], 422);
            }

            // ── 2. Traduction + correction en français via LLM ───────
            $translated = $this->toFrench($original, $apiKey);

            return response()->json([
                'ok'         => true,
                'original'   => $original,
                'translated' => $translated,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur technique : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Traduit et normalise un texte en français standard via Groq LLM.
     * Si le texte est déjà en français correct, il est retourné tel quel.
     */
    private function toFrench(string $text, string $apiKey): string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->timeout(10)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => 'llama-3.3-70b-versatile',
                    'temperature' => 0.1,
                    'max_tokens'  => 250,
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => 'Tu es un assistant de la CIE (Côte d\'Ivoire Électricité). '
                                . 'Le client vient de dicter un signalement de coupure d\'électricité. '
                                . 'Traduis le texte en français standard, clair et concis. '
                                . 'Si c\'est déjà en français, corrige seulement les fautes. '
                                . 'Langues possibles : français, dioula, baoulé, bété, nouchi. '
                                . 'Ne génère QUE la traduction finale, rien d\'autre.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $text,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $result = trim($response->json('choices.0.message.content') ?? '');
                if (!empty($result)) return $result;
            }
        } catch (\Exception) {}

        return $text;
    }

    public function latestAlert()
    {
        $user  = Auth::user();
        $alert = $user->zone
            ? $this->nextSlotAlertForClient($user->zone)
            : null;

        if (!$alert) {
            return response()->json(['alert' => null]);
        }

        return response()->json([
            'alert' => [
                'id'          => $alert->id,
                'zone'        => $alert->zone,
                'start_at'    => $alert->start_at->format('d/m/Y à H:i'),
                'reason'      => $alert->reason,
                'probability' => $alert->probability,
                'language'    => $user->language ?? 'fr',
                'audio_url'   => $this->resolveAudioUrl($alert, $user->language ?? 'fr'),
                'tts_text'    => $this->translator->translateAlert($alert, $user->language ?? 'fr', $user->name),
            ]
        ]);
    }

    public function storeIncident(Request $request)
    {
        if (!Auth::check()) return redirect('/login');
        $user = Auth::user();
        if ($user->role !== 'client') return redirect('/admin');

        $request->validate([
            'type'        => ['required', 'in:surcharge,vieillissement,meteo,vandalisme,autre'],
            'severity'    => ['required', 'in:mineur,normal,critique'],
            'description' => ['nullable', 'string', 'max:500'],
            'quartier'    => ['nullable', 'string', 'max:100'],
        ], [
            'type.required'     => 'Veuillez sélectionner le type de coupure.',
            'severity.required' => 'Veuillez indiquer la gravité.',
        ]);

        // Trouver le poste principal de la zone du client
        $poste = PosteDonnee::where('zone', $user->zone)->first();

        Incident::create([
            'poste_id'    => $poste?->poste_id,
            'zone'        => $user->zone ?? 'Non précisée',
            'quartier'    => $request->quartier ?: null,
            'declared_by' => $user->id,
            'declared_at' => now(),
            'source_type' => 'client',
            'type'        => $request->type,
            'description' => $request->description,
            'severity'    => $request->severity,
            'resolved'    => false,
        ]);

        return back()->with('incident_reported', 'Votre signalement a bien été transmis aux équipes CIE de ' . ($user->zone ?? 'votre zone') . '. Merci !');
    }

    public function setLanguage(Request $request)
    {
        $request->validate(['language' => ['required', 'in:fr,dioula,baoule,bete,nouchi']]);
        Auth::user()->update(['language' => $request->language]);
        return back();
    }

    /**
     * Alertes affichées dans le dashboard client.
     * Ordre : 3H en cours/à venir d'abord, puis 5J si aucune 3H.
     * Les alertes 3H (automatiques, précises) passent toujours avant les 5J.
     */
    private function alertsForClient(string $zone, int $limit = 0): \Illuminate\Database\Eloquent\Collection
    {
        // Alertes 3H : en cours ou futures (le plus pertinent pour le client)
        $alerts3h = Alert::where('zone', $zone)
            ->where('prediction_mode', '3h')
            ->where('probability', '>=', 70)
            ->futures()
            ->orderBy('start_at', 'asc')
            ->orderByDesc('probability')
            ->get();

        // Si on a des alertes 3H → on les retourne (5J ignorées)
        if ($alerts3h->count() > 0) {
            return $limit > 0 ? $alerts3h->take($limit) : $alerts3h;
        }

        // Aucune alerte 3H → on complète avec les prédictions 5J futures
        $alerts5j = Alert::where('zone', $zone)
            ->where('probability', '>=', 70)
            ->where('start_at', '>', now())
            ->orderBy('start_at', 'asc')
            ->orderByDesc('probability')
            ->get();

        return $limit > 0 ? $alerts5j->take($limit) : $alerts5j;
    }

    /**
     * Alerte pour la notification vocale — ordre de priorité strict :
     *
     * 1. Slot 3H immédiatement suivant  (prédiction la plus proche et fiable)
     * 2. N'importe quel slot 3H futur   (autre fenêtre automatique)
     * 3. Prédiction 5J / manuelle        (horizon plus lointain, en dernier recours)
     *
     * Les alertes 5J ne doivent jamais passer devant une alerte 3H plus proche.
     */
    private function nextSlotAlertForClient(string $zone): ?Alert
    {
        $slotHour  = (int)(floor(now()->hour / 3) * 3);
        $slotStart = now()->copy()->setTime($slotHour, 0, 0);
        $nextStart = $slotStart->copy()->addHours(3);
        $nextEnd   = $nextStart->copy()->addHours(3);

        // ── Priorité 1 : slot 3H immédiatement suivant ─────────────
        $alert = Alert::where('zone', $zone)
            ->where('prediction_mode', '3h')
            ->where('probability', '>=', 70)
            ->where('start_at', '>=', $nextStart)
            ->where('start_at', '<',  $nextEnd)
            ->orderByDesc('probability')
            ->first();

        if ($alert) return $alert;

        // ── Priorité 2 : n'importe quel slot 3H futur ou en cours ──
        $alert = Alert::where('zone', $zone)
            ->where('prediction_mode', '3h')
            ->where('probability', '>=', 70)
            ->futures()
            ->orderBy('start_at', 'asc')
            ->orderByDesc('probability')
            ->first();

        if ($alert) return $alert;

        // ── Priorité 3 : prédiction 5J (dernier recours) ───────────
        return Alert::where('zone', $zone)
            ->where('probability', '>=', 70)
            ->where('start_at', '>', now())
            ->orderBy('start_at', 'asc')
            ->orderByDesc('probability')
            ->first();
    }

    private function consumptionStats(int $userId): array
    {
        $now        = now();
        $monthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd   = $monthStart->copy()->subDay();

        $monthlyKwh   = (float) ConsumptionLog::where('user_id', $userId)
            ->where('log_date', '>=', $monthStart->toDateString())
            ->sum('kwh');

        $lastMonthKwh = (float) ConsumptionLog::where('user_id', $userId)
            ->whereBetween('log_date', [$lastMonthStart->toDateString(), $lastMonthEnd->toDateString()])
            ->sum('kwh');

        // 7 derniers jours pour le graphique
        $logs = ConsumptionLog::where('user_id', $userId)
            ->whereBetween('log_date', [today()->subDays(6)->toDateString(), today()->toDateString()])
            ->orderBy('log_date')
            ->get()
            ->keyBy(fn($l) => $l->log_date->format('Y-m-d'));

        $weeklyChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $key  = $date->format('Y-m-d');
            $weeklyChartData[] = [
                'label' => $date->locale('fr')->isoFormat('ddd D'),
                'kwh'   => isset($logs[$key]) ? round((float)$logs[$key]->kwh, 1) : 0,
            ];
        }

        // Historique 6 derniers mois
        $monthlyHistory = ConsumptionLog::where('user_id', $userId)
            ->where('log_date', '>=', $now->copy()->subMonths(5)->startOfMonth()->toDateString())
            ->selectRaw("STRFTIME('%Y-%m', log_date) as month, ROUND(SUM(kwh),1) as total_kwh, ROUND(SUM(amount_fcfa),0) as total_fcfa")
            ->groupByRaw("STRFTIME('%Y-%m', log_date)")
            ->orderByRaw("STRFTIME('%Y-%m', log_date)")
            ->get();

        return [$monthlyKwh, $lastMonthKwh, $weeklyChartData, $monthlyHistory];
    }

    private function resolveAudioUrl(Alert $alert, string $lang): ?string
    {
        $type = $this->alertType($alert);
        $path = "audio/{$lang}/{$type}.mp3";
        return file_exists(public_path($path)) ? asset($path) : null;
    }

    private function alertType(Alert $alert): string
    {
        $prob = $alert->probability;
        if ($prob >= 80) return 'coupure_urgente';
        if ($prob >= 60) return 'coupure_prevue';
        return 'coupure_possible';
    }
}
