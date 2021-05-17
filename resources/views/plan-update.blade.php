@extends('layouts.app')



@section('content')
<style type="text/css">
  ul.return-page{
    background: #fff;
    padding: 40px 20px;
    text-align: center;
    border-radius: 20px;
    width: 50%;
    margin: 0 auto;    
    height: 310px;
  }

  ul.return-page h2{margin-bottom: 20px;}
  ul.return-page li{
    list-style: none;
    width: 65%;
    margin: 0 auto;
    text-align: left;
    font-size: 18px;
    text-transform: capitalize;
  }
  ul.return-page li label{width:120px;}
  ul.return-page a{}
  ul.return-page a button{
background-color: #44b955;
    font-size: 20px;
    font-weight: 700;
    color: #ffffff;
    border: none;
    padding: 5px;
    width: 100%;
    max-width: 250px;
    border-radius: 10px;
    cursor: pointer;   
    margin-top: 30px; 
  }
  li.liButtons{margin-bottom: 20px;}
  li.liButtons button{
     font-family: Roboto;
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    color: #ffffff;
    text-decoration: none;
    display: grid;
    align-content: center;
    min-height: 42px;
    padding: 10px;
    max-width: 214px;
    margin-left: auto;
    margin-right: auto;
    margin-top: 21px;
    border-radius: 10px;
    border: none;
    cursor: pointer;   
  }
  button.leftButton {float: left; padding: 5px 20px;background: #f39ca2}
  button.rightButton {float: right;; padding: 5px 20px; background-color: #44b955;}  
</style>

<div class="indexContent" data-page_name="UPDATING PLAN">


        <div class="maincontent">


            <div class="wrapinsidecontent">
            	
            	<ul class="return-page">
                    <h2>You are updating your plan...</h2>
                    <li><label>Plan:</label> {{ app('request')->input('p') }}</li>
                    <li><label>Price:</label> USD$ {{ $price }} / Mo</li>
                    <li class='line'></li>
                    <li>Note: Accepting this payment it will be charging this price each month.</li>
                    <li class="liButtons">
                       <button class="leftButton">CANCEL</button></a> 
                       <button class="rightButton">ACCEPT</button></a>
                    </li>
            	</ul>

            	
            	
            </div>

        </div>
</div>  


<script type="text/javascript">
    $('.leftButton').click(function(){
        window.location.href = "{{url('/plans')}}";
    });

    $('.rightButton').click(function(){


            $.post('{{url("/plans/update")}}', {

                "_token": "{{ csrf_token() }}",

                'plan': "{{ app('request')->input('p') }}"

            }, function(data, status) {

                $('.token-error').hide();

                //window.location.href = "{{url('/plans')}}?update=true";
        window.location.href = "{{url('/plans/update-success')}}";

            }).fail(function(data) {

                $('.token-error').show();
         window.location.href = "{{url('/plans/update-failure')}}";

            });
    });    
</script>      




@endsection