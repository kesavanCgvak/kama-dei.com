<?php 
namespace App;

use Illuminate\Database\Eloquent\Model;

class KamaLogSetting extends Model {
	//---------------------------------------------------------------
	protected $connection = 'mysqllog';
	protected $table      = 'kama_log_setting';
	protected $primaryKey = "id";
	public    $timestamps = false;
	//---------------------------------------------------------------
}