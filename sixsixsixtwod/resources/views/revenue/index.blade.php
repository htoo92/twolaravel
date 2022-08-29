@extends('layouts.main')
@section('content')
<div class="container">
    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>Profit & Loss</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>
    <form action="/revenue" method="post">
        @csrf
        <div class="owner-container mb-5">
            <div class="row mb-3">

                <div class="col-md-3"></div>
                <div class="col-md-2">
                    <label for="startdate" class="form-label">Start Date</label>
                    <input type="date" class="form-control clickable" id="startdate" name="startdate">

                </div>
                <div class="col-md-2">
                    <label for="enddate" class="form-label">End Date</label>
                    <input type="date" class="form-control clickable" id="enddate" name="enddate">
                </div>
                <div class="col-md-2">
                    <label for="betno" class="form-label" style="visibility: hidden">လုပ်ဆောင်ချက်များ</label>
                    <br>
                    {{-- <a class="btn btn-primary" href="#" role="button" id="lc_return">Return --}}
                    </a>
                    <button type="submit" class="btn btn-primary" id="revenue_find">Find</button>
                    <div class="loader_container">
                        {{-- <div class="loader"></div> --}}
                        <div id="spinloader">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3"></div>
            </div>
        </div>
    </form>


    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6 col-sm-12">
            <table class="table table-borderless mb-3">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Particular</th>
                        <th scope="col">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">1</th>
                        <td>Sale Amount</td>
                        <td>60000</td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Over Sale Amount</td>
                        <td>211000</td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>Sale % (Give)</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <th scope="row">3</th>
                        <td>Over Sale % (Receive)</td>
                        <td>16880</td>
                    </tr>
                    <tr>
                        <th scope="row">4</th>
                        <td>Total Return (Home)</td>
                        <td>96000</td>
                    </tr>
                    <tr>
                        <th scope="row">5</th>
                        <td>Total Return (Over)</td>
                        <td>1200000</td>
                    </tr>
                    <tr>
                        <th scope="row">6</th>
                        <td>Net Return</td>
                        <td>1104000</td>
                    </tr>
                    <tr>
                        <th scope="row">7</th>
                        <td>Profit & Loss</td>
                        <td>969800</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-3"></div>
    </div>
</div>


<script>
    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

    var checkin = $('#startdate').datepicker({

        beforeShowDay: function(date) {
            return date.valueOf() >= now.valueOf();
        }
        , autoclose: true

    }).on('changeDate', function(ev) {
        if (ev.date.valueOf() > checkout.datepicker("getDate").valueOf() || !checkout.datepicker("getDate").valueOf()) {

            var newDate = new Date(ev.date);
            newDate.setDate(newDate.getDate() + 1);
            checkout.datepicker("update", newDate);

        }
        $('#enddate')[0].focus();
    });


    var checkout = $('#enddate').datepicker({
        beforeShowDay: function(date) {
            if (!checkin.datepicker("getDate").valueOf()) {
                return date.valueOf() >= new Date().valueOf();
            } else {
                return date.valueOf() > checkin.datepicker("getDate").valueOf();
            }
        }
        , autoclose: true

    }).on('changeDate', function(ev) {});

</script>

@endsection
