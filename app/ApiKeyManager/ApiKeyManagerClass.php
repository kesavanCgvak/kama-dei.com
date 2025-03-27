<?php
namespace App\ApiKeyManager;

class ApiKeyManagerClass{
	//-----------------------------------------
	public static function login($user_id=0, $portal_code=''){
		try{
			$user = \App\ConsumerUserPersonality::where('consumerUserId', $user_id)->first();
			if($user==null){ throw new \ErrorException('invalid user.'); }

			$portal_code = trim($portal_code);
			if($portal_code==''){ throw new \ErrorException('invalid portal.'); }
			$portal_code = substr($portal_code, 0, 6);
			for($i=strlen($portal_code); $i<6; $i++){ $portal_code .= '_'; }
			
			$api_key = $portal_code.md5($portal_code.time().rand(1000000, 9999999)."KAMADEI");
			//$api_key_expire = strtotime( "+10 minutes" );
			$apikey_expire_in_min = env("apikey_expire_in_min", 10);
			$api_key_expire = strtotime( "+{$apikey_expire_in_min} minutes" );
			
			$apiKeyManager = \App\ApiKeyManager::where("userID", $user_id)
													->where('portal_code', $portal_code)->first();
			if($apiKeyManager==null){
				//-----------------------------
				$tmp = new \App\ApiKeyManager;
				$tmp->userID         = $user_id;
				$tmp->orgID          = $user->organizationId;
				$tmp->portal_code    = $portal_code;
				$tmp->api_key        = $api_key;
				$tmp->registerOn     = date("Y-m-d H:i:s");
				//-----------------------------
				$from = substr($portal_code ,0 ,1);
				if( $from==2 || $from==3 || 
				   	($from==1 && $user->organizationId==20) ||
//				   	(($from=='a' || $from=='t' ) && $user->organizationId==15) 
				   	($from=='a' || $from=='t') 
				)
//				if( $from==2 || $from==3 )
					{ $tmp->api_key_valid_for_ever = 1; }
					else{ $tmp->api_key_expire = $api_key_expire; }
				//-----------------------------
				if($tmp->save()){ return ['result'=>0, 'user_id'=>$user_id, 'api_key'=>$api_key]; }
				else{ throw new \ErrorException('can\'t create api key.'); }
				//-----------------------------
			}else{
				if($apiKeyManager->api_key_valid_for_ever==1){
					return ['result'=>0, 'user_id'=>$user_id, 'api_key'=>$apiKeyManager->api_key];
				}else{
					$from = substr($api_key ,0 ,1);
					if( $from=='z' ){
						$api_key_expire_now = time();
						if($api_key_expire_now<$apiKeyManager->api_key_expire){
							$apiKeyManagerTMP = \App\ApiKeyManager::where("userID", $user_id)
															->where('portal_code', $portal_code)
															->update(['api_key_expire'=>$api_key_expire]);
							//if($apiKeyManagerTMP==false){ throw new \ErrorException("can\'t renew api key."); }
							return ['result'=>0, 'user_id'=>$user_id, 'api_key'=>$apiKeyManager->api_key, 'isOld'=>'1']; 
						}
					}
					$apiKeyManager = \App\ApiKeyManager::where("userID", $user_id)
													->where('portal_code', $portal_code)
													->update(['api_key'=>$api_key, 'api_key_expire'=>$api_key_expire]);
					if($apiKeyManager==false){ throw new \ErrorException("can\'t create api key."); }
					return ['result'=>0, 'user_id'=>$user_id, 'api_key'=>$api_key];
				}
			}
		}catch(\ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//-----------------------------------------
	public function authenticate($user_id=0, $api_key=''){
		try{
			$user = \App\ConsumerUserPersonality::where('consumerUserId', $user_id)->count();
			if($user==0){ return ['result'=>2, 'msg'=>'invalid user.']; }

			$apiKeyManager = \App\ApiKeyManager::where('userID', $user_id)->count();
			if($user==0){ return ['result'=>3, 'msg'=>'user has not logged in.']; }
			
			$apiKeyManager = \App\ApiKeyManager::where("userID", $user_id)
													->where('api_key', $api_key)->first();
			if($apiKeyManager==null){ return ['result'=>4, 'msg'=>'invalid api key.']; }
			
			if($apiKeyManager->api_key_valid_for_ever==1){
				return ['result'=>0, 'msg'=>'OK'];
			}else{
				$api_key_expire = time();
				
				$specialOrgIds = explode(",", env('specialOrgIds', "0"));
				if(!in_array($apiKeyManager->orgID, $specialOrgIds)){
					if($api_key_expire>$apiKeyManager->api_key_expire){ return ['result'=>5, 'msg'=>'api key expired.']; }
				}

				//$api_key_expire = strtotime( "+10 minutes" );
				$apikey_expire_in_min = env("apikey_expire_in_min", 10);
				$api_key_expire = strtotime( "+{$apikey_expire_in_min} minutes" );
				$from = substr($api_key ,0 ,1);
				if( $from==4 || $from==3 || $from=='z' ){ $api_key_expire++; }
				else{
					if($api_key_expire==$apiKeyManager->api_key_expire){
//$t1 = microtime(true);
//$t2 = time();
//\Log::info("ApiKeyManager: user:{$user_id} - apiKey:{$api_key} call time:".date('Y-m-d H:i:s', $t2).".".round(($t1-$t2)*1000,0));
//						return ['result'=>6, 'msg'=>'too many request per second.'];
					}
				}
				if( $apiKeyManager->api_key_expire != $api_key_expire ){
					$apiKeyManager = \App\ApiKeyManager::where("userID", $user_id)
													->where('api_key', $api_key)
													->update(['api_key_expire'=>$api_key_expire]);
					if($apiKeyManager==false){ return ['result'=>7, 'msg'=>'can\'t renew api key.']; }
				}
				return ['result'=>0, 'msg'=>'OK'];
			}
		}catch(\ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//-----------------------------------------
	public static function active_users(){
		$api_key_expire = time();
		return \App\ApiKeyManager::where('api_key_expire', '>', $api_key_expire)->orwhere('api_key_valid_for_ever', 1)->count();
	}
	//-----------------------------------------
}
//---------------------------------------------
/*
portalCoed:
	1:chatbot
	2:lex
	3:test
	4:facebook
	z:alexa
*/