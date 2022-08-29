@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>အသုံးပြုသူအား ပြင်ရန်</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>

    <form action="/users/update/{{$user->id}}" method="post" class="form-middle">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label">အမည်</label>
            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{$user->name}}">
            @error('username')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="useremail" class="form-label">ဖုန်းနံပါတ်</label>
            <input type="text" class="form-control @error('useremail') is-invalid @enderror" id="useremail" name="useremail" value="{{$user->email}}">
            @error('useremail')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        {{-- <div class="mb-3">
            <label for="useremail" class="form-label">ဖုန်းနံပါတ်</label>
            <input type="text" class="form-control @error('useremail') is-invalid @enderror" id="useremail" name="useremail">
            @error('useremail')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
        </span>
        @enderror
</div> --}}

@role('Admin')
<div class="mb-3">
    <label for="userrole" class="form-label">ရာထူး</label>
    {{-- @if(auth()->user()->can('role-edit') --}}
    <select class="form-select @error('userrole') is-invalid @enderror" name="userrole">
        @foreach($user->getRoleNames() as $rolename)
        @foreach ($rolesedit as $role)
        @if($rolename == $role->name)
        <option value="{{$role->name}}" selected>{{$role->name}}</option>
        @else
        <option value="{{$role->name}}">{{$role->name}}</option>
        @endif
        @endforeach
        @endforeach
    </select>
    {{-- @else --}}
    {{-- <select class="form-select @error('userrole') is-invalid @enderror" name="userrole">
                @foreach($user->getRoleNames() as $rolename)
                @foreach ($rolesedit as $role)
                @if($rolename == $role->name)
                <option value="{{$role->name}}" selected disabled>{{$role->name}}</option>
    @endif
    @endforeach
    @endforeach
    </select> --}}
    {{-- @endif --}}
    @error('userrole')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>
@endrole

<div class="mb-3">
    <label for="usergroup" class="form-label">အဖွဲ့</label>
    <select class="form-select @error('usergroup') is-invalid @enderror" name="usergroup">
        @foreach($groupsedit as $group)
        @if($user->group_id == $group->id)
        <option value="{{$group->id}}" selected>{{$group->group_name}}</option>
        @else
        <option value="{{$group->id}}">{{$group->group_name}}</option>
        @endif
        @endforeach
    </select>
    @error('usergroup')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>
<div class="mb-3">
    <label for="userpercentage" class="form-label">Owner Detail - Sales Rate</label>
    <input type="number" class="form-control @error('userpercentage') is-invalid @enderror" id="userpercentage" name="userpercentage" value="{{$user->ownerdetails_overrate}}" min="0" max="99">
    @error('userpercentage')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

<div class="mb-3">
    <label for="userreturnpercentage" class="form-label">Owner Detail - Return Rate</label>
    <input type="number" class="form-control @error('userreturnpercentage') is-invalid @enderror" id="userreturnpercentage" name="userreturnpercentage" value="{{$user->ownerdetails_returnrate}}" min="1" max="99">
    @error('userreturnpercentage')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>
<div class="mb-3" id="reportToUpperContainer">
    <label for="reportToUpper" class="form-label">Report တင်ရမည့်သူ</label>
    <select class="form-select @error('reportToUpper') is-invalid @enderror" aria-label="Default select example" id="reportToUpper" name="reportToUpper">
        <option disabled selected>အထက်အရာရှိကိုရွေးပါ။</option>
        @foreach ($rs as $r)
        <option value="{{$r->id}}">{{$r->name}} - @foreach($r->getRoleNames() as $rolename)
            {{$rolename}}
            @endforeach</option>
        @endforeach
        {{-- @foreach($assigntousers as $users)
                <option value="{{$users->id}}">{{$users->name}} - @foreach($users->getRoleNames() as $rolename)
        {{$rolename}}"
        @endforeach</option>
        @endforeach --}}

        {{-- <option value="1">Mg Mg - Admin</option>
                <option value="2">Ko Ko - Supervisor</option>
                <option value="3">Ma Ma - Supervisor</option> --}}
    </select>
    @error('reportToUpper')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>
<div class="mb-3">
    <label for="olduserpassword" class="form-label">စကားဝှက် (အဟောင်း)</label>
    <input type="password" class="form-control @error('olduserpassword') is-invalid @enderror" id="olduserpassword" name="olduserpassword">
    @error('olduserpassword')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

<div class="mb-3">
    <label for="newuserpassword0" class="form-label">စကားဝှက် (အသစ်)</label>
    <input type="password" class="form-control @error('newuserpassword0') is-invalid @enderror" id="newuserpassword0" name="newuserpassword0">
    @error('newuserpassword0')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

<div class="mb-3">
    <label for="newuserpassword1" class="form-label">စကားဝှက် (အသစ်) ပြန်ရိုက်ပါ။</label>
    <input type="password" class="form-control @error('newuserpassword1') is-invalid @enderror" id="newuserpassword1" name="newuserpassword1">
    @error('newuserpassword1')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

<button type="submit" class="btn btn-primary">ပြုပြင်မည်</button>

<button type="reset" class="btn btn-dark">လုပ်ဆောင်မှု ပြန်စမည် </button>
</form>
</div>
@endsection
