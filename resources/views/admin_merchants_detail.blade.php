@extends('layouts.app')
@section('content')
<div class="orderDetailContent" data-page_name="ADMIN ORDER DETAIL">
    <div class="maincontent">

        <p class="back" data-url='{{ url()->previous() }}'><a href="{{ url()->previous() }}">
                < BACK</a>
        </p>

        <div class="wrapinsidecontent">


            <div class="panel-left merchant-info">
                <h3 class="titlebasic text-center py-3">Merchant Information</h3>
                <div class="cwrap">
                    <p>
                        <strong>Name</strong> 
                        {{$merchant->name}}
                    </p>

                    <p>
                        <strong>Email</strong> 
                        {{$merchant->email}}
                    </p>

                    <p>
                        <strong>Shopify URL</strong> 
                        {{$merchant->shopify_url}}
                    </p>

                    <p>
                        <strong>Domain</strong> 
                        {{$shopify_data ? $shopify_data->domain : ''}}
                    </p>

                    <p>
                        <strong>Address1</strong> 
                        {{$shopify_data ? $shopify_data->address1 : ''}}
                    </p>

                    <p>
                        <strong>Address2</strong> 
                        {{$shopify_data ? $shopify_data->address2 : ''}}
                    </p>

                    <p>
                        <strong>City</strong> 
                        {{$shopify_data ? $shopify_data->city : ''}}
                    </p>

                    <p>
                        <strong>Province Code</strong> 
                        {{$shopify_data ? $shopify_data->province_code : ''}}
                    </p>

                    <p>
                        <strong>Zip</strong> 
                        {{$shopify_data ? $shopify_data->zip : ''}}
                    </p>

                    <p>
                        <strong>Country Code</strong> 
                        {{$shopify_data ? $shopify_data->country_code : ''}}
                    </p>

                    <p>
                        <strong>Customer Email</strong> 
                        {{$shopify_data ? $shopify_data->customer_email : ''}}
                    </p>

                    <p>
                        <strong>Phone</strong> 
                        {{$shopify_data ? $shopify_data->phone : ''}}
                    </p>

                    <p>
                        <strong>Shop Owner</strong> 
                        {{$shopify_data ? $shopify_data->shop_owner : ''}}
                    </p>

                    <p>
                        <strong>Role</strong> 
                        {{$merchant->role}}
                    </p>

                    <p>
                        <strong>Active</strong> 
                        {{$merchant->active==1?'Yes':'No'}}
                    </p>

                    <p>
                        <strong>Date</strong> 
                        {{$merchant->created_at}}
                    </p>

                </div>
            </div>





        </div>
    </div>
</div>

@endsection