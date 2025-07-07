<?php 
namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganizationModel extends Model {

	public    $timestamps = false;
	protected $connection = 'mysql';
	protected $table      = 'organization_model';
//	protected $primaryKey = "id";

	//--------------------------------------------------------------------
}
