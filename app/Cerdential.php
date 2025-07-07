<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cerdential extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql';
	protected $table      = 'cerdential';
	//--------------------------------------------------------------------
}