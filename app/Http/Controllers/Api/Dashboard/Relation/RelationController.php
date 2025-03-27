<?php

namespace App\Http\Controllers\Api\Dashboard\Relation;

use Illuminate\Http\Request;
use App\Relation;
use App\Controllers;

class RelationController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function show($orgID, $id){
		$data = Relation::findByIdprotected($id);
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
		$data = Relation::where($field, 'like', "%{$value}%")->get();
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
		$count = Relation::count();
		$tmp   = new Relation;
		$data  = $tmp->knowledgeRecordsAll()->orderBy($sort, $order)->get();
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	//---------------------------------------
	public function showPage($orgID, $perPage, $page){
		$count = Relation::count();
		$data  = Relation::myPageing($orgID, $perPage, $page, 'relationId', 'asc');
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	public function showPageSortedPost(Request $req){
		$data = $req->all();
		if(trim($data['search'])==''){ return $this->showPageSorted($data['orgID'], $data['sort'], $data['order'], $data['limit'], $data['page'], $data['ownerId']); }
		return $this->showPageSortSearch($data['orgID'],$data['sort'],$data['order'],$data['limit'],$data['page'],'allFields',trim($data['search']),$data['ownerId']);
	}
	public function showPageSorted($orgID, $sort, $order, $perPage, $page, $ownerId=-1, $shwglblSTT=1 ){
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
		$count = Relation::myRelationNew($orgID, $sort, $order, '', '', $ownerId, $shwglblSTT)->count();
		$data  = Relation::myPageingNew($orgID, $perPage, $page, $sort, $order, '', '', $ownerId, $shwglblSTT);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	public function showPageSortSearch($orgID, $sort, $order, $perPage, $page, $field, $value, $ownerId=-1, $shwglblSTT=1 ){
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

		$count = Relation::myRelationNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT)->count();
		$data  = Relation::myPageingNew($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId, $shwglblSTT);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			$relation = Relation::find($id);
			if(is_null($relation) ){
				return ['result'=>1, 'msg'=>"Relation not found"];
			}else{
				$relation->leftTermId         = trim($request->input('leftTermId'));
				$relation->relationTypeId     = trim($request->input('relationTypeId'));
				$relation->rightTermId        = trim($request->input('rightTermId'));
//				$relation->shortText          = trim($request->input('shortText'));//substr(trim($request->input('shortText')), 0, 20);
				$relation->relationIsReserved = trim($request->input('relationIsReserved'));
				$relation->relationOperand    = trim($request->input('relationOperand'));
				$relation->ownership          = trim($request->input('ownership'));
				$relation->ownerId            = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
//				$relation->lastUserId         = trim($request->input('lastUserId'));
				$relation->lastUserId         = trim($request->input('userID'    ));

				if($relation->leftTermId     ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Left Term is empty']; }
				if($relation->relationTypeId ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Relation Type is empty']; }
				if($relation->rightTermId    ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Right Term is empty']; }
				if($relation->ownership      ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Ownership is empty']; }
				if($relation->ownerId        ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Owner is empty']; }
				if($relation->lastUserId     ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: last user is empty']; }
//				if($relation->shortText      ==''){ $relation->shortText=null; }

				$relation->ownerId = (($relation->ownerId==0) ?null :$relation->ownerId);
				$relation->save();
				$newReq = new Request();
				$newReq->setMethod('POST');
				$newReq->request->add([
					'relationId'     => $relation->relationId,
					'orgId'          => $relation->ownerId,
					'language_code'  => trim($request->input('languageCode1'  )),
					'shortText'      => trim($request->input('shortText1'     )),
					'optionalText'   => trim($request->input('optionalText1'  )),
					'validationText' => trim($request->input('validationText1'))
				]);
				return self::setRelationLanguage($newReq);
//				return ['result'=>0, 'msg'=>''];
			}
		}catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			$relation = new Relation;
			$relation->leftTermId         = trim($request->input('leftTermId'      ));
			$relation->relationTypeId     = trim($request->input('relationTypeId'));
			$relation->rightTermId        = trim($request->input('rightTermId'));
//			$relation->shortText          = trim($request->input('shortText'));//substr(trim($request->input('shortText')), 0, 20);
			$relation->relationIsReserved = trim($request->input('relationIsReserved'));
			$relation->ownership          = trim($request->input('ownership'     ));
			$relation->ownerId            = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
			$relation->dateCreated        = date("Y-m-d H:i:s");//$request->input('dateCreated'   );
//			$relation->lastUserId         = trim($request->input('lastUserId'    ));
			$relation->lastUserId         = trim($request->input('userID'    ));
			$relation->relationOperand    = '';

			if($relation->leftTermId         ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Left Term is empty']; }
			if($relation->relationTypeId     ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Relation Type is empty']; }
			if($relation->rightTermId        ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Right Term is empty']; }
			if($relation->relationIsReserved ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Reserved is empty']; }
			if($relation->ownership          ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Ownership is empty']; }
			if($relation->ownerId            ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: Owner is empty']; }
			if($relation->lastUserId         ==''){ return ['result'=>1, 'msg'=>'Knowledge Record: last user is empty']; }
//			if($relation->shortText          ==''){ $relation->shortText=null; }

			$relation->ownerId = (($relation->ownerId==0) ?null :$relation->ownerId);
			$relation->save();

			$tmp = new Relation;
			$tmp = $tmp->findById($relation->relationId)->first();
			
			$newReq = new Request();
			$newReq->setMethod('POST');
			$newReq->request->add([
				'relationId'     => $relation->relationId,
				'orgId'          => $relation->ownerId,
				'language_code'  => trim($request->input('languageCode1'  )),
				'shortText'      => trim($request->input('shortText1'     )),
				'optionalText'   => trim($request->input('optionalText1'  )),
				'validationText' => trim($request->input('validationText1'))
			]);
			$returnVal = self::setRelationLanguage($newReq);
			if($returnVal['result']==1){ return $returnVal; }
			return ['result'=>0, 'relationId'=>$relation->relationId, 'knowledgeRecordName'=>$tmp->knowledgeRecordName]; 
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			$relation = Relation::find($id);
			if(is_null($relation) ){
				return ['result'=>1, 'msg'=>"relation not found"];
			}else{
				if($relation->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this knowledge record."]; }
				if(\App\PersonalityRelation::where('relationId', $id)->count()!=0)
					{ return ['result'=>1, 'msg'=>"This knowledge record is used in a personality rating and can not be deleted."]; }
				if(\App\LinkKrToTerm::where('relationId', $id)->count()!=0)
					{ return ['result'=>1, 'msg'=>"This item is being used in a kr-term link , and cannot be deleted."]; }
				

				$relation->delete($id);
				\App\RelationLanguage::where('relationId', $id)->delete();
				return ['result'=>0, 'msg'=>''];
			}
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	private function validFieldName($fieldName){
		switch(strtolower($fieldName)){
			case 'relationid'           : { return "relationId";            }
			case 'relationtypeid'       : { return "relationTypeId";        }
			case 'relationtypename'     : { return "relationTypeName";      }
			case 'lefttermid'           : { return "leftTermId";            }
			case 'lefttermname'         : { return "leftTermName";          }
			case 'righttermid'          : { return "rightTermId";           }
			case 'righttermname'        : { return "rightTermName";         }
			case 'relationisreserved'   : { return "relationIsReserved";    }
			case 'relationoperand'      : { return "relationOperand";       }
			case 'ownership'            : { return "ownership";             }
			case 'ownerid'              : { return "ownerId";               }
			case 'datecreated'          : { return "dateCreated";           }
			case 'lastuserid'           : { return "lastUserId";            }
			case 'allfields'            : { return "allFields";             }
			case 'organizationshortname': { return "organizationShortName"; }
			case 'ownershipcaption'     : { return "ownership";             }

			default:{ return ''; }
		}
	}
	//---------------------------------------
	private function validSortName($fieldName){
		switch(strtolower($fieldName)){
			case 'relationid'           : { return "relationId";          }
			case 'relationtypeid'       : { return "relationTypeId";      }
			case 'relationtypename'     : { return "relationTypeName";    }
			case 'lefttermid'           : { return "leftTermId";          }
			case 'lefttermname'         : { return "leftTermName";        }
			case 'righttermid'          : { return "rightTermId";         }
			case 'righttermname'        : { return "rightTermName";       }
			case 'relationisreserved'   : { return "relationIsReserved";  }
			case 'relationoperand'      : { return "relationOperand";     }
			case 'ownerid'              : { return "ownerId";             }
			case 'datecreated'          : { return "dateCreated";         }
			case 'lastuserid'           : { return "lastUserId";          }
			case 'allfields'            : { return "allFields";           }
			case 'ownershipcaption'     : { return "ownership";           }

			case 'knowledgerecordname'  : { return "knowledgeRecordName"; }
			case 'knowledgerecords'     : { return "knowledgeRecords";    }

			case 'relationgrouptype'    : { return "relationGroupType";   }
				
			case 'extdatalink'          : { return "extDataLink";         }
			case 'linkingkr'            : { return "linkingKR";           }
			case 'krtermlink'           : { return "KRTermLink";          }
			case 'pkr'                  : { return "PKR";                 }

			case 'ownership'            : { return "ownerShipText";         }
			case 'organizationshortname': { return "organizationShortName"; }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	public function allKnowledgeRecords($orgID, $prsnaID){
		$tmp = new Relation;
		if( $orgID==0 ){ $data = $tmp->allKnowledgeRecords($orgID, $prsnaID)->orderBy('knowledgeRecords', 'asc')->get(); }
		else{ 
			$data = $tmp->allKnowledgeRecords($orgID, $prsnaID)
							->where('relation.ownership',0)
							->orwhere('relation.ownerId',$orgID)
							->orderBy('knowledgeRecords', 'asc')->get(); 
		}
		$count = count($data);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	public function allKnowledgeRecordsWithOwner($orgID, $prsnaID, $ownrID, $showGlobal=1){
		
		$data = \App\Relation::myRelationNew($orgID, 'knowledgeRecordName', 'asc', '', '', $ownrID, $showGlobal)
			->whereNotIn('relationId', function($q) use($orgID, $prsnaID, $ownrID){
				$tmp = new \App\PersonalityRelation;
				return $q
						->select('relationId')
						->from($tmp->getTable())
						->where('personalityId',$prsnaID)
						->where('ownerId',$ownrID);
			});
		return ['result'=>0, 'msg'=>'', 'total'=>$data->count(), 'data'=>$data->get()];
	}
	//---------------------------------------
	//Relation Group Type
	//---------------------------------------
	public function showAllRelationGroupTypes($orgID, $sort, $order, $perPage, $page){
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
		$rightTermId    = \Config::get('kama_dei.static.RELATION_GROUP_TYPE_ID',0);
		$tmp   = new Relation;
		$count = $tmp->relationGroupTypes($orgID, $rightTermId, $relationTypeId)->count();
		$data  = $tmp->relationGroupTypesPaging($orgID, $rightTermId, $relationTypeId, $perPage, $page, $sort, $order);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	//---------------------------------------
	public function showAllRelationGroupTypesSearch($orgID, $sort, $order, $perPage, $page, $field, $value){
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
		$rightTermId    = \Config::get('kama_dei.static.RELATION_GROUP_TYPE_ID',0);
		$tmp   = new Relation;
		$count = $tmp->relationGroupTypes($orgID, $rightTermId, $relationTypeId, $field, $value)->count();
		$data  = $tmp->relationGroupTypesPaging($orgID, $rightTermId, $relationTypeId, $perPage, $page, $sort, $order, $field, $value);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	//---------------------------------------
	public function insertRelationGroupTypes($orgID, Request $request){
		try{
			$data = $request->all();
			if(trim($data['termName'])==''){ return ['result'=>1, 'msg'=>'Term Name is empty']; }
			if($orgID==0){
				if($data['ownerId']==0){ $tmp = \App\Term::whereRaw('binary termName=?', [trim($data['termName'])] )->where('ownerId', null)->first(); }
				else{ $tmp = \App\Term::whereRaw('binary termName=?', [trim($data['termName'])] )->where('ownerId', $data['ownerId'] )->first(); }
			}else{ $tmp = \App\Term::whereRaw('binary termName=?', [trim($data['termName'])] )->where('ownerId', $orgID )->first(); }
			if(is_null($tmp) ){
				$tmp = new \App\Term;
				$tmp->termName       = trim($data['termName']);
				$tmp->termIsReserved = 1;
				$tmp->ownership      = 0;
				$tmp->ownerId        = (($orgID==0)? trim($data['ownerId']) :$orgID);;
				$tmp->dateCreated    = date("Y-m-d H:i:s");
				$tmp->lastUserId     = trim($request->input('userID'));
				if($tmp->ownerId ==''){ return ['result'=>1, 'msg'=>'Owner is empty']; }
				$tmp->ownerId        = (($tmp->ownerId==0) ?null :$tmp->ownerId);
				if($tmp->save()==false){ return ['result'=>1, 'msg'=>"Can't create Term"]; }
				$leftTermId = $tmp->termId;
			}else{ $leftTermId = $tmp->termId; }
			
			$relationTypeId = \Config::get('kama_dei.static.is_a_member_of_ID',0);
			$rightTermId    = \Config::get('kama_dei.static.RELATION_GROUP_TYPE_ID',0);
			
			$relation = new Relation;
			$relation->leftTermId         = $leftTermId;
			$relation->relationTypeId     = $relationTypeId;
			$relation->rightTermId        = $rightTermId;
			$relation->relationIsReserved = 1;
			$relation->ownership          = 0;
			$relation->ownerId            = (($orgID==0)? trim($data['ownerId']) :$orgID);
			$relation->dateCreated        = date("Y-m-d H:i:s");
			$relation->lastUserId         = trim($request->input('userID'));
			$relation->relationOperand    = '';

			if($relation->leftTermId         ==''){ return ['result'=>1, 'msg'=>'Relation Left Term ID is empty']; }
			if($relation->relationTypeId     ==''){ return ['result'=>1, 'msg'=>'Relation Type ID is empty']; }
			if($relation->rightTermId        ==''){ return ['result'=>1, 'msg'=>'Relation Right Term ID is empty']; }
			if($relation->relationIsReserved ==''){ return ['result'=>1, 'msg'=>'Relation Reserved is empty']; }
			if($relation->ownerId            ==''){ return ['result'=>1, 'msg'=>'Relation owner ID is empty']; }
			if($relation->lastUserId         ==''){ return ['result'=>1, 'msg'=>'Relation last user id is empty']; }

			$relation->ownerId = (($relation->ownerId==0) ?null :$relation->ownerId);
			$tmp = $relation->save();
			if($tmp){ return ['result'=>0, 'relationId'=>$relation->relationId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function showAllRelationGroupType($orgID){
		//---------------------------------------
		$relationTypeId = \Config::get('kama_dei.static.is_a_member_of_ID',0);
		$rightTermId    = \Config::get('kama_dei.static.RELATION_GROUP_TYPE_ID',0);
		$tmp   = new Relation;
		$data  = $tmp->relationGroupTypes($orgID, $rightTermId, $relationTypeId);
		if( $data->count()==0 ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$data->count(), 'data'=>$data->get()]; }
	}
	//---------------------------------------
	public function getOwnersList($orgID){
		$data  = Relation::getOwnersList($orgID);
		if($data!=null){
			$tmp = array_map('strtolower',  array_column($data, 'text'));
			array_multisort($tmp, SORT_NATURAL, $data);
		}
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
	public function deleteRows(Request $req){
		try{
			$data = $req->all();

			$pass = "";
			if(isset($data['pass']) ){ $pass = trim($data['pass']); }
//			else{ return ['result'=>1, 'msg'=>"Invalid password 1"]; }

			$userID = 0;
			if(isset($data['userID']) && trim($data['userID'])!='' ){ $userID = trim($data['userID']); }
			else{ return ['result'=>1, 'msg'=>"Invalid user"]; }

			$IDs = [];
			if(isset($data['IDs']) && is_array($data['IDs']) ){ $IDs = $data['IDs']; }
			else{ return ['result'=>1, 'msg'=>"Invalid knowledge records"]; }

			$user = new \App\User;
			$row = $user->where('id', $userID)
						->where('userPass', $user->hash($pass))
						->first();
			
			$haveAccess = false;
			if($row!=false){ if($row->levelID==1){ $haveAccess = true; } }
			
			foreach($IDs as $id){
				$relation = Relation::find($id);
				if(is_null($relation)){ return ['result'=>1, 'msg'=>"knowledge record({$id}) not found"]; }
				if($relation->relationIsReserved==1 && $haveAccess==false)
					{ return ['result'=>1, 'msg'=>" You do not have authorization to delete knowledge record({$id}) is <b>Reserved</b>"]; }
			}
			foreach($IDs as $id){ 
				$relation = Relation::find($id);
				$relation->delete($id); 
			}
			return ['result'=>0, 'msg'=>''];
		}catch(\ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getRelationLanguage($relationId,$orgID, $code, Request $req){
		try{
			if($orgID==0){ $orgID=null; }
			$data = \App\RelationLanguage::where('relationId', $relationId)
				->where('language_code', $code)
				->where('orgId',$orgID)
				->first();
			return ['result'=>0, 'data'=>$data];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function setRelationLanguage(Request $req){
		try{
			$data = $req->all();
			if($data['orgId']==0){ $data['orgId']=null; }
			$tmp = \App\RelationLanguage::where('relationId', $data['relationId'])
				->where('language_code', $data['language_code'])
				->where('orgId',$data['orgId'])
				->first();
			if($tmp!=null){
				$tmp = \App\RelationLanguage::where('relationLanguageId', $tmp->relationLanguageId)
					->update([
						'validationText' => $data['validationText'],
						'optionalText'   => $data['optionalText'  ],
						'shortText'      => $data['shortText'     ]
					]);
			}else{
				$tmp = \App\RelationLanguage::insert([
							'relationId'     => $data['relationId'    ],
							'language_code'  => $data['language_code' ],
							'orgId'          => $data['orgId'         ],
							'validationText' => $data['validationText'],
							'optionalText'   => $data['optionalText'  ],
							'shortText'      => $data['shortText'     ]
					]);
			}
			return ['result'=>0, 'msg'=>'OK'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}
