<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlayTime;

class PlayTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        PlayTime::create([
            'start_time' => "09:00",
            'end_time' => '12:30',
            'type' => "AM"
        ]);

        PlayTime::create([
            'start_time' => "02:00",
            'end_time' => '04:30',
            'type' => "PM"
        ]);
    }
}
