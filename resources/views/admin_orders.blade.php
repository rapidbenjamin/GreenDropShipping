@extends('layouts.app')
@section('content')

<div class="indexContent" data-page_name="ADMIN ORDERS">
    <div class="maincontent">
        <div class="wrapinsidecontent">

            <div class="headerorders adminorders-settings" id="searchFilters">
                <div class="date">
                    Date
                </div>
                <div class="dates">
                    <label for="">From</label>
                    <input type="date" id="dateFrom">
                </div>
                <div class="dates">
                    <label for="">To:</label>
                    <input type="date" id="dateTo">
                </div>
                <div class="noorder">
                    Custom order number
                </div>
                <div class="search">
                    <input type="text" id="idOrder" list="numbers" placeholder="Order #">
                    <datalist id="numbers">
                        <div id="number_data"></div>
                    </datalist>
                </div>
                <div>
                    <input type="text" id="merchant" list="names" placeholder="Merchant">
                    <datalist id="names">
                        <div id="merchant_data"></div>
                    </datalist>
                </div>
                <div class="paymentstatus">
                    <select id="paymentstatus">
                        <option value="0">Payment Status</option>
                        @foreach($status as $st)
                        @if($st->type == 1)
                        <option value="{{$st->id}}" {{(Request()->selectFS==$st->id?'selected':'')}}>{{$st->name}}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div class="orderstate">
                    <select id="orderstate">
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
            </div>


            <div class="results">
            </div>

            <div class="actions">
                <button class="exporsel">Export Selected Orders</button>
                <div></div>
                <div class="pagesize">
                    <span>Show</span>
                    <select name="PageSize" id="page_size">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
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
                                ORDER STATE <span class="simple-tooltip" title="This is the status of your customer's order.">?</span>
                            </th>
                            <th>
                                VIEW
                            </th>
                        </tr>
                    </thead>
                    <tbody id="order_data">

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
                        <tr class="orderrow">
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
                                ${{number_format($ol->total, 2, '.', '')}}
                            </td>
                            <td data-label="MERCHANT">
                                {{$ol->merchant_name}}
                            </td>
                            <td>
                                <div class="buttonge" style="background-color: {{$ol->color1}}">{{$ol->status1}}</div>
                            </td>
                            <td>
                                <div class="buttonge" style="background-color: {{$ol->color2}}">{{$ol->status2}}</div>
                            </td>
                            <td>
                                <a href="https://app.greendropship.com/admin/orders/{{$ol->id}}">
                                    <button class="view">View</button>
                                </a>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>


            <!-- pagination -->
            <div class="pagination">
                <ul class="pagination" role="navigation">
                    <li class="page-item" id="prev">
                        <a class="page-link" rel="prev" aria-label="« Previous">‹</a>
                    </li>

                    <li class="page-item active" aria-current="page"><span id="page_number" class="page-link">1/{{ceil($total_count/10)}}</span></li>

                    <li class="page-item" id="next" aria-disabled="true" aria-label="Next »">
                        <span class="page-link" aria-hidden="true">›</span>
                    </li>
                </ul>
                <input type="text" id="total_count" value="{{$total_count}}" hidden>
            </div>
            <!-- /pagination -->

        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#total_count').text("{{$total_count}}");

        $('#idOrder').keypress(function(e) {
            if ($('#idOrder').val().length >= 2) {
                var parameters = {
                    action: 'admin-order-number'
                }
                $.getJSON(ajax_link, parameters, function(data) {
                    var str = '<div id="number_data">';
                    data.numbers.forEach(number => {
                        str += `<option value="${number.substr(1)}">`;
                    });
                    str += '</div>';
                    $('#number_data').remove();
                    $('#numbers').html(str);
                })
            }
        })

        $('#merchant').keypress(function(e) {
            if ($('#merchant').val().length >= 2) {
                var parameters = {
                    action: 'admin-order-merchant'
                }
                $.getJSON(ajax_link, parameters, function(data) {
                    var str = '<div id="merchant_data">';
                    data.names.forEach(name => {
                        str += `<option value="${name}">`;
                    });
                    str += '</div>';
                    $('#merchant_data').remove();
                    $('#names').html(str);
                })
            }
        })

        $('.exporsel').click(function() {
            window.location.href = encodeURI('{{url("/admin/orders/exports")}}');

        });
    });
</script>
@endsection
