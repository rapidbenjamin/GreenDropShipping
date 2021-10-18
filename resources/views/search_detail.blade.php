@extends('layouts.app')

@section('content')
<div class="indexContent productDetailContent" data-page_name="PRODUCT DETAILS">
    <div class="maincontent">
        <div class="wrapinsidecontent">
            @if(Auth::user()->plan == 'free')
            <div class="alertan">
                <div class="agrid">
                    <img src="{{ asset('img/infogray.png')}}" srcset="{{ asset('img/infogray@2x.png 2x')}},{{ asset('img/infogray@3x.png 3x')}}">
                    <p>You have a free plan. <a href="/plans">Click here to upgrade your plan.</a></p>
                </div>
            </div>
            @endif
            @if ($product == null)
            <div class="comments">
                <p id="before_add" class="text-center text-info h4 bg-white">This product doesn't exist in our app.</p>
            </div>
            @else
            @if($action == 'search-products')
            <div class="comments">
                <p id="before_add" class="text-center text-info h4 bg-white">Note: To edit the product info you must add it to the import list first.</p>
                <p id="added" style="display: none;" class="text-center text-success h4 bg-white">The product has been successfully added to the import list</p>
            </div>
            @endif
            <div class="productbox">
                <div>
                    @php $k=0; @endphp
                    <ul class="thumbnails">
                        @foreach ($product->mini_images as $image)
                        @php $k++; @endphp
                        <li>
                            <a href="{{$image['gallery']}}" data-fancybox="gallery">
                                <img class="imgThumb" src="{{$image['main']}}" data-img="{{$image['main']}}">
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <div class="productmainimage">
                        <a href="{{$product->image_url['gallery']}}" class="maingreenproducimagelink detailImage" data-fancybox="gallery">
                            <img src="{{$product->image_url['main']}}" class="maingreenproducimage" data-img="{{$product->image_url['main']}}">
                        </a>
                    </div>
                </div>
                <div>
                    <div class="productinfo">
                        <h2>{{ $product->name }}</h2>
                        <p class="price"><strong>Price:</strong> US ${{ $product->price }}</p>
                        <p><strong>Stock:</strong> <span class="{{ $product->stock ? '' : 'text-danger' }}">{{ $product->stock ? $product->stock : 'OUT OF STOCK' }}<span></p>
                        <p><strong>Brand:</strong> {{ $product->brand }}</p>
                        <p><strong>SKU:</strong> {{ $product->sku }}</p>
                        <p><strong>Storage:</strong> {{ $product->stock_info == 'null' ? '' : $product->stock_info }}</p>
                        <p class="lead_time"><strong>Current lead time:</strong> {{ $product->lead_time == 'null' ? '' : $product->lead_time.' days' }} <span class="simple-tooltip" title="The number of days it takes to ship out an order from the date it was received.">?</span></p>
                    </div>
                    <div class="description">
                        <h3>Description</h3>
                        <p>{!! $product->description !!}</p>
                    </div>
                    @if($action == 'search-product')
                    <button class="cel-icon-plus addtoimport btn_import_list_detail add-to-import-list-{{ $product->sku }}" data-sku="{{ $product->sku }}">Add to Import List</button>
                    <a href="{{url('/import-list')}}"><button class="addtoimport added edit-on-import-list-{{ $product->sku }}" style="display:none">Edit in Import List</button></a>
                    @elseif($action == 'added')
                    <a href="{{url('/import-list')}}"><button class="addtoimport added edit-on-import-list-{{ $product->sku }}">Edit in Import List</button></a>
                    @elseif($action == 'my-product')
                    <a href="https://{{Auth::user()->shopify_url}}/admin/products/{{$product->id_shopify}}" target="_blank"><button class="addtoimport added btn_import_list_detail edit-on-shopify-{{ $product->id_shopify }}">Edit in Store</button></a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.add-to-import-list-{{$product ? $product->sku : 0}}').click(function() {
            var parameters = {
                action: 'add_import_list',
                sku: $(this).data('sku')
            }
            let sku = $(this).data('sku');
            $.getJSON(ajax_link, parameters, function(data) {
                $('.add-to-import-list-' + sku).hide();
                $('.edit-on-import-list-' + sku).show();
                $('#before_add').hide();
                $('#added').show();
            })
        })
    });
</script>
@endsection
