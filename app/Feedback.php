<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model {
	//---------------------------------------------------------------
	use Encryptable;
	//---------------------------------------------------------------
	protected $connection = 'mysqllog';
	protected $table      = 'feedback';
	protected $primaryKey = "msg_id";
	public    $timestamps = false;
	//---------------------------------------------------------------
	protected $encryptable = [
	];
	//---------------------------------------------------------------
}