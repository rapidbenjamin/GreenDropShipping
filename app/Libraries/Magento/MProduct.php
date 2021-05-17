<?php
namespace App\Libraries\Magento;

class MProduct{
    const ATTRIBUTE_MANUFACTURER = 'manufacturer';

    public static function get($criteria = [],$pageSize = 10, $page = 1){
        $api = MagentoApi::getInstance();
        return $api->query('GET', 'products',
            array_merge($criteria,['searchCriteria[pageSize]' => $pageSize,'searchCriteria[currentPage]' => $page]));
    }

    public static function getBySKU($sku){
        $api = MagentoApi::getInstance();
        return $api->query('GET', 'products/'. $sku);
    }
    
    public static function attributes($code){
        $api = MagentoApi::getInstance();
        return $api->query('GET', 'products/attributes/'.$code.'/options');
    }

    public static function getStock($sku){
        $api = MagentoApi::getInstance();
        return $api->query('GET', 'stockItems/'.$sku);
    }
}