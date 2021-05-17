@extends('layouts.app')



@section('content')


<style type="text/css">
    .adminorders-settings {
        background-color: #fff;
        width: 98% !important;
        padding: 15px;
        margin: 20px 1%;
    }

    .adminCtrl {
        float: left;
        margin: 0 20px;
    }

    .adminCtrl input {
        text-align: center;
        border: solid 2px #ddd;
        padding: 5px;
        color: #989898;
        font-weight: bold;
    }

    .adminCtrl select {
        text-align: center;
        border: solid 2px #ddd;
        padding: 5px;
        color: #989898;
        font-weight: bold;
    }

    .adminCtrl button {
        text-align: center;
        border: solid 2px #fff;
        padding: 7px 30px;
        color: #fff;
        font-weight: bold;
        font-size: 16px;
        letter-spacing: 2px;
    }

    .adminCtrl button:hover {
        background-color: #89B73D;
    }

    .adminCtrl label {
        padding: 5px 10px;
        color: #989898;
        font-size: 19px;
        font-weight: bold;
    }

    ::placeholder {
        color: #989898;
    }


    .adminorders-settings {
        float: left;
        margin-bottom: 20px;
        width: 100%
    }

    img {
        width: 100%;
    }

    .adminorders-orders {
        float: left;
        width: 100%;
        margin: 100px 0 20px;
    }

    .admin-orders-titles {
        list-style: none;
        float: left;
        width: 100%;
        font-weight: bold;
    }

    .admin-orders-titles li {
        float: left;
        width: 14%;
        text-align: center;
    }

    .admin-orders-data {
        list-style: none;
        float: left;
        width: 100%;
        margin: 0;
        padding: 10px;
    }

    .admin-orders-data li {
        float: left;
        width: 14%;
        text-align: center;
    }

    .admin-orders-data li span {
        padding: 5px 30px;
    }
</style>




<div class="container indexContent" data-page_name="ADMIN ORDERS">

    <form id="searchFilters" method="get" action="{{url('/admin/orders')}}" class="col-md-12 col-sm-12 col-xs-12 adminorders-settings">

        <div class="adminCtrl"><input type="text" id="idOrder" name="idOrder" value="{{Request()->idOrder}}" placeholder="Order #"></div>
        <div class="adminCtrl"><input type="text" id="merchant" name="merchant" value="{{Request()->merchant}}" placeholder="Merchant"></div>
        <div class="adminCtrl"><select id="selectFS" name="selectFS">
                <option value="0">Payment Status</option>
                @foreach($status as $st)
                @if($st->type == 1)
                <option value="{{$st->id}}" {{(Request()->selectFS==$st->id?'selected':'')}}>{{$st->name}}</option>
                @endif
                @endforeach
            </select></div>
        <div class="adminCtrl"><select id="selectOS" name="selectOS">
                <option value="0">Order State</option>
                @foreach($status as $st)
                @if($st->type == 2)
                <option value="{{$st->id}}" {{(Request()->selectOS==$st->id?'selected':'')}}>{{$st->name}}</option>
                @endif
                @endforeach
            </select></div>
        <div class="adminCtrl">
            <label>Date</label>
            <input type="date" id="dateFrom" name="dateFrom" value="{{Request()->dateFrom}}">
            <input type="date" id="dateTo" name="dateTo" value="{{Request()->dateTo}}">
        </div>
        <div class="adminCtrl"><button id="search" class="bgVO colorBL">Search</button></div>

    </form>


    <div class="col-md-12 col-sm-12 col-xs-12 adminorders-orders">
        <button class="btn-order-export-selected colorBL" style="margin-bottom: 20px;">Export Selected Orders</button>
        <ul class="col-md-12 col-sm-12 col-xs-12 admin-orders-titles bgGCC">
            <li>ORDER #</li>
            <li>DATE</li>
            <li>TOTAL TO PAY</li>
            <li>MERCHANT</li>
            <li>PAYMENT STATUS</li>
            <li>ORDER STATE</li>
            <li class="order-table-view">VIEW</li>
        </ul>

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


        <ul class="col-md-12 col-sm-12 col-xs-12 admin-orders-data bgGCC" style="background:{{$back}}">
           
            <li>{{$ol->order_number_shopify}}</li>
            <li>{{$ol->created_at}}</li>
            <li>${{$ol->total}}</li>
            <li>{{$ol->merchant_name}}</li>
            <li><span style="background-color: {{$ol->color1}}">{{$ol->status1}}</span></li>
            <li><span style="background-color: {{$ol->color2}}">{{$ol->status2}}</span></li>
            <li class="order-table-view"><a href="http://shopify.greendropship.com/admin/orders/{{$ol->id}}" class="bgVO colorBL">VIEW</a></li>
        </ul>
        @endforeach



    </div>

    <div class="row" style="float:left;">
        <div class="col-md-12">
            {{ $order_list->appends(request()->query())->links() }}
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