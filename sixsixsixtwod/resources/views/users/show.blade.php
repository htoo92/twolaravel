@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>အသုံးပြုသူ၏ အချက်အလက်များ</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6 col-sm-12">
            <table class="table table-borderless mb-3">
                <tbody>
                    <tr>
                        <th scope="row">အသုံးပြုသူ အိုင်ဒီ</th>
                        <td>{{$user->id}}</td>
                    </tr>
                    <tr>
                        <th scope="row">စတင်ဝင်ရောက်ချိန်</th>
                        <td>{{$user->created_at}}</td>
                    </tr>
                    <tr>
                        <th scope="row">အမည်</th>
                        <td>{{$user->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">ဖုန်းနံပါတ်</th>
                        <td>{{$user->userphone}}</td>
                    </tr>
                    <tr>
                        <th scope="row">ရာထူး</th>
                        <td>@foreach($user->getRoleNames() as $rolename)
                            {{$rolename}}
                            @endforeach</td>
                    </tr>
                    <tr>
                        <th scope="row">အဖွဲ့</th>
                        <td> @foreach($user->groups as $group)
                            {{$group->group_name}}
                            @endforeach</td>
                    </tr>
                </tbody>
            </table>
            @if(auth()->user()->can('user-edit'))
            <a class="btn btn-primary" href="/users/edit/{{$user->id}}" role="button">
                <i class="fas fa-user-edit"></i> ပြင်မည်
            </a>
            @endif
        </div>
        <div class="col-md-3"></div>
    </div>
</div>
@endsection
