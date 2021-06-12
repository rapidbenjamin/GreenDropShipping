@extends('layouts.app')

@section('content')
<div class="indexContent" data-page_name="MY PRODUCTS">

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
                    @if(count($prods) > 0)
                    <p>You can view here all the products you have added to your Shopify store.</p>
                    @else
                    <p>When products are published to Shopify you'll see them here.</p>
                    @endif
                </div>
            </div>
            @can("plan_view-my-products")
            <div class="myproducts" style="display: block;">
                <div class="checksend" style="float: left; padding: 10px;">
                    <input type="checkbox" id="check-all-mp" value="" data-mark="false">
                </div>
                <div>
                    <button class="btn-mp-delete-all alldeletebutton" data-toggle="modal" data-target="#delete-product-modal">Delete</button>
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
                        <th>

                        </th>
                    </tr>
                </thead>
                <tbody id="product_data">


                    @foreach ($prods as $pr)
                    <tr class="productdatarow">
                        <td class="check">
                            <input type="checkbox" id="check-{{ $pr->id_shopify }}" data-id="{{ $pr->id_my_products }}" value="" class="checkbox">
                        </td>
                        <td class="pimage">
                            <div class="productphoto">
                                <img src="{{$pr->image_url_75}}">
                            </div>
                        </td>
                        <td data-label="PRODUCT NAME">
                            <a href="search-products/{{$pr->id}}" target="_blank">{{ $pr->name }}</a>
                        </td>
                        <td data-label="COST GDS">
                            ${{$pr->price}}
                        </td>
                        <td data-label="PROFIT">
                            {{ $pr->profit }}%
                        </td>
                        <td data-label="RETAIL PRICE">
                            ${{number_format($pr->price * (100 + $pr->profit) / 100, 2,'.','')}}
                        </td>
                        <td data-label="SKU">
                            {{$pr->sku}}
                        </td>
                        <td>
                            <button class="btn-mp-view viewbutton vplist" data-id="{{ $pr->id }}" id="view-{{$pr->id_shopify}}" data-view="#product{{ $pr->id }}">View</button>
                        </td>
                        <td>
                            <button class="btn-mp-delete deletebutton" data-toggle="modal" data-target="#delete-product-modal" id="delete-{{ $pr->id_shopify }}" data-myproductid="{{ $pr->id_shopify }}" data-name="{{ $pr->name }}" data-sku="{{ $pr->sku }}">Delete</button>
                            <button class="deletebutton" id="deleting-{{ $pr->id_shopify }}" data-myproductid="{{ $pr->id_shopify }}" style="display: none;">Deleting...</button>
                            <button class="deletebutton" id="deleted-{{ $pr->id_shopify }}" data-myproductid="{{ $pr->id }}" style="display: none;">Deleted</button>
                        </td>
                    </tr>
                    <tr class="shoproductrow" id="product{{ $pr->id }}">
                        <td></td>
                        <td colspan="8">
                            <div class="productlisthow">
                                <div class="productimage">
                                    <img src="{{$pr->image_url_285}}">
                                </div>
                                <div class="productdata">
                                    <h3>{{ $pr->name }}</h3>
                                    <p class="price">Price ${{number_format($pr->price * (100 + $pr->profit) / 100, 2,'.','')}}</p>
                                    <p>
                                        Stock: {{ $pr->stock }}
                                    </p>
                                    <p>
                                        Cost: {{ $pr->price }}
                                    </p>
                                    <p>
                                        Profit: {{ $pr->profit }}%
                                    </p>
                                    <p>
                                        Brand: {{ $pr->brand }}
                                    </p>

                                    <div class="pbuttons">
                                        <button class="edit edit-product" id="edit-{{$pr->id_shopify}}" data-shopifyid="{{ $pr->id_shopify }}">Edit on Shopify</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>

        </div>
    </div>
</div>

<input type="text" id="product_id" value="" hidden>

<!-- pagination -->
<div class="pagination">
    <!-- {{ $prods->appends(request()->query())->links() }} -->
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

</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#total_count').text("{{$total_count}}");
        $('#check-all-mp').click(function() {
            if ($('#check-all-mp').is(':checked')) {
                $('.checkbox').prop('checked', true);
            } else {
                $('.checkbox').prop('checked', false);
            }
        });

        $('.btn-mp-delete-all').click(function() {
            let products = [];
            $("input.checkbox:checked").each(function(index, ele) {
                if ($(`#delete-${$(ele).attr('id').split('-')[1]}`).is(":visible")) {
                    products.push($(ele).data('id'));
                }
            });
            if (products.length) {
                $('#modal-body').html(`<h5>Are you sure to delete these checked products from your Shopify Store?</h5>`);
                $('#product_id').val('delete-products');
            } else {
                $('#modal-body').html(`<h5>At least one checkbox must be selected</h5>`);
                $('#product_id').val('cancel');
            }

        });

        $('#confirm').click(function() {
            if ($('#product_id').val() != 'cancel'){
                if ($('#product_id').val() == 'delete-products') deleteProducts();
                else deleteProduct($('#product_id').val());
            }
        });

        function deleteProduct(id) {
            $(`#check-${id}`).prop('disabled', true);
            $(`#check-${id}`).removeClass();
            $(`#check-${id}`).prop('checked', false);
            $(`#delete-${id}`).hide();
            $(`#deleting-${id}`).show();
            $(`#deleted-${id}`).hide();
            $(`#view-${id}`).hide();
            $(`#edit-${id}`).hide();
            $.post('{{url("/delete-shopify-product")}}', {
                "_token": "{{ csrf_token() }}",
                product_id: id
            }, function(data, status) {
                if (data.result) {
                    $(`#delete-${data.product_id}`).hide();
                    $(`#deleting-${data.product_id}`).hide();
                    $(`#deleted-${data.product_id}`).show();
                }
            });
        }

        function deleteProducts() {
            let products = [];
            $("input.checkbox:checked").each(function(index, ele) {
                let product_id = $(ele).attr('id').split('-')[1];
                if ($(`#delete-${product_id}`).is(":visible")) {
                    products.push({
                        product_id: $(ele).data('id'),
                        product_shopify_id: product_id
                    })
                }
            });
            products.forEach(product => {
                $(`#check-${product.product_shopify_id}`).prop('disabled', true);
                $(`#check-${product.product_shopify_id}`).removeClass();
                $(`#delete-${product.product_shopify_id}`).hide();
                $(`#deleting-${product.product_shopify_id}`).show();
                $(`#deleted-${product.product_shopify_id}`).hide();
                $(`#view-${product.product_shopify_id}`).hide();
                $(`#edit-${product.product_shopify_id}`).hide();
            });
            $('#check-all-mp').prop('checked', false);
            $('#check-all-mp').prop('disabled', true);
            $.post('{{url("/delete-all-shopify-product")}}', {
                "_token": "{{ csrf_token() }}",
                products: products,
            }, function(data, status) {});
        }
        var user_id = "{{Auth::user() ? Auth::user()->id : 0}}";

        function deleteProductsAjax() {
            let product_ids = [];
            $("input[type='checkbox']").each(function(index, ele) {
                if (ele.disabled && ele.checked) {
                    let product_id = $(ele).attr('id').split('-')[1];
                    product_ids.push(product_id);
                }

            });
            if (user_id && product_ids.length) {
                $.ajax({
                        type: 'POST',
                        url: '/check-delete-shopify-products',
                        data: {
                            product_shopify_ids: product_ids,
                            "_token": "{{ csrf_token() }}",
                        },
                    })
                    .then(res => {
                        res.product_shopify_ids.forEach(id => {
                            $(`#delete-${id}`).hide();
                            $(`#deleting-${id}`).hide();
                            $(`#deleted-${id}`).show();
                            $(`#check-${id}`).prop('checked', false);
                            $('#check-all-mp').prop('disabled', false);
                        });
                    });
            }
        }
        deleteProductsAjax();
        setInterval(deleteProductsAjax, 15000);
    });
    $('#product_data').on('click', '.btn-mp-delete', function() {
        $('#modal-body').html(`<h5 style='text-align:center'>${$(this).data('name')}(${$(this).data('sku')})</h5><h5>This product will remove it from your Shopify store. Do you really want to delete it?</h5>`);
        $('#product_id').val($(this).data('myproductid'));
    })
    $('#product_data').on('click', '.edit-product', function() {
        window.open('http://{{Auth::user()->shopify_url}}/admin/products/' + $(this).data('shopifyid'), '_blank');
    });
    $('#product_data').on('click', '.vplist', function(e) {
        e.preventDefault();
        var idp= $(this).attr('data-view');
        if(!$(idp).hasClass("active")){
            $(".shoproductrow").removeClass("active");
            $(".productdatarow").removeClass("showp");
        }

        $(idp).toggleClass("active");
        $(this).parents(".productdatarow").toggleClass("showp");
    });
</script>
@endsection
