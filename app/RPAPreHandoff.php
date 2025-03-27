<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class RPAPreHandoff extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlRPA';
	protected $table      = 'pre_handoff';
	protected $primaryKey = "pre_handoff_message_id";
	//--------------------------------------------------------------------
}