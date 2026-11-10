<?php

namespace Database\Seeders;

use App\Models\Alert;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AlertSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Alert::factory()->count(15)->create();
    }
}
