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
  }

  ul.return-page h2{margin-bottom: 20px;}

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
</style>

<div class="indexContent" data-page_name="YOUR PLAN HAS NOT BEEN UPDATED">


        <div class="maincontent">


            <div class="wrapinsidecontent">
            	
            	<ul class="return-page">
                <H2>There was a problem updating your plan.  Please try again later.</H2>

            	<a href="/plans"><button>CONTINUE</button></a>
            	</ul>
            </div>

        </div>
</div>        

@endsection