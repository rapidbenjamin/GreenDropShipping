
@extends('layouts.app')

@section('content')
<div class="indexContent" data-page_name="DASHBOARD">

        <div class="maincontent">



            <div class="wrapinsidecontent">


            @if(Auth::user()->plan == 'free')
            <div class="alertan">
               <div class="agrid">
                   <img src="img/infogray.png"
                     srcset="img/infogray@2x.png 2x,
                         img/infogray@3x.png 3x">
                    <p>You have a free plan. <a href="/plans">Click here to upgrade your plan.</a></p>
               </div>
            </div>
            @endif


                           <div class="threecols">
                                <a class="wbox">
                                   @if($user->shopify_url)
                                   <div class="greencheck">
                                       <img src="img/checkgreen.png">
                                   </div>
                                   @endif


                                    <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 472 472" viewBox="0 0 472 472" style="max-width: 118px;">
                                        <path d="M403.992 336h-64v104h64V336zm-16 88h-32v-72h32v72zM259.992 264h-192c-13.255 0-24 10.745-24 24v152h240V288c0-13.255-10.745-24-24-24zm8 160h-208V320h208v104zm0-120h-208v-16c0-4.418 3.582-8 8-8h192c4.418 0 8 3.582 8 8v16z" style="fill: #89b73d;" />
                                        <path d="M467.44 109.272c-.08-.224-.16-.44-.256-.656s-.112-.344-.2-.496l-38.992-70.192V8c0-4.418-3.582-8-8-8h-376c-4.418 0-8 3.582-8 8v30.304L4.68 108.752v.072c0 .088-.048.192-.088.28-.311.816-.484 1.679-.512 2.552 0 .12-.072.224-.072.344v360h463.984V112v-.144c-.036-.886-.223-1.76-.552-2.584zM446.392 104H353.76l-18.664-56h80.192l31.104 56zm-106.4 16v80c0 17.673-14.327 32-32 32h-32c-17.673 0-32-14.327-32-32v-80h96zm-96-16V48h74.232l18.664 56h-92.896zm-192-88h360v16h-360V16zm83.104 88l18.664-56h74.232v56h-92.896zm92.896 16v72c-.026 22.08-17.92 39.974-40 40h-16c-22.08-.026-39.974-17.92-40-40v-72h96zm-178.8-72h87.696l-18.664 56h-93.92l24.888-56zm-29.2 72h96v68.8c-.026 23.848-19.352 43.174-43.2 43.2h-12.8c-22.08-.026-39.974-17.92-40-40v-72zm400 336h-96V320h96v136zm0-152h-96v-9.6c0-7.953 6.447-14.4 14.4-14.4h67.2c7.953 0 14.4 6.447 14.4 14.4v9.6zm32 152h-16V294.4c0-16.789-13.611-30.4-30.4-30.4h-67.2c-16.789 0-30.4 13.611-30.4 30.4V456h-288V231.112c10.506 10.8 24.933 16.892 40 16.888h12.8c20.647-.022 39.787-10.811 50.496-28.464 9.921 17.569 28.527 28.443 48.704 28.464h16c18.5-.014 35.8-9.164 46.224-24.448 8.496 15.088 24.46 24.43 41.776 24.448h32c17.316-.018 33.28-9.36 41.776-24.448 10.424 15.284 27.724 24.434 46.224 24.448h16c15.067.004 29.494-6.088 40-16.888V456zm0-264c-.026 22.08-17.92 39.974-40 40h-16c-22.08-.026-39.974-17.92-40-40v-72h96v72z" style="fill: #89b73d;" />
                                    </svg>

                                    <h2>Connect your Shopify store</h2>
                                    <p>To start selling, you must connect your Shopify store to the app.</p>
                                    @if($user->shopify_url)
                                    <p class="clickhere">Connected to your store</p>
                                    @else
                                    <p class="clickhere">Connect your store</p>
                                    @endif
                                </a>
                                <div  class="wbox">

                                    <svg id="Capa_1" data-name="Capa 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 258.04 199.08" style="max-width: 154px;">
                                        <path d="M238.89,23.49a4,4,0,1,1,4-4A4,4,0,0,1,238.89,23.49Zm-11.09-4a4,4,0,1,0-4,4A4,4,0,0,0,227.8,19.46Zm-15.12,0a3.78,3.78,0,0,0-3.78-3.78h-.5a3.78,3.78,0,0,0-3.78,3.78,4,4,0,0,0,8.06,0ZM258,11.65v176.4a11.35,11.35,0,0,1-11.34,11.34H11.34A11.36,11.36,0,0,1,0,188.05V50a3.78,3.78,0,0,1,7.56,0V176.71H250.48V38.61h-7.56V165.37a3.78,3.78,0,0,1-7.56,0V38.61H3.78A3.78,3.78,0,0,1,0,34.83V11.65A11.36,11.36,0,0,1,11.34.31H193.28a3.78,3.78,0,1,1,0,7.56H11.34a3.79,3.79,0,0,0-3.78,3.78v19.4H250.48V11.65a3.78,3.78,0,0,0-3.78-3.78H208.4a3.78,3.78,0,0,1,0-7.56h38.3A11.35,11.35,0,0,1,258,11.65ZM186.8,85.52a61.78,61.78,0,1,1-20.19-27.18,3.78,3.78,0,0,1-4.61,6,53.68,53.68,0,0,0-33-11.19,54.1,54.1,0,1,0,51.83,38.33l-46.13,46.14a11.36,11.36,0,0,1-16,0L92.44,111.37a11.36,11.36,0,0,1,0-16h0a11.36,11.36,0,0,1,16,0l18.22,18.22L171.05,69.2a11.34,11.34,0,0,1,16,16Zm-89,15.16a3.78,3.78,0,0,0,0,5.35L124,132.26a3.78,3.78,0,0,0,5.35,0l52.38-52.37a3.78,3.78,0,0,0-5.35-5.35h0l-47,47a3.78,3.78,0,0,1-5.35,0l-20.89-20.89a3.78,3.78,0,0,0-5.34,0Zm152.69,87.37v-3.78H7.56v3.78a3.79,3.79,0,0,0,3.78,3.78H246.7A3.78,3.78,0,0,0,250.48,188.05Z" transform="translate(0 -0.31)"style="fill: #89b73d;"/>
                                    </svg>
                                    <h2>Upgrade your plan</h2>
                                    <p>Get a <a href="https://greendropship.com/membership-account/membership-checkout/" target="_blank">GreenDropShip membership</a> to upgrade your plan.</p>
                                    <p class="clickhere"><a href="/plans" >Current Plan</a></p>
                                </div>
                                <div  class="wbox">

                                    <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                             viewBox="0 0 269 335" style="max-width: 97px;">
                                            <path d="M259.5,117.2L194.5,92c11.8-33.4-5.8-70-39.2-81.8s-70,5.8-81.8,39.2c-4.8,13.8-4.8,28.8,0,42.6L8.5,117.2
                                                c-2.7,1-4.5,3.6-4.5,6.5v150.9c0,2.9,1.8,5.5,4.5,6.5l123,47.7c0.8,0.3,1.7,0.5,2.5,0.5c0.9,0,1.7-0.2,2.5-0.5l0,0l123-47.7
                                                c2.7-1,4.5-3.6,4.5-6.5V123.7C264,120.8,262.2,118.2,259.5,117.2L259.5,117.2z M237.6,123.7l-22.7,8.8L177.3,118
                                                c4.3-3.9,8-8.5,11.1-13.4L237.6,123.7z M163.5,127.7l32,12.3l-18.1,7l-33.1-12.9C151,133,157.5,130.8,163.5,127.7z M185.3,159
                                                l23.5-9.1V179l-3.9-2.6c-2.8-1.8-6.6-1.4-8.9,1.1l-10.7,11.3V159z M134,20.7c27.7,0,50.1,22.4,50.1,50.1S161.7,121,134,121
                                                S83.9,98.5,83.9,70.8l0,0C83.9,43.2,106.3,20.8,134,20.7z M83.6,110.3l-9.4-3.7l5.4-2.1C80.8,106.6,82.2,108.5,83.6,110.3
                                                L83.6,110.3z M54.9,114.2l103.2,40.3l-24.1,9.4L30.4,123.7L54.9,114.2z M18,269.8V133.9l109,42.3v135.9L18,269.8z M141,312.1V176.2
                                                l30.3-11.8v42.1c0,2.9,1.8,5.4,4.4,6.5c0.8,0.3,1.7,0.5,2.6,0.5c1.9,0,3.8-0.8,5.1-2.2l18.7-19.9l9.9,6.4c3.2,2.1,7.6,1.2,9.7-2.1
                                                c0.7-1.1,1.1-2.4,1.1-3.8v-47.5l27.2-10.5v135.9L141,312.1z" style="fill: #89b73d;"/>
                                            <path d="M121.5,91.4c2.7,2.3,6.6,2.2,9.2-0.1l33.9-30.5c3-2.5,3.3-6.9,0.9-9.9c-2.5-3-6.9-3.3-9.9-0.9c-0.1,0.1-0.2,0.2-0.4,0.3
                                                l-29.4,26.4l-13.4-11.3c-3-2.5-7.4-2.1-9.9,0.8s-2.1,7.4,0.8,9.9L121.5,91.4z" style="fill: #89b73d;"/>
                                      </svg>

                                    <h2>Add products to your store</h2>
                                    <p>Find products you want to sell and add them to your store.</p>
                                    <p class="clickhere"><a href="/search-products">Search products</a></p>
                                </div>
                            </div>

                            <div class="contentbox">
                                <h3>Are you ready?</h3>

                                    @if (session('status'))
                                        <div class="alert alert-success" role="alert">
                                            {{ session('status') }}
                                        </div>
                                    @endif

                                <div class="content">
                                    <p class="text-center">Here are some important actions you should take to use the app:</p>

                                    <ul class="wcheck">
                                        <li>
                                            <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                            <span>
                                                <strong>
                                                    1. Get a GreenDropShip membership
                                                </strong>
                                                <p>
                                                    An annual <a href=" https://greendropship.com/membership-account/membership-checkout/" target="_blank">dropship membership</a> is required to upgrade your plan and start selling.
                                                </p>
                                            </span>
                                        </li>

                                        <li>
                                            <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                            <span>
                                                <strong>
                                                    2. Upgrade your plan
                                                </strong>
                                                <p>
                                                    Use your invoice number to connect the app to your GreenDropShip account.
                                                </p>
                                            </span>
                                        </li>

                                        <li>
                                            <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                            <span>
                                                <strong>
                                                    3. Create an Import List
                                                </strong>
                                                <p>
                                                    Browse our <a href="/search-products">catalog of products</a> and add the item you want to sell to an Import List.
                                                </p>
                                            </span>
                                        </li>

                                        <li>
                                            <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                            <span>
                                                <strong>
                                                    4. Add products to Shopify
                                                </strong>
                                                <p>
                                                    Edit product details such as descriptions and price before adding products to your store.
                                                </p>
                                            </span>
                                        </li>

                                        <li>
                                            <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                            <span>
                                                <strong>
                                                    5. Make a sale
                                                </strong>
                                                <p>
                                                    When you make a sale, go to <a @can('plan_view-manage-orders') href="/orders" @endcan>Manage Orders</a> in the GreenDropShip app.

                                                </p>
                                            </span>
                                        </li>

                                        <li>
                                            <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                            <span>
                                                <strong>
                                                    6. Get your order fulfilled automatically
                                                </strong>
                                                <p>
                                                    Pay for the order (wholesale price + shipping) to submit it to GreenDropShip.
                                                    We will fulfill your order and ship it to your customer.
                                                </p>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>


                            <div class="contentbox">
                                <h3>Help Center</h3>
                                <div class="content">
                                    <div class="twocols helpcenter">
                                        <div class="youtubeframe">
                                            <div class='embed-container'>
                                            <iframe src="https://player.vimeo.com/video/507146771" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

            </div>

        </div>
    </div>

</div>
@endsection
