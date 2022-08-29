@extends('layouts.main')
@section('content')
<div class="container">
    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>ပေါက်ဂဏန်း</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>
    <form action="/luckynumber/detail" method="get">
        @csrf
        <div class="owner-container mb-5">
            <div class="row mb-3">

                <div class="col-md-4">
                    <label for="luckyno" class="form-label">ပေါက်ဂဏန်း</label>
                    <input type="number" class="form-control" name="luckyno" id="luckyno" min="0" max="99">
                </div>
                <div class="col-md-4">
                    <label for="betno" class="form-label" style="visibility: hidden">လုပ်ဆောင်ချက်များ</label>
                    <br>
                    {{-- <a class="btn btn-primary" href="#" role="button" id="">Return --}}
                    </a>
                    <button type="submit" class="btn btn-primary" id="">ပြန်နှုန်း</button>
                    <div class="loader_container">
                        {{-- <div class="loader"></div> --}}
                        <div id="spinloader">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="my-3 d-none">
            Return Total : 100,000
        </h3>
        <div class="alert alert-success alert-dismissible fade show my-3" id="betalert" role="alert">
            <strong>Lucky Users Found!</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        {{-- <div class="alert alert-danger alert-dismissible fade show my-3" id="betalertnot" role="alert">
            <strong>Lucky Users not Found:(</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div> --}}

        <div style="overflow-x:auto;">
            <table class="table" id="luckynumber_table" style="width:100%">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">ဘောက်ချာ</th>
                        <th scope="col">နေ့စွဲ</th>
                        <th scope="col">အချိန်</th>
                        <th scope="col">အနိုင်ရသူ</th>
                        <th scope="col">ရာခိုင်နှုန်း</th>
                        {{-- <th scope="col">Percent</th> --}}
                        <th scope="col">အရောင်းနှုန်း</th>
                        <th scope="col">လျော်မည့်အဆ</th>
                        {{-- <th scope="col">SR Total</th>
                        <th scope="col">RT Total</th>
                        <th scope="col">AMT</th>
                        <th scope="col">Total</th> --}}
                    </tr>
                </thead> 
                <tbody id="luckyuserbody">
                    @if($luckynumber != null )
                    @foreach ($luckynumber as $ln)
                    @if(empty($ln->myorders->voucher_number))
                    @else
                    <tr class="lucky_user_rows">
                        <td class="align-middle">{{ $loop->index }}</td>
                        <td class="align-middle">{{ $ln->myorders->voucher_number ?? 'ဖျက်ထားသည့် voucher များ'  }}</td>
                        <td class="align-middle">{{date_format($ln->created_at,'d-M-Y')}} </td>
                        <td class="align-middle">{{date_format($ln->created_at,'A')}} </td>
                        <td class="align-middle">{{ $ln->luckymember->name }}</td>
                        {{-- <td class="align-middle">{{ $ln->mynum->number_types }} </td> --}}
                        <td class="align-middle">{{ $ln->luckymember->percentage }}</td>
                        {{-- <td class="align-middle">{{ $ln->owner->name }}</td> --}}
                        <td class="align-middle">{{ $ln->luckymemberpercentage->ownerdetails_overrate }}</td>
                        <td class="align-middle">{{ $ln->luckymemberpercentage->ownerdetails_returnrate }}</td>
                        @endif
                        @endforeach
                        @else
                        <td scope="row" class="align-middle text-center" colspan="12">မတွေ့ပါ</td>
                        @endif
                        <!-- <tr class="lucky_user_rows">
                        <td scope="row" class="align-middle text-center" colspan="12">Lucky Number နှင့်တူသော Cusotomer များကို ပြသမည်။</td>-->
                    </tr>
                </tbody>
            </table>
        </div>




        {{-- <div class="detail-container mt-5">
            <h3 class="my-3">
                Over Number & Amount
            </h3>

            <table class="table table-info" id="luckyovernumber_table" style="width:100%">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Winner</th>
                        <th scope="col">%</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Percent</th>
                        <th scope="col">Total</th>
                        <th scope="col">LN</th>
                        <th scope="col">AMT</th>
                    </tr>
                </thead>
                <tbody>
                    @if(empty($luckynymber))

                    @else
                    @foreach($luckynumber as $bet)
                    <td class="align-middle">{{$bet->number}}</td>
        @endforeach
        @endif
        <tr>
            <td scope="row" class="align-middle">1</td>
            <td class="align-middle">18-Oct-2021</td>
            <td class="align-middle">PM</td>
            <td class="align-middle">KP</td>
            <td class="align-middle">210,000</td>
            <td class="align-middle">168,000</td>
            <td class="align-middle">1,200,000</td>
            <td class="align-middle">1,005,888</td>
            <td class="align-middle">01</td>
            <td class="align-middle">15,000</td>
        </tr>
        <tr>
            <td scope="row" class="align-middle">2</td>
            <td class="align-middle">18-Oct-2021</td>
            <td class="align-middle">PM</td>
            <td class="align-middle">KK</td>
            <td class="align-middle">210,000</td>
            <td class="align-middle">168,000</td>
            <td class="align-middle">1,200,000</td>
            <td class="align-middle">1,005,888</td>
            <td class="align-middle">01</td>
            <td class="align-middle">15,000</td>
        </tr>
        </tbody>
        </table>
</div> --}}
</form>
</div>



<script>
    $(document).ready(function() {
        $("#lc_return").click(function(e) {
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr('content')
                }

            });

            let datefilter = document.querySelectorAll('.date_slice');
            for (let element of datefilter) {
                let originalDate = element.textContent;
                let modifiedDate = originalDate.slice(0, 10);
                element.innerHTML = modifiedDate;
            }


            let timefilter = document.querySelectorAll('.time_slice');
            for (let element of timefilter) {
                let originalTime = element.textContent;
                let modifiedTime = originalTime.slice(11, 16);
                element.innerHTML = modifiedTime;
            }
            $.ajax({
                // url: "http://127.0.0.1:8000/luckynumber"
                // url: "https://6666662d.neptune.link/luckynumber"
                url: "https://6666662d.com/luckynumber"
                , method: 'POST'
                , data: {
                    luckyno: $("#luckyno").val()
                , }
                , beforeSend: function() {
                    $("#spinloader").show();
                    $("#betalert").hide();
                }
                , success: function(result) {
                    console.log(result);
                    if (result.luckynumber.length > 0) {
                        $("#spinloader").hide();
                        $("#betalert").show();
                        setTimeout(function() {
                            $('#betalert').hide();
                        }, 3000);

                        $(".lucky_user_rows").remove();
                        // console.log(result.luckynumber[0].length)

                        for (var i = 0; i < result.luckynumber.length; i++) {

                            // for (var j = 0; j < result.luckynumber[i].member_i_d.length; j++) {
                            // for (var k = 0; k < result.vouchernumber.length; k++) {
                            for (var j = 0; j < result.luckynumber[i].length; j++) {
                                console.log(result.luckynumber[i][i].name);
                                $('#luckyuserbody').append(`
                                <tr class="lucky_user_rows">
                                    <th scope="row" class="align-middle">${result.luckynumber[i][j].id}</th>
                                    <td class="align-middle">${result.luckynumber[i][j].my_orders[j].voucher_number}</td>
                                    <td class="align-middle date_slice">${result.luckynumber[i][j].created_at}</td>
                                    <td class="align-middle time_slice">${result.luckynumber[i][j].created_at}</td>
                                    <td class="align-middle">${result.luckynumber[i][j].name}</td>
                                    <td class="align-middle">${result.luckynumber[i][j].percentage}</td>
                                    <td class="align-middle">${result.luckynumber[i][j].bet_number_for_members[j].amount * result.luckynumber[i][j].percentage / 100 }</td>
                                    <td class="align-middle">${result.luckynumber[i][j].member_user[j].ownerdetails_overrate}</td>
                                    <td class="align-middle">${result.luckynumber[i][j].member_user[j].ownerdetails_returnrate}</td>
                                    <td class="align-middle">${(parseInt(result.luckynumber[i][j].member_user[j].ownerdetails_overrate) * parseInt(result.luckynumber[i][j].bet_number_for_members[j].amount))/ 100} </td>
                                    <td class="align-middle">${parseInt(result.luckynumber[i][j].member_user[j].ownerdetails_returnrate) * parseInt(result.luckynumber[i][j].bet_number_for_members[j].amount)} </td>
                                    <td class="align-middle">${result.singleamount.final_amount}</td>
                                    <td class="align-middle">${(parseInt(result.luckynumber[i][j].member_user[j].ownerdetails_returnrate) * parseInt(result.luckynumber[i][j].bet_number_for_members[j].amount)) - ((parseInt(result.luckynumber[i][j].member_user[j].ownerdetails_overrate) * parseInt(result.luckynumber[i][j].bet_number_for_members[j].amount))/ 100)}</td>

                                </tr>
                        `);
                            }

                            // }
                            // }
                            let datefilter = document.querySelectorAll('.date_slice');
                            for (let element of datefilter) {
                                let originalDate = element.textContent;
                                let modifiedDate = originalDate.slice(0, 10);
                                element.innerHTML = modifiedDate;
                            }

                            let timefilter = document.querySelectorAll('.time_slice');
                            for (let element of timefilter) {
                                let originalTime = element.textContent;
                                let modifiedTime = originalTime.slice(11, 16);
                                element.innerHTML = modifiedTime;
                            }

                        }
                    } else {
                        $("#spinloader").hide();
                        $("#betalertnot").show();

                        setTimeout(function() {
                            $('#betalertnot').hide();
                        }, 3000);
                        console.log("not found")
                    }







                }
            })
        });

        $(document).ready(function() {
            var luckytable = $('#luckynumber_table').DataTable({
                responsive: true
            });
            new $.fn.dataTable.FixedHeader(luckytable);
        });
        // $(document).ready(function() {
        //     var overtable = $('#luckyovernumber_table').DataTable({
        //         responsive: true
        //     });
        //     new $.fn.dataTable.FixedHeader(overtable);
        // });
    });

</script>


@endsection
