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
        var imported_ids = "{{ $imported_ids }}";
        var myproduct_ids = "{{ $myproduct_ids }}";
        var shopify_ids = "{{ $shopify_ids }}";
        var shopify_url = "https://{{ Auth::User()->shopify_url }}/admin/products/";
        window.localStorage.setItem('imported_ids', imported_ids);
        window.localStorage.setItem('myproduct_ids', myproduct_ids);
        window.localStorage.setItem('shopify_ids', shopify_ids);
        window.localStorage.setItem('shopify_url', shopify_url);
    });
</script>
@endsection
