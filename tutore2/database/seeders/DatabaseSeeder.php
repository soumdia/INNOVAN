<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        /* ─────────────────────────────────────────────────
        |  ADMIN GÉNÉRAL CIE
        |  Accès complet : upload dataset, prédiction IA,
        |  envoi d'alertes aux agents terrain.
        ───────────────────────────────────────────────── */
        User::factory()->create([
            'name'     => 'Admin CIE',
            'email'    => 'agent@cie.local',
            'role'     => 'admin',
            'password' => 'password',
            'phone'    => '225 20 21 00 00',
            'address'  => 'Siège CIE, Plateau, Abidjan',
            'zone'     => null,
            'language' => 'fr',
        ]);

        /* ─────────────────────────────────────────────────
        |  5 AGENTS TERRAIN CIE (chacun assigné à une commune)
        ───────────────────────────────────────────────── */
        $agents = [
            [
                'name'     => 'Kouamé Serge',
                'email'    => 'agent.abobo@cie.local',
                'zone'     => 'Abobo',
                'phone'    => '225 07 11 22 33',
                'address'  => 'Agence CIE Abobo, Abidjan',
            ],
            [
                'name'     => 'Bamba Mamadou',
                'email'    => 'agent.yopougon@cie.local',
                'zone'     => 'Yopougon',
                'phone'    => '225 05 44 55 66',
                'address'  => 'Agence CIE Yopougon, Abidjan',
            ],
            [
                'name'     => 'Diallo Fatoumata',
                'email'    => 'agent.cocody@cie.local',
                'zone'     => 'Cocody',
                'phone'    => '225 07 77 88 99',
                'address'  => 'Agence CIE Cocody, Abidjan',
            ],
            [
                'name'     => 'Kouassi Noël',
                'email'    => 'agent.marcory@cie.local',
                'zone'     => 'Marcory',
                'phone'    => '225 05 00 11 22',
                'address'  => 'Agence CIE Marcory, Abidjan',
            ],
            [
                'name'     => 'Yao Albert',
                'email'    => 'agent.koumassi@cie.local',
                'zone'     => 'Koumassi',
                'phone'    => '225 07 33 44 55',
                'address'  => 'Agence CIE Koumassi, Abidjan',
            ],
        ];

        foreach ($agents as $a) {
            User::factory()->create(array_merge($a, [
                'role'     => 'agent',
                'password' => 'password',
                'language' => 'fr',
            ]));
        }

        /* ─────────────────────────────────────────────────
        |  5 CLIENTS (abonnés CIE répartis dans les communes)
        ───────────────────────────────────────────────── */
        $clients = [
            [
                'name'         => 'Konan Jean-Baptiste',
                'email'        => 'konan.jean@gmail.com',
                'zone'         => 'Cocody',
                'meter_number' => '0012345678',
                'phone'        => '225 07 60 11 22',
                'address'      => 'Riviera 2, Cocody, Abidjan',
                'language'     => 'fr',
            ],
            [
                'name'         => 'Tra Bi Marie-Claire',
                'email'        => 'trabi.marie@gmail.com',
                'zone'         => 'Abobo',
                'meter_number' => '0023456789',
                'phone'        => '225 05 71 33 44',
                'address'      => 'Abobo Baoulé, Abidjan',
                'language'     => 'baoule',
            ],
            [
                'name'         => 'Coulibaly Ibrahim',
                'email'        => 'coulibaly.ibou@gmail.com',
                'zone'         => 'Yopougon',
                'meter_number' => '0034567890',
                'phone'        => '225 07 52 55 66',
                'address'      => 'Yopougon Selmer, Abidjan',
                'language'     => 'dioula',
            ],
            [
                'name'         => 'Gnangnan Sophie',
                'email'        => 'gnangnan.sophie@gmail.com',
                'zone'         => 'Marcory',
                'meter_number' => '0045678901',
                'phone'        => '225 05 63 77 88',
                'address'      => 'Zone 4, Marcory, Abidjan',
                'language'     => 'fr',
            ],
            [
                'name'         => 'Touré Aminata',
                'email'        => 'toure.aminata@gmail.com',
                'zone'         => 'Koumassi',
                'meter_number' => '0056789012',
                'phone'        => '225 07 84 99 00',
                'address'      => 'Koumassi Remblais, Abidjan',
                'language'     => 'dioula',
            ],
            // Compte de test client (conservé)
            [
                'name'         => 'Client Demo',
                'email'        => 'client@demo.local',
                'zone'         => 'Cocody',
                'meter_number' => '0099999999',
                'phone'        => '225 07 00 00 01',
                'address'      => 'Cocody Riviera 2, Abidjan',
                'language'     => 'fr',
            ],
        ];

        foreach ($clients as $c) {
            User::factory()->create(array_merge($c, [
                'role'     => 'client',
                'password' => 'password',
            ]));
        }

        // Postes de données + alertes de démonstration
        $this->call([
            PosteDonneeSeeder::class,
            AlertSeeder::class,
        ]);
    }
}
