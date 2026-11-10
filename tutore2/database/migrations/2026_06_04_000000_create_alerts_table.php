<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('zone');
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->string('reason')->nullable();
            $table->unsignedTinyInteger('probability')->default(0);
            $table->string('status')->default('predicted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
