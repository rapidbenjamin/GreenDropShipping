@extends('layouts.app')
@section('content')

<div class="indexContent" data-page_name="ADMIN MERCHANTS">
    <div class="maincontent">
        <div class="wrapinsidecontent">

            <div class="actions">
                <button class="exporsel">Export CSV</button>
                <div></div>
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

            <div class="orders">
                <h5>Search </h5>
                <table class="tableorders mb-5" cellspacing="0">
                    <thead>
                        <tr>
                            <th>
                                <input type="text" id="merchant_name" class="merchant-search" list="names" placeholder="MERCHANT NAME">
                                <datalist id="names">
                                    <div id="name_data"></div>
                                </datalist>
                            </th>
                            <th>
                                <input type="text" id="merchant_email" class="merchant-search" list="emails" placeholder="EMAIL">
                                <datalist id="emails">
                                    <div id="email_data"></div>
                                </datalist>
                            </th>
                            <th>
                                <input type="text" id="merchant_url" class="merchant-search" list="urls" placeholder="SHOPIFY URL">
                                <datalist id="urls">
                                    <div id="url_data"></div>
                                </datalist>
                            </th>
                            <th>
                                <select name="Plan" id="merchant_plan" class="merchant-search">
                                    <option value="" style="color: transparent">Plan</option>
                                    <option value="0">None</option>
                                    <option value="free">Free</option>
                                    <option value="basic">Basic</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </th>
                            <th>
                                <select name="Active" id="merchant_active" class="merchant-search">
                                    <option value="" style="color: transparent">Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </th>
                        </tr>
                    </thead>
                </table>
                <table class="greentable tableorders" cellspacing="0">
                    <thead>
                        <tr>
                            <th>
                                MERCHANT NAME
                            </th>
                            <th>
                                EMAIL
                            </th>
                            <th>
                                SHOPIFY URL
                            </th>
                            <th>
                                PLAN
                            </th>
                            <th>
                                ACTIVE
                            </th>
                            <th>
                                ACTIONS
                            </th>
                        </tr>
                    </thead>
                    <tbody id="merchant_data">

                        @php $k = 0 @endphp
                        @foreach($merchants_list as $ml)
                        @if($k == 0)
                        @php
                        $back = 'transparent';
                        $k = 1;
                        @endphp
                        @else
                        @php
                        $back = '';
                        $k = 0;
                        @endphp

                        @endif
                        <tr class="merchantrow">
                            <td data-label="MERCHANT NAME">
                                {{$ml->name}}
                            </td>
                            <td data-label="EMAIL">
                                {{$ml->email}}
                            </td>
                            <td data-label="SHOPIFY URL">
                                @if($ml->shopify_url) {{$ml->shopify_url}} @else --- @endif
                            </td>
                            <td data-label="PLAN">
                                {{$ml->plan}}
                            </td>
                            <td data-label="ACTIVE">
                                <input type="checkbox" name="switch-button" id="switch-label{{$ml->id}}" data-merchantid="{{$ml->id}}" data-toggle="modal" data-target="#delete-product-modal" class="switch-button__checkbox change-status" @if($ml->active == 1)checked @endif>
                            </td>
                            <td class="btngroup">

                                <button class="view detail-merchants" data-merchantid="{{$ml->id}}">View</button>
                                <button class="payorder orders-customers" data-merchant="{{$ml->name}}">Orders</button>

                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

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
        </div>
    </div>
</div>
<input type="text" id="user_id" hidden>
<script type="text/javascript">
    $(document).ready(function() {
        $('#total_count').text("{{$total_count}}");
        $('.exporsel').click(function() {
            window.location.href = encodeURI('{{url("/admin/merchants/exportCSV")}}');
        });
        $('#confirm').click(function() {
            var user_id = $('#user_id').val();
            var status = $(`#switch-label${user_id}`).is(':checked') ? false : true;
            var parameters = {
                action: 'change-user-status',
                user_id: user_id,
                active: status ? 1 : 0,
            }
            $.getJSON(ajax_link, parameters, function(res) {
                $(`#switch-label${user_id}`).prop('checked', status);
            })
        });

    });
    $('#merchant_data').on('click', 'input.change-status', function() {
        let status = $(this).is(':checked')
        if ($(this).is(':checked')) $(this).prop('checked', false);
        else $(this).prop('checked', true);
        $('#user_id').val($(this).data('merchantid'));
        $('#modal-body').html(`<h5>Are your sure to ${$(this).is(':checked') ? 'disable' : 'enable'} this merchant?</h5>`);
    })
</script>
@endsection
