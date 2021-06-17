@extends('layouts.app')

@section('content')
<div class="indexContent productDetailContent" data-page_name="PRODUCT DETAILS">
    <div class="maincontent">


        <p class="back" data-url='{{ url()->previous() }}'>
            <a href="{{ url()->previous() }}">
                < BACK
            </a>
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

            <div class="productbox">
                @if($action == 'search-products')
                <div class="comments">
                    <p>Note: To edit the product info you must add it to an import list first.</p>
                </div>
                @endif
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
                        <p class="price">$ {{ $product->price }} USD</p>
                        <p>
                            Stock: {{ $product->stock }}
                        </p>
                        <p>
                            Brand: {{ $product->brand }}
                        </p>
                        <p>
                            SKU: {{ $product->sku }}
                        </p>

                    </div>
                    <div class="description">
                        <h3>Description</h3>

                        <p>
                            {!! $product->description !!}
                        </p>
                    </div>
                    @if($action == 'search-products')
                    <button class="addtoimport btn_import_list_detail add-to-import-list-{{ $product->id }}" data-id="{{ $product->id }}">Add to Import List</button>
                    <button class="addtoimport edit-on-import-list-{{ $product->id }}" data-id="{{ $product->id }}" style="display:none">Added on Import List</button>
                    @elseif($action == 'added')
                    <button class="addtoimport edit-on-import-list-{{ $product->id }}" data-id="{{ $product->id }}">Added on Import List</button>
                    @elseif($action == 'my-product')
                    <a href="http://{{Auth::user()->shopify_url}}/admin/products/{{$product->id_shopify}}" target="_blank"><button class="addtoimport btn_import_list_detail edit-on-shopify-{{ $product->id_shopify }}">Edit on Shopify<img class="button-icon" src="/img/edit.png" alt="Pencil in Square - Edit Icon"></button></a>
                    @endif
                </div>

            </div>

        </div>

    </div>
</div>

</div>

<script type="text/javascript">
    $(document).ready(function() {

        $(".edit-on-import-list-{{$product->id}}").click(function() {
            window.location.href = "{{url('/import-list')}}";
        });
    });
</script>
@endsection
