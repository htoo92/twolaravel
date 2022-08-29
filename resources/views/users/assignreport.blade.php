@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>အသုံးပြုသူ ဂဏန်း တင်ရမည့် အထက်အရာရှိ</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>

    <form action="/users/assignto" method="post" class="form-middle">
        @csrf
        @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show" id="bsalert" role="alert">
            <strong>{{ $message }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="mb-3">
            <label for="username" class="form-label">အမည်</label>
            <input type="text" class="form-control" id="username" value="{{$assigntoget->name}}" disabled>
        </div>

        <div class="mb-3">
            <label for="usergroup" class="form-label">အဖွဲ့</label>
            <input type="text" class="form-control" id="usergroup" value="{{$assigntogroup->group_name}}" disabled>
        </div>

        <div class="mb-3" id="reportToUpperContainer">
            <label for="reportToUpper" class="form-label">ဂဏန်းတင်ရမည့်သူ</label>
            <input type="hidden" name="user_id" value="{{$assigntoget->id}}">
            <select class="form-select @error('reportToUpper') is-invalid @enderror" aria-label="Default select example" id="reportToUpper" name="reportToUpper">
                @foreach($assigntousers as $users)
                <option value="{{$users->id}}">{{$users->name}} - @foreach($users->getRoleNames() as $rolename)
                    {{$rolename}}
                    @endforeach</option>
                @endforeach
                {{-- <option disabled selected>အထက်အရာရှိကိုရွေးပါ။</option>
                <option value="1">Mg Mg - Admin</option>
                <option value="2">Ko Ko - Supervisor</option>
                <option value="3">Ma Ma - Supervisor</option> --}}
            </select>
            @error('reportToUpper')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">အတည်ပြုမည်။</button>
        <button type="reset" value="Reset" class="btn btn-dark">လုပ်ဆောင်မှု ပြန်စမည် </button>
    </form>
</div>

{{-- <script type="text/javascript">
    $(document).ready(function() {
        document.getElementById('reportToUpperContainer').style.display = 'none';
        document.getElementById('userrole').onchange = function() {
            var x = document.getElementById("usergroup").value;
            if (this.value == 'Member' && x.length != 0) {
                document.getElementById('reportToUpperContainer').style.display = 'block';
            } else {
                document.getElementById('reportToUpperContainer').style.display = 'none';
            }
        }
    });

</script> --}}
@endsection
