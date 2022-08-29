<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NumberType;
use App\Models\Bet;
use App\Models\PermanentNumber;
use App\Models\Number;
use App\Models\ChangeLimit;
use App\Models\BetNumberForMember;
use App\Models\Member;
use App\Models\Order;
use App\Models\OrderDetail;

class TestController extends Controller
{
   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = auth()->user()->id;
        $orders_to_show = Order::with('members')->get();
        $member_to_show = Member::all();
        $context = ['orders' => $orders_to_show, 'members_to_show' => $member_to_show];
        // print_r($context);
        return view('bets.index',$context);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user_id = auth()->user()->id;
        // printf($user_id);
        $numbertype = NumberType::all();
        $bet_all = Bet::all(); 
       
        $firstDatas = Bet::where([
            ["number","<=",'49'],
            ["user_id","=",$user_id]
        ])->get();

        $secondDatas = Bet::where([
            ["number",">=",'50'],
            ["user_id","=",$user_id]
        ])->get();
        $vouchergenerate_member = $this->generatevoucheridformembers();
        $permanent_members = Member::where([["is_member","=",True]])->get();
        $context = ['number_type'=> $numbertype,'firstRows'=>$firstDatas,'secondRows'=>$secondDatas, 'permanent_members'=>$permanent_members, 'voucheridmember'=>$vouchergenerate_member , 'betsall' => $bet_all];        
        
        return view('bets.create',$context);
    }
    private function generatevoucheridformembers()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 20; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $user_id = auth()->user()->id;
        $changelimit_to_calculate_over = ChangeLimit::where([["user_id","=",$user_id]])->first();
        
        $member = new Member();
        $lastMember = null;
        // if(!$request->get('loyalcustomer')){
        //     $member->name = $request->get('normalcustomer');
        //     $member->user_id = auth()->user()->id;
        //     $member->save();    
        // }

        $order = new Order();
        $orderDetail = new OrderDetail();
        /*
            Check VoucherNumber in database, if there is not save in the database of orders table and orderdetails table.
        */
        $ant = Number::where([["number_type_id","=",$request->get('bettype')]])->count();

        if(!Order::where([["voucher_number","=",$request->get('vouchermemberid')]])->exists()){

            // Check the Bet Type

            if($request->get('bettype') == 1){

                // $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                // get the customnumber
                $specific_number = $request->get('customnumber');

                // foreach($specific_number as $fetch_specific_number){

                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $specific_number],
                        ["user_id", '=', $user_id],
                    ])->get();

                    // Check Normal Customer
                    if($request->get('normalcustomer') != null){

                        // Add New Normal customer

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();

                        // Create Bet For Member
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $specific_number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = $request->get('betamount') * ((int)$request->get('percentage') / 100);
                        $bet_number_for_member->save();
                    }

                    // This is for Loyal Customer
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $specific_number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = $request->get('betamount') * ((int)$request->get('percentage') / 100);
                        $bet_number_for_member->save();
                    }

                    // To Update Bet
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $specific_number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount")* (int)$request->get('percentage') / 100)  ? ((int)$request->get("betamount")* (int)$request->get('percentage') / 100 - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
               // }
            }

            // Same Logic Here but with different bet type

            else if($request->get('bettype') == 2){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 3){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 4){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 5){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 6){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();
                for($i = 0; $i <= 9 ; $i++){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $request->get('customnumber').$i],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $request->get('customnumber').$i;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $request->get('customnumber').$i;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
                
            }
            else if($request->get('bettype') == 7){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();
                for($i = 0; $i <= 9 ; $i++){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $i.$request->get('customnumber')],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $i.$request->get('customnumber');
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $i.$request->get('customnumber');
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
                
            }
            else if($request->get('bettype') == 8){
                // get the customnumber
                $numbers = "0123456789";
                $numberArr = str_split($numbers);
                $myNum = null;

                $specific_number = $request->get('customnumber');
                    foreach($numberArr as $num){
                        $myNum = $request->get('customnumber').$num;

                        $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->user_id = auth()->user()->id;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            $bet_number_for_member->amount = $request->get('betamount');
                            $bet_number_for_member->save();
                        }
                        // This is for Loyal Customer
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            $bet_number_for_member->amount = $request->get('betamount');
                            $bet_number_for_member->save();
                        }
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                        }
                        
                        if(strval($specific_number.$num) == strval($num.$specific_number)){
                            continue;
                        }
                        else{
                            $myNum = $num.$specific_number;   
                            $fetch_number_to_change_amount = Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                            ])->get();
                            // Check Normal Customer
                            if($request->get('normalcustomer') != null){
                                // Add New Normal customer
                                $member->name = $request->get('normalcustomer');
                                $member->user_id = auth()->user()->id;
                                $member->save();
                                // Create Bet For Member
                                $bet_number_for_member = new BetNumberForMember();
                                $bet_number_for_member->number = $myNum;
                                $bet_number_for_member->number_type = $request->get('bettype');
                                $bet_number_for_member->user_id =auth()->user()->id;
                                $bet_number_for_member->member_id = $member->id;
                                $bet_number_for_member->amount = $request->get('betamount');
                                $bet_number_for_member->save();
                            }
                            // This is for Loyal Customer
                            else{
                                $lastMember = Member::where([
                                    ["id", '=' , $request->get('loyalcustomer')]
                                ])->first();
                                $bet_number_for_member = new BetNumberForMember();
                                $bet_number_for_member->number = $myNum;
                                $bet_number_for_member->number_type = $request->get('bettype');
                                $bet_number_for_member->user_id =auth()->user()->id;
                                $bet_number_for_member->member_id = $request->get('loyalcustomer');
                                $bet_number_for_member->amount = $request->get('betamount');
                                $bet_number_for_member->save();
                            }
                            // To Update Bet
                            foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                                Bet::where([
                                    ["number", '=', $myNum],
                                    ["user_id", '=', $user_id],
                                    ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                                    "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                            } 
                        }                        
                    }      
            }
            else if($request->get('bettype') == 9){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 10){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 11){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 12){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 13){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 14){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 15){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 16){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 18){
                $input =  $request->get('customnumber');
                $numberArr = str_split($input);
                $myNum = null ;
                for($i =0; $i<=count($numberArr)-1; $i++){

                for($j = 0; $j<$i ; $j++){
                    
                    $myNum = $numberArr[$j].$numberArr[$i];
                    //Logic go here;
                        $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->user_id = auth()->user()->id;
                            $member->percentage = 0;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save();
                        }
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount") * (int)$request->get('percentage') / 100)  ? ((int)$request->get("betamount") * (int)$request->get('percentage') / 100 - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                        }

                    if($numberArr[$i].$numberArr[$j] == $numberArr[$j].$numberArr[$i]){
                    continue;
                    }
                    else{
                        $myNum = $numberArr[$i].$numberArr[$j];

                                                $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->user_id = auth()->user()->id;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save();
                        }
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount") * (int)$request->get('percentage') / 100)  ? ((int)$request->get("betamount") * (int)$request->get('percentage') / 100 - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                        }
                    
                    // $myNum = $numberArr[$i].$numberArr[$j];
                    //Logic go here
                    }
                }
                }
            }
            else if($request->get('bettype') == 19){
                $input =  $request->get('customnumber');
                $numberArr = str_split($input);
                $myNum = null ;
                for($i =0; $i<=count($numberArr)-1; $i++){

                for($j = 0; $j<=$i ; $j++){
                    
                    $myNum = $numberArr[$j].$numberArr[$i];
                    //Logic go here;
                        $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->user_id = auth()->user()->id;
                            $member->percentage = 0;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save();
                        }
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount") * (int)$request->get('percentage') / 100)  ? ((int)$request->get("betamount") * (int)$request->get('percentage') / 100 - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                        }

                    if($numberArr[$i].$numberArr[$j] == $numberArr[$j].$numberArr[$i]){
                    continue;
                    }
                    else{
                        $myNum = $numberArr[$i].$numberArr[$j];

                                                $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->user_id = auth()->user()->id;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save();
                        }
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount") * (int)$request->get('percentage') / 100)  ? ((int)$request->get("betamount") * (int)$request->get('percentage') / 100 - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                        }
                    
                    // $myNum = $numberArr[$i].$numberArr[$j];
                    //Logic go here
                    }
                }
                }
            }
            else if($request->get('bettype') == 21){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }

            // Whatever bettype This Process will continue

            $order->voucher_number =  $request->get('vouchermemberid');
            $order->member_id = $member->id == null ?  $request->get('loyalcustomer') : $member->id;;

            $order->save();
            $orderDetail->order_id = $order->id;
            $orderDetail->amount = $ant == 0 ? (int)$request->get("betamount") * 1 : (int)$request->get("betamount") * $ant;
            $orderDetail->pink_number = 10;
            $orderDetail->save();
        }
        /*
            Same Logic with above

            If the vouchernumber is exist in orders table, then save to orderdetail.
        */
        else{

            $getOrder = Order::where([["voucher_number","=",$request->get('vouchermemberid')]])->first();
            $orderDetail->order_id = $getOrder->id;
            $orderDetail->amount = $ant == 0 ? $request->get('betamount') * 1 : $request->get('betamount') * $ant;
            $orderDetail->pink_number = 10;
            $orderDetail->save();
            if($request->get('bettype') == 1){
                // $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();
                $specific_number = $request->get('customnumber');
                // foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $specific_number],
                        ["user_id", '=', $user_id],
                    ])->get();

                    if($request->get('normalcustomer') != null ){
                        $lastMember = Member::latest()->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $specific_number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount')* (int)$request->get('percentage') / 100;
                        $bet_number_for_member->save();

                    }

                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $specific_number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount')* (int)$request->get('percentage') / 100;
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $specific_number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount")*(int)$request->get('percentage') / 100)  ? ((int)$request->get("betamount")* (int)$request->get('percentage') / 100 - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
               // }
            }
            else if($request->get('bettype') == 2){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();

                    if($request->get('normalcustomer') !=null ){

                        $lastMember = Member::latest()->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();
                        
                       
                        $getBetNumber = BetNumberForMember::where([
                            ["number_type","=",$request->get('bettype')],
                            ["member_id","=",$lastMember->id],
                        ])->get();
    
                        foreach($getBetNumber as $getBet){
                            echo $getBet->amount."\n";
                            BetNumberForMember::where([
                                ["number_type","=",$request->get('bettype')],
                                ["member_id","=",$lastMember->id],
                            ])->update([
                                "amount"=> (int)$getBet->amount +  (int)$request->get('betamount')
                            ]);
                            
                        }

                        // $bet_number_for_member = new BetNumberForMember();
                        // $bet_number_for_member->number = $fetch_specific_number->number;
                        // $bet_number_for_member->number_type = $request->get('bettype');
                        // $bet_number_for_member->user_id =auth()->user()->id;
                        // $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        // $bet_number_for_member->amount = (int)$request->get('betamount');
                        // $bet_number_for_member->save();
                    }
                    
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 3){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 4){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 5){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 6){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();
                for($i = 0; $i <= 9 ; $i++){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $request->get('customnumber').$i],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $request->get('customnumber').$i;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $request->get('customnumber').$i;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }  
            }
            else if($request->get('bettype') == 7){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();
                for($i = 0; $i <= 9 ; $i++){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $i.$request->get('customnumber')],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $i.$request->get('customnumber');
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $i.$request->get('customnumber');
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
                
            }
            else if($request->get('bettype') == 9){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 10){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 11){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 12){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 13){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 14){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 15){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 16){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 18){
                $input =  $request->get('customnumber');
                $numberArr = str_split($input);
                $myNum = null ;
                for($i =0; $i<=count($numberArr)-1; $i++){

                for($j = 0; $j<$i ; $j++){
                    
                    $myNum = $numberArr[$j].$numberArr[$i];
                    //Logic go here;
                        $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->user_id = auth()->user()->id;
                            $member->percentage = 0;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save();
                        }
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount") * (int)$request->get('percentage') / 100)  ? ((int)$request->get("betamount") * (int)$request->get('percentage') / 100 - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                        }

                    if($numberArr[$i].$numberArr[$j] == $numberArr[$j].$numberArr[$i]){
                    continue;
                    }
                    else{
                        $myNum = $numberArr[$i].$numberArr[$j];

                                                $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->user_id = auth()->user()->id;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save();
                        }
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount") * (int)$request->get('percentage') / 100)  ? ((int)$request->get("betamount") * (int)$request->get('percentage') / 100 - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                        }
                    
                    // $myNum = $numberArr[$i].$numberArr[$j];
                    //Logic go here
                    }
                }
                }
            }
            else if($request->get('bettype') == 19){
                $input =  $request->get('customnumber');
                $numberArr = str_split($input);
                $myNum = null ;
                for($i =0; $i<=count($numberArr)-1; $i++){

                for($j = 0; $j<=$i ; $j++){
                    
                    $myNum = $numberArr[$j].$numberArr[$i];
                    //Logic go here;
                        $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->user_id = auth()->user()->id;
                            $member->percentage = 0;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save();
                        }
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount") * (int)$request->get('percentage') / 100)  ? ((int)$request->get("betamount") * (int)$request->get('percentage') / 100 - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                        }

                    if($numberArr[$i].$numberArr[$j] == $numberArr[$j].$numberArr[$i]){
                    continue;
                    }
                    else{
                        $myNum = $numberArr[$i].$numberArr[$j];

                                                $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->user_id = auth()->user()->id;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            $bet_number_for_member->amount = $request->get('betamount') * (int)$request->get('percentage') / 100;
                            $bet_number_for_member->save();
                        }
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount") * (int)$request->get('percentage') / 100)  ? ((int)$request->get("betamount") * (int)$request->get('percentage') / 100 - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                        }
                    
                    // $myNum = $numberArr[$i].$numberArr[$j];
                    //Logic go here
                    }
                }
                }
            }
            else if($request->get('bettype') == 21){
                $specific_number = Number::where([["number_type_id","=",$request->get('bettype')]])->get();

                foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $fetch_specific_number->number],
                        ["user_id", '=', $user_id],
                    ])->get();
                    if($request->get('normalcustomer')){

                        $member->name = $request->get('normalcustomer');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>(int)$request->get('betamount') + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"user_id"=>$user_id]);
                    }
                }
            }
        }
        $orderdetails_with_orders = OrderDetail::all();
        $orderAll = Order::where([
            ["member_id","=", !$lastMember ? $member->id : $lastMember->id ]
        ])->get();
        $context =[];
        $array = null;
        
        foreach($orderAll as $order){
            foreach($orderdetails_with_orders as $ord){
                if($order->id == $ord->order_id){
                    $context[] = ["order"=>$order,"orderDetail"=>$ord];
                    $array = array_merge($order->toArray(), $ord->toArray());
                }
            }
        }
        $getMyOrder = Order::where([
            ["voucher_number","=",$request->get('vouchermemberid')]
        ])->first();

        $myOrderDetail = OrderDetail::where([
            ["order_id","=", $getMyOrder->id]
        ])->with('orders')->get();

        $firstDatas = Bet::where([
            ["number","<=",'49'],
            ["user_id","=",$user_id]
        ])->get();
        $betall = Bet::all();
        $secondDatas = Bet::where([
            ["number",">=",'50'],
            ["user_id","=",$user_id]
        ])->get();

        $contct = [
            "orderDetail" => $myOrderDetail,
            "fristCol" =>$firstDatas,
            "secCol" =>  $secondDatas,
            "betsall" => $betall
        ];
        return response($contct, 200);
        // return response($array, 200);              
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order_details = OrderDetail::find($id);
        $context = ['orderdetails' => $order_details];
        return view('bets.show',$context);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $betNumberWithMember = BetNumberForMember::where([
            ["number_type","=",$request->get("hidden_number_type")],
            ["member_id", "=",$request->get("hidden_member_id")]
        ]);


        // $getMyOrder = Order::where([
        //     ["voucher_number","=",$request->get('vouchermemberid')]
        // ])->first();
       

        // $myOrderDetail = OrderDetail::where([
        //     ["order_id","=", $getMyOrder->id]
        // ])->with('orders')->get();

        // $firstDatas = Bet::where([
        //     ["number","<=",'49'],
        //     ["user_id","=",$user_id]
        // ])->get();

        // $secondDatas = Bet::where([
        //     ["number",">=",'50'],
        //     ["user_id","=",$user_id]
        // ])->get();

        $contct = [
            "updateBetData" => $betNumberWithMember,
        ];
        return response($contct, 200);
        // return view('bets.edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
