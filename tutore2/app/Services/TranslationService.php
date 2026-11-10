<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\Alert;

class TranslationService
{
    private const GROQ_URL  = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL     = 'llama-3.3-70b-versatile';
    private const CACHE_TTL = 3600;

    // Conseils pratiques selon le niveau d'alerte
    private const CONSEILS = [
        'urgent'   => [
            'fr'     => "Branchez et rechargez IMMÉDIATEMENT vos appareils (téléphone, lampes, groupes électrogènes). Faites le plein d'eau. Coupure quasi certaine.",
            'dioula' => "Brancher i telefooni ni lampe sisan. I ka ji falen. Courant bena diya.",
            'baoule' => "Branche wou appareil sisan. Prépare djo. Courant bé yiè.",
            'bete'   => "Branche vos appareils maintenant. Préparez eau et lampes.",
            'nouchi' => "Go go go ! Charge ton telephone et lampe wari. Courant va dja maintenant !",
        ],
        'probable' => [
            'fr'     => "Pensez à recharger votre téléphone, préparer des lampes et faire des réserves d'eau avant l'heure de coupure.",
            'dioula' => "Falen i telefooni ni lampe. I ka ji sɔrɔ. Courant bena tɛmɛ.",
            'baoule' => "Pense recharger ton téléphone. Prépare lampes. Courant bé yiè.",
            'bete'   => "Rechargez téléphone. Préparez lampes et eau avant coupure.",
            'nouchi' => "Pense à charger ton tel et préparer ta lampe avant que courant dja !",
        ],
    ];

    private const LANG_PROMPTS = [
        'fr' => [
            'name'   => 'Français',
            'prompt' => 'Tu es l\'assistant SMS de la CIE (Compagnie Ivoirienne d\'Électricité). '
                . 'Rédige un message d\'alerte PERSONNALISÉ, chaleureux et pratique en français. '
                . 'Commence par "Bonjour {prenom}," puis indique la coupure prévue avec heure début/fin et la cause, '
                . 'puis termine par un conseil pratique (brancher appareils, recharger téléphone, préparer lampe/eau). '
                . 'Ton: professionnel mais accessible. Maximum 3 phrases.',
        ],
        'dioula' => [
            'name'   => 'Dioula',
            'prompt' => 'Tu es l\'assistant SMS de la CIE. Rédige un message d\'alerte personnalisé en dioula (bambara CI). '
                . 'Commence par une salutation avec le prénom, indique la coupure et donne un conseil pratique simple. '
                . 'Utilise des mots courants de Bouaké/Korhogo, mélange français pour les termes techniques. Maximum 3 phrases.',
        ],
        'baoule' => [
            'name'   => 'Baoulé',
            'prompt' => 'Tu es l\'assistant SMS de la CIE. Rédige un message d\'alerte personnalisé en baoulé du centre CI. '
                . 'Commence par une salutation avec le prénom, indique la coupure et donne un conseil pratique. '
                . 'Utilise des mots simples compris à Yamoussoukro/Bouaflé. Maximum 3 phrases.',
        ],
        'bete' => [
            'name'   => 'Bété',
            'prompt' => 'Tu es l\'assistant SMS de la CIE. Rédige un message d\'alerte personnalisé en bété du centre-ouest CI. '
                . 'Commence par une salutation avec le prénom, indique la coupure et donne un conseil pratique. '
                . 'Utilise des mots simples compris à Gagnoa/Daloa. Maximum 3 phrases.',
        ],
        'nouchi' => [
            'name'   => 'Nouchi',
            'prompt' => 'Tu es l\'assistant SMS de la CIE. Rédige un message d\'alerte court en nouchi abidjanais. '
                . 'Commence par saluer avec le prénom (style "Eh [prénom] !"), annonce la coupure avec humour mais sérieux, '
                . 'et donne un conseil pratique en argot. Dynamique et court. Maximum 3 phrases.',
        ],
    ];

    /**
     * Génère un message d'alerte personnalisé pour le client.
     *
     * @param Alert       $alert    L'alerte à notifier
     * @param string      $lang     Langue choisie par le client
     * @param string|null $userName Prénom du client pour personnalisation
     */
    public function translateAlert(Alert $alert, string $lang, ?string $userName = null): string
    {
        $prenom   = $userName ? explode(' ', trim($userName))[0] : null;
        $cacheKey = "alert_tts_{$alert->id}_{$lang}_" . ($prenom ? md5($prenom) : 'anon');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($alert, $lang, $prenom) {
            return $this->generateTranslation($alert, $lang, $prenom);
        });
    }

    private function generateTranslation(Alert $alert, string $lang, ?string $prenom): string
    {
        $apiKey = env('GROQ_API_KEY');

        if (!$apiKey) {
            return $this->fallbackText($alert, $lang, $prenom);
        }

        $langInfo  = self::LANG_PROMPTS[$lang] ?? self::LANG_PROMPTS['fr'];
        $duree     = $this->formatDuration($alert);
        $niveau    = $alert->probability >= 80 ? 'URGENTE' : 'PROBABLE';
        $consKey   = $alert->probability >= 80 ? 'urgent' : 'probable';
        $conseil   = self::CONSEILS[$consKey][$lang] ?? self::CONSEILS[$consKey]['fr'];
        $civility  = $this->detectCivility($prenom);

        // Construction du message à traduire/personnaliser
        $alertInfo = "Client: {$civility}" . ($prenom ? " {$prenom}" : '')
            . ". Alerte {$niveau} zone {$alert->zone}"
            . ($alert->quartier ? " ({$alert->quartier})" : '')
            . ". Coupure prévue le {$alert->start_at->format('d/m/Y')}"
            . " de {$alert->start_at->format('H:i')}"
            . ($alert->end_at ? " à {$alert->end_at->format('H:i')}" : '')
            . ($duree ? " (durée : {$duree})" : '')
            . ". Cause : " . ($alert->reason ?? 'Maintenance réseau')
            . ". Probabilité : {$alert->probability}%."
            . " Conseil à inclure : {$conseil}";

        try {
            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->post(self::GROQ_URL, [
                    'model'       => self::MODEL,
                    'temperature' => 0.4,
                    'max_tokens'  => 180,
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => $langInfo['prompt']
                                . ' Réponds UNIQUEMENT avec le message final pour le client, sans explication.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $alertInfo,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $text = trim($response->json('choices.0.message.content') ?? '');
                if (!empty($text)) {
                    return $text;
                }
            }
        } catch (\Exception) {
            // Fallback silencieux
        }

        return $this->fallbackText($alert, $lang, $prenom);
    }

    private function fallbackText(Alert $alert, string $lang, ?string $prenom): string
    {
        $zone    = $alert->zone;
        $hDebut  = $alert->start_at->format('H:i');
        $hFin    = $alert->end_at ? $alert->end_at->format('H:i') : '?';
        $duree   = $this->formatDuration($alert) ?? '3h';
        $civ     = $this->detectCivility($prenom);
        $greeting = $prenom ? "Bonjour {$civ} {$prenom}" : 'Bonjour';
        $consKey  = $alert->probability >= 80 ? 'urgent' : 'probable';

        return match ($lang) {
            'dioula' => "{$greeting} ! Courant bena diya {$zone} la, {$hDebut} - {$hFin}. "
                . (self::CONSEILS[$consKey]['dioula']),
            'baoule' => "{$greeting} ! Courant bé yiè {$zone} {$hDebut} - {$hFin}. "
                . (self::CONSEILS[$consKey]['baoule']),
            'bete'   => "{$greeting} ! Coupure courant {$zone}, {$hDebut} - {$hFin}. "
                . (self::CONSEILS[$consKey]['bete']),
            'nouchi' => "Eh {$prenom} ! Courant va dja à {$zone} de {$hDebut} à {$hFin}. "
                . (self::CONSEILS[$consKey]['nouchi']),
            default  => "{$greeting}, une coupure d'électricité est prévue dans votre zone {$zone} "
                . "de {$hDebut} à {$hFin} (durée estimée : {$duree}). "
                . "Cause : " . ($alert->reason ?? 'Maintenance réseau') . ". "
                . self::CONSEILS[$consKey]['fr'],
        };
    }

    private function detectCivility(?string $prenom): string
    {
        if (!$prenom) return 'Cher(e) client(e)';
        // Prénoms féminins ivoiriens courants
        $feminins = ['marie', 'fatou', 'aminata', 'fatoumata', 'awa', 'aissatou', 'ramatou',
                     'sophie', 'claire', 'nadège', 'nathalie', 'christelle', 'isabelle'];
        return in_array(strtolower($prenom), $feminins) ? 'Madame' : 'Monsieur';
    }

    private function formatDuration(Alert $alert): ?string
    {
        if (!$alert->end_at) return null;
        $diff = $alert->start_at->diff($alert->end_at);
        $h    = $diff->h;
        $m    = $diff->i;
        if ($h > 0 && $m > 0) return "{$h}h{$m}min";
        if ($h > 0) return "{$h}h";
        return "{$m} minutes";
    }
}
