<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentSession extends Model
{
	protected $table = 'payment_session';
	public $timestamps = true;
}