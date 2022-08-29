@extends('layouts.main')
@section('content')
<div class="container">
    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>ဘောက်ချာ နံပါတ် - {{$voucheridmember}}</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>

    <div class="second-section-box mb-3">
        <div class="row">
            <div class="col-md-12 page-title d-flex align-items-center justify-content-md-end justify-content-sm-start">
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal">
                    <i class="fas fa-plus-circle"></i> ဖောက်သည် အသစ်ထည့်မည်
                </button>
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

    <form action="/bets/create" method="post">
        @csrf

        <input type="hidden" name="vouchermemberid" id="voucheridmember" value="{{$voucheridmember}}">

        <div class="customer-container mb-5">
            <div class="row">
                <div class="col-md-4">
                    <label for="loyalcustomer" class="form-label">စာရင်းရှိပြီးဖောက်သည်</label>
                    <select class="form-select" name="loyalcustomer" id="loyalcustomer" onchange="loyalfunction()">
                        <option selected>တစ်ဦး ရွေးချယ်ပါ</option>
                        @foreach($permanent_members as $permanent_member)
                        <option value="{{$permanent_member->id}}">{{$permanent_member->name}}~{{$permanent_member->percentage}}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 validate-design">
                    <label for="normalcustomer" class="form-label">ဖောက်သည်အသစ်</label>
                    <input type="text" class="form-control" id="normalcustomer" name="normalcustomer" onchange="loyalfunction()">
                </div>
                <div class="col-md-4">
                    <label for="percentage" class="form-label">ပေးမည့် ရာခိုင်နှုန်း</label>
                    <input type="text" class="form-control" id="percentage" name="percentage" value="0">
                </div>
            </div>
        </div>

        <div class="bet-container">
            <div class="row">
                <div class="col-xl-7 col-md-12 col-sm-12 order-md-0 order-sm-1 order-xs-1 bet-amount-container">
                    <div class="row">
                        {{-- <div class="col-md-4 over-amounts overscroll mb-5">
                            <table class="table table-hover table-bordered">
                                <thead class="bg-white sticky-top">
                                    <tr>
                                        <th scope="col">00 မှ 99</th>
                                        <th scope="col">အပို</th>
                                    </tr>
                                </thead>
                                <tbody id="over_table_body">
                                    @foreach($betsall as $overRow)
                                    <tr class="over_table_rows">
                                        <th scope="row">{{$overRow->number}}</th>
                        <td>{{$overRow->over_amount}}</td>
                        </tr>
                        @endforeach
                        </tbody>
                        </table>
                    </div> --}}
                    <div class="col-md-6 bet-amounts-1 overscroll mb-5 onmobile">
                        <table class="table table-hover table-bordered">
                            <thead class="bg-white sticky-top">
                                <tr>
                                    <th scope="col">00 မှ 49</th>
                                    <th scope="col">ထိုးကြေး</th>
                                </tr>
                            </thead>
                            <tbody id="first_table_body">
                                @foreach($firstRows as $firstrow)
                                <tr class="first_table_rows">
                                    <th scope="row">{{$firstrow->number}}</th>
                                    <td>{{$firstrow->amount}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6 bet-amounts-2 overscroll mb-5 onmobile">
                        <table class="table table-hover table-bordered">
                            <thead class="bg-white sticky-top">
                                <tr>
                                    <th scope="col">50 မှ 99</th>
                                    <th scope="col">ထိုးကြေး</th>
                                </tr>
                            </thead>
                            <tbody id="second_table_body">
                                @foreach($secondRows as $secondrow)
                                <tr class="second_table_rows">
                                    <th scope="row">{{$secondrow->number}}</th>
                                    <td>{{$secondrow->amount}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 col-md-12 col-sm-12 order-md-1 order-sm-0 order-xs-0 bet-list-container">
                <div class="row sticky-top">
                    <div class="col-md-4">
                        <label for="bettype" class="form-label">ထိုးကြေး အမျိုးအစား</label>
                        <select class="form-select validate-bettype" name="bettype" id="bettype" onchange="zerofunction(event)">
                            <option value="0" disabled selected>တစ်မျိုး ရွေးချယ်ပါ</option>
                            @foreach ($number_type as $type)

                            <option value="{{$type->id}}">{{$type->number_types}}</option>

                            @endforeach


                        </select>
                    </div>
                    <div class="col-md-4 validate-design" id="pink_bet_box">
                        <label for="betno" class="form-label">နံပါတ်</label>
                        <input type="text" class="form-control validate-betnumber" id="betno" name="betno" disabled onkeyup="myFunction(this.value)" onchange="rslashfunction()">
                        <div id="counterDisplay" class=""></div>

                    </div>
                    <div class="col-md-4 validate-design">
                        <label for="betamount" class="form-label">ထိုးကြေး</label>
                        <input type="number" class="form-control" id="betamount" name="betamount">
                    </div>
                    <div class="col-md-12 my-3">

                        @foreach ($changelimit as $change_limit)
                        @if($change_limit->is_offButton != '1')
                        <button id="addbet" class="btn btn-primary firstchildonly"><i class="fas fa-plus-circle"></i> ပေါင်းထည့်မည်။</button>
                        @else
                        <span class="firstchildonly">အချိန်ပြည့်သွားပါပြီ။ ထိုးခွင့်မရှိတော့ပါ။</span>
                        @endif
                        @endforeach

                        <div class="loader_container">
                            {{-- <div class="loader"></div> --}}
                            <div id="spinloader">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 my-3 d-sm-block d-md-none">
                        <button id="od_report" class="btn btn-info" form="sendReportForm"><i class="fab fa-telegram-plane"></i> ဂဏန်းတင်မည်။</button>
                    </div>
                    <div class="col-md-12 mt-3">

                        <h3>ထိုးကြေးမှတ်တမ်း</h3>
                        <div class="alert alert-success alert-dismissible fade show my-3" id="betalert" role="alert">
                            <strong>ထိုးကြေးအသစ်ထည့်ပြီး</strong>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="alert alert-success alert-dismissible fade show my-3" id="betalertdelete" role="alert">
                            <strong>ထိုးကြေးဖျက်ပြီးပါပြီ</strong>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">အမျိုးအစား</th>
                                    <th scope="col">နံပါတ်</th>
                                    <th scope="col">အရေအတွက်</th>
                                    {{-- <th scope="col">လုပ်ဆောင်မှု</th> --}}
                                </tr>
                            </thead>
                            <tbody id="testshow">
                                <tr class="betslot">
                                    <td colspan="5" class="text-center">ထိုးကြေးများကို ဤနေရာတွင် ပြသမည်။</td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- <div id="testshow">

                            </div> --}}

                        {{-- <div class="mt-3 mb-5">
                            <label for="totalbet" class="form-label">ထိုးကြေးစုစုပေါင်း</label>
                            <input type="number" class="form-control" id="totalbet" name="totalbet" disabled>
                        </div> --}}
                    </div>

                </div>
            </div>
        </div>
</div>
</form>
<div class="row">
    <div class="col-md-12 mt-3">
        <a class="btn btn-success d-none" href="/bets/create" role="button">သေချာပါသည်
        </a>
        <form action="/ownerdetails/sendReport/{{$reportTo}}" method="post" id="sendReportForm">
            @csrf
            <div class="d-flex justify-content-between align-items-center d-sm-none d-md-block mb-2 onmobile">
                <button id="od_report" class="btn btn-info onmobile" formaction="/ownerdetails/sendReport/{{$reportTo}}"><i class="fab fa-telegram-plane"></i> ဂဏန်းတင်မည်။</button>
            </div>
            <div class="overscroll">
                <table class="table table-hover table-bordered">
                    <thead class="bg-white sticky-top">
                        <tr>
                            <th scope="col">00 to 99</th>
                            <th scope="col">တင်မည့်ငွေစာရင်း</th>
                            <th scope="col">ပြန်အမ်းသည့်ငွေ ({{$return_amount}})</th>
                            <th scope="col">ပိတ်ဂဏန်းပြန်အမ်းငွေ ({{$off_return_amount}})</th>
                        </tr>
                    </thead>
                    <tbody id="report_table_body">
                        @foreach($displayOwnerDetails as $row)
                        <tr class="report_table_slot">
                            <th scope="row">{{$row->number}}</th>
                            <td>{{$row->final_amount}}
                                <input type="hidden" name="reportamount{{ $loop->index }}" value="{{$row->final_amount}}">
                                <input type="hidden" name="{{ $loop->index }}" value="{{$row->number}}">
                            </td>
                            <td>{{$row->return_amount}}</td>
                            <td>{{$row->off_return_amount}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    {{-- <tfoot>
                            <tr class="table-secondary">
                                <th scope="col" colspan="2">Over Total</th>
                                <th scope="col" id="over_amount_total">{{$reporttotalamount}}</th>
                    </tr>
                    </tfoot> --}}
                </table>
            </div>
        </form>
    </div>
</div>
</div>


<!-- Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="customerModalLabel">အမြဲတမ်း ဖောက်သည် စာရင်းသွင်းရန်</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/customers/create" method="post">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="customername" class="form-label text-dark">အမည်</label>
                        <input type="text" class="form-control" id="customername" name="customername">
                    </div>
                    <div class="mb-3">
                        <label for="customerpercent" class="form-label text-dark">ပေးမည့် ရာခိုင်နှုန်း</label>
                        <input type="number" class="form-control" id="customerpercent" name="customerpercent" min="0" max="100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ပိတ်မည်</button>
                    <button type="submit" class="btn btn-primary">အကောင့် ဖွင့်မည်</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- <script type="text/javascript">
    $(document).ready(function() {
        $("#deletebet").click(function(e) {
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr('content')
                }
            });
            $.ajax({
                url: "http://127.0.0.1:8000/bets/delete"
                , method: 'POST'
                , data: {
                    // normalcustomer: $("#normalcustomer").val() ,// Check Here,
                    // , loyalcustomer: $("#loyalcustomer").val() // Check Here
                    number_type_id: $("#hiddennumbertype").val(),
                    order_detail_id:$('#order_detail_id').val(),
                    , hidden_member_id: $("#hidden_member_id").val()
                    , bettype: $("#hiddennumbertype").val()
                    , hidden_amount: $("#hiddenamount").val()
                    , vouchermemberid: $("#voucheridmember").val()
                    , customnumber: $("#hiddencustomnumber").val()

                }
            })
        });
    }); 

</script> --}}

<script type="text/javascript">
    $(document).ready(function() {


        // $("#deletebet").click(function(e) {
        //     e.preventDefault();
        // });

        $("#addbet").click(function(e) {
            e.preventDefault();
            // $("#deletebet").click(function(e) {
            //     e.preventDefault();
            // });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr('content')
                }
            });
            // $.post("/bets/create", {
            //         normalcustomer: $("#normalcustomer").val()
            //         , loyalcustomer: $("#loyalcustomer").val()
            //         , bettype: $("#bettype").val()
            //         , betamount: $("#betamount").val()
            //         , voucheridmember: $("#voucheridmember").val()
            //     }
            //     , function(data, status) {
            //         alert("Data: " + data + "\nStatus: " + status);
            //     });
            $.ajax({
                // url: "http://127.0.0.1:8000/bets/create"
                // url: "https://6666662d.neptune.link/bets/create"
                url: "https://6666662d.com/bets/create"
                , method: 'POST'
                , data: {
                    normalcustomer: $("#normalcustomer").val()
                    , loyalcustomer: $("#loyalcustomer").val()
                    , bettype: $("#bettype").val()
                    , betamount: $("#betamount").val()
                    , vouchermemberid: $("#voucheridmember").val()
                    , customnumber: $("#betno").val()
                    , percentage: $("#percentage").val()
                }
                , beforeSend: function() {
                    $("#spinloader").show();
                    $("#betalert").hide();
                }
                , success: function(result) {
                    console.log(result);
                    $("#spinloader").hide();
                    $("#betalert").show();

                    setTimeout(function() {
                        $('#betalert').hide();
                    }, 3000);

                    $(".first_table_rows").remove();
                    for (var j = 0; j < result.fristCol.length; j++) {
                        $('#first_table_body').append(`
                        <tr class="first_table_rows">
                            <th scope="row">` + result.fristCol[j].number + `</th>
                            <td>` + result.fristCol[j].amount + `</td>
                        </tr>
                        `);
                    }

                    $(".second_table_rows").remove();
                    for (var k = 0; k < result.secCol.length; k++) {
                        $('#second_table_body').append(`
                        <tr class="second_table_rows">
                            <th scope="row">` + result.secCol[k].number + `</th>
                            <td>` + result.secCol[k].amount + `</td>
                        </tr>
                        `);
                    }

                    $(".over_table_rows").remove();
                    for (var m = 0; m < result.betsall.length; m++) {
                        $('#over_table_body').append(`
                        <tr class="over_table_rows">
                            <th scope="row">` + result.betsall[m].number + `</th>
                            <td>` + result.betsall[m].over_amount + `</td>
                        </tr>
                        `);
                    }


                    $(".report_table_slot").remove();
                    for (var n = 0; n < result.betsall.length; n++) {
                        $('#report_table_body').append(`
                        <tr class="report_table_slot">
                                <th scope="row">${result.betsall[n].number}</th>
                                <td>${result.betsall[n].final_amount}
                                    <input type="hidden" name="reportamount${n}" value="${result.betsall[n].final_amount}">
                                    <input type="hidden" name="${n}" value="${result.betsall[n].number}">
                                </td>
                                <td>${result.betsall[n].return_amount}</td>
                            </tr>
                        `);
                    }


                    $(".betslot").remove();
                    var c = 1;
                    var totalamount = 0;
                    for (var i = 0; i < result.orderDetail.length; i++) {
                        $('#testshow').append(`
                        <tr class="betslot">
                                        <th scope="row">` + c + `</th>
                                        <td>${result.orderDetail[i].number_type}</td>
                                        <td>${result.orderDetail[i].pink_number}</td>
                                        <td>` + result.orderDetail[i].amount +
                            `</td>
                                    </tr>
                        `);
                        c++;
                        totalamount += result.orderDetail[i].amount;
                        $("#totalbet").val(`${totalamount}`);
                        $(".gg").on('click', function(e) {
                            e.preventDefault()
                        });

                    }
                }
            })
        });


    });


    function mydelete(j) {


        $("#deletebet" + j).click(function(e) {

            e.preventDefault();
            console.log($("#myvoucher").val());
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr('content')
                }
            });
            $.ajax({
                 url: "http://127.0.0.1:8000/bets/delete"
                // url: "https://6666662d.neptune.link/bets/delete"
                , method: 'POST'
                , data: {
                    // normalcustomer: $("#normalcustomer").val() ,// Check Here,
                    // , loyalcustomer: $("#loyalcustomer").val() // Check Here
                    number_type_id: $("#hiddennumbertype" + j).val()
                    , order_detail_id: $("#order_detail_id" + j).val()

                    , hidden_member_id: $("#hidden_member_id" + j).val()
                    , bettype: $("#hiddennumbertype" + j).val()
                    , hidden_amount: $("#hiddenamount" + j).val()
                    , vouchermemberid: $("#myvoucher" + j).val()
                    , customnumber: $("#hiddencustomnumber" + j).val()

                }
                , success: function(result) {
                    console.log("Delete");
                    console.log(result);
                    $("#spinloader").hide();
                    $("#betalertdelete").show();

                    setTimeout(function() {
                        $('#betalertdelete').hide();
                    }, 3000);

                    $(".first_table_rows").remove();
                    for (var j = 0; j < result.fristCol.length; j++) {
                        $('#first_table_body').append(`
                        <tr class="first_table_rows">
                            <th scope="row">` + result.fristCol[j].number + `</th>
                            <td>` + result.fristCol[j].amount + `</td>
                        </tr>
                        `);
                    }

                    $(".second_table_rows").remove();
                    for (var k = 0; k < result.secCol.length; k++) {
                        $('#second_table_body').append(`
                        <tr class="second_table_rows">
                            <th scope="row">` + result.secCol[k].number + `</th>
                            <td>` + result.secCol[k].amount + `</td>
                        </tr>
                        `);
                    }

                    $(".over_table_rows").remove();
                    for (var m = 0; m < result.betsall.length; m++) {
                        $('#over_table_body').append(`
                        <tr class="over_table_rows">
                            <th scope="row">` + result.betsall[m].number + `</th>
                            <td>` + result.betsall[m].over_amount + `</td>
                        </tr>
                        `);
                    }

                    $(".betslot").remove();
                    var c = 1;
                    var totalamount = 0;
                    for (var i = 0; i < result.orderDetail.length; i++) {
                        $('#testshow').append(`
                        <tr class="betslot">
                                        <th scope="row">` + c + `</th>
                                        <td>${result.orderDetail[i].number_type}</td>
                                        <td>${result.orderDetail[i].pink_number}</td>
                                        <td>` + result.orderDetail[i].amount +
                            `</td>        
                                    </tr>
                        `);
                        c++;
                        totalamount += result.orderDetail[i].amount;
                        $("#totalbet").val(`${totalamount}`);
                        $(".gg").on('click', function(e) {
                            e.preventDefault()
                        });

                    }


                }
            })
        });
    }




    // $(document).ready(function() {
    //     setTimeout(function() {
    //         $('#betalert').remove();
    //     }, 3000);
    // });

    $(document).ready(function() {
        $('.validate-bettype').change(function() {

            var select_value = $(this).val();

            if (select_value == '1' || select_value == '6' || select_value == '7' || select_value == '8' || select_value == '17' || select_value == '18' || select_value == '19' || select_value == '20') {
                $('.validate-betnumber').prop('disabled', '');
            } else {
                $('.validate-betnumber').prop('disabled', 'disabled')
            }
        });
    });

    function loyalfunction() {
        var i = document.getElementById("loyalcustomer").value;
        var nc = document.getElementById("normalcustomer").value;
        if (i >= 1) {
            document.getElementById('normalcustomer').disabled = true;
            document.getElementById('normalcustomer').value = "";
            var select = document.getElementById('loyalcustomer');
            var option = select.options[select.selectedIndex];

            document.getElementById('percentage').value = option.text;
            var tosplit_percent = document.getElementById('percentage').value;
            var splited_percent = tosplit_percent.split('~');
            document.getElementById('percentage').value = splited_percent[1];
            document.getElementById('addbet').disabled = false;

        } else if (nc.length == 0 || i >= 1) {
            document.getElementById('normalcustomer').disabled = false;
            document.getElementById('addbet').disabled = true;
        } else {
            document.getElementById('normalcustomer').disabled = false;
            document.getElementById('percentage').value = '0';
            document.getElementById('addbet').disabled = false;
        }

    }
    loyalfunction();

    function hideAddButton(val) {

        var nc = document.getElementById("normalcustomer").value;
        var lc = document.getElementById("loyalcustomer").value;
        var ba = document.getElementById("betamount").value;
        if (nc.length == 0 || lc >= 1 || ba.length == 0) {
            document.getElementById('addbet').disabled = true;
        }
         else {
            document.getElementById('addbet').disabled = false;
        }
    }


    function zerofunction(val) {
        var i = document.getElementById("bettype").value;
        document.getElementById("betno").value = "";
        document.getElementById('counterDisplay').innerHTML = "";
        // document.getElementById('addbet').disabled = false;
        document.getElementById('betamount').value = "";
        if (i == '17') {
            $("#betamount").prop("disabled", true);
        } else {
            $("#betamount").prop("disabled", false);
        }



        if (i == '2') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="01, 10, 12, 21, 23, 32, 34, 43, 45, 54, 56, 65, 67, 76, 78, 87, 89, 98, 09, 90" name="betno" id="pink_bets">`);
        } else if (i == '3') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="00, 11, 22, 33, 44, 55, 66, 77, 88, 99" name="betno" id="pink_bets">`);
        } else if (i == '4') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="01, 03, 05, 07, 09, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35, 37, 39, 41, 43, 45, 47, 49, 51, 53, 55, 57, 59, 61, 63, 65, 67, 69, 71, 73, 75, 77, 79, 81, 83, 85, 87, 89, 91, 93, 95, 97, 99" name="betno" id="pink_bets">`);
        } else if (i == '5') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="00, 02, 04, 06, 08, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54, 56, 58, 60, 62, 64, 66, 68, 70, 72, 74, 76, 78, 80, 82, 84, 86, 88, 90, 92, 94, 96, 98" name="betno" id="pink_bets">`);
        } else if (i == '9') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="00, 02, 04, 06, 08, 20, 22, 24, 26, 28, 40, 42, 44, 46, 48, 60, 62, 64, 66, 68, 80, 82, 84, 86, 88" name="betno" id="pink_bets">`);
        } else if (i == '10') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="11, 13, 15, 17, 19, 31, 33, 35, 37, 39, 51, 53, 55, 57, 59, 71, 73, 75, 77, 79, 91, 93, 95, 97, 99" name="betno" id="pink_bets">`);
        } else if (i == '11') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="21, 23, 25, 27, 29, 41, 43, 45, 47, 49, 61, 63, 65, 67, 69, 81, 83, 85, 87, 89, 01, 03, 05, 07, 09" name="betno" id="pink_bets">`);
        } else if (i == '12') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="10, 12, 14, 16, 18, 30, 32, 34, 36, 38, 50, 52, 54, 56, 58, 70, 72, 74, 76, 78, 90, 92, 94, 96, 98" name="betno" id="pink_bets">`);
        } else if (i == '13') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="05, 16, 27, 38, 49, 50, 61, 72, 83, 94" name="betno" id="pink_bets">`);
        } else if (i == '14') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="00, 01, 02, 03, 04, 05, 06, 07, 08, 09, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49" name="betno" id="pink_bets">`);
        } else if (i == '15') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99" name="betno" id="pink_bets">`);
        } else if (i == '16') {
            $("#pink_bets").remove();
            $("#pink_bet_box").append(`<input type="hidden" value="07, 18, 24, 35, 69, 70, 81, 42, 53, 96" name="betno" id="pink_bets">`);
        } else {
            $("#pink_bets").remove();
        }
    }


    document.getElementById('addbet').disabled = true;


    function myFunction(val) {
        var x = document.getElementById("betno").value;
        var y = document.getElementById("bettype").value;
        var z = document.getElementById("betamount").value;

        if (y == '1' && x.length > 2) {
            // document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၂ လုံးထက် မပိုရ";
            // document.getElementById('addbet').disabled = true;
        } else if (y != '1' && y != '17' && y != '18' && y != '19' && x.length >= 2) {
            document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၁ လုံးထက် မပိုရ";
            document.getElementById('addbet').disabled = true;
        } else if (y == '18' && x.length <= 2) {
            document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၃ လုံးထက် မနည်းရ";
            document.getElementById('addbet').disabled = true;
        } else if (y == '19' && x.length <= 2) {
            document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၃ လုံးထက် မနည်းရ";
            document.getElementById('addbet').disabled = true;
        } else if (y == '18' && x.length >= 8) {
            document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၇ လုံးထက် မပိုရ";
            document.getElementById('addbet').disabled = true;
        } else if (y == '19' && x.length >= 8) {
            document.getElementById('counterDisplay').innerHTML = "ဂဏန်း ၇ လုံးထက် မပိုရ";
            document.getElementById('addbet').disabled = true;
        } else {
            document.getElementById('counterDisplay').innerHTML = "";
            document.getElementById('addbet').disabled = false;
        }
    }


    // 12/1000R500
    function rslashfunction() {
        var i = document.getElementById("betno").value;

        var split_dash = i.split('/');
        var split_R = split_dash[1].split('R');
        var total_split_R = parseInt(split_R[0]) + parseInt(split_R[1]);

        document.getElementById("betamount").value = total_split_R;
        console.log(total_split_R);

        // var splited_percent = tosplit_percent.split('~');
        // document.getElementById('percentage').value = splited_percent[1];

        // if (i >= 1) {
        //     document.getElementById('normalcustomer').disabled = true;
        //     var select = document.getElementById('loyalcustomer');
        //     var option = select.options[select.selectedIndex];

        //     document.getElementById('percentage').value = option.text;
        //     var tosplit_percent = document.getElementById('percentage').value;
        //     var splited_percent = tosplit_percent.split('~');
        //     document.getElementById('percentage').value = splited_percent[1];

        // } else {
        //     document.getElementById('normalcustomer').disabled = false;
        //     document.getElementById('percentage').value = '0';
        // }

    }
    loyalfunction();

</script>
@endsection
