<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsumerUserPersonalityLog extends Model
{
	//----------------------------------------------------
	public    $timestamps = false;
	//----------------------------------------------------
	protected $connection = 'mysql2';
	protected $table      = 'consumer_user_personality_log';
	protected $primaryKey = "consUserPersonalityId_log";
	//----------------------------------------------------
    public function persona(){ 
		return $this->belongsTo('App\Personality', 'parentPersonaId_old', 'personalityId')
			->select(
				'*',
				\DB::raw("(select name from kamadeiep.portal where organization_id=ownerId and unknownPersonalityId=personalityId limit 1) as portalname")
			);
	}
	//----------------------------------------------------
}
