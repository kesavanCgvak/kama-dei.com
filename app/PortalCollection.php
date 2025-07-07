<?php 
namespace App;

use Illuminate\Database\Eloquent\Model;

class PortalCollection extends Model {

	public    $timestamps = false;
	protected $connection = 'mysql';
	protected $table      = 'portal_collection';
//	protected $primaryKey = "id";

	//--------------------------------------------------------------------
}
