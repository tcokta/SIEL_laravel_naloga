<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            [
                'title'       => 'Postaviti razvojno okolje',
                'description' => 'Namestiti PHP, Composer, Laravel in SQLite za lokalni razvoj.',
                'status'      => 'done',
                'priority'    => 'high',
                'due_date'    => now()->subDays(10)->toDateString(),
            ],
            [
                'title'       => 'Načrtovati strukturo baze podatkov',
                'description' => 'Definirati tabele, relacije in migracije za task tracker.',
                'status'      => 'done',
                'priority'    => 'high',
                'due_date'    => now()->subDays(5)->toDateString(),
            ],
            [
                'title'       => 'Implementirati REST API endpointe',
                'description' => 'CRUD operacije za taske z validacijo in poslovnimi pravili.',
                'status'      => 'in_progress',
                'priority'    => 'high',
                'due_date'    => now()->addDays(2)->toDateString(),
            ],
            [
                'title'       => 'Napisati avtomatizirane teste',
                'description' => 'Feature testi za vse API endpointe, vključno z robnimi primeri.',
                'status'      => 'in_progress',
                'priority'    => 'medium',
                'due_date'    => now()->addDays(3)->toDateString(),
            ],
            [
                'title'       => 'Pripraviti Docker konfiguracijo',
                'description' => 'docker-compose.yml za enostavno zaganjanje aplikacije brez lokalne namestitve PHP.',
                'status'      => 'todo',
                'priority'    => 'low',
                'due_date'    => now()->addDays(7)->toDateString(),
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
