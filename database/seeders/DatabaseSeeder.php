<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            RolesSeeders::class
        ]);

        \App\Models\User::factory(10)->create();

        Task::factory(4)->state(new Sequence(['user_id'=>2], ['user_id'=>4]))->create();
    }
}
