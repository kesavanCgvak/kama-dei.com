<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LiveAgentStructure extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlliveagent';
	protected $table      = 'structure';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
}