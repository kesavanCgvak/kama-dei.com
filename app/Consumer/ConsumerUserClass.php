<?php

namespace App\Consumer;

use App\User;
use App\Personality;
use App\ConsumerUser;
use App\PersonalityValue;
use App\PersonalityTrait;
use App\PersonalityRelation;
use App\OrganizationPersonality;
use App\ConsumerUserPersonality;
use App\PersonalityRelationValue;

class ConsumerUserClass{
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function setParent($email, $orgID, $portalcode){
		$userID = $this->isUser( $email, $orgID );
		//-------------------------------
		$defaultPersonalityId = $this->getDeaultPersonality( $orgID, $portalcode );
		if($defaultPersonalityId==0){ return; }

		$consumer_user_personality = \App\ConsumerUserPersonality::where('consumerUserId', $userID)->first();
		if($consumer_user_personality==null){ return; }

		$personality = \App\Personality::where('personalityId', $consumer_user_personality->personalityId)->first();
		
		if($personality->parentPersonaId!=$defaultPersonalityId){
			\App\ConsumerUserPersonalityLog::insert([
				'consUserPersonalityId' => $consumer_user_personality->consUserPersonalityId,
				'personalityId'         => $consumer_user_personality->personalityId,
				'parentPersonaId_old'   => $personality->parentPersonaId,
				'parentPersonaId_new'   => $defaultPersonalityId,
				'dateCreated'           => date("Y-m-d H:i:s")
			]);
		}
		
		\App\Personality::where('personalityId', $consumer_user_personality->personalityId)->
			update(['parentPersonaId'=>$defaultPersonalityId]);
		//-------------------------------
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function createNew( $email, $orgID, $name, $portalcode=null ){
		$userID = $this->isUser( $email, $orgID );
		//-----------------------------------
		$defaultPersonalityId = $this->getDeaultPersonality( $orgID, $portalcode );
		if($defaultPersonalityId==0){ return 0; }
		//-------------------------------
		$consumerUserId = $this->createConsumerUser( 0, $userID );
		if($consumerUserId==0){ 
			//User::where('id', $userID)->delete();
			return -2; 
		}
		//-------------------------------
		$personalityId = $this->createPersonality( $name, $defaultPersonalityId, $orgID, $userID, "create with {$portalcode}" );
		if($personalityId==0){ 
			//User::where('id', $userID)->delete();
			ConsumerUser::where('consumerUserId', $consumerUserId)->delete();
			return -3; 
		}
		//-------------------------------
		$consUserPersonalityId = $this->createConsumerUserPersonality( $name, $consumerUserId, $personalityId, $orgID, $userID );
		if($consUserPersonalityId==0){ 
			//User::where('id', $userID)->delete();
			ConsumerUser::where('consumerUserId', $consumerUserId)->delete();
			Personality::where('personalityId', $personalityId)->delete();
			return -4; 
		}
		//-------------------------------
		else{ return $userID; }
		//-----------------------------------
	}
	//---------------------------------------
	public function create( $email, $orgID, $name, $portalcode=null ){
		$userID = $this->isUser( $email, $orgID );
		//-----------------------------------
		if($userID==0){
			//-------------------------------
			$defaultPersonalityId = $this->getDeaultPersonality( $orgID, $portalcode );
			if($defaultPersonalityId==0){ return 0; }
			//-------------------------------
			$userID = $this->createUser( $email, $orgID );
			if($userID==0){ return -1; }
			//-------------------------------
			$consumerUserId = $this->createConsumerUser( 0, $userID );
			if($consumerUserId==0){ 
				User::where('id', $userID)->delete();
				return -2; 
			}
			//-------------------------------
			$personalityId = $this->createPersonality( $name, $defaultPersonalityId, $orgID, $userID, "create with {$portalcode}" );
			if($personalityId==0){ 
				User::where('id', $userID)->delete();
				ConsumerUser::where('consumerUserId', $consumerUserId)->delete();
				return -3; 
			}
			//-------------------------------
			$consUserPersonalityId = $this->createConsumerUserPersonality( $name, $consumerUserId, $personalityId, $orgID, $userID );
			if($consUserPersonalityId==0){ 
				User::where('id', $userID)->delete();
				ConsumerUser::where('consumerUserId', $consumerUserId)->delete();
				Personality::where('personalityId', $personalityId)->delete();
				return -4; 
			}
			//-------------------------------
//			$this->clonePersonalityValue   ($defaultPersonalityId, $personalityId, $orgID, $userID);
//			$this->clonePersonalityTrait   ($defaultPersonalityId, $personalityId, $orgID, $userID);
//			$this->clonePersonalityRelation($defaultPersonalityId, $personalityId, $orgID, $userID);
			return $userID;
		}
		//-----------------------------------
		else{ return $userID; }
		//-----------------------------------
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function isUser( $email, $orgID ){
		$consumerUser = User::where('email', $email)->where('orgID', $orgID)->first();
		if($consumerUser==null){ return 0; }
		else{ return $consumerUser->id; }
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function getDeaultPersonality( $orgID, $portalcode=null ){
		if($portalcode!=null){
			$code = substr($portalcode, 1);
			$pNum = substr($portalcode, 0, 1);
			$portal = \App\Portal::where('code', $code)
						//->where('portal_number', $pNum)
						->where('organization_id', $orgID)
						->whereNotNull('unknownPersonalityId')
						->first();
			if($portal!=null){ return $portal->unknownPersonalityId; }
		}
		$defaultPersonality = OrganizationPersonality::where('organizationId', $orgID)->where('is_default', 1)->first();
		if($defaultPersonality==null){ return 0; }
		else{ return $defaultPersonality->personalityId; }
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function createUser( $email, $orgID, $userName="consumerUser" ){
		try{
			$tmp = new User;
			$tmp->userName = $userName;
			$tmp->userPass = "";
			$tmp->email    = $email;
			$tmp->isAdmin  = 0;
			$tmp->orgID    = $orgID;
			$tmp->levelID  = 4;
			$tmp->createAt = date("Y-m-d H:i:s");
			if($tmp->save()){ return $tmp->id; }
			else{ return 0; }
		}catch(ErrorException $ex){ return 0; }
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function createConsumerUser( $personID, $consumerUserId, $lastUserId=0 ){
		try{
			if($lastUserId==0){ $lastUserId=$consumerUserId; }
			$tmp = new ConsumerUser;
			$tmp->consumerUserId = $consumerUserId;
			$tmp->personId       = $personID;
			$tmp->dateCreated    = date("Y-m-d H:i:s");
			$tmp->lastUserId     = $lastUserId;
			if($tmp->save()){ return $consumerUserId; }
			else{ return 0; }
		}catch(ErrorException $ex){ return 0; }
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function createPersonality( $name, $defaultPersonalityId, $orgID, $userID, $personalityDescription="-" ){
		try{
			$tmp = new Personality;
			$tmp->parentPersonaId        = $defaultPersonalityId;
			$tmp->personalityName        = $name;
			$tmp->personalityDescription = $personalityDescription;
			$tmp->ownership              = 0;
			$tmp->ownerId                = $orgID;
			$tmp->dateCreated            = date("Y-m-d H:i:s");
			$tmp->lastUserId             = $userID;
			if($tmp->save()){ return $tmp->personalityId; }
			else{ return 0; }
		}catch(ErrorException $ex){ return 0; }
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function createConsumerUserPersonality( $name, $consumerUserId, $personalityId, $orgID, $userID ){
		try{
			$nicknamelength = \Config::get('kama_dei.static.nicknamelength',100);
			$tmp = new ConsumerUserPersonality;
			$tmp->consumerUserId = $consumerUserId;
			$tmp->nickname       = substr($name, 0, $nicknamelength);
			$tmp->personalityId  = $personalityId;
			$tmp->organizationId = $orgID;
			$tmp->dateCreated    = date("Y-m-d H:i:s");
			$tmp->lastUserId     = $userID;
			if($tmp->save()){ return $tmp->consUserPersonalityId; }
			else{ return 0; }
		}catch(ErrorException $ex){ return 0; }
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function clonePersonalityValue($sourcePersonalityId, $targetPersonalityId, $orgId, $userID, $extraData=null){
		$rs =  PersonalityValue::
				where( function($query) use($sourcePersonalityId) {
					$query->where( 'personalityId', '=', $sourcePersonalityId );
//					->where( 'ownership', '=', 0 );
				})
				/*
				->orWhere( function($query) use($sourcePersonalityId, $orgId)  {
					$query;
					->where( 'personalityId', '=', $sourcePersonalityId )
					->where( 'ownership', '>', 0 )
					->where('ownerId', '=', $orgId);
				})
				*/
				->get(); 

		if( !empty($rs) ){ 
			foreach ($rs as $rs0){	
				$personTermId = $rs0->personTermId;
				$scalarValue  = $rs0->scalarValue;
//				$ownership    = (($extraData!=null) ?$extraData['ownership'] :$rs0->ownership);
				$ownership    = $rs0->ownership;
				$ownerId      = (($extraData!=null) ?$extraData['ownerId'  ] :$rs0->ownerId  );
				$lastUserId   = $userID;

				$oPV = new PersonalityValue;
				$oPV->personalityId   = $targetPersonalityId;
				$oPV->personTermId    = $personTermId;
				$oPV->scalarValue     = $scalarValue;
				$oPV->ownership       = $ownership;
				$oPV->ownerId         = $ownerId;
				$oPV->dateCreated     = date("Y-m-d H:i:s");
				$oPV->lastUserId      = $lastUserId;
				$oPV->save();
			}			
		}
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function clonePersonalityTrait($sourcePersonalityId, $targetPersonalityId, $sourceOrgId, $userID, $extraData=null){
		// get source records
		$rs =  PersonalityTrait::
				where( function($query) use($sourcePersonalityId) {
					$query->where( 'personalityId', '=', $sourcePersonalityId );
//					->where( 'ownership', '=', 0 );
				})
				/*
				->orWhere( function($query) use($sourcePersonalityId, $sourceOrgId)  {
					$query
					->where( 'personalityId', '=', $sourcePersonalityId )
					->where( 'ownership', '>', 0 )
					->where('ownerId', '=', $sourceOrgId);
				})
				*/
				->get(); 
		
		if (!empty($rs)) { 
			foreach($rs as $rs0){
				$personTraitDefn = $rs0->personalityTraitDefn;
				$termTraitId     = $rs0->termTraitId;
				$scalarValue     = $rs0->scalarValue;
//				$ownership       = (($extraData!=null) ?$extraData['ownership'] :$rs0->ownership);
				$ownership       = $rs0->ownership;
				$ownerId         = (($extraData!=null) ?$extraData['ownerId'  ] :$rs0->ownerId  );
				$lastUserId      = $userID;
		
				$oPT = new PersonalityTrait;
				$oPT->personalityTraitDefn = $personTraitDefn;
				$oPT->personalityId        = $targetPersonalityId;
				$oPT->termTraitId          = $termTraitId;
				$oPT->scalarValue          = $scalarValue;
				$oPT->ownership            = $ownership;
				$oPT->ownerId              = $ownerId;
				$oPT->dateCreated          = date("Y-m-d H:i:s");
				$oPT->lastUserId           = $lastUserId;
				$oPT->save();
			}			
		}
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function clonePersonalityRelation($defaultPersonalityId, $personalityId, $orgID, $userID, $extraData=null){
		$rs =  PersonalityRelation::
				where( function($query) use($defaultPersonalityId) {
					$query->where( 'personalityId', '=', $defaultPersonalityId );
//					->where( 'ownership', '=', 0 );
				})
				/*
				->orWhere( function($query) use($defaultPersonalityId, $orgID)  {
					$query
					->where( 'personalityId', '=', $defaultPersonalityId )
					->where( 'ownership', '>', 0 )
					->where('ownerId', '=', $orgID);
				})
				*/
				->get(); 
		
		if (!empty($rs)) { 
			foreach($rs as $rs0){
				$tmp = new PersonalityRelation;
				$tmp->personalityId = $personalityId;
				$tmp->relationId    = $rs0->relationId;
//				$tmp->ownership     = (($extraData!=null) ?$extraData['ownership'] :$rs0->ownership);
				$tmp->ownership     = $rs0->ownership;
				$tmp->ownerId       = (($extraData!=null) ?$extraData['ownerId'  ] :$orgID  );
				$tmp->lastUserId    = $userID;
				$tmp->dateCreated   = date("Y-m-d H:i:s");
				if( $tmp->save() ){
					$tmpRS = PersonalityRelationValue::where('personalityRelationId', $rs0->personalityRelationId)->get();
					if (!empty($tmpRS)) { 
						foreach($tmpRS as $rs1){
							$tmp1 = new PersonalityRelationValue;
							$tmp1->personalityRelationId = $tmp->personalityRelationId;
							$tmp1->personRelationTermId  = $rs1->personRelationTermId;
							$tmp1->scalarValue           = $rs1->scalarValue;
							$tmp1->ownership             = $rs1->ownership;
//							$tmp1->ownership             = (($extraData!=null) ?$extraData['ownership'] :$rs1->ownership);
							$tmp1->ownerId               = (($extraData!=null) ?$extraData['ownerId'  ] :$rs1->ownerId  );
							$tmp1->lastUserId            = $rs1->lastUserId;
							$tmp1->dateCreated           = date("Y-m-d H:i:s");
							$tmp1->save();
						}
					}
				}
			}
		}
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function clonePersonality($sourcePersonalityId, $personalityName, $orgId, $userId, $extraData=null, $parentPersonaId=null){
		$rs =  Personality::where('personalityId', $sourcePersonalityId)->first();
		if( !empty($rs) ){ 
			$tmp = new Personality;
			if($parentPersonaId!=null){ $tmp->parentPersonaId = $parentPersonaId; }
			else{ $tmp->parentPersonaId  = (($rs->parentPersonaId==0) ?$rs->personalityId :$rs->parentPersonaId); }
			$tmp->personalityName        = $personalityName;
			$tmp->personalityDescription = $rs->personalityDescription;
//			$tmp->ownership              = (($extraData!=null) ?$extraData['ownership'] :$rs->ownership);
			$tmp->ownership              = $rs->ownership;
			$tmp->ownerId                = (($extraData!=null) ?$extraData['ownerId'  ] :$rs->ownerId  );
			$tmp->dateCreated            = date("Y-m-d H:i:s");
			$tmp->lastUserId             = $userId;
			if($tmp->save()){
				$this->clonePersonalityValue   ($sourcePersonalityId, $tmp->personalityId, $orgId, $userId, $extraData);
				$this->clonePersonalityTrait   ($sourcePersonalityId, $tmp->personalityId, $orgId, $userId, $extraData);
				$this->clonePersonalityRelation($sourcePersonalityId, $tmp->personalityId, $orgId, $userId, $extraData);
			}
			return ['result'=>0, 'msg'=>'Personality cloned'];
		}else{
			return ['result'=>1, 'msg'=>'Personality not found'];
		}
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function clonePersona($sourcePersonalityId, $personalityName, $orgId, $userId, $extraData=null){
		$rs =  Personality::where('personalityId', $sourcePersonalityId)->first();
		if( !empty($rs) ){ 
			$tmp = new Personality;
			$tmp->parentPersonaId        = 0;//(($rs->parentPersonaId==0) ?$rs->personalityId :$rs->parentPersonaId);
			$tmp->personalityName        = $personalityName;
			$tmp->personalityDescription = $rs->personalityDescription;
			$tmp->ownership              = $rs->ownership;
//			$tmp->ownership              = (($extraData!=null) ?$extraData['ownership'] :$rs->ownership);
			$tmp->ownerId                = (($extraData!=null) ?$extraData['ownerId'  ] :$rs->ownerId  );
			$tmp->dateCreated            = date("Y-m-d H:i:s");
			$tmp->lastUserId             = $userId;
			if($tmp->save()){
				$this->clonePersonalityValue   ($sourcePersonalityId, $tmp->personalityId, $orgId, $userId, $extraData);
				$this->clonePersonalityTrait   ($sourcePersonalityId, $tmp->personalityId, $orgId, $userId, $extraData);
				$this->clonePersonalityRelation($sourcePersonalityId, $tmp->personalityId, $orgId, $userId, $extraData);
			}
			return ['result'=>0, 'msg'=>'Persona cloned'];
		}else{
			return ['result'=>1, 'msg'=>'Persona not found'];
		}
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function deletePersonalityRecords($personalityId){
		/* Delete records from personality_value */
		PersonalityValue::where('personalityId', $personalityId)->delete();

		/* Delete records from personality_trait */
		PersonalityTrait::where('personalityId', $personalityId)->delete();

		/* Get records from personality_relation */
		$rs = PersonalityRelation::where('personalityId', $personalityId)->get();
		if (!empty($rs)){
			foreach ($rs as $rs0){
				// delete personality_relation_value records
				PersonalityRelationValue::where('personalityRelationId', $rs0->personalityRelationId)->delete();
			}
			/* Delete records from personality_relation */
			PersonalityRelation::where('personalityId', $personalityId)->delete();
		}
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function create4lex( $personaId, $orgID, $userID ){
		try{
			$email = 'lexConsumerEmail';
			$newUserID = $this->isUser( $email, $orgID );
			//-----------------------------------
			if($newUserID==0){
				//-------------------------------
				$newUserID = $this->createUser( $email, $orgID, "LexbotUser" );
				if($userID==0){ 
					return ['result'=>1, 'msg'=>'Error on createUser[-1]'];
				}
				//-------------------------------
				$consumerUserId = $this->createConsumerUser( 0, $newUserID, $userID );
				if($consumerUserId==0){ 
					User::where('id', $newUserID)->delete();
					return ['result'=>1, 'msg'=>'Error on createConsumerUser[-2]'];
				}
				//-------------------------------
				$personaName = \App\Personality::find($personaId)->personalityName;
				//-------------------------------
				$personalityId = $this->createPersonality( "Lexbot {$personaName}", $personaId, $orgID, $userID );
				if($personalityId==0){ 
					User::where('id', $newUserID)->delete();
					ConsumerUser::where('consumerUserId', $consumerUserId)->delete();
					return ['result'=>1, 'msg'=>'Error on createPersonality[-3]'];
				}
				//-------------------------------
				$consUserPersonalityId = $this->createConsumerUserPersonality( "LexbotUser", $consumerUserId, $personalityId, $orgID, $userID );
				if($consUserPersonalityId==0){ 
					User::where('id', $newUserID)->delete();
					ConsumerUser::where('consumerUserId', $consumerUserId)->delete();
					Personality::where('personalityId', $personalityId)->delete();
					return ['result'=>1, 'msg'=>'Error on createConsumerUserPersonality[-4]'];
				}
				//-------------------------------
//				$this->clonePersonalityValue   ($personaId, $personalityId, $orgID, $userID);
//				$this->clonePersonalityTrait   ($personaId, $personalityId, $orgID, $userID);
//				$this->clonePersonalityRelation($personaId, $personalityId, $orgID, $userID);
				return ['result'=>0, 'userID'=>$newUserID, 'personalityId'=>$personalityId];
			}
			//-----------------------------------
			else{ 
				$cup = \App\ConsumerUserPersonality::where('consumerUserId', $newUserID)
													->where('nickname', 'LexbotUser')
													->where('organizationId', $orgID)
													->first();
				if($cup==null){ return ['result'=>1, 'msg'=>'Error on findConsumerUserPersonality[-5]']; }
				return ['result'=>0, 'userID'=>$newUserID, 'personalityId'=>$cup->personalityId];
			}
			//-----------------------------------
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
}
