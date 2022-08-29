@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>အဖွဲ့ အသစ်ထည့်ရန်</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>
    <form action="/groups/create" method="post" class="form-middle">
        @csrf
        <div class="mb-3">
            <label for="groupname" class="form-label">အမည်</label>
            <input type="text" class="form-control @error('groupname') is-invalid @enderror" id="groupname" name="groupname">
            @error('groupname')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="groupvouchercode" class="form-label">အဖွဲ့ ဘောက်ချာကုတ်</label>
            <input type="text" class="form-control @error('groupvouchercode') is-invalid @enderror" id="groupvouchercode" name="groupvouchercode" value="{{$voucherid}}">
            @error('groupvouchercode')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="grouplimit" class="form-label">အဖွဲ့ဝင် အရေအတွက် ကန့်သတ်ချက်</label>
            <input type="number" class="form-control @error('grouplimit') is-invalid @enderror" id="grouplimit" name="grouplimit" min="1">
            @error('grouplimit')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">အဖွဲ့ ဖွဲ့မည်</button>
        <button type="reset" value="Reset" class="btn btn-dark">လုပ်ဆောင်မှု ပြန်စမည် </button>
    </form>
</div>
@endsection
