@if(Auth::user())
<section id="sectionMenu">
    <div class="row row-menu menuUser">
        <ul class="col-md-12 col-sm-12 col-xs-12">
            <li class="col-md-12 col-sm-12 col-xs-12"><img src="{{ asset('img/icons/user_avatar.png')}}" width="35%"></li>
            <li class="col-md-12 col-sm-12 col-xs-12">{{Auth::user()->name}}</li>
            <li class="col-md-12 col-sm-12 col-xs-12">
                <a href="https://{{Auth::user()->shopify_url}}" class="colorGO bold" target="_blank">Go to Shopify Store</a></li>
        </ul>
    </div>
    <div class="row row-menu menuOperative">
        <ul class="level0">
            <li><a href="{{ url('/') }}" class="colorGO bold" data-name="DASHBOARD">
                <span class="menu-icon">
                    <img src="{{ asset('img/icons/Dashboard.png')}}">
                </span>
            Dashboard</a>
            </li>
            @if(Auth::user()->shopify_url and Auth::user()->active == 1)
            <li class="colorGO bold liManageProduct">
                <span class="menu-icon">
                    <img src="{{ asset('img/icons/Manage.png')}}">
                </span>
                Manage Product
                <ul class="level1">
                    <li><a href="{{ url('/search-products') }}" class="colorGO bold"  data-name="SEARCH PRODUCTS">Search Products</a></li>
                    <li><a href="{{ url('/import-list') }}" class="colorGO bold"  data-name="IMPORT LIST">Import List</a></li>
                    @can('plan_view-my-products')
                        <li><a href="{{ url('/my-products') }}" class="colorGO bold"  data-name="MY PRODUCTS">My Products</a></li>
                    @else
                    <li class="colorGO bold" data-toggle="modal" data-target="#upgrade-plans-modal">My Products</li>
                    @endcan
                </ul>
            </li>
            @can('plan_view-manage-orders')
            <li><a href="{{ url('/orders') }}" class="colorGO bold" data-name="ORDERS">
                <span class="menu-icon">
                    <img src="{{ asset('img/icons/Order.png')}}">
                </span>
            Manage Order</a></li>
            @else
            <li class="colorGO bold" data-toggle="modal" data-target="#upgrade-plans-modal">
                <span class="menu-icon">
                    <img src="{{ asset('img/icons/Order.png')}}">
                </span>
            Manage Order</li>
            @endcan
            @endif
        </ul>
    </div>
    <div class="row row-menu menuHelp">
        <ul class="level0">
            <li><a href="{{ url('/settings') }}" class="colorGO bold"  data-name="SETTINGS">
                <span class="menu-icon">
                    <img src="{{ asset('img/icons/Settings.png')}}">
                </span>
            Setting</a></li>
            <li><a href="{{ url('/plans') }}" class="colorGO bold"  data-name="PLANS">
                <span class="menu-icon">
                    <img src="{{ asset('img/icons/Plan.png')}}">
                </span>
            Your Plan</a></li>
            <li><a href="http://greendropship.com/knowledge-base" class="colorGO bold"  data-name="HELP CENTER" target="_blank">
                <span class="menu-icon">
                    <img src="{{ asset('img/icons/Plan.png')}}">
                </span>
            Help</a></li>
        </ul>
    </div>
</section>
@endif