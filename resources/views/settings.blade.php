@extends('layouts.app')







@section('content')



<div class="indexContent" data-page_name="SETTINGS">

    <div class="maincontent">

        <div class="wrapinsidecontent">



        <div class="screen-settings">



            <div class="set-inputs">

                <h3>The Product Updates below are automated in Shopify:</h3>

                  

                <p class="inputs"><input type="checkbox" id="check-set1" value="" {{$settings->set1==0?'':'checked'}} /><label>Publish products when added to Shopify.</label></p><br><br><br>

<!--

                <p>

                    Notify the merchant when:



                </p>

                <p class="inputs not"><input type="checkbox" id="check-set2" value="" {{$settings->set2==0?'':'checked'}}/><label>Sending an order.</label>

                </p>



                <p class="inputs not"><input type="checkbox" id="check-set3" value="" {{$settings->set3==0?'':'checked'}}/><label>The product is not available.</label></p>

                <p class="inputs not"><input type="checkbox" id="check-set4" value="" {{$settings->set4==0?'':'checked'}}/><label>The cost changes.</label></p>

                <p class="inputs not"><input type="checkbox" id="check-set5" value="" {{$settings->set5==0?'':'checked'}}/><label>The stock changes.</label></p>

                <p>
-->
                    <strong> DEFAULT PROFIT CONFIGURATION (Percentage)</strong>

                </p>

                <p class="inputs2">

                    <input type="text" id="txt-value" value="{{$settings->set8}}" /> <span>%</span>

                    <label id="txt-value-error" class="text-danger"></label>

                </p>

            </div>

                <button class="bgVC colorBL" id="save-settings">Save</button>

            </div>





        </div>

    </div>

</div>



<script type="text/javascript">

$(document).ready(function() {

    $('#save-settings').click(function() {

        $.post('save-settings', {

            "_token": "{{ csrf_token() }}",

            "set1": $('#check-set1').is(':checked'),

            "set2": $('#check-set2').is(':checked'),

            "set3": $('#check-set3').is(':checked'),

            "set4": $('#check-set4').is(':checked'),

            "set5": $('#check-set5').is(':checked'),

			"set6": $('#radio-amount').is(':checked'),

			"set7": $('#radio-percentage').is(':checked'),

			"set8": $('#txt-value').val()

        }, function(data, status) {

          window.location.href = '{{url("/settings")}}';

        }).fail(function(xhr, status, error) {

            $.each(JSON.parse(xhr.responseText).errors, function(key, val) {

				$('#txt-value-error').text('');

                if(key=='set8'){

					$('#txt-value-error').text(val[0].replace("set8", "value"));

				}

			});

			

        });

    });



});

</script>

@endsection