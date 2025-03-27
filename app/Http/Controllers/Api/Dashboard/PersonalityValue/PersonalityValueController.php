<?php

namespace App\Http\Controllers\Api\Dashboard\PersonalityValue;

use Illuminate\Http\Request;
use App\Term;
use App\PersonalityValue;
use App\Controllers;

class PersonalityValueController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function show($orgID, $id){
		$data = PersonalityValue::find($id);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[]];
		}else{
			return ['result'=>0, 'msg'=>'', 'data'=>$data];
		}
	}
	public function search($orgID, $prsnltyID, $field, $value){
		//---------------------------------------
		$field = $this->validFieldName( $field );
		if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//---------------------------------------
		$data = PersonalityValue::where($field, 'like', "%{$value}%")->where('personalityId', '=', $prsnltyID)->get();
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
		$count = PersonalityValue::count();
		$data  = PersonalityValue::orderBy($sort, $order)->get();
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	//---------------------------------------
	public function showPage($orgID, $prsnltyID, $ownerID, $perPage, $page){
		$count = count(PersonalityValue::myPersonalityValue($orgID, $prsnltyID, '', '')->get());
		$data  = PersonalityValue::myPageing($orgID, $prsnltyID, $perPage, $page, 'termId', 'asc');
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	public function showPageSorted($orgID, $prsnltyID, $ownerID, $sort, $order, $perPage, $page){
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
		$count = count(PersonalityValue::myPersonalityValue($orgID, $prsnltyID, $ownerID, '', '')->get());
		$data  = PersonalityValue::myPageing($orgID, $prsnltyID, $ownerID, $perPage, $page, $sort, $order);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data, 'BHR']; }
	}
	public function showPageSortSearch($orgID, $prsnltyID, $ownerID, $sort, $order, $perPage, $page, $field, $value){
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
		$count = count(PersonalityValue::myPersonalityValue($orgID, $prsnltyID, $ownerID, $field, $value)->get());
		$data  = PersonalityValue::myPageingWithSearch($orgID, $prsnltyID, $ownerID, $perPage, $page, $sort, $order, $field, $value);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function newEditRow($orgID, $prsnltyID, $reservd, $id, Request $request){ return $this->editRow($orgID, $prsnltyID, $id, $request); }
	public function editRow($orgID, $prsnltyID, $id, Request $request){
		try{
			$tmp = PersonalityValue::find($id);
			if(is_null($tmp) ){ return ['result'=>1, 'msg'=>"Personality Value not found"]; }
			else{
				$tmp->personalityValueId = trim($request->input('personalityValueId'));
				$tmp->personalityId      = trim($request->input('personalityId'     ));
				$tmp->personTermId       = trim($request->input('personTermId'      ));
				$tmp->scalarValue        = trim($request->input('scalarValue'       ));
	
				$tmp->ownership          = trim($request->input('ownership'));
				$tmp->ownerId            = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
				$tmp->lastUserId         = trim($request->input('userID'));

				if($tmp->personalityId==''){ return ['result'=>1, 'msg'=>'Personality Value is empty']; }
				if($tmp->personTermId ==''){ return ['result'=>1, 'msg'=>'Term is empty']; }
				if($tmp->scalarValue  ==''){ return ['result'=>1, 'msg'=>'Scalar Value is empty']; }

				if($tmp->ownership    ==''){ return ['result'=>1, 'msg'=>'Owner ship is empty']; }
				if($tmp->ownerId      ==''){ return ['result'=>1, 'msg'=>'Owner is empty']; }
				if($tmp->lastUserId   ==''){ return ['result'=>1, 'msg'=>'Last user id is empty']; }

				$tmp->ownerId = (($tmp->ownerId==0) ?null :$tmp->ownerId);
				$tmp = $tmp->save();
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function newInsertRow($orgID, $prsnltyID, $reservd, Request $request){ return $this->insertRow($orgID, $prsnltyID, $request); }
	public function insertRow($orgID, $prsnltyID, Request $request){
		try{
			$tmp = new PersonalityValue;
			$tmp->personalityId      = trim($request->input('personalityId'     ));
			$tmp->personTermId       = trim($request->input('personTermId'      ));
			$tmp->scalarValue        = trim($request->input('scalarValue'       ));

			$tmp->dateCreated        = date("Y-m-d H:i:s");
			$tmp->ownership          = trim($request->input('ownership'));
			$tmp->ownerId            = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
			$tmp->lastUserId         = trim($request->input('userID'));

			if($tmp->personalityId==''){ return ['result'=>1, 'msg'=>'Personality Value is empty']; }
			if($tmp->personTermId ==''){ return ['result'=>1, 'msg'=>'Value is empty']; }
			if($tmp->scalarValue  ==''){ return ['result'=>1, 'msg'=>'Scalar Value is empty']; }

			if($tmp->ownership    ==''){ return ['result'=>1, 'msg'=>'Owner ship is empty']; }
			if($tmp->ownerId      ==''){ return ['result'=>1, 'msg'=>'Owner is empty']; }
			if($tmp->lastUserId   ==''){ return ['result'=>1, 'msg'=>'Last user id is empty']; }

			$tmp->ownerId = (($tmp->ownerId==0) ?null :$tmp->ownerId);
			$tmp1 = $tmp->save();
			if($tmp1){ return ['result'=>0, 'personalityValueId'=>$tmp->rtSynonymId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function newDeleteRow($orgID, $prsnltyID, $reservd, $id){ return $this->deleteRow($orgID, $prsnltyID, $id); }
	public function deleteRow($orgID, $prsnltyID, $id){
		try{
			$temp = PersonalityValue::find($id);
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
	public function editScalarValue($orgID, $id, Request $request){
		try{
			$tmp = PersonalityValue::find($id);
			if(is_null($tmp) ){ return ['result'=>1, 'msg'=>"Personality Value not found"]; }
			else{
				if($request->has('isparent') && trim($request->input('isparent'))!='0'){
					$tmp1 = new PersonalityValue;
					$tmp1->personalityId = trim($request->input('isparent'));
					$tmp1->personTermId  = $tmp->personTermId;
					$tmp1->scalarValue   = trim($request->input('scalarValue'));

					$tmp1->dateCreated   = date("Y-m-d H:i:s");
					$tmp1->ownership     = $tmp->ownership;
					$tmp1->ownerId       = $tmp->ownerId;
					$tmp1->lastUserId    = trim($request->input('userID'));

					if($tmp1->lastUserId ==''){ return ['result'=>1, 'msg'=>'Last user id is empty']; }
					$tmp1->save();

				}else{
					$tmp->scalarValue = trim($request->input('scalarValue'));
					$tmp->lastUserId  = trim($request->input('userID'     ));

					if($tmp->lastUserId   ==''){ return ['result'=>1, 'msg'=>'Last user id is empty']; }
					$tmp = $tmp->save();
				}
				return ['result'=>($tmp ?0 :1), 'msg'=>'', 'data'=>$tmp];
			}
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}	
	//---------------------------------------}
	private function validFieldName($fieldName){
		switch(strtolower($fieldName)){
			case 'personalityvalueid'    : { return "personalityValueId";    }
			case 'personalityid'         : { return "personalityId";         }
			case 'personalityname'       : { return "personalityName";       }
			case 'persontermid'          : { return "personTermId";          }
			case 'persontermname'        : { return "personTermName";        }
			case 'scalarvalue'           : { return "scalarValue";           }

			case 'ownership'             : { return "ownership";             }
			case 'ownerid'               : { return "ownerId";               }
			case 'datecreated'           : { return "dateCreated";           }
			case 'lastuserid'            : { return "lastUserId";            }
			case 'organizationshortname' : { return "organizationShortName"; }
			case 'ownershipcaption'      : { return "ownership";             }

			case 'allfields'             : { return "allFields";             }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	private function validSortName($fieldName){
		switch(strtolower($fieldName)){
			case 'personalityvalueid'    : { return "personalityValueId";    }
			case 'personalityid'         : { return "personalityId";         }
			case 'personalityname'       : { return "personalityName";       }
			case 'persontermid'          : { return "personTermId";          }
			case 'persontermname'        : { return "personTermName";        }
			case 'scalarvalue'           : { return "scalarValue";           }

			case 'ownership'             : { return "ownership";             }
			case 'ownerid'               : { return "ownerId";               }
			case 'datecreated'           : { return "dateCreated";           }
			case 'lastuserid'            : { return "lastUserId";            }
			case 'organizationshortname' : { return "ownerId";               }
			case 'ownershipcaption'      : { return "ownership";             }

			case 'personalized'      : { return "personalized";             }
				
			default:{ return ''; }
		}
	}
	//---------------------------------------
}
