<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(3)->create();
        \App\Models\Survey::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Administrator',
        //     'email' => 'admin@admin.com',
        // ]);
    }
}
