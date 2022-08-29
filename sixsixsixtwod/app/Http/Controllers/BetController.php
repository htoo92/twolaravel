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
use App\Models\User;
class BetController extends Controller
{
   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct(){ 
        $this->middleware('permission:bet-list|bet-create|bet-edit|bet-delete', ['only' => ['index','store']]);
         $this->middleware('permission:bet-create', ['only' => ['create','store']]);
         $this->middleware('permission:bet-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:bet-delete', ['only' => ['destroy']]); 
        $this->middleware("mysession");
    }
    public function index()
    {
        $user_id = auth()->user()->id;
        // $roles = User::findOrFail($current_user_id);
        $current_user = User::findOrFail($user_id);
        $roles = $current_user->getRoleNames();
        if (strcasecmp($roles[0], "member") == 0) {
            $orders_to_show = Order::with('members')->get();
            // $orderDetails = OrderDetail::all();
            error_log($orders_to_show[0]->id);
            $orderDetails = OrderDetail::where([
                ['order_id','=',$orders_to_show[0]->id],
            ])->get();
            error_log('Some message here 2');
            // $member_to_show = Member::all();
            $member_to_show = Member::where([
                ['user_id','=',$current_user->id],
            ])->get();
            error_log('Some message here.');
            $context = ['orders' => $orders_to_show, 'members_to_show' => $member_to_show,"orderDetails"=>$orderDetails];
            // print_r($context);
            return view('bets.index',$context);
        }
        if (strcasecmp($roles[0], "supervisor") == 0) {
            $orders_to_show = Order::with('members')->get();
            $orderDetails = OrderDetail::all();
            $member_to_show = Member::all();
            $context = ['orders' => $orders_to_show, 'members_to_show' => $member_to_show,"orderDetails"=>$orderDetails];
            // print_r($context);
            return view('bets.index',$context);
        }
        if (strcasecmp($roles[0], "admin") == 0) {
            $orders_to_show = Order::with('members')->get();
            $orderDetails = OrderDetail::all();
            $member_to_show = Member::all();
            $context = ['orders' => $orders_to_show, 'members_to_show' => $member_to_show,"orderDetails"=>$orderDetails];
            // print_r($context);
            return view('bets.index',$context);
        }
       
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {  
        $user_id = auth()->user()->id;
        $report_tp = auth()->user()->report_to;
        $display_owner_details = Bet::where([['user_id','=',$user_id]])->get();
        $report_total_amount = Bet::where('user_id',$user_id)->sum('final_amount');
        $over_amount_total = Bet::where('user_id',$user_id)->sum('over_amount');
        $return_amount_total = Bet::where('user_id',$user_id)->sum('return_amount');
        $isoff_amount_total = Bet::where('user_id',$user_id)->sum('off_return_amount');
        $changelimit = ChangeLimit::all();
        // printf($user_id);
        $numbertype = NumberType::all();
        // $bet_all = Bet::all();
        $bet_all = Bet::where([
            ['user_id','=',$user_id],
        ])->get(); 
       
        $firstDatas = Bet::where([
            ["number","<=",'49'],
            ["user_id","=",$user_id]
        ])->get(); 

        $secondDatas = Bet::where([
            ["number",">=",'50'],
            ["user_id","=",$user_id]
        ])->get();
        $vouchergenerate_member = $this->generatevoucheridformembers();
        $permanent_members = Member::where([
            ["user_id","=",$user_id],
            ["is_member","=",True]
            ])->get();
        $context = ['number_type'=> $numbertype,'firstRows'=>$firstDatas,'secondRows'=>$secondDatas, 'permanent_members'=>$permanent_members, 'voucheridmember'=>$vouchergenerate_member , 'betsall' => $bet_all, 'reportTo'=>$report_tp,'displayOwnerDetails' => $display_owner_details,'reporttotalamount' => $report_total_amount,'changelimit'=>$changelimit, 'return_amount' => $return_amount_total, 'off_return_amount' => $isoff_amount_total];        
        
        return view('bets.create',$context);
    }
    private function generatevoucheridformembers()
    {
        $characters = 'A0B0E1D';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 6; $i++) {
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
        
        $number_type_for_bet_list_preview = NumberType::where([
            ['id','=',$request->get('bettype')],
        ])->first();
        $order = new Order();
        $orderDetail = new OrderDetail();
        /*
            Check VoucherNumber in database, if there is not save in the database of orders table and orderdetails table.
        */
        $ant = Number::where([["number_type_id","=",$request->get('bettype')]])->count();

        if(!Order::where([["voucher_number","=",$request->get('vouchermemberid')]])->exists()){

            // Check the Bet Type

            if($request->get('bettype') == 1){
                // get the customnumber
                $specific_number = $request->get('customnumber');
                $splitspecific_number = explode(".",$specific_number);
                $j =  count($splitspecific_number);
                for($i = 0; $i < $j; $i++){
                    // echo $splitspecific_number[$i].'<br />';
                    // foreach($specific_number as $fetch_specific_number){
                        $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $splitspecific_number[$i]],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                        // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->percentage = $request->get('percentage');
                            $member->user_id = auth()->user()->id;
                            $member->save();
                        // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $splitspecific_number[$i];
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
                            $bet_number_for_member->number = $splitspecific_number[$i];
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            $bet_number_for_member->amount = $request->get('betamount');
                            $bet_number_for_member->save();
                        }
    
                        // To Update Bet
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - ((int)$request->get('betamount') * 1);
                        $total_include_percentage = ((int)$request->get('betamount') * 1);
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $splitspecific_number[$i]],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                                "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                                "user_id"=>$user_id]);
                        }
                   // }
                }
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - ((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100);
                        $bet_number_for_member->amount = $request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=> $total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $request->get('customnumber').$i;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $i.$request->get('customnumber');
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                            $member->percentage = $request->get('percentage');
                            $member->user_id = auth()->user()->id;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        $total_include_percentage = (int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                                "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                                "user_id"=>$user_id]);
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
                                $member->name = $request->get('percentage');
                                $member->user_id = auth()->user()->id;
                                $member->save();
                                // Create Bet For Member
                                $bet_number_for_member = new BetNumberForMember();
                                $bet_number_for_member->number = $myNum;
                                $bet_number_for_member->number_type = $request->get('bettype');
                                $bet_number_for_member->user_id =auth()->user()->id;
                                $bet_number_for_member->member_id = $member->id;
                                // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                                $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                                // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                                $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                                $bet_number_for_member->save();
                            }
                            // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $total_include_percentage = (int)$request->get('betamount') * 1;
                            // To Update Bet
                            foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                                Bet::where([
                                    ["number", '=', $myNum],
                                    ["user_id", '=', $user_id],
                                    ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                    "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                                    "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                                    "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 17){
                // get the customnumber
                $specific_number = $request->get('customnumber');
                $splitBySlash = explode("/",$specific_number);
                $splitByR = explode("R",$splitBySlash[1]);
                $splitBydot = explode(".",$splitBySlash[0]);
                $splitByDotLoop = count($splitBydot);
                $splitnumber = str_split($splitBydot[0]);
                for($y = 0; $y < $splitByDotLoop; $y++){
                    // echo $splitBydot[$y].' is '.$splitByR[0].'<br />';
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $splitBydot[$y]],
                        ["user_id", '=', $user_id],
                    ])->get();
                    // Check Normal Customer
                    if($request->get('normalcustomer') != null){
                        // Add New Normal customer
                        $member->name = $request->get('normalcustomer');
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();
                        // Create Bet For Member
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $splitBydot[$y];
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$rAmount[0] * 1) - (((int)$rAmount[0] * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$splitByR[0] * 1;
                        $bet_number_for_member->save();
                    }
                    // This is for Loyal Customer
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $splitBydot[$y];
                        // $bet_number_for_member->number = $r_number[1].$r_number[0];
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        // $bet_number_for_member->amount = ((int)$rAmount[0] * 1) - (((int)$rAmount[0] * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$splitByR[0] * 1;
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = $splitByR[0] * 1;
                    $total_include_percentage_one = $splitByR[0] * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $splitBydot[$y]],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$splitByR[0])  ? ((int)$splitByR[0] - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$splitByR[0])  ? ((int)$splitByR[0] - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
                    }
                }
                for($t = 0; $t < $splitByDotLoop; $t++ ){
                    $splitnumber = str_split($splitBydot[$t]);
                    // echo $splitnumber[1].$splitnumber[0].' is '.$splitByR[1].'<br />';
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $splitnumber[1].$splitnumber[0]],
                        ["user_id", '=', $user_id],
                    ])->get();
                    // Check Normal Customer
                    if($request->get('normalcustomer') != null){
                        // Add New Normal customer
                        $member->name = $request->get('normalcustomer');
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();
                        // Create Bet For Member
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $splitnumber[1].$splitnumber[0];
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$rAmount[0] * 1) - (((int)$rAmount[0] * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$splitByR[1] * 1;
                        $bet_number_for_member->save();
                    }
                    // This is for Loyal Customer
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $splitnumber[1].$splitnumber[0];
                        // $bet_number_for_member->number = $r_number[1].$r_number[0];
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        // $bet_number_for_member->amount = ((int)$rAmount[0] * 1) - (((int)$rAmount[0] * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$splitByR[1] * 1;
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = $splitByR[1] * 1;
                    $total_include_percentage_one = $splitByR[1] * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $splitnumber[1].$splitnumber[0]],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$splitByR[1])  ? ((int)$splitByR[1] - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$splitByR[1])  ? ((int)$splitByR[1] - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
                    }
                }
               // }
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
                            $member->percentage = $request->get('percentage');
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage =(int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                                "user_id"=>$user_id]);
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
                            $member->percentage = $request->get('percentage');
                            $member->user_id = auth()->user()->id;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage = (int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                                "user_id"=>$user_id]);
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
                            $member->percentage = $request->get('percentage');
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage =(int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount")) ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                            $member->percentage = $request->get('percentage');
                            $member->user_id = auth()->user()->id;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage = (int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount")) ? ((int)$request->get("betamount")- $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                        }
                    
                    // $myNum = $numberArr[$i].$numberArr[$j];
                    //Logic go here
                    }
                }
                }
            }
            else if($request->get('bettype') == 20){
                $constant = "1234567890";
                $array = str_split($constant);
                $myNum = null ;
                for($i=0;$i< count($array);$i++){
                    for($j=0;$j<= $i;$j++ ){
                        if($array[$i] + $array[$j] == $request->get('customnumber') or $array[$i] + $array[$j] == "1".$request->get('customnumber')){
                            //Logic go here;
                            $myNum = $array[$i].$array[$j];
                        $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->percentage = $request->get('percentage');
                            $member->user_id = auth()->user()->id;
                            $member->percentage = 0;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $request->get('customnumber');
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $request->get('customnumber');
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage = (int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount")) ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                        }

                        if($array[$i].$array[$j] == $array[$j].$array[$i]){
                            continue;
                        }
                        else{
                            $myNum_else = $array[$j].$array[$i];
                            $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum_else],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $member->name = $request->get('normalcustomer');
                            $member->percentage = $request->get('percentage');
                            $member->user_id = auth()->user()->id;
                            $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $request->get('customnumber');
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $member->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $request->get('customnumber');
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage = (int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum_else],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount")) ? ((int)$request->get("betamount")- $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                        }
                    }
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
                        $member->percentage = $request->get('percentage');
                        $member->user_id = auth()->user()->id;
                        $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $member->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                    }
                }
            }

            // Whatever bettype This Process will continue
            // First Order
            $order->voucher_number =  $request->get('vouchermemberid');
            $order->member_id = $member->id == null ?  $request->get('loyalcustomer') : $member->id;;
            $order->save();
            $orderDetail->order_id = $order->id;
            // $orderDetail->amount = $ant == 0 ? (int)$request->get("betamount") * 1 : (int)$request->get("betamount") * $ant;
            // $orderDetail->amount = $ant == 0 ? ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100)) * 1 : ((int)$request->get('betamount') * $ant) - (((int)$request->get('betamount') * $ant) * ((int)$request->get('percentage') / 100));
            // $orderDetail->amount = $ant == 0 ? ((int)$request->get('betamount') * 1) * 1 : ((int)$request->get('betamount') * $ant); 
            $orderDetail->amount = $ant == 0 ? ((int)$request->get('betamount') * 1) * 1 : ((int)$request->get('betamount')); 
            // $orderDetail->pink_number = $request->get('customnumber');
            $orderDetail->pink_number = $request->get("customnumber") == null ? "—-"  : $request->get("customnumber") ;
            $orderDetail->number_type = $number_type_for_bet_list_preview->number_types;
            $orderDetail->save();
        }
        /*
            Same Logic with above

            If the vouchernumber is exist in orders table, then save to orderdetail.
        */
        else{

            $getOrder = Order::where([["voucher_number","=",$request->get('vouchermemberid')]])->first();
            $orderDetail->order_id = $getOrder->id;
            // $orderDetail->amount = $ant == 0 ? $request->get('betamount') * 1 : $request->get('betamount') * $ant;
            // $orderDetail->amount = $ant == 0 ? ((int)$request->get('betamount') * 1) * 1 : ((int)$request->get('betamount') * $ant);
            $orderDetail->amount = $ant == 0 ? ((int)$request->get('betamount') * 1) * 1 : ((int)$request->get('betamount'));
            
            // $orderDetail->pink_number = $request->get('customnumber');
            $orderDetail->pink_number = $request->get("customnumber") == null ? "—-"  : $request->get("customnumber");
            $orderDetail->number_type = $number_type_for_bet_list_preview->number_types;
            $orderDetail->save();
            if($request->get('bettype') == 1){
                // get the customnumber
                $specific_number = $request->get('customnumber');
                $splitInput = explode(".",$specific_number);
                $j =  count($splitInput);
                for($i = 0; $i < $j; $i++){
                  // foreach($specific_number as $fetch_specific_number){
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $splitInput[$i]],
                        ["user_id", '=', $user_id],
                    ])->get();
                    // Check Normal Customer
                    if($request->get('normalcustomer') != null){
                        // Add New Normal customer
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();

                        // Create Bet For Member
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $splitInput[$i];
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        $bet_number_for_member->amount = $request->get('betamount');

                        $bet_number_for_member->save();
                    }
                    // This is for Loyal Customer
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();

                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $splitInput[$i];
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = $request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = ((int)$request->get('betamount') * 1);
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $splitInput[$i]],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                    }  
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
                    if($request->get('normalcustomer')){
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - ((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100);
                        $bet_number_for_member->amount = $request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=> $total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,
                            "final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,
                            "user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = $request->get('betamount');
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $request->get('customnumber').$i;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $i.$request->get('customnumber');
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                            $lastMember = Member::orderBy('id', 'desc')->first();
                            // $member->name = $request->get('normalcustomer');
                            // $member->percentage = $request->get('percentage');
                            // $member->user_id = auth()->user()->id;
                            // $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $lastMember->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        $total_include_percentage = (int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                                $lastMember = Member::orderBy('id', 'desc')->first();
                                // $member->name = $request->get('normalcustomer');
                                // $member->name = $request->get('percentage');
                                // $member->user_id = auth()->user()->id;
                                // $member->save();
                                // Create Bet For Member
                                $bet_number_for_member = new BetNumberForMember();
                                $bet_number_for_member->number = $myNum;
                                $bet_number_for_member->number_type = $request->get('bettype');
                                $bet_number_for_member->user_id =auth()->user()->id;
                                $bet_number_for_member->member_id = $lastMember->id;
                                // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                                $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                                // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                                $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                                $bet_number_for_member->save();
                            }
                            // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $total_include_percentage = (int)$request->get('betamount') * 1;
                            // To Update Bet
                            foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                                Bet::where([
                                    ["number", '=', $myNum],
                                    ["user_id", '=', $user_id],
                                    ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                    "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                    }
                }
            }
            else if($request->get('bettype') == 17){
                // get the customnumber
                $specific_number = $request->get('customnumber');
                $splitBySlash = explode("/",$specific_number);
                $splitByR = explode("R",$splitBySlash[1]);
                $splitBydot = explode(".",$splitBySlash[0]);
                $splitByDotLoop = count($splitBydot);
                $splitnumber = str_split($splitBydot[0]);
                for($y = 0; $y < $splitByDotLoop; $y++){
                    // echo $splitBydot[$y].' is '.$splitByR[0].'<br />';
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $splitBydot[$y]],
                        ["user_id", '=', $user_id],
                    ])->get();
                    // Check Normal Customer
                    if($request->get('normalcustomer') != null){
                        // Add New Normal customer
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // Create Bet For Member
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $splitBydot[$y];
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        $bet_number_for_member->amount = (int)$splitByR[0] * 1;
                        $bet_number_for_member->save();
                    }
                    // This is for Loyal Customer
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $splitBydot[$y];
                        // $bet_number_for_member->number = $r_number[1].$r_number[0];
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$splitByR[0] * 1;
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$splitByR[0] * 1;
                    $total_include_percentage_one = (int)$splitByR[0] * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $splitBydot[$y]],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$splitByR[0])  ? ((int)$splitByR[0] - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$splitByR[0])  ? ((int)$splitByR[0] - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                    }
                }
                for($t = 0; $t < $splitByDotLoop; $t++ ){
                    $splitnumber = str_split($splitBydot[$t]);
                    // echo $splitnumber[1].$splitnumber[0].' is '.$splitByR[1].'<br />';
                    $fetch_number_to_change_amount = Bet::where([
                        ["number", '=', $splitnumber[1].$splitnumber[0]],
                        ["user_id", '=', $user_id],
                    ])->get();
                    // Check Normal Customer
                    if($request->get('normalcustomer') != null){
                        // Add New Normal customer
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // Create Bet For Member
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $splitnumber[1].$splitnumber[0];
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        $bet_number_for_member->amount = (int)$splitByR[1] * 1;
                        $bet_number_for_member->save();
                    }
                    // This is for Loyal Customer
                    else{
                        $lastMember = Member::where([
                            ["id", '=' , $request->get('loyalcustomer')]
                        ])->first();
                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $splitnumber[1].$splitnumber[0];
                        // $bet_number_for_member->number = $r_number[1].$r_number[0];
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $request->get('loyalcustomer');
                        $bet_number_for_member->amount = (int)$splitByR[1] * 1;
                        $bet_number_for_member->save();
                    }
                    $total_include_percentage = (int)$splitByR[1] * 1;
                    $total_include_percentage_one = (int)$splitByR[1] * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $splitnumber[1].$splitnumber[0]],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$splitByR[1])  ? ((int)$splitByR[1] - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$splitByR[1])  ? ((int)$splitByR[1] - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                    }
                }
               // }
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
                            $lastMember = Member::orderBy('id', 'desc')->first();
                            // $member->name = $request->get('normalcustomer');
                            // $member->percentage = $request->get('percentage');
                            // $member->user_id = auth()->user()->id;
                            // $member->percentage = 0;
                            // $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $lastMember->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage =(int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                            $lastMember = Member::orderBy('id', 'desc')->first();
                            // $member->name = $request->get('normalcustomer');
                            // $member->percentage = $request->get('percentage');
                            // $member->user_id = auth()->user()->id;
                            // $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $lastMember->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage = (int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                            $lastMember = Member::orderBy('id', 'desc')->first();
                            // $member->name = $request->get('normalcustomer');
                            // $member->percentage = $request->get('percentage');
                            // $member->user_id = auth()->user()->id;
                            // $member->percentage = 0;
                            // $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $lastMember->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage =(int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount")) ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
                            $lastMember = Member::orderBy('id', 'desc')->first();
                            // $member->name = $request->get('normalcustomer');
                            // $member->percentage = $request->get('percentage');
                            // $member->user_id = auth()->user()->id;
                            // $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $myNum;
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $lastMember->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage = (int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount")) ? ((int)$request->get("betamount")- $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                        }
                    
                    // $myNum = $numberArr[$i].$numberArr[$j];
                    //Logic go here
                    }
                }
                }
            }
            else if($request->get('bettype') == 20){
                $constant = "1234567890";
                $array = str_split($constant);
                $myNum = null ;
                for($i=0;$i< count($array);$i++){
                    for($j=0;$j<= $i;$j++ ){
                        if($array[$i] + $array[$j] == $request->get('customnumber') or $array[$i] + $array[$j] == "1".$request->get('customnumber')){
                            //Logic go here;
                            $myNum = $array[$i].$array[$j];
                        $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $lastMember = Member::orderBy('id', 'desc')->first();
                            // $member->name = $request->get('normalcustomer');
                            // $member->percentage = $request->get('percentage');
                            // $member->user_id = auth()->user()->id;
                            // $member->percentage = 0;
                            // $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $request->get('customnumber');
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $lastMember->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $request->get('customnumber');
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage = (int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount")) ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                        }

                        if($array[$i].$array[$j] == $array[$j].$array[$i]){
                            continue;
                        }
                        else{
                            $myNum_else = $array[$j].$array[$i];
                            $fetch_number_to_change_amount = Bet::where([
                            ["number", '=', $myNum_else],
                            ["user_id", '=', $user_id],
                        ])->get();
                        // Check Normal Customer
                        if($request->get('normalcustomer') != null){
                            // Add New Normal customer
                            $lastMember = Member::orderBy('id', 'desc')->first();
                            // $member->name = $request->get('normalcustomer');
                            // $member->percentage = $request->get('percentage');
                            // $member->user_id = auth()->user()->id;
                            // $member->save();
                            // Create Bet For Member
                            $bet_number_for_member = new BetNumberForMember();
                            // $percentage_calc = 
                            $bet_number_for_member->number = $request->get('customnumber');
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $lastMember->id;
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save(); 
                        }
                        // This is for Loyal Customer 
                        else{
                            $lastMember = Member::where([
                                ["id", '=' , $request->get('loyalcustomer')]
                            ])->first();
                            $bet_number_for_member = new BetNumberForMember();
                            $bet_number_for_member->number = $request->get('customnumber');
                            $bet_number_for_member->number_type = $request->get('bettype');
                            $bet_number_for_member->user_id =auth()->user()->id;
                            $bet_number_for_member->member_id = $request->get('loyalcustomer');
                            // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                            $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                            $bet_number_for_member->save();
                        }
                        // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $total_include_percentage = (int)$request->get('betamount') * 1;
                        // To Update Bet
                        foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                            Bet::where([
                                ["number", '=', $myNum_else],
                                ["user_id", '=', $user_id],
                                ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                                "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount")) ? ((int)$request->get("betamount")- $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
                        }
                    }
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
                        $lastMember = Member::orderBy('id', 'desc')->first();
                        // $member->name = $request->get('normalcustomer');
                        // $member->percentage = $request->get('percentage');
                        // $member->user_id = auth()->user()->id;
                        // $member->save();


                        $bet_number_for_member = new BetNumberForMember();
                        $bet_number_for_member->number = $fetch_specific_number->number;
                        $bet_number_for_member->number_type = $request->get('bettype');
                        $bet_number_for_member->user_id =auth()->user()->id;
                        $bet_number_for_member->member_id = $lastMember->id;
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
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
                        // $bet_number_for_member->amount = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                        $bet_number_for_member->amount = (int)$request->get('betamount') * 1;
                        $bet_number_for_member->save();
                    }
                    // $total_include_percentage = ((int)$request->get('betamount') * 1) - (((int)$request->get('betamount') * 1) * ((int)$request->get('percentage') / 100));
                    $total_include_percentage = (int)$request->get('betamount') * 1;
                    foreach($fetch_number_to_change_amount as $fetch_number_tochangeamount){
                        Bet::where([
                            ["number", '=', $fetch_number_tochangeamount->number],
                            ["user_id", '=', $user_id],
                            ])->update(['number'=>$fetch_number_tochangeamount->number,"amount"=>$total_include_percentage + (int)$fetch_number_tochangeamount->amount,
                            "over_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->over_amount) : $fetch_number_tochangeamount->over_amount,"final_amount"=>($changelimit_to_calculate_over->limit_amount < (int)$request->get("betamount"))  ? ((int)$request->get("betamount") - $changelimit_to_calculate_over->limit_amount + $fetch_number_tochangeamount->final_amount) : $fetch_number_tochangeamount->final_amount,"user_id"=>$user_id]);
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
        $betall = Bet::where([
            ["user_id","=",$user_id]
        ])->get();
        $secondDatas = Bet::where([
            ["number",">=",'50'],
            ["user_id","=",$user_id]
        ])->get();

        $contct = [
            "member_id" => $request->get('normalcustomer') != null ? Member::orderBy('id', 'desc')->first() :  Member::where([
                ["id", '=' , $request->get('loyalcustomer')]
            ])->first(),
            "orderDetail" => $myOrderDetail,
            "fristCol" =>$firstDatas,
            "secCol" =>  $secondDatas,
            "betsall" => $betall,
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
        $members = Member::all();

        // $bet_number     = BetNumberForMember::all();
        $bet_number = OrderDetail::where([
            ['order_id','=',$id],
        ])->get();
        
        $orders = Order::where([
            ["id","=",$id],
        ])->first();

        $totalAmount = OrderDetail::where([
            ["order_id","=",$id]
        ])->sum('amount');

        $bet_number_for_each_member = Member::where([
            ['id','=',$orders->member_id],
        ])->first();

        // $order_details = OrderDetail::all();

        $context = ['members_to_show'=>$members, 'orders_to_show'=>$orders, 'bet_number'=>$bet_number,'bet_number_for_each_member'=>$bet_number_for_each_member,'totalAmount'=>$totalAmount];
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

        $secondDatas = Bet::where([
            ["number",">=",'50'],
            ["user_id","=",$user_id]
        ])->get();

        $contct = [
            "orderDetail" => $myOrderDetail,
            "fristCol" =>$firstDatas,
            "secCol" =>  $secondDatas
        ];
        return response($contct, 200);
        return view('bets.edit');
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
    public function destroy(Request $request)
    {
        $numbers = "0123456789";
        $current_login_user_id = auth()->user()->id;
        $numbers_type = NumberType::where([
            ["number_types","=",$request->get("number_type_id")]
        ])->first();
        $numbers = Number::where([
            ["number_type_id","=",$numbers_type->id]
        ])->get();
        $bet_number_for_members = Bet::where([
            ["user_id","=",$current_login_user_id]
        ])->get();
        $over_delete = ChangeLimit::where([
            ["user_id","=",$current_login_user_id]
        ])->first();

        $amount = (int)$request->get("hidden_amount");
        if($request->get("number_type_id") == 'N'){
           
                foreach($bet_number_for_members as $bet_member){
                    if($request->get("customnumber") == $bet_member->number){
                        Bet::where([
                            ["number","=",$request->get("customnumber")],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            
        }
        if($request->get("number_type_id") == 'TO'){
            for($i = 0; $i <= 9 ; $i++){
                $fetch_delete_amount = Bet::where([
                    ["number", '=', $request->get('customnumber').$i],
                    ["user_id", '=', $current_login_user_id],
                ])->get();
                foreach($bet_number_for_members as $bet_member){
                    foreach($fetch_delete_amount as $fetch_delete){
                       if($fetch_delete->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$fetch_delete->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        }
        if($request->get("number_type_id") == 'EN'){
            for($i = 0; $i <= 9 ; $i++){
                $fetch_delete_amount = Bet::where([
                    ["number", '=', $i.$request->get('customnumber')],
                    ["user_id", '=', $current_login_user_id],
                ])->get();
                foreach($bet_number_for_members as $bet_member){
                    foreach($fetch_delete_amount as $fetch_delete){
                       if($fetch_delete->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$fetch_delete->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        }
        if($request->get("number_type_id") == 'WN'){
            $numberArr = str_split("0123456789");
            $myNum = null;
            $specific_number = $request->get('customnumber');
            foreach($numberArr as $num){
                $myNum = $request->get('customnumber').(int)$num;
                error_log($myNum);
                // $myNum = $specific_number.$num;
                $fetch_delete_amount = Bet::where([
                    ["number", '=', $myNum],
                    ["user_id", '=', $current_login_user_id],
                ])->get();
                foreach($bet_number_for_members as $bet_member){
                    
                    foreach($fetch_delete_amount as $fetch_delete){
                        
                    if($myNum == $bet_member->number){
                        Bet::where([
                            ["number","=",$myNum],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                        ]);
                    } 
                }
                if( $request->get('customnumber').(int)$num ==  (int)$num.$request->get('customnumber')){
                    continue;
                }
                else{ 
                    $gg = (int)$num.$request->get('customnumber');  
                    $fetch_delete_amount = Bet::where([
                        ["number", '=', $gg],
                        ["user_id", '=', $current_login_user_id],
                    ])->get(); 
                    
                    foreach($fetch_delete_amount as $fetch_delete){   
                        if($gg == $bet_member->number){
                            Bet::where([
                                ["number","=",$gg],
                                ["user_id","=",$current_login_user_id],
                                ])->update(
                                    ["amount"=> $bet_member->amount - $amount,
                                    "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                    "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                        } 
                    }
                }
            }
            }
            }
        if($numbers_type->id == '2'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '3'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '4'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '5'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        
        if($numbers_type->id == '7'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '8'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '9'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '10'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '11'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '12'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '13'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '14'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '15'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '16'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '17'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '18'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '19'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '20'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }
        if($numbers_type->id == '21'){
            foreach($numbers as $number){
                foreach($bet_number_for_members as $bet_member){
                    if($number->number == $bet_member->number){
                        Bet::where([
                            ["number","=",$number->number],
                            ["user_id","=",$current_login_user_id],
                            ])->update(
                                ["amount"=> $bet_member->amount - $amount,
                                "over_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount,
                                "final_amount"=> $amount > $over_delete ->limit_amount ?  $bet_member->over_amount - ($amount - $over_delete->limit_amount) : $bet_member->over_amount
                            ]);
                    }
                }
            }
        }

        $orderdetails_with_orders = OrderDetail::all();
        $orderAll = Order::where([
            ["member_id","=", $request->get("hidden_member_id")]
        ])->get();
        $context =[];
        $array = null;
        
        foreach($orderAll as $order){
            foreach($orderdetails_with_orders as $ord){
                if($order->id == $ord->order_id){
                    $context[] = ["order"=>$order,"orderDetail"=>$ord];
                    $array = array_merge($order->toArray(), $ord->toArray());

                    OrderDetail::where([
                        ["id","=",$request->get("order_detail_id")],
                        ['order_id','=',$request->get('vouchermemberid')]
                    ])->update([
                        "amount"=>$ord->amount - $amount
                    ]);
                
                }
            }
        }
        $getMyOrder = Order::where([
            ["id","=",$request->get('vouchermemberid')]
        ])->first();

        $myOrderDetail = OrderDetail::where([
            ["order_id","=", $getMyOrder->id]
        ])->with('orders')->get();

        $firstDatas = Bet::where([
            ["number","<=",'49'],
            ["user_id","=",$current_login_user_id]
        ])->get();
        $betall = Bet::where([
            ["user_id","=",$current_login_user_id]
        ])->get();
        $secondDatas = Bet::where([
            ["number",">=",'50'],
            ["user_id","=",$current_login_user_id]
        ])->get();

        $contct = [
            "orderDetail" => $myOrderDetail,
            "member_id" => $request->get('normalcustomer') != null ? Member::orderBy('id', 'desc')->first() :  Member::where([
                ["id", '=' , $request->get('hidden_member_id')]
            ])->first(),
            "fristCol" =>$firstDatas,
            "secCol" =>  $secondDatas,
            "betsall" => $betall
        ];
        return response($contct, 200);
        
    }
    
}
