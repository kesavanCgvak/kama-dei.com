<?php

namespace App\Http\Controllers\Api\Dashboard\RelationType;

use Illuminate\Http\Request;
use App\RelationType;
use App\Relation;
use App\Controllers;
//use App\Http\Resources\RelationType as RelationTypeResource;

class RelationTypeController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function show($orgID, $id){
		$data = RelationType::find($id);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[]];
		}else{
			return ['result'=>0, 'msg'=>'', 'data'=>$data];
		}
	}
	public function search($orgID, $field, $value){
		//---------------------------------------
		$field = $this->validFieldName( $field );
		if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$data = RelationType::where($field, 'like', "%{$value}%")->get();
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data]; }
	}
	//---------------------------------------
	public function showAll($orgID){ return $this->showAllSorted($orgID, 'relationTypeName', 'asc'); }
	public function showAllSorted($orgID, $sort, $order){
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
		$count = RelationType::count();
		$data  = RelationType::orderBy($sort, $order)->get();
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
			/*
			$order = strtolower($order);
			switch($order){
				case 'asc' :{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data->sortBy($sort)->values()->all()]; }
				case 'desc':{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data->sortByDesc($sort)->values()->all()]; }
				default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
			}
			*/
		}
	}
	//---------------------------------------
	public function showPage($orgID, $perPage, $page){
		$count = RelationType::myelationTypes($orgID, '', '')->count();
		$data  = RelationType::myPageing($orgID, $perPage, $page, 'termId', 'asc');
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	public function showPageSorted($orgID, $sort, $order, $perPage, $page, $ownerId, $shwglblSTT=1){
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
		$count = RelationType::myRelationTypesNew($orgID, $sort, $order, '', '', $ownerId, $shwglblSTT)->count();
		$data  = RelationType::myPageingNew($orgID, $perPage, $page, $sort, $order, '', '', $ownerId, $shwglblSTT);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	public function showPageSortSearch($orgID, $sort, $order, $perPage, $page, $field, $value, $ownerId, $shwglblSTT=1){
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
		$count = RelationType::myRelationTypesNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT)->count();
		$data  = RelationType::myPageingNew($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId, $shwglblSTT);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			$tmp = RelationType::find($id);
			if(is_null($tmp) ){
				return ['result'=>1, 'msg'=>"relation type not found"];
			}else{
				$tmp->relationTypeName             = trim($request->input('relationTypeName'));
				$tmp->relationTypeDescription      = trim($request->input('relationTypeDescription'));
//				try{ $tmp->relationTypeIdOld       = trim($request->input('relationTypeIdOld')); }catch(Exception $ex){}
//				$tmp->relationTypeClassificationId = trim($request->input('relationTypeClassificationId'));
				$tmp->relationTypeIsReserved       = trim($request->input('relationTypeIsReserved'));
				$tmp->relationTypeOperand          = trim($request->input('relationTypeOperand'));
				$tmp->ownership                    = trim($request->input('ownership'));
				$tmp->ownerId                      = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
//				$tmp->lastUserId                   = trim($request->input('lastUserId'));
				$tmp->lastUserId                   = trim($request->input('userID'    ));

				if($tmp->relationTypeName             ==''){ return ['result'=>1, 'msg'=>'RelationType name is empty']; }
//				if($tmp->relationTypeDescription      ==''){ return ['result'=>1, 'msg'=>'RelationType Description is empty']; }
//				if($tmp->relationTypeClassificationId ==''){ return ['result'=>1, 'msg'=>'RelationType Classification ID is empty']; }
//				if($tmp->relationTypeOperand          ==''){ return ['result'=>1, 'msg'=>'RelationType Operand is empty']; }
				if($tmp->ownership                    ==''){ return ['result'=>1, 'msg'=>'RelationType owner ship is empty']; }
				if($tmp->ownerId                      ==''){ return ['result'=>1, 'msg'=>'RelationType owner ID is empty']; }
				if($tmp->lastUserId                   ==''){ return ['result'=>1, 'msg'=>'RelationType last user id is empty']; }

				$tmp->ownerId = (($tmp->ownerId==0) ?null :$tmp->ownerId);
				$tmp = $tmp->save();
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			$relationTypeName = $request->input('relationTypeName');
			$RelationTypetmp  = RelationType::where('relationTypeName','=',strtolower($relationTypeName) )->first();
			if(!is_null($RelationTypetmp) ){
				if($RelationTypetmp->termIsReserved==1){ return ['result'=>1, 'msg'=>'This is a reserved relation type']; }
				else{ return ['result'=>1, 'msg'=>'relation type already exists']; }
			}
			$RelationTypetmp = new RelationType;
			$RelationTypetmp->relationTypeName             = trim($request->input('relationTypeName'));
//			try{ $RelationTypetmp->relationTypeIdOld       = trim($request->input('relationTypeIdOld')); }catch(Exception $ex){}
			$RelationTypetmp->relationTypeDescription      = trim($request->input('relationTypeDescription'));
//			$RelationTypetmp->relationTypeClassificationId = trim($request->input('relationTypeClassificationId'));
			$RelationTypetmp->relationTypeIsReserved       = trim($request->input('relationTypeIsReserved'));
			$RelationTypetmp->relationTypeOperand          = trim($request->input('relationTypeOperand'));
			$RelationTypetmp->ownership                    = trim($request->input('ownership'));
			$RelationTypetmp->ownerId                      = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
			$RelationTypetmp->dateCreated                  = date("Y-m-d H:i:s");//$request->input('dateCreated'   );
//			$RelationTypetmp->lastUserId                   = trim($request->input('lastUserId'));
			$RelationTypetmp->lastUserId                   = trim($request->input('userID'    ));

			if($RelationTypetmp->relationTypeName             ==''){ return ['result'=>1, 'msg'=>'RelationType name is empty']; }
//			if($RelationTypetmp->relationTypeDescription      ==''){ return ['result'=>1, 'msg'=>'RelationType Description is empty']; }
//			if($RelationTypetmp->relationTypeClassificationId ==''){ return ['result'=>1, 'msg'=>'RelationType Classification ID is empty']; }
//			if($RelationTypetmp->relationTypeOperand          ==''){ return ['result'=>1, 'msg'=>'RelationType Operand is empty']; }
			if($RelationTypetmp->ownership                    ==''){ return ['result'=>1, 'msg'=>'RelationType owner ship is empty']; }
			if($RelationTypetmp->ownerId                      ==''){ return ['result'=>1, 'msg'=>'RelationType owner ID is empty']; }
			if($RelationTypetmp->lastUserId                   ==''){ return ['result'=>1, 'msg'=>'RelationType last user id is empty']; }

			$RelationTypetmp->ownerId = (($RelationTypetmp->ownerId==0) ?null :$RelationTypetmp->ownerId);
			$tmp = $RelationTypetmp->save();
			if($tmp){ return ['result'=>0, 'relationTypeId'=>$RelationTypetmp->relationTypeId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			$temp = RelationType::find($id);
			if(is_null($temp) ){
				return ['result'=>1, 'msg'=>"Relation Type not found"];
			}else{
				if($temp->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this relation type"]; }
				if( Relation::where('relationTypeId', '=', $id)->count()!=0 )
					{ return ['result'=>1, 'msg'=>"This relation type is used in at least one relation, it can not be deleted."]; }
				$temp = $temp->delete($id);
				return ['result'=>($temp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------}
	private function validFieldName($fieldName){
		switch(strtolower($fieldName)){
			case 'relationtypeid'              : { return "relationTypeId";               }
//			case 'relationtypeidold'           : { return "relationTypeIdOld";            }
			case 'relationtypename'            : { return "relationTypeName";             }
			case 'relationtypedescription'     : { return "relationTypeDescription";      }
			case 'relationtypeclassificationid': { return "relationTypeClassificationId"; }
			case 'relationtypeisreserved'      : { return "relationTypeIsReserved";       }
			case 'relationtypeoperand'         : { return "relationTypeOperand";          }
			case 'ownership'                   : { return "ownership";                    }
			case 'ownerid'                     : { return "ownerId";                      }
			case 'datecreated'                 : { return "dateCreated";                  }
			case 'lastuserid'                  : { return "lastUserId";                   }
			case 'organizationshortname'       : { return "organizationShortName";        }
			case 'ownershipcaption'            : { return "ownership";                    }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	private function validSortName($fieldName){
		switch(strtolower($fieldName)){
			case 'relationtypeid'              : { return "relationTypeId";               }
//			case 'relationtypeidold'           : { return "relationTypeIdOld";            }
			case 'relationtypename'            : { return "relationTypeName";             }
			case 'relationtypedescription'     : { return "relationTypeDescription";      }
//			case 'relationtypeclassificationid': { return "relationTypeClassificationId"; }
			case 'relationtypeisreserved'      : { return "relationTypeIsReserved";       }
			case 'relationtypeoperand'         : { return "relationTypeOperand";          }
			case 'ownerid'                     : { return "ownerId";                      }
			case 'datecreated'                 : { return "dateCreated";                  }
			case 'lastuserid'                  : { return "lastUserId";                   }
			case 'ownershipcaption'            : { return "ownership";                    }

			case 'ownership'             : { return "ownerShipText";         }
			case 'organizationshortname' : { return "organizationShortName"; }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	public function getOwnersList($orgID){
		$data  = RelationType::getOwnersList($orgID);
		if($data!=null){
			$tmp  = array_map('strtolower',  array_column($data, 'text'));
			array_multisort($tmp, SORT_NATURAL, $data);
		}
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
}
