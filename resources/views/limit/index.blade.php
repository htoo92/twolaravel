@extends('layouts.main')
@section('content')
<div class="container">

    <div class="first-section-box p-3 mb-3 border rounded">
        <div class="row">
            <div class="col-md-6 page-title d-flex align-items-center">
                <h3>လစ်မစ် ပြောင်းရန်</h3>
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



    <div class="row">
        <div class="col-md-5 mb-3">
            @foreach($changelimit as $change_limit)
            <form action="/changelimit/update/{{$change_limit->id}}" method="post" class="form-middle-unused">
                @csrf
                <div class="mb-3">
                    <label for="changelimitdate" class="form-label">ရက်စွဲ</label>
                    <input type="text" class="form-control" id="changelimitdate" name="changelimitdate" value="{{date('d-F-Y')}}" disabled>
                </div>

                <div class="mb-3">
                    <label for="chagelimittime" class="form-label">အချိန်</label>
                    <input type="text" class="form-control" id="chagelimittime" name="chagelimittime" value="{{date('A')}}" disabled>
                </div>

                <div class="mb-3">
                    <label for="changelimit" class="form-label">လစ်မစ်</label>
                    <input type="number" class="form-control @error('changelimit') is-invalid @enderror" id="changelimit" name="changelimit" min="0" value="{{$change_limit->limit_amount}}">
                    @error('changelimit')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary mb-3">သိမ်းဆည်းမည်</button>

            </form>
            @endforeach

            @role('Admin')
            @foreach($changelimit as $change_limit)
            <form action="/changelimit/offbetbutton" method="post" class="form-middle-unused">
                @csrf
                <div class="mb-3">
                    <label for="changelimitdate" class="form-label">ထိုးကြေး</label>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="offBetButton" id="offBetButton" class="form-check-input" onchange="offBet()" {{$change_limit->is_offButton == "1" ? 'checked' : ''}}>
                        <input type="hidden" name="offBetValue" id="offBetValue" value="1">
                        <label for="offBetButton" class="form-check-label">ဖွင့်မည်/ပိတ်မည်</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mb-3">သိမ်းဆည်းမည်</button>
            </form>
            @endforeach
            @endrole

        </div>

        <div class="col-md-7">
            <div class="row">
                <div class="col-md-12">
                    <h5>စာရင်းကွက် ပြင်မည်</h5>
                </div>
                <form action="/changelimit/updateall/{{$change_limit->id}}" method="post" class="form-middle-unused">
                    @csrf
                    <div class="d-flex justify-content-end justify-content-xs-start
                    ">
                        <input type="number" class="form-control @error('updateEachLimit') is-invalid @enderror" id="updateEachLimit" name="updateEachLimit" min="0" style="max-width: 240px; margin-right:10px;">
                        @error('updateEachLimit')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <button id="limit_update" class="btn btn-primary mb-2">အကုန်လုံး ပြင်မည်</button>
                    </div>
                </form>
            </div>
            <form action="/changenumberlimit/update/{{$change_limit->id}}" method="post" class="form-middle-unused">
                @csrf
                <div class="row">

                </div>
                <div class="overscroll">
                    <table class="table table-hover table-bordered">
                        <thead class="bg-white sticky-top">
                            <tr>
                                <th scope="col">00 to 99</th>
                                <th scope="col">ထိုးကြေး</th>
                                <th scope="col"><button id="limit_update" class="btn btn-primary btn-sm" onclick="ss()">တစ်ခုချင်း ပြင်မည်</button></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($numberlimit as $row)
                            <tr class="numberlimitrows">
                                <th scope="row">

                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="numlimit[]" class="form-check-input" id="numlimit{{ $loop->index }}" {{$row->is_off == "1" ? 'checked' : ''}} onchange="getValue()">
                                        <input type="hidden" name="numlimittext{{ $loop->index }}" id="numlimittext{{ $loop->index }}" value="0">
                                        <label class="form-check-label" for="{{$row->numbers}}">{{$row->numbers}}</label>
                                    </div>
                                </th>
                                <td colspan="2">
                                    <input type="number" name="editlimitnumber{{ $loop->index }}" min="0" value="{{$row->amount}}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

    </div>
</div>


<script>
    function offBet() {
        if (document.getElementById("offBetButton").checked) {
            document.getElementById("offBetValue").setAttribute("value", "1");
        } else {
            document.getElementById("offBetValue").setAttribute("value", "0");
        }
    }

    function getValue() {
        for (var i = 0; i < 100; i++) {

            if (document.getElementById(`numlimit` + i).checked) {
                console.log("Checked");
                document.getElementById(`numlimittext` + i).setAttribute("value", "1");

            } else {
                console.log("Not Checked");
                document.getElementById(`numlimittext` + i).setAttribute("value", "0");
            }

        }
    }

    function ss() {
        // window.location = 'http://127.0.0.1:8000/event';
        // window.location = 'https://6666662d.neptune.link/event';
        window.location = 'https://6666662d.com/event';
    }

</script>


@endsection
