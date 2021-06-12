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
                <button class="btn-import-list-delete-all" data-toggle="modal" data-target="#delete-product-modal">Delete <img class="button-icon" src="img/delete.png" alt="Trash Can - Delete Icon"></button>
                <button class="btn-import-list-send-all">Send to Shopify <img class="button-icon" src="img/edit.png" alt="Pencil in Square - Edit Icon"></button>
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
                            <button @cannot("plan_view-my-products") data-toggle="modal" data-target="#upgrade-plans-modal" @endcannot @can("plan_view-my-products") data-toggle="modal" data-target="#delete-product-modal" @endcan class='delete @can("plan_view-my-products") btn-import-list-delete @endcan' id="delete-{{$ap->id_import_list}}" data-id="{{$ap->id_import_list}}"  data-name="{{ $ap->name }}" data-sku="{{ $ap->sku }}">Delete <img class="button-icon" src="img/delete.png" alt="Trash Can - Delete Icon"></button>
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
                                                <input type="text" class="box-price" id="price{{$ap->id_import_list}}" data-price="{{$ap->price}}" data-id="{{$ap->id_import_list}}" value="{{number_format($ap->price * (100 + $profit) / 100, 2,'.','')}}">
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

<input type="text" id="product_id" value="" hidden>

<!-- pagination -->
<div class="pagination">
    <ul class="pagination" role="navigation">
        <li class="page-item" id="prev">
            <a class="page-link" rel="prev" aria-label="« Previous">‹</a>
        </li>

        <li class="page-item active" aria-current="page"><span id="page_number" class="page-link">1/{{ceil($total_count/10)}}</span></li>

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


        $('.btn-import-list-send-all').click(function() {

            //Get all checked products
            let products = [];
            $(this).prop('disabled', true);
            $('.btn-import-list-delete-all').prop('disabled', true);

            $("input.checkbox:checked").each(function(index, ele) {
                let productId = $(ele).attr('id').split('-')[1];
                let images = [];
                if ($(`.btn-import-list-send-${productId}`).is(":visible")) {

                    $(`.btn-import-list-send-${productId}`).hide();
                    $(`.btn-import-list-send3-${productId}`).show();
                    $(`.btn-import-list-send-${productId}`).prop('disabled', true);
                    $(ele).prop('disabled', true);
                    $(ele).removeClass();
                    $(`#delete-${productId}`).hide();

                    // data array of all checked products
                    $("input.chk-img" + productId + ":checked").each(function(index, ele) {
                        images.push($('.img' + productId + '-' + $(ele).attr('data-index')).attr('src'));
                    });

                    products.push({
                        id: productId,
                        name: $(`#name${productId}`).val(),
                        weight: $(`#weight${productId}`).text().trim(),
                        price: $(`#price${productId}`).val(),
                        cost: $(`#cost${productId}`).val(),
                        description: CKEDITOR.instances[`description${productId}`].getData(),
                        product_type: $(`#type${productId}`).val(),
                        tags: $(`#tags${productId}`).val(),
                        collections: $(`#collections${productId}`).val(),
                        sku: $(`#sku${productId}`).val(),
                        upc: $(`#upc${productId}`).val(),
                        profit: $(`#profit${productId}`).val(),
                        images: images
                    });
                }
            });



            let btn = $(this);
            if (products.length == 0) {
                alert('At least one checkbox must be selected');
                return;
            } else {
                $('#check-all').prop('disabled', true);
                $('#check-all').prop('checked', false);
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

        $('.btn-import-list-delete-all').click(function () {
            var product_ids = [];
            $('input.checkbox:checked').each(function (index, ele) {
                product_ids.push($(ele).attr('id').split('-')[1]);
            });
            if (product_ids.length) {
                $('#modal-body').html(`<h5>Are you sure to delete these checked products from Import List?</h5>`);
                $('#product_id').val('delete-products');
            } else {
                $('#modal-body').html(`<h5>At least one checkbox must be selected</h5>`);
                $('#product_id').val('cancel');
            }
        })

        $('#confirm').click(function() {
            if ($('#product_id').val() != 'cancel'){
                if ($('#product_id').val() == 'delete-products') deleteProducts();
                else deleteProduct($('#product_id').val());
            }
        });

        function deleteProduct(id) {
            var parameters = {
                action: 'delete_import_list',
                id_import_list: [id]
            }
            $(`#delete-${id}`).hide();
            $(`#deleting-${id}`).show();
            $(`.btn-import-list-send-${id}`).hide();
            $.getJSON(ajax_link, parameters, function (data) {
                location.reload()
            }).fail(function (data) {
                if (data.status == 403) $('#upgrade-plans-modal').modal('show')
            })
        }

        function deleteProducts() {
            var product_ids = [];
            $('input.checkbox:checked').each(function (index, ele) {
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
            });
            $('#check-all').prop('checked', false);
            $('#check-all').prop('disabled', true);
            $.getJSON(ajax_link, parameters, function (data) {
                $('#check-all').prop('disabled', false);
                location.reload();
            }).fail(function (data) {
                console.log('error1', data.status)
                if (data.status == 403) $('#upgrade-plans-modal').modal('show')
            })
        }

        var usr_id = "{{Auth::user() ? Auth::user()->id : 0}}";

        function publishProductsAjax() {
            let product_ids = [];
            $("input[type='checkbox']").each(function(index, ele) {
                if (ele.disabled && ele.checked) {
                    product_ids.push($(ele).attr('id').split('-')[1]);
                }

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
                    .then(data => {
                        if (data.result) {
                            $('.btn-import-list-send-all').prop('disabled', false);
                            $('.btn-import-list-delete-all').prop('disabled', false);
                            data.id_shopify.forEach(productId => {
                                $(`.btn-import-list-send3-${productId}`).hide();
                                $(`.btn-import-list-send2-${productId}`).show();
                                $(`#check-${productId}`).prop('checked', false);
                                $('#check-all').prop('disabled', false);
                            });
                        }
                    });
            }
        }
        publishProductsAjax();
        setInterval(publishProductsAjax, 15000);

    }); //Close document ready

    $('#import-products').on('click', '.producttabs .thetab', function(e) {
        e.preventDefault();
        $(this).parents(".producttabs").find(".thetab").removeClass("active");
        $(this).addClass("active");
        var thetabid = $(this).attr("href");
        $(this).parents(".producttabs").find(".tabcontent").removeClass("active");
        $(this).parents(".producttabs").find(thetabid).addClass("active");
    })
    $('#import-products').on('click', '.btn-import-list-send', function() {
        let productId = $(this).data('id');
        let images = [];
        $("input.chk-img" + productId + ":checked").each(function(index, ele) {
            images.push($('.img' + productId + '-' + $(ele).attr('data-index')).attr('src'));
        });

        $(`.btn-import-list-send-${productId}`).hide();
        $(`.btn-import-list-send3-${productId}`).show();
        $(`#delete-${productId}`).hide();
        $(`#check-${productId}`).prop('disabled', true);
        $(`#check-${productId}`).removeClass();

        let btn = $(this);
        btn.prop('disabled', true);

        let product = {
            id: productId,
            name: $(`#name${productId}`).val(),
            weight: $(`#weight${productId}`).text().trim(),
            price: $(`#price${productId}`).val(),
            cost: $(`#cost${productId}`).val(),
            description: CKEDITOR.instances[`description${productId}`].getData(),
            product_type: $(`#type${productId}`).val(),
            tags: $(`#tags${productId}`).val(),
            collections: $(`#collections${productId}`).val(),
            sku: $(`#sku${productId}`).val(),
            upc: $(`#upc${productId}`).val(),
            profit: $(`#profit${productId}`).val(),
            images: images
        };

        $.post('{{url("/publish-product")}}', {
            "_token": "{{ csrf_token() }}",
            product: product
        }, function(data, status) {
            btn.prop('disabled', false);
            $('.alert-publish-single').show();
            $(`.btn-import-list-send3-${productId}`).hide();
            $(`.btn-import-list-send2-${productId}`).show();
            $(`.btn-import-list-send2-${productId}`).attr('data-shopifyid', data.id_shopify);
            $(`#check-${productId}`).prop('checked', false);
        }).fail(function(data) {
            if (data.status == 403)
                $('#upgrade-plans-modal').modal('show')
        });
    });
    $('#import-products').on('click', '.btn-import-list-delete', function() {
        $('#modal-body').html(`<h5 style='text-align:center'>${$(this).data('name')}(${$(this).data('sku')})</h5><h5>This product will remove it from Import List. Do you really want to delete it?</h5>`);
        $('#product_id').val($(this).data('id'));
    });
    $('#import-products').on('change', '.box-profit', function() {
        var id_product = $(this).data('id');
        var cost = $(`#cost${id_product}`).val();
        var profit = $(this).val();
        if (profit > 0) {
            var value = parseFloat((cost * (100 + profit * 1)) / 100).toFixed(2);
        } else value = cost;

        $(`#price${id_product}`).val(value);
        $(`#price${id_product}`).data('price', value);
    });
    $('#import-products').on('change', '.box-price', function() {
        var id_product = $(this).data('id');
        var cost = $(`#cost${id_product}`).val();
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
        window.open('http://{{Auth::user()->shopify_url}}/admin/products/', '_blank');
    });
    $('#import-products').on('click', '.verModal', function(e) {
        e.preventDefault();
        $('#upgrade-plans-modal').modal('show');
    });

</script>
@endsection
