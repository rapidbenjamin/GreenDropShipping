<?php
namespace App\Libraries\Magento;

class MCategory{
    const ATTRIBUTE_MANUFACTURER = 'manufacturer';

    public static function get($criteria = []){
        $api = MagentoApi::getInstance();
        return $api->query('GET', 'categories',$criteria);
    }
}