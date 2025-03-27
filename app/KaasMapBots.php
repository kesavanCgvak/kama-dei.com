<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class KaasMapBots extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlkaas';
	protected $table      = 'mapping_bot';
	protected $primaryKey = "bot_id";

	protected $fillable = 
		['bot_id','mappingName','bot_name','bot_alias','ownerId','personaiD','kaasPersonalityID','kaasUserID','publish_status','user_id','last'];
	//--------------------------------------------------------------------
	protected function myQuery($orgID, $field, $value){
		if( $value=='' ){
			return $this
				->leftJoin('kamadeiep.organization_ep as ORG'  , 'mapping_bot.ownerId'         , '=', 'ORG.organizationId')
				->leftJoin('kamadeiep.portal as portal', 'mapping_bot.portal_id', '=', 'portal.id' )
				->leftJoin('structure as structure', 'mapping_bot.structure_id', '=', 'structure.id')
				->leftJoin('kamadeikb.organization_personality as o_prs', function($join){
					$join->on('ORG.organizationId', '=', 'o_prs.organizationId')
						->where('o_prs.is_default', 1);
				})
				->leftJoin('kamadeikb.personality as personality', 'o_prs.personalityId', '=', 'personality.personalityId')
//				->leftJoin('kamadeikb.personality as personality', 'portal.unknownPersonalityId', '=', 'personality.personalityId')
				->where(
					function($q) use($orgID){ 
						if($orgID==0){ return $q;}
						else{ return $q->where('mapping_bot.ownerId', $orgID); } 
					}
				)
				->select(
					'mapping_bot.*',
					'ORG.organizationShortName as organizationShortName',
					'portal.name as portalName',
					'personality.personalityName as personaName',
//					'kaasPRS.personalityName as personalityName',
					'structure.name as structureName'
				);
		}else{
			return $this
				->leftJoin('kamadeiep.organization_ep as ORG'  , 'mapping_bot.ownerId'         , '=', 'ORG.organizationId')
				->leftJoin('kamadeiep.portal as portal', 'mapping_bot.portal_id', '=', 'portal.id' )
				->leftJoin('structure as structure', 'mapping_bot.structure_id', '=', 'structure.id')
				->leftJoin('kamadeikb.organization_personality as o_prs', function($join){
					$join->on('ORG.organizationId', '=', 'o_prs.organizationId')
						->where('o_prs.is_default', 1);
				})
				->leftJoin('kamadeikb.personality as personality', 'o_prs.personalityId', '=', 'personality.personalityId')
//				->leftJoin('kamadeikb.personality as personality', 'portal.unknownPersonalityId', '=', 'personality.personalityId')
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
							->orwhere('portal.name' , 'like', "%{$value}%")
							->orwhere('personality.personalityName' , 'like', "%{$value}%")
							->orwhere('structure.name' , 'like', "%{$value}%")
							->orwhere('mapping_bot.publish_status' , 'like', "%{$value}%")
							->orwhere('mapping_bot.mappingName' , 'like', "%{$value}%")
							->orwhere('mapping_bot.bot_name' , 'like', "%{$value}%")
							->orwhere('mapping_bot.bot_alias' , 'like', "%{$value}%");
					}
				)
				->select(
					'mapping_bot.*',
					'ORG.organizationShortName as organizationShortName',
					'portal.name as portalName',
					'personality.personalityName as personaName',
					'structure.name as structureName'
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
			->leftJoin('kamadeiep.portal as portal', 'mapping_bot.portal_id', '=', 'portal.id' )
			->leftJoin('structure as structure', 'mapping_bot.structure_id', '=', 'structure.id')
			->leftJoin('kamadeikb.organization_personality as o_prs', function($join){
				$join->on('ORG.organizationId', '=', 'o_prs.organizationId')
					->where('o_prs.is_default', 1);
			})
			->leftJoin('kamadeikb.personality as personality', 'o_prs.personalityId', '=', 'personality.personalityId')
			->leftJoin('kamadeikb.personality as prsnlty', 'portal.unknownPersonalityId', '=', 'prsnlty.personalityId')
/*
			->leftJoin('kamadeiep.organization_ep as ORG'  , 'mapping_bot.ownerId'         , '=', 'ORG.organizationId')
			->leftJoin('kamadeikb.personality     as PRS'  , 'mapping_bot.personaiD'       , '=', 'PRS.personalityId' )
			->leftJoin('kamadeikb.personality     as kaasPRS', 'mapping_bot.kaasPersonalityID', '=', 'kaasPRS.personalityId')
			->leftJoin('kamadeiep.user            as USR'  , 'mapping_bot.kaasUserID'       , '=', 'USR.id')
*/
			->select(
				'mapping_bot.*',
				'ORG.organizationShortName as organizationShortName',
				'portal.name as portalName',
//				'personality.personalityName as personaName',
				\DB::raw("if(prsnlty.personalityName is null, personality.personalityName, prsnlty.personalityName) as personaName"),
				'structure.name as structureName'
/*
				'mapping_bot.*',
				'PRS.personalityName as personaName',
				'ORG.organizationShortName as organizationShortName',
				'kaasPRS.personalityName as kaasPersonalityName',
				'USR.userName as kaasUserName'
*/
			)->first();
	}
	//--------------------------------------------------------------------	
}