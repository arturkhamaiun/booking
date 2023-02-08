<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'Janusz Kowalski',
            'email' => 'janusz@example.com',
        ]);

        User::factory()->create([
            'name' => 'Leszek Kozłowski',
            'email' => 'leszek@example.com',
        ]);

        now()->toPeriod(now()->addYear())->forEach(function (Carbon $date) {
            Vacancy::create([
                'date' => $date,
                'total' => rand(0, 10),
            ]);
        });
    }
}
