@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>ရာထူး အသစ်ထည့်ရန်</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>
    <form action="/roles/create" method="post" enctype="multipart/form-data" class="form-middle">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">ရာထူး အမည်</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name">
            @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="rolepermissions" class="form-label">ပါမစ်ရှင်များ</label>
            @foreach($permission as $value)
            <div class="form-check form-switch">
                <input type="checkbox" name="permission[]" class="form-check-input" id="{{$value -> name}}" value="{{$value -> id}}">
                <label class="form-check-label" for="{{$value -> name}}">{{$value -> name}}</label>
            </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">အသစ်ထည့်မည်</button>
        <button type="reset" value="Reset" class="btn btn-dark">လုပ်ဆောင်မှု ပြန်စမည် </button>
    </form>
</div>
@endsection
