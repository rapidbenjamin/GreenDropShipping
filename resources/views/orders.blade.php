@extends('layouts.app')



@section('content')

<div class="indexContent" data-page_name="ORDERS">

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

            <div class="alertan level2">
               <div class="agrid">
                    <p><strong> Orders are submitted to GreenDropShip for processing once the grand total for each transaction is paid, which includes the wholesale price + shipping.</strong></p>
               </div>
            </div>

             @if(Request()->payment=='success')
            <div class="alertan level2">
               <div class="agrid">
                    <p><strong>success!</strong> Payment successful.</p>
               </div>
            </div>
            @endif

            @if(Request()->payment=='cancel')
            <div class="alertan level2">
               <div class="agrid">
                    <p><strong>Error!</strong> Transaction canceled.
               </div>
            </div>
            @endif


               <div class="headerorders">
                   <div class="date">
                       Date
                   </div>
                   <div class="dates">
                       <label for="">From</label>
                       <input type="date" id="date-order-from" value="{{Request()->from}}">
                   </div>
                   <div class="dates">
                       <label for="">To:</label>
                       <input type="date" id="date-order-to" value="{{Request()->to}}">
                   </div>
                   <div class="noorder">
                       N<sup>o</sup> Order
                   </div>
                   <div class="search">
                       <input type="text" id="txt-order-search" value="{{Request()->order}}" placeholder="Search Order">
                   </div>
                   <div class="paymentstatus">
		                <select id="selectFS" name="selectFS">
		                    <option value="0">Payment Status</option>
		                    @foreach($status as $st)
		                    @if($st->type == 1)
		                    <option value="{{$st->id}}" {{(Request()->selectFS==$st->id?'selected':'')}}>{{$st->name}}</option>
		                    @endif
		                    @endforeach
		                </select>
                   </div>
                   <div class="orderstate">
		                <select id="selectOS" name="selectOS">
		                    <option value="0">Order Status</option>
		                    @foreach($status as $st)
		                    @if($st->type == 2)
		                    <option value="{{$st->id}}" {{(Request()->selectOS==$st->id?'selected':'')}}>{{$st->name}}</option>
		                    @endif
		                    @endforeach
		                </select>
                   </div>
                   <div class="searchbtn">
                   	   <button class="btn-order-search searchbutton">
                   	   		<i class="fa fa-search" aria-hidden="true"></i>
                   	   Search</button>
                   </div>
               </div>


               @if(Auth::user()->plan == 'basic')
               <div class="results">
                   <div><strong>Period</strong></div>
                   <div>{{$basic_period_orders}}</div>
                   <div><strong>Total Orders</strong></div>
                   <div>
                       <span class="badge" style="{{($total_period_orders >= env('LIMIT_ORDERS'))?'background-color:red':''}}">{{$total_period_orders}}</span> of <span class="badge">100</span>
                   </div>
               </div>


               <div class="actions">
		            <button id="checkout-button" class="btn-order-pay-selected pays">Pay Selected Orders</button>
		            <button class="btn-order-export-selected exporsel">Export Selected Orders</button>
		            <button class="btn-order-notifications pendingpay" title="Outstanding orders that require payment"> <span class="badge">{{$notifications}}</span> Pending payments</button>
               </div>
               @endif

               <div class="orders">
                   <table class="greentable tableorders" cellspacing="0">
                       <thead>
                           <tr>
                               <th>
                                   <input type="checkbox" id="check-all-mp" value="">
                               </th>
                               <th>
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
                                   ORDER STATUS  <span class="simple-tooltip" title="This is the status of your customer's order.">?</span>
                               </th>
                               <th>
                                   PAY ORDER <span class="simple-tooltip" title="You need to pay for the order to submit it to GreenDropShip.">?</span>
                               </th>
                               <th>
                                   VIEW
                               </th>
                           </tr>
                       </thead>
                       <tbody>
                       	   @foreach($order_list as $ol)
                           <tr class="productdatarow">
                               <td class="check">
                                   <input class="checkbox cb{{$ol->id}}" type="checkbox" data-id="{{$ol->id}}" value="">
                               </td>
                               <td data-label="ORDER #">
                                   {{$ol->order_number_shopify}}
                               </td>
                               <td data-label="DATE">
                                   {{$ol->created_at}}
                               </td>
                               <td data-label="CUSTOMER NAME">
                                  {{$ol->first_name}} {{$ol->last_name}}
                               </td>
                               <td data-label="TOTAL TO PAY">
                                   ${{$ol->total + $ol->shipping_price}}
                               </td>
                               <td>
                                   <div class="buttonge" >
                                       {{$ol->status1}}
                                   </div>
                               </td>
                               <td>
                                   <div class="buttonge" >
                                       {{$ol->status2}}
                                   </div>
                               </td>
                               <td>
					                @if($ol->financial_status== App\Libraries\OrderStatus::Outstanding && ($ol->fulfillment_status != 9 && $ol->fulfillment_status != 12))
					                <button class="payorder pay-button checkout-button checkout-button2"  data-id="{{$ol->id}}">PAY ORDER</button>
					                @else
					                <button class="payorder payorderoff disabled" data-id="{{$ol->id}}">PAID</button>
					                @endif
                               </td>
                               <td>
                                   <a href="/orders/{{$ol->id}}"><button class="view">VIEW</button></a>
                               </td>
                           </tr>
                           @endforeach
                       </tbody>
                   </table>

               <!-- pagination -->
                <div class="pagination">
                       {{ $order_list->appends(request()->query())->links() }}
                </div>
               <!-- /pagination -->


               </div>

            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        var order_id = 0;


        $('.btn-order-search').click(function() {
            window.location.href = encodeURI('{{url("/orders")}}?from=' + $('#date-order-from').val() + '&to=' +
                $('#date-order-to').val() + '&order=' + $('#txt-order-search').val() + '&st1=' + $('#selectFS').val() + '&st2=' + $('#selectOS').val());
        });

        $('#check-all-mp').click(function() {
            if (!$(this).data('mark')) {
                $('.checkbox').prop('checked', true);
                $(this).data('mark', true)
            } else {
                $('.checkbox').prop('checked', false);
                $(this).data('mark', false)
            }
        });

        $('.btn-order-export-selected').click(function() {
            let orders = '';
            $("input.checkbox:checked").each(function(index, ele) {
                orders += $(ele).attr('data-id') + ',';
            });
            if (orders == '') {
                alert('you must select at least one order');
                return;
            }
            window.location.href = encodeURI('{{url("/orders/exports")}}?orders=' + orders);

        });

        $('.btn-order-notifications').click(function() {
            window.location.href = encodeURI('{{url("/orders/")}}?notifications=true');
        });



        var checkoutButton = document.getElementById('checkout-button');
        checkoutButton.addEventListener('click', function() {

            var stripe = Stripe('{{env("STRIPE_API_KEY")}}');
            let orders = [$(this).attr('data-id')];

            $("input.checkbox:checked").each(function(index, ele) {
                orders.push($(ele).attr('data-id'));
            });
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

        $("div.alert button.close").click(function() {
            window.location.href = "{{url('/orders')}}"
        });

        $('.checkout-button2').click(function() {

            var idx = $(this).data('id');
            $('.cb' + idx).attr('checked','checked');

            //let arrayboxes = $(".checkbox").prop("checked");
            var stripe = Stripe('{{env("STRIPE_API_KEY")}}');
            let res = $('input[name=s_method]:checked', '#shipping-methods').val();
            let orders = [$(this).attr('data-id')];

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

    });
</script>



@endsection
