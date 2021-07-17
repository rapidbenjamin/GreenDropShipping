@extends('layouts.app')
@section('content')
<div class="orderDetailContent" data-page_name="ADMIN ORDER DETAIL">
    <div class="maincontent">
        <p class="back" data-url='{{ url()->previous() }}'>
            <a href="{{ url()->previous() }}">
                < BACK
            </a>
        </p>
        <div class="wrapinsidecontent">
            <div class="ordertable">
                <div class="headerorder">
                    <div class="norder">
                        Order
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
                                <h3>Merchant Information</h3>
                                <div class="cwrap">
                                    <p><strong>Name</strong> <br>{{$merchant->name}}</p>
                                    <p><strong>Email</strong> <br>{{$merchant->email}}</p>
                                    <p><strong>Plan</strong> <br>{{$merchant->plan}}</p>

                                    @if($order->shipping_title)
                                    <p><strong>Shipping Information</strong> <br>
                                        @if($order->shipping_title)
                                    <p><strong>Shipping</strong> <br>{{$order->shipping_title}}</p>
                                    @if($order->tracking_code)
                                    <p><strong>Tracking Number</strong> <br>{{$order->tracking_code}}</p>
                                    @endif
                                    @endif
                                    </p>
                                    @endif

                                    <div class="box addres">
                                        <h3>Customer Address</h3>
                                        <div class="cwrap">
                                            <p><strong>Name</strong> <br>{{$osa->first_name}} {{$osa->last_name}}</p>
                                            <p><strong>Address</strong> <br>{{$osa->address1}} {{$osa->address2}}</p>
                                            <p><strong>Zip Code</strong> <br>{{$osa->zip}}</p>
                                            <p><strong>City</strong> <br> {{$osa->city}}</p>
                                            <p><strong>State</strong> <br> {{$osa->province}}</p>
                                            <p><strong>Country</strong> <br> {{$osa->country}}</p>
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
                            </div>
                        </div>
                        <div class="rightSide">
                            <div class="states">
                                <div>
                                    <p>Payment Status</p>
                                    <div class="paid" style="background:{{$fs->color}};">
                                        <span>{{$fs->name}}</span>
                                    </div>
                                </div>
                                <div>
                                    <p>Order State</p>
                                    <div class="inprocess" style="background:{{$os->color}};">
                                        <span>{{$os->name}}</span>
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
                                                PRICE / PROFIT
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
                                            <td data-label="PRODUCT NAME">
                                                {{$op->name}}
                                            </td>
                                            <td data-label="PRICE">
                                                <strong>
                                                    ${{$op->price . ' / ' . $op->profit}}%
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
                                            <tr>
                                                <td colspan="2" class="header">
                                                    ORDER RESUME
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>SUB TOTAL</strong></td>
                                                <td>${{$order->total}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>SHIPPING & HANDLING</strong></td>
                                                <td>${{$order->shipping_price}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>STORE CREDIT</strong></td>
                                                <td>${{$mg_order ? number_format($mg_order->grand_total - $mg_order->subtotal - $mg_order->shipping_amount, 2, '.', '') : 0}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>GRAND TOTAL</strong></td>
                                                <td>${{$mg_order ? $mg_order->grand_total : $order->total + $order->shipping_price}}</td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="notes">
                                        <textarea class="ta{{$order->id}}">{{$order->notes}}</textarea>
                                        <div class="btns">
                                            <button id='btnNotes' data-id="{{$order->id}}" class="btn bgVO colorBL">Update Notes</button>
                                            @if($order->fulfillment_status== 4)
                                            <button class="btn bgVC bgRED colorBL" id="cancel-button" data-toggle="modal" data-target="#delete-product-modal" data-id="{{$order->id}}">Cancel Order</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#cancel-button").click(function() {
            $('#modal-body').html('<h5>Do you really want to cancel the order?</h5>');
        });
        
        $('#confirm').click(function() {
            window.location.href = "{{url('admin/orders/cancel')}}/" + $('#cancel-button').attr('data-id');
        });
    });
</script>
@endsection