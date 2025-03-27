<?php
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
//-------------------------------------------------------------
class SensitivePassword extends Model {
	//---------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysql';
	protected $table      = 'sensitive_password';
	protected $primaryKey = "userId";
	//---------------------------------------------------------
}
//-------------------------------------------------------------
