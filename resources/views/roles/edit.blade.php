@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>ရာထူးအားပြင်ရန်</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>
    <form action="/roles/update/{{$role->id}}" method="post" class="form-middle">
        @csrf
        <div class="mb-3">
            <label for="rolename" class="form-label">ရာထူး အမည်</label>
            <input type="text" class="form-control @error('rolename') is-invalid @enderror" id="rolename" name="rolename" value="{{$role->name}}">
            @error('rolename')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="rolepermissions" class="form-label">ပါမစ်ရှင်များ</label>
            @foreach($permission as $value)
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="{{ $value->name }}" name="permissions[]" value="{{ $value->id }}" {{ $role->permissions->contains($value->id) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $value->name }}">{{ $value->name }}</label>
            </div>
            @endforeach

        </div>

        <button type="submit" class="btn btn-primary">ပြင်မည်</button>
        <button type="reset" value="Reset" class="btn btn-dark">လုပ်ဆောင်မှု ပြန်စမည် </button>
    </form>
</div>
@endsection
