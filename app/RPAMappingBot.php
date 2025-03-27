<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RPAMappingBot extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysqlRPA';
	protected $table      = 'mapping_bot';
	protected $primaryKey = "bot_id";
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $field='', $value='', $portalID=0){
		$data = null;
		$data = $this->myQuery($orgID, $field, $value, $portalID)->orderBy($sort, $order)->get()->forPage($page, $perPage);
		if($data->isEmpty()){ return null; }
		$retVal = [];
		foreach( $data as $key=>$tmp ){ 
			if($tmp->ownerId==null || $tmp->ownerId==0){ $tmp->organizationShortName=env('BASE_ORGANIZATION'); }
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
	protected function myQuery($orgID, $field, $value, $portalID=0){
		return $this
			->leftJoin('kamadeiep.organization_ep as ORG'    , 'mapping_bot.ownerId'     , '=', 'ORG.organizationId'  )
			->leftJoin('kamadeiep.portal as portal'          , 'mapping_bot.portal_id'   , '=', 'portal.id'           )
			->leftJoin('bot_type as botType'                 , 'mapping_bot.bot_type_id' , '=', 'botType.id'          )
			->leftJoin('structure as structure'              , 'mapping_bot.structure_id', '=', 'structure.id'        )
			->leftJoin('kamadeikb.organization_personality as o_prs', function($join){
				$join->on('ORG.organizationId', '=', 'o_prs.organizationId')
					->where('o_prs.is_default', 1);
			})
			->leftJoin('kamadeikb.personality as personality', 'o_prs.personalityId'     , '=', 'personality.personalityId')
//				->leftJoin('kamadeikb.personality as personality', 'portal.unknownPersonalityId', '=', 'personality.personalityId')
			->where(
				function($q) use($orgID){ 
					if($orgID==0){ return $q;}
					else{ return $q->where('mapping_bot.ownerId', $orgID); } 
				}
			)
			->where(function($q) use($portalID){
				if($portalID==0) return $q;
				return $q->where('portal_id', $portalID);
			})
			->where(
				function($q) use ($value){ 
					if($value==""){ return $q; }
					return $q
						->where('ORG.organizationShortName'    , 'like', "%{$value}%")
						->orwhere('portal.name'                , 'like', "%{$value}%")
						->orwhere('personality.personalityName', 'like', "%{$value}%")
						->orwhere('structure.name'             , 'like', "%{$value}%")
						->orwhere('mapping_bot.publish_status' , 'like', "%{$value}%")
						->orwhere('mapping_bot.mappingName'    , 'like', "%{$value}%")
						->orwhere('mapping_bot.bot_name'       , 'like', "%{$value}%")
						->orwhere('mapping_bot.bot_alias'      , 'like', "%{$value}%");
				}
			)
			->select(
				'mapping_bot.*',
				'ORG.organizationShortName as organizationShortName',
				'ORG.RPA as rpaFlag',
				'portal.name as portalName',
				'personality.personalityName as personaName',
				'structure.name as structureName',
				'botType.name as bot_type_name'
			);
	}
	//--------------------------------------------------------------------
	public static function myMap($bot_id){
		return self::where('bot_id', $bot_id)
			->leftJoin('rpamapping.bot_type as bot_type'  , 'mapping_bot.bot_type_id', '=', 'bot_type.id'         )
			->leftJoin('kamadeiep.organization_ep as ORG' , 'mapping_bot.ownerId'    , '=', 'ORG.organizationId'  )
			->leftJoin('kamadeiep.portal as portal'       , 'mapping_bot.portal_id'  , '=', 'portal.id'           )
			->leftJoin('bot_type as botType', 'mapping_bot.bot_type_id', '=', 'botType.id')
			->leftJoin('rpamapping.structure as structure', 'mapping_bot.structure_id', '=', 'structure.id'       )
			->leftJoin('kamadeikb.organization_personality as o_prs', function($join){
				$join->on('ORG.organizationId', '=', 'o_prs.organizationId')
					->where('o_prs.is_default', 1);
			})
			->leftJoin('kamadeikb.personality as personality', 'o_prs.personalityId'        , '=', 'personality.personalityId')
			->leftJoin('kamadeikb.personality as prsnlty'    , 'portal.unknownPersonalityId', '=', 'prsnlty.personalityId'    )
			->select(
				'mapping_bot.*',
				'bot_type.name as botName',
				'ORG.organizationShortName as organizationShortName',
				'portal.name as portalName',
				\DB::raw("if(prsnlty.personalityName is null, personality.personalityName, prsnlty.personalityName) as personaName"),
				'structure.name as structureName',
				'botType.name as bot_type_name'
			)->first();
	}
	//--------------------------------------------------------------------	
}