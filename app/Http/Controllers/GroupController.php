<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use App\Models\Group;
class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    { 
         $this->middleware('permission:groups-list|groups-create|groups-edit|groups-delete', ['only' => ['index','store']]);
         $this->middleware('permission:groups-create', ['only' => ['create','store']]);
         $this->middleware('permission:groups-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:groups-delete', ['only' => ['destroy']]);
         $this->middleware("mysession");
    }
    public function index()
    {
        $groups = Group::all();
        $context = ['groups' => $groups];
        return view('groups.index',$context);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $vouchergenerate = $this->generatevoucherid();
        $context = ['voucherid' => $vouchergenerate];
        return view('groups.create',$context); 
    }
    private function generatevoucherid()
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
        $this->validate($request,[
            'groupname' =>'required',
            'groupvouchercode' =>'required',
            'grouplimit' =>'required'
        ]);
        $group = new Group();
        $group->group_name = $request->get('groupname');
        $group->group_voucher = $request->get('groupvouchercode');
        $group->members_limit = $request->get('grouplimit');
        $group->save();

        return redirect('/groups')->with('success','Group Created Successfully :)');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $group = Group::find($id);
        $context = ['group' => $group];
        return view('groups.show',$context);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $group = Group::find($id);
        $context = ['group' => $group];
        return view('groups.edit',$context);
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
        $this->validate($request,[
            'groupname' =>'required',
            'groupvouchercode' =>'required',
            'grouplimit' =>'required'
        ]);
        $group = Group::find($id);
        $group->group_name = $request->get('groupname');
        $group->group_voucher = $request->get('groupvouchercode');
        $group->members_limit = $request->get('limit');
        $user->save();
        return redirect('/groups')->with('success','Group Updated Successfully :)');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table("groups")->where('id',$id)->delete();
        return redirect('/groups')->with('success','Group Deleted Successfully :)');
    }
}
