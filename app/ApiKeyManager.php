<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiKeyManager extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql';
	protected $table      = 'api_key_manager';
	//--------------------------------------------------------------------
}