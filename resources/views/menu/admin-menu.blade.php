@if(Auth::user())
    <div class="mt-5 pt-5">
        <div class="currenuser mt-3">
            <div class="avatar">
                <img src="{{ asset('img/user_avatar.png') }}">
            </div>
            <div>
                <h3>{{Auth::user()->name}}</h3>
            <p><a class="dropdown-item text-light" href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                {{ __('Logout') }}
            </a></p>
            </div>
        </div>

        <ul class="mainmenu">
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
                <a class="colorBL bold account" data-name="ACCOUNT">
                    <img src="{{ asset('img/Admin_12.png') }}">
                    Account
                </a>
                <ul style="display: none;" class="items">
                    <li><a href="{{ url('/admin/profile') }}">Profile</a></li>
                    <li><a href="{{ url('/admin/password') }}">Change password</a></li>
                </ul>
            </li>
        </ul>
    </div>
@endif
