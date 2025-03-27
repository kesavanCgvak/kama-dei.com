<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Portal extends Model
{
	//----------------------------------------------------
	public    $timestamps = false;
	//----------------------------------------------------
	protected $table      = 'portal';
	protected $primaryKey = "id";
	//----------------------------------------------------
	protected static function myPortal($orgID, $ownerId, $field, $value){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$data = self::
			leftJoin('kamadeiep.user', 'portal.OnOff_by', '=', 'user.id')->
			leftJoin('kamadeiep.organization_ep as ORG', 'portal.organization_id', '=', 'ORG.organizationId')->
/**/
			leftJoin('kamadeikb.organization_personality as o_prs', function($join){
				$join->on('kamadeiep.portal.organization_id', '=', 'o_prs.organizationId')
					->where('o_prs.is_default', 1);
			})->
			leftJoin('kamadeikb.personality as PERD', 'o_prs.personalityId', '=', 'PERD.personalityId')->
/**/
			leftJoin('kamadeikb.personality as PER', 'portal.unknownPersonalityId', '=', 'PER.personalityId')->
			leftJoin('kamadeiep.portalType as prtType', 'portal.portal_number', '=', 'prtType.number')->
/*
			where(function($q) use($orgID, $PUBLIC, $PRTCTD){ 
				if($orgID==0){ return $q; }
				else{
					$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
					return $q
						->where(function($q) use($tmpOrgIDs, $PRTCTD){ 
							return $q->whereIn('organization_id', $tmpOrgIDs)->where('ownership', $PRTCTD); 
						})
						->orWhere('organization_id', $orgID);
				}
			})->
			where(function($q) use($ownerId){ 
				if($ownerId==-1){ return $q; }
				else{
					return $q->where('portal.organization_id', $ownerId);
				}
			});
*/
			where(function($q) use($orgID, $ownerId){ 
				if($orgID==0){
					if($ownerId==-1){ return $q; }
					else{ return $q->where('portal.organization_id', $ownerId); }
				}
				else{ return $q->where('portal.organization_id', $orgID); }
			});
		if( $value=='' ){
		}else{
			$data = $data->
						where(function($q) use($value){
							$qTmp = $q
										->where("name",                        'like', "%{$value}%")
//										->orWhere("code",                      'like', "%{$value}%")
										->orWhere("portal_number",             'like', "%{$value}%")
										->orWhere("ORG.organizationShortName", 'like', "%{$value}%")
										->orWhere("PER.personalityName"      , 'like', "%{$value}%")
										->orWhere("prtType.caption"          , 'like', "%{$value}%")
										->orWhere("PERD.personalityName"     , 'like', "%{$value}%");
							if(strpos(strtolower(env('BASE_ORGANIZATION')), strtolower($value))!==false){
								return $qTmp->orWhere('organization_id', null);
							}else{ return $qTmp; }
						});
		}
		return 
			$data->select(
				"portal.*",
				"ORG.KaaS3PB as org_KasS3PB",
				"ORG.feedback as orgFeedback",
				"prtType.caption as portalType",
				\DB::raw("IF(ORG.organizationShortName is null, '".env('BASE_ORGANIZATION')."', ORG.organizationShortName) as orgName"),
//				\DB::raw("ORG.AutoEmail as AutoEmail"),
			
				\DB::raw("(select emails from logEmailsConfig where logEmailsConfig.portal_id=portal.id limit 1) as emails"),
				\DB::raw("(select body from logEmailsConfig where logEmailsConfig.portal_id=portal.id limit 1) as body"),
				\DB::raw("(select subject from logEmailsConfig where logEmailsConfig.portal_id=portal.id limit 1) as subject"),
				\DB::raw("(select send_format from logEmailsConfig where logEmailsConfig.portal_id=portal.id limit 1) as send_format"),
			
				\DB::raw("IF(PER.personalityName  is null, '', PER.personalityName ) as unknownPersonality"),
				\DB::raw("IF(PERD.personalityName is null, '', PERD.personalityName) as orgPersona"),
				\DB::raw("IF(portal.OnOff_by is null, '', user.userName) as OnOffBy")
			);
	}
	//----------------------------------------------------
	protected function ownersList($orgId){
		return $this
			->leftJoin('kamadeiep.organization_ep as ORG', 'portal.organization_id', '=', 'ORG.organizationId')
			->where(function($q) use($orgId){
				if($orgId==0){ return $q; }
				return $q->where('portal.organization_id', $orgId);
			})
			->groupBy('portal.organization_id')
			->select(
				'portal.organization_id as orgId',
				\DB::raw("IF(ORG.organizationShortName is null, '".env('BASE_ORGANIZATION')."', ORG.organizationShortName) as orgName")
			)
			->orderBy("orgName", 'asc')
			->get();
	}
	//----------------------------------------------------
	protected function liveAgentList($orgId){
		return $this
			->leftJoin('kamadeiep.organization_ep as ORG', 'portal.organization_id', '=', 'ORG.organizationId')
			->where('ORG.hasLiveAgent', 1)
			->where(function($q) use($orgId){
				if($orgId==0){ return $q; }
				return $q->where('portal.organization_id', $orgId);
			})
			->groupBy('portal.organization_id')
			->select(
				'portal.organization_id as orgId',
				\DB::raw("IF(ORG.organizationShortName is null, '".env('BASE_ORGANIZATION')."', ORG.organizationShortName) as orgName")
			)
			->orderBy("orgName", 'asc')
			->get();
	}
	//----------------------------------------------------
	protected function getPortalPerson($id){
		$A = \App\Portal::where('portal.id', $id)
			->leftJoin('kamadeikb.organization_personality as o_prs', 'kamadeiep.portal.organization_id', '=', 'o_prs.organizationId')
			->leftJoin('kamadeikb.personality as PRS', 'portal.unknownPersonalityId', '=', 'PRS.personalityId')
			->select(
				'PRS.personalityName as personaName'
			)->first();
		if($A->personaName!=null){ return $A; }
		return \App\Portal::where('portal.id', $id)
			->leftJoin('kamadeikb.organization_personality as o_prs', 'kamadeiep.portal.organization_id', '=', 'o_prs.organizationId')
			->leftJoin('kamadeikb.personality as PRS', 'o_prs.personalityId', '=', 'PRS.personalityId')
			->where('o_prs.is_default', 1)
			->select(
				'PRS.personalityName as personaName'
			)->first();
	}
	//----------------------------------------------------
}
