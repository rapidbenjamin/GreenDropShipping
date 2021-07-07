@extends('layouts.app')

@section('content')
<div class="indexContent" data-page_name="MIGRATION">

    <div class="maincontent">

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

            <div class="alertan level2 alert-publish-all">
                <div class="agrid">
                    <p>You can view here all the products to migrate from your Shopify store.</p>
                </div>
            </div>
            <div class="migrate-products" style="display: block;">
                @if($total_count == 0)
                <button class="btn-migration migration" data-toggle="modal" data-target="#migrate-products-modal">Migrate</button>
                @else
                <div style="display: flex;">
                    <input type="checkbox" id="check-all-mp" value="" data-mark="false">
                    <button class="btn-delete-products alldeletebutton mx-1">Delete</button>
                    <button class="btn-confirm-products allconfirmbutton mx-1">Confirm</button>
                    <button class="btn-set-profit profit mx-1">Set Profit</button>
                    <button class="btn-setting-profit profit mx-1" style="display: none;">Setting...</button>
                </div>
                <div class="pagesize">
                    <span>Size</span>
                    <select name="PageSize" id="page_size">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <table class="greentable" cellspacing="0">
                    <thead>
                        <tr>
                            <th>
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
                        </tr>
                    </thead>
                    <tbody id="product_data">
                        @foreach ($mig_products as $product)
                        <tr class="productdatarow">
                            <td class="check">
                                <input type="checkbox" id="check-{{$product->id_shopify}}" data-id="{{$product->id_shopify}}" value="" class="checkbox">
                            </td>
                            <td class="pimage">
                                <div class="productphoto">
                                    <img src="{{json_decode($product->payload)->image_url}}">
                                </div>
                            </td>
                            <td data-label="PRODUCT NAME">
                                {{json_decode($product->payload)->name}}
                            </td>
                            <td data-label="COST GDS">
                                @if($product->type == 'migration')
                                <span id="cost-{{$product->id_shopify}}">${{number_format($product->cost,2,'.','')}}</span>
                                @endif
                            </td>
                            <td data-label="PROFIT">
                                @if($product->type == 'migration')
                                <div id="profit">
                                    <input type="text" style="text-align:center;" class="box-profit" id="profit-{{$product->id_shopify}}" data-id="{{$product->id_shopify}}" data-sku="{{$product->sku}}" value="{{number_format(($product->price - $product->cost)/$product->cost*100, 2, '.', '')}}">
                                %</div>
                                @endif
                            </td>
                            <td data-label="RETAIL PRICE">
                                <span id="price-{{$product->id_shopify}}">${{number_format($product->price,2,'.','')}}</span>
                            </td>
                            <td data-label="SKU">
                                {{$product->sku}}
                            </td>
                            <td>
                                @if ($product->type == 'migration')
                                <button class="btn-confirm-product confirmbutton mx-0" data-id="{{$product->id_shopify}}" id="confirm-{{$product->id_shopify}}">Confirm</button>
                                <button class="confirmbutton mx-0" data-id="{{$product->id_shopify}}" id="confirming-{{$product->id_shopify}}" style="display: none;">Confirming...</button>
                                <button class="confirmbutton mx-0" data-id="{{$product->id_shopify}}" id="confirmed-{{$product->id_shopify}}" style="display: none;">Confirmed</button>
                                <button class="btn-mp-delete deletebutton" id="delete-{{$product->id_shopify}}" data-migproductid="{{$product->id_shopify}}" style="display: none;">Delete</button>
                                <button class="deletebutton" id="deleting-{{$product->id_shopify}}" data-migproductid="{{$product->id_shopify}}" style="display: none;">Deleting...</button>
                                <button class="deletebutton" id="deleted-{{$product->id_shopify}}" data-migproductid="{{$product->id_shopify}}" style="display: none;">Deleted</button>
                                @else
                                <button class="btn-mp-delete deletebutton" id="delete-{{$product->id_shopify}}" data-migproductid="{{$product->id_shopify}}">Delete</button>
                                <button class="deletebutton" id="deleting-{{$product->id_shopify}}" data-migproductid="{{$product->id_shopify}}" style="display: none;">Deleting...</button>
                                <button class="deletebutton" id="deleted-{{$product->id_shopify}}" data-migproductid="{{$product->id_shopify}}" style="display: none;">Deleted</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div id="migrate-products-modal" class="modal fade" role="dialog" data-backdrop="true">
  <div class="modal-dialog modal-dialog-centered">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header" style="display:block">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Migrating</h4>
      </div>
      <div class="modal-body" style="text-align:center; font-size:20px;">
      <progress id="migrating-progress" max="100" value="0" style="width:100%;"> 70% </progress>
      <p id="percentage"></p>
      </div>
    </div>

  </div>
</div>

<div id="pagination"></div>
<input type="text" id="total_count" value="{{$total_count}}" hidden>
<input type="text" id="csrf_token" value="{{csrf_token()}}" hidden>
<input type="text" id="default_profit" value="{{$default_profit}}" hidden>

<script type="text/javascript">
    $(document).ready(function() {
        $('#total_count').text("{{$total_count}}");

        var user_id = "{{Auth::user() ? Auth::user()->id : 0}}";
        function deleteProductsAjax() {
            let product_ids = [];
            $("input[type='checkbox']").each(function(index, ele) {
                if (ele.disabled && ele.checked)
                    product_ids.push($(ele).attr('id').split('-')[1]);
            });
            if (user_id && product_ids.length) {
                $.ajax({
                        type: 'POST',
                        url: '/check-delete-migrate',
                        data: {
                            "_token": "{{ csrf_token() }}",
                            product_ids: product_ids,
                        },
                    })
                    .then(res => {
                        $('.btn-delete-products').prop('disabled', false);
                        $('.btn-confirm-products').prop('disabled', false);
                        $('.btn-set-profit').prop('disabled', false);
                        res.product_ids.forEach(id => {
                            $(`#delete-${id}`).hide();
                            $(`#deleting-${id}`).hide();
                            $(`#deleted-${id}`).show();
                            $(`#check-${id}`).prop('checked', false);
                        });
                    });
            }
        }
        deleteProductsAjax();
        setInterval(deleteProductsAjax, 15000);
    });
    $('.migrate-products').on('click', '.btn-mp-delete', function() {
        $(`#check-${$(this).data('migproductid')}`).prop('disabled', true);
        $(`#delete-${$(this).data('migproductid')}`).hide();
        $(`#deleting-${$(this).data('migproductid')}`).show();
        $(`#deleted-${$(this).data('migproductid')}`).hide();
        $(`#check-${$(this).data('migproductid')}`).removeClass();
        $(`#check-${$(this).data('migproductid')}`).prop('disabled', true);
        $.post('{{url("/delete-migrate-product")}}', {
            "_token": "{{ csrf_token() }}",
            product_ids: [$(this).data('migproductid')]
        }, function(data, status) {
            if (data.result) {
                $(`#delete-${data.product_id}`).hide();
                $(`#deleting-${data.product_id}`).hide();
                $(`#deleted-${data.product_id}`).show();
                $(`#check-${data.product_id}`).prop('checked', false);
            }
        });
    });
    $('.migrate-products').on('click', '#check-all-mp', function() {
        if ($('#check-all-mp').is(':checked')) {
            $('.checkbox').prop('checked', true);
        } else {
            $('.checkbox').prop('checked', false);
        }
    })
    $('.migrate-products').on('click', '.btn-delete-products', function() {
        let product_ids = [];
        $("input.checkbox:checked").each(function(index, ele) {
            let product_id = $(ele).attr('id').split('-')[1];
            if ($(`#delete-${product_id}`).is(":visible")) {
                product_ids.push(product_id);
            }
        });
        if (product_ids.length) {
            if (confirm('Are you sure to delete these products from shopify?')) {
                $('.btn-delete-products').prop('disabled', true);
                $('.btn-confirm-products').prop('disabled', true);
                $('.btn-set-profit').prop('disabled', true);
                product_ids.forEach(product_id => {
                    $(`#check-${product_id}`).prop('disabled', true);
                    $(`#delete-${product_id}`).hide();
                    $(`#deleting-${product_id}`).show();
                    $(`#deleted-${product_id}`).hide();
                    $(`#check-${product_id}`).removeClass();
                    $(`#check-${product_id}`).prop('disabled', true);
                });
                $.post('{{url("/delete-migrate-products")}}', {
                    "_token": "{{ csrf_token() }}",
                    product_ids: product_ids,
                }, function(data, status) {});
            }
        } else {
            alert('At least one checkbox must be selected');
        }
    });
    $('.migrate-products').on('click', '.btn-set-profit', function() {
        if (confirm('Are you sure to replace current profits of all products with {{$default_profit}}%?')) {
            $(this).hide();
            $('.btn-delete-products').prop('disabled', true);
            $('.btn-confirm-products').prop('disabled', true);
            $('.btn-confirm-product').prop('disabled', true);
            $('.btn-setting-profit').show();
            var parameters = {
                action: 'set-default-profit',
            }
            $.getJSON(ajax_link, parameters, function (res) {
                $('.box-profit').each(function(index, ele) {
                    $(ele).val($('#default_profit').val());
                    var cost = $(`#cost-${$(ele).data('id')}`).text();
                    var profit = $('#default_profit').val();
                    $(`#price-${$(ele).data('id')}`).text(parseFloat(cost.substr(1) * (1 + profit / 100)).toFixed(2));
                });
                $('.btn-setting-profit').hide();
                $('.btn-set-profit').show();
                $('.btn-delete-products').prop('disabled', false);
                $('.btn-confirm-product').prop('disabled', false);
                $('.btn-confirm-products').prop('disabled', false);
            })
        }
    });
    $('.migrate-products').on('click', '.btn-confirm-product', function() {
        $(`#check-${$(this).data('id')}`).prop('disabled', true);
        $(`#confirm-${$(this).data('id')}`).hide();
        $(`#confirming-${$(this).data('id')}`).show();
        $(`#confirmed-${$(this).data('id')}`).hide();
        $(`#check-${$(this).data('id')}`).removeClass();
        $(`#check-${$(this).data('id')}`).prop('disabled', true);
        $.post('{{url("/confirm-migrate-products")}}', {
            "_token": "{{ csrf_token() }}",
            products: [{
                id: $(this).data('id'),
                profit: $(`#profit-${$(this).data('id')}`).val()
            }]
        }, function(data, status) {
            data.products.forEach(product => {
                $(`#confirm-${product.id}`).hide();
                $(`#confirming-${product.id}`).hide();
                $(`#check-${product.id}`).prop('checked', false);
                $(`#profit-${product.id}`).prop('disabled', true);
                $(`#profit-${product.id}`).css({'border': 'none', 'background': 'transparent'});
                if (product.result) {
                    $(`#confirmed-${product.id}`).show();
                    $(`#check-${product.id}`).prop('checked', false);
                } else {
                    $(`#delete-${product.id}`).show();
                    $(`#confirmed-${product.id}`).hide();
                    $(`#check-${product.id}`).prop('disabled', false);
                    $(`#check-${product.id}`).addClass('checkbox');
                }
            });
        });
    });
    $('.migrate-products').on('click', '.btn-confirm-products', function() {
        let products = [];
        $("input.checkbox:checked").each(function(index, ele) {
            let product_id = $(ele).attr('id').split('-')[1];
            let profit = $(`#profit-${$(this).data('id')}`).val();
            if ($(`#confirm-${product_id}`).is(":visible")) {
                products.push({
                    id: product_id,
                    profit: profit
                });
            }
        });
        if (products.length > 0) {
            $(this).prop('disabled', true);
            $('.btn-delete-products').prop('disabled', true);
            $('.btn-set-profit').prop('disabled', true);
            products.forEach(product => {
                $(`#check-${product.id}`).prop('disabled', true);
                $(`#confirm-${product.id}`).hide();
                $(`#confirming-${product.id}`).show();
                $(`#check-${product.id}`).removeClass();
                $(`#check-${product.id}`).prop('disabled', true);
            });
            $.post('{{url("/confirm-migrate-products")}}', {
                "_token": "{{ csrf_token() }}",
                products: products,
            }, function(data, status) {
                $('.btn-confirm-products').prop('disabled', false);
                $('.btn-delete-products').prop('disabled', false);
                $('.btn-set-profit').prop('disabled', false);
                data.products.forEach(product => {
                    $(`#confirm-${product.id}`).hide();
                    $(`#confirming-${product.id}`).hide();
                    $(`#check-${product.id}`).prop('checked', false);
                    $(`#profit-${product.id}`).prop('disabled', true);
                    $(`#profit-${product.id}`).css({'border': 'none', 'background': 'transparent'});
                    if (product.result) {
                        $(`#confirmed-${product.id}`).show();
                        $(`#check-${product.id}`).prop('checked', false);
                    } else {
                        $(`#delete-${product.id}`).show();
                        $(`#confirmed-${product.id}`).hide();
                        $(`#check-${product.id}`).prop('disabled', false);
                        $(`#check-${product.id}`).addClass('checkbox');
                    }
                });
            });
        } else {
            alert('At least one checkbox must be selected');
        }
    });
    $('.migrate-products').on('change', '.page_size', function (event) {
        var parameters = {
            action: 'migrate-products',
            page_size: event.target.value,
            page_number: 1
        }
        $.getJSON(ajax_link, parameters, function (res) {
            pagination(res);
            showMigrateProducts(res.mig_products);
        })
        function pagination (data) {
            if (data.page_number == '1') {
                $('#prev').prop('disabled', true);
            } else {
                $('#prev').prop('disabled', false);
            }
            if (data.page_number * data.page_size >= data.total_count) {
                $('#next').prop('disabled', true);
            } else {
                $('#next').prop('disabled', false);
            }
            $('#total_count').text(data.total_count);
            $('#page_number').text(`${data.page_number}/${Math.ceil(data.total_count / data.page_size)}`);
        }
        function showMigrateProducts (data) {
            $('.productdatarow').remove();
            var str = migrateProducts(data);
            $('#product_data').html(str);
        }
        function migrateProducts (products) {
            var str = '';
            products.forEach(product => {
                var payload = JSON.parse(product.payload);
                var button_str = '', profit_str = '';
                var cost_str = '';
                if (product.type == 'migration'){
                    button_str = `<button class="btn-confirm-product confirmbutton mx-0" data-id="${product.id_shopify}" id="confirm-${product.id_shopify}">Confirm</button>
                                    <button class="confirmbutton mx-0" data-id="${product.id_shopify}" id="confirming-${product.id_shopify}" style="display: none;">Confirming...</button>
                                    <button class="confirmbutton mx-0" data-id="${product.id_shopify}" id="confirmed-${product.id_shopify}" style="display: none;">Confirmed</button>
                                    <button class="btn-mp-delete deletebutton" id="delete-${product.id_shopify}" data-migproductid="${product.id_shopify}" style="display: none;">Delete</button>
                                    <button class="deletebutton" id="deleting-${product.id_shopify}" data-migproductid="${product.id_shopify}" style="display: none;">Deleting...</button>
                                    <button class="deletebutton" id="deleted-${product.id_shopify}" data-migproductid="${product.id_shopify}" style="display: none;">Deleted</button>`;
                    profit_str = `<div id="profit">
                        <input type="text" style="text-align:center;" class="box-profit" id="profit-${product.id_shopify}" data-id="${product.id_shopify}" data-sku="${product.sku}" value="${parseFloat((product.price - product.cost) / product.cost * 100).toFixed(2)}">
                        %</div>`;
                    cost_str = `<span id="cost-${product.id_shopify}">$${parseFloat(product.cost).toFixed(2)}</span>`;
                } else {
                    button_str = `<button class="btn-mp-delete deletebutton" id="delete-${product.id_shopify}" data-migproductid="${product.id_shopify}">Delete</button>
                                    <button class="deletebutton" id="deleting-${product.id_shopify}" data-migproductid="${product.id_shopify}" style="display: none;">Deleting...</button>
                                    <button class="deletebutton" id="deleted-${product.id_shopify}" data-migproductid="${product.id_shopify}" style="display: none;">Deleted</button>`;
                }

                str += `<tr class="productdatarow">
                    <td class="check">
                        <input type="checkbox" id="check-${ product.id_shopify }" data-id="${ product.id_shopify }" value="" class="checkbox">
                    </td>
                    <td class="pimage">
                        <div class="productphoto">
                            <img src="${payload.image_url}">
                        </div>
                    </td>
                    <td data-label="PRODUCT NAME">
                        ${ payload.name }
                    </td>
                    <td data-label="COST GDS">
                        ${cost_str}
                    </td>
                    <td data-label="PROFIT">
                        ${profit_str}
                    </td>
                    <td data-label="RETAIL PRICE">
                        <span id="price-${product.id_shopify}">$${parseFloat(product.price).toFixed(2)}</span>
                    </td>
                    <td data-label="SKU">
                        ${product.sku}
                    </td>
                    <td>
                        ${button_str}
                    </td>
                </tr>`;
            });
            return str;
        }
    });
    $('.migrate-products').on('change', '.box-profit', function() {
        var id_product = $(this).data('id');
        var cost = $('#cost-' + id_product).text();
        var profit = $(this).val();
        var value = cost.substr(1);
        if (profit > 0) {
            value = parseFloat((cost.substr(1) * (100 + profit * 1)) / 100).toFixed(2);
        }
        $.getJSON(ajax_link, {
            action: 'change-profit',
            sku: $(this).data('sku'),
            profit: profit
        }, function (res) {
            $('#price-' + id_product).text(`$${value}`);
        })
    });
</script>
@endsection
