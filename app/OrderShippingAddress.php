<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderShippingAddress extends Model
{
	protected $table = 'order_shipping_address';
	public $timestamps = false;
}