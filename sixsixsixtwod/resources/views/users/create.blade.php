@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>အသုံးပြုသူ အသစ်ထည့်ရန်</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>

    <form action="/users/create" method="post" class="form-middle">
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
            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username">
            @error('username')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="userphone" class="form-label">ဖုန်းနံပါတ်</label>
            <input type="text" class="form-control @error('userphone') is-invalid @enderror" id="userphone" name="userphone">
            @error('userphone')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="userrole" class="form-label">ရာထူး</label>
            <select class="form-select @error('userrole') is-invalid @enderror" name="userrole" id="userrole">
                <option disabled selected>အသုံးပြုသူ ရာထူးကို ထည့်ပါ။</option>
                @foreach($roles as $role)
                <option value={{$role->name}}>{{$role->name}}</option>
                @endforeach
            </select>
            @error('userrole')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="usergroup" class="form-label">အဖွဲ့</label>
            <select class="form-select @error('usergroup') is-invalid @enderror" name="usergroup" id="usergroup">
                <option value="0" disabled selected>အသုံးပြုသူကို အဖွဲ့တစ်ခုတွင်ထည့်ပါ။</option>
                @foreach($groups as $group)
                <option value={{$group->id}}>{{$group->group_name}}</option>
                @endforeach
            </select>
            @error('usergroup')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="userpercentage" class="form-label">အရောင်းနှုန်း</label>
            <input type="number" class="form-control @error('userpercentage') is-invalid @enderror" id="userpercentage" name="userpercentage" value="0" min="0" max="99">
            @error('userpercentage')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="userreturnpercentage" class="form-label">လျော်မည့်အဆ</label>
            <input type="number" class="form-control @error('userreturnpercentage') is-invalid @enderror" id="userreturnpercentage" name="userreturnpercentage" value="1" min="1" max="99">
            @error('userreturnpercentage')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        {{-- <div class="mb-3" id="reportToUpperContainer">
            <label for="reportToUpper" class="form-label">Report တင်ရမည့်သူ</label>
            <select class="form-select @error('reportToUpper') is-invalid @enderror" aria-label="Default select example" id="reportToUpper" name="reportToUpper">
                <option disabled selected>အထက်အရာရှိကိုရွေးပါ။</option>
                <option value="1">Mg Mg - Admin</option>
                <option value="2">Ko Ko - Supervisor</option>
                <option value="3">Ma Ma - Supervisor</option>
            </select>
            @error('reportToUpper')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
        </span>
        @enderror
</div> --}}



<div class="mb-3">
    <label for="userpassword0" class="form-label @error('userpassword0') is-invalid @enderror">စကားဝှက်</label>
    <input type="password" class="form-control" id="userpassword0" name="userpassword0">
    @error('userpassword0')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

<div class="mb-3">
    <label for="userpassword1" class="form-label @error('userpassword1') is-invalid @enderror">စကားဝှက် ပြန်ရိုက်ပါ။</label>
    <input type="password" class="form-control" id="userpassword1" name="userpassword1">
    @error('userpassword1')
    <span class="invalid-feedback" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>

<button type="submit" class="btn btn-primary">အကောင့် ဖွင့်မည်</button>
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
