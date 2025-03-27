<?php

namespace App\Http\Controllers\Api\Dashboard\RelationTypeSynonym;

use Illuminate\Http\Request;
use App\RelationTypeSynonym;
use App\RelationType;
use App\Relation;
use App\Controllers;
//use App\Http\Resources\RelationType as RelationTypeResource;

class RelationTypeSynonymController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function show($orgID, $id){
		$data = RelationTypeSynonym::find($id);
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
		$data = RelationTypeSynonym::where($field, 'like', "%{$value}%")->get();
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data]; }
	}
	//---------------------------------------
	public function showAll($orgID){ return $this->showAllSorted($orgID, 'termId', 'asc'); }
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
		$count = RelationTypeSynonym::count();
		$data  = RelationTypeSynonym::orderBy($sort, $order)->get();
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
		$count = RelationTypeSynonym::myRelationTypeSynonym($orgID, '', '')->count();
		$data  = RelationTypeSynonym::myPageing($orgID, $perPage, $page, 'termId', 'asc');
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
		$count = RelationTypeSynonym::myRelationTypeSynonymNew($orgID, $sort, $order, '', '', $ownerId, $shwglblSTT)->count();
		$data  = RelationTypeSynonym::myPageingNew($orgID, $perPage, $page, $sort, $order, '', '', $ownerId, $shwglblSTT);
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
		$count = RelationTypeSynonym::myRelationTypeSynonymNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT)->count();
		$data  = RelationTypeSynonym::myPageingNew($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId, $shwglblSTT);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			$tmp = RelationTypeSynonym::find($id);
			if(is_null($tmp) ){ return ['result'=>1, 'msg'=>"Relation Type Synonym not found"]; }
			else{
				$tmp->rtSynonymDescription    = trim($request->input('rtSynonymDescription'));
				$tmp->rtSynonymRelationTypeId = trim($request->input('rtSynonymRelationTypeId'));
				$tmp->rtSynonymTenseId        = trim($request->input('rtSynonymTenseId'));
				$tmp->rtSynonymDisplayName    = trim($request->input('rtSynonymDisplayName'));
				$tmp->rtSynonymTermId         = trim($request->input('rtSynonymTermId'));

				$tmp->rtIsReserved            = trim($request->input('rtIsReserved'));

				$tmp->ownership               = trim($request->input('ownership'));
				$tmp->ownerId                 = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
				$tmp->lastUserId              = trim($request->input('userID'));

				if($tmp->rtSynonymDescription    ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Description is empty']; }
				if($tmp->rtSynonymDisplayName    ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Name is empty']; }
				if($tmp->rtSynonymRelationTypeId ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Relation Type is empty']; }
				if($tmp->rtSynonymTenseId        ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Tense is empty']; }
				if($tmp->rtSynonymTermId         ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Term is empty']; }

				if($tmp->ownership               ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym owner ship is empty']; }
				if($tmp->ownerId                 ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym owner ID is empty']; }
				if($tmp->lastUserId              ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym last user id is empty']; }

				$tmp->ownerId = (($tmp->ownerId==0) ?null :$tmp->ownerId);
				$tmp = $tmp->save();
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			/*
			$Name = $request->input('rtSynonymDisplayName');
			$RelationTypeSynonymTMP  = RelationTypeSynonym::where('rtSynonymDisplayName','=',strtolower($Name) )->first();
			if(!is_null($RelationTypeSynonymTMP) ){return ['result'=>1, 'msg'=>'Relation Type Synonym Name already exists']; }
			*/
			$tmp = new RelationTypeSynonym;
			$tmp->rtSynonymDescription    = trim($request->input('rtSynonymDescription'));
			$tmp->rtSynonymRelationTypeId = trim($request->input('rtSynonymRelationTypeId'));
			$tmp->rtSynonymTenseId        = trim($request->input('rtSynonymTenseId'));
			$tmp->rtSynonymDisplayName    = trim($request->input('rtSynonymDisplayName'));
			$tmp->rtSynonymTermId         = trim($request->input('rtSynonymTermId'));

			$tmp->rtIsReserved            = trim($request->input('rtIsReserved'));

			$tmp->dateCreated             = date("Y-m-d H:i:s");
			$tmp->ownership               = trim($request->input('ownership'));
			$tmp->ownerId                 = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
			$tmp->lastUserId              = trim($request->input('userID'));

			if($tmp->rtSynonymDescription    ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Description is empty']; }
			if($tmp->rtSynonymDisplayName    ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Name is empty']; }
			if($tmp->rtSynonymRelationTypeId ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Relation Type is empty']; }
			if($tmp->rtSynonymTenseId        ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Tense is empty']; }
			if($tmp->rtSynonymTermId         ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Term is empty']; }

			if($tmp->rtIsReserved            ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym Reserved is empty']; }

			if($tmp->ownership               ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym owner ship is empty']; }
			if($tmp->ownerId                 ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym owner ID is empty']; }
			if($tmp->lastUserId              ==''){ return ['result'=>1, 'msg'=>'Relation Type Synonym last user id is empty']; }

			$tmp->ownerId = (($tmp->ownerId==0) ?null :$tmp->ownerId);
			$tmp1 = $tmp->save();
			if($tmp1){ return ['result'=>0, 'rtSynonymId'=>$tmp->rtSynonymId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			$temp = RelationTypeSynonym::find($id);
			if(is_null($temp) ){ return ['result'=>1, 'msg'=>"Relation Type Synonym not found"]; }
			else{
				if($temp->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this relation type"]; }
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
			case 'rtsynonymid'             : { return "rtSynonymId";                 }
			case 'rtsynonymdescription'    : { return "rtSynonymDescription";        }
			case 'rtSynonymrelationtypeid' : { return "rtSynonymRelationTypeId";     }
			case 'rtsynonymtenseid'        : { return "rtSynonymTenseId";            }
			case 'rtsynonymtermid'         : { return "rtSynonymTermId";             }
			case 'rtsynonymdisplayname'    : { return "rtSynonymDisplayName";        }
			case 'rtisreserved'            : { return "rtIsReserved";                }
			case 'ownership'               : { return "ownership";                   }
			case 'ownerid'                 : { return "ownerId";                     }
			case 'datecreated'             : { return "dateCreated";                 }
			case 'lastuserid'              : { return "lastUserId";                  }
			case 'organizationshortname'   : { return "organizationShortName";       }
			case 'ownershipcaption'        : { return "ownership";                   }

			case 'allfields'               : { return "allFields";                   }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	private function validSortName($fieldName){
		switch(strtolower($fieldName)){
			case 'rtsynonymid'               : { return "rtSynonymId";             }
			case 'rtsynonymdescription'      : { return "rtSynonymDescription";    }
			case 'rtsynonymrelationtypeid'   : { return "rtSynonymRelationTypeId"; }
			case 'rtsynonymtenseid'          : { return "rtSynonymTenseId";        }
			case 'rtsynonymtermid'           : { return "rtSynonymTermId";         }
			case 'rtsynonymdisplayname'      : { return "rtSynonymDisplayName";    }
			case 'rtisreserved'              : { return "rtIsReserved";            }
			case 'ownerid'                   : { return "ownerId";                 }
			case 'datecreated'               : { return "dateCreated";             }
			case 'lastuserid'                : { return "lastUserId";              }
			case 'ownershipcaption'          : { return "ownership";               }
			default:{ return ''; }

			case 'rtsynonymrelationtypename' : { return "rtSynonymRelationTypeName"; }
			case 'rtsynonymtensename'        : { return "rtSynonymTenseName";        }
			case 'rtsynonymtermname'         : { return "rtSynonymTermName";         }

			case 'ownership'             : { return "ownerShipText";         }
			case 'organizationshortname' : { return "organizationShortName"; }
		}
	}
	//---------------------------------------
	public function getOwnersList($orgID){
		$data  = RelationTypeSynonym::getOwnersList($orgID);
		if($data!=null){
			$tmp = array_map('strtolower',  array_column($data, 'text'));
			array_multisort($tmp, SORT_NATURAL, $data);
		}
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
}
