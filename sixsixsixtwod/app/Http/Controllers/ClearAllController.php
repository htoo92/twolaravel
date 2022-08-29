<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Bet;
use App\Models\BetNumberForMember;
use DB;

class ClearAllController extends Controller
{
    function __construct(){ 
        $this->middleware("mysession");
    }
    public function index(){
        return view('clearall.index');
    }
    public function destroy()
    {
        $current_user_id = auth()->user()->id;
        $user = User::findOrFail($current_user_id);
        $roles = $user->getRoleNames();

        $limit = Carbon::now();
        DB::table('betnumbers')->update(['amount' => 0,'over_amount' => 0,'final_amount' => 0,'return_amount' => 0,'accept_amount'=>0,'is_over'=>false]);
        DB::table('bet_numbers_for_members')->update(['amount' => 0]);
        
        Order::where('created_at','<',$limit)->delete();
        OrderDetail::where('created_at','=',$limit)->delete();
        return redirect('/clearall');
    }
}
