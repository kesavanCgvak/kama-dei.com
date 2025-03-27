<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'message';
	protected $primaryKey = "messageId";
	
	//--------------------------------------------------------------------
	protected function ownersList($orgId){
		$kama = env('BASE_ORGANIZATION');
		return $this
			->leftJoin('kamadeiep.organization_ep as ORG', 'message.orgId', '=', 'ORG.organizationId')
			->where(function($q) use($orgId){
				if($orgId==0){ return $q; }
				return $q->where('message.orgId', $orgId);
			})
			->groupBy('message.orgId')
			->select(
//				'message.orgId as orgId',
				\DB::raw(
					"IF(orgId is null, 0, orgId) as orgId"
				),
				\DB::raw(
					"IF(organizationShortName is null,if(orgId is null,'{$kama}',concat('org_',orgId)), organizationShortName) as orgName"
				)
			)
			->orderBy("orgName", 'asc')
			->get();
	}
	//----------------------------------------------------
	protected static function myQuery_($orgID, $ownerId, $field, $value){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$data = self::
			leftJoin('kamadeiep.organization_ep as ORG', 'message.orgId', '=', 'ORG.organizationId')->
			where(function($q) use($orgID, $ownerId){ 
				if($orgID==0){
					if($ownerId==-1){ return $q; }
					else{ return $q->where('message.orgId', $ownerId); }
				}
				else{ return $q->where('message.orgId', $orgID); }
			});
		if( $value!='' ){
			$data = $data->
						where(function($q) use($value){
							$qTmp = $q
										->orWhere("message.messageLanguage"          , 'like', "%{$value}%")
										->orWhere("message.messageText"   , 'like', "%{$value}%")
										->orWhere("ORG.organizationShortName", 'like', "%{$value}%")
										->orWhere("message.messageVoice"       , 'like', "%{$value}%");
							if(strpos(strtolower(env('BASE_ORGANIZATION')), strtolower($value))!==false){
									return $qTmp->orWhere('ORG.organizationId', null);
							}else{ return $qTmp; }
						});			
		}
		return 
			$data
				->where('message.messageLanguage', 'en')
				->select(
					"message.messageId as id",
					"message.messageCode as Code",
					"message.orgId as OrgId",
					"message.messageText as Message",
					"message.messageLanguage as Lang",
					\DB::raw("IF(ORG.organizationShortName is null, '".env('BASE_ORGANIZATION')."', ORG.organizationShortName) as orgName")
				);
	}
	//----------------------------------------------------
	protected static function myQuery__($orgID, $ownerId, $field, $value){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$langs  = \App\Http\Controllers\Api\Dashboard\BotMessage\BotMessageController::langList_();
		$QRYs = [];
		$ownerId = (($ownerId==0) ?null :$ownerId);
		foreach($langs as $lang){
			$qry = self::
				leftJoin('kamadeiep.organization_ep as ORG', 'message.orgId', '=', 'ORG.organizationId')->
				where(function($q) use($orgID, $ownerId){ 
	//				return $q->where('botMessage.id', 0);
					if($orgID==0){
						if($ownerId==-1){ return $q; }
						else{ return $q->where('message.orgId', $ownerId); }
					}
					else{ return $q->where('message.orgId', $orgID)->orWhere('message.orgId', null); }
				});
			if( $value!='' ){
				$qry = $qry->
							where(function($q) use($value){
								$qTmp = $q
										->orWhere("message.messageLanguage"          , 'like', "%{$value}%")
										->orWhere("message.messageText"   , 'like', "%{$value}%")
										->orWhere("ORG.organizationShortName", 'like', "%{$value}%")
										->orWhere("message.messageVoice"       , 'like', "%{$value}%");
								if(strpos(strtolower(env('BASE_ORGANIZATION')), strtolower($value))!==false){
									return $qTmp->orWhere('ORG.organizationId', null);
								}else{ return $qTmp; }
							});			
			}
			$qry = $qry->where('message.messageLanguage', $lang['value'])
					->select(
						"message.*",
						"message.description as Description",
						"message.messageId as id",
						"message.messageCode as Code",
						"message.orgId as OrgId",
						"message.messageText as Message",
						"message.messageLanguage as Lang",
						\DB::raw("IF(ORG.organizationShortName is null, '".env('BASE_ORGANIZATION')."', ORG.organizationShortName) as orgName")
					);
			$QRYs[] = $qry;
		}
		
		$qry = $QRYs[0];
		$codeOrg = "0";
		for($i=1; $i<count($QRYs); $i++){
			$tmp = $QRYs[$i-1]->get();
			if(! $tmp->isEmpty()){ foreach($tmp as $tmp_){ $codeOrg .= ",'{$tmp_->Code}{$tmp_->OrgId}'"; } }
			$QRYs[$i] = $QRYs[$i]->whereRaw("concat(message.messageCode,message.orgId) not in ({$codeOrg})");
			$qry = $qry->unionAll($QRYs[$i]);
		}

		return $qry;
	}
	//----------------------------------------------------
	protected static function myQuery($orgID, $ownerId, $field, $value){
/*
		if($orgID!=0 || ( $ownerId!=0 && $ownerId!=-1 )){
			return self::myQuery2($orgID, $ownerId, $field, $value);
		}
		return self::myQuery1($orgID, $ownerId, $field, $value);
*/
		if($orgID!=0){ return self::myQuery2($orgID, $ownerId, $field, $value); }
		if($ownerId!=-1){ return self::myQuery2($orgID, $ownerId, $field, $value); }
		return self::myQuery1($orgID, $ownerId, $field, $value);
	}
	//----------------------------------------------------
	protected static function myQuery1($orgID, $ownerId, $field, $value){
		$PUBLIC  = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD  = \Config::get('kama_dei.static.PROTECTED',1);
		$kamaDEI = env('BASE_ORGANIZATION');
		//$ownerId = (($ownerId==0) ?null :$ownerId);
		$qry = self::
			leftJoin('kamadeiep.organization_ep as ORG', 'message.orgId', '=', 'ORG.organizationId')->
//			where('message.orgId', $org)->
			where(function($q) use($value){
				if($value==''){ return $q; }
				$qTmp = $q
						->orWhere("message.messageLanguage"  , 'like', "%{$value}%")
						->orWhere("message.messageText"      , 'like', "%{$value}%")
						->orWhere("ORG.organizationShortName", 'like', "%{$value}%")
						->orWhere("message.messageVoice"     , 'like', "%{$value}%")
						->orWhere("message.description"      , 'like', "%{$value}%");
				if(strpos(strtolower(env('BASE_ORGANIZATION')), strtolower($value))!==false){
					return $qTmp->orWhere('ORG.organizationId', null);
				}else{ return $qTmp; }
			})->
			where('message.messageLanguage', 'en')->
			select(
				"message.*",
				"message.description as Description",
				"message.messageId as id",
				"message.messageCode as Code",
				"message.orgId as OrgId",
				"message.messageText as Message",
				"message.messageLanguage as Lang",
				\DB::raw("IF(ORG.organizationShortName is null, '{$kamaDEI}', ORG.organizationShortName) as orgName")
			);
		return $qry;
	}
	//----------------------------------------------------
	protected static function myQuery2($orgID, $ownerId, $field, $value){
		$PUBLIC  = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD  = \Config::get('kama_dei.static.PROTECTED',1);
		$kamaDEI = env('BASE_ORGANIZATION');
		//$ownerId = (($ownerId==0) ?null :$ownerId);
		$orgs = [(($orgID==0) ?$ownerId : $orgID), null];
		$QRYs = [];
		foreach($orgs as $org){
			$QRYs[] = self::
				leftJoin('kamadeiep.organization_ep as ORG', 'message.orgId', '=', 'ORG.organizationId')->
				where('message.orgId', $org)->
				where(function($q) use($value){
					if($value==''){ return $q; }
					$qTmp = $q
							->orWhere("message.messageLanguage"  , 'like', "%{$value}%")
							->orWhere("message.messageText"      , 'like', "%{$value}%")
							->orWhere("ORG.organizationShortName", 'like', "%{$value}%")
							->orWhere("message.messageVoice"     , 'like', "%{$value}%")
							->orWhere("message.description"      , 'like', "%{$value}%");
					if(strpos(strtolower(env('BASE_ORGANIZATION')), strtolower($value))!==false){
						return $qTmp->orWhere('ORG.organizationId', null);
					}else{ return $qTmp; }
				})->
				where('message.messageLanguage', 'en')->
				select(
					"message.*",
					"message.description as Description",
					"message.messageId as id",
					"message.messageCode as Code",
					"message.orgId as OrgId",
					"message.messageText as Message",
					"message.messageLanguage as Lang",
					\DB::raw("IF(ORG.organizationShortName is null, '{$kamaDEI}', ORG.organizationShortName) as orgName")
				);
		}
		
		$qry = $QRYs[0];
		$codeOrg = [0];
		$tmp = $QRYs[0]->get();
		if(! $tmp->isEmpty()){ foreach($tmp as $tmp_){ $codeOrg[] = $tmp_->Code; } }
		$QRYs[1] = $QRYs[1]->whereNotIn("message.messageCode", $codeOrg);
		$qry = $qry->unionAll($QRYs[1]);
		return $qry;
	}
	//----------------------------------------------------
}