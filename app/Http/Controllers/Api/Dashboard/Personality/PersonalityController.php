<?php

namespace App\Http\Controllers\Api\Dashboard\Personality;

use Illuminate\Http\Request;
use App\Personality;
use App\Controllers;

use Illuminate\Support\Facades\Config;
use App\Consumer\ConsumerUserClass;
//use App\Http\Resources\Personality as PersonalityResource;

class PersonalityController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function find($id){
		$data = new Personality;
		$data = $data->getData($id);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[]];
		}else{
			return ['result'=>0, 'msg'=>'', 'data'=>$data];
		}
	}
	//---------------------------------------
	public function show($orgID, $id){
		$data = Personality::find($id);
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
		$data = Personality::where($field, 'like', "%{$value}%")->get();
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
		$count = Personality::count();
		$data  = Personality::orderBy($sort, $order)->get();
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	
	public function allPersonality($orgID, $ownerID, $sort, $order, $search="", $limit=1000){
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
		if($ownerID==-1){
			$personality = Personality::
							with(['parentPersona'])->
							with(['getConsumerUser'=>function($q){ return $q->leftJoin( 'kamadeiep.user as user', 'consumerUserId', 'user.id'); }])->
							where(function($q) use($orgID){
								if($orgID==0){ return $q; }
								else{ return $q->whereNull('ownerID')->where('ownership',0); }
							})->
							orWhere(function($q) use($orgID){
								if($orgID==0){ return $q; }
								else{ return $q->where('ownerID', $orgID); }
							});
		}else{
			$personality = Personality::
							with(['parentPersona'])->
							with(['getConsumerUser'=>function($q){ return $q->leftJoin( 'kamadeiep.user as user', 'consumerUserId', 'user.id'); }])->
							where(function($q) use($orgID, $ownerID){
								if($orgID==0){ 
									if($ownerID==0){ return $q->whereNull('ownerID'); }
									else{ return $q->where('ownerID', $ownerID); }
								}
								else{ 
									if($ownerID==0){ return $q->whereNull('ownerID')->where('ownership',0); }
									else{ return $q->where('ownerID', $ownerID); }
								}
							});
		}
		$personality = $personality->where(function($q) use($search){
			if($search==""){ return $q; }
			return $q
				->where('personalityName', 'like', "{$search}%")
				->orWhereHas("parentPersona", function($q) use($search){
					return $q->where("personalityName", 'like', "{$search}%");
				})
				->orWhereHas("getConsumerUser", function($q) use($search){
					return $q
						->leftJoin('kamadeiep.user as user', 'consumerUserId', 'user.id')
						->where("user.email", 'like', "{$search}%");
				});
		});
		$count = $personality->count();
		$data  = $personality->orderBy($sort, $order)->limit($limit)->get();

		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0, 'limit'=>$limit];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data, 'limit'=>$limit];
		}
	}

	public function nonzeroPersonality($orgID, $ownerID, $sort, $order, $search="", $limit=1000){
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
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		if($ownerID==-1){
			$personality = Personality::
							with(['parentPersona'])->
							with(['oldParentPersona'])->
							with(['getConsumerUser'=>function($q){
								return $q->leftJoin( 'kamadeiep.user as user', 'consumerUserId', 'user.id');
							}])->
							where('parentPersonaId', '>', 0)->
							where(function($q) use($orgID, $PUBLIC, $PRTCTD){
								if($orgID==0){ return $q->where('parentPersonaId', '>', 0); }
								else{ 
									$tmpOrgIDs = \App\OrgRelations::haveAccessTo($orgID);
									return $q
											->where('ownership', $PUBLIC)
											->orwhere('ownerId', $orgID)
											->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){ 
												return $q->whereIn('ownerId', $tmpOrgIDs)->where('ownership', $PRTCTD); 
											});
								}
							});
		}else{
			$personality = Personality::
							with(['parentPersona'])->
							with(['oldParentPersona'])->
							with(['getConsumerUser'=>function($q){
								return $q->leftJoin('kamadeiep.user as user', 'consumerUserId', 'user.id');
							}])->
							where(function($q) use($orgID, $ownerID){
								if($orgID==0){ 
									if($ownerID==0){ return $q->whereNull('ownerID')->where('parentPersonaId', '>', 0); }
									else{ return $q->where('ownerID', $ownerID)->where('parentPersonaId', '>', 0); }
								}
								else{ 
									if($ownerID==0){ return $q->whereNull('ownerID')->where('ownership',0)->where('parentPersonaId', '>', 0); }
									else{ return $q->where('ownerID', $ownerID)->where('parentPersonaId', '>', 0); }
								}
							});
		}
		$personality = $personality->where(function($q) use($search){
			if($search==""){ return $q; }
			return $q
				->where('personalityName', 'like', "{$search}%")
				->orWhereHas("parentPersona", function($q) use($search){
					return $q->where("personalityName", 'like', "{$search}%");
				})
				->orWhereHas("getConsumerUser", function($q) use($search){
					return $q
						->leftJoin('kamadeiep.user as user', 'consumerUserId', 'user.id')
						->where("user.email", 'like', "{$search}%");
				});
		});
		$count = $personality->count();
		$data  = $personality->orderBy($sort, $order)->limit($limit)
			->select(
				"*",
				\DB::raw("(select name from kamadeiep.portal where organization_id=ownerId and unknownPersonalityId=parentPersonaId limit 1) as portalname")
			)
			->get();

		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0, 'limit'=>$limit];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data, 'limit'=>$limit];
		}
	}

	public function zeroPersonality($orgID, $ownerID, $sort, $order, $search="", $limit=1000){
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
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		if($ownerID==-1){
			$personality = Personality::
							with(['getConsumerUser'=>function($q){ return $q->leftJoin( 'kamadeiep.user as user', 'consumerUserId', 'user.id'); }])->
								where('parentPersonaId', 0)->
								where(function($q) use($orgID, $PUBLIC, $PRTCTD){
								if($orgID==0){ return $q->where('parentPersonaId', '=', 0); }
								else{ 
									$tmpOrgIDs = \App\OrgRelations::haveAccessTo($orgID);
									return $q
											->where('ownership', $PUBLIC)
											->orwhere('ownerId', $orgID)
											->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){ 
												return $q->whereIn('ownerId', $tmpOrgIDs)->where('ownership', $PRTCTD); 
											});
								}
							});
		}else{
			$personality = Personality::
							with(['getConsumerUser'=>function($q){ return $q->leftJoin( 'kamadeiep.user as user', 'consumerUserId', 'user.id'); }])->
							where(function($q) use($orgID, $ownerID){
								if($orgID==0){ 
									if($ownerID==0){ return $q->whereNull('ownerID')->where('parentPersonaId', '=', 0); }
									else{ return $q->where('ownerID', $ownerID)->where('parentPersonaId', '=', 0); }
								}
								else{ 
									if($ownerID==0){ return $q->whereNull('ownerID')->where('ownership',0)->where('parentPersonaId', '=', 0); }
									else{ return $q->where('ownerID', $ownerID)->where('parentPersonaId', '=', 0); }
								}
							});
		}
		$personality = $personality->where(function($q) use($search){
			if($search==""){ return $q; }
			return $q
				->where('personalityName', 'like', "{$search}%")
				->orWhereHas("getConsumerUser", function($q) use($search){
					return $q
						->leftJoin('kamadeiep.user as user', 'consumerUserId', 'user.id')
						->where("user.email", 'like', "{$search}%");
				});
		});
		$count = $personality->count();
		$data  = $personality->orderBy($sort, $order)->limit($limit)->get();

		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0, 'limit'=>$limit];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data, 'limit'=>$limit];
		}
	}
	//---------------------------------------
	public function showPage($orgID, $perPage, $page){
		$count = Personality::myPersonality($orgID, '', '')->count();
		$data  = Personality::myPageing($orgID, $perPage, $page, 'termId', 'asc');
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}

	public function showPageSorted($orgID, $sort, $order, $perPage, $page, $ownerId=-1, $shwglblSTT=1){
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
		$count = Personality::myPersonalityNew($orgID, $sort, $order, '', '', $ownerId, $shwglblSTT)->count();
		$data  = Personality::myPageingNew($orgID, $perPage, $page, $sort, $order, '', '', $ownerId, $shwglblSTT);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	public function showPageSortSearch($orgID, $sort, $order, $perPage, $page, $field, $value, $ownerId=-1, $shwglblSTT=1){
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
		
		$count = Personality::myPersonalityNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT)->count();
		$data  = Personality::myPageingNew($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId, $shwglblSTT);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			$personalityName = strtolower(trim($request->input('personalityName')));
			$parentPersonaId = trim($request->input('parentPersonaId'));
			$parentPersonaId = (($parentPersonaId!='') ?$parentPersonaId : $tmp->parentPersonaId);
			$ownerId = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
			$errP = 'Personality';
			if( $parentPersonaId==0 ){ $errP = 'Persona'; }

			$tmp = Personality::find($id);
			if(is_null($tmp) ){ return ['result'=>1, 'msg'=>"{$errP} not found"]; }
			else{
				
				$personalityTmp = Personality::where('personalityName', $personalityName)->
												where('ownerId', $ownerId)->
												where('personalityId', '<>', $id)->first();
				if(!is_null($personalityTmp) ){ return ['result'=>1, 'msg'=>"{$errP} already exists"]; }

				$tmp->personalityName        = trim($request->input('personalityName'));
				$tmp->personalityDescription = trim($request->input('personalityDescription'));
				$tmp->parentPersonaId        = $parentPersonaId;
				
				$tmp->ownership              = trim($request->input('ownership'));
				$tmp->ownerId                = $ownerId;
				$tmp->lastUserId             = trim($request->input('userID'    ));
				
				if($tmp->personalityName        ==''){ return ['result'=>1, 'msg'=>"{$errP} name is empty"]; }
				if($tmp->personalityDescription ==''){ return ['result'=>1, 'msg'=>"{$errP} Description is empty"]; }
				if($tmp->ownership              ==''){ return ['result'=>1, 'msg'=>"{$errP} owner ship is empty"]; }
				if($tmp->lastUserId             ==''){ return ['result'=>1, 'msg'=>"{$errP} last user id is empty"]; }

				
				$tmp->ownerId = (($tmp->ownerId==0) ?null :$tmp->ownerId);
				$tmp = $tmp->save();
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			$personalityName = $request->input('personalityName');
			$parentPersonaId = trim($request->input('parentPersonaId'));
			$parentPersonaId = (($parentPersonaId!='') ?$parentPersonaId : 0);
			$ownerId = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
			$errP = 'Personality';
			if( $parentPersonaId==0 ){ $errP = 'Persona'; }

			$personalityTmp = Personality::where('personalityName', strtolower($personalityName))->where('ownerId', $ownerId)->first();
			if(!is_null($personalityTmp) ){ return ['result'=>1, 'msg'=>"{$errP} already exists"]; }
			
			$personalityTmp = new Personality;

			$personalityTmp->personalityName        = trim($request->input('personalityName'));
			$personalityTmp->personalityDescription = trim($request->input('personalityDescription'));
			$personalityTmp->parentPersonaId        = $parentPersonaId;
			
			$personalityTmp->ownership              = trim($request->input('ownership'));
			$personalityTmp->ownerId                = $ownerId;
			$personalityTmp->lastUserId             = trim($request->input('userID'    ));
			$personalityTmp->dateCreated            = date("Y-m-d H:i:s");//$request->input('dateCreated'   );
			
			if($personalityTmp->personalityName        ==''){ return ['result'=>1, 'msg'=>"{$errP} name is empty"]; }
			if($personalityTmp->personalityDescription ==''){ return ['result'=>1, 'msg'=>"{$errP} Description is empty"]; }
			if($personalityTmp->ownership              ==''){ return ['result'=>1, 'msg'=>"{$errP} owner ship is empty"]; }
			if($personalityTmp->lastUserId             ==''){ return ['result'=>1, 'msg'=>"{$errP} last user id is empty"]; }
			
			$personalityTmp->ownerId = (($personalityTmp->ownerId==0) ?null :$personalityTmp->ownerId);
			$tmp = $personalityTmp->save();
			if($tmp){ return ['result'=>0, 'personalityId'=>$personalityTmp->personalityId]; }
			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			if($id==Config::get('kama_dei.static.No_Persona',0)){ return ['result'=>1, 'msg'=>"You can't delete this Persona/Personality"]; }

			if( \App\LexSetting::where('personalityId', '=', $id)->count()!=0 )
				{ return ['result'=>1, 'msg'=>"This item is used in LEX SETTING, it can not be deleted."]; }
			if( \App\LexSetting::where('lexPersonalityID', '=', $id)->count()!=0 )
				{ return ['result'=>1, 'msg'=>"This item is used in LEX SETTING, it can not be deleted."]; }
			if( \App\LexMapBots::where('personaiD', '=', $id)->count()!=0 )
				{ return ['result'=>1, 'msg'=>"This item is used in LEX MAP, it can not be deleted."]; }
			if( \App\LexMapBots::where('lexPersonalityID', '=', $id)->count()!=0 )
				{ return ['result'=>1, 'msg'=>"This item is used in LEX MAP, it can not be deleted."]; }
			
			$temp = Personality::find($id);
			if(is_null($temp) ){
				return ['result'=>1, 'msg'=>"Personality not found"];
			}else{
				//---------------------------
				if($temp->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this Persona/Personality"]; }
				//---------------------------
				if( Personality::where('parentPersonaId', '=', $id)->count()!=0 )
					{ return ['result'=>1, 'msg'=>"This personality is used in at least one parent persona, it can not be deleted."]; }
				//---------------------------
				$consumerUserClass = new ConsumerUserClass;
				$consumerUserClass->deletePersonalityRecords( $id );
				if($temp->parentPersonaId==0){ $temp = $temp->delete($id); }
				//---------------------------
				return ['result'=>($temp ?0 :1), 'msg'=>''];
				//---------------------------
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------}
	public function showAllParents($orgID){
		//---------------------------------------
		if($orgID==0){ 
			$data  = Personality::
						where('parentPersonaId', '=', 0)->
						where('personalityId', '<>', Config::get('kama_dei.static.No_Persona',0))->
						orderBy('personalityName')->get(); 
		}else{ 
			$data  = Personality::
						where('ownerId', '=', $orgID)->
						where('parentPersonaId', '=', 0)->
						where('personalityId', '<>', Config::get('kama_dei.static.No_Persona',0))->
						orderBy('personalityName')->get(); 
		}
		if(is_null($data) )
			{ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ 
			$No_Persona = Personality::where('personalityId', '=', Config::get('kama_dei.static.No_Persona',0))->first();
			if($No_Persona!=null){ $data->prepend($No_Persona); } 
			return ['result'=>0, 'msg'=>'', 'total'=>$data->count(), 'data'=>$data]; 
		}
	}
	//---------------------------------------
	private function validFieldName($fieldName){
		switch(strtolower($fieldName)){
			case 'personalityid'          : { return "personalityId";          }

			case 'parentpersonaid'        : { return "parentPersonaId";        }
			case 'personalityname'        : { return "personalityName";        }
			case 'personalitydescription' : { return "personalityDescription"; }
			case 'ownership'              : { return "ownership";              }
			case 'ownerid'                : { return "ownerId";                }
			case 'datecreated'            : { return "dateCreated";            }
			case 'lastuserid'             : { return "lastUserId";             }
			case 'organizationshortname'  : { return "organizationShortName";  }
			case 'ownershipcaption'       : { return "ownership";              }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	private function validSortName($fieldName){
		switch(strtolower($fieldName)){
			case 'personalityid'          : { return "personalityId";          }
			case 'parentpersonaid'        : { return "parentPersonaId";        }
			case 'parentpersonaname'      : { return "parentPersonaId";        }
			case 'personalityname'        : { return "personalityName";        }
			case 'personalitydescription' : { return "personalityDescription"; }
			case 'ownerid'                : { return "ownerId";                }
			case 'datecreated'            : { return "dateCreated";            }
			case 'lastuserid'             : { return "lastUserId";             }
			case 'ownershipcaption'       : { return "ownership";              }

			case 'ownership'              : { return "ownerShipText";         }
			case 'organizationshortname'  : { return "organizationShortName"; }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	public function clonePersonality(Request $request){
		try{
			$sourcePersonalityId = $request->input('sourcePersonalityId');
			$personalityName     = $request->input('personalityName'    );
			$orgId               = $request->input('orgId'              );
			$userId              = $request->input('userId'             );
			$ownership           = $request->input('ownership'          );
			$ownerId             = $request->input('ownerId'            );
			$parentPersonaId     = $request->input('parentPersonaId'    );
			$extraData           = ['ownership'=>$ownership, 'ownerId'=>$ownerId];

			$tmp = Personality::where('personalityName', $personalityName)->first();
			if($tmp!=null){ return ['result'=>1, 'msg'=>"Name already exists"]; }
			
			$consumerUserClass = new ConsumerUserClass;
			if($parentPersonaId==0){ return $consumerUserClass->clonePersona( $sourcePersonalityId, $personalityName, $orgId, $userId ,$extraData); }
			else{ return $consumerUserClass->clonePersonality( $sourcePersonalityId, $personalityName, $orgId, $userId ,$extraData, $parentPersonaId); }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function clonePersona(Request $request){
		try{
			$sourcePersonalityId = $request->input('sourcePersonalityId');
			$personalityName     = $request->input('personalityName'    );
			$orgId               = $request->input('orgId'              );
			$userId              = $request->input('userId'             );
			$ownership           = $request->input('ownership'          );
			$ownerId             = $request->input('ownerId'            );
			$parentPersonaId     = $request->input('parentPersonaId'    );
			$extraData           = ['ownership'=>$ownership, 'ownerId'=>$ownerId];

			$tmp = Personality::where('personalityName', $personalityName)->first();
			if($tmp!=null){ return ['result'=>1, 'msg'=>"Name already exists"]; }

			$consumerUserClass = new ConsumerUserClass;
			if($parentPersonaId==0){ return $consumerUserClass->clonePersona( $sourcePersonalityId, $personalityName, $orgId, $userId ,$extraData); }
			else{ return $consumerUserClass->clonePersonality( $sourcePersonalityId, $personalityName, $orgId, $userId ,$extraData, $parentPersonaId); }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getUserDate($uID){
		try{
			$usr = \App\User::find($uID);
			return ['result'=>0, 'name'=>$usr->userName, 'email'=>$usr->email, ];
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getPersonalityOwnersList($orgID){
		$data  = Personality::getOwnersList($orgID, 1);
		if($data!=null){
			$tmp = array_map('strtolower',  array_column($data, 'text'));
			array_multisort($tmp, SORT_NATURAL, $data);
		}
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
	public function getPersonaOwnersList($orgID){
		$data  = Personality::getOwnersList($orgID, 0);
		if($data!=null){
			$tmp = array_map('strtolower',  array_column($data, 'text'));
			array_multisort($tmp, SORT_NATURAL, $data);
		}
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
	public function getPersonalitiesOfPersona($orgID, $personaID){
		$data  = Personality::where('parentPersonaId', $personaID)
								->where(function($q) use($orgID){
									if($orgID==0){ return $q; }
									return $q->where('ownerId', $orgID);
								})
								->select('personalityId as id', 'personalityName as name')
								->orderBy('personalityName');
		return ['result'=>0, 'msg'=>'', 'total'=>$data->count(), 'data'=>$data->get()];
	}
	//---------------------------------------
}