<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserKey extends Model
{
	public    $timestamps = false;

	protected $table = 'user_key';
	protected $primaryKey = "user_id";

	protected $salt = "K@m@Key!!";
	public function hash($pass){ return md5($pass.$this->salt); }
	//--------------------------------------------------------------------
}
