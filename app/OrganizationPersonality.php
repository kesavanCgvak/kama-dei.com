<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class OrganizationPersonality extends Model {

	public    $timestamps = false;
	protected $connection = 'mysql2';
	protected $table      = 'organization_personality';
	protected $primaryKey = "organizationPersonalityId";

}
