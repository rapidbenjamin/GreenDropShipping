@extends('layouts.app')

@section('content')

<div class="indexContent" data-page_name="SETTINGS">
    <div class="maincontent">
        <div class="wrapinsidecontent">
            <div class="screen-settings">
                <div class="set-inputs">
                    <h3>The Product Updates below are automated in Shopify:</h3>
                    <div class="setting-checkboxes">
                        <p class="inputs">
                            <input type="checkbox" id="check-set1" value="" {{$settings->set1==0?'':'checked'}} />
                            <label>Set the product as active when published to your Shopify store</label>
                        </p>
                        <p class="inputs">
                            <input type="checkbox" id="sync-inventory" value="" {{$settings->sync_inventory==0?'':'checked'}} />
                            <label>Automated Inventory Syncing.</label>
                        </p>
                        <p class="inputs">
                            <input type="checkbox" id="sync-price" value="" {{$settings->sync_price==0?'':'checked'}} />
                            <label>Automated Price Syncing.</label>
                        </p>
                    </div>
                    <p><strong>Default Profit Configuration (Percentage)</strong></p>
                    <p class="profit">
                        <input type="number" id="profit" value="{{$settings->set8}}" min="0"/> <span>%</span>
                        <label id="profit-error" class="text-danger"></label>
                    </p>
                    <p><strong>Default Inventory Threshold (Min-threshold is 20)</strong></p>
                    <p class="inventory_threshold">
                        <input type="number" min="20" id="inventory-threshold" value="{{$settings->inventory_threshold}}" />
                        <label id="inventory-threshold-error" class="text-danger"></label>
                    </p>
                </div>
                <div class="d-flex align-items-center">
                    <button class="greenbutton" id="save-settings">Update</button>
                    <span class="h6 ml-3 my-0 d-none" id="update-success"><a href="#">Your settings have been updated.</a></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#save-settings').click(function () {
            $.post('save-settings', {
                "_token": "{{ csrf_token() }}",
                "set1": $('#check-set1').is(':checked'),
                "set8": $('#profit').val(),
                "inventory_threshold": parseInt($('#inventory-threshold').val()),
                "sync_inventory": $('#sync-inventory').is(':checked'),
                "sync_price": $('#sync-price').is(':checked'),
            }, function(data, status) {
                $('#update-success').removeClass('d-none');
                $('#update-success').addClass('d-flex align-items-center');
                setTimeout(() => {
                    $('#update-success').removeClass('d-flex align-items-center');
                    $('#update-success').addClass('d-none');
                }, 1500);
            }).fail(function(xhr, status, error) {
                $.each(JSON.parse(xhr.responseText).errors, function(key, val) {
                    $('#txt-value-error').text('');
                    if (key == 'set8') {
                        $('#profit-error').text(val[0].replace("set8", "value"));
                    }
                    if (key == 'inventory_threshold') {
                        $('#inventory-threshold-error').text(val[0].replace("inventory_threshold", "value"));
                    }
                });
            });
        });

        $('#inventory-threshold').blur(function (e) {
            if (e.target.value < 20) {
                $('#inventory-threshold').val(20);
            } else {
                $('#inventory-threshold').val(parseInt(e.target.value));
            }
        })
    });
</script>

@endsection