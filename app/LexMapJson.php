<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LexMapJson extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqllex';
	protected $table      = 'mapping_json';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
	protected $fillable = ['id', 'mapId', 'type', 'name', 'version', 'json'];
	//--------------------------------------------------------------------
}