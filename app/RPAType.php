<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RPAType extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysqlRPA';
	protected $table      = 'type';
	protected $primaryKey = "id";
}