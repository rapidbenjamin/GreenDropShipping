@extends('layouts.app')

@section('content')



<div class="indexContent" data-page_name="SEARCH PRODUCTS">

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
 
            @if(count($products) == 0)
            <div class="alertan">
               <div class="agrid">
                   <img src="img/infogray.png"
                     srcset="img/infogray@2x.png 2x,
                         img/infogray@3x.png 3x">
                    <p>There are no results for this search, please try again.</p>
               </div>                
            </div>
            @endif



              
               <div class="headerel">
                   <form id="searchFilters" method="get" action="{{url('/search-products')}}">
                       <div class="formgrid">
                           <div class="search">
                                <div class="searchinput">
                                    <img src="img/search.png"
                                     srcset="img/search@2x.png 2x,
                                             img/search@3x.png 3x">
                                    <input type="text" id="txt_search" name="txt_search" value="{{Request()->txt_search}}" placeholder="Search by Product Name" />
                                </div>                               
                               <input class="btn_search" value="Search" type="submit" />
                           </div>
                           <div>
                              <select id="select_category" name="select_category">
                                  <option value="">Select a Category</option>
                                  @foreach ($categories as $cat)
                                  <option value="{{$cat->id}}" {{(Request()->select_category==$cat->id?'selected':'')}}>{{$cat->name}}</option>
                                  @endforeach
                              </select>
                           </div>
	                       <div>
		                       <input type="checkbox" name="only_in_stock" id="only_in_stock" {{ Request()->only_in_stock ? 'checked' : '' }}/>
		                       <label for="only_in_stock">Show only products that are in stock</label>
	                       </div>
                       </div>
                       
                       <div class="subcategories">
                          @if (count($subcategories) > 0)
                          <ul class="subcategories-content">
                              @for($i = 0; $i < count($subcategories); $i++) <li class="subcategory{{($i>9?' hide-aux hide':'')}}"><a href="{{ url('/search-products').'?select_category='.$subcategories[$i]->id}}"><span class="label-txt label-default">{{$subcategories[$i]->name}}</span></a></li>
                                  @if($i==count($subcategories)-1 && $i>10)
                                  <li class="subcategory"><a href="#" class="load-more" data-visibility="false">View more</a></li>
                                  @endif
                                  @endfor
                          </ul>
                          @endif
                       </div>
                   </form>
               </div> 
               
               <div class="productsgrid" id="searchResults">
                  @foreach ($products as $pr)
                   <article class="product id_{{ $pr->id }}">
                       <div class="image">
                          <a href="/search-products/{{ $pr->id }}">
                           <img src="{{ $pr->image_url }}" alt="">
                         </a>
                       </div>
                       <div class="description">
                           <a href="/search-products/{{ $pr->id }}">
                           <h3>{{ $pr->name }}</h3>
                           </a>
                           <p class="price">
                               Price $ {{ $pr->price }} USD
                           </p>
                           <p>
                               Stock: {{ $pr->stock }}
                           </p>
                           <p>
                               Brand: {{ $pr->brand }}
                           </p>
                           <p>
                               SKU: {{ $pr->sku }}
                           </p>

                            <span class="lable-out-stock" style="display:none; color:red;">Out of Stock</span>
                            <button class="addtoimport btn_import_list add" data-id="{{ $pr->id }}" data-stock="{{ $pr->stock }}">Add to Import List</button>
                            <button class="addtoimport btn_import_list edit" data-url="{{url('/import-list')}}" style="display:none">Edit on import list</button>

                       </div>
                   </article>
                   @endforeach
                      
               </div>
               
               
               <!-- pagination -->
                <div class="pagination">
                        {{ $products->appends(request()->query())->links() }}
                </div>
               <!-- /pagination -->
                
                
            </div>
            
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        $("#select_category").change(function() {
            $('#searchFilters').submit();
        });
        $(".edit").click(function() {
            window.location.href = $(this).attr('data-url');
        });
        $(".load-more").click(function() {
            if ($(this).attr('visibility') == "true") {
                $('.hide-aux').hide();
                $(this).attr('visibility', "false");
                $(this).text('View More');
            } else {
                $('.hide-aux').show();
                $(this).attr('visibility', "true");
                $(this).text('View Less');
            }
        });
    });
</script>
@endsection