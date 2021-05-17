<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class ShopifyBulk extends Model
{
	//use SoftDeletes; 
	protected $table = 'shopify_bulk';
	public $timestamps = true;
}