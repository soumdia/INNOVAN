<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Déduction horaire de la consommation de chaque client
Schedule::command('simulate:consumption')->hourly();

// Prédiction fenêtre glissante 3H — se relance toutes les heures
Schedule::command('predict:3h')->hourly();
