<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class KaasMapJson extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlkaas';
	protected $table      = 'mapping_json';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
	protected $fillable = ['id', 'mapId', 'type', 'name', 'version', 'json'];
	//--------------------------------------------------------------------
}