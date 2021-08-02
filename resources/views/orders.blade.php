@extends('layouts.app')



@section('content')

<div class="indexContent" data-page_name="ORDERS">
    <div class="maincontent">
        <div class="wrapinsidecontent">
            @if(Auth::user()->plan == 'free')
            <div class="alertan">
                <div class="agrid">
                    <img src="/img/infogray.png" srcset="/img/infogray@2x.png 2x,/img/infogray@3x.png 3x">
                    <p>You have a free plan. <a href="/plans">Click here to upgrade your plan.</a></p>
                </div>
            </div>
            @endif

            <div class="alertan level2">
                <div class="agrid">
                    <p><strong>You must pay the grand total for each transaction including wholesale price + shipping.</strong></p>
                </div>
                <i class="fa fa-close text-secondary" aria-hidden="true"></i>
            </div>

            @if(Request()->payment=='success')
            <div class="alertan level2">
                <div class="agrid">
                    <p><strong>Success!</strong> Payment successful.</p>
                </div>
                <i class="fa fa-close text-secondary" aria-hidden="true"></i>
            </div>
            @endif

            @if(Request()->payment=='cancel')
            <div class="alertan level2">
                <div class="agrid">
                    <p><strong>Error!</strong> Transaction canceled.
                </div>
                <i class="fa fa-close text-secondary" aria-hidden="true"></i>
            </div>
            @endif


            <div class="headerorders">
                <div class="date">
                    Date
                </div>
                <div class="dates">
                    <label for="">From</label>
                    <input type="date" id="date_from" value="{{Request()->from}}">
                </div>
                <div class="dates">
                    <label for="">To:</label>
                    <input type="date" id="date_to" value="{{Request()->to}}">
                </div>
                <div class="noorder">
                    Order #
                </div>
                <div class="search">
                    <input type="text" id="order_id" value="{{Request()->order}}" placeholder="Order #">
                </div>
                <div class="paymentstatus">
                    <select id="payment_status" name="selectFS">
                        <option value="0">Payment Status</option>
                        @foreach($status as $st)
                        @if($st->type == 1)
                        <option value="{{$st->id}}" {{(Request()->selectFS==$st->id?'selected':'')}}>{{$st->name}}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div class="orderstate">
                    <select id="order_state" name="selectOS">
                        <option value="0">Order Status</option>
                        @foreach($status as $st)
                        @if($st->type == 2)
                        <option value="{{$st->id}}" {{(Request()->selectOS==$st->id?'selected':'')}}>{{$st->name}}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div class="searchbtn">
                    <button class="btn-order-search searchbutton greenbutton">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        Search
                    </button>
                </div>
            </div>

            @if(Auth::user()->plan == 'basic')
            <div class="results">
                <div><strong>Period</strong></div>
                <div><label id="period">{{$basic_period}}</label></div>
                <div><strong>Total Orders</strong></div>
                <div>
                    <span class="font-weight-bold" id="total_period_orders" style="{{($total_period_orders >= env('LIMIT_ORDERS'))?'background-color:red':''}}">{{$total_period_orders}}</span> of <span class="font-weight-bold" id="total_orders">{{$total_count}}</span>
                </div>
            </div>
            <div class="actions">
                <button class="btn-order-notifications pendingpay mt-5" title="Outstanding orders that require payment"> <span class="badge" id="notifications">{{$notifications}}</span> Pending Payments</button>
                <div></div>
                <div></div>
            </div>
            <div class="pagesize">
                <span>Show</span>
                <select name="PageSize" id="page_size">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            @endif

            <div class="orders">
                <table class="greentable tableorders" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="nowrap">
                                ORDER #
                            </th>
                            <th>
                                DATE
                            </th>
                            <th>
                                CUSTOMER NAME
                            </th>
                            <th>
                                TOTAL TO PAY
                            </th>
                            <th>
                                PAYMENT STATUS <span class="simple-tooltip" title="This indicates whether or not your payment has been processed.">?</span>
                            </th>
                            <th>
                                ORDER STATUS <span class="simple-tooltip" title="This is the status of your customer's order.">?</span>
                            </th>
                            <th>
                                PAY ORDER <span class="simple-tooltip" title="You need to pay for the order to submit it to GreenDropShip.">?</span>
                            </th>
                            <th>
                                VIEW
                            </th>
                        </tr>
                    </thead>
                    <tbody id="order_data">
                        @if($is_notification && !$notifications)
                        <div class="alertan level2">
                            <div class="agrid">
                                <p><strong>No orders pending payment. Good job!</strong></p>
                            </div>
                        </div>
                        @endif
                        @foreach($order_list as $ol)
                        <tr class="productdatarow">
                            <td data-label="ORDER #">
                                {{substr($ol->order_number_shopify, 1)}}
                            </td>
                            <td data-label="DATE">
                                {{$ol->created_at}}
                            </td>
                            <td data-label="CUSTOMER NAME">
                                {{$ol->first_name}} {{$ol->last_name}}
                            </td>
                            <td data-label="TOTAL TO PAY">
                                ${{number_format($ol->total + $ol->shipping_price, 2, '.', '')}}
                            </td>
                            <td data-label="PAYMENT STATUS">
                                {{$ol->status1}}
                            </td>
                            <td data-label="ORDER STATE">
                                {{$ol->status2}}
                            </td>
                            <td>
                                @if($ol->financial_status== App\Libraries\OrderStatus::Outstanding && $ol->fulfillment_status != 9 && $ol->fulfillment_status != 12)
                                <button class="payorder pay-button checkout-button" data-id="{{$ol->id}}">PAY ORDER</button>
                                @elseif($ol->fulfillment_status == 9)
                                <button class="payorder payorderoff canceled" data-id="{{$ol->id}}">Canceled</button>
                                @else
                                <button class="payorder payorderoff paid" data-id="{{$ol->id}}">Paid</button>
                                @endif
                            </td>
                            <td>
                                <a href="/orders/{{$ol->id}}"><button class="view greenbutton">VIEW</button></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div id="pagination"></div>
<input type="text" id="total_count" value="{{$total_count}}" hidden>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#total_count').text("{{$total_count}}");

        if ($('#notifications').text() > 9) {
            $('#notifications').addClass('circle');
        } else {
            $('#notifications').removeClass('circle');
        }
        $('#check-all-mp').click(function() {
            if (!$(this).data('mark')) {
                $('.checkbox').prop('checked', true);
                $(this).data('mark', true)
            } else {
                $('.checkbox').prop('checked', false);
                $(this).data('mark', false)
            }
        });

        $('.btn-order-notifications').click(function() {
            window.location.href = encodeURI('{{url("/orders/")}}?notifications=true');
        });
    });

    $('#order_data').on('click', '.checkout-button', function() {
        let idx = $('.checkout-button').data('id');
        $('.cb' + idx).attr('checked', 'checked');

        let stripe = Stripe('{{env("STRIPE_API_KEY")}}');
        let res = $('input[name=s_method]:checked', '#shipping-methods').val();
        let orders = [$('.checkout-button').attr('data-id')];

        $("input.checkbox:checked").each(function(index, ele) {
            orders.push($(ele).attr('data-id'));
        });

        console.log('ordenes... ' + orders);

        // Create a new Checkout Session using the server-side endpoint you
        // created in step 3.
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
</script>

@endsection