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
        DB::table('dogs')->truncate();
        DB::table('dogs')->insert(['name' => 'Joe']);
        DB::table('dogs')->insert(['name' => 'Jock']);
        DB::table('dogs')->insert(['name' => 'Jackie']);
        DB::table('dogs')->insert(['name' => 'Jane']);
    }
}
