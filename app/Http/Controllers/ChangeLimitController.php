<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChangeLimit;
use App\Models\Bet;
use App\Models\Users;
use App\Models\Highlevelnumberlimit;
use App\Events\MessageNotification;

class ChangeLimitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */ 
    function __construct()
    { 
        $this->middleware("mysession");
    }
    public function index()
    {
        
        $id = auth()->user()->id;
        $changelimit = ChangeLimit::where('user_id',$id)->get();

        $number_limit = Highlevelnumberlimit::where([
            ['user_id','=',$id]
            ])->get();
        $context = ['changelimit' => $changelimit,'numberlimit' => $number_limit];
        return view('limit.index',$context); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $changelimit = new limit();
        // $chamgelimit->name = $request->get('');
        // $changelimit->email = $request->get('');
        // $changelimit->save();
        // return redirect('/limit')->with('success','Limited Created Successfully :)');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
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
        // $this->validate($request,[
        //     'changelimit' =>'required'
        // ]);
        
        $user_id = auth()->user()->id;
        $changelimit = ChangeLimit::find($id);
        $bet_over_amount_to_change = Bet::where([
            ['user_id','=',$user_id],
        ])->get();
        foreach($bet_over_amount_to_change as $over_to_change_accordingto_updatelimit){
            Bet::where([
                ['user_id','=',$user_id],
            ])->update(['over_amount'=>$over_to_change_accordingto_updatelimit->over_amount - (int)$request->get('changelimit') <= 0 ? 0 : $over_to_change_accordingto_updatelimit->over_amount - (int)$request->get('changelimit')]);
        }
        $changelimit->limit_amount = request('changelimit');
        $changelimit->save();   
        
        return redirect('/changelimit')->with('success','Limit   Updated Successfully :)');
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
    public function changeAll(Request $request){
        $user_id = auth()->user()->id;
                Highlevelnumberlimit::where([
                    // ['user_id','=',$user_id],
                ])->update(['amount'=>$request->get('updateEachLimit')]);
                return redirect('/changelimit')->with('success','Limit   Updated Successfully :)');
    }
    public function offBetAdd(Request $request){
            ChangeLimit::where([
                // ['is_offButton','=','0'],
            ])->update(['is_offButton'=>$request->get('offBetValue')]);
            return redirect('/changelimit')->with('success','ထို:ကြေးများ ထိုးလို့မရတော့ပါ)');  
    }
}
