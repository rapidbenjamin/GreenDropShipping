@extends('layouts.app')



@section('content')


<style type="text/css">
    .adminorders-settings {
        background-color: #fff;
        width: 98% !important;
        padding: 15px;
        margin: 20px 1%;
    }

    .adminCtrl {
        float: left;
        margin: 0 20px;
    }

    .adminCtrl input {
        text-align: center;
        border: solid 2px #ddd;
        padding: 5px;
        color: #989898;
        font-weight: bold;
    }

    .adminCtrl select {
        text-align: center;
        border: solid 2px #ddd;
        padding: 5px;
        color: #989898;
        font-weight: bold;
    }

    .adminCtrl button {
        text-align: center;
        border: solid 2px #fff;
        padding: 7px 30px;
        color: #fff;
        font-weight: bold;
        font-size: 16px;
        letter-spacing: 2px;
    }

    .adminCtrl button:hover {
        background-color: #89B73D;
    }

    .adminCtrl label {
        padding: 5px 10px;
        color: #989898;
        font-size: 19px;
        font-weight: bold;
    }

    ::placeholder {
        color: #989898;
    }


    .adminorders-settings {
        float: left;
        margin-bottom: 20px;
        width: 100%
    }

    img {
        width: 100%;
    }

    .adminorders-orders {
        float: left;
        width: 100%;
        margin: 100px 0 20px;
    }

    .admin-orders-titles {
        list-style: none;
        float: left;
        width: 100%;
        font-weight: bold;
    }

    .admin-orders-titles li {
        float: left;
        width: 14%;
        text-align: center;
    }

    .admin-orders-data {
        list-style: none;
        float: left;
        width: 100%;
        margin: 0;
        padding: 10px;
    }

    .admin-orders-data li {
        float: left;
        width: 14%;
        text-align: center;
    }

    .admin-orders-data li span {
        padding: 5px 30px;
    }
</style>




<div class="container indexContent" data-page_name="ADMIN LOGS">
    
    <ul style="display: flex;list-style-type: none;">
        @foreach($logs as $log)
        <li style="padding:15px;background-color: #E2CD9E;margin:5px;">
            <a style="color:black" href="{{url('admin/logs/download').'/?log='.$log}}">{{ $log}}</a>
        </li>
        @endforeach
    </ul>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>

<script type="text/javascript">

</script>
@endsection