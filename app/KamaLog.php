<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class KamaLog extends Model {
	//---------------------------------------------------------------
	use Encryptable;
	//---------------------------------------------------------------
	protected $connection = 'mysqllog';
	protected $table      = 'kama_log';
	protected $primaryKey = "msg_id";
	public    $timestamps = false;
	//---------------------------------------------------------------
	protected $encryptable = [
		'raw_msg','msg'
	];
	//---------------------------------------------------------------
	public static function getSignInIDs($searc){
		$retVal = [];
/*
		$ids = self::select('signin_id')
//				->where('msg',  'like', "%{$searc}%")
				->groupBy('signin_id')
				->get()
				->filter(function($record) use($searc){
					if(strpos($record->msg, $searc)!==false){ return $record; }
				});
*/
		$ids = self::all()
				->filter(function($record) use($searc){
					if(strpos($record->msg, $searc)!==false){ return $record; }
				});
		if($ids->isEmpty()){ return null; }
		foreach( $ids as $id ){ if($id->signin_id && !in_array($id->signin_id, $retVal)){ $retVal[] = $id->signin_id; } }
		return $retVal;
	}
}