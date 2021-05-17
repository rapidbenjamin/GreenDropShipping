<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="https://cdn.shortpixel.ai/spai/q_lossy+ret_img/https://greendropship.com/wp-content/uploads/2017/06/Allen-favicon1-1-e1497849113735.png" data-spai-eager="1" sizes="32x32">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Green Drop Ship</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" ></script>
    <script src="{{ asset('js/custom.js') }}" ></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Styles -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <!--link href="{{ asset('css/custom.css') }}" rel="stylesheet"-->

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://app.greendropship.com/js/slideout.min.js"></script>
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css">
    <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
    <script src="{{ asset('js/functions.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/tipped.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/tipped.css') }}">

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Q9NW56N7ZW"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-Q9NW56N7ZW');
    </script>


</head>
<body class=" @can('view-admin-menu') bodyAdmin @else bodyFront @endcan">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm" style="display: none">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <!-- {{ config('app.name', 'Laravel') }} -->
                    <img src="{{ asset('img/logoGDS.png') }}">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                       
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

    <!--left menu2 -->
    @if(Auth::user())
    <div class="leftmenu2" id="menu">
           
           <div class="topel">
              <div class="currenuser">
                    <div class="avatar">
                        <img src="img/user_avatar.png">
                    </div>               
                    <div>
                        <h3>{{Auth::user()->name}}</h3>
                        <p><a href="https://{{Auth::user()->shopify_url}}" target="_blank">Go to Shopify Store</a></p>
                        <p>                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a></p>
                    </div>
                </div>

                <ul class="mainmenu">
                    <li class="active">
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
                            <p>Manage Product</p>
                        </a>
                        
                        <ul>
                            <li>
                                <a href="{{ url('/search-products') }}">
                                    Search Products
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/import-list') }}">
                                    Import List
                                </a>
                            </li>
                            @can('plan_view-my-products')
                            <li>
                                <a href="{{ url('/my-products') }}">
                                    My Products
                                </a>
                            </li>
                            @else
                            <li data-toggle="modal" data-target="#upgrade-plans-modal">
                                <a>
                                    My Products
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @can('plan_view-manage-orders')
                    <li>
                        <a href="{{ url('/orders') }}">
                            <img src="{{ asset('img/manageorder.png') }}" srcset="img/manageorder@2x.png 2x,
                                     img/manageorder@3x.png 3x">
                            <p>Manage Order</p>
                        </a>

                    </li>
                    @else
                    <li data-toggle="modal" data-target="#upgrade-plans-modal">
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
                <li>
                    <a href="{{ url('/settings') }}">
                        <img src="{{ asset('img/settings.png') }}" srcset="img/settings@2x.png 2x,
                                 img/settings@3x.png 3x">
                        <p>Settings</p>
                        
                    </a>
                </li>
                <li>
                    <a href="{{ url('/plans') }}">
                        <img src="{{ asset('img/info.png') }}" srcset="img/info@2x.png 2x,
                                 img/info@3x.png 3x">
                        <p>Your Plan</p>
                        
                    </a>
                </li>
                <li>
                    <a href="http://greendropship.com/knowledge-base" target="_blank">
                        <img src="{{ asset('img/info.png') }}" srcset="img/info@2x.png 2x,
                                 img/info@3x.png 3x">
                        <p>Help</p>
                        
                    </a>
                </li>
            </ul>
    </div>
    @endif
    <!--/left menu2 -->




        <div id="panel">

        <header class="theheader">
            <div class="logo">
               <div class="hamburguer toggle-button">
                   <div></div>
                   <div></div>
                   <div></div>
                   <span>
                       <i class="fa fa-times" aria-hidden="true"></i>
                   </span>
               </div>
                  <a href="/"> 
                    <img class="mainlogo" src="{{ asset('img/logo.png') }}">
                  </a>
            </div>
            <div class="titlebox">
                <h1 id="pageName"></h1>
            </div>
        </header> 


          <div class="wrapcontent">
             <div class="leftmenu">
               <div class="leftmenu">
                   @can('view-admin-menu')
                       @include('menu.admin-menu')
                   @else
                      @include('menu.menu')
                   @endcan               
               </div>
             </div>
             <div class="maincontent">@yield('content')</div>
          </div>
        </div>
    </div>
</body>
<!-- Modal -->
<div id="upgrade-plans-modal" class="modal fade" role="dialog" data-backdrop="true">
  <div class="modal-dialog modal-dialog-centered">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header" style="display:block">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Plans</h4>
      </div>
      <div class="modal-body">
        <p>Upgrade your plan to perform this action. <a href="{{url('/plans')}}">View plans</a></p>
      </div>
    </div>

  </div>
</div>

<div id="order-limit-modal" class="modal fade" role="dialog" data-backdrop="true">
  <div class="modal-dialog modal-dialog-centered">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header" style="display:block">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Plans</h4>
      </div>
      <div class="modal-body">
          <p>Order limit is reached.</p>
        <p>Upgrade your plan. <a href="{{url('/plans')}}">View plans</a></p>
      </div>
    </div>

  </div>
</div>

<div id="order-shipping" class="modal fade" role="dialog" data-backdrop="true">
  <div class="modal-dialog modal-dialog-centered">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header" style="display:block">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Select a shipping method</h4>
      </div>
      <div class="modal-body">
          <ul id="shipping-methods">
            
          </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default bgVC" style="color:white" id="select-shipping-method" data-dismiss="modal">Pay</button>
      </div>
    </div>

  </div>
</div>
</html>
