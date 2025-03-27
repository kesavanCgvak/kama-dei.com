<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class KaasStructure extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlkaas';
	protected $table      = 'structure';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
}