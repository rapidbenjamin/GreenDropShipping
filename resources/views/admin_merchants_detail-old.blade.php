@extends('layouts.app')

@section('content')
<div class="container orderDetailContent" data-page_name="ADMIN ORDER DETAIL">

    <p class="pBack bgGC" data-url='{{ url()->previous() }}'><a class="colorVO" href="{{ url()->previous() }}">
            < BACK</a>
    </p>

 <div class="screen-order-detail">
        <div class="sod-body">

            <div class="sod-body-side-left">

                <div class="sod-body-side-content">
                    <div class="sod-title">Merchant Information</div>
                    <div class="sod-row"><label>Name</label>
                        <value>{{$merchant->name}}</value>
                    </div>
                    <div class="sod-row"><label>Email</label>
                        <value>{{$merchant->email}}</value>
                    </div>
                    <div class="sod-row"><label>Plan</label>
                        <value>{{$merchant->plan}}</value>
                    </div>
                    <div class="sod-row"><label>Shopify URL</label>
                        <value>{{$merchant->shopify_url}}</value>
                    </div>
                    <div class="sod-row"><label>Role</label>
                        <value>{{$merchant->role}}</value>
                    </div>
                    <div class="sod-row"><label>Active</label>
                        <value>{{$merchant->active==1?'Yes':'No'}}</value>
                    </div>
                    <div class="sod-row"><label>Date</label>
                        <value>{{$merchant->created_at}}</value>
                    </div>
                </div>

            </div>




        </div>
    </div>
</div>

@endsection