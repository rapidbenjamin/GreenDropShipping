@extends('layouts.app')



@section('content')
<style type="text/css">
    ul.return-page {
        background: #fff;
        padding: 40px 20px;
        text-align: center;
        border-radius: 20px;
        width: 50%;
        margin: 0 auto;
    }

    ul.return-page h2 {
        margin-bottom: 20px;
    }

    ul.return-page li {
        list-style: none;
        width: 65%;
        margin: 0 auto;
        text-align: left;
        font-size: 18px;
        text-transform: capitalize;
    }

    ul.return-page li label {
        width: 120px;
    }

    ul.return-page a {}

    ul.return-page a button {
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

<div class="indexContent" data-page_name="YOUR PLAN HAS BEEN UPDATED">


    <div class="maincontent">


        <div class="wrapinsidecontent">

            <ul class="return-page">
                <h2>CONGRATULATIONS</h2>
                <li><label>Date:</label> {{$user->plan_updated_at}}</li>
                <li><label>Transaction:</label> {{$user->id_recurring_application}}</li>
                <li><label>Shopify Url:</label> {{$user->shopify_url}}</li>
                <li><label>Current Plan:</label> {{$user->plan}}</li>
                <a href="/plans"><button class="greenbutton">CONTINUE</button></a>
            </ul>



        </div>

    </div>
</div>

@endsection
