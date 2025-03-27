<?php

namespace App\Http\Controllers\Api\Dashboard\PersonalityRelationValue;

use Illuminate\Http\Request;
use App\Term;
use App\PersonalityValue;
use App\PersonalityRelation;
use App\PersonalityRelationValue;

use App\Controllers;
//use App\Http\Resources\RelationType as RelationTypeResource;

class PersonalityRelationValueController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function showAll($orgID, $ownrID, $prsID, $showProblemsSolutions=0, $oldParentID=0){ 
		$oldParentID = (($oldParentID==0)? null :$oldParentID);
		//-----------------------------------
		if($ownrID==0){ 
//			$qry = PersonalityRelation::myQuery($orgID, $prsID, $ownrID);
			$qry = PersonalityRelation::myNewQuery($orgID, $prsID, $oldParentID);
			$qry = $qry->where(function($q){
				return $q->where('personality_relation.ownerId', null)->orwhere('personality_relation.ownerId', 0);
			});
		}
		else{
//			$qry = PersonalityRelation::myQuery($orgID, $prsID, $ownrID)->where('personality_relation.ownerId', $ownrID);
			$qry = PersonalityRelation::myNewQuery($orgID, $prsID, $oldParentID)->where('personality_relation.ownerId', $ownrID);
		}
		//-----------------------------------
//		if($ownrID==-1){ $qry = PersonalityRelation::myQuery($orgID, $prsID, $ownrID); }
		if($ownrID==-1){ $qry = PersonalityRelation::myNewQuery($orgID, $prsID, $oldParentID); }
		//-----------------------------------
/*
		switch($showProblemsSolutions){
			case 1:{
//				$qry = $qry->where('netRating','>=', 0);
				$qry = $qry->whereRaw(
						"(".
							"select SUM(scalarValue) ".
							"FROM personality_relation_value ".
							"where personality_relation_value.personalityRelationId = personality_relation.personalityRelationId ".
						") >= 0"
				);
				break;
			}
			case 2:{
//				$qry = $qry->where('netRating','<', 0);
				$qry = $qry->whereRaw(
						"(".
							"select SUM(scalarValue) ".
							"FROM personality_relation_value ".
							"where personality_relation_value.personalityRelationId = personality_relation.personalityRelationId ".
						") < 0"
				);
				break;
			}
			case 0:
			default:{
			}
		}
*/
		$data = $qry->get();
		if(!$data->isEmpty()){
			$newData = [];
			foreach($data as $key=>$row){
				switch($showProblemsSolutions){
					case 1:{
						if($row->netRating!=null && intval($row->netRating)>=0){ $newData[] = $row; }
						continue;
					}
					case 2:{
						if($row->netRating==null){ $row->netRating=-99999; }
						if(intval($row->netRating)<0){ $newData[] = $row; }
						continue;
					}
					case 0:
					default:{
						$newData[] = $row;
						continue;
					}
				}
			}
			$data = $newData;
		}
		return ['result'=>0, 'msg'=>$ownrID, 'total'=>count($data), 'data'=>$data ];
	}
	//---------------------------------------
	public function showAllValue($orgID, $personalityRelationId, Request $request){ 
		$isParent = 0;
		if($request->has('isparent')){ $isParent = trim($request->input('isparent')); }
		$qry = PersonalityRelationValue::myQuery($orgID, $personalityRelationId, $isParent);
		$data = $qry->get();
		$netRating = 0;
		if(!$data->isEmpty()){
			foreach($data as $row){ $netRating+=$row->scalarValue; }
			foreach($data as $row){
				$row->uniqueid      = $personalityRelationId;
				$row->netRating     = $netRating;
				$row->netRatingText = (($netRating>=0) ?'solution' :'problem');
			}
		}
		return [
			'result'=>0,
			'msg'   =>'',
			
			'total'=>$data->count(),
			'data' =>$data,
			
			'netRating'    =>$netRating,
			'netRatingText'=>(($netRating>=0) ?'solution' :'problem')
		];
//		return ['result'=>0, 'msg'=>'', 'total'=>(($data->isEmpty()) ?0 :count($data)), 'data'=>$data];
	}
	//---------------------------------------
	public function editScalarValue($id, Request $request){
		try{
			$personalityRelationId = 0;
			$ownerId = trim($request->input('ownerId'));
			if($ownerId==0){ $tmp = PersonalityRelationValue::find($id); }
			else{ $tmp = PersonalityRelationValue::where('personalityRelationValueId', $id)->where('ownerId', $ownerId)->first(); }
			if(is_null($tmp) ){ return ['result'=>1, 'msg'=>"You can't change this Scaler value"]; }
			else{
				$personalityRelationId = $tmp->personalityRelationId;
				if($request->has("isparent") && trim($request->input("isparent"))!=0){
					$basePersonalityId = trim($request->input("isparent"));

					$currPersonalityRelation = \App\PersonalityRelation::find($tmp->personalityRelationId);
					$basePersonalityRelation = \App\PersonalityRelation::where('personalityId', $basePersonalityId)
														->where('relationId', $currPersonalityRelation->relationId)
														->where('ownerId', $currPersonalityRelation->ownerId)
														->first();
					if($basePersonalityRelation==null){//clone
						$tmpPersonalityRelation = \App\PersonalityRelation::find($tmp->personalityRelationId);
						$newPersonalityRelation = new \App\PersonalityRelation;
						$newPersonalityRelation->dateCreated   = date("Y-m-d H:i:s");
						$newPersonalityRelation->lastUserId    = trim($request->input('userID'));
						$newPersonalityRelation->ownerId       = $currPersonalityRelation->ownerId;
						$newPersonalityRelation->ownership     = $currPersonalityRelation->ownership;
						$newPersonalityRelation->relationId    = $currPersonalityRelation->relationId;
						$newPersonalityRelation->personalityId = $basePersonalityId;
						$newPersonalityRelation->save();
						$personalityRelationId = $newPersonalityRelation->personalityRelationId;
						
						$allPersonalityRelationValue = \App\PersonalityRelationValue::
															where('personalityRelationId', $tmp->personalityRelationId)
															->get();
						if( ! $allPersonalityRelationValue->isEmpty() ){
							foreach($allPersonalityRelationValue as $tmpPersonalityRelationValue){
								$newRow = new \App\PersonalityRelationValue;
								$newRow->dateCreated           = date("Y-m-d H:i:s");
								$newRow->lastUserId            = trim($request->input('userID'));
								$newRow->ownerId               = $tmpPersonalityRelationValue->ownerId;
								$newRow->ownership             = $tmpPersonalityRelationValue->ownership;
								$newRow->scalarValue           = $tmpPersonalityRelationValue->scalarValue;
								$newRow->personRelationTermId  = $tmpPersonalityRelationValue->personRelationTermId;
								$newRow->personalityRelationId = $personalityRelationId;
								
								if($tmp->personRelationTermId==$tmpPersonalityRelationValue->personRelationTermId){
									$newRow->scalarValue = trim($request->input('scalarValue'));
								}
								$newRow->save();
							}
						}
					}else{//edit
						$personalityRelationId = $basePersonalityRelation->personalityRelationId;
						$rowPersonalityRelationValue = \App\PersonalityRelationValue::
															where('personalityRelationId', $personalityRelationId)
															->where('personRelationTermId', $tmp->personRelationTermId)
															->first();
						if($rowPersonalityRelationValue!=null){
							$rowPersonalityRelationValue->scalarValue = trim($request->input('scalarValue'));
							$rowPersonalityRelationValue->lastUserId  = trim($request->input('userID'     ));
							$rowPersonalityRelationValue->save();
						}
					}
					$tmpTmp = \App\PersonalityRelation::where('personalityRelationId', $personalityRelationId)
								->leftJoin('personality', 'personality_relation.personalityId', '=', 'personality.personalityId')
								->select("personality.parentPersonaId as parentId")->first();

					return ['result'=>0, 'msg'=>'-', 'personalityRelationId'=>$personalityRelationId, 'parentId'=>$tmpTmp->parentId];
				}else{
					$tmp->scalarValue = trim($request->input('scalarValue'));
					$tmp->lastUserId  = trim($request->input('userID'     ));

					if($tmp->lastUserId ==''){ return ['result'=>1, 'msg'=>'Last user id is empty']; }
					$tmp = $tmp->save();
					return [
						'result'                => ($tmp ?0 :1),
						'msg'                   => ($tmp ?'OK' :'Error on update scaler value'),
						'personalityRelationId' => $personalityRelationId,
						'parentId'              => 0
					];
				}
			}
		}catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function addKnowledgeRecord(Request $request){
		try{
			$tmp = new PersonalityRelation;
			$tmp->personalityId = trim($request->input('personalityID'    ));
			$tmp->relationId    = trim($request->input('knowledgeRecordID'));
			$tmp->ownerId       = trim($request->input('ownerID'));
			$tmp->ownership     = (($tmp->ownerId==0) ?0 :2);
			$tmp->dateCreated   = date("Y-m-d H:i:s");
			$tmp->lastUserId    = trim($request->input('userID'));

			if($tmp->ownerId==0){ $tmp->ownerId = null; }
			
			$rslt = $tmp->save();
			if($rslt){ return ['result'=>0, 'id'=>$tmp->personalityRelationId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function eraseKnowledgeRecord($orgID, $id, $andPersonalities, $erasePersonalities){
		try{
			if($orgID!=0){
				if( PersonalityRelationValue::where('personalityRelationId', $id)->where('ownerId', '<>', $orgID)->count()>0 )
					{ return ['result'=>1, 'msg'=>"You can't delete this Knowledge Record"]; }
			}
			if($andPersonalities==1){// && $erasePersonalities==1){
				$thisPersonalityRelation = \App\PersonalityRelation::find($id);
				$relationId    = $thisPersonalityRelation->relationId;
				$personalityId = $thisPersonalityRelation->personalityId;
				$personalities = \App\Personality::where('parentPersonaId', $personalityId)
										->where(function($q) use($orgID){
											if($orgID==0){ return $q; }
											return $q->where('ownerId', $orgID);
										})
										->select('personalityId as id')
										->get();
				foreach($personalities as $personalitiy){
					$thisPersonalityRelation = \App\PersonalityRelation::where('personalityId', $personalitiy->id)
																		->where('relationId', $relationId)->get();
					if(!$thisPersonalityRelation->isEmpty()){
						$tmp = $this->eraseKnowledgeRecord_($orgID, $thisPersonalityRelation[0]->personalityRelationId); 
						if($tmp['result']!=0){ return $tmp; }
					}
				}
			}
			if($erasePersonalities==1){ return ['result'=>0, 'msg'=>"Knowledge Record Reset"]; }
			return $this->eraseKnowledgeRecord_($orgID, $id);
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function eraseKnowledgeRecord_($orgID, $id){
		try{
			if($orgID!=0){
				if( PersonalityRelationValue::where('personalityRelationId', $id)->where('ownerId', '<>', $orgID)->count()>0 ){
					return ['result'=>1, 'msg'=>"You can't delete this Knowledge Record"];
				}
			}
			if( PersonalityRelationValue::where('personalityRelationId', $id)->count()>0 ){
				if( PersonalityRelationValue::where('personalityRelationId', $id)->delete()===false )
					{ return ['result'=>1, 'msg'=>'Error on Erasing Personality Relation Value']; }
			}
			if( PersonalityRelation::where('personalityRelationId', $id)->delete() )
				{ return ['result'=>0, 'msg'=>'Knowledge Record Erased'];}
				else{ return ['result'=>1, 'msg'=>'Error on Erasing Personality Relation']; }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function createValue(Request $request){
		try{
			if($request->has('isparent') && trim($request->input('isparent'))!=0){
				$personalityId = trim($request->input('isparent'));
				$personalityRelationId = trim($request->input('personalityRelationId'));
				$ownerId = trim($request->input('ownerId'));
				if($ownerId==0 || $ownerId==''){ $ownerId=null; }
				
				$currPersonalityRelation = \App\PersonalityRelation::find($personalityRelationId);
				$basePersonalityRelation = \App\PersonalityRelation::where('personalityId', $personalityId)
													->where('relationId', $currPersonalityRelation->relationId)
													->where(function($q) use($ownerId){
														if($ownerId==null){ return $q->whereNull('ownerId'); }
														else{ return $q->where('ownerId', $ownerId); }
													})
													->first();
				if($basePersonalityRelation!=null){
					$tmp = new PersonalityRelationValue;
					$tmp->personalityRelationId = $basePersonalityRelation->personalityRelationId;
					$tmp->personRelationTermId  = trim($request->input('personRelationTermId'));
					$tmp->scalarValue           = trim($request->input('scalarValue'         ));
					$tmp->ownership             = trim($request->input('ownership'           ));
					$tmp->lastUserId            = trim($request->input('userID'              ));
					$tmp->ownerId               = trim($request->input('ownerId'             ));
					$tmp->dateCreated           = date("Y-m-d H:i:s");

					if($tmp->ownerId==0){ $tmp->ownerId = null; }
					$rslt = $tmp->save();
					$personalityRelationId = $basePersonalityRelation->personalityRelationId;
				}else{
					$newPersonalityRelation = new \App\PersonalityRelation;
					$newPersonalityRelation->dateCreated   = date("Y-m-d H:i:s");
					$newPersonalityRelation->lastUserId    = trim($request->input('userID'));
					$newPersonalityRelation->ownerId       = $currPersonalityRelation->ownerId;
					$newPersonalityRelation->ownership     = $currPersonalityRelation->ownership;
					$newPersonalityRelation->relationId    = $currPersonalityRelation->relationId;
					$newPersonalityRelation->personalityId = $personalityId;
					$newPersonalityRelation->save();
					$personalityRelationId = $newPersonalityRelation->personalityRelationId;
						
					$allPersonalityRelationValue = \App\PersonalityRelationValue::
														where('personalityRelationId', $currPersonalityRelation->personalityRelationId)
														->get();
					if( ! $allPersonalityRelationValue->isEmpty() ){
						foreach($allPersonalityRelationValue as $tmpPersonalityRelationValue){
							$newRow = new \App\PersonalityRelationValue;
							$newRow->dateCreated           = date("Y-m-d H:i:s");
							$newRow->lastUserId            = trim($request->input('userID'               ));
							$newRow->ownerId               = trim($request->input('ownerId'              ));
							$newRow->ownership             = trim($request->input('ownership'            ));
							$newRow->scalarValue           = $tmpPersonalityRelationValue->scalarValue;
							$newRow->personRelationTermId  = $tmpPersonalityRelationValue->personRelationTermId;
							$newRow->personalityRelationId = $personalityRelationId;
							$newRow->save();
						}
						$rslt = true;
					}
					$newRow = new \App\PersonalityRelationValue;
					$newRow->dateCreated           = date("Y-m-d H:i:s");
					$newRow->lastUserId            = trim($request->input('userID'               ));
					$newRow->ownerId               = trim($request->input('ownerId'              ));
					$newRow->ownership             = trim($request->input('ownership'            ));
					$newRow->scalarValue           = trim($request->input('scalarValue'          ));
					$newRow->personRelationTermId  = trim($request->input('personRelationTermId' ));
					$newRow->personalityRelationId = $personalityRelationId;

					$rslt = $newRow->save();
				}
			}else{
				$tmp = new PersonalityRelationValue;
				$tmp->personalityRelationId = trim($request->input('personalityRelationId'));
				$tmp->personRelationTermId  = trim($request->input('personRelationTermId' ));
				$tmp->scalarValue           = trim($request->input('scalarValue'          ));
				$tmp->ownership             = trim($request->input('ownership'            ));
				$tmp->ownerId               = trim($request->input('ownerId'              ));
				$tmp->lastUserId            = trim($request->input('userID'               ));
				$tmp->dateCreated           = date("Y-m-d H:i:s");

				if($tmp->ownerId==0){ $tmp->ownerId = null; }
				$rslt = $tmp->save();
				$personalityRelationId = $tmp->personalityRelationId;
			}
			
			if($rslt){ return ['result'=>0, 'id'=>$personalityRelationId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function eraseKnowledgeRecordValue($orgID, $id){
		try{
			if($orgID==0){
				if( PersonalityRelationValue::where('personalityRelationValueId', $id)->delete() ){ return ['result'=>0, 'msg'=>'Record Deleted']; }
				else{ return ['result'=>1, 'msg'=>'Error on Erasing...']; }
			}else{
				if( PersonalityRelationValue::where('personalityRelationValueId', $id)->where('ownerId', $orgID)->delete() )
					{ return ['result'=>0, 'msg'=>'Record Deleted']; }
				else{ return ['result'=>1, 'msg'=>'Error on Erasing...']; }
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function copyKRs($rID, Request $request){
		try{
			$data = $request->all();
			//-------------------------------
			$allPersonaA = [];
			switch($data['howToCopy']){
				case 1:{//All Personas
					$allPersonaO = \App\Personality::where('parentPersonaId', 0)->where('ownerId', $data['destOrgId'])->select('personalityId')->get();
					if( $allPersonaO!=null ){ foreach($allPersonaO as $tmp){ $allPersonaA[]=$tmp->personalityId; } }
					break;
				}
				case 2:{//All Personalities and all Personas
					$allPersonaO = \App\Personality::where('ownerId', $data['destOrgId'])->select('personalityId')->get();
					if( $allPersonaO!=null ){ foreach($allPersonaO as $tmp){ $allPersonaA[]=$tmp->personalityId; } }
					break;
				}
				case 3:{//All Personalities produced from this Persona
					$allPersonaO = \App\Personality::where('parentPersonaId', $data['pID'])->where('ownerId', $data['destOrgId'])->select('personalityId')->get();
					if( $allPersonaO!=null ){ foreach($allPersonaO as $tmp){ $allPersonaA[]=$tmp->personalityId; } }
					break;
				}
				case 4:{// A Specific persona
					$allPersonaO = \App\Personality::where('personalityId', $data['destPersonalityId'])->select('personalityId')->get();
					if( $allPersonaO!=null ){ foreach($allPersonaO as $tmp){ $allPersonaA[]=$tmp->personalityId; } }
					break;
				}
				case 5:{//A specific persona and its personalities
					$allPersonaO = \App\Personality::where('personalityId', $data['destPersonalityId'])
									->orWhere('parentPersonaId', $data['destPersonalityId'])->select('personalityId')->get();
					if( $allPersonaO!=null ){ foreach($allPersonaO as $tmp){ $allPersonaA[]=$tmp->personalityId; } }
					break;
				}
				case 6:{//A specific personality
					$allPersonaO = \App\Personality::where('personalityId', $data['destPersonalityId'])->select('personalityId')->get();
					if( $allPersonaO!=null ){ foreach($allPersonaO as $tmp){ $allPersonaA[]=$tmp->personalityId; } }
					break;
				}
				default:{}
			}
			//-------------------------------
			foreach($allPersonaA as $allPersona){
				$tmpPR  = \App\PersonalityRelation::where('personalityRelationId', $data['personalityRelationId'])->first();
				$tmpCnt = \App\PersonalityRelation::where('ownerId', $data['destOrgId'])->where('personalityId', $allPersona)->
							where('relationId', $tmpPR->relationId)->count();
				if( $tmpCnt==0){
					$tmp = new \App\PersonalityRelation();
					$tmp->personalityId = $allPersona;
					$tmp->relationId = $tmpPR->relationId;
					$tmp->ownership = $tmpPR->ownership;
					$tmp->ownerId = $data['destOrgId'];
					$tmp->dateCreated = date("Y-m-d H:i:s");
					$tmp->lastUserId = $data['userID'];
					$tmp->save();
				}
			}
			//-------------------------------
			$allPRo = \App\PersonalityRelation::where('ownerId', $data['destOrgId'])
												->whereIn('personalityId', $allPersonaA)
												->where('relationId', $data['krID'])
												->select('personalityRelationId')
												->get();
			if( $allPRo!=null ){ foreach($allPRo as $tmp){ $allPRa[]=$tmp->personalityRelationId; } }
			//-------------------------------
			if( $allPRo!=null ){
				foreach($allPRo as $allPR){
					if( $data['addMergeUpdate']==2){
						\App\PersonalityRelationValue::where('personalityRelationId', $allPR->personalityRelationId)->delete();
					}
					$tmps = \App\PersonalityRelationValue::where('personalityRelationId', $data['personalityRelationId'])->get();
					if($tmps!=null){
						foreach($tmps as $tmp){
							$tmpC = \App\PersonalityRelationValue::where('personalityRelationId', $allPR->personalityRelationId)->
																	where('personRelationTermId', $tmp->personRelationTermId)->count();
							if($tmpC!=0){
/*
								if( $data['addMergeUpdate']==2){
									\App\PersonalityRelationValue::where('personalityRelationId', $allPR->personalityRelationId)->
																	where('personRelationTermId', $tmp->personRelationTermId)->
																	update(['scalarValue'=>$tmp->scalarValue]);
								}
*/
								\App\PersonalityRelationValue::where('personalityRelationId', $allPR->personalityRelationId)->
																where('personRelationTermId', $tmp->personRelationTermId)->
																update(['scalarValue'=>$tmp->scalarValue]);
							}else{
								$tmpC = new \App\PersonalityRelationValue();
								$tmpC->personalityRelationId = $allPR->personalityRelationId;
								$tmpC->personRelationTermId = $tmp->personRelationTermId;
								$tmpC->scalarValue = $tmp->scalarValue;
								$tmpC->ownership = $tmp->ownership;
								$tmpC->ownerId = $data['destOrgId'];
								$tmpC->dateCreated = date("Y-m-d H:i:s");
								$tmpC->lastUserId = $data['userID'];
								$tmpC->save();
							}
						}
					}
				}
			}
			//-------------------------------
			return ['result'=>0];
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getCopyToOrgs($rID){
//		if($rID==0){ $tmpOrgIDs = \App\Organization::all(); }
//		else{ $tmpOrgIDs = \App\Organization::myOrgRelated($rID)->get(); }
		$tmpOrgIDs = \App\Organization::myOrgRelated($rID)->get();
		
		$tmpOrgIDs = json_decode(json_encode($tmpOrgIDs), true);
		$tmp = array_column($tmpOrgIDs, 'organizationShortName');
		array_multisort($tmp, SORT_NATURAL, $tmpOrgIDs);

		return $tmpOrgIDs;
	}
	//---------------------------------------
	public function getKRcaption($ID){
		/*
		$tmpTBL = new \App\Relation();
		$retVal = $tmpTBL->findById($ID)->get();
		*/
		$retVal = \App\PersonalityRelation::knowledgeRecord($ID)->first();
		
		return $retVal;
	}
	//---------------------------------------
}
