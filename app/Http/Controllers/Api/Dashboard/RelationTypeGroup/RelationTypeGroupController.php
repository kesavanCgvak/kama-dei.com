<?php

namespace App\Http\Controllers\Api\Dashboard\RelationTypeGroup;

use Illuminate\Http\Request;
use App\RelationTypeGroup;
use App\Controllers;
//use App\Http\Resources\RelationType as RelationTypeResource;

class RelationTypeGroupController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function showAllSorted($orgID, $sort, $order, $perPage, $page, $ownerId, $shwglblSTT=1){
		//---------------------------------------
		$sort = $this->validSortName( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$order = strtolower($order);
		switch($order){
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
			case 'asc' :
			case 'desc':{ break; }
		}
		//---------------------------------------
		$relationTypeId = \Config::get('kama_dei.static.is_a_member_of_ID',0);
		$RELATION_ASSOCIATION_ID    = \Config::get('kama_dei.static.RELATION_ASSOCIATION_ID',0);
		//---------------------------------------
		$count = RelationTypeGroup::myQueryNew($orgID, $sort, $order, '', '', $ownerId, $shwglblSTT)->count();
		$data  = RelationTypeGroup::myPageingNew($orgID, $perPage, $page, $sort, $order, $RELATION_ASSOCIATION_ID, '', '', $ownerId, $shwglblSTT);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
		//---------------------------------------
	}
	//---------------------------------------}
	public function showPageSortSearch($orgID, $sort, $order, $perPage, $page, $field, $value, $ownerId){
		//---------------------------------------
		$sort = $this->validSortName( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$field = $this->validFieldName( $field );
		if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		//---------------------------------------
		$relationTypeId = \Config::get('kama_dei.static.is_a_member_of_ID',0);
		$RELATION_ASSOCIATION_ID    = \Config::get('kama_dei.static.RELATION_ASSOCIATION_ID',0);
		//---------------------------------------
		$data  = RelationTypeGroup::myPageing($orgID, $perPage, $page, $sort, $order, $RELATION_ASSOCIATION_ID, $ownerId, $field, $value);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data]; }
		//---------------------------------------
	}
	//---------------------------------------
	public function myTermsShow($orgID, $relationTypeID){
		//---------------------------------------
		$relationTypeID          = \Config::get('kama_dei.static.is_a_member_of_ID',0);
		$RELATION_ASSOCIATION_ID = \Config::get('kama_dei.static.RELATION_ASSOCIATION_ID',0);
		//---------------------------------------
		$data  = \App\Relation::
						leftJoin('kamadeikb.term as leftterm', 'relation.leftTermId', '=', 'leftterm.termId')->
						leftJoin('kamadeikb.relation_type as relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')->
						leftJoin('kamadeikb.term as rightterm', 'relation.rightTermId', '=', 'rightterm.termId')->
						where('relation.relationTypeId', $relationTypeID)->
						where('relation.rightTermId', $RELATION_ASSOCIATION_ID)->
//						where('leftterm.termId', '<>', null)->
						orderBy('leftterm.termName', 'asc')->
//						groupBy('leftterm.termId')->
						select(
							'leftterm.termId as termId',
							'leftterm.termName as relationAssociationTermName'
//							\DB::raw('CONCAT(leftterm.termName," ",relation_type.relationTypeName," ",rightterm.termName) as relationAssociationTermName')
						)->
						get();
		$count = count($data);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------}
	public function insertRow($orgID, Request $request){
		try{
			//-------------------------------
			$data = $request->all();
			//-------------------------------
			if($orgID==0){
				$tmpOrgID = trim($data['ownerId']);
				if($tmpOrgID==0){ 
					$cnt = RelationTypeGroup::
							where('relationAssociationTermId', trim($data['relationAssociationTermId']))->
							where('relationTypeId'           , trim($data['relationTypeId']))->
							where('ownerId'                  , null)->count();
				}else{
					$cnt = RelationTypeGroup::
							where('relationAssociationTermId', trim($data['relationAssociationTermId']))->
							where('relationTypeId'           , trim($data['relationTypeId']))->
							where('ownerId'                  , trim($tmpOrgID))->count();
				}
			}else{
				$cnt = RelationTypeGroup::
							where('relationAssociationTermId', trim($data['relationAssociationTermId']))->
							where('relationTypeId'           , trim($data['relationTypeId']))->
							where('ownerId'                  , trim($orgID))->count();
			}
			if($cnt!=0){ return ['result'=>1, 'msg'=>"Relation type group is exist on this organization"]; }
			//-------------------------------
			$tmp  = new RelationTypeGroup;
			$tmp->relationAssociationTermId = trim($data['relationAssociationTermId']);
			$tmp->relationTypeId            = trim($data['relationTypeId']);
			$tmp->description               = trim($data['description']);
			$tmp->ssReserved                = trim($data['ssReserved']);
			$tmp->ownership                 = trim($data['ownership']);
			$tmp->ownerId                   = (($orgID==0)? trim($data['ownerId']) :$orgID);
			$tmp->lastUserId                = trim($data['userID']);
			$tmp->dateCreated               = date("Y-m-d H:i:s");

			if($tmp->relationAssociationTermId==''){ return ['result'=>1, 'msg'=>'Relation Type Group is empty']; }
			if($tmp->relationTypeId           ==''){ return ['result'=>1, 'msg'=>'Relation Type is empty']; }
			if($tmp->ownerId                  ==''){ return ['result'=>1, 'msg'=>'Owner is empty']; }
			if($tmp->lastUserId               ==''){ return ['result'=>1, 'msg'=>'Last user is empty']; }

			$tmp->ownerId = (($tmp->ownerId==0) ?null :$tmp->ownerId);
			$tmpSave = $tmp->save();
			if($tmpSave){ return ['result'=>0, 'relationTypeGroupId'=>$tmp->relationTypeGroupId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
			//-------------------------------
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			//-------------------------------
			$data = $request->all();
			//-------------------------------
			if($orgID==0){
				$tmpOrgID = trim($data['ownerId']);
				if($tmpOrgID==0){
					$cnt = RelationTypeGroup::
							where('relationTypeGroupId', '<>', $id)->
							where('relationAssociationTermId', trim($data['relationAssociationTermId']))->
							where('relationTypeId'           , trim($data['relationTypeId']))->
							where('ownerId'                  , null)->count();
				}else{
					$cnt = RelationTypeGroup::
							where('relationTypeGroupId', '<>', $id)->
							where('relationAssociationTermId', trim($data['relationAssociationTermId']))->
							where('relationTypeId'           , trim($data['relationTypeId']))->
							where('ownerId'                  , trim($tmpOrgID))->count();
				}
			}else{
				$cnt = RelationTypeGroup::
							where('relationTypeGroupId', '<>', $id)->
							where('relationAssociationTermId', trim($data['relationAssociationTermId']))->
							where('relationTypeId'           , trim($data['relationTypeId']))->
							where('ownerId'                  , trim($orgID))->count();
			}
			if($cnt!=0){ return ['result'=>1, 'msg'=>"Relation type group is exist on this organization"]; }
			//-------------------------------
			$tmp = RelationTypeGroup::find($id);
			if(is_null($tmp) ){ return ['result'=>1, 'msg'=>"Relation type  group not found"]; }
			//-------------------------------
			else{
				$tmp->relationAssociationTermId = trim($data['relationAssociationTermId']);
				$tmp->relationTypeId            = trim($data['relationTypeId']);
				$tmp->description               = trim($data['description']);
				$tmp->ssReserved                = trim($data['ssReserved']);
				$tmp->ownership                 = trim($data['ownership']);
				$tmp->ownerId                   = (($orgID==0)? trim($data['ownerId']) :$orgID);
				$tmp->lastUserId                = trim($data['userID']);

				if($tmp->relationAssociationTermId==''){ return ['result'=>1, 'msg'=>'Relation Type Group is empty']; }
				if($tmp->relationTypeId           ==''){ return ['result'=>1, 'msg'=>'Relation Type is empty']; }
				if($tmp->ownerId                  ==''){ return ['result'=>1, 'msg'=>'Owner is empty']; }
				if($tmp->lastUserId               ==''){ return ['result'=>1, 'msg'=>'Last user is empty']; }

				$tmp->ownerId = (($tmp->ownerId==0) ?null :$tmp->ownerId);
				$tmp = $tmp->save();
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
			//-------------------------------
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}

	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			$temp = RelationTypeGroup::find($id);
			if(is_null($temp) ){ return ['result'=>1, 'msg'=>"Relation Type Group not found"]; }
			else{
				$temp = $temp->delete($id);
				return ['result'=>($temp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	private function validFieldName($fieldName){
		switch(strtolower($fieldName)){
			case 'ownership'             : { return "ownership";         }
			case 'ownerid'               : { return "ownerId";           }
			case 'datecreated'           : { return "dateCreated";       }
			case 'lastuserid'            : { return "lastUserId";        }
			case 'organizationshortname' : { return "ownerId";           }
			case 'ownershipcaption'      : { return "ownership";         }
			
			case 'allfields'             : { return "allFields";         }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	private function validSortName($fieldName){
		switch(strtolower($fieldName)){
			case 'relationtypegroup'     : { return "relationTypeGroup";     }
			case 'relationtypename'      : { return "relationTypeName";      }
			case 'ssreserved'            : { return "ssReserved";            }
			
			case 'relationtypegroupname' : { return "relationTypeGroupName"; }
			case 'ownerid'               : { return "ownerId";               }
			case 'datecreated'           : { return "dateCreated";           }
			case 'lastuserid'            : { return "lastUserId";            }
			case 'ownershipcaption'      : { return "ownership";             }
			
			case 'allfields'             : { return "allFields";         }

			case 'ownership'             : { return "ownerShipText";         }
			case 'organizationshortname' : { return "organizationShortName"; }

			default:{ return ''; }
		}
	}
	//---------------------------------------
	public function allShow($orgID){
		//---------------------------------------
		$termID = \Config::get('kama_dei.static.organization_association_ID',0);
		//---------------------------------------
		if($orgID==0){
			$data = RelationTypeGroup::
						leftJoin('kamadeikb.term', 'relation_type_group.relationAssociationTermId', '=', 'term.termId')->
						leftJoin('kamadeikb.relation_type', 'relation_type_group.relationTypeId', '=', 'relation_type.relationTypeId')->
						where('relation_type_group.relationAssociationTermId', $termID)->
						select(
							'relationTypeGroupId as relationTypeGroupId',
							'relation_type.relationTypeName as relationTypeGroupName'
							/*
							\DB::raw(
								'CONCAT('.
									'term.termName," ",'.
									'relation_type.relationTypeName," ",'.
									'(select t.termName from kamadeikb.term t where t.termId='.$termID.') )'.
								'as relationTypeGroupName'
								)
							*/
						)->get();
		}else{
			$data = RelationTypeGroup::
						leftJoin('kamadeikb.term', 'relation_type_group.relationAssociationTermId', '=', 'term.termId')->
						leftJoin('kamadeikb.relation_type', 'relation_type_group.relationTypeId', '=', 'relation_type.relationTypeId')->
						where('relation_type_group.relationAssociationTermId', $termID)->
						where('relation_type_group.ownerId', $orgID)->
						orwhere(function($q){
							return $q
								->where('relation_type_group.ownerId', null)
								->where('relation_type_group.ownership', 0);
						})->
						select(
							'relationTypeGroupId as relationTypeGroupId',
							'relation_type.relationTypeName as relationTypeGroupName'
							/*
							\DB::raw(
								'CONCAT('.
									'term.termName," ",'.
									'relation_type.relationTypeName," ",'.
									'(select t.termName from kamadeikb.term t where t.termId='.$termID.') )'.
								'as relationTypeGroupName'
								)
							*/
						)->get();
		}
		return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
	}
	//---------------------------------------
	public function getOwnersList($orgID){
		$data  = RelationTypeGroup::getOwnersList($orgID);
		if($data!=null){
			$tmp = array_map('strtolower',  array_column($data, 'text'));
			array_multisort($tmp, SORT_NATURAL, $data);
		}
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
}
