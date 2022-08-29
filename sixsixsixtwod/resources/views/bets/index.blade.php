@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>နေ့စဉ် ထိုးကြေးများ</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>

    <div class="second-section-box mb-3">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center justify-content-md-start justify-content-sm-start">

            </div>
            <div class="col-md-6 page-title d-flex align-items-center justify-content-md-end justify-content-sm-start">
                <a class="btn btn-primary" href="/bets/create" role="button">
                    <i class="fas fa-plus-circle"></i> ထိုးကြေး အသစ်ထည့်မည်
                </a>
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

    <table class="table" id="bets_table" style="width:100%">
        <thead>
            <tr>
                <th scope="col">အိုင်ဒီ</th>
                <th scope="col">ဘောက်ချာအမှတ်</th>
                <th scope="col">ထိုးခဲ့သည့်ရက်</th>
                <th scope="col">ထိုးခဲ့သည့်အချိန်</th>
                <th scope="col">အမည်</th>
                <th scope="col">%</th>
                <th scope="col">လုပ်ဆောင်ချက်</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            @foreach($members_to_show as $memberlist)
            @if($order->member_id == $memberlist->id)
            <tr>
                <th scope="row" class="align-middle">
                    {{$order->id}}
                </th>
                <td class="align-middle">{{$order->voucher_number}}</td>
                <td class="align-middle">{{date_format($order->created_at,'d-M-Y')}}</td>
                <td class="align-middle">{{date_format($order->created_at,'A')}}</td>
                <td class="align-middle">
                    {{$memberlist->name}}
                </td>
                <td class="align-middle">{{$memberlist->percentage}}</td>

                <td class="align-middle">
                    <a class="btn btn-outline-success" href="/bets/show/{{$order->id}}" role="button">
                        <i class="fas fa-eye"></i> ကြည့်မည်
                    </a>

                    {{-- <form action="#" method="delete">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt"></i> ဖျက်မည်</button>

                    </form> --}}
                </td>
            </tr>
            @endif
            @endforeach
            @endforeach

        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        var bettable = $('#bets_table').DataTable({
            responsive: true
        });
        new $.fn.dataTable.FixedHeader(bettable);
    });

</script>


@endsection
