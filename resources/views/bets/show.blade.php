@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>ထိုးကြေး အသေးစိတ်</h3>
            </div>
            <div class="col-md-6 date-time text-md-right text-left d-flex align-items-center justify-content-md-end justify-content-sm-start"><span class="web-time">{{date('d-F-Y (A)') }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6 col-sm-12" id="print-table">
            <table class="table table-borderless mb-3">
                <tbody>
                    <tr>
                        <th scope="row">ဘောက်ချာ နံပါတ်</th>
                        <td>{{$orders_to_show->voucher_number}}</td>
                    </tr>
                    <tr>
                        <th scope="row">ရက်စွဲ နှင့် အချိန်</th>
                        <td>
                            @foreach($members_to_show as $member)
                            @if($orders_to_show->member_id == $member->id)
                            {{date_format($member->created_at,'d-M-Y')}}
                            ({{date_format($member->created_at,'A')}})
                            @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">အမည်</th>
                        <td>
                            @foreach($members_to_show as $member)
                            @if($orders_to_show->member_id == $member->id)
                            {{$member->name}}
                            @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ရာခိုင်နှုန်း</th>
                        <td>
                            @foreach($members_to_show as $member)
                            @if($orders_to_show->member_id == $member->id)
                            {{$member->percentage}}
                            @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">စုစုပေါင်း</th>
                        <td>{{$totalAmount}}</td>
                    </tr>
                    <tr>
                        <th scope="row">ထိုးကြေးများ</th>
                        <td>
                            <ol>
                                @foreach($bet_number as $orderdetail)
                                <li>
                                    {{$orderdetail->number_type}} - {{$orderdetail->pink_number}}
                                </li>
                                @endforeach
                            </ol>
                        </td>
                    </tr>
                </tbody>
            </table>
            <a class="btn btn-success m-1" onclick="myPrint()">
                <i class="fas fa-print"></i> Print ထုတ်မည်
            </a>
        </div>
    </div>
    <div class="col-md-3"></div>
</div>

<div class="cc" style="display:none;">

    <div id="to-print-real" style=" ">
        <div class="invoice-box">
            <table>
                <tr class="top">
                    <td colspan="2">
                        <table>
                            <tr>
                                <td class="title">
                                    <!-- <img
                          src="./images/logo.png"
                          alt="Company logo"
                          style="width: 100%; max-width: 300px"
                        /> -->
                                    6666662d.com
                                </td>

                                <td>
                                    ဘောက်ချာအမှတ် - {{$orders_to_show->voucher_number}} <br />
                                    အချိန်နှင့်ရက်စွဲ: {{date_format($member->created_at,'d-M-Y')}}
                                    ({{date_format($member->created_at,'A')}})
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr class="information">
                    <td colspan="2">
                        <table>
                            <tr>
                                <td></td>

                                <td>ဝယ်ယူသူ - @foreach($members_to_show as $member)
                                    @if($orders_to_show->member_id == $member->id)
                                    {{$member->name}}
                                    @endif
                                    @endforeach</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr class="heading">
                    <td>အမျိုးအစား</td>

                    <td>စျေးနှုန်း</td>
                </tr>

                @foreach($bet_number as $orderdetail)
                <tr class="item">
                    <td>{{$orderdetail->number_type}}</td>
                    <td>{{$orderdetail->amount}} ကျပ်</td>
                </tr>
                @endforeach

                <tr class="item last">
                    <td>ရာခိုင်နှုန်း</td>

                    <td>
                        @foreach($members_to_show as $member)
                        @if($orders_to_show->member_id == $member->id)
                        {{$member->percentage}}
                        @endif
                        @endforeach
                    </td>
                </tr>
                <tr class="total">
                    <td></td>

                    <td>စုစုပေါင်း: {{$totalAmount}} ကျပ်</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<script>
    function myPrint() {
        var divToPrint = document.getElementById('to-print-real');

        var newWin = window.open('', 'Print-Window');

        newWin.document.open();

        newWin.document.write(
            `<html>
                <style>
      body {
        font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
        text-align: center;
        color: #777;
      }

      body h1 {
        font-weight: 300;
        margin-bottom: 0px;
        padding-bottom: 0px;
        color: #000;
      }

      body h3 {
        font-weight: 300;
        margin-top: 10px;
        margin-bottom: 20px;
        font-style: italic;
        color: #555;
      }

      body a {
        color: #06f;
      }

      .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 30px;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        font-size: 16px;
        line-height: 24px;
        font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
        color: #555;
      }

      .invoice-box table {
        width: 100%;
        line-height: inherit;
        text-align: left;
        border-collapse: collapse;
      }

      .invoice-box table td {
        padding: 5px;
        vertical-align: top;
      }

      .invoice-box table tr td:nth-child(2) {
        text-align: right;
      }

      .invoice-box table tr.top table td {
        padding-bottom: 20px;
      }

      .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
      }

      .invoice-box table tr.information table td {
        padding-bottom: 40px;
      }

      .invoice-box table tr.heading td {
        background: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
      }

      .invoice-box table tr.details td {
        padding-bottom: 20px;
      }

      .invoice-box table tr.item td {
        border-bottom: 1px solid #eee;
      }

      .invoice-box table tr.item.last td {
        border-bottom: none;
      }

      .invoice-box table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
      }

      @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td {
          width: 100%;
          display: block;
          text-align: center;
        }

        .invoice-box table tr.information table td {
          width: 100%;
          display: block;
          text-align: center;
        }
      }
    </style><body onload="window.print()">` + divToPrint.innerHTML + `</body></html>`
        );

        newWin.document.close();

        setTimeout(function() {
            newWin.close();
        }, 10);
    }

</script>
@endsection
