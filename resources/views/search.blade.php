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
        window.localStorage.setItem('imported_ids', imported_ids);
    });
</script>
@endsection
