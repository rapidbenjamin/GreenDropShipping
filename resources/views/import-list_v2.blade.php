@extends('layouts.app')

@section('content')


<div class="indexContent" data-page_name="IMPORT LIST">


    <div class="wrapinsidecontent">

        @if(Auth::user()->plan == 'free')
        <div class="alertan">
            <div class="agrid">
                <img src="img/infogray.png" srcset="img/infogray@2x.png 2x,img/infogray@3x.png 3x">
                <p>You have a free plan. <a href="/plans">Click here to upgrade your plan.</a></p>
            </div>
        </div>
        @endif
        @if(count($array_products) > 0)
        <div class="alertan level2">
            <div class="agrid">
                <p>You can edit product details before adding products to your store.</p>
            </div>
            <i class="fa fa-close text-secondary" aria-hidden="true"></i>
        </div>
        @else
        <div class="alertan level2">
            <div class="agrid">
                <p>When products are imported you can publish them to your Shopify store. <a href="/search-products">Click here to import products.</a></p>
            </div>
            <i class="fa fa-close text-secondary" aria-hidden="true"></i>
        </div>
        @endif
        <div class="alertan level2 alert-publish-all" style="display: none;">
            <div class="agrid">
                <p><strong>Publishing in Progress!</strong> We're currently publishing your products into your store.</p>
            </div>
        </div>
        <div class="alertan level2 alert-publish-single" style="display: none;">
            <div class="agrid">
                <span>The product has been published to your store successfully.</span>
            </div>
        </div>
        <div class="alertan level2 alert-publish-all-ready" style="display: none;">
            <div class="agrid">
                <span><strong>The products have been published to your store successfully!</strong></span>
            </div>
        </div>



        @can("plan_view-my-products")
        @if(count($array_products) > 0)
        
        <div class="product-menu" id="product-top-menu">
            <div class="sendtoshopify">
                <div class="checksend" style="margin-top: 8px;">
                    <input title="Select all products" type="checkbox" id="check-all">
                </div>
                <div class="btn-import-actions">
                    <button class="btn-import-list-delete-all redbutton mx-1 my-1" data-toggle="modal" data-target="#delete-product-modal">Delete <img class="button-icon" src="img/delete.png" alt="Trash Can - Delete Icon"></button>
                    <button class="btn-import-list-send-all greenbutton mx-1 my-1" id="btn-import-list-send-all">Send to Shopify <img class="button-icon" src="img/edit.png" alt="Pencil in Square - Edit Icon"></button>
                    <button class="btn-import-list-send-all greenbutton mx-1 my-1" id="btn-import-list-sending" style="display: none;">Sending...<img class="button-icon" src="img/edit.png" alt="Pencil in Square - Edit Icon"></button>
                </div>
            </div>
            <div class="pagesize">
                <span>Show</span>
                <select name="PageSize" id="page_size">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        @endif
        @endcan
        <div id="import-products">
            @foreach ($array_products as $ap)
            <div class="productboxelement import-product" id="product{{$ap->id_import_list}}" data-id="{{$ap->id_import_list}}">
                <h2>{{$ap->name}}</h2>
                <div class="producttabs">
                    <div class="headertabs">
                        <div class="checkt">
                            <input type="checkbox" id="check-{{$ap->id_import_list}}" class="checkbox" style="display: block; width: 20px; height: 20px;">
                        </div>
                        <div class="tabs">

                            <a href=".tab-1" class="thetab active"> Product </a>
                            <a href=".tab-2" class="thetab"> Description </a>
                            <a href=".tab-3" class="thetab"> Pricing </a>
                            <a href=".tab-4" class="thetab"> Images </a>

                        </div>
                        <div class="buttons import-actions">
                            {{--@can('plan_delete-product-import-list')--}}
                            <button @cannot("plan_view-my-products") data-toggle="modal" data-target="#upgrade-plans-modal" @endcannot @can("plan_view-my-products") data-toggle="modal" data-target="#delete-product-modal" @endcan class='delete redbutton @can("plan_view-my-products") btn-import-list-delete @endcan' id="delete-{{$ap->id_import_list}}" data-id="{{$ap->id_import_list}}" data-name="{{ $ap->name }}" data-sku="{{ $ap->sku }}" data-img="{{$ap->images != null ? env('URL_MAGENTO_IMAGES').'/dc09e1c71e492175f875827bcbf6a37c'.$ap->images[0]->file :  env('URL_MAGENTO_IMAGES').'/dc09e1c71e492175f875827bcbf6a37cnoselection'}}">
                                Delete <img class="button-icon" src="img/delete.png" alt="Trash Can - Delete Icon">
                            </button>
                            <button class='delete redbutton' id="deleting-{{$ap->id_import_list}}" style="display: none;" data-id="{{$ap->id_import_list}}">Deleting... <img class="button-icon" src="img/delete.png" alt="Trash Can - Delete Icon"></button>
                            {{--@endcan--}}
                            {{--@can('plan_publish-product-import-list')--}}
                            <button @cannot("plan_view-my-products") data-toggle="modal" data-target="#upgrade-plans-modal" @endcannot class='sendto greenbutton @can("plan_view-my-products") btn-import-list-send @endcan btn-import-list-send-{{$ap->id_import_list}}' data-id="{{$ap->id_import_list}}">Send to Shopify <img class="button-icon" src="img/edit.png" alt="Pencil in Square - Edit Icon"></button>
                            <button class="sendto greenbutton sending btn-import-list-send3 btn-import-list-send3-{{$ap->id_import_list}}" data-shopifyid="0" style="display:none">Sending...</button>
                            <button class="sendto greenbutton edit-in-shopify btn-import-list-send2 btn-import-list-send2-{{$ap->id_import_list}}" data-shopifyid="0" style="display:none">Edit in Shopify Store</button>
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
                                            <input type="text" list="collection{{$ap->id_import_list}}" id="collections{{$ap->id_import_list}}" class="collection" data-id="{{$ap->id_import_list}}">
                                            <datalist id="collection{{$ap->id_import_list}}">
                                                <div id="collection_data"></div>
                                            </datalist>
                                            <span id="collection_error{{$ap->id_import_list}}" style="color:red; display:none;">You can only add a product to one collection.</span>
                                        </div>
                                        <div>
                                            <label for="">Type <span class="simple-tooltip" title="You can give this product a classification that will be saved in the 'Product Type' field in Shopify.">?</span></label>
                                            <input type="text" list="type{{$ap->id_import_list}}" id="types{{$ap->id_import_list}}" class="type" data-id="{{$ap->id_import_list}}">
                                            <datalist id="type{{$ap->id_import_list}}">
                                                <div id="type_data"></div>
                                            </datalist>
                                            <span id="type_error{{$ap->id_import_list}}" style="color:red; display:none;">Every product has a single product type.</span>
                                        </div>
                                        <div>
                                            <label for="">Tags <span class="simple-tooltip" title="You can create your own tags separated by commas.">?</span></label>
                                            <input type="text" list="tag{{$ap->id_import_list}}" id="tags{{$ap->id_import_list}}" class="tag" data-id="{{$ap->id_import_list}}">
                                            <datalist id="tag{{$ap->id_import_list}}">
                                                <div id="tag_data"></div>
                                            </datalist>
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
                                                US$<span id="cost{{$ap->id_import_list}}" data-id="{{$ap->id_import_list}}">{{$ap->price}}</span>
                                            </div>
                                        </td>
                                        <td data-label="PROFIT (%) " class="w100">
                                            <span class="simple-tooltip" title="First tooltip">?</span>
                                            <div class="inpupercent">
                                                <input type="text" style="width: 50%; text-align:center;" class="box-profit" id="profit{{$ap->id_import_list}}" data-id="{{$ap->id_import_list}}" value="{{$profit}}">%
                                            </div>
                                        </td>
                                        <td data-label="PRICE" class="w100">
                                            <div class="inputprice">
                                                US$<input type="text" style="width: 50%; text-align:center;" class="box-price" id="price{{$ap->id_import_list}}" data-price="{{$ap->price}}" data-id="{{$ap->id_import_list}}" value="{{number_format($ap->price * (100 + $profit) / 100, 2,'.','')}}">
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
                                        <img class="img{{$ap->id_import_list}}-{{$i}}" src="{{env('URL_MAGENTO_IMAGES').'/3a98496dd7cb0c8b28c4c254a98f915a'.$ap->images[$i]->file}}">
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
<div class="back-to-top" style="display:none">
    <img src=" {{ asset('/img/back_to_top.png') }}" alt="Back to Top">
    <span style="text-align: center;" class="h5">Back<br>to Top</span>
</div>

<div id="pagination"></div>
<input type="text" id="total_count" value="{{$total_count}}" hidden>
<input type="text" id="product_id" value="" hidden>
<input type="text" id="modal_type" value="" hidden>

<script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#total_count').text("{{$total_count}}");

        $(".editor").each(function(index, ele) {
            CKEDITOR.replace($(ele).attr('id'), {});
        });

        $('#check-all').click(function() {
            if ($('#check-all').is(':checked')) {
                $('.checkbox').prop('checked', true);
            } else {
                $('.checkbox').prop('checked', false);
            }
        });

        $('#btn-import-list-send-all').click(function() {

            //Get all checked products
            let products = [];
            let product_ids = [];
            $("input.checkbox:checked").each(function(index, ele) {
                let productId = $(ele).attr('id').split('-')[1];
                let images = [];
                if ($(`.btn-import-list-send-${productId}`).is(":visible")) {
                    var permission = permission_collection_type(productId);
                    if (permission) {
                        $(`.btn-import-list-send-${productId}`).hide();
                        $(`.btn-import-list-send3-${productId}`).show();
                        $(ele).prop('disabled', true);
                        $(ele).prop('checked', false);
                        $(ele).removeClass();
                        $(`#delete-${productId}`).hide();

                        // data array of all checked products
                        $("input.chk-img" + productId + ":checked").each(function(index, ele) {
                            images.push($('.img' + productId + '-' + $(ele).attr('data-index')).attr('src'));
                            $(ele).prop('disabled', true);
                            $(ele).prop('checked', false);
                        });
                        product_ids.push(productId);
                        window.localStorage.removeItem('send_product_ids');
                        window.localStorage.setItem('send_product_ids', JSON.stringify(product_ids));
                        products.push({
                            id: productId,
                            name: $(`#name${productId}`).val(),
                            weight: $(`#weight${productId}`).text().trim(),
                            price: $(`#price${productId}`).val(),
                            cost: $(`#cost${productId}`).text(),
                            description: CKEDITOR.instances[`description${productId}`].getData(),
                            product_type: $(`#types${productId}`).val(),
                            tags: $(`#tags${productId}`).val().trim(),
                            collections: $(`#collections${productId}`).val().trim(),
                            sku: $(`#sku${productId}`).val().trim(),
                            upc: $(`#upc${productId}`).val(),
                            profit: $(`#profit${productId}`).val(),
                            images: images
                        });
                    }
                }
            });

            if (products.length == 0) {
                $(this).attr('data-toggle', 'modal');
                $(this).attr('data-target', '#delete-product-modal');
                $('#modal-body').html('<h5>At least one checkbox must be selected</h5>');
                $('#modal-footer').hide();
            } else {
                $(this).attr('data-toggle', '');
                $(this).hide();
                $('.alert-publish-all').show();
                $('.btn-import-list-delete-all').prop('disabled', true);
                $('#btn-import-list-sending').show();
                $('#check-all').prop('disabled', true);
                $('#check-all').prop('checked', false);
                $.post('{{url("/publish-all-products")}}', {
                    "_token": "{{ csrf_token() }}",
                    products: JSON.stringify(products)
                }, function(data, status) {
                }).fail(function(data) {
                });
            }

        }); //Close send all function

        $('.btn-import-list-delete-all').click(function() {
            var product_ids = [];
            $('input.checkbox:checked').each(function(index, ele) {
                product_ids.push($(ele).attr('id').split('-')[1]);
            });
            if (product_ids.length) {
                $('#modal-body').html(`<h5>Are you sure to delete checked products from Import List?</h5>`);
                $('#product_id').val('delete-products');
                $('#modal-footer').show();
            } else {
                $('#modal-body').html(`<h5>At least one checkbox must be selected</h5>`);
                $('#modal-footer').hide();
                $('#product_id').val('cancel');
            }
        })

        $('#confirm').click(function() {
            if ($('#product_id').val() != 'cancel') {
                if ($('#product_id').val() == 'delete-products') {
                    deleteProducts();
                } else {
                    deleteProduct($('#product_id').val());
                }
            }
            $('#delete-product-modal').removeClass('show');
            $('#delete-product-modal').css('display', 'none');
            $('.modal-backdrop.fade.show').remove();
        });

        $('#cancel').click(function() {
            if ($('#modal_type').val().split('-')[0] == '#collections') {
                $(`${$('#modal_type').val().split('-').join('')}`).val('');
                $('#modal_type').val('');
            }
            $('#delete-product-modal').removeClass('show');
            $('#delete-product-modal').css('display', 'none');
            $('.modal-backdrop.fade.show').remove();
        });

        $('#close').click(function() {
            $('#delete-product-modal').removeClass('show');
            $('#delete-product-modal').css('display', 'none');
            $('.modal-backdrop.fade.show').remove();
        });

        $('#delete-product-modal .modal-content').blur(function() {
            $('#delete-product-modal').removeClass('show');
            $('#delete-product-modal').css('display', 'none');
            $('.modal-backdrop.fade.show').remove();
        });

        function deleteProduct(id) {
            var parameters = {
                action: 'delete_import_list',
                id_import_list: [id]
            }
            $(`#delete-${id}`).hide();
            $(`#deleting-${id}`).show();
            $(`.btn-import-list-send-${id}`).hide();
            $(`#check-${id}`).prop('checked', false);
            $(`#check-${id}`).prop('disabled', true);
            $.getJSON(ajax_link, parameters, function(data) {
                $(`#product${id}`).remove();
            }).fail(function(data) {
            })
        }

        function deleteProducts() {
            var product_ids = [];
            $('input.checkbox:checked').each(function(index, ele) {
                let product_id = $(ele).attr('id').split('-')[1];
                product_ids.push(product_id);
            });
            var parameters = {
                action: 'delete_import_list',
                id_import_list: product_ids
            }
            product_ids.forEach(product_id => {
                $(`#delete-${product_id}`).hide();
                $(`#deleting-${product_id}`).show();
                $(`.btn-import-list-send-${product_id}`).hide();
                $(`#check-${product_id}`).prop('disabled', true);
                $(`#check-${product_id}`).prop('checked', false);
            });
            $('#check-all').prop('checked', false);
            $('#check-all').prop('disabled', true);
            $.getJSON(ajax_link, parameters, function(data) {
                $('#check-all').prop('disabled', false);
                product_ids.forEach(product_id => {
                    $(`#product${product_id}`).remove();
                });
            }).fail(function(data) {
                console.log('error1', data.status)
            })
        }

        function publishProductsAjax() {
            let product_ids = JSON.parse(window.localStorage.getItem('send_product_ids'));
            if (product_ids) {
                if (product_ids.length) {
                    $.post('/check-publish-products', {
                        product_ids: product_ids,
                        "_token": "{{ csrf_token() }}",
                    }).then(data => {
                        if (data.result) {
                            window.localStorage.removeItem('send_product_ids');
                            $('.btn-import-list-send-all').prop('disabled', false);
                            $('.btn-import-list-delete-all').prop('disabled', false);
                            $('#btn-import-list-sending').hide();
                            $('#btn-import-list-send-all').show();
                            $('.alert-publish-all').hide();
                            $('.alert-publish-all-ready').show();
                            setTimeout(() => {
                                $('.alert-publish-all-ready').hide();
                            }, 1500);
                            for (const key in data.id_shopify) {
                                $(`.btn-import-list-send-${data.id_shopify[key]}`).hide();
                                $(`.btn-import-list-send2-${data.id_shopify[key]}`).show();
                                $(`.btn-import-list-send2-${data.id_shopify[key]}`).attr('data-shopifyid', key);
                                $(`.btn-import-list-send3-${data.id_shopify[key]}`).hide();
                                $(`#delete-${data.id_shopify[key]}`).hide();
                                $(`#check-${data.id_shopify[key]}`).prop('checked', false);
                                $('#check-all').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        }
        publishProductsAjax();
        setInterval(publishProductsAjax, 15000);

    }); //Close document ready

    function permission_collection_type(id) {
        var collection_flag = true;
        var type_flag = true;
        if ($(`#collections${id}`).val().indexOf(',') > -1) {
            $(`#collection_error${id}`).show();
            collection_flag = false;
        } else {
            $(`#collection_error${id}`).hide();
            collection_flag = true;
        }
        if ($(`#types${id}`).val().indexOf(',') > -1) {
            $(`#type_error${id}`).show();
            type_flag = false;
        } else {
            $(`#type_error${id}`).hide();
            type_flag = true;
        }
        if (collection_flag && type_flag) return true;
        else return false;
    }

    $('#import-products').on('click', '.producttabs .thetab', function(e) {
        e.preventDefault();
        $(this).parents(".producttabs").find(".thetab").removeClass("active");
        $(this).addClass("active");
        var thetabid = $(this).attr("href");
        $(this).parents(".producttabs").find(".tabcontent").removeClass("active");
        $(this).parents(".producttabs").find(thetabid).addClass("active");
    });

    $('#import-products').on('click', '.btn-import-list-send', function() {
        let productId = $(this).data('id');
        var permission = permission_collection_type(productId);
        if (permission) {
            let images = [];
            $("input.chk-img" + productId + ":checked").each(function(index, ele) {
                images.push($('.img' + productId + '-' + $(ele).attr('data-index')).attr('src'));
                $(ele).prop('disabled', true);
                $(ele).prop('checked', false);
            });

            $(`.btn-import-list-send-${productId}`).hide();
            $(`.btn-import-list-send3-${productId}`).show();
            $(`#delete-${productId}`).hide();
            $(`#check-${productId}`).prop('disabled', true);
            $(`#check-${productId}`).prop('checked', false);
            $(`#check-${productId}`).removeClass();

            $(this).prop('disabled', true);

            let product = {
                id: productId,
                name: $(`#name${productId}`).val(),
                weight: $(`#weight${productId}`).text().trim(),
                price: $(`#price${productId}`).val(),
                cost: $(`#cost${productId}`).text(),
                description: CKEDITOR.instances[`description${productId}`].getData(),
                product_type: $(`#types${productId}`).val().trim(),
                tags: $(`#tags${productId}`).val().trim(),
                collections: $(`#collections${productId}`).val().trim(),
                sku: $(`#sku${productId}`).val(),
                upc: $(`#upc${productId}`).val(),
                profit: $(`#profit${productId}`).val(),
                images: images
            };
            $.post('{{url("/publish-product")}}', {
                "_token": "{{ csrf_token() }}",
                product: product
            }, function(data, status) {
                $('.alert-publish-single').show();
                setTimeout(() => {
                    $('.alert-publish-single').hide();
                }, 1500);
                $(`.btn-import-list-send3-${productId}`).hide();
                $(`.btn-import-list-send2-${productId}`).show();
                $(`.btn-import-list-send2-${productId}`).attr('data-shopifyid', data.id_shopify);
                $(`#check-${productId}`).prop('checked', false);
            }).fail(function(data) {
                if (data.status == 403)
                    $('#upgrade-plans-modal').modal('show')
            });
        }
    });

    $('#import-products').on('click', '.btn-import-list-delete', function() {
        $('#modal-body').html(`<div style="display:flex;">
                <img style="width:75px; height:75px;" src="${$(this).data('img')}"/>
                <div>
                    <h5>${$(this).data('name')}</h5>
                    <h5 style="text-align:center" class="mt-3">${$(this).data('sku')}</h5>
                </div>
            </div>
            <h5 class="mt-3">This product will be removed from Import List. Do you really want to delete it?</h5>`);
        $('#product_id').val($(this).data('id'));
        $('#modal-footer').show();
    });

    $('#import-products').on('change', '.box-profit', function() {
        var id_product = $(this).data('id');
        var cost = $(`#cost${id_product}`).text();
        var profit = $(this).val();
        if (profit > 0) {
            var value = parseFloat((cost * (100 + profit * 1)) / 100).toFixed(2);
        } else {
            value = cost;
        }

        $(`#price${id_product}`).val(value);
        $(`#price${id_product}`).data('price', value);
    });

    $('#import-products').on('change', '.box-price', function() {
        var id_product = $(this).data('id');
        var cost = $(`#cost${id_product}`).text();
        var precio = $(this).val();
        var value = 0;
        if (precio > 0) {
            value = parseFloat((precio - cost) / cost * 100).toFixed(2);
        }
        $(`#profit${id_product}`).val(value);
        $(`#profit${id_product}`).data('profit', value);
    });

    $('#import-products').on('click', '.btn-import-list-send2', function(e) {
        e.preventDefault();
        window.open(`http://{{Auth::user()->shopify_url}}/admin/products/${e.target.dataset.shopifyid}`, '_blank');
    });

    $('#import-products').on('click', '.verModal', function(e) {
        e.preventDefault();
        $('#upgrade-plans-modal').modal('show');
    });

    $('#import-products').on('keydown', '.collection', function(e) {
        var id = $(this).data('id');
        if ($(this).val().indexOf(',') > -1) {
            $(`#collection_error${id}`).show();
        } else {
            $(`#collection_error${id}`).hide();
        }
        let length = e.target.value.length;
        let collection = e.target.value;
        if (e.key) {
            if (e.key.length == 1) {
                length += 1;
                collection += e.key;
            } else {
                if (e.code == 'Backspace') {
                    length -= 1;
                    collection = collection.slice(0, -1);
                }
            }
            if (length > 2) {
                var parameters = {
                    action: 'product_collection',
                    collection: JSON.stringify(collection)
                }
                $.getJSON(ajax_link, parameters, function(data) {
                    var str = '<div id="collection_data">';
                    data.collections.forEach(collection => {
                        str += `<option value="${collection}">`;
                    });
                    str += '</div>';
                    $('#collection_data').remove();
                    $(`#collection${id}`).html(str);
                })
            } else {
                $('#collection_data').remove();
            }
        }
    });

    $('#import-products').on('blur', '.collection', function(e) {
        if (e.target.value.trim() != '' && $('#collection_data').children().length == 0) {
            $('#modal_type').val(`#collections-${e.target.dataset.id}`);
            $('#modal-body').html("<h5>This collection doesn't exist in your Shopify store.<br>Are you sure to create new custom collection?</h5>");
            $('#delete-product-modal').css('display', 'block');
            $('#delete-product-modal').addClass('show');
            // $('#delete-product-modal .modal-content').attr('tabindex', -1).focus();
            $('body').append('<div class="modal-backdrop fade show"></div>');
        }
    });

    $('#import-products').on('keydown', '.type', function(e) {
        var id = $(this).data('id');
        if ($(this).val().indexOf(',') > -1) {
            $(`#type_error${id}`).show();
        } else {
            $(`#type_error${id}`).hide();
        }
        let length = e.target.value.length;
        let type = e.target.value;
        if (e.key) {
            if (e.key.length == 1) {
                length += 1;
                type += e.key;
            } else {
                if (e.code == 'Backspace') {
                    length -= 1;
                    type = type.slice(0,-1);
                }
            }
            if (length > 2) {
                var parameters = {
                    action: 'product_type',
                    type: type
                }
                $.getJSON(ajax_link, parameters, function(data) {
                    var str = '<div id="type_data">';
                    data.types.forEach(type => {
                        str += `<option value="${type}">`;
                    });
                    str += '</div>';
                    $('#type_data').remove();
                    $(`#type${id}`).html(str);
                })
            } else {
                $('#type_data').remove();
            }
        }
    });

    $('#import-products').on('keydown', '.tag', function(e) {
        let length = e.target.value.length;
        let tag = e.target.value;
        if (e.key) {
            if (e.key.length == 1) {
                length += 1;
                tag += e.key;
            } else {
                if (e.code == 'Backspace') {
                    length -= 1;
                    tag = tag.slice(0, -1);
                }
            }
            if (length > 2) {
                var parameters = {
                    action: 'product_tag',
                    tag: tag
                }
                var id = $(this).data('id');
                $.getJSON(ajax_link, parameters, function(data) {
                    var str = '<div id="tag_data">';
                    data.tags.forEach(tag => {
                        str += `<option value="${tag}">`;
                    });
                    str += '</div>';
                    $('#tag_data').remove();
                    $(`#tag${id}`).html(str);
                })
            } else {
                $('#tag_data').remove();
            }
        }
    });
</script>
@endsection
