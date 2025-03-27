<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BotMessage extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql';
	protected $table      = 'botMessage';
	//--------------------------------------------------------------------
	protected function ownersList($orgId){
		$kama = env('BASE_ORGANIZATION');
		return $this
			->leftJoin('kamadeiep.organization_ep as ORG', 'botMessage.OrgId', '=', 'ORG.organizationId')
			->where(function($q) use($orgId){
				if($orgId==0){ return $q; }
				return $q->where('botMessage.OrgId', $orgId);
			})
			->groupBy('botMessage.OrgId')
			->select(
//				'botMessage.OrgId as orgId',
				\DB::raw(
					"IF(OrgId is null, 0, OrgId) as orgId"
				),
				\DB::raw(
					"IF(organizationShortName is null,if(OrgId is null,'{$kama}',concat('org_',OrgId)), organizationShortName) as orgName"
				)
			)
			->orderBy("orgName", 'asc')
			->get();
	}
	//----------------------------------------------------
	protected static function myQuery($orgID, $ownerId, $field, $value){
		if($orgID!=0 || ( $ownerId!=0 && $ownerId!=-1 )){
			return self::myQuery2($orgID, $ownerId, $field, $value);
		}
		return self::myQuery1($orgID, $ownerId, $field, $value);
	}
	//----------------------------------------------------
	protected static function myQuery2($orgID, $ownerId, $field, $value){
		$kama = env('BASE_ORGANIZATION');
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);

		$ownerId = (($ownerId==0) ?null :$ownerId);
		$qry = self::
			leftJoin('kamadeiep.user as userC', 'botMessage.Created_by' , '=', 'userC.id')->
			leftJoin('kamadeiep.user as userM', 'botMessage.Modified_by', '=', 'userM.id')->
			leftJoin('kamadeiep.organization_ep as ORG', 'botMessage.OrgId', '=', 'ORG.organizationId')->
			where('botMessage.Lang', 'en')->
			where(function($q) use($orgID, $ownerId){ 
				if($orgID==0){
					if($ownerId==-1){ return $q; }
					else{ 
						if($ownerId==0){ return $q->where('botMessage.OrgId', null); }
						return $q->where('botMessage.OrgId', '=', $ownerId)->orwhere('botMessage.OrgId', '=', null);
					}
				}
				else{ return $q->where('botMessage.OrgId', '=', $orgID)->orwhere('botMessage.OrgId','=', null); }
			})->
			where(function($q) use($value){
				if($value!=''){
					$qTmp = $q
								->where("botMessage.Name"            , 'like', "%{$value}%")
								->orWhere("botMessage.Lang"          , 'like', "%{$value}%")
								->orWhere("botMessage.Description"   , 'like', "%{$value}%")
								->orWhere("ORG.organizationShortName", 'like', "%{$value}%")
								->orWhere("botMessage.Message"       , 'like', "%{$value}%")
								->orWhere("botMessage.Type"          , 'like', "%{$value}%");
					if(strpos(strtolower(env('BASE_ORGANIZATION')), strtolower($value))!==false){
						return $qTmp->orWhere('ORG.organizationId', null);
					}else{ return $qTmp; }
				}else{ return $q; }
			})
			->select( "botMessage.Code", "botMessage.id" )
//			->groupBy("botMessage.Code")
			->get();
		
		$codes = [];
		$ids   = [0];
		if(! $qry->isEmpty()){
			foreach($qry as $tmp){
				if(!in_array($tmp->Code, $codes)){
					$codes[] = $tmp->Code;
					$ids[]   = $tmp->id;
				}
			}
		}
		$qry = self::
			leftJoin('kamadeiep.user as userC', 'botMessage.Created_by' , '=', 'userC.id')->
			leftJoin('kamadeiep.user as userM', 'botMessage.Modified_by', '=', 'userM.id')->
			leftJoin('kamadeiep.organization_ep as ORG', 'botMessage.OrgId', '=', 'ORG.organizationId')->
			whereIn('botMessage.id', $ids)->
			select(
				"botMessage.*",
				\DB::raw(
					"IF(ORG.organizationShortName is null,if(botMessage.OrgId is null,'{$kama}',concat('org_',botMessage.OrgId)), organizationShortName) as orgName"
				),
				\DB::raw("IF(botMessage.Created_by  is null, '', userC.userName) as CreatedBy"),
				\DB::raw("IF(botMessage.Modified_by is null, '', userM.userName) as ModifiedBy")
			);

		return $qry;
	}
	//----------------------------------------------------
	protected static function myQuery1($orgID, $ownerId, $field, $value){
		$kama = env('BASE_ORGANIZATION');
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);

		$ownerId = (($ownerId==0) ?null :$ownerId);
		$qry = self::
			leftJoin('kamadeiep.user as userC', 'botMessage.Created_by' , '=', 'userC.id')->
			leftJoin('kamadeiep.user as userM', 'botMessage.Modified_by', '=', 'userM.id')->
			leftJoin('kamadeiep.organization_ep as ORG', 'botMessage.OrgId', '=', 'ORG.organizationId')->
			where('botMessage.Lang', 'en')->
			where(function($q) use($orgID, $ownerId){ 
				if($orgID==0){
					if($ownerId==-1){ return $q; }
					else{ 
						if($ownerId==0){ return $q->where('botMessage.OrgId', null); }
						return $q->where('botMessage.OrgId', '=', $ownerId)->where('botMessage.OrgId', '=', null, 'xor');
					}
				}
				else{ return $q->where('botMessage.OrgId', '=', $orgID)->where('botMessage.OrgId','=', null, 'xor'); }
			})->
			where(function($q) use($value){
				if($value!=''){
					$qTmp = $q
								->where("botMessage.Name"            , 'like', "%{$value}%")
								->orWhere("botMessage.Lang"          , 'like', "%{$value}%")
								->orWhere("botMessage.Description"   , 'like', "%{$value}%")
								->orWhere("ORG.organizationShortName", 'like', "%{$value}%")
								->orWhere("botMessage.Message"       , 'like', "%{$value}%")
								->orWhere("botMessage.Type"          , 'like', "%{$value}%");
					if(strpos(strtolower(env('BASE_ORGANIZATION')), strtolower($value))!==false){
						return $qTmp->orWhere('ORG.organizationId', null);
					}else{ return $qTmp; }
				}else{ return $q; }
			})
			->select(
				"botMessage.*",
				\DB::raw(
					"IF(ORG.organizationShortName is null,if(botMessage.OrgId is null,'{$kama}',concat('org_',botMessage.OrgId)), organizationShortName) as orgName"
				),
				\DB::raw("IF(botMessage.Created_by  is null, '', userC.userName) as CreatedBy"),
				\DB::raw("IF(botMessage.Modified_by is null, '', userM.userName) as ModifiedBy")
			);

		return $qry;
	}
	//----------------------------------------------------
}