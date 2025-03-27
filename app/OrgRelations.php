<?php

namespace App;
use \Illuminate\Support\Facades\Config;

class OrgRelations{
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public static function haveAccessToWithName($orgID){
		//-----------------------------------
		$retVal = [];
		$rtID  = \Config::get('kama_dei.static.can_access_protected_data_from',0);
		//-----------------------------------
		$tmpID = \App\RelationTypeGroup::where('relationTypeId', $rtID)->select('relationTypeGroupId')->get();
		if($tmpID==null){ return [0, '']; }
		$rtgID = [];
		foreach( $tmpID as $tmp){ $rtgID[] = $tmp->relationTypeGroupId; }
		//-----------------------------------
		$tmpID = \App\OrganizationAssociation::where('leftOrgId', $orgID)->whereIn('relationTypeGroupId', $rtgID)->select('rightOrgId')->get();
		if($tmpID==null){ return [[0, env('BASE_ORGANIZATION')]]; }
		foreach( $tmpID as $tmp){ $retVal[] = [$tmp->rightOrgId, $tmp->rightOrgId]; }
		//-----------------------------------
		return $retVal;
		//-----------------------------------
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public static function haveAccessTo($orgID){
		//-----------------------------------
		$retVal = [];
		$rtID  = \Config::get('kama_dei.static.can_access_protected_data_from',0);
		//-----------------------------------
		$tmpID = \App\RelationTypeGroup::where('relationTypeId', $rtID)->select('relationTypeGroupId')->get();
		if($tmpID==null){ return [0]; }
		$rtgID = [];
		foreach( $tmpID as $tmp){ $rtgID[] = $tmp->relationTypeGroupId; }
		//-----------------------------------
		$tmpID = \App\OrganizationAssociation::where('leftOrgId', $orgID)->whereIn('relationTypeGroupId', $rtgID)->select('rightOrgId')->get();
		if($tmpID==null){ return [0]; }
		foreach( $tmpID as $tmp){ $retVal[] = $tmp->rightOrgId; }
		//-----------------------------------
		return $retVal;
		//-----------------------------------
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	private static function validRelation($leftOrgId, $rightOrgId){
		//-----------------------------------
		$rtID  = \Config::get('kama_dei.static.can_access_protected_data_from',0);
		//-----------------------------------
		$tmpID = \App\RelationTypeGroup::where('relationTypeId', $rtID)->select('relationTypeGroupId')->get();
		if($tmpID==null){ return 0; }
		$rtgID = [];
		foreach( $tmpID as $tmp){ $rtgID[] = $tmp->relationTypeGroupId; }
		//-----------------------------------
		return \App\OrganizationAssociation::where('leftOrgId', $leftOrgId)->where('rightOrgId', $rightOrgId)->whereIn('relationTypeGroupId', $rtgID)->count();
		//-----------------------------------
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	private static function hasAccessTo_($orgID, $inID, $TABLE, $ID, $ownership, $ownerId){
		if($orgID==0 || $orgID==null){ return true; }
		//-----------------------------------
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		//-----------------------------------
		$tmp = $TABLE->where($ID, $inID)->select($ownership, $ownerId)->first();
		//-----------------------------------
		if($tmp==null){ return false; }
		//-----------------------------------
		if($tmp->ownerId==$orgID){ return true; }
		//-----------------------------------
		if($tmp->ownership==$PUBLIC){ return true; }
		//-----------------------------------
		if($tmp->ownership==$PRTCTD && self::validRelation($orgID, $tmp->ownerId)>0 ){ return true; }
		//-----------------------------------
		return false;
		//-----------------------------------
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public static function hasAccessToTerm($orgID, $inID){
		return self::hasAccessTo_($orgID, $inID, new \App\Term, 'termId', 'ownership as ownership', 'ownerId as ownerId' );
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public static function hasAccessToRelation($orgID, $inID){
		return self::hasAccessTo_($orgID, $inID, new \App\Relation, 'relationId', 'ownership as ownership', 'ownerId as ownerId' );
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public static function hasAccessToRelationType($orgID, $inID){
		return self::hasAccessTo_($orgID, $inID, new \App\RelationType, 'relationTypeId', 'ownership as ownership', 'ownerId as ownerId' );
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public static function hasAccessToRelationLink($orgID, $inID){
		return self::hasAccessTo_($orgID, $inID, new \App\RelationLink, 'relationLinkId', 'ownership as ownership', 'ownerId as ownerId' );
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
}
