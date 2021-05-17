<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductListController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data = [
    	'oauth_consumer_key' => '0fp5ly25wpu0xn0oh4hsjoearl64opo5',
    	'oauth_nonce' => md5(uniqid(rand(), true)),
    	'oauth_signature_method' => 'HMAC-SHA1',
    	'oauth_timestamp' => time(),
    	'oauth_token' => 'xfneydf1sgt6y2lemyn6m5ildo0pq91a',
    	'oauth_version' => '1.0',
        ];
        $id=20987;
        $data['oauth_signature'] = $this->sign('GET', 'https://magento-shopify.greendropship.com/rest/V1/customers/' . $id,$data);
        //dd($data);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => 'https://magento-shopify.greendropship.com/rest/V1/customers/'.$id,
        	CURLOPT_HTTPHEADER => [
        		'Authorization: OAuth ' . http_build_query($data, '', ',')
        	]
        ]);
        $result = curl_exec($curl);
        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
            echo 'Took ', $info['total_time'], ' seconds to send a request to ', $info['url'], "\n";
        }
        curl_close($curl);
        dd($result);
        return 'hola mundo';
    }
    public function sign($method, $url, $data){
	    $url = $this->urlEncodeAsZend($url);
	    $data = $this->urlEncodeAsZend(http_build_query($data, '', '&'));
	    $data = implode('&', [$method, $url, $data]);
	    $secret = implode('&', ["827rr28fjw2oiyhmovim2yisubd7lkwt", "lzwfv3sgncw5mcx13hpmf9eq799n8i1w"]);
	    return base64_encode(hash_hmac('sha1', $data, $secret, true));
    }
    
    public function urlEncodeAsZend($value){
	    $encoded = rawurlencode($value);
	    $encoded = str_replace('%7E', '~', $encoded);
	    return $encoded;
    }
}
