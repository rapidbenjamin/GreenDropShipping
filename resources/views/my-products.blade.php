
@extends('layouts.app')

@section('content')
<div class="indexContent" data-page_name="MY PRODUCTS">

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
            
            <div class="alertan level2 alert-publish-all">
               <div class="agrid">
                    <p>You can view here all the products you have added to your Shopify store.</p>
               </div>                
            </div>   

                <table class="greentable" cellspacing="0">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="check-all-mp" value="" data-mark="false">	
                            </th>
                            <th>
                                Image		
                            </th>
                            <th>
                                Product Name	
                            </th>
                            <th>
                                Cost		
                            </th>
                            <th>
                                Profit
                            </th>
                            <th>
                                Price		
                            </th>
                            <th>
                                SKU		
                            </th>
                            <th>
                                	
                            </th>
                            <th>
                               	
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                       
                        
                        @foreach ($prods as $pr)
                        <tr class="productdatarow">
                           <td class="check">
                                <input type="checkbox" id="check-{{ $pr->id }}" value="" class="checkbox">	
                            </td>
                            <td class="pimage">
                               <div class="productphoto">
                                   <img src="{{$pr->image_url}}">
                               </div>                                
                            </td>
                            <td data-label="PRODUCT NAME">
                                {{ $pr->name }}
                            </td>
                            <td data-label="COST GDS">
                                ${{$pr->price}}
                            </td>
                            <td data-label="PROFIT">
                                {{ $pr->profit }}%
                            </td>
                            <td data-label="RETAIL PRICE">
                                ${{number_format(100*$pr->price/ (100-$pr->profit), 2,'.','')}}
                            </td>
                            <td data-label="SKU">
                                {{$pr->sku}}
                            </td>
                            <td>
                                <button class="btn-mp-view viewbutton vplist" data-id="{{ $pr->id }}" data-view="#product{{ $pr->id }}">View</button>
                            </td>
                            <td>
                                <button class="btn-mp-delete deletebutton" data-myproductid="{{ $pr->id_my_products }}">Delete</button>
                            </td>
                        </tr>
                        <tr class="shoproductrow" id="product{{ $pr->id }}">
                           <td></td>
                            <td colspan="8">
                                <div class="productlisthow">
                                    <div class="productimage">
                                        <img src="{{$pr->image_url}}">
                                    </div>
                                    <div class="productdata">
                                        <h3>{{ $pr->name }}</h3>
                                        <p class="price">Price ${{number_format(100*$pr->price/ (100-$pr->profit), 2,'.','')}}</p>
                                        <p>
                                            Stock: {{ $pr->stock }}
                                        </p>
                                        <p>
                                            Cost: {{ $pr->price }}
                                        </p>
                                        <p>
                                            Profit: {{ $pr->profit }}%
                                        </p>                                        
                                        <p>
                                            Brand: {{ $pr->brand }}
                                        </p>

                                        <div class="pbuttons">
                                            <button class="edit edit-product" data-shopifyid="{{ $pr->id_shopify }}">Edit on Shopify</button>
                                            <button class="delete btn-mp-delete" data-myproductid="{{ $pr->id_my_products }}">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        
                    </tbody>
                </table>
                

            </div>
        </div>
    </div>  


               <!-- pagination -->
                <div class="pagination">
                        {{ $prods->appends(request()->query())->links() }}
                </div>
               <!-- /pagination -->

    
</div>




<script type="text/javascript">
    $(document).ready(function() {

        $('#check-all-mp').click(function() {
            console.log('updload');

            if (!$(this).data('mark')) {
                $('.checkbox').prop('checked', true);
                $(this).data('mark', true)
            } else {
                $('.checkbox').prop('checked', false);
                $(this).data('mark', false)
            }
        });

        $('.edit-product').click(function() {
            window.open('http://{{Auth::user()->shopify_url}}/admin/products/' + $(this).data('shopifyid'), '_blank');
        });

        $('.btn-mp-delete').click(function() {
            if (confirm('Are you sure to delete this product from shopify?')) {
                $.post('{{url("/delete-shopify-product")}}/'+$(this).data('myproductid'), {
                    "_token": "{{ csrf_token() }}",
                }, function(data, status) {
                    window.location.href = '{{url("/my-products")}}';
                });
            }
        });
    });
</script>
@endsection