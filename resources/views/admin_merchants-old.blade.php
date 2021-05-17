@extends('layouts.app')



@section('content')


<style type="text/css">
	.admin-merchants {
		float: left;
		width: 100%;
		margin: 20px 0;
	}

	.admin-merchants-titles {
		list-style: none;
		float: left;
		width: 100%;
		font-weight: bold;
	}

	.admin-merchants-titles li {
		float: left;
		width: 14%;
		text-align: center;
	}

	.admin-merchants-data {
		list-style: none;
		float: left;
		width: 100%;
		margin: 0;
		padding: 10px;
	}

	.admin-merchants-data li {
		float: left;
		width: 14%;
		text-align: center;
	}

	.admin-merchants-data li button {
		text-align: center;
		border: solid 2px #fff;
		padding: 3px 20px;
		color: #fff;
		font-weight: bold;
		font-size: 12px;
		letter-spacing: 2px;
	}

	.admin-merchants-data li button:hover {
		background-color: #89B73D;
	}

	.admin-merchants-data li button.btn2:hover {
		background-color: #1A6E33;
	}
</style>


<div class="container indexContent" data-page_name="ADMIN MERCHANTS">

	<div class="col-md-12 col-sm-12 col-xs-12 admin-merchants">
		<a href="{{url('admin/merchants/exportCSV')}}" >Export CSV</a>
		<ul class="col-md-12 col-sm-12 col-xs-12 admin-merchants-titles bgGCC">
			<li>ID</li>
			<li>MERCHANT NAME</li>
			<li>EMAIL</li>
			<li>SHOPIFY URL</li>
			<li>PLAN</li>
			<li>ACTIVE</li>
			<li>ACTIONS</li>
		</ul>

		@php $k = 0 @endphp
		@foreach($merchants_list as $ml)
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
		<ul class="col-md-12 col-sm-12 col-xs-12 admin-merchants-data bgGCC" style="background:{{$back}}">
			<li>{{$ml->id}}</li>
			<li style="text-align: left;">{{$ml->name}}</li>
			<li style="text-align: left;">{{$ml->email}}</li>
			<li>@if($ml->shopify_url) {{$ml->shopify_url}} @else --- @endif</li>
			<li>{{$ml->plan}}</li>
			<li>
				<input type="checkbox" name="switch-button" id="switch-label{{$ml->id}}" data-merchantid="{{$ml->id}}" class="switch-button__checkbox change-status" @if($ml->active == 1)checked @endif>
			</li>
			<li><button class="bgVO colorBL detail-merchants" data-merchantid="{{$ml->id}}">View</button> <button class="bgVC colorBL btn2 orders-customers" data-merchant="{{$ml->name}}">Orders</button></li>
		</ul>
		@endforeach



	</div>

</div>



@endsection