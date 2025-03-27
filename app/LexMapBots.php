<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LexMapBots extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqllex';
	protected $table      = 'mapping_bot';
	protected $primaryKey = "bot_id";

	protected $fillable = 
		['bot_id','mappingName','bot_name','bot_alias','ownerId','personaiD','lexPersonalityID','lexUserID','publish_status','user_id','last'];
	//--------------------------------------------------------------------
	protected function myQuery($orgID, $field, $value){
		if( $value=='' ){
			return $this
				->leftJoin('kamadeiep.organization_ep as ORG'  , 'mapping_bot.ownerId'         , '=', 'ORG.organizationId')
				->leftJoin('kamadeikb.personality     as PRS'  , 'mapping_bot.personaiD'       , '=', 'PRS.personalityId' )
				->leftJoin('kamadeikb.personality     as lxPRS', 'mapping_bot.lexPersonalityID', '=', 'lxPRS.personalityId')
				->leftJoin('kamadeiep.user            as USR'  , 'mapping_bot.lexUserID'       , '=', 'USR.id')
				->where(
					function($q) use($orgID){ 
						if($orgID==0){ return $q;}
						else{ return $q->where('mapping_bot.ownerId', $orgID); } 
					}
				)
				->select(
					'mapping_bot.*',
					'PRS.personalityName as personaName',
					'ORG.organizationShortName as organizationShortName',
					'lxPRS.personalityName as personalityName',
					'USR.userName as lexUserName'
				);
		}else{
			return $this
				->leftJoin('kamadeiep.organization_ep as ORG', 'mapping_bot.ownerId'  , '=', 'ORG.organizationId')
				->leftJoin('kamadeikb.personality     as PRS'  , 'mapping_bot.personaiD'       , '=', 'PRS.personalityId' )
				->leftJoin('kamadeikb.personality     as lxPRS', 'mapping_bot.lexPersonalityID', '=', 'lxPRS.personalityId')
				->leftJoin('kamadeiep.user            as USR'  , 'mapping_bot.lexUserID'       , '=', 'USR.id')
				->where(
					function($q) use($orgID){ 
						if($orgID==0){ return $q;}
						else{ return $q->where('mapping_bot.ownerId', $orgID); } 
					}
				)
				->where(
					function($q) use ($value){ 
						return $q
							->where('ORG.organizationShortName' , 'like', "%{$value}%")
							->orwhere('PRS.personalityName' , 'like', "%{$value}%")
							->orwhere('lxPRS.personalityName' , 'like', "%{$value}%")
							->orwhere('USR.userName' , 'like', "%{$value}%")
							->orwhere('mapping_bot.publish_status' , 'like', "%{$value}%")
							->orwhere('mapping_bot.mappingName' , 'like', "%{$value}%")
							->orwhere('mapping_bot.bot_name' , 'like', "%{$value}%")
							->orwhere('mapping_bot.bot_alias' , 'like', "%{$value}%");
					}
				)
				->select(
					'mapping_bot.*',
					'PRS.personalityName as personaName',
					'ORG.organizationShortName as organizationShortName',
					'lxPRS.personalityName as personalityName',
					'USR.userName as lexUserName'
				);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $field='', $value=''){
		$data = null;
		$data = $this->myQuery($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
		if($data->isEmpty()){ return null; }
		$retVal = [];
		foreach( $data as $key=>$tmp ){ 
			if($tmp->ownerId==null || $tmp->ownerId==0){ $tmp->organizationShortName=env('BASE_ORGANIZATION'); }
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
	public static function myMap($bot_id){
		return self::where('bot_id', $bot_id)
			->leftJoin('kamadeiep.organization_ep as ORG'  , 'mapping_bot.ownerId'         , '=', 'ORG.organizationId')
			->leftJoin('kamadeikb.personality     as PRS'  , 'mapping_bot.personaiD'       , '=', 'PRS.personalityId' )
			->leftJoin('kamadeikb.personality     as lxPRS', 'mapping_bot.lexPersonalityID', '=', 'lxPRS.personalityId')
			->leftJoin('kamadeiep.user            as USR'  , 'mapping_bot.lexUserID'       , '=', 'USR.id')
			->select(
				'mapping_bot.*',
				'PRS.personalityName as personaName',
				'ORG.organizationShortName as organizationShortName',
				'lxPRS.personalityName as lexPersonalityName',
				'USR.userName as lexUserName'
			)->first();
	}
	//--------------------------------------------------------------------	
}