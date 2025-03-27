<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsumerUserPersonality extends Model
{
	//----------------------------------------------------
	public    $timestamps = false;
	//----------------------------------------------------
	protected $connection = 'mysql2';
	protected $table      = 'consumer_user_personality';
	protected $primaryKey = "consUserPersonalityId";
	//----------------------------------------------------
	public function consumerUserPersonality( $user, $organizationId, $lastUserId, $nickname ){
		$tmp = $this->where('consumerUserId', $user->id)->where('organizationId', $organizationId)->first();
		if($tmp==null){
			if($user->levelID==4){ 
				$tmp = new ConsumerUser;
				$tmp->consumerUserId = $user->id;
				$tmp->dateCreated    = date("Y-m-d H:i:s");;
				$tmp->lastUserId     = $lastUserId;
				$tmp->save();
			}
		}else{
			if($user->levelID!=4){ $this->where('consumerUserId', $user->id)->delete(); }
		}
	}
	//----------------------------------------------------
}
