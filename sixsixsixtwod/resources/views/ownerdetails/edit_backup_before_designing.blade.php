@extends('layouts.main')
@section('content')
<div class="container">
    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>ပိုင်ရှင် မှတ်တမ်း</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>
    {{-- <div class="second-section-box mb-3">
        <div class="row">
            <div class="col-md-12 page-title d-flex align-items-center justify-content-md-end justify-content-sm-start">
                <form action="/ownerdetails/sendReport/{{$reportTo}}" method="post">
    @csrf
    <button id="od_report" class="btn btn-info"><i class="fab fa-telegram-plane"></i> Report တင်မည်။</button>
    </form>
</div>
</div>
</div> --}}
<div class="detail-container mb-3">
    <h5>စောင့်ကြည့်ရေး စာရင်းကွက်</h5>
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="overscroll">
                <table class="table table-hover table-bordered">
                    <thead class="bg-white sticky-top">
                        <tr>
                            <th scope="col">00 to 99</th>
                            <th scope="col">ပမာဏများ </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hignlimitnumber as $hln)
                        <tr class="second_table_rows">
                            <th scope="row">{{$hln->numbers}}</th>
                            <td>{{$hln->amount}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<form action="/ownerdetails/update" method="post">
    @csrf
    <div class="owner-container mb-5">
        {{-- <div class="row mb-3">
                <div class="col-md-4">
                    <label for="bettype" class="form-label">Bet အမျိုးအစား</label>
                    <select class="form-select validate-bettype" name="bettype" id="bettype" onchange="zerofunction(event)">
                        <option value="0" disabled selected>တစ်မျိုး ရွေးချယ်ပါ</option>
                        @foreach ($numbertype as $type)
                        <option value="{{$type->id}}">{{$type->number_types}}</option>
        @endforeach
        </select>
    </div>
    <div class="col-md-4 validate-design">
        <label for="betno" class="form-label">Bet နံပါတ်</label>
        <input type="text" class="form-control validate-betnumber" id="betno" name="betno" disabled onkeyup="myFunction(this.value)">
        <div id="counterDisplay" class=""></div>
    </div>
    <div class="col-md-4 validate-design">
        <label for="betamount" class="form-label">Amount</label>
        <input type="number" class="form-control" id="betamount" name="betamount" min="0">
    </div>
    </div> --}}
    <div class="row">
        <div class="col-md-5">
            <label for="overrate" class="form-label">
                ကိုယ်စားလှယ်ပေးမည့် ရာခိုင်နှုန်း
                {{-- Over Rate Sales Percentage (%) --}}
            </label>
            <input type="number" class="form-control" name="overrate" id="overrate" min="0" max="100" value={{$ownerdetailsoverrate}} disabled>
        </div>
        <div class="col-md-5">
            <label for="returnrate" class="form-label">
                လျော်မည့်အဆ
                {{-- Return Rate (%) --}}
            </label>
            <input type="number" class="form-control" name="returnrate" id="returnrate" min="0" max="100" value={{$ownerdetailsreturnrate}} disabled>
        </div>
        <div class="col-md-2">
            <label for="betno" class="form-label" style="visibility: hidden">လုပ်ဆောင်ချက်များ</label>
            <br>
            {{-- <button id="od_update" class="btn btn-primary">Update</button> --}}
            {{-- <button id="od_save" class="btn btn-success">Save</button> --}}
        </div>
    </div>
    </div>
    <div class="bet-container mb-5">
        <div class="row">
            <div class="col-md-4 over-amount-table mb-3">
                <h5 class="mb-4">ထိုးကြေးစုစုပေါင်း</h5>
                <div class="overscroll">
                    <table class="table table-hover table-bordered">
                        <thead class="bg-white sticky-top">
                            <tr class="table-secondary">
                                <th scope="col">လစ်မစ်</th>
                                <th scope="col">
                                    @foreach($changelimit as $cl)
                                    {{$cl->limit_amount}}
                                    @endforeach
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overlimit as $over)
                            <tr class="second_table_rows">
                                <th scope="row">{{$over->number}}</th>
                                <td>{{$over->over_amount}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th scope="col">ထိုးကြေးစုစုပေါင်း</th>
                                <th scope="col" id="over_amount_total">{{$total_over_amount}}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="col-md-4 total-amount-table-1 mb-3 d-none">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5>ပြင်ရန် စာရင်းကွက်</h5>
                    <button id="od_update" class="btn btn-primary btn-sm" disabled>ပြင်မည်</button>
                </div>
                <div class="overscroll tempo">
                    <table class="table table-hover table-bordered table-dark">
                        <thead class="bg-white sticky-top">
                            <tr>
                                <th scope="col">00 to 99</th>
                                <th scope="col">ထိုးကြေး</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($displayOwnerDetails as $row)
                            <tr class="first_table_rows">
                                <th scope="row">{{$row->number}}</th>
                                <td>
                                    <input type="number" name="editbetownernumber{{ $loop->index }}" min="0" value="{{$row->final_amount}}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

</form>

<div class="col-md-4 total-amount-table-2 mb-3  d-none">
    <form action="/ownerdetails/sendReport/{{$reportTo}}" method="post">
        @csrf
        <div class="d-flex justify-content-between align-items-center mb-2">

            <h5>Report စာရင်းကွက်</h5>
            <button id="od_report" class="btn btn-info btn-sm" disabled><i class="fab fa-telegram-plane"></i> Report တင်မည်။</button>
        </div>

        <div class="overscroll">
            <table class="table table-hover table-bordered table-dark">
                <thead class="bg-white sticky-top">
                    <tr>
                        <th scope="col">00 to 99</th>
                        <th scope="col">Amounts</th>
                        <th scope="col">Return</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($displayOwnerDetails as $row)
                    <tr>
                        <th scope="row">{{$row->number}}</th>
                        <td>{{$row->final_amount}}
                            <input type="hidden" name="reportamount{{ $loop->index }}" value="{{$row->final_amount}}">
                            <input type="hidden" name="{{ $loop->index }}" value="{{$row->number}}">
                        </td>
                        <td>{{$row->return_amount}}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-secondary">
                        <th scope="col" colspan="2">ထိုးကြေးစုစုပေါင်း</th>
                        <th scope="col" id="over_amount_total">{{$reporttotalamount}}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </form>
</div>
</div>
</div>

</div>

<script type="text/javascript">
    $(document).ready(function() {

        $("#bettype option[value='1']").hide();
        $("#bettype option[value='6']").hide();
        $("#bettype option[value='7']").hide();
        $("#bettype option[value='8']").hide();
        $("#bettype option[value='17']").hide();
        $("#bettype option[value='18']").hide();
        $("#bettype option[value='19']").hide();
        $("#bettype option[value='20']").hide();

        $('.validate-bettype').change(function() {

            var select_value = $(this).val();

            if (select_value == '1' || select_value == '6' || select_value == '7' || select_value == '8' || select_value == '17' || select_value == '18' || select_value == '19' || select_value == '20') {
                $('.validate-betnumber').prop('disabled', '');
            } else {
                $('.validate-betnumber').prop('disabled', 'disabled')
            }
        });
    });

    // var xvalue = document.getElementById("bettype").value;
    // if (xvalue == '1' || xvalue == '6' || xvalue == '7' || xvalue == '8' || xvalue == '17' || xvalue == '18' || xvalue == '19' || xvalue == '20') {
    //     $('.xvalue').children("option[value^=" + $(this).val() + "]").hide();
    // }

    $("#bettype option[value='1']").hide();

    function zerofunction(val) {
        var i = document.getElementById("bettype").value;
        document.getElementById("betno").value = "";
        document.getElementById('counterDisplay').innerHTML = "";
        document.getElementById('od_update').disabled = false;
        document.getElementById('od_save').disabled = false;
        document.getElementById('od_report').disabled = false;

        if (i == '17') {
            $("#betamount").prop("disabled", true);
        } else {
            $("#betamount").prop("disabled", false);
        }
    }

    function myFunction(val) {
        var x = document.getElementById("betno").value;
        var y = document.getElementById("bettype").value;
        if (y == '1' && x.length >= 0) {
            // document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၂ လုံးထက် မပိုရ";
            // document.getElementById('od_update').disabled = true;
            // document.getElementById('od_save').disabled = true;
            // document.getElementById('od_report').disabled = true;
        } else if (y != '1' && y != '17' && y != '18' && y != '19' && x.length >= 2) {
            document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၁ လုံးထက် မပိုရ";
            document.getElementById('od_update').disabled = true;
            document.getElementById('od_save').disabled = true;
            document.getElementById('od_report').disabled = true;
        } else if (y == '18' && x.length >= 8) {
            document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၇ လုံးထက် မပိုရ";
            document.getElementById('od_update').disabled = true;
            document.getElementById('od_save').disabled = true;
            document.getElementById('od_report').disabled = true;
        } else if (y == '19' && x.length >= 8) {
            document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၇ လုံးထက် မပိုရ";
            document.getElementById('od_update').disabled = true;
            document.getElementById('od_save').disabled = true;
            document.getElementById('od_report').disabled = true;
        } else if (y == '18' && x.length <= 2) {
            document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၃ လုံးထက် မနည်းရ";
            document.getElementById('od_update').disabled = true;
            document.getElementById('od_save').disabled = true;
            document.getElementById('od_report').disabled = true;
        } else if (y == '19' && x.length <= 2) {
            document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၃ လုံးထက် မနည်းရ";
            document.getElementById('od_update').disabled = true;
            document.getElementById('od_save').disabled = true;
            document.getElementById('od_report').disabled = true;
        } else {
            document.getElementById('counterDisplay').innerHTML = "";
            document.getElementById('od_update').disabled = false;
            document.getElementById('od_save').disabled = false;
            document.getElementById('od_report').disabled = false;
        }
    }

    $(document).ready(function() {
        var monitoringtable = $('#monitoring_table').DataTable({
            responsive: true
        });
        new $.fn.dataTable.FixedHeader(monitoringtable);
    });

</script>
@endsection
