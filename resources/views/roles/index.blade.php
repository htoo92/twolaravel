@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>ရာထူးများကို စီမံရန်</h3>
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
    @if(auth()->user()->can('role-create'))
    <div class="second-section-box mb-3">
        <div class="row">
            <div class="col-md-12 page-title d-flex align-items-center justify-content-md-end justify-content-sm-start">
                <a class="btn btn-primary" href="/roles/create" role="button">
                    <i class="fas fa-plus-circle"></i> ရာထူး အသစ်ထည့်မည်
                </a>
            </div>
        </div>
    </div>
    @endif
    <table class="table" id="roles_table" style="width:100%">
        <thead>
            <tr>
                <th scope="col">ရာထူးအမည်</th>
                {{-- <th scope="col">ပါမစ်ရှင်များ</th> --}}
                <th scope="col">လုပ်ဆောင်ချက်</th>
            </tr>
        </thead>
        <tbody>

            @foreach($roles as $role)
            <tr>
                <td class="align-middle">{{$role->name}}</td>
                {{-- <td class="align-middle">
                    <span class="list_of_roles">Insert</span>,
                    <span class="list_of_roles">Update</span>,
                    <span class="list_of_roles">Delete</span>
                </td> --}}
                <td class="align-middle">
                    @if(auth()->user()->can('role-edit'))
                    <a class="btn btn-outline-primary" href="/roles/edit/{{$role->id}}" role="button">
                        <i class="fas fa-edit"></i> ပြင်မည်
                    </a>
                    @else
                    လုပ်ဆောင်ခွင့် ပါမစ်ရှင်မရှိ
                    @endif
                    @if(auth()->user()->can('role-delete'))
                    <a class="btn btn-outline-danger" href="/roles/delete/{{$role->id}}" role="button">
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
        var roletable = $('#roles_table').DataTable({
            responsive: true
        });
        new $.fn.dataTable.FixedHeader(roletable);
    });

</script>
@endsection
