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
                    <p>You can view here all the products you have added to your Shopify store.</p>
                </div>
            </div>
            @can("plan_view-my-products")
            <div class="myproducts" style="display: block;">
                <div class="checksend" style="float: left; padding: 10px;">
                    <input type="checkbox" id="check-all-mp" value="" data-mark="false">
                </div>
                <div>
                    <button class="btn-mp-delete-all alldeletebutton">Delete</button>
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
                                <img src="{{$pr->image_url}}">
                            </div>
                        </td>
                        <td data-label="PRODUCT NAME">
                            {{ $pr->name }}
                        </td>
                        <td data-label="COST GDS">
                            ${{$pr->price}}
                        </td>
                        <td data-label="PROFIT">
                            {{ $pr->profit }}%
                        </td>
                        <td data-label="RETAIL PRICE">
                            ${{number_format(100*$pr->price/ (100-$pr->profit), 2,'.','')}}
                        </td>
                        <td data-label="SKU">
                            {{$pr->sku}}
                        </td>
                        <td>
                            <button class="btn-mp-view viewbutton vplist" data-id="{{ $pr->id }}" data-view="#product{{ $pr->id }}">View</button>
                        </td>
                        <td>
                            <button class="btn-mp-delete deletebutton" id="delete-{{ $pr->id_shopify }}" data-myproductid="{{ $pr->id_shopify }}">Delete</button>
                            <button class="deletebutton" id="deleting-{{ $pr->id_shopify }}" data-myproductid="{{ $pr->id_shopify }}" style="display: none;">Deleting...</button>
                            <button class="deletebutton" id="deleted-{{ $pr->id_shopify }}" data-myproductid="{{ $pr->id }}" style="display: none;">Deleted</button>
                        </td>
                    </tr>
                    <tr class="shoproductrow" id="product{{ $pr->id }}">
                        <td></td>
                        <td colspan="8">
                            <div class="productlisthow">
                                <div class="productimage">
                                    <img src="{{$pr->image_url}}">
                                </div>
                                <div class="productdata">
                                    <h3>{{ $pr->name }}</h3>
                                    <p class="price">Price ${{number_format(100*$pr->price/ (100-$pr->profit), 2,'.','')}}</p>
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
                                        <button class="edit edit-product" data-shopifyid="{{ $pr->id_shopify }}">Edit on Shopify</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
            <div class="pagesize">
                <select name="PageSize" id="page_size">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

        </div>
    </div>
</div>


<!-- pagination -->
<div class="pagination">
    <!-- {{ $prods->appends(request()->query())->links() }} -->
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

</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#total_count').text("{{$total_count}}");
        $('#check-all-mp').click(function() {

            if (!$(this).data('mark')) {
                $('.checkbox').prop('checked', true);
                $(this).data('mark', true)
            } else {
                $('.checkbox').prop('checked', false);
                $(this).data('mark', false)
            }
        });

        $('.edit-product').click(function() {
            window.open('http://{{Auth::user()->shopify_url}}/admin/products/' + $(this).data('shopifyid'), '_blank');
        });

        $('.btn-mp-delete').click(function() {
            if (confirm('Are you sure to delete this product from shopify?')) {
                $(`#check-${$(this).data('myproductid')}`).prop('disabled', true);
                $(`#delete-${$(this).data('myproductid')}`).hide();
                $(`#deleting-${$(this).data('myproductid')}`).show();
                $(`#deleted-${$(this).data('myproductid')}`).hide();
                $.post('{{url("/delete-shopify-product")}}', {
                    "_token": "{{ csrf_token() }}",
                    product_id: $(this).data('myproductid')
                }, function(data, status) {
                    if (data.result) {
                        $(`#delete-${data.product_id}`).hide();
                        $(`#deleting-${data.product_id}`).hide();
                        $(`#deleted-${data.product_id}`).show();
                    }
                });
            }
        });


        $('.btn-mp-delete-all').click(function() {
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
            if (products.length) {
                if (confirm('Are you sure to delete these products from shopify?')) {
                    products.forEach(product => {
                        $(`#check-${product.product_shopify_id}`).prop('disabled', true);
                        $(`#delete-${product.product_shopify_id}`).hide();
                        $(`#deleting-${product.product_shopify_id}`).show();
                        $(`#deleted-${product.product_shopify_id}`).hide();
                    });
                    $.post('{{url("/delete-all-shopify-product")}}', {
                        "_token": "{{ csrf_token() }}",
                        products: products,
                    }, function(data, status) {});
                }
            } else {
                alert('At least one checkbox must be selected');
            }

        });
        var user_id = "{{Auth::user() ? Auth::user()->id : 0}}";

        function publishProductsAjax() {
            let product_ids = [];
            $("input.checkbox:checked").each(function(index, ele) {
                let product_id = $(ele).attr('id').split('-')[1];
                if ($(`#deleting-${product_id}`).is(":visible")) {
                    product_ids.push(product_id);
                }
            });
            if (user_id && product_ids.length) {
                $.ajax({
                        type: 'POST',
                        url: '/check-delete-shopify-products',
                        data: {
                            user_id: user_id,
                            product_shopify_ids: product_ids,
                            "_token": "{{ csrf_token() }}",
                        },
                    })
                    .then(res => {
                        res.product_shopify_ids.forEach(id => {
                            $(`#delete-${id}`).hide();
                            $(`#deleting-${id}`).hide();
                            $(`#deleted-${id}`).show();
                        });
                    });
            }
        }
        publishProductsAjax();
        setInterval(publishProductsAjax, 15000);
    });
</script>
@endsection
