<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RPAStructure extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysqlRPA';
	protected $table      = 'structure';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
}