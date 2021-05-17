<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportList extends Model
{
	protected $table = 'import_list';
	public $timestamps = true;

	public function product()
    {
        return $this->belongsTo('App\Products','id_product');
    }
}