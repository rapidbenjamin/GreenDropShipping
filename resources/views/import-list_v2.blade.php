@extends('layouts.app')

@section('content')


<div class="indexContent" data-page_name="IMPORT LIST">


    <div class="wrapinsidecontent">

        @if(Auth::user()->plan == 'free')
        <div class="alertan">
            <div class="agrid">
                <img src="img/infogray.png" srcset="img/infogray@2x.png 2x,
                         img/infogray@3x.png 3x">
                <p>You have a free plan. <a href="/plans">Click here to upgrade your plan.</a></p>
            </div>
        </div>
        @endif
        <div class="alertan level2">
            <div class="agrid">
                <p>You can edit product details before adding products to your store.</p>
            </div>
        </div>
        <div class="alertan level2 alert-publish-all" style="display: none;">
            <div class="agrid">
                <p><strong>Publishing in Progress!</strong> We´re currently publishing your products into your store.</p>
            </div>
        </div>
        <div class="alertan level2 alert-publish-single" style="display: none;">
            <div class="agrid">
                <p>The product has been published into your store successfully.</p>
            </div>
        </div>
        <div class="alertan level2 alert-publish-all-ready" style="display: none;">
            <div class="agrid">
                <p><strong>The products have been published into your store successfully!</strong></p>
            </div>
        </div>



        @can("plan_view-my-products")
        <div class="sendtoshopify" style="display: block;">
            <div class="checksend" style="float: left; margin-right: 20px; margin-top: 10px;">
                <input title="Select all products" type="checkbox" id="check-all">
            </div>
            <div style="display: flex;">
                <button class='btn-import-list-send-all'>Send to Shopify <img class="button-icon" src="img/edit.png" alt="Pencil in Square - Edit Icon"></button>
                <button class='btn-import-list-delete-all'>Delete <img class="button-icon" src="img/delete.png" alt="Trash Can - Delete Icon"></button>
            </div>
            <div class="pagesize">
                <select name="PageSize" id="page_size">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        @endcan
        <div id="import-products">
            @foreach ($array_products as $ap)
            <div class="productboxelement import-product" id='product{{$ap->id_import_list}} data-id=' {{$ap->id_import_list}}'>
                <h2>{{$ap->name}}</h2>
                <div class="producttabs">
                    <div class="headertabs">
                        <div class="checkt">
                            <input type="checkbox" id="check-{{$ap->id_import_list}}" class="checkbox" style="display: block;">
                        </div>
                        <div class="tabs">

                            <a href=".tab-1" class="thetab active"> Product </a>
                            <a href=".tab-2" class="thetab"> Description </a>
                            <a href=".tab-3" class="thetab"> Pricing </a>
                            <a href=".tab-4" class="thetab"> Images </a>

                        </div>
                        <div class="buttons import-actions">
                            {{--@can('plan_delete-product-import-list')--}}
                            <button @cannot("plan_view-my-products") data-toggle="modal" data-target="#upgrade-plans-modal" @endcannot class='delete @can("plan_view-my-products") btn-import-list-delete @endcan' id="delete-{{$ap->id_import_list}}" data-id="{{$ap->id_import_list}}">Delete <img class="button-icon" src="img/delete.png" alt="Trash Can - Delete Icon"></button>
                            <button class='delete' id="deleting-{{$ap->id_import_list}}" style="display: none;" data-id="{{$ap->id_import_list}}">Deleting... <img class="button-icon" src="img/delete.png" alt="Trash Can - Delete Icon"></button>
                            {{--@endcan--}}
                            {{--@can('plan_publish-product-import-list')--}}
                            <button @cannot("plan_view-my-products") data-toggle="modal" data-target="#upgrade-plans-modal" @endcannot class='sendto btn-import-list-send btn-import-list-send-{{$ap->id_import_list}} @cannot("plan_view-my-products") verModal @endcannot' data-id="{{$ap->id_import_list}}">Send to Shopify <img class="button-icon" src="img/edit.png" alt="Pencil in Square - Edit Icon"></button>
                            <button class="sendto sending btn-import-list-send3 btn-import-list-send3-{{$ap->id_import_list}}" data-shopifyid="0" style="display:none">Sending...</button>
                            <button class="sendto edit-in-shopify btn-import-list-send2 btn-import-list-send2-{{$ap->id_import_list}}" data-shopifyid="0" style="display:none">Edit in Shopify Store</button>
                            {{--@endcan--}}
                        </div>
                    </div>
                    <div class="contenttabs">
                        <div class="tab-1 wpadding tabcontent active">
                            <div class="productgrid">
                                <div>
                                    <div class="imagewrap">
                                        <img src='{{$ap->image_url}}'>
                                    </div>
                                </div>
                                <div>
                                    <h3>
                                        {{$ap->name}}
                                    </h3>
                                    <div class="editform">
                                        <div class="full">
                                            <label for="">Change product name</label>
                                            <input type="text" id="name{{$ap->id_import_list}}" value='{{$ap->name}}'>
                                        </div>
                                        <div class="full">
                                            <label for="">Collection <span class="simple-tooltip" title="You can assign the product to a Collection in your Shopify store.">?</span></label>
                                            <input type="text" id="collections{{$ap->id_import_list}}">
                                        </div>
                                        <div>
                                            <label for="">Type <span class="simple-tooltip" title="You can give this product a classification that will be saved in the 'Product Type' field in Shopify.">?</span></label>
                                            <input type="text" id="type{{$ap->id_import_list}}">
                                        </div>
                                        <div>
                                            <label for="">Tags <span class="simple-tooltip" title="You can create your own tags separated by commas.">?</span></label>
                                            <input type="text" id="tags{{$ap->id_import_list}}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="tab-2 tabcontent wpadding import-content import-description">
                            <textarea class="texteditor editor" name="" id="description{{$ap->id_import_list}}" cols="30" rows="10">{!! $ap->description !!}</textarea>
                        </div>
                        <div class="tab-3 tabcontent wpaddingtop">
                            <table class="greentable" cellspacing="0">
                                <thead>
                                    <tr>

                                        <th>
                                            SKU <span class="simple-tooltip" title="Do not change this SKU in your Shopify store.">?</span>
                                        </th>
                                        <th>
                                            HEIGHT
                                        </th>
                                        <th>
                                            WIDTH
                                        </th>
                                        <th>
                                            LENGTH
                                        </th>
                                        <th>
                                            WEIGHT
                                        </th>
                                        <th>
                                            COST
                                        </th>
                                        <th>
                                            PROFIT (%) <span class="simple-tooltip" title="Profit excludes shipping charges.">?</span>
                                        </th>
                                        <th>
                                            PRICE
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr class="productdatarow">

                                        <td data-label="SKU" class="skutd">
                                            <input type="text" id="sku{{$ap->id_import_list}}" data-id="{{$ap->id_import_list}}" value="{{$ap->sku}}" disabled="disabled">
                                            <input type="hidden" id="upc{{$ap->id_import_list}}" value="{{$ap->upc}}" />
                                        </td>
                                        <td data-label="HEIGHT">
                                            {{$ap->ship_height}}
                                        </td>
                                        <td data-label="WIDTH">
                                            {{$ap->ship_width}}
                                        </td>
                                        <td data-label="LENGTH">
                                            {{$ap->ship_length}}
                                        </td>
                                        <td data-label="WEIGHT" id="weight{{$ap->id_import_list}}">
                                            {{$ap->weight}}
                                        </td>
                                        <td data-label="COST" class="w100">
                                            <div class="costgrid">
                                                <div>
                                                    $
                                                </div>
                                                <input type="text" id="cost{{$ap->id_import_list}}" data-id="{{$ap->id_import_list}}" value="{{$ap->price}}" disabled="disabled">
                                            </div>

                                        </td>
                                        <td data-label="PROFIT (%) " class="w100">
                                            <span class="simple-tooltip" title="First tooltip">?</span>
                                            <div class="inpupercent">
                                                <input type="text" class="box-profit" id="profit{{$ap->id_import_list}}" data-id="{{$ap->id_import_list}}" value="{{$profit}}">
                                                <div class="percent">
                                                    %
                                                </div>
                                            </div>

                                        </td>
                                        <td data-label="PRICE" class="w100">
                                            <div class="inputprice">
                                                <div class="currency">
                                                    $
                                                </div>
                                                @if($profit < 100) <input type="text" class="box-price" id="price{{$ap->id_import_list}}" data-price="{{$ap->price}}" data-id="{{$ap->id_import_list}}" value="{{number_format((100*$ap->price)/ (100-$profit), 2,'.','')}}">
                                                    @elseif($profit == 100)
                                                    <input type="text" class="box-price" id="price{{$ap->id_import_list}}" data-price="{{$ap->price}}" data-id="{{$ap->id_import_list}}" value="{{number_format((100*$ap->price), 2,'.','')}}">
                                                    @elseif($profit > 100)
                                                    <input type="text" class="box-price" id="price{{$ap->id_import_list}}" data-price="{{$ap->price}}" data-id="{{$ap->id_import_list}}" value="{{number_format((100*$ap->price)/ (100+$profit), 2,'.','')}}">
                                                    @endif
                                            </div>

                                        </td>

                                    </tr>


                                </tbody>
                            </table>
                        </div>
                        <div class="tab-4 wpadding tabcontent">
                            <div class="imagesgrid">
                                @for($i = 0; $i < count($ap->images); $i++)
                                    <div class="selectimage">
                                        <div class="imagewrap">
                                            <img class="img{{$ap->id_import_list}}-{{$i}}" src="{{env('URL_MAGENTO_IMAGES').$ap->images[$i]->file}}">
                                        </div>
                                        <div class="checkim">
                                            <input type="checkbox" class="chk-img{{$ap->id_import_list}}" data-index="{{$i}}" value="" checked="checked">
                                        </div>
                                    </div>
                                    @endfor

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @endforeach
        </div>

    </div>
</div>
<!-- pagination -->
<div class="pagination">
    <ul class="pagination" role="navigation">
        <li class="page-item" id="prev">
            <a class="page-link" rel="prev" aria-label="« Previous">‹</a>
        </li>

        <li class="page-item active" aria-current="page"><span id="page_number" class="page-link">1</span></li>

        <li class="page-item" id="next" aria-disabled="true" aria-label="Next »">
            <span class="page-link" aria-hidden="true">›</span>
        </li>
    </ul>
    <input type="text" id="total_count" value="{{$total_count}}" hidden>
</div>
<!-- /pagination -->


<script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>


<script type="text/javascript">
    $(document).ready(function() {
        $('#total_count').text("{{$total_count}}");
        $('.verModal').click(function(e) {
            e.preventDefault();
            $('#upgrade-plans-modal').modal('show');
            //alert("Upgrade your plan to perform this action.");
        });


        $(".editor").each(function(index, ele) {
            CKEDITOR.replace($(ele).attr('id'), {});
        });


        $(".edit-in-shopify").click(function() {
            window.location.href = $(this).attr('data-url');
        });
        $('.btn-import-list-send').click(function() {

            let productId = $(this).data('id');
            let images = [];
            $("input.chk-img" + productId + ":checked").each(function(index, ele) {
                images.push($('.img' + productId + '-' + $(ele).attr('data-index')).attr('src'));
            });

            $('.btn-import-list-send-' + productId).hide();
            $('.btn-import-list-send3-' + productId).show();

            let btn = $(this);
            btn.attr('disabled', true);

            let product = {
                id: productId,
                name: $('#name' + productId).val(),
                weight: $('#weight' + productId).text().trim(),
                price: $('#price' + productId).val(),
                cost: $('#cost' + productId).val(),
                description: CKEDITOR.instances['description' + productId].getData(),
                product_type: $('#type' + productId).val(),
                tags: $('#tags' + productId).val(),
                collections: $('#collections' + productId).val(),
                sku: $('#sku' + productId).val(),
                upc: $('#upc' + productId).val(),
                profit: $('#profit' + productId).val(),
                images: images
            };

            $.post('{{url("/publish-product")}}', {
                "_token": "{{ csrf_token() }}",
                product: product
            }, function(data, status) {

                btn.attr('disabled', false);
                $('.alert-publish-single').show();
                $('.btn-import-list-send3-' + productId).hide();
                $('.btn-import-list-send2-' + productId).show();
                $('.btn-import-list-send2-' + productId).attr('data-shopifyid', data.id_shopify);
            }).fail(function(data) {
                if (data.status == 403)
                    $('#upgrade-plans-modal').modal('show')
            });
        });
        $('.btn-import-list-send2').off('click');
        $('.btn-import-list-send2').click(function(e) {
            e.preventDefault();
            window.open('http://{{Auth::user()->shopify_url}}/admin/products/', '_blank');
            return false;
        });

        $('#check-all').click(function() {
            if (!$(this).data('mark')) {
                $('.checkbox').prop('checked', true);
                $(this).data('mark', true)
            } else {
                $('.checkbox').prop('checked', false);
                $(this).data('mark', false)
            }
        });


        $('.btn-import-list-send-all').click(function() {

            //Get all checked products
            let products = [];

            let nProd = $("input.checkbox:checked").length;

            if (nProd > 0) {
                $(this).attr('disabled', true);
                $('.alert-publish-all').show();
                $('.alert-publish-all-ready').hide();
            }

            $("input.checkbox:checked").each(function(index, ele) {
                let productId = $(ele).attr('id').split('-')[1];
                let images = [];
                if ($('.btn-import-list-send-' + productId).is(":visible")) {

                    $('.btn-import-list-send-' + productId).hide();
                    $('.btn-import-list-send3-' + productId).show();
                    $('.btn-import-list-send-' + productId).attr('disabled', true);

                    // data array of all checked products
                    $("input.chk-img" + productId + ":checked").each(function(index, ele) {
                        images.push($('.img' + productId + '-' + $(ele).attr('data-index')).attr('src'));
                    });

                    products.push({
                        id: productId,
                        name: $('#name' + productId).val(),
                        weight: $('#weight' + productId).text().trim(),
                        price: $('#price' + productId).val(),
                        cost: $('#cost' + productId).val(),
                        description: CKEDITOR.instances['description' + productId].getData(),
                        product_type: $('#type' + productId).val(),
                        tags: $('#tags' + productId).val(),
                        collections: $('#collections' + productId).val(),
                        sku: $('#sku' + productId).val(),
                        upc: $('#upc' + productId).val(),
                        profit: $('#profit' + productId).val(),
                        images: images
                    });
                }
            });

            $(this).attr('disabled', false);


            let btn = $(this);
            if (products.length == 0) {
                alert('At least one checkbox must be selected');
                return;
            } else {
                $.post('{{url("/publish-all-products")}}', {
                    "_token": "{{ csrf_token() }}",
                    products: products
                }, function(data, status) {

                }).fail(function(data) {
                    if (data.status == 403)
                        $('#upgrade-plans-modal').modal('show')
                });

            }

        }); //Close send all function


        var usr_id = "{{Auth::user() ? Auth::user()->id : 0}}";

        function publishProductsAjax() {
            let product_ids = [];
            $("input.checkbox:checked").each(function(index, ele) {
                product_ids.push($(ele).attr('id').split('-')[1]);
            });
            if (usr_id && product_ids.length) {
                $.ajax({
                        type: 'POST',
                        url: '/check-publish-products',
                        data: {
                            user_id: usr_id,
                            product_ids: product_ids,
                            "_token": "{{ csrf_token() }}",
                        },
                    })
                    .then(res => {
                        res.id_shopify.forEach(productId => {
                            $('.btn-import-list-send3-' + productId).hide();
                            $('.btn-import-list-send2-' + productId).show();
                        });
                    });
            }
        }
        publishProductsAjax();
        setInterval(publishProductsAjax, 15000);

    }); //Close document ready
</script>
@endsection
