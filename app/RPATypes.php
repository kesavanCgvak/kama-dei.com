<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RPATypes extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysqlRPA';
	protected $table      = 'bot_type';
	protected $primaryKey = "id";
}