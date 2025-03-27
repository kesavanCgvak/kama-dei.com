<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LiveAgentType extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlliveagent';
	protected $table      = 'type';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
}