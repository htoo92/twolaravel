<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NumberType;
class NumberTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numbertypes = [
            'ပုံမှန်',
            'ညီကို',
            'အစုံ',
            'မ(၅၀)',
            'စုံ(၅၀)',
            'ထိပ်စည်း',
            'နောက်ပိတ်',
            'အပါ',
            'စုံစုံ',
            'မမ',
            'စုံမ',
            'မစုံ',
            'ပါဝါ(၁၀)',
            'သေး(၅၀)',
            'ကြီး(၅၀)',
            'နက္ခတ်(၁၀)',
            '(R)',
            'ခွေ',
            '(ပူး)ခွေ',
            'ဘရိတ်',
            'ကွက်စုံ'
        ];
        foreach($numbertypes as $numbertype){
            NumberType::create(['number_types' => $numbertype]);
        }
    } 
} 
