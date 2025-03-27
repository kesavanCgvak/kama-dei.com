<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
//----------------------------------------------------
class OrganizationAssociation extends Model {
	//------------------------------------------------
	public    $timestamps = false;
	//------------------------------------------------
	protected $connection = 'mysql2';
	protected $table      = 'organization_association';
	protected $primaryKey = "orgAssociationId";
	//------------------------------------------------
	protected function myOrganizationAssociation($orgID, $field, $value, $staticTermID){
		if( $value=='' ){
			return $this
				->leftJoin('kamadeiep.organization_ep as leftOrganization', 'organization_association.leftOrgId', '=', 'leftOrganization.organizationId')
				->leftJoin('kamadeiep.organization_ep as rightOrganization', 'organization_association.rightOrgId', '=', 'rightOrganization.organizationId')
				->leftJoin(
							'kamadeikb.relation_type_group as relation_type_group',
							'organization_association.relationTypeGroupId', '=', 'relation_type_group.relationTypeGroupId'
				)
				->leftJoin('kamadeikb.relation_type as relation_type', 'relation_type_group.relationTypeId', '=', 'relation_type.relationTypeId')
//				->leftJoin('kamadeikb.relation as relation', 'relation_group_classification.relationGroupId', '=', 'relation.relationId')
//				->leftJoin('kamadeikb.term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
//				->leftJoin('kamadeikb.term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
//				->leftJoin('kamadeikb.relation_type as relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
				->where(function($q) use($orgID){ 
						if($orgID==0){ return $q; }
						else{ return $q->where('leftOrgId', '=',$orgID); } 
				})
				->select(
					'organization_association.*',
					'leftOrganization.organizationShortName as leftOrgName',
					'rightOrganization.organizationShortName as rightOrgName',
					'relation_type.relationTypeName as relationTypeGroupName'
					/*
					\DB::raw(
						'('.
							'select '.
								'concat(lTerm.termName, " ", rType.relationTypeName, " ", (select termName from kamadeikb.term where termId='.$staticTermID.')) '.
							'from '.
								'kamadeikb.relation_type_group '.
								'left join kamadeikb.term lTerm on relation_type_group.relationAssociationTermId=lTerm.termId '.
								'left join kamadeikb.relation_type rType on relation_type_group.relationTypeId=rType.relationTypeId '.
							'where '.
								'relationTypeGroupId=organization_association.relationTypeGroupId'.
						') as relationTypeGroupName'
					)
					*/
//					\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as relationGroupClassficationIdName')
				);
		}else{
			if($value==''){
				return $this
					->leftJoin('kamadeiep.organization_ep as leftOrganization', 'organization_association.leftOrgId', '=', 'leftOrganization.organizationId')
					->leftJoin('kamadeiep.organization_ep as rightOrganization', 'organization_association.rightOrgId', '=', 'rightOrganization.organizationId')
					->leftJoin(
								'kamadeikb.relation_type_group as relation_type_group',
								'organization_association.relationTypeGroupId', '=', 'relation_type_group.relationTypeGroupId'
					)
					->leftJoin('kamadeikb.relation_type as relation_type', 'relation_type_group.relationTypeId', '=', 'relation_type.relationTypeId')
					->where(function($q) use($orgID){ 
							if($orgID==0){ return $q; }
							else{ return $q->where('leftOrgId', '=',$orgID); } 
					})
					->select(
						'organization_association.*',
						'leftOrganization.organizationShortName as leftOrgName',
						'rightOrganization.organizationShortName as rightOrgName',
						'relation_type.relationTypeName as relationTypeGroupName'
						/*
						\DB::raw(
							'('.
								'select '.
									'concat(lTerm.termName, " ", rType.relationTypeName, " ", (select termName from kamadeikb.term where termId='.$staticTermID.')) '.
								'from '.
									'kamadeikb.relation_type_group '.
									'left join kamadeikb.term lTerm on relation_type_group.relationAssociationTermId=lTerm.termId '.
									'left join kamadeikb.relation_type rType on relation_type_group.relationTypeId=rType.relationTypeId '.
								'where '.
									'relationTypeGroupId=organization_association.relationTypeGroupId'.
							') as relationTypeGroupName'
						)
						*/
					);
			}else{
				return $this
					->leftJoin('kamadeiep.organization_ep as leftOrganization', 'organization_association.leftOrgId', '=', 'leftOrganization.organizationId')
					->leftJoin('kamadeiep.organization_ep as rightOrganization', 'organization_association.rightOrgId', '=', 'rightOrganization.organizationId')
					->leftJoin(
								'kamadeikb.relation_type_group as relation_type_group',
								'organization_association.relationTypeGroupId', '=', 'relation_type_group.relationTypeGroupId'
					)
					->leftJoin('kamadeikb.relation_type as relation_type', 'relation_type_group.relationTypeId', '=', 'relation_type.relationTypeId')
					->where(function($q) use($field, $value){ 
						return $q
								->whereRaw("leftOrganization.organizationShortName like ?", ["%{$value}%"])
								->orWhereRaw("rightOrganization.organizationShortName like ?", ["%{$value}%"])
								->orWhereRaw("relation_type.relationTypeName like ?", ["%{$value}%"]);
					})
					->where(function($q) use($orgID){ 
							if($orgID==0){ return $q; }
							else{ return $q->where('leftOrgId', '=',$orgID); } 
					})
					->select(
						'organization_association.*',
						'leftOrganization.organizationShortName as leftOrgName',
						'rightOrganization.organizationShortName as rightOrgName',
						'relation_type.relationTypeName as relationTypeGroupName'
						/*
						\DB::raw(
							'('.
								'select '.
									'concat(lTerm.termName, " ", rType.relationTypeName, " ", (select termName from kamadeikb.term where termId='.$staticTermID.')) '.
								'from '.
									'kamadeikb.relation_type_group '.
									'left join kamadeikb.term lTerm on relation_type_group.relationAssociationTermId=lTerm.termId '.
									'left join kamadeikb.relation_type rType on relation_type_group.relationTypeId=rType.relationTypeId '.
								'where '.
									'relationTypeGroupId=organization_association.relationTypeGroupId'.
							') as relationTypeGroupName'
						)
						*/
					);
			}
		}
	}
	//------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $staticTermID){
		//----------------------------------------------------------------
		$data = null;
		//----------------------------------------------------------------
		$data = $this->myOrganizationAssociation($orgID, '', '', $staticTermID)->orderBy($sort, $order)->get()->forPage($page, $perPage);
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $key=>$tmp ){ 
			if($tmp->leftOrgId==null){ $tmp->leftOrgName = env('BASE_ORGANIZATION'); }
//			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		//----------------------------------------------------------------
		return $retVal;
		//----------------------------------------------------------------
	}
	//------------------------------------------------
	protected function myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value, $staticTermID){
		//----------------------------------------------------------------
		$data = null;
		//----------------------------------------------------------------
		$data = $this->myOrganizationAssociation($orgID, $field, $value, $staticTermID)->orderBy($sort, $order)->get()->forPage($page, $perPage);
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $tmp ){
			if($tmp->leftOrgId==null){ $tmp->leftOrgName = env('BASE_ORGANIZATION'); }
//			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//------------------------------------------------
}
//----------------------------------------------------
