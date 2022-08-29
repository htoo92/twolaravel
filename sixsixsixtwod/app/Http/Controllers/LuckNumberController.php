<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BetNumberForMember;
use App\Models\Order;
use App\Models\Member;
use App\Models\Bet;
use App\Models\OrderDetail;

class LuckNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct(){ 
        $this->middleware('permission:lucky-number-list', ['only' => ['index','show']]);
        $this->middleware("mysession");
    }
    public function index()
    {
        $lucky_number= BetNumberForMember::find(0);
        $context = ['luckynumber' => $lucky_number];
        return view('luckynumber.index',$context);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $lucky_number = BetNumberForMember::where([["number","=",$request->get('luckyno')]])->with("memberID")->get();
        $x = new stdClass();
        $context = ['luckynumber' => $x];
        return view('luckynumber.index',$context);
        // $context = ['luckynumber' => $lucky_number];
        // return view('luckynumber.index',$context);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $arr = [];
        $current = auth()->user()->id;
        $lucky_number = BetNumberForMember::where([
            ["number","=",$request->get('luckyno')]
            ])->get();
        if($current == 1){
            $variable = BetNumberForMember::with('luckymember','mynum','owner','luckymemberpercentage',"myorders")->where([
                ["number","=",$request->get('luckyno')],
                ])->get();
            $context = ['luckynumber' => $variable];
            return view("luckynumber.index",$context);
        }
        else{
            $variable = BetNumberForMember::with('luckymember','mynum','owner','luckymemberpercentage',"myorders")->where([
                ["number","=",$request->get('luckyno')],
                ["user_id","=",$current]
                ])->get();
            $context = ['luckynumber' => $variable];
            return view("luckynumber.index",$context);
        }
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
