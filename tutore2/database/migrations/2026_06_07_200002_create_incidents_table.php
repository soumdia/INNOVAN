<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('poste_id');
            $table->string('zone');
            $table->foreignId('declared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('declared_at');
            $table->string('type')->default('technique'); // technique | vandalisme | meteo | autre
            $table->string('description')->nullable();
            $table->string('severity')->default('normal'); // mineur | normal | critique
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['poste_id', 'declared_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
