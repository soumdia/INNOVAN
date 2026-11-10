<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PosteDonnee;

class PosteDonneeSeeder extends Seeder
{
    // Profils de charge par cluster (valeurs de base)
    private const CLUSTER_PROFILES = [
        'A' => ['charge_mw' => 17.0, 'capacite_mw' => 25.0, 'niveau_barrage_pct' => 62.0,
                'temperature_c' => 26.5, 'humidite_pct' => 74.0, 'pluviometrie_norm' => 0.25,
                'age_cable_ans' => 13.0],
        'B' => ['charge_mw' => 12.0, 'capacite_mw' => 20.0, 'niveau_barrage_pct' => 55.0,
                'temperature_c' => 27.5, 'humidite_pct' => 72.0, 'pluviometrie_norm' => 0.30,
                'age_cable_ans' => 15.5],
        'C' => ['charge_mw' =>  8.0, 'capacite_mw' => 14.0, 'niveau_barrage_pct' => 48.0,
                'temperature_c' => 29.0, 'humidite_pct' => 68.0, 'pluviometrie_norm' => 0.35,
                'age_cable_ans' => 17.0],
    ];

    public function run(): void
    {
        $metadataPath = 'C:\\Hackathon\\tutore_2\\innovan\\Data\\poste_metadata.csv';

        if (!file_exists($metadataPath)) {
            $this->command->warn("poste_metadata.csv introuvable — utilisation des postes de démonstration.");
            $this->seedFallback();
            return;
        }

        $lines  = file($metadataPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $header = array_map('trim', str_getcsv(array_shift($lines)));

        $count = 0;
        foreach ($lines as $line) {
            $row = array_combine($header, array_map('trim', str_getcsv($line)));
            if (!$row) continue;

            $cluster = $row['cluster'] ?? 'A';
            $profile = self::CLUSTER_PROFILES[$cluster] ?? self::CLUSTER_PROFILES['A'];

            PosteDonnee::updateOrCreate(
                ['poste_id' => $row['poste_id']],
                [
                    'zone'               => $row['zone'],
                    'quartier'           => $row['quartier'],
                    'cluster'            => $cluster,
                    'latitude'           => isset($row['latitude'])  ? (float)$row['latitude']  : null,
                    'longitude'          => isset($row['longitude']) ? (float)$row['longitude'] : null,

                    'charge_mw'          => $profile['charge_mw'],
                    'capacite_mw'        => $profile['capacite_mw'],
                    'source_reseau'      => 'estime',

                    'niveau_barrage_pct' => $profile['niveau_barrage_pct'],
                    'source_barrage'     => 'manuel',

                    'temperature_c'      => $profile['temperature_c'],
                    'humidite_pct'       => $profile['humidite_pct'],
                    'pluviometrie_norm'  => $profile['pluviometrie_norm'],
                    'temp_max_24h'       => $profile['temperature_c'] + 2.0,
                    'source_meteo'       => 'estime',

                    'age_cable_ans'      => $profile['age_cable_ans'],
                    'maintenance_recente'=> false,
                    'nb_incidents_7j'    => 0,
                    'incidents_moy_3j'   => 0.0,

                    'donnees_updated_at' => now(),
                ]
            );
            $count++;
        }

        $this->command->info("PosteDonneeSeeder : $count postes initialisés.");
    }

    /**
     * Postes de démonstration avec zone + quartier — utilisés quand le CSV est absent.
     */
    private function seedFallback(): void
    {
        $postes = [
            // Abidjan — Cluster A (urbain dense)
            ['poste_id'=>'PST_COC_001','zone'=>'Cocody',    'quartier'=>'Angré',              'cluster'=>'A','charge_mw'=>19.2,'capacite_mw'=>25.0,'age_cable_ans'=>14.0,'nb_incidents_7j'=>2],
            ['poste_id'=>'PST_COC_002','zone'=>'Cocody',    'quartier'=>'Riviera 2',           'cluster'=>'A','charge_mw'=>16.8,'capacite_mw'=>22.0,'age_cable_ans'=>11.5,'nb_incidents_7j'=>0],
            ['poste_id'=>'PST_COC_003','zone'=>'Cocody',    'quartier'=>'II Plateaux',         'cluster'=>'A','charge_mw'=>18.5,'capacite_mw'=>24.0,'age_cable_ans'=>9.0, 'nb_incidents_7j'=>1],
            ['poste_id'=>'PST_ABO_001','zone'=>'Abobo',     'quartier'=>'Abobo Baoulé',        'cluster'=>'A','charge_mw'=>21.0,'capacite_mw'=>25.0,'age_cable_ans'=>18.0,'nb_incidents_7j'=>4],
            ['poste_id'=>'PST_ABO_002','zone'=>'Abobo',     'quartier'=>'Abobo Gare',          'cluster'=>'A','charge_mw'=>17.5,'capacite_mw'=>22.0,'age_cable_ans'=>16.0,'nb_incidents_7j'=>3],
            ['poste_id'=>'PST_ABO_003','zone'=>'Abobo',     'quartier'=>'Avocatier',           'cluster'=>'A','charge_mw'=>14.2,'capacite_mw'=>20.0,'age_cable_ans'=>13.0,'nb_incidents_7j'=>1],
            ['poste_id'=>'PST_YOP_001','zone'=>'Yopougon',  'quartier'=>'Yopougon Selmer',     'cluster'=>'A','charge_mw'=>20.3,'capacite_mw'=>26.0,'age_cable_ans'=>15.5,'nb_incidents_7j'=>3],
            ['poste_id'=>'PST_YOP_002','zone'=>'Yopougon',  'quartier'=>'Yopougon Nord',       'cluster'=>'A','charge_mw'=>18.0,'capacite_mw'=>24.0,'age_cable_ans'=>12.0,'nb_incidents_7j'=>2],
            ['poste_id'=>'PST_YOP_003','zone'=>'Yopougon',  'quartier'=>'Wassakara',           'cluster'=>'A','charge_mw'=>15.5,'capacite_mw'=>20.0,'age_cable_ans'=>20.0,'nb_incidents_7j'=>5],
            ['poste_id'=>'PST_MAR_001','zone'=>'Marcory',   'quartier'=>'Zone 4',              'cluster'=>'A','charge_mw'=>16.0,'capacite_mw'=>22.0,'age_cable_ans'=>13.0,'nb_incidents_7j'=>1],
            ['poste_id'=>'PST_MAR_002','zone'=>'Marcory',   'quartier'=>'Marcory Résidentiel', 'cluster'=>'A','charge_mw'=>14.5,'capacite_mw'=>20.0,'age_cable_ans'=>10.0,'nb_incidents_7j'=>0],
            ['poste_id'=>'PST_KOU_001','zone'=>'Koumassi',  'quartier'=>'Koumassi Remblais',   'cluster'=>'A','charge_mw'=>15.8,'capacite_mw'=>20.0,'age_cable_ans'=>17.0,'nb_incidents_7j'=>3],
            ['poste_id'=>'PST_ADJ_001','zone'=>'Adjamé',    'quartier'=>'Adjamé Blingué',      'cluster'=>'A','charge_mw'=>17.2,'capacite_mw'=>22.0,'age_cable_ans'=>22.0,'nb_incidents_7j'=>4],
            ['poste_id'=>'PST_ATT_001','zone'=>'Attécoubé', 'quartier'=>'Attécoubé Centre',    'cluster'=>'A','charge_mw'=>12.0,'capacite_mw'=>18.0,'age_cable_ans'=>19.0,'nb_incidents_7j'=>2],
            ['poste_id'=>'PST_TRE_001','zone'=>'Treichville','quartier'=>'Treichville Centre', 'cluster'=>'A','charge_mw'=>13.5,'capacite_mw'=>18.0,'age_cable_ans'=>25.0,'nb_incidents_7j'=>3],
            // Cluster B — villes secondaires
            ['poste_id'=>'PST_BOU_001','zone'=>'Bouaké',    'quartier'=>'Koko',                'cluster'=>'B','charge_mw'=>11.0,'capacite_mw'=>18.0,'age_cable_ans'=>16.0,'nb_incidents_7j'=>2],
            ['poste_id'=>'PST_BOU_002','zone'=>'Bouaké',    'quartier'=>'Air France',          'cluster'=>'B','charge_mw'=> 9.5,'capacite_mw'=>16.0,'age_cable_ans'=>14.0,'nb_incidents_7j'=>1],
            ['poste_id'=>'PST_YAM_001','zone'=>'Yamoussoukro','quartier'=>'Habitat',           'cluster'=>'B','charge_mw'=> 8.5,'capacite_mw'=>14.0,'age_cable_ans'=>12.0,'nb_incidents_7j'=>0],
            // Cluster C — zones éloignées
            ['poste_id'=>'PST_SAN_001','zone'=>'San-Pédro', 'quartier'=>'Cité',                'cluster'=>'C','charge_mw'=> 7.0,'capacite_mw'=>12.0,'age_cable_ans'=>18.0,'nb_incidents_7j'=>1],
            ['poste_id'=>'PST_DAL_001','zone'=>'Daloa',     'quartier'=>'Lobia',               'cluster'=>'C','charge_mw'=> 6.5,'capacite_mw'=>11.0,'age_cable_ans'=>20.0,'nb_incidents_7j'=>2],
        ];

        $profiles = self::CLUSTER_PROFILES;
        $count    = 0;

        foreach ($postes as $p) {
            $profile = $profiles[$p['cluster']];
            \App\Models\PosteDonnee::updateOrCreate(
                ['poste_id' => $p['poste_id']],
                [
                    'zone'               => $p['zone'],
                    'quartier'           => $p['quartier'],
                    'cluster'            => $p['cluster'],
                    'charge_mw'          => $p['charge_mw'],
                    'capacite_mw'        => $p['capacite_mw'],
                    'source_reseau'      => 'estime',
                    'niveau_barrage_pct' => $profile['niveau_barrage_pct'],
                    'source_barrage'     => 'manuel',
                    'temperature_c'      => $profile['temperature_c'],
                    'humidite_pct'       => $profile['humidite_pct'],
                    'pluviometrie_norm'  => $profile['pluviometrie_norm'],
                    'temp_max_24h'       => $profile['temperature_c'] + 2.0,
                    'source_meteo'       => 'estime',
                    'age_cable_ans'      => $p['age_cable_ans'],
                    'nb_incidents_7j'    => $p['nb_incidents_7j'],
                    'incidents_moy_3j'   => round($p['nb_incidents_7j'] / 7, 2),
                    'maintenance_recente'=> false,
                    'donnees_updated_at' => now(),
                ]
            );
            $count++;
        }
        $this->command->info("PosteDonneeSeeder fallback : $count postes de démonstration créés.");
    }
}
