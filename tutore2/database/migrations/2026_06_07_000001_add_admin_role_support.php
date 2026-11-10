<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Promouvoir l'agent existant en admin
        DB::table('users')
            ->where('email', 'agent@cie.local')
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'agent@cie.local')
            ->update(['role' => 'agent']);
    }
};
