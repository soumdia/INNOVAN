<?php

namespace Database\Factories;

use App\Models\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertFactory extends Factory
{
    protected $model = Alert::class;

    private static array $ZONES = [
        ['zone' => 'Cocody',    'quartier' => 'Angré'],
        ['zone' => 'Cocody',    'quartier' => 'Riviera 2'],
        ['zone' => 'Abobo',     'quartier' => 'Abobo Baoulé'],
        ['zone' => 'Abobo',     'quartier' => 'Abobo Gare'],
        ['zone' => 'Yopougon',  'quartier' => 'Yopougon Selmer'],
        ['zone' => 'Yopougon',  'quartier' => 'Yopougon Nord'],
        ['zone' => 'Marcory',   'quartier' => 'Zone 4'],
        ['zone' => 'Marcory',   'quartier' => 'Marcory Résidentiel'],
        ['zone' => 'Koumassi',  'quartier' => 'Koumassi Remblais'],
        ['zone' => 'Adjamé',    'quartier' => 'Adjamé Blingué'],
        ['zone' => 'Bouaké',    'quartier' => 'Koko'],
        ['zone' => 'Yamoussoukro', 'quartier' => 'Habitat'],
    ];

    private static array $CAUSES = [
        ['cause' => 'surcharge_réseau',          'reason' => 'Surcharge du réseau'],
        ['cause' => 'vieillissement_équipement', 'reason' => 'Vieillissement des équipements'],
        ['cause' => 'intempérie_pluie',          'reason' => 'Intempérie / Fortes pluies'],
        ['cause' => 'défaut_câble_ht',           'reason' => 'Défaut câble haute tension'],
        ['cause' => 'vandalisme_vol_câble',       'reason' => 'Vandalisme / Vol de câble'],
    ];

    public function definition(): array
    {
        $slotHour = [0, 3, 6, 9, 12, 15, 18, 21][array_rand([0, 3, 6, 9, 12, 15, 18, 21])];
        $start    = now()->addDays(rand(0, 5))->setTime($slotHour, 0, 0);
        $end      = $start->copy()->addHours(3);

        $location = self::$ZONES[array_rand(self::$ZONES)];
        $cause    = self::$CAUSES[array_rand(self::$CAUSES)];

        return [
            'zone'            => $location['zone'],
            'quartier'        => $location['quartier'],
            'start_at'        => $start,
            'end_at'          => $end,
            'reason'          => $cause['reason'],
            'cause_probable'  => $cause['cause'],
            'probability'     => $this->faker->numberBetween(45, 92),
            'status'          => 'predicted',
            'prediction_mode' => $this->faker->randomElement(['3h', '3h', '5j']), // 2/3 de chance d'être 3h
            'cluster'         => $this->faker->randomElement(['A', 'A', 'B', 'C']),
        ];
    }
}
