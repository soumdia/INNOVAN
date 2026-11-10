<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poste_donnees', function (Blueprint $table) {
            $table->id();
            $table->string('poste_id')->unique();
            $table->string('zone');
            $table->string('quartier')->nullable();
            $table->string('cluster', 1)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();

            // ── Réseau (SCADA ou estimé) ──────────────────────────
            $table->decimal('charge_mw', 8, 3)->default(15.0);
            $table->decimal('capacite_mw', 8, 3)->default(24.0);
            $table->string('source_reseau')->default('estime'); // estime | scada | manuel

            // ── Hydraulique ───────────────────────────────────────
            $table->decimal('niveau_barrage_pct', 5, 2)->default(60.0);
            $table->string('source_barrage')->default('manuel');

            // ── Météo (API ou saisie) ─────────────────────────────
            $table->decimal('temperature_c', 5, 2)->default(27.0);
            $table->decimal('humidite_pct', 5, 2)->default(74.0);
            $table->decimal('pluviometrie_norm', 5, 4)->default(0.25);
            $table->decimal('temp_max_24h', 5, 2)->default(29.0);
            $table->string('source_meteo')->default('estime'); // api | manuel | estime
            $table->timestamp('meteo_updated_at')->nullable();

            // ── Infrastructure (saisie agent) ─────────────────────
            $table->decimal('age_cable_ans', 6, 2)->default(14.0);
            $table->boolean('maintenance_recente')->default(false);
            $table->timestamp('derniere_maintenance_at')->nullable();

            // ── Incidents (calculés auto depuis table incidents) ───
            $table->integer('nb_incidents_7j')->default(0);
            $table->decimal('incidents_moy_3j', 5, 2)->default(0.0);

            // ── Métadonnées ───────────────────────────────────────
            $table->timestamp('donnees_updated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poste_donnees');
    }
};
