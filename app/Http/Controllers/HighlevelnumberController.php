<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Highlevelnumberlimit;
use App\Models\User;
use DB;

class HighlevelnumberController extends Controller
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
        //
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
        $user_id = auth()->user()->id;
        for($i=0; $i<=99; $i++){
            if($i <=9 ){
                Highlevelnumberlimit::where([
                    ['numbers','=',"0".$i],
                    //['user_id','=',$user_id],
                ])->update(['amount'=>$request->get('editlimitnumber'.$i),'is_off'=>$request->get('numlimittext'.$i)]);
                
            }
            else{
                Highlevelnumberlimit::where([
                    ['numbers','=',$i],
                    //['user_id','=',$user_id],
                ])->update(['amount'=>$request->get('editlimitnumber'.$i),'is_off'=>$request->get('numlimittext'.$i)]);
            }
        }
        return redirect('/changelimit')->with('success','Limit Updated Successfully :)');
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
