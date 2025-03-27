<?php

namespace App\Http\Controllers\Api\Dashboard\OrganizationAssociation;

use Illuminate\Http\Request;
use App\OrganizationAssociation;

use Illuminate\Support\Facades\Config;

class OrganizationAssociationController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function showPageSorted($orgID, $sort, $order, $perPage, $page){
		//---------------------------------------
		$sort = $this->validSortName( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		//---------------------------------------
		$staticTermID = \Config::get('kama_dei.static.RELATION_ASSOCIATION_ID',0);
		//---------------------------------------
		$count = OrganizationAssociation::myOrganizationAssociation($orgID, '', '', $staticTermID)->count();
		$data  = OrganizationAssociation::myPageing($orgID, $perPage, $page, $sort, $order, $staticTermID);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function showPageSortSearch($orgID, $sort, $order, $perPage, $page, $field, $value){
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
		$staticTermID = \Config::get('kama_dei.static.RELATION_ASSOCIATION_ID',0);
		//---------------------------------------
		$count = OrganizationAssociation::myOrganizationAssociation($orgID, $field, $value, $staticTermID)->count();
		$data  = OrganizationAssociation::myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value, $staticTermID);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			//-------------------------------
			$data = $request->all();
			//-------------------------------
			if($data['leftOrgId']==0){
				$OrganizationAssociationTmp  = OrganizationAssociation::
													where('leftOrgId', null )->
													where('relationTypeGroupId', $data['relationTypeGroupId'] )->
													where('rightOrgId', $data['rightOrgId'] )->
													count();
			}else{
				$OrganizationAssociationTmp  = OrganizationAssociation::
													where('leftOrgId', $data['leftOrgId'] )->
													where('relationTypeGroupId', $data['relationTypeGroupId'] )->
													where('rightOrgId', $data['rightOrgId'] )->
													count();
			}
			if( $OrganizationAssociationTmp!=0 ){ return ['result'=>1, 'msg'=>'Organization association already exists for this relation type and organization']; }
			//-------------------------------
			$OrganizationAssociationTmp = new OrganizationAssociation;

			$OrganizationAssociationTmp->leftOrgId           = trim($data['leftOrgId'          ]);
			$OrganizationAssociationTmp->relationTypeGroupId = trim($data['relationTypeGroupId']);
			$OrganizationAssociationTmp->rightOrgId          = trim($data['rightOrgId'         ]);
			$OrganizationAssociationTmp->lastUserId          = trim($data['userID'             ]);
			$OrganizationAssociationTmp->dateCreated         = date("Y-m-d H:i:s");
			
			if($OrganizationAssociationTmp->leftOrgId           ==''){ return ['result'=>1, 'msg'=>'Left Organization is empty']; }
			if($OrganizationAssociationTmp->rightOrgId          ==''){ return ['result'=>1, 'msg'=>'Right Organization is empty']; }
			if($OrganizationAssociationTmp->relationTypeGroupId ==''){ return ['result'=>1, 'msg'=>'Relation Type Group is empty']; }
			if($OrganizationAssociationTmp->lastUserId          ==''){ return ['result'=>1, 'msg'=>'User is empty']; }
			
			$OrganizationAssociationTmp->leftOrgId = (($OrganizationAssociationTmp->leftOrgId==0) ?null :$OrganizationAssociationTmp->leftOrgId);
			//-------------------------------
			$tmp = $OrganizationAssociationTmp->save();
			if($tmp){ return ['result'=>0, 'orgAssociationId'=>$OrganizationAssociationTmp->orgAssociationId]; }
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
			$tmp = OrganizationAssociation::find($id);
			if( is_null($tmp) ){ return ['result'=>1, 'msg'=>"Organization association not found"]; }
			//-------------------------------
			$data = $request->all();
			//-------------------------------
			if($data['leftOrgId']==0){
				$orgRelationTypeTmp  = OrganizationAssociation::
										where('leftOrgId', null )->
										where('relationTypeGroupId', $data['relationTypeGroupId'] )->
										where('rightOrgId', $data['rightOrgId'] )->
										where('orgAssociationId', '<>', $id )->
										count();
			}else{
				$orgRelationTypeTmp  = OrganizationAssociation::
										where('leftOrgId', $data['leftOrgId'] )->
										where('relationTypeGroupId', $data['relationTypeGroupId'] )->
										where('rightOrgId', $data['rightOrgId'] )->
										where('orgAssociationId', '<>', $id )->
										count();
			}
			if( $orgRelationTypeTmp!=0 ){ return ['result'=>1, 'msg'=>'Organization association already exists for this relation type and organization']; }
			//-------------------------------
			$tmp->leftOrgId           = trim($data['leftOrgId'          ]);
			$tmp->relationTypeGroupId = trim($data['relationTypeGroupId']);
			$tmp->rightOrgId          = trim($data['rightOrgId'         ]);
			$tmp->lastUserId          = trim($data['userID'             ]);
			
			if($tmp->leftOrgId           ==''){ return ['result'=>1, 'msg'=>'Left Organization is empty']; }
			if($tmp->rightOrgId          ==''){ return ['result'=>1, 'msg'=>'Right Organization is empty']; }
			if($tmp->relationTypeGroupId ==''){ return ['result'=>1, 'msg'=>'Relation Type Group is empty']; }
			if($tmp->lastUserId          ==''){ return ['result'=>1, 'msg'=>'User is empty']; }

			$tmp->leftOrgId = (($tmp->leftOrgId==0) ?null :$tmp->leftOrgId);
			$tmp = $tmp->save();
			return ['result'=>($tmp ?0 :1), 'msg'=>''];
			//-------------------------------
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			$temp = OrganizationAssociation::find($id);
			if(is_null($temp) ){ return ['result'=>1, 'msg'=>"Organization association not found"]; }
			else{
				//---------------------------
				$temp = $temp->delete($id);
				//---------------------------
				return ['result'=>($temp ?0 :1), 'msg'=>''];
				//---------------------------
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	private function validSortName($fieldName){
		switch(strtolower($fieldName)){
			case 'leftorgname'     : { return "leftOrgName"; }
			case 'rightorgname'    : { return "rightOrgName"; }
			case 'relationtypename': { return "relationTypeName"; }
			case 'datecreated'     : { return "dateCreated"; }
			case 'relationtypegroupname': { return "relationTypeGroupName"; }
			
			default:{ return ''; }
		}
	}
	//---------------------------------------
	private function validFieldName($fieldName){
		switch(strtolower($fieldName)){
			case 'leftorgname'     : { return "leftOrgName"; }
			case 'rightorgname'    : { return "rightOrgName"; }
			case 'relationtypename': { return "relationTypeName"; }
			case 'datecreated'     : { return "dateCreated"; }

			default:{ return ''; }
		}
	}
	//---------------------------------------
}