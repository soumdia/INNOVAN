<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            // '5j' = prédiction horizon 5 jours (ancienne méthode)
            // '3h' = prédiction fenêtre glissante 3 heures (nouvelles données réelles)
            $table->string('prediction_mode', 3)->default('5j')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropColumn('prediction_mode');
        });
    }
};
