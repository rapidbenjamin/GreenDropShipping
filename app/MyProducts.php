<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MyProducts extends Model
{
	use SoftDeletes; 

	protected $table = 'my_products';
	public $timestamps = true;
	
	/**
     * Get the post that owns the comment.
     */
    public function import_list()
    {
        return $this->belongsTo('App\ImportList','id_imp_product','id');
    }
}