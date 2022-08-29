<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermanentNumber;
use App\Models\Bet;
class PermanentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $eight = 8;
        $nine = 9;
        $zeroEight = str_pad($eight, 2, '0', STR_PAD_LEFT);
        $zeroNine  = str_pad($nine, 2, '0', STR_PAD_LEFT);
        // PermanentNumber::create(
        //     ['permanent_number' => 00],
        //     ['permanent_number' => 01],
        //     ['permanent_number' => 02],
        //     ['permanent_number' => 03],
        //     ['permanent_number' => 04],
        //     ['permanent_number' => 05],
        //     ['permanent_number' => 06],
        //     ['permanent_number' => 07],
        //     ['permanent_number' => $zeroEight],
        //     ['permanent_number' => $zeroNine],
        //     );

        // small 50 $i = 0; $i <=49; $i++;
        // large 50 $i = 50; $i <=99; $i++;
        for($i = 0; $i <= 99; $i++){
            if($i <= 9){
                PermanentNumber::create(
                    ['permanent_number' => "0$i"]
                    );
            }
            else{
                PermanentNumber::create(
                    ['permanent_number' => $i]
                    );
            }  
        }
        $permanent_number = PermanentNumber::all();
        foreach($permanent_number as $pnum){
            Bet::create(
                ["number"=>$pnum->permanent_number,"user_id" => "1","amount"=>0,"over_amount"=>0,"is_over"=>false,"to_leader"=>false,"to_supervisor"=>false]
            );
        }
    }
}
