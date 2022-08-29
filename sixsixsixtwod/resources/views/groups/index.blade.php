@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>အဖွဲ့များကို စီမံရန်</h3>
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

    @if(auth()->user()->can('groups-create'))
    <div class="second-section-box mb-3">
        <div class="row">
            <div class="col-md-12 page-title d-flex align-items-center justify-content-md-end justify-content-sm-start">
                <a class="btn btn-primary" href="/groups/create" role="button">
                    <i class="fas fa-plus-circle"></i> အဖွဲ့အသစ် ပြုလုပ်မည်
                </a>
            </div>
        </div>
    </div>
    @endif

    <table class="table" id="groups_table" style="width:100%">
        <thead>
            <tr>
                <th scope="col">အိုင်ဒီ</th>
                <th scope="col">အမည်</th>
                <th scope="col">ဘောက်ချာကုတ်</th>
                <th scope="col">အဖွဲ့ဝင် ကန့်သတ်ချက်</th>
                <th scope="col">အဖွဲ့ စတင်သည့် ရက်စွဲ</th>
                <th scope="col">လုပ်ဆောင်ချက်</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
            <tr>
                <th scope="row" class="align-middle">{{$group->id}}</th>
                <td class="align-middle">{{$group->group_name}}</td>
                <td class="align-middle">{{$group->group_voucher}}</td>
                <td class="align-middle">{{$group->members_limit}}</td>
                <td class="align-middle">{{date_format($group->created_at,'d-M-Y')}}
                </td>
                <td class="align-middle">
                    <a class="btn btn-outline-success" href="/groups/show/{{$group->id}}" role="button">
                        <i class="fas fa-eye"></i> ကြည့်မည်
                    </a>
                    @if(auth()->user()->can('groups-edit'))
                    <a class="btn btn-outline-primary" href="/groups/edit/{{$group->id}}" role="button">
                        <i class="fas fa-edit"></i> ပြင်မည်
                    </a>
                    @endif
                    @if(auth()->user()->can('groups-delete'))
                    <a class="btn btn-outline-danger" href="/groups/delete/{{$group->id}}" role="button">
                        <i class="fas fa-trash-alt"></i> ဖျက်မည်
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        var grouptable = $('#groups_table').DataTable({
            responsive: true
        });
        new $.fn.dataTable.FixedHeader(grouptable);
    });

</script>
@endsection
