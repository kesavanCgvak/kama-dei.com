<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelationTypeGroup extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_type_group';
	protected $primaryKey = "relationTypeGroupId";
	//--------------------------------------------------------------------
	protected function myQuery($orgID, $field, $value, $ownerId=-99){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE = \Config::get('kama_dei.static.PRIVATE'  ,2);
		if( $value=='' ){
			return $this
				->leftJoin('kamadeiep.organization_ep as organization', 'relation_type_group.ownerId', '=', 'organization.organizationId')
				->leftJoin('kamadeikb.term as term', 'relation_type_group.relationAssociationTermId', '=', 'term.termId')
				->leftJoin('kamadeikb.relation_type as relation_type', 'relation_type_group.relationTypeId', '=', 'relation_type.relationTypeId')
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){ 
					if($orgID==0 && $ownerId==-99){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						$q = $q
							->where('relation_type_group.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
								return $q->whereIn('relation_type_group.ownerId', $tmpOrgIDs)->where('relation_type_group.ownership', $PRTCTD);
							});
						if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('relation_type_group.ownerId', $orgID); }
						if($orgID==0 && $ownerId!=-99){ return $q->orWhere('relation_type_group.ownerId', $ownerId); }
						return $q;
					}
				})
				->select(
					'relation_type_group.*',
//					'organization.organizationShortName as organizationShortName',
					'relation_type.relationTypeName as relationTypeName',
					'term.termName as relationTypeGroupName',
//					\DB::raw('CONCAT(term.termName," ",relation_type.relationTypeName) as relationTypeGroupName')
					\DB::raw("if(relation_type_group.ownerId=0 or relation_type_group.ownerId is null, '".env('BASE_ORGANIZATION')."', organization.organizationShortName) as organizationShortName"),
					\DB::raw("if(relation_type_group.ownership=0, 'Public', if(relation_type_group.ownership=1, 'Protected', 'Private' )) as ownerShipText")
				);
		}else{
			return $this
				->leftJoin('kamadeiep.organization_ep as organization', 'relation_type_group.ownerId', '=', 'organization.organizationId')
				->leftJoin('kamadeikb.term as term', 'relation_type_group.relationAssociationTermId', '=', 'term.termId')
				->leftJoin('kamadeikb.relation_type as relation_type', 'relation_type_group.relationTypeId', '=', 'relation_type.relationTypeId')
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){ 
					if($orgID==0 && $ownerId==-99){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						$q = $q
							->where('relation_type_group.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
								return $q->whereIn('relation_type_group.ownerId', $tmpOrgIDs)->where('relation_type_group.ownership', $PRTCTD);
							});
						if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('relation_type_group.ownerId', $orgID); }
						if($orgID==0 && $ownerId!=-99){ return $q->orWhere('relation_type_group.ownerId', $ownerId); }
						return $q;
					}
				})
				->where(function($q) use($field, $value){ 
					return $q
						->where(
							\DB::raw("if(relation_type_group.ownerId=0 or relation_type_group.ownerId is null, '".env('BASE_ORGANIZATION')."', organization.organizationShortName)"),
							'like', 
							"%{$value}%"
						)
						->orWhereRaw("term.termName like ?", ["%{$value}%"])
						->orWhereRaw("relation_type.relationTypeName like ?", ["%{$value}%"])
						->orwhere(
							\DB::raw("if(relation_type_group.ownership=0,'public', if(relation_type_group.ownership=1,'protected', 'private'))") , 
							'like', 
							"%{$value}%"
						);
				})
				->select(
					'relation_type_group.*',
//					'organization.organizationShortName as organizationShortName',
					'relation_type.relationTypeName as relationTypeName',
					'term.termName as relationTypeGroupName',
//					\DB::raw('CONCAT(term.termName," ",relation_type.relationTypeName) as relationTypeGroupName')
					\DB::raw("if(relation_type_group.ownerId=0 or relation_type_group.ownerId is null, '".env('BASE_ORGANIZATION')."', organization.organizationShortName) as organizationShortName"),
					\DB::raw("if(relation_type_group.ownership=0, 'Public', if(relation_type_group.ownership=1, 'Protected', 'Private' )) as ownerShipText")
				);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $STATIC_TERM_ID, $ownerId, $field='', $value=''){
		$data = null;
//		$data = $this->myQuery($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
		switch($ownerId){
			case -1: $data = $this->myQuery($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			case  0: 
				$data = $this->myQuery($orgID, $field, $value)->where('relation_type_group.ownerId', null)->orderBy($sort, $order)->get()->forPage($page, $perPage);
				break;
			default: 
				$data = $this->myQuery($orgID, $field, $value)->where('relation_type_group.ownerId', $ownerId)->orderBy($sort, $order)->get()->forPage($page, $perPage);
				break;
		}
		if($data->isEmpty()){ return null; }
//		$STATIC_TERM_TEMP = \App\Term::find($STATIC_TERM_ID);
//		if($STATIC_TERM_TEMP==null){ $STATIC_TERM_TEMP=""; }
//		else{ $STATIC_TERM_VALUE = $STATIC_TERM_TEMP->termName; }
		$retVal = [];
		foreach( $data as $tmp ){
			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
//			$tmp->relationTypeGroupName = $tmp->relationTypeGroupName." ".$STATIC_TERM_VALUE;
//			$tmp->relationTypeGroupName = $STATIC_TERM_VALUE;
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
	protected function ownersList($orgID){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		return $this
			->leftJoin('kamadeiep.organization_ep as organization', 'relation_type_group.ownerId', '=', 'organization.organizationId')
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD){ 
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->where('ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){ return $q->whereIn('ownerId', $tmpOrgIDs)->where('ownership', $PRTCTD); })
								->orWhere('ownerId', $orgID);
					}
			})
			->groupBy('ownerId')
			->select('ownerId', 'organization.organizationShortName as organizationShortName');
	}
	//--------------------------------------------------------------------
	protected function getOwnersList($orgID){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$data = $this
			->leftJoin('kamadeiep.organization_ep', 'kamadeikb.relation_type_group.ownerId', '=', 'kamadeiep.organization_ep.organizationId')
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD){ 
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->where('relation_type_group.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('relation_type_group.ownerId', $tmpOrgIDs)->where('relation_type_group.ownership', $PRTCTD);
								})
								->orWhere('relation_type_group.ownerId', $orgID);
					}
			})
			->groupBy('relation_type_group.ownerId')
			->select(
				\DB::raw("if(relation_type_group.ownerId=0 or relation_type_group.ownerId is null, 0, relation_type_group.ownerId) as id"),
				\DB::raw("if(relation_type_group.ownerId=0 or relation_type_group.ownerId is null, '".env('BASE_ORGANIZATION')."', organization_ep.organizationShortName) as text")
			)
			->orderBy("text", "asc")
			->get()->toArray();
		
		$isTrue = false;
		foreach($data as $tmp){ if($tmp['id']==$orgID){ $isTrue=true; } }
		if($isTrue==false){
			if($orgID!=0){
				$tmp = \App\Organization::find($orgID);
				if($tmp!=null){
					$val         = [];
					$val['id']   = $orgID;
					$val['text'] = $tmp->organizationShortName; 
					$data[]      = $val;
				}
			}else{
				$val       = [];
				$val->id   = 0;
				$val->text = env('BASE_ORGANIZATION'); 
				$data[]    = $val;
			}
		}

		$isTrue = false;
		$i = 0;
		foreach($data as $tmp){
			if($tmp['id']==0 && $isTrue){
				$isTrue=false;
				break;
			}
			if($tmp['id']==0){ $isTrue=true; }
			$i++;
		}
		if(!$isTrue){ unset($data[$i]); }

		return $data;
	}
	//--------------------------------------------------------------------
	protected function myQueryNew($orgID, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1){
		$PRIVATE = \Config::get('kama_dei.static.PRIVATE',2);
//		$tmp = $this->myQuery($orgID, $field, $value)->orderBy($sort, $order);

		switch($ownerId){
			case -1: $tmp = $this->myQuery($orgID, $field, $value)->orderBy($sort, $order); break;
			default: $tmp = $this->myQuery($orgID, $field, $value, $ownerId)->orderBy($sort, $order); break;
		}

		if($tmp==null){ return null; }
		if($orgID!=$ownerId && $ownerId!=-1 && $orgID!=0){ $tmp = $tmp->where('relation_type_group.ownership', '<>', $PRIVATE); }
		if($shwglblSTT==1){ return $tmp; }
		switch($ownerId){
			case -1: return $tmp->where('relation_type_group.ownerId', $orgID);
			case  0: return $tmp->where('relation_type_group.ownerId', null);
			default: return $tmp->where('relation_type_group.ownerId', $ownerId);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageingNew($orgID, $perPage, $page, $sort, $order, $STATIC_TERM_ID, $field='', $value='', $ownerId=-1, $shwglblSTT=1){
		//----------------------------------------------------------------
		$tmp = $this->myQueryNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT);
		//----------------------------------------------------------------
		if($tmp==null){ return null; }
		//----------------------------------------------------------------
		$data = $tmp->get()->forPage($page, $perPage);
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $key=>$tmp ){
			$tmp->organization = new \StdClass;
			$tmp->organization->organizationShortName = $tmp->organizationShortName;
			$retVal[] = $tmp;
		}
		return $retVal;
		//----------------------------------------------------------------
	}
	//--------------------------------------------------------------------
}
