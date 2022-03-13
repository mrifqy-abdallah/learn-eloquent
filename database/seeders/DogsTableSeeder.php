<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Dog::truncate();

        // \App\Models\Dog::create(['name' => 'Joe']);
        // \App\Models\Dog::create(['name' => 'Jock']);
        // \App\Models\Dog::create(['name' => 'Jackie']);
        // \App\Models\Dog::create(['name' => 'Jane']);

        \App\Models\Dog::factory()
            ->count(50)
            ->create();
    }
}
