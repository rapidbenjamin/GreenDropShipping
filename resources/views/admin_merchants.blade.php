@extends('layouts.app')
@section('content')

<div class="indexContent" data-page_name="ADMIN MERCHANTS">
	<div class="maincontent">
        <div class="wrapinsidecontent"> 

        	<div class="actions">
                   <a href="{{url('admin/merchants/exportCSV')}}" >
                   	<button class="exporsel">Export CSV</button>
                   	</a>
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
                                   ID
                               </th>
                               <th>
                                   MERCHANT NAME
                               </th>
                               <th>
                                   EMAIL
                               </th>
                               <th>
                                   SHOPIFY URL
                               </th>
                               <th>
                                   PLAN
                               </th>
                               <th>
                                   ACTIVE
                               </th>
                               <th>
                                   ACTIONS
                               </th>
                           </tr>
                       </thead>
                       <tbody>

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
                           <tr class="productdatarow">
                               <td class="check">
                                   <input type="checkbox">
                               </td>
                               <td data-label="ID">
                                   {{$ml->id}}
                               </td>
                               <td data-label="MERCHANT NAME">
                                   {{$ml->name}}
                               </td>
                               <td data-label="EMAIL">
                                  {{$ml->email}}
                               </td>
                               <td data-label="SHOPIFY URL">
                                  @if($ml->shopify_url) {{$ml->shopify_url}} @else --- @endif
                               </td>
                               <td data-label="PLAN">
                                   {{$ml->plan}}
                               </td>
                               <td data-label="ACTIVE">
                                   <input type="checkbox" name="switch-button" id="switch-label{{$ml->id}}" data-merchantid="{{$ml->id}}" class="switch-button__checkbox change-status" @if($ml->active == 1)checked @endif>
                               </td>
                               <td class="btngroup">
                                   
                                   <button class="view detail-merchants" data-merchantid="{{$ml->id}}">View</button>
                                   <button class="payorder orders-customers" data-merchant="{{$ml->name}}">Orders</button>
                                   
                               </td>
                           </tr>
                        @endforeach

                       </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection