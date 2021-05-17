<?php

namespace App\Libraries;

class Shipping
{
    public static function getShippingPrice(){
        return rand(4,12);
    }
}