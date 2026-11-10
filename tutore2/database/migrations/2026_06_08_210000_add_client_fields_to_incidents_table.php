<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            // Source de la déclaration : 'agent' (terrain) ou 'client' (abonné)
            $table->string('source_type', 10)->default('agent')->after('declared_by');
            // Quartier précis déclaré par le client
            $table->string('quartier', 100)->nullable()->after('zone');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'quartier']);
        });
    }
};
