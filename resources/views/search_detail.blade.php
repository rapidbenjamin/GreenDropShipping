@extends('layouts.app')

@section('content')
<div class="indexContent productDetailContent" data-page_name="PRODUCT DETAILS">
        <div class="maincontent">


            <p class="back" data-url='{{ url()->previous() }}'><a href="{{ url()->previous() }}">< BACK</a></p>
            
            <div class="wrapinsidecontent">
 
                 @if(Auth::user()->plan == 'free')
                <div class="alertan">
                   <div class="agrid">
                       <img src="{{ asset('img/infogray.png')}}"
                         srcset="{{ asset('img/infogray@2x.png 2x')}},
                             {{ asset('img/infogray@3x.png 3x')}}">
                        <p>You have a free plan. <a href="/plans">Click here to upgrade your plan.</a></p>
                   </div>                
                </div>
                @endif
              
                <div class="productbox">
                    <div class="comments">
                        <p>Note: To edit the product info you must add it to an import list first.</p>
                    </div>
                    <div>
                        @php $k=0; $main_image = ""; @endphp
                       <ul class="thumbnails">
                         @foreach ($product->mini_images as $image)
                            @php if($k == 0)$main_image = $image; @endphp
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
                          <a href="{{$main_image}}" class="maingreenproducimagelink detailImage" data-fancybox="gallery">
                              <img src="{{$main_image}}" class="maingreenproducimage" >
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
                        
            <button class="addtoimport btn_import_list_detail add-to-import-list-{{ $product->id }}" data-id="{{ $product->id }}">Add to Import List</button>
            <button class="addtoimport editimportlist btn_import_list_detail edit-on-import-list-{{ $product->id }}" data-id="{{ $product->id }}" style="display:none">Edit on import list</button>
                        
                    </div>
                    
                </div>
                
            </div>
            
        </div>
    </div>  
    
</div>

<script type="text/javascript">
    $(document).ready(function(){
        
        $(".edit-on-import-list-{{$product->id}}").click(function(){
            window.location.href = "{{url('/import-list')}}";
        });
    });
</script>
@endsection