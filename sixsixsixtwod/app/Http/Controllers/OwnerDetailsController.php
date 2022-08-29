<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\ChangeLimit;
use App\Models\NumberType;
use App\Models\Number;
use App\Models\User;
use App\Models\Highlevelnumberlimit;
class OwnerDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct(){ 
        $this->middleware("mysession");
    }
    public function index()
    {
        
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user_id = auth()->user()->id;
        $report_tp = auth()->user()->report_to;
        $ownerdetails_overrate = auth()->user()->ownerdetails_overrate;
        $ownerdetails_returnrate = auth()->user()->ownerdetails_returnrate;
        $limit = Bet::where('user_id',$user_id)->get();
        $report_total_amount = Bet::where('user_id',$user_id)->sum('final_amount');
        $over_amount_total = Bet::where('user_id',$user_id)->sum('accept_amount');
        $numbertype = NumberType::all();
        $overdetails_changelimit = ChangeLimit::where('user_id',$user_id)->get();
        $display_owner_details = Bet::where([['user_id','=',$user_id]])->get();
        $highlimitnumber = Highlevelnumberlimit::where([['user_id','=',$report_tp]])->get(); 
        
        $context =['overlimit' => $limit,'changelimit' => $overdetails_changelimit,'numbertype' => $numbertype,'total_over_amount' => $over_amount_total,'reportTo'=>$report_tp,'displayOwnerDetails' => $display_owner_details,'hignlimitnumber'=>$highlimitnumber,'ownerdetailsoverrate' => $ownerdetails_overrate, 'ownerdetailsreturnrate' => $ownerdetails_returnrate, 'reporttotalamount' => $report_total_amount];
        return view('ownerdetails.edit',$context);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user_id = auth()->user()->id;
        $numbertype = Number::where([
            ['number_type_id','=',$request->get('bettype')],
        ])->get();
        $limit = Bet::where('user_id',$user_id)->get();
        $over_amount_total = Bet::where('user_id',$user_id)->sum('final_amount');
        $numbertype = NumberType::all();
        $overdetails_changelimit = ChangeLimit::where('user_id',$user_id)->get();
        $firstDatas = Bet::where([
            ["number","<=",'49'],
            ["user_id","=",$user_id]
        ])->get();
        
        $secondDatas = Bet::where([
            ["number",">=",'50'],
            ["user_id","=",$user_id]
        ])->get();

        for($i=0; $i<=99; $i++){
            if($i <=9 ){
                
                Bet::where([
                    ['number','=',"0".$i],
                    ['user_id','=',$user_id],
                ])->update(['final_amount'=>$request->get('editbetownernumber'.$i)]);
            }
            else{
                Bet::where([
                    ['number','=',$i],
                    ['user_id','=',$user_id],
                ])->update(['final_amount'=>$request->get('editbetownernumber'.$i)]);
            }
        }
        $context = ['overlimit' => $limit,'firsttable' => $firstDatas,'secondtable' => $secondDatas, 'changelimit' => $overdetails_changelimit, 'numbertype' => $numbertype, 'total_over_amount' => $over_amount_total];
        return redirect('/ownerdetails'."/".$user_id.'/');
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
    public function sendReport(Request $request,$id)
    {
       $authUser = auth()->user(); 
       $supervisorId = $id;
       
       $highNumLimits = Highlevelnumberlimit::where('user_id',1)->get();

       //! start number looping 
       foreach($highNumLimits as $key => $highNumLimit){

        $requestAmount = (int) $request->get("reportamount".$key);

        if($highNumLimit->is_off == 0 && $requestAmount > 0){

            $members = User::role("Member")->pluck('id')->toArray();
            
            $existingAcceptAmount = Bet::where('number',$request->get($key))->whereIn('user_id',$members)->sum('accept_amount');
    
            $sumOfExistsAndRequestAmount = (int) $existingAcceptAmount + $requestAmount;
        
            if($sumOfExistsAndRequestAmount <= $highNumLimit->amount){

                //! update current member bet & supervisor bet & admin bet (freely add accept_amount)
                $currentUserBet = Bet::where('user_id',$authUser->id)->where('number',$request->get($key))->first();
                $currentUserBet->accept_amount = $currentUserBet->accept_amount + $requestAmount;
                $currentUserBet->return_amount = 0;
                $currentUserBet->final_amount = 0;
                $currentUserBet->update();

                $supervisorBet = Bet::where('user_id',$supervisorId)->where('number',$request->get($key))->first();
                $membersBySupervisorId = User::role("Member")->where('report_to',$supervisorId)->pluck('id')->toArray();
                $supervisorTotalAmountByMembers = Bet::where('number',$request->get($key))->whereIn('user_id',$membersBySupervisorId)->sum('accept_amount');
                $supervisorBet->accept_amount = (int) $supervisorTotalAmountByMembers;
                $supervisorBet->update();

                $adminBet = Bet::where('user_id',1)->where('number',$request->get($key))->first();
                $allMemberIds = User::role("Member")->pluck('id')->toArray();
                $totalAmountByallMembers = Bet::where('number',$request->get($key))->whereIn('user_id',$allMemberIds)->sum('accept_amount');
                $adminBet->accept_amount = (int) $totalAmountByallMembers;
                $adminBet->update();
            }else{
                //! check if accept amount exists
                $needAmount = $highNumLimit->amount - (int) $existingAcceptAmount;
                if($needAmount > 0){
                    //! update current user bet & user's supervisor bet & admin bet (add need_amount)
                    $currentUserBet = Bet::where('user_id',$authUser->id)->where('number',$request->get($key))->first();
                    $currentUserBet->accept_amount = $currentUserBet->accept_amount + $needAmount; // aceeptAmount == needAmount
                    $currentUserBet->return_amount = 0;
                    $currentUserBet->final_amount = 0;
                    $currentUserBet->update();

                    $supervisorBet = Bet::where('user_id',$supervisorId)->where('number',$request->get($key))->first();
                    $membersBySupervisorId = User::role("Member")->where('report_to',$supervisorId)->pluck('id')->toArray();
                    $supervisorTotalAmountByMembers = Bet::where('number',$request->get($key))->whereIn('user_id',$membersBySupervisorId)->sum('accept_amount');;
                    $supervisorBet->accept_amount = (int) $supervisorTotalAmountByMembers;
                    $supervisorBet->update();

                    $adminBet = Bet::where('user_id',1)->where('number',$request->get($key))->first();
                    $allMemberIds = User::role("Member")->pluck('id')->toArray();
                    $totalAmountByallMembers = Bet::where('number',$request->get($key))->whereIn('user_id',$allMemberIds)->sum('accept_amount');
                    $adminBet->accept_amount = (int) $totalAmountByallMembers;
                    $adminBet->update();

                    //! find return amount
                    $returnAmount = $requestAmount - $needAmount;

                    //! update current user bet (add return amount) 
                    $currentUserBet = Bet::where('user_id',$authUser->id)->where('number',$request->get($key))->first();
                    $currentUserBet->return_amount = $returnAmount;
                    $currentUserBet->final_amount = 0;
                    $currentUserBet->update();

                }else{
                    //! return all request amount as return_amount
                    $returnAmount = $requestAmount - $needAmount;

                    //! update current user bet (return amount) 
                    $currentUserBet = Bet::where('user_id',$authUser->id)->where('number',$request->get($key))->first();
                    $currentUserBet->return_amount = $returnAmount;
                    $currentUserBet->final_amount = 0;
                    $currentUserBet->update();

                }

            }
        }else{
            //! reset member's other return_amount to '0'
            $resetBet = Bet::where('user_id',$authUser->id)->where('number',$request->get($key))->first();
            $resetBet->return_amount = 0;
            $resetBet->off_return_amount = 0;
            $resetBet->update();

            //! set off_return_amount if number is OFF
            if($highNumLimit->is_off == 1 && $requestAmount > 0){
                $currentUserBet = Bet::where('user_id',$authUser->id)->where('number',$request->get($key))->first();
                $currentUserBet->off_return_amount = $requestAmount;
                $currentUserBet->final_amount = 0;
                $currentUserBet->update();
            }
        
        }
       }
       //! end number looping
       return redirect('/bets/create')->with('success','Send Report Successfully');
    }
}
