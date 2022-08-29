<?php

namespace App\Http\Controllers;
use Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Group;
use App\Models\ChangeLimit;
use App\Models\PermanentNumber;
use App\Models\Bet;
use DB;
use App\Models\Highlevelnumberlimit;
class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    { 
         $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index','store']]);
         $this->middleware('permission:user-create', ['only' => ['create','store']]);
         $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:user-delete', ['only' => ['destroy']]); 
         $this->middleware("mysession");
    }

    public function assignToGet(){
        $user = User::latest()->first();
        $groupforrole = Group::where([
            ["id","=",$user->group_id]
        ])->first();
        $users = User::where([
            ["group_id","=",$user->group_id]
        ])->get();
        $context = ['assigntoget'=>$user,'assigntogroup'=>$groupforrole,'assigntousers'=>$users];
        return view('users.assignreport',$context);
    }

    public function assignToPost(Request $request){
        User::where([
            ["id","=",$request->get("user_id")]
        ])->update([
            "report_to"=>$request->get("reportToUpper")
        ]);

        return redirect('/users')->with('success','User Created Successfully :)');

    }

    public function index()
    {
        $users = User::with('groups')->get();
        $groupall = Group::all();
        $userall = User::where([["id","=",auth()->user()->id]])->get();
        $context = ['users' => $users,'groupall' => $groupall,'userall' => $userall];
        return view('users.index',$context);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::all();
        $groups = Group::all();
        $context = ['groups' => $groups,'roles' => $roles];
        return view('users.create',$context);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // User::create($request->all());
        $this->validate($request,[
            'username' =>'required',
            'userphone' => 'required',
            'userrole' => 'required',
            'usergroup' => 'required',
            'userpassword0' => 'required'
        ]);
        
        $user = new User();
        try{
        $user->name = $request->get('username');
        $user->email = $request->get('userphone');
        $user->password = Hash::make($request->get('userpassword'));
        $user->group_id = $request->get('usergroup');
        $user->report_to = "1";
        $user->ownerdetails_overrate = $request->get('userpercentage');
        $user->ownerdetails_returnrate = $request->get('userreturnpercentage');
        if($request->get('userpassword0') == $request->get('userpassword1')){
            $user->save();
            $user->assignRole(request('userrole'));
            if($user->hasRole(['Admin']) || $user->hasRole(['Supervisor'])){
                for($i=0; $i<=99; $i++){
                    if($i <=9 ){
                        Highlevelnumberlimit::create([
                            'numbers' => "0".$i, 
                            'amount' => 1000,
                            'user_id' => $user->id,
                        ]);
                    }
                    else{
                        Highlevelnumberlimit::create([
                            'numbers' => $i, 
                            'amount' => 1000,
                            'user_id' => $user->id,
                        ]);
                    }
                }
            }
            
        $change_limit = ChangeLimit::create(['limit_amount'=>1,'user_id'=>$user->id]);
        $permanent_number = PermanentNumber::all();
       
        foreach($permanent_number as $pnum){
            Bet::create(
                ["number"=>$pnum->permanent_number,"user_id" => $user->id,"amount"=>0,"over_amount"=>0,"is_over"=>false,"to_leader"=>false,"to_supervisor"=>false]
            );
        }
        $roles = $user->getRoleNames();
        
        return redirect('/users/assignto')->with('success','User Created Successfully :)');
        } else{
            return redirect('/users/create')->with('error','User Not Created :(');
        }
        }catch(\Illuminate\Database\QueryException $e){
            // return redirect('/users/create')->with('success','User Not Created :(');
            return redirect('/users/create')->with('error','Email is Duplicate :(');
        }   
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        $context = ['user' => $user];
        return view('users.show',$context);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $relate_users = User::where([
            ["group_id","=",$user->group_id]
        ])->get();
        $groups = Group::all();
        $rolesall = Role::all();
        $context = ['user' => $user, 'groupsedit' => $groups, 'rolesedit' => $rolesall,'rs'=>$relate_users];
        return view('users.edit',$context);
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
            'username' =>'required',
            'useremail' => 'required',
            'userrole' => 'required',
            'usergroup' => 'required',
            'userpassword0' => 'required'
        ]);
        $user = User::find($id);
        $user->name = $request->get('username');
        $user->email = $request->get('useremail');
        $user->ownerdetails_overrate = $request->get('userpercentage');
        $user->ownerdetails_returnrate = $request->get('userreturnpercentage');
        $user->password = Hash::make($request->get('newuserpassword'));
        $user->group_id = $request->get('usergroup');
        $user->save();
        return redirect('/users')->with('success','User Updated Successfully :)');
    }

    /** 
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table("users")->where('id',$id)->delete();
        return redirect('/users')->with('success','User Deleted Successfully :)');
    } 
}
//Hello Bob is 