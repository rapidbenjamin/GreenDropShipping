<?php 

namespace App\Libraries\Magento;

use Illuminate\Support\Facades\Log;

class MagentoApi {
    private static $instance;

    private function __construct() {

    }

    public static function getInstance()
   {
      if (!self::$instance instanceof self)
      {
         self::$instance = new self;
      }

      return self::$instance;
   }

    public function query($method,$endpoint,$criteria = [], $raw = [])
    {
        ksort($criteria);

        $data  =  array_merge($this->getAuthorizationData(), $criteria);

        $data['oauth_signature'] = $this->sign($method, env('URL_MAGENTO') . $endpoint, $data);

        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $raw,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => env('URL_MAGENTO') . $endpoint .= '?' . http_build_query($criteria),
        	CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: OAuth ' . http_build_query($data, '', ','),
                //'Content-Length: ' . strlen($data)
            )
        ]);

        $result = curl_exec($curl);

        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
        }

        curl_close($curl);

        return $result;
    }

     public function getAuthorizationData()
     {
        return array(
                'oauth_consumer_key' => env('CONSUMER_KEY'),
            	'oauth_nonce' => md5(uniqid(rand(), true)),
            	'oauth_signature_method' => 'HMAC-SHA1',
            	'oauth_timestamp' => time(),
            	'oauth_token' => env('ACCESS_TOKEN'),
            	'oauth_version' => '1.0'
            );
    }

    public function sign($method, $url,$data)
    {
	    $url = $this->urlEncodeAsZend($url);
	    $data = $this->urlEncodeAsZend(http_build_query($data, '', '&'));
	    $data = implode('&', [$method, $url, $data]);
	    $secret = implode('&', [env('CONSUMER_SECRET'), env('ACCESS_TOKEN_SECRET')]);

	    return base64_encode(hash_hmac('sha1', $data, $secret, true));
    }

    public function urlEncodeAsZend($value)
    {
	    $encoded = rawurlencode($value);
	    $encoded = str_replace('%7E', '~', $encoded);

	    return $encoded;
    }
}
