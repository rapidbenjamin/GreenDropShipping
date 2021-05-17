@extends('layouts.app')

@section('content')
<div class="indexContent orderDetailContent" data-page_name="ORDER DETAIL">

        <div class="maincontent">


            <p class="back" data-url='{{ url()->previous() }}'><a href="{{ url()->previous() }}">< BACK</a> </p> 

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

            @if($order->fulfillment_status == 12)
            <div class="alertan level2">
               <div class="agrid">
                    <p><strong> Orders are submitted to GreenDropShip for processing once the grand total for each transaction is paid, which includes the wholesale price + shipping.</strong></p>
               </div>                
            </div>            
            @endif            	

              <div class="screen-order-detail">

               <div class="ordertable">
                   <div class="headerorder">
                       <div class="norder">
                           Shopify Order
                       </div>
                       <div>
                           {{$order->order_number_shopify}}
                       </div>

                       @if($order->magento_order_id)
                       <div class="date">
                           GDS Order
                       </div>
                       <div>
                           #{{$order->magento_order_id}}
                       </div>
                       @endif

                       <div class="date">
                           Date
                       </div>
                       <div>
                           {{$order->created_at}}
                       </div>
                   </div>
                   <div class="otawrap">
                       <div class="twocols2">
                           <div>
                               <div class="box">
                                   <h3>Customer's Information</h3>
                                   <div class="cwrap">
                                       <p><strong>Name</strong> <br>
                                       {{$osa->first_name}} {{$osa->last_name}}
                                       </p>

                                       <p><strong>Email</strong> <br>
                                       {{$osa->email}}
                                       </p>

                                      
                                       <p><strong>Shipping Information</strong> <br>
					                        @if($order->shipping_title)
					                            <div class="sod-row"><label>Shipping</label>
					                                <value>{{$order->shipping_title}}</value>
					                            </div>
					                            @if($order->tracking_code)
					                            <div class="sod-row"><label>Tracking Number</label>
					                                <value>{{$order->tracking_code}}</value>
					                            </div>
					                            @endif
					                        @endif
                                       </p>
                                       
                                 <div class="box addres">
	                            <form action="{{url('/save-address')}}" method="post">
	                                @csrf
                                   <h3>Customer Address</h3>
                                   <div class="cwrap">
                                       <div class="formg">
                                           <label for="">
                                               Address1
                                           </label>
                                           <input type="text" name="address1" value="{{$osa->address1}}">
                                           
                                           <label for="">
                                               Address2
                                           </label>
                                           <input type="text" name="address2" value="{{$osa->address2}}">
                                           
                                           <label for="">
                                               Zip Code
                                           </label>
                                           <input type="text" name="zip" value="{{$osa->zip}}">
                                           
                                           <label for="">
                                               City
                                           </label>
                                           <input name="city" type="text" value="{{$osa->city}}">
                                           
                                           <label for="">
                                               State
                                           </label>
		                                    <select name="state">
		                                        @foreach ($states as $key=>$value)

		                                        @if($key==$state_key)
		                                        <option value="{{$key}}" selected>{{$value}}</option>
		                                        @else
		                                        <option value="{{$key}}">{{$value}}</option>

		                                        @endif
		                                        @endforeach
		                                    </select>
                                       </div>
                                       <p><strong>Country:</strong> <br> {{$osa->country}}</p>

	                                    @if($order->fulfillment_status== 4)
	                                    <button class="" id="save-address">Save</button>
	                                    @endif                                       
                                     
	                                     <!-- If address was updated -->
	                                    @if($osa->update_merchant_id > 0)                                      
                                       	<p class="updated"><strong>Updated 2 </strong> <br>{{$osa->update_date}}.</p>
                                       	@endif
                                   </div>                                     


                                </div>

				                        @if($order->financial_status == 2)
				                        <div class="sod-body-side-content">
				                            <p class="sod-title"><strong>Stripe Payment Detail</strong></p>
				                            <p class="sod-row"><strong>Date</strong> <br>
				                                {{$order->created_at}}
				                            </p>
				                            <p class="sod-row"><strong>Id Transaction</strong><br>
				                                {{$payment_intent}}
				                            </p>
				                            <p class="sod-row"><strong>Card Number</strong><br>
				                                xxxx xxxx xxxx {{$payment_card_number}}
				                            </p>
				                        </div>
				                        @endif

                               </div>


                                   <input name="order_id" type="hidden" value="{{$order->id}}">


                               </div>


                           </div>
                           <div class="rightSide">


                               <div class="states">
                                   <div>
                                       <p>Order Status</p>
                                       <div class="paid" style="background-color: transparent;">
                                           <span >{{$fs->name}}</span>
                                       </div>
                                   </div>
                                   <div>
                                       <p>Payment Status</p>
                                       <div class="inprocess" style="background-color: transparent;">
                                           <span >{{$os->name}}</span>
                                       </div>
                                   </div>

                                @if($order->fulfillment_status == 9)
                                <div class="sod-row colorRED" style="text-align: center;">
                                    <span>Cancelled by {{$user_canceled}} at {{$order->canceled_at}}</span>
                                </div>
                                @endif
                                   
                              </div>


                               <div class="box product">
                                   <h3>Product Detail</h3>
                                   <table class="greentable" cellspacing="0">
                                       <thead>
                                           <tr>
                                               <th>
                                                   IMAGE
                                               </th>
                                               <th>
                                                   PRODUCT NAME
                                               </th>
                                               <th>
                                                   PRICE
                                               </th>
                                               <th>
                                                   QUANTITY
                                               </th>
                                               <th>
                                                   SKU
                                               </th>
                                           </tr>
                                       </thead>
                                       <tbody>

                                         	@foreach($order_products as $op)
                                           <tr>
                                               <td>
                                                    <div class="productphoto">
                                                       <img src="{{$op->image_url}}" >
                                                    </div>
                                               </td>
                                               <td data-label="PRODUCT NAME">
                                                   {{$op->name}}
                                               </td>
                                               <td data-label="PRICE">
                                                   <strong>
                                                       ${{$op->price}}
                                                   </strong>
                                               </td>
                                               <td data-label="QUANTITY">
                                                   {{$op->quantity}}
                                               </td>
                                               <td class="sku" data-label="SKU">{{$op->sku}}</td>
                                           </tr>
                                           @endforeach
                                   </tbody>
                                   </table>
                                   <div class="cwrap">
                                       <table class="greentable resume" cellspacing="0">
                                           <tbody>
                                              <tr >
                                                  <td colspan="2" class="header">
                                                      ORDER RESUME
                                                  </td>
                                              </tr>
                                               <tr>
                                                   <td><strong>TOTAL PRODUCTS</strong></td>
                                                   <td>${{number_format($order->total, 2)}}</td>
                                               </tr>
                                               <tr>
                                                   <td><strong>TOTAL SHIPPING</strong></td>
                                                   <td>${{number_format($order->shipping_price, 2)}}</td>
                                               </tr>
                                               <tr>
                                                   <td><strong>SHIPPING METHOD</strong></td>
                                                   <td>{{($order->shipping_title == null?'NA':$order->shipping_title)}}</td>
                                               </tr>
                                               <tr>
                                                   <td><strong>TOTAL ORDER</strong></td>
                                                   <td>${{number_format($order->total + $order->shipping_price, 2)}}</td>
                                               </tr>
                                           </tbody>
                                       </table>
                                   </div>
                               </div>
                               <div class="productbtn">
                                 @if($order->financial_status== App\Libraries\OrderStatus::Outstanding && ($order->fulfillment_status != 9 && $order->fulfillment_status != 12))
                                <!--button class="payments" id="checkout-button" data-id="{{$order->id}}">Pay Order</button-->
                                @endif
                                @if($order->fulfillment_status== 4)
                                <button class="cancel" id="cancel-button" data-id="{{$order->id}}">Cancel Order</button>
                                @endif
                                @if($order->financial_status== 2 && $order->fulfillment_status== 5)
                                <button class="cancel" id="cancel-req-button" data-id="{{$order->id}}">Cancel Request</button>
                                @endif                               
                               </div>
                           </div>

                                @if($order->fulfillment_status == 12)
                                <br>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    You have completed all 100 orders for the current month.
                                    If you want to process an order, you must do it manually
                                    at the following link:
                                    <a href="https://members.greendropship.com/customer/account/login/" target="_blank"> https://members.greendropship.com/customer/account/login/</a>
                                </div>
                                @endif


                       </div>
                   
                   </div>
               </div>
               

                
            </div>
            
        </div>
    </div>  
</div>

<script type="text/javascript">
    $(document).ready(function() {

        

        var checkoutButton = document.getElementById('checkout-button');
        checkoutButton.addEventListener('click', function() {

            //let res = $('input[name=s_method]:checked', '#shipping-methods').val()
           // if (res == undefined) {
             //   alert('you must select a valid option');
              //  return;
           // }
            //alert('{{$order->id}}');
            //$('input[name=s_method]:checked', '#shipping-methods').val()
            //alert($('input[name=s_method]:checked', '#shipping-methods').val());
            //return;
            var stripe = Stripe('{{env("STRIPE_API_KEY")}}');
            let orders = [$(this).attr('data-id')];
            // Create a new Checkout Session using the server-side endpoint you
            // created in step 3.

            console.log('ordenes... ' + orders);

            fetch('/create-checkout-session', {
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json, text-plain, */*",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    },
                    method: 'POST',
                    body: JSON.stringify({
                        orders: orders
                        //shipping: $('input[name=s_method]:checked', '#shipping-methods').val(),
                    }),
                })
                .then(function(response) {
                    if (response.status == 406) {
                        $('#order-limit-modal').modal('show')
                    }
                    return response.json();
                })
                .then(function(session) {
                    return stripe.redirectToCheckout({
                        sessionId: session.id
                    }).then(function(result) {
                        console.log('res', result);
                    });
                })
                .then(function(result) {
                    // If `redirectToCheckout` fails due to a browser or network
                    // error, you should display the localized error message to your
                    // customer using `error.message`.
                    if (result.error) {
                        alert(result.error.message);
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                });
        });

        $("#cancel-button").click(function() {
            if (confirm('Do you really want to cancel the order?')) {
                window.location.href = "{{url('orders/cancel/')}}/" + $(this).attr('data-id');
            }

        });

        $("#cancel-req-button").click(function() {
            if (confirm('Do you really want to cancel the order?')) {
                window.location.href = "{{url('orders/cancel-request/')}}/" + $(this).attr('data-id');
            }

        });
    });
</script>


@endsection