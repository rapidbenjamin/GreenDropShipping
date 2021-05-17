@extends('layouts.app')
@section('content')

<div class="indexContent" data-page_name="ADMIN ORDERS">
    <div class="maincontent">
        <div class="wrapinsidecontent"> 

            <form class="headerorders adminorders-settings" id="searchFilters" method="get" action="{{url('/admin/orders')}}">
                   <div class="date">
                       Date
                   </div>
                   <div class="dates">
                       <label for="">From</label>
                       <input type="date" id="dateFrom" name="dateFrom" value="{{Request()->dateFrom}}">
                   </div>
                   <div class="dates">
                       <label for="">To:</label>
                       <input type="date" id="dateTo" name="dateTo" value="{{Request()->dateTo}}">
                   </div>
                   <div class="noorder">
                       N<sup>o</sup> Order
                   </div>
                   <div class="search">
                       <input type="text" id="idOrder" name="idOrder" value="{{Request()->idOrder}}" placeholder="Order #">                  
                   </div>
                   <div>
                       <input type="text" id="merchant" name="merchant" value="{{Request()->merchant}}" placeholder="Merchant">
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
                            <option value="0">Order State</option>
                            @foreach($status as $st)
                            @if($st->type == 2)
                            <option value="{{$st->id}}" {{(Request()->selectOS==$st->id?'selected':'')}}>{{$st->name}}</option>
                            @endif
                            @endforeach
                        </select>
                   </div>
                   <div class="searchbtn">
                    <button id="search" class="searchbutton"><i class="fa fa-search" aria-hidden="true"></i> Search</button>
                   </div>
            </form> 


            <div class="results">
            </div>

            <div class="actions">
                   <button class="exporsel">Export Selected Orders</button>
                   <div></div>
                   <div></div>
            </div>

            <div class="orders">
                <table class="greentable tableorders" cellspacing="0">
                       <thead>
                           <tr>
                               <th>
                                   <input type="checkbox">
                               </th>
                               <th>
                                   ORDER #
                               </th>
                               <th>
                                   DATE
                               </th>
                               <th>
                                   TOTAL TO PAY
                               </th>
                               <th>
                                   MERCHANT
                               </th>
                               <th>
                                   PAYMENT STATUS <span class="simple-tooltip" title="This indicates whether or not your payment has been processed.">?</span>
                               </th>
                               <th>
                                   ORDER STATE  <span class="simple-tooltip" title="This is the status of your customer's order.">?</span>
                               </th>
                               <th>
                                   VIEW
                               </th>
                           </tr>
                       </thead>
                       <tbody>

                        @php $k = 0 @endphp
                        @foreach($order_list as $ol)
                        @if($k == 0)
                        @php
                        $back = 'transparent';
                        $k = 1;
                        @endphp
                        @else
                        @php
                        $back = '';
                        $k = 0;
                        @endphp

                        @endif
                           <tr class="productdatarow">
                               <td class="check">
                                   <input type="checkbox">
                               </td>
                               <td data-label="ORDER #">
                                   {{$ol->order_number_shopify}}
                               </td>
                               <td data-label="DATE">
                                   {{$ol->created_at}}
                               </td>
                               <td data-label="TOTAL TO PAY">
                                  ${{$ol->total}}
                               </td>
                               <td data-label="MERCHANT">
                                  {{$ol->merchant_name}}
                               </td>
                               <td>
                                   <div class="buttonge"  style="background-color: {{$ol->color1}}">{{$ol->status1}}</div>
                               </td>
                               <td>
                                   <div class="buttonge"  style="background-color: {{$ol->color2}}">{{$ol->status2}}</div>
                               </td>
                               <td>
                                   <a href="https://app.greendropship.com/admin/orders/{{$ol->id}}">
                                       <button class="view" >View</button>
                                   </a>
                               </td>
                           </tr>
                        @endforeach

                       </tbody>
                </table>
            </div>
            

            <!-- pagination -->
            <div class="pagination">
                {{ $order_list->appends(request()->query())->links() }}
            </div>
            <!-- /pagination -->

        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>

<script type="text/javascript">
    $("#search").click(function(e) {
        if ($('#dateFrom').val() != '' && $('#dateTo').val() == '') {
            e.preventDefault();
            alert('You must select a valid end date.');
        } else if ($('#dateTo').val() != '' && $('#dateFrom').val() == '') {
            e.preventDefault();
            alert('You must select a valid start date.');
        } else if (moment($('#dateFrom').val()).isAfter(moment($('#dateTo').val()).format('YYYY-MM-DD'))) {
            e.preventDefault();
            alert('Invalid date range.');
            //return;
        }
    });

    $('.btn-order-export-selected').click(function() {
        window.location.href = encodeURI('{{url("/admin/orders/exports")}}');

    });
</script>
@endsection