<?php 
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogEmailsConfig extends Model {
	//---------------------------------------------------------------
	protected $table      = 'logEmailsConfig';
	protected $primaryKey = "id";
	public    $timestamps = false;
	//---------------------------------------------------------------
}