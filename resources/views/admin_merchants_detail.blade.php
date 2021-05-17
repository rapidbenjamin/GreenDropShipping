@extends('layouts.app')
@section('content')
<div class="orderDetailContent" data-page_name="ADMIN ORDER DETAIL">
    <div class="maincontent">

        <p class="back" data-url='{{ url()->previous() }}'><a href="{{ url()->previous() }}">< BACK</a> </p>

        <div class="wrapinsidecontent"> 
    

            <div class="panel-left">      
                <div class="cwrap">
                            
                    <h3 class="titlebasic">Merchant Information</h3>
                    
                    <p>
                        <strong>Name</strong> <br>
                        {{$merchant->name}}
                    </p>

                    <p>
                        <strong>Email</strong> <br>
                        {{$merchant->email}}
                    </p>
                                    
                    <p>
                        <strong>Plan</strong> <br>
                        {{$merchant->plan}}
                    </p>          
                                
                    <p>
                        <strong>Shopify URL</strong> <br>
                        {{$merchant->shopify_url}}
                    </p>     
                                
                    <p>
                        <strong>Role</strong> <br>
                        {{$merchant->role}}
                    </p>            
                                
                    <p>
                        <strong>Active</strong> <br>
                        {{$merchant->active==1?'Yes':'No'}}
                    </p>           
                                
                    <p>
                        <strong>Date</strong> <br>
                        {{$merchant->created_at}}
                    </p>                
      
                </div>
            </div>

                    
                  
    

        </div>
    </div>
</div>

@endsection