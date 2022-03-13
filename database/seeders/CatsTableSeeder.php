<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Cat::truncate();
        \App\Models\Cat::create(['name' => 'Joe', 'age' => 5 ]);
        \App\Models\Cat::create(['name' => 'Jock', 'age' => 7 ]);
        \App\Models\Cat::create(['name' => 'Jackie', 'age' => 2 ]);
        \App\Models\Cat::create(['name' => 'Jane', 'age' => 9 ]);
    }
}
