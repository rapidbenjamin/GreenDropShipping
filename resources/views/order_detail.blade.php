@extends('layouts.app')

@section('content')
<div class="indexContent orderDetailContent" data-page_name="ORDER DETAIL">
    <div class="maincontent">
        <div class="wrapinsidecontent">
            <a href="{{ url()->previous() }}">
                <button class="btn btn-lg mx-3 my-2 back">
                    < BACK
                </button>
            </a>
            @if(Auth::user()->plan == 'free')
            <div class="alertan">
                <div class="agrid">
                    <img src="/img/infogray.png" srcset="/img/infogray@2x.png 2x,/img/infogray@3x.png 3x">
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
                    <div class="otawrap">
                        <div class="twocols2">
                            <div class="box">
                                <div class="cwrap">
                                    <h3>Order Information</h3>
                                    <div class="formg">
                                        <p class="font-weight-bold">Shopify Order Number</p>
                                        <p>{{substr($order->order_number_shopify, 1)}}</p>
                                        @if($order->magento_order_id)
                                        <p class="font-weight-bold">GDS Order</p>
                                        <p>#{{$order->magento_order_id}}</p>
                                        @endif
                                        <p class="font-weight-bold">Date</p>
                                        <p>{{$order->created_at}}</p>
                                        <p class="font-weight-bold">Order Status</p>
                                        <p class="paid" style="background-color: transparent;">{{$fs->name}}</p>
                                        <p class="font-weight-bold">Payment Status</p>
                                        <p class="inprocess" style="background-color: transparent;">{{$os->name}}</p>
                                        @if($order->fulfillment_status == 9)
                                        <p class="font-weight-bold">Canceld By</p>
                                        <p class="inprocess" style="background-color: transparent;">{{$user_canceled}}</p>
                                        <p class="font-weight-bold">Canceled At</p>
                                        <p class="inprocess" style="background-color: transparent;">{{$order->canceled_at}}</p>
                                        @endif
                                        @if($order->shipping_title)
                                        <p class="font-weight-bold">Shipping Method</p>
                                        <p class="inprocess" style="background-color: transparent;">{{$order->shipping_title == null ? 'N/A' : $order->shipping_title}}</p>
                                        @endif
                                        @if($order->tracking_code)
                                        <p class="font-weight-bold">Tracking Number</p>
                                        <p>{{$order->tracking_code}}</p>
                                        @endif
                                        <p class="font-weight-bold btn-link">
                                            <a href="https://greendropship.com/shipping-rates/">
                                                Shipping Information
                                            </a>
                                        </p>
                                    </div>
                                    <h3>Customer Information</h3>
                                    <div class="formg">
                                        <p class="font-weight-bold">Name</p>
                                        <p>{{$osa->first_name}} {{$osa->last_name}}</p>
                                        <p class="font-weight-bold">Email</p>
                                        <p>{{$osa->email}}</p>
                                    </div>
                                    <div class="addres">
                                        <form action="{{url('/save-address')}}" method="post">
                                            @csrf
                                            <h3>Customer Address</h3>
                                            <div class="formg address">
                                                <label for="">Address1</label>
                                                <input type="text" name="address1" value="{{$osa->address1}}">

                                                <label for="">Address2</label>
                                                <input type="text" name="address2" value="{{$osa->address2}}">

                                                <label for="">Zip Code</label>
                                                <input type="text" name="zip" value="{{$osa->zip}}">

                                                <label for="">City</label>
                                                <input name="city" type="text" value="{{$osa->city}}">

                                                <label for="">State</label>
                                                <select name="state">
                                                    @foreach ($states as $key=>$value)

                                                    @if($key==$state_key)
                                                    <option value="{{$key}}" selected>{{$value}}</option>
                                                    @else
                                                    <option value="{{$key}}">{{$value}}</option>

                                                    @endif
                                                    @endforeach
                                                </select>
                                                <label>Country</label>
                                                <span>{{$osa->country}}</span>
                                            </div>

                                            @if($order->fulfillment_status== 4)
                                            <button class="" id="save-address">Save</button>
                                            @endif

                                            <!-- If address was updated -->
                                            @if($osa->update_merchant_id > 0)
                                            <p class="updated"><strong>Updated 2 </strong> <br>{{$osa->update_date}}.</p>
                                            @endif
                                        </form>
                                    </div>

                                    @if($order->financial_status == 2)
                                    <h3 class="sod-title">Stripe Payment Detail</h3>
                                    <div class="formg">
                                        <p class="font-weight-bold">Date</p>
                                        <p>{{$order->created_at}}</p>
                                        <p class="font-weight-bold">Id Transaction</p>
                                        <p>{{$payment_intent}}</p>
                                        <p class="font-weight-bold">Card Number</p>
                                        <p>xxxx xxxx xxxx {{$payment_card_number}}</p>
                                    </div>
                                    @endif
                                </div>
                                <input name="order_id" type="hidden" value="{{$order->id}}">
                            </div>
                            <div class="rightSide">
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
                                                        <img src="{{$op->image_url}}">
                                                    </div>
                                                </td>
                                                <td data-label="PRODUCT NAME">{{$op->name}}</td>
                                                <td data-label="PRICE" class="nowrap">US$ {{$op->price}}</td>
                                                <td data-label="QUANTITY">{{$op->quantity}}</td>
                                                <td class="sku" data-label="SKU">{{$op->sku}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <h3 class="mt-5">Cost Summary</h3>
                                    <table class="resumetable resume" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td class="font-weight-bold">SUB TOTAL</td>
                                                <td>${{$mg_order ? number_format($mg_order->subtotal, 2) : number_format($order->total, 2)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">SHIPPING & HANDLING</td>
                                                <td>${{$mg_order ? number_format($mg_order->shipping_amount, 2) : number_format($order->shipping_price, 2)}}</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">STORE CREDIT</td>
                                                <td>${{$mg_order ? number_format($mg_order->grand_total - $mg_order->subtotal - $mg_order->shipping_amount, 2) : 0}}</td>
                                            </tr>
                                            <tr class="border-top">
                                                <td class="font-weight-bold">GRAND TOTAL</td>
                                                <td>${{$mg_order ? number_format($mg_order->grand_total, 2) : $order->total + $order->shipping_price}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="productbtn">
                                    @if($order->fulfillment_status== 4)
                                    <button class="cancel my-1" id="cancel-button" data-toggle="modal" data-target="#confirm-modal" data-id="{{$order->id}}">Cancel Order</button>
                                    @endif
                                    @if($order->financial_status== 2 && $order->fulfillment_status== 5)
                                    <button class="cancel my-1" id="cancel-req-button" data-toggle="modal" data-target="#confirm-modal" data-id="{{$order->id}}">Cancel Request</button>
                                    @endif
                                    @if($order->financial_status== App\Libraries\OrderStatus::Outstanding && ($order->fulfillment_status != 9 && $order->fulfillment_status != 12))
                                    <button class="payments my-1" id="checkout-button" data-id="{{$order->id}}">Pay Order</button>
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
<input type="text" value="" id="request_type" hidden>

<script type="text/javascript">
    $(document).ready(function() {
        
        $("#cancel-button").click(function() {
            $('#confirm-modal-body').html('<h5>Do you really want to cancel the order?</h5>');
            $('#confirm').text('Cancel Order');
            $('#cancel').text('Do Not Cancel');
            $('#request_type').val('cancel');
        });

        $("#cancel-req-button").click(function() {
            $('#confirm-modal-body').html('<h5>Do you really want to cancel the order?</h5>');
            $('#confirm').text('Cancel Order');
            $('#cancel').text('Do Not Cancel');
            $('#request_type').val('cancel_req');
        });

        $('#confirm').click(function() {
            if ($('#request_type').val() === 'cancel') {
                window.location.href = "{{url('orders/cancel/')}}/" + $('#cancel-button').attr('data-id');
            } else if ($('#request_type').val() === 'cancel_req') {
                window.location.href = "{{url('orders/cancel-request/')}}/" + $('#cancel-req-button').attr('data-id');
            }
        })

        var checkoutButton = document.getElementById('checkout-button');
        checkoutButton.addEventListener('click', function() {
            var stripe = Stripe('{{env("STRIPE_API_KEY")}}');
            let orders = [$(this).attr('data-id')];
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
    });
</script>


@endsection