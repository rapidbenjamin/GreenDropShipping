@if(Auth::user())

<div class="leftmenu fixedpos">



           <div class="topel row-menu">

              <div class="currenuser">

                    <div class="avatar">

                        <img src="{{ asset('img/user_avatar.png') }}">

                    </div>

                    <div>

                        <h3>{{Auth::user()->name}}</h3>

                        <p> <a href="https://{{Auth::user()->shopify_url}}" target="_blank">Go to Shopify Store</a></p>
                        <p>                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a></p>
                    </div>

                </div>



                <ul class="mainmenu">

                    <li data-name="DASHBOARD">

                        <a href="{{ url('/') }}">

                            <img src="{{ asset('img/dashboard.png') }}" srcset="img/dashboard@2x.png 2x,

                                 img/dashboard@3x.png 3x">

                            <p>Dashboard</p>

                        </a>

                    </li>



                    @if(Auth::user()->shopify_url and Auth::user()->active == 1)

                    <li>

                        <a href="#">

                            <img src="{{ asset('img/mproduct.png') }}" srcset="img/mproduct@2x.png 2x,

                                         img/mproduct@3x.png 3x">

                            <p>Manage Products</p>

                        </a>



                        <ul>

                            <li data-name="SEARCH PRODUCTS">

                                <a href="{{ url('/search-products') }}">

                                    Search Products

                                </a>

                            </li>

                            <li data-name="IMPORT LIST">

                                <a href="{{ url('/import-list') }}">

                                    Import List

                                </a>

                            </li>

                            @can('plan_view-my-products')

                            <li data-name="MY PRODUCTS">

                                <a href="{{ url('/my-products') }}">

                                    My Products

                                </a>

                            </li>

                            @else

                            <li data-toggle="modal" data-target="#upgrade-plans-modal" data-name="MY PRODUCTS">

                                <a>

                                    My Products

                                </a>

                            </li>

                            @endcan

                            @if(Auth::User()->migration == 0)

                            <li data-name="MIGRATION">

                                <a href="{{ url('/migrate-products') }}">

                                    Migration

                                </a>

                            </li>

                            @endif

                        </ul>

                    </li>

                    @can('plan_view-manage-orders')

                    <li data-name="ORDERS">

                        <a href="{{ url('/orders') }}">

                            <img src="{{ asset('img/manageorder.png') }}" srcset="img/manageorder@2x.png 2x,

                                     img/manageorder@3x.png 3x">

                            <p>Manage Order</p>

                        </a>



                    </li>

                    @else

                    <li data-toggle="modal" data-target="#upgrade-plans-modal" data-name="ORDERS">

                        <a>

                            <img src="{{ asset('img/manageorder.png') }}" srcset="img/manageorder@2x.png 2x,

                                     img/manageorder@3x.png 3x">

                            <p>Manage Order</p>

                        </a>

                    </li>

                    @endcan

                    @endif

                </ul>

           </div>







            <ul class="mainmenu footermenu">

                <li data-name="SETTINGS">

                    <a href="{{ url('/settings') }}">

                        <img src="{{ asset('img/settings.png') }}" srcset="img/settings@2x.png 2x,

                                 img/settings@3x.png 3x">

                        <p>Settings</p>



                    </a>

                </li>

                <li data-name="PLANS">

                    <a href="{{ url('/plans') }}">

                        <img src="{{ asset('img/info.png') }}" srcset="img/info@2x.png 2x,

                                 img/info@3x.png 3x">

                        <p>Your Plan</p>



                    </a>

                </li>

                <li data-name="HELP CENTER">

                    <a href="https://help.greendropship.com/hc/en-us/categories/1260800665930-GreenDropShip-App-for-Shopify" target="_blank">

                        <img src="{{ asset('img/info.png') }}" srcset="img/info@2x.png 2x,

                                 img/info@3x.png 3x">

                        <p>Help</p>



                    </a>

                </li>

            </ul>

        </div>

@endif
