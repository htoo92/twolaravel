@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>အသုံးပြုသူများကို စီမံရန်</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>

    @if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible fade show" id="bsalert" role="alert">
        <strong>{{ $message }}</strong>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    @endif

    @if(auth()->user()->can('user-create'))
    <div class="second-section-box mb-3">
        <div class="row">
            <div class="col-md-12 page-title d-flex align-items-center justify-content-md-end justify-content-sm-start">
                <a class="btn btn-primary" href="/users/create" role="button">
                    <i class="fas fa-plus-circle"></i> အသုံးပြုသူ အသစ်ထည့်မည်
                </a>
            </div>
        </div>
    </div>
    @endif

    <table class="table" id="users_table" style="width:100%">
        <thead>
            <tr>
                <th scope="col">အိုင်ဒီ</th>
                <th scope="col">အမည်</th>
                <th scope="col">ဖုန်းနံပါတ်</th>
                <th scope="col">ရာထူး</th>
                <th scope="col">အဖွဲ့</th>
                <th scope="col">စတင်ဝင်ရောက်ချိန်</th>
                <th scope="col">လုပ်ဆောင်ချက်</th>
            </tr>
        </thead>
        <tbody>
            @if(auth()->user()->can('user-list'))
            @role('Admin')
            @foreach($users as $user)
            <tr>
                <th scope="row" class="align-middle">{{$user->id}}</th>
                <td class="align-middle">{{$user->name}}</td>
                <td class="align-middle">{{$user->email}}</td>
                <td class="align-middle">@foreach($user->getRoleNames() as $rolename)
                    {{$rolename}}
                    @endforeach</td>
                <td class="align-middle">
                    @foreach($user->groups as $group)
                    {{$group->group_name}}
                    @endforeach
                </td>
                <td class="align-middle">{{date_format($user->created_at,'d-M-Y')}}
                </td>
                <td class="align-middle">
                    <a class="btn btn-outline-success" href="/users/show/{{$user->id}}" role="button">
                        <i class="fas fa-eye"></i> ကြည့်မည်
                    </a>
                    <a class="btn btn-outline-primary" href="/users/edit/{{$user->id}}" role="button">
                        <i class="fas fa-user-edit"></i> ပြင်မည်
                    </a>
                    <form action="/users/delete/{{$user->id}}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt"></i> ဖျက်မည်</button>

                    </form>
                </td>
            </tr>
            @endforeach
            @else
            @foreach ($userall as $user)
            @foreach ($groupall as $group)
            @if($user->group_id == $group->id)
            <tr>
                <th scope="row" class="align-middle">{{$user->id}}</th>
                <td class="align-middle">{{$user->name}}</td>
                <td class="align-middle">{{$user->email}}</td>
                <td class="align-middle">leader</td>
                <td class="align-middle">
                    @foreach($user->groups as $group)
                    {{$group->group_name}}
                    @endforeach
                </td>
                <td class="align-middle">{{date_format($user->created_at,'d-M-Y')}}</td>
                <td class="align-middle">
                    <a class="btn btn-outline-success" href="/users/show/{{$user->id}}" role="button">
                        <i class="fas fa-eye"></i> ကြည့်မည်
                    </a>
                    @if(auth()->user()->can('user-edit'))
                    <a class="btn btn-outline-primary" href="/users/edit/{{$user->id}}" role="button">
                        <i class="fas fa-user-edit"></i> ပြင်မည်
                    </a>
                    @endif
                    @if(auth()->user()->can('user-delete'))
                    <form action="/users/delete/{{$user->id}}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt"></i> ဖျက်မည်</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endif
            @endforeach
            @endforeach
            @endrole
            @endif



        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        var usertable = $('#users_table').DataTable({
            responsive: true
        });
        new $.fn.dataTable.FixedHeader(usertable);
    });

</script>
@endsection
