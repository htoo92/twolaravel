@extends('layouts.main')
@section('content')
<div class="container">
    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>မှတ်တမ်းများဖျက်ရန်</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>
    <form action="/clearall/destroy" method="post">
        @csrf
        <div class="owner-container mb-5">
            <div class="row mb-3">

                <div class="col-md-3"></div>
                <div class="col-md-2">
                    <label for="startdate" class="form-label">စမည့် ရက်</label>
                    <input type="date" class="form-control clickable" id="startdate" name="startdate">
                </div>
                <div class="col-md-2">
                    <label for="enddate" class="form-label">ဆုံးမည့် ရက်</label>
                    <input type="date" class="form-control clickable" id="enddate" name="enddate">
                </div>
                <div class="col-md-2">
                    <label for="betno" class="form-label" style="visibility: hidden">လုပ်ဆောင်ချက်များ</label>
                    <br>
                    {{-- <a class="btn btn-primary" href="#" role="button" id="lc_return">Return --}}
                    </a>
                    <button type="submit" class="btn btn-primary" id="clearall_submit">အကုန်ဖျက်မည်</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
