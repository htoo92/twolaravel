@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>အဖွဲ့၏ အချက်အလက်များ</h3>
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
                        <th scope="row">အဖွဲ့ အိုင်ဒီ</th>
                        <td>{{$group->id}}</td>
                    </tr>
                    <tr>
                        <th scope="row">အဖွဲ့ စတင်သည့် ရက်စွဲ</th>
                        <td>{{$group->created_at}}</td>
                    </tr>
                    <tr>
                        <th scope="row">အဖွဲ့ အမည်</th>
                        <td>{{$group->group_name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">ဘောက်ချာကုတ်</th>
                        <td>{{$group->group_voucher}}</td>
                    </tr>
                    <tr>
                        <th scope="row">အဖွဲ့ဝင် ကန့်သတ်ချက် </th>
                        <td>{{$group->members_limit}}</td>
                    </tr>
                </tbody>
            </table>
            @if(auth()->user()->can('groups-edit'))
            <a class="btn btn-primary" href="/groups/edit/{{$group->id}}" role="button">
                <i class="fas fa-edit"></i> ပြင်မည်
            </a>
            @endif
        </div>
        <div class="col-md-3"></div>
    </div>
</div>
@endsection
