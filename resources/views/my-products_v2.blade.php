@extends('layouts.app')

@section('content')
<div class="indexContent" data-page_name="MY PRODUCTS">
    <div class="maincontent">
        <div class="wrapinsidecontent">
            @if($total_count > 0)
            @can("plan_view-my-products")
            <div class="product-menu my-product-menu" id="product-top-menu">
                <div class="sendtoshopify">
                    <div class="checksend">
                        <input type="checkbox" id="check-all-products" value="" data-mark="false">
                        <span id="select-all" class="h4 mx-2 my-0 font-weight-bold">Select All</span>
                        <span id="selected-products">0</span>
                    </div>
                    <div class="btn-import-actions">
                        <button class="btn-mp-delete-all alldeletebutton redbutton">Delete selected</button>
                    </div>
                </div>
                <div class="pagesize">
                    <span class="h5 my-0">Show</span>
                    <select name="PageSize" id="page_size">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            @endcan
            <table class="greentable my-products" cellspacing="0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Cost</th>
                        <th>Profit</th>
                        <th>Price</th>
                        <th>SKU</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="product_data"></tbody>
            </table>
            <div id="pagination"></div>
            @endif
            <div class="empty-product my-empty" style="display: none;">
                <div class="empty-text">
                    <h2 class="my-3"><strong>Your product list is empty!</strong></h2>
                    <h4 style="line-height: 1.5;" class="my-0">You don't have any product in your store.<br>Go to your import list to add products to your store. If you have not created an import list yet, go to Search Products and add  products to your import list.</h4>
                    <a href="/import-list"><button class="btn btn-success btn-lg my-3 greenbutton border-0">Go To Import List</button></a>
                </div>
                <div>
                    <img src="{{ asset('/img/noproduct.png') }}" alt="No Products">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="back-to-top" style="display:none">
    <img src=" {{ asset('/img/back_to_top.png') }}" alt="">
    <span style="text-align: center;" class="h5">Back<br>to Top</span>
</div>

<input type="text" id="product_id" value="" hidden>
<input type="text" id="total_count" value="{{$total_count}}" hidden>

<script type="text/javascript">
    $(document).ready(function() {
        $('#total_count').text("{{$total_count}}");
        $('#check-all-products').click(function() {
            if ($('#check-all-products').is(':checked')) {
                $('.checkbox').prop('checked', true);
                let count = 0;
                $("input.checkbox:checked").each(function(index, ele) {
                    count++;
                });
                $('#selected-products').text(count);
            } else {
                $('.checkbox').prop('checked', false);
                $('#selected-products').text(0);
            }
            showBulkActionButtons();
        });

        $('.btn-mp-delete-all').click(function() {
            let products = [];
            $("input.checkbox:checked").each(function(index, ele) {
                if ($(`#delete-${$(ele).attr('id').split('-')[1]}`).is(":visible")) {
                    products.push($(ele).data('id'));
                }
            });
            if (products.length) {
                $(this).attr('data-toggle', 'modal');
                $(this).attr('data-target', '#confirm-modal');
                $('#confirm-modal-title').text('Product');
                $('#confirm-modal-body').html(`<h5>Are you sure you want to remove ${products.length} products from your Shopify store?</h5>`);
                $('#product_id').val('delete-products');
                $('#confirm').text('Delete');
                $('#confirm').removeClass('btn-success');
                $('#confirm').removeClass('greenbutton');
                $('#confirm').addClass('btn-danger');
                $('#confirm').css('background-color', '#F72525');
                $('#confirm-modal-footer').show();
            } else {
                $(this).removeAttr('data-toggle');
                $(this).removeAttr('data-target');
            }
        });

        $('#confirm').click(function() {
            if ($('#product_id').val() != 'cancel') {
                if ($('#product_id').val() == 'delete-products') {
                    deleteProducts();
                } else {
                    deleteProduct($('#product_id').val());
                }
            }
        });

        function disableProduct(shopify_id) {
            $(`#check-${shopify_id}`).prop('disabled', true);
            $(`#check-${shopify_id}`).removeClass();
            $(`#check-${shopify_id}`).prop('checked', false);
            $(`#edit-${shopify_id}`).hide();
            $(`#name-${shopify_id}`).css('pointer-events', 'none');
            $(`#view-${shopify_id}`).hide();
            $(`#delete-${shopify_id}`).hide();
            $(`#deleting-${shopify_id}`).show();
        }

        function enableProduct(shopify_id) {
            $(`#check-${shopify_id}`).prop('disabled', false);
            $(`#check-${shopify_id}`).addClass('checkbox');
            $(`#edit-${shopify_id}`).show();
            $(`#name-${shopify_id}`).css('pointer-events', 'auto');
            $(`#view-${shopify_id}`).show();
            $(`#deleting-${shopify_id}`).hide();
            $(`#delete-${shopify_id}`).show();
        }

        function deleteProduct(shopify_id) {
            disableProduct(shopify_id);
            $('#check-all-products').prop('checked', false);
            $('#check-all-products').prop('disabled', true);
            $('.btn-mp-delete-all').prop('disabled', true);
            $.post('{{url("/delete-shopify-product")}}', {
                "_token": "{{ csrf_token() }}",
                id_shopify: shopify_id
            }, function(data, status) {
                if (data.result) {
                    $(`#deleting-${shopify_id}`).hide();
                    $(`#deleted-msg-${shopify_id}`).show();
                    $('.btn-mp-delete-all').prop('disabled', false);
                    $('#check-all-products').prop('disabled', false);
                    showBulkActionButtons();
                } else {
                    enableProduct(shopify_id);
                    popupFailMsg('A problem has occured while deleting your product from Shopify store.');
                    $('#contact').show();
                    $('#check-all-products').prop('disabled', false);
                    $('.btn-mp-delete-all').prop('disabled', false);
                    showBulkActionButtons();
                }
            }).fail(function() {
                enableProduct(shopify_id);
                popupFailMsg('A problem has occured while deleting your product from Shopify store.');
                $('#contact').show();
                $('#check-all-products').prop('disabled', false);
                $('.btn-mp-delete-all').prop('disabled', false);
                showBulkActionButtons();
            });
        }

        function deleteProducts() {
            let products = [];
            let product_ids = [];
            $("input.checkbox:checked").each(function(index, ele) {
                let product_id = $(ele).attr('id').split('-')[1];
                if ($(`#delete-${product_id}`).is(":visible")) {
                    products.push({
                        product_id: $(ele).data('id'),
                        product_shopify_id: product_id
                    });
                    product_ids.push(product_id);
                }
            });
            products.forEach(product => {
                disableProduct(product.product_shopify_id);
            });
            $('#check-all-products').prop('checked', false);
            $('#check-all-products').prop('disabled', true);
            $(`.btn-mp-delete-all`).prop('disabled', true);
            window.localStorage.removeItem('my_product_ids');
            window.localStorage.setItem('my_product_ids', JSON.stringify(product_ids));
            $.post('{{url("/delete-all-shopify-product")}}', {
                "_token": "{{ csrf_token() }}",
                products: products,
            }, function(data, status) {
            }).fail(function(data, status) {
            });
        }

        function deleteProductsAjax() {
            let product_ids = JSON.parse(window.localStorage.getItem('my_product_ids'));
            if (product_ids) {
                if (product_ids.length) {
                    $.post('/check-delete-shopify-products', {
                        product_shopify_ids: product_ids,
                        "_token": "{{ csrf_token() }}",
                    }).then(res => {
                        if (res.product_shopify_ids.length > 0) {
                            product_ids.forEach(product_id => {
                                let flag = true;
                                res.product_shopify_ids.forEach(shopify_id => {
                                    if (product_id == shopify_id) {
                                        $(`#deleting-${shopify_id}`).hide();
                                        $(`#deleted-msg-${shopify_id}`).show();
                                        flag = false;
                                    }
                                });
                                if (flag) {
                                    enableProduct(product_id);
                                }
                            });
                            $(`.btn-mp-delete-all`).prop('disabled', false);
                            $('#check-all-products').prop('disabled', false);
                            showBulkActionButtons();
                        } else {
                            product_ids.forEach(product_id => {
                                enableProduct(product_id);
                            });
                            $('#check-all-products').prop('disabled', false);
                            $(`.btn-mp-delete-all`).prop('disabled', false);
                            popupFailMsg('A problem has occured while deleting your products from Shopify store.');
                            $('#contact').show();
                        }
                        showBulkActionButtons();
                        window.localStorage.removeItem('my_product_ids');
                    });
                }
            }
        }
        deleteProductsAjax();
        setInterval(deleteProductsAjax, 15000);
    });
    
    $('#product_data').on('click', '.btn-mp-delete', function(e) {
        $('#confirm-modal-title').text('Product');
        $('#confirm-modal-body').html(`<div style="gap: 1rem;" class="d-flex align-items-center pb-3">
                <img style="width:75px; height:75px;" src="${e.target.dataset.img}"/>
                <div>
                    <h5 class="font-weight-bold">${e.target.dataset.name}</h5>
                    <h5 class="mt-2 mb-0">${e.target.dataset.sku}</h5>
                </div>
            </div>
            <h5 class="my-3">This product will be removed from your store. Are you sure you want to remove this product?</h5>`);
        $('#product_id').val(e.target.dataset.myproductid);
        $('#confirm').text('Delete Product');
        $('#confirm').removeClass('btn-success');
        $('#confirm').removeClass('greenbutton');
        $('#confirm').addClass('btn-danger');
        $('#confirm').css('background-color', '#F72525');
        $('#confirm-modal-footer').show();
    })
    
    $('#product_data').on('click', '.edit-product', function() {
        window.open('https://{{Auth::user()->shopify_url}}/admin/products/' + $(this).data('shopifyid'), '_blank');
    });

    $('#product_data').on('click', '.vplist', function(e) {
        e.preventDefault();
        var idp = $(this).attr('data-view');
        if (!$(idp).hasClass("active")) {
            $(".shoproductrow").removeClass("active");
            $(".productdatarow").removeClass("showp");
        }

        $(idp).toggleClass("active");
        $(this).parents(".productdatarow").toggleClass("showp");
    });

    $('#product_data').on('click', '.checkbox', function(e) {
        let count = parseInt($('#selected-products').text());
        if (e.target.checked) {
            $('#selected-products').text(count + 1);
        } else {
            if ($('#selected-products').text() > 0) {
                $('#selected-products').text(count - 1);
            }
        }
        showBulkActionButtons();
    });
</script>
@endsection
