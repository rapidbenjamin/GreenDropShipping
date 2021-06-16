@if(Auth::user())
        <div class="currenuser">
                    <div class="avatar">
                        <img src="{{ asset('img/user_avatar.png') }}">
                    </div>
                    <div>
                        <h3>{{Auth::user()->name}}</h3>

                    </div>
        </div>

        <ul class="mainmenu mt-5 pt-5">
            <li>
                <a href="{{ url('/admin/dashboard') }}" class="colorBL bold" data-name="DASHBOARD">
                    <img src="{{ asset('img/admin_03.png') }}">
                                     Dashboard
                </a>
            </li>
            <li><a href="{{ url('/admin/orders') }}" class="colorBL bold" data-name="MANAGE ORDERS">
                <img src="{{ asset('img/Admin_09.png') }}">
                    Orders
                </a>
            </li>
            <li>
                <a href="{{ url('/admin/merchants') }}" class="colorBL bold" data-name="MANAGE MERCHANTS">
                    <img src="{{ asset('img/admin_07.png') }}" >
                    Merchants
                </a>
            </li>
            <li>
                <a href="{{ url('/admin/users') }}" class="colorBL bold" data-name="USERS">
                    <img src="{{ asset('img/Admin_11.png') }}">
                    Users
                </a>
            </li>
            <li>
                <a href="{{ url('/admin/logs') }}" class="colorBL bold" data-name="LOGS">
                    <img src="">
                    Logs
                </a>
            </li>
            <li>
                <a class="colorBL bold" data-name="LOGS" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <img src="">
                    {{ __('Logout') }}
                </a>
            </li>

        </ul>

@endif
