<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql';
	protected $table      = 'tier';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
}