@extends('layouts.app')

@section('content')
<div class="indexContent productDetailContent" data-page_name="PRODUCT DETAILS">
    <div class="maincontent">
        <p class="back" data-url='{{ url()->previous() }}'>
            <a href="{{ url()->previous() }}">< BACK </a>
        </p>
        <div class="wrapinsidecontent">
            @if(Auth::user()->plan == 'free')
            <div class="alertan">
                <div class="agrid">
                    <img src="{{ asset('img/infogray.png')}}" srcset="{{ asset('img/infogray@2x.png 2x')}},
                             {{ asset('img/infogray@3x.png 3x')}}">
                    <p>You have a free plan. <a href="/plans">Click here to upgrade your plan.</a></p>
                </div>
            </div>
            @endif
            @if($action == 'search-products')
            <div class="comments">
                <p id="before_add">Note: To edit the product info you must add it to an import list first.</p>
                <p id="added" style="display: none;">Successfully added on import list.</p>
            </div>
            @endif
            <div class="productbox">
                <div>
                    @php $k=0; @endphp
                    <ul class="thumbnails">
                        @foreach ($product->mini_images as $image)
                        @php $k++; @endphp
                        <li>
                            <a href="{{$image}}" data-fancybox="gallery">
                                <img class="imgThumb" src="{{$image}}" data-img="{{$image}}">
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <div class="productmainimage">
                        <a href="{{$product->image_url}}" class="maingreenproducimagelink detailImage" data-fancybox="gallery">
                            <img src="{{$product->image_url}}" class="maingreenproducimage" data-img="{{$product->image_url}}">
                        </a>
                    </div>
                </div>
                <div>
                    <div class="productinfo">
                        <h2>{{ $product->name }}</h2>
                        <p class="price">Price US$ {{ $product->price }}</p>
                        <p>Stock: {{ $product->stock }}</p>
                        <p>Brand: {{ $product->brand }}</p>
                        <p>SKU: {{ $product->sku }}</p>
                        <p>Storage: {{ $product->stock_info == 'null' ? '' : $product->stock_info }}</p>
                    </div>
                    <div class="description">
                        <h3>Description</h3>
                        <p>{!! $product->description !!}</p>
                    </div>
                    @if($action == 'search-products')
                    <button class="addtoimport btn_import_list_detail add-to-import-list-{{ $product->sku }}" data-sku="{{ $product->sku }}">Add to Import List</button>
                    <a href="{{url('/import-list')}}"><button class="addtoimport edit-on-import-list-{{ $product->sku }}" style="display:none">Added on Import List</button></a>
                    @elseif($action == 'added')
                    <a href="{{url('/import-list')}}"><button class="addtoimport edit-on-import-list-{{ $product->sku }}">Added on Import List</button></a>
                    @elseif($action == 'my-product')
                    <a href="http://{{Auth::user()->shopify_url}}/admin/products/{{$product->id_shopify}}" target="_blank"><button class="addtoimport btn_import_list_detail edit-on-shopify-{{ $product->id_shopify }}">Edit in Shopify<img class="button-icon" src="/img/edit.png" alt="Pencil in Square - Edit Icon"></button></a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.add-to-import-list-{{$product->sku}}').click(function() {
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
