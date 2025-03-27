<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class KaasType extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlkaas';
	protected $table      = 'type';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
}