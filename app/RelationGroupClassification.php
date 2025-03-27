<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
//----------------------------------------------------
class RelationGroupClassification extends Model {
	//------------------------------------------------
	public    $timestamps = false;
	//------------------------------------------------
	protected $connection = 'mysql2';
	protected $table      = 'relation_group_classification';
	protected $primaryKey = "relationGroupClassficationId";
	//------------------------------------------------
	protected function myRelationGroupClassification($orgID, $field, $value){
		if( $value=='' ){
			return $this
				->leftJoin('kamadeikb.relation', 'relation_group_classification.relationGroupId', '=', 'relation.relationId')
				->leftJoin('kamadeikb.term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
				->leftJoin('kamadeikb.term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
				->leftJoin('kamadeikb.relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
				->leftJoin('kamadeiep.organization_ep as organization', 'relation.ownerId', '=', 'organization.organizationId')
				->where(function($q) use($orgID){ 
					if($orgID==0){ return $q; }
					else{ return $q->where('relation.ownerId', '=',$orgID); } 
				})
				->select(
					'relation_group_classification.*',
					'relation.ownerId as ownerId',
					'organization.organizationShortName as organizationShortName',
					\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as relationGroupType')
				);
		}else{
			if($value==''){
				return $this
					->leftJoin('kamadeikb.relation', 'relation_group_classification.relationGroupId', '=', 'relation.relationId')
					->leftJoin('kamadeikb.term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
					->leftJoin('kamadeikb.term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
					->leftJoin('kamadeikb.relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
					->leftJoin('kamadeiep.organization_ep as organization', 'relation.ownerId', '=', 'organization.organizationId')
					->where(function($q) use($orgID){ 
						if($orgID==0){ return $q; }
						else{ return $q->where('relation.ownerId', '=',$orgID); } 
					})
					->select(
						'relation_group_classification.*',
						'relation.ownerId as ownerId',
						'organization.organizationShortName as organizationShortName',
						\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as relationGroupType')
					);
			}else{
				return $this
					->leftJoin('kamadeikb.relation', 'relation_group_classification.relationGroupId', '=', 'relation.relationId')
					->leftJoin('kamadeikb.term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
					->leftJoin('kamadeikb.term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
					->leftJoin('kamadeikb.relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
					->leftJoin('kamadeiep.organization_ep as organization', 'relation.ownerId', '=', 'organization.organizationId')
					->where(function($q) use($field, $value){ 
						return $q
								->whereRaw("organization.organizationShortName like ?", ["%{$value}%"])
								->orWhereRaw("leftTerm.termName like ?", ["%{$value}%"])
								->orWhereRaw("rightTerm.termName like ?", ["%{$value}%"])
								->orWhereRaw("relation_type.relationTypeName like ?", ["%{$value}%"]);
					})
					->where(function($q) use($orgID){ 
						if($orgID==0){ return $q; }
						else{ return $q->where('relation.ownerId', '=',$orgID); } 
					})
					->select(
						'relation_group_classification.*',
//						'relation.ownerId as ownerId',
						'organization.organizationShortName as organizationShortName',
						\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as relationGroupType')
					);
			}
		}
	}
	//------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order){
		//----------------------------------------------------------------
		$data = null;
		//----------------------------------------------------------------
		$data = $this->myRelationGroupClassification($orgID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage);
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $key=>$tmp ){ 
			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
//			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		//----------------------------------------------------------------
		return $retVal;
		//----------------------------------------------------------------
	}
	//------------------------------------------------
	protected function myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value){
		//----------------------------------------------------------------
		$data = null;
		//----------------------------------------------------------------
		$data = $this->myRelationGroupClassification($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $tmp ){
			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
//			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//------------------------------------------------
}
//----------------------------------------------------
