<?php

namespace App\Http\Controllers\Api\Chatbox;

use Illuminate\Http\Request;
use App\Chatbox;
use App\Controllers;

use App\User;
use App\ConsumerUser;
use App\ConsumerUserPersonality;
use App\Organization;

//use App\Http\Resources\Chatbox as ChatboxResource;

class ConsumerController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function consumerIdentify(Request $request){
		//-----------------------------------
		if(!$request->has('portalcode')){ return \Response::json([ 'message' => 'PortalCode [portalcode] not defined'], 400); }
		$portalcode = trim($request->input('portalcode'));
		if($portalcode==null){ return \Response::json([ 'message' => 'PortalCode is empty' ], 400); }
		$facebook = (substr($portalcode, 0, 1)=='4' ?true : false);
		//-----------------------------------
		$tmpPortal   = substr($portalcode, 0, 1);
		$otherPortal = (($tmpPortal!='1' && $tmpPortal!='2' && $tmpPortal!='3' && $tmpPortal!='4') ?true : false);
		//-----------------------------------
		if(!$request->has('orgid')){ return \Response::json([ 'message' => 'organization ID [orgid] not defined'], 400); }
		$orgID = trim($request->input('orgid'));
		if($orgID==null){ return \Response::json([ 'message' => 'organization ID is empty' ], 400); }
		//-----------------------------------
		if($facebook){ 
			if(!$request->has('fbid')){ return \Response::json([ 'message' => 'Facebook ID [fbid] not defined'], 400); }
			else{ $email = trim($request->input('fbid')); }
			if($email==null){ return \Response::json([ 'message' => 'Facebook ID is empty' ], 400); }
		}else{ 
			if($otherPortal){ 
				if(!$request->has('email')){ return \Response::json([ 'message' => 'ID [email] not defined'], 400); }
				else{ $email = trim($request->input('email')); }
				if($email==null){ return \Response::json([ 'message' => 'ID is empty' ], 400); }
			}else{
				if(!$request->has('email')){ return \Response::json([ 'message' => 'Email address [email] not defined'], 400); }
				else{ $email = trim($request->input('email')); }
				if($email==null){ return \Response::json([ 'message' => 'Email address is empty' ], 400); }
				if( !filter_var($email, FILTER_VALIDATE_EMAIL) ){ return ['result'=>1, 'msg'=>'Invalid email address']; }
 
			}
		}
		//-----------------------------------
		$tmpRetVal = $this->isValidOrgID( $orgID );
		if($tmpRetVal['result']==1){
			if($request->has('needRegister') &&  $request->input('needRegister') == 0){
				if($request->has('getName') &&  $request->input('getName') == 0) {
					$request->request->add(['name' => 'user_'.$email]);
					return $this->consumerRegister($request);
				}
			}
			
			return $tmpRetVal;  
		}
		//-----------------------------------

		//-----------------------------------
		$user = User::where('email', $email)->where('orgID', $orgID)->first();
		if($user==null){ 
			if($request->has('needRegister') &&  $request->input('needRegister') == 0){
				if($request->has('getName') &&  $request->input('getName') == 0) {
					$request->request->add(['name' => 'user_'.$email]);
					return $this->consumerRegister($request);
				}
			}
			
			return[ 'result'=>1, 'id'=>0 , 'name'=>'']; 
		}/* ERROR user not found */
		//-----------------------------------
		$consumerUser = ConsumerUser::where('consumerUserId', '=', $user->id)->first();
		if($consumerUser==null){ 
			//-------------------------------
//			return \Response::json([ 'message' => "This email cannot be used to register, please use another email." ], 400);
			$name = $user->userName;
			$consumerUserClass = new \App\Consumer\ConsumerUserClass;
			$userID = $consumerUserClass->createNew( $email, $orgID, $name, $portalcode );
			if($userID<=0){ return \Response::json([ 'message' => "cant't create user [code:$userID]" ], 400); }
			else{
				$ApiKeyManager = \App\ApiKeyManager\ApiKeyManagerClass::login($userID ,$portalcode);
				if($ApiKeyManager['result']==0){
					$kamaLog = \App\Logs\KamaLogClass::addUsage($ApiKeyManager['api_key'], '-', $userID, $orgID, $name);
					return[ 'result'=>0,'id'=>$userID, 'name'=>$name, 'apikey'=>$ApiKeyManager['api_key']];
				}
				else{ return \Response::json([ 'message' => $ApiKeyManager['msg'] ], 400); }
			}
			//-------------------------------
		}else{ 
			//-------------------------------
			$consumerUserPersonality = ConsumerUserPersonality::where('consumerUserId', $consumerUser->consumerUserId)
											->where('organizationId', $orgID)
											->first();
			if($consumerUserPersonality==null){ return[ 'result'=>1, 'id'=>0 , 'name'=>'']; }/* ERROR user not found */
			else{
//file_put_contents("/var/www/html/login.kama-dei.com/public/aa/myLog1.txt",date("Y-m-d H:i:s"));
				//---------------------------
				$consumerUserClass = new \App\Consumer\ConsumerUserClass;
				$consumerUserClass->setParent($user->email, $orgID, $portalcode);
				//---------------------------
				$name = trim($consumerUserPersonality->nickname);
				if($name==''){ $name = trim($consumerUser->email); }
				$ApiKeyManager = \App\ApiKeyManager\ApiKeyManagerClass::login($user->id ,$portalcode);
				if($ApiKeyManager['result']==0){
					if(!isset($ApiKeyManager['isOld'])){
						$kamaLog = \App\Logs\KamaLogClass::addUsage($ApiKeyManager['api_key'], '-', $user->id, $orgID, $name);
					}
					return[ 'result'=>0, 'id'=>$user->id, 'name'=>$name, 'apikey'=>$ApiKeyManager['api_key'] ];
				}else{ return \Response::json([ 'message' => $ApiKeyManager['msg'] ], 400); }

				//---------------------------
			}
			//-------------------------------
		}
		//-----------------------------------
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	public function consumerRegister(Request $request){
		//-----------------------------------
		if(!$request->has('portalcode')){ return \Response::json([ 'message' => 'PortalCode [portalcode] not defined'], 400); }
		$portalcode = trim($request->input('portalcode'));
		if($portalcode==null){ return \Response::json([ 'message' => 'PortalCode is empty' ], 400); }
		$facebook = (substr($portalcode, 0, 1)=='4' ?true : false);
		//-----------------------------------
		$tmpPortal   = substr($portalcode, 0, 1);
		$otherPortal = (($tmpPortal!='1' && $tmpPortal!='2' && $tmpPortal!='3' && $tmpPortal!='4') ?true : false);
		//-----------------------------------
		if(!$request->has('orgid')){ return \Response::json([ 'message' => 'organization ID [orgid] not defined'], 400); }
		$orgID = trim($request->input('orgid'));
		if($orgID==null){ return \Response::json([ 'message' => 'organization ID is empty' ], 400); }
		//-----------------------------------
		if($facebook){ 
			if(!$request->has('fbid')){ return \Response::json([ 'message' => 'Facebook ID [fbid] not defined'], 400); }
			else{ $email = trim($request->input('fbid')); }
			if($email==null){ return \Response::json([ 'message' => 'Facebook ID is empty' ], 400); }
		}else{ 
			if($otherPortal){
				if(!$request->has('email')){ return \Response::json([ 'message' => 'ID [email] not defined'], 400); }
				else{ $email = trim($request->input('email')); }
				if($email==null){ return \Response::json([ 'message' => 'ID is empty' ], 400); }
			}else{
				if(!$request->has('email')){ return \Response::json([ 'message' => 'Email address [email] not defined'], 400); }
				else{ $email = trim($request->input('email')); }
				if($email==null){ return \Response::json([ 'message' => 'Email address is empty' ], 400); }
				if( !filter_var($email, FILTER_VALIDATE_EMAIL) ){ return ['result'=>1, 'msg'=>'Invalid email address']; }
			}
		}
		//-----------------------------------
		$tmpRetVal = $this->isValidOrgID( $orgID );
		if($tmpRetVal['result']==1){ return $tmpRetVal; }
		//-----------------------------------
		if(!$request->has('name' )){ $name = 'consumerUser'; }
		else{ $name = trim($request->input('name' )); }
		//-----------------------------------

		//-----------------------------------
		$consumerUserClass = new \App\Consumer\ConsumerUserClass;
		$userID = $consumerUserClass->create( $email, $orgID, $name, $portalcode );
		if($userID<=0){ return \Response::json([ 'message' => "cant't create user [code:$userID]" ], 400); }
		else{
			$ApiKeyManager = \App\ApiKeyManager\ApiKeyManagerClass::login($userID ,$portalcode);
			if($ApiKeyManager['result']==0){
				$kamaLog = \App\Logs\KamaLogClass::addUsage($ApiKeyManager['api_key'], '-', $userID, $orgID, $name);
				return[ 'result'=>0,'id'=>$userID,'apikey'=>$ApiKeyManager['api_key']];
			}
			else{ return \Response::json([ 'message' => $ApiKeyManager['msg'] ], 400); }
		}
		//-----------------------------------
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
	private function isValidOrgID( $orgID ){
		try{
			if($orgID==0){ return ['result'=>0, 'msg'=>'OK']; }
			$tmp = Organization::find($orgID);
			if($tmp==null ){ return ['result'=>1, 'msg'=>'Invalid organization ID']; }
			return ['result'=>0, 'msg'=>'OK'];
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	/////////////////////////////////////////
	//---------------------------------------
}
