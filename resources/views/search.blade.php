@extends('layouts.app')

@section('content')

<div class="indexContent" data-page_name="SEARCH PRODUCTS">
    <div id="celUITDiv" class="maincontent">
        <div ng-view class="wrapinsidecontent">
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let imported_ids = {!! $imported_ids !!};
        let myproduct_ids = {!! $myproduct_ids !!};
        let shopify_ids = "{{ $shopify_ids }}";
        let shopify_url = "https://{{ Auth::User()->shopify_url }}/admin/products/";
        window.localStorage.setItem('imported_ids', JSON.stringify(imported_ids));
        window.localStorage.setItem('myproduct_ids', JSON.stringify(myproduct_ids));
        window.localStorage.setItem('shopify_ids', shopify_ids);
        window.localStorage.setItem('shopify_url', shopify_url);
        $('#confirm').click(function () {
            let product_ids = [];
            let checkboxes = document.querySelectorAll('.check-product');
            checkboxes.forEach(checkbox => {
                if (checkbox.checked){
                    product_ids.push(checkbox.id.split('-')[1]);
                    checkbox.disabled = true;
                }
            });

            if (product_ids.length) {
                $('.all-add-products').css("display", "none");
                $('#selected-products').css("display", "none");
                $('#import-loading').css("display", "block");
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked){
                        $(`#add-${checkbox.id.split('-')[1]}`).text("Adding...");
                    }
                });
                $('.all-add-products').prop("disabled", true);
                $('.check-all-products').prop("checked", false);
                $('.check-all-products').prop("disabled", true);
                $.getJSON('/ajax',{
                    action:'import-products',
                    skus: JSON.stringify(product_ids),
                }, function(res) {
                    let imported_ids = JSON.parse(window.localStorage.getItem('imported_ids'));
                    let new_ids = [];
                    new_ids = imported_ids.concat(res.skus);
                    window.localStorage.setItem('imported_ids', JSON.stringify(new_ids));
                    res.skus.forEach(sku => {
                        $(`#add-${sku}`).hide();
                        $(`#edit-${sku}`).removeAttr('hidden');
                        $(`#check-${sku}`).prop("checked", false);
                    });
                    res.nonskus.forEach(sku => {
                        $(`#add-${sku}`).text('Add to Import List');
                        $(`#check-${sku}`).prop("checked", false);
                        $(`#check-${sku}`).prop("disabled", false);
                    });
                    $('.check-all-products').prop("checked", false);
                    $('.check-all-products').prop("disabled", false);
                    $('#select-all').css("color", '#44b955');
                    $('#select-all').text('Import successful');
                    $('#select-all').css("display", 'block');
                    setTimeout(() => {
                        $('#select-all').css("color", '#212529');
                        $('#select-all').text('Select All');
                        $('.all-add-products').css("display", 'block');
                        $('.all-add-products').prop("disabled", false);
                    }, 2000);
                    $('#selected-products').css("display", 'none');
                    $('#selected-products').text(0);
                    $('#import-loading').css("display", "none");
                })
            }
        })
    });
</script>
@endsection
