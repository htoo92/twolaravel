{{-- Up to Date --}}
<nav id="sidebar">
    <div class="sticky-top">
        <div class="sidebar-header">
            {{-- <h5 style="margin: 0"> <i class="fas fa-user"></i> {{ Auth::user()->name }}</h5> --}}
            <img src="{{ asset('images/img/666666.png')}}" alt="" class="img-fluid">
        </div>
        <ul class="list-unstyled components">

            @if(auth()->user()->can('user-list'))
            <li class="active">
                <a href="#usersSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">အသုံးပြုသူများ</a>
                <ul class="collapse list-unstyled" id="usersSubmenu">
                    <li>
                        <a href="/users">အားလုံး ကြည့်ရန်</a>
                    </li>
                    @if(auth()->user()->can('user-create'))
                    <li>
                        <a href="/users/create">အသစ်ထည့်ရန်</a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif
            {{-- @if(auth()->user()->can('role-list') && auth()->user()->can('role-create') && auth()->user()->can('role-edit') && auth()->user()->can('role-delete'))
                @role('Admin') --}}
            @if(auth()->user()->can('role-list'))

            <li>
                <a href="#rolesSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">ရာထူများ</a>
                <ul class="collapse list-unstyled" id="rolesSubmenu">
                    <li>
                        <a href="/roles">အားလုံး ကြည့်ရန်</a>
                    </li>
                    @if(auth()->user()->can('role-create'))
                    <li>
                        <a href="/roles/create">အသစ်ထည့်ရန်</a>
                    </li>
                    @endif
                </ul>
            </li>
            {{-- @endrole --}}
            @endif


            @if(auth()->user()->can('groups-list'))
            <li>
                <a href="#groupsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">အုပ်စုများ</a>
                <ul class="collapse list-unstyled" id="groupsSubmenu">
                    <li>
                        <a href="/groups">အားလုံး ကြည့်ရန်</a>
                    </li>
                    @if(auth()->user()->can('groups-create'))
                    <li>
                        <a href="/groups/create">အသစ်ထည့်ရန်</a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            <li>
                <a href="/changelimit">လစ်မစ် ပြောင်းရန်</a>
            </li>
            <li>
                <a href="#betsSubmeu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">ထိုးကြေးများ</a>
                <ul class="collapse list-unstyled" id="betsSubmeu">
                    <li>
                        <a href="/bets">နေ့စဉ်မှတ်တမ်း</a>
                    </li>
                    <li>
                        <a href="/bets/create">အသစ်ထည့်ရန်</a>
                    </li>
                </ul>
            </li>
            @if(auth()->user()->can('ownerdetails-list'))
            <li>
                <a href="/ownerdetails/{{auth()->user()->id}}">ပိုင်ရှင် မှတ်တမ်း</a>
            </li>
            @endif
            @if(auth()->user()->can('lucky-number-list'))
            <li>
                <a href="/luckynumber">ပေါက်ဂဏန်း</a>
            </li>
            @endif
            @if(auth()->user()->can('clear-all'))
            <li>
                <a href="/clearall">မှတ်တမ်း ဖျက်ရန်</a>
            </li>
            @endif

            {{-- <li>
                <a href="#customersSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Customers</a>
                <ul class="collapse list-unstyled" id="customersSubmenu">
                    <li>
                        <a href="/customers">All Customers</a>
                    </li>
                    <li>
                        <a href="/customers/create">Create New Customers</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="/revenue">Profit & Loss</a>
            </li> --}}
        </ul>

        <ul class="list-unstyled CTAs">
            <li>

                <a href="/users/show/{{ Auth::user()->id }}" class="download">{{auth()->user()->name}}</a>
            </li>
            <li>
                <a class="article" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                    {{ __('ထွက်မည်') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</nav>
