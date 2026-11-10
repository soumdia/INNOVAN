<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->string('quartier')->nullable()->after('zone');
            $table->string('cause_probable')->nullable()->after('reason');
            $table->string('poste_id')->nullable()->after('cause_probable');
            $table->string('cluster')->nullable()->after('poste_id');
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropColumn(['quartier', 'cause_probable', 'poste_id', 'cluster']);
        });
    }
};
