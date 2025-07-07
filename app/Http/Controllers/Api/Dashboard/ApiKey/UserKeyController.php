<?php
namespace App\Http\Controllers\Api\Dashboard\ApiKey;
//-------------------------------------------
use Illuminate\Http\Request;
use App\Controllers;
//-------------------------------------------
class UserKeyController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	private static function isValidUserKey($key){
		//-------------------------------
		$userKey = \App\UserKey::where('userKey', $key)->first();
		if($userKey==null){ throw new \Exception("Invalid userkey"); }
		//-------------------------------
		$expireInMin = env("userkey_expire_in_min", 60);
		$genrateAt   = strtotime($userKey->genrateAt);
		$expireIn    = strtotime("+{$expireInMin} min", $genrateAt);
		$now         = time();
		if($userKey->valid4ever==0){ if($expireIn<$now){ throw new \Exception("UserKey expired"); } }

		return $userKey;
	}
	//---------------------------------------
	public function check(Request $req){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'userkey' => 'required',
						'page'    => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $req->all();
			//-------------------------------
			$userKey = self::isValidUserKey($data['userkey']);
			//-------------------------------
			$user = \App\User::where('id', $userKey->user_id)->first();
			if($user==null){ throw new \Exception("Invalid userkey-userid"); }
			//-------------------------------
			$page = \App\SitePages::where('id', $data['page'])->first();
			if($page==null){ throw new \Exception("Invalid page id"); }
			//-------------------------------
			if($user->levelID!=1){
				$pageLevel = \App\PageLevel::where('pageID', $page->id)->where('levelID', $user->levelID)->first();
				if($pageLevel==null){ throw new \Exception("Access denied"); }
			}
			//-------------------------------
			return ['result'=>1, 'msg'=>"OK", 'detail'=>"Access granted"];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>0, 'msg'=>"NO", 'detail'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public static function renew($inValue){
		//-------------------------------
		$userKey = \App\UserKey::where('userKey', $inValue)->first();
		if($userKey==null){
			$userKey = \App\UserKey::where('user_id', $inValue)->first();
			if($userKey==null){ throw new \Exception("Invalid key value"); }
		}
		//-------------------------------
		$userKey->userKey   = $userKey->hash($userKey->user_id.date("YmdHis"));
		$userKey->genrateAt = date("Y-m-d H:i:s");
		$userKey->save();
		//-------------------------------
		return $userKey->userKey;
	}
	//---------------------------------------
	public static function renewApi(Request $req){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'value' => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $req->all();
			//-------------------------------
			self::renew($data['value']);
			return ['result'=>1, 'msg'=>"OK"];
			//return ['result'=>1, 'msg'=>"OK", 'userkey'=>self::renew($data['value'])];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>0, 'msg'=>"NO", 'detail'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public static function isValid($key){
		//-------------------------------
		$userKey = \App\UserKey::where('userKey', $key)->first();
		if($userKey==null){ throw new \Exception("Invalid userkey"); }
		//-------------------------------
		$expireInMin = env("userkey_expire_in_min", 60);
		$genrateAt   = strtotime($userKey->genrateAt);
		$expireIn    = strtotime("+{$expireInMin} min", $genrateAt);
		$now         = time();
		if($userKey->valid4ever==0){ if($expireIn<$now){ throw new \Exception("UserKey expired"); } }

		return $userKey->userKey;
	}
	//---------------------------------------
	public static function isValidApi(Request $req){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'userkey' => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $req->all();
			//-------------------------------
			return ['result'=>1, 'msg'=>"OK", 'userkey'=>self::isValid($data['userkey'])];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>0, 'msg'=>"NO", 'detail'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public static function getApi($user_id){
		try{
			//-------------------------------
			$userKey = \App\UserKey::where('user_id', $user_id)->first();
			if($userKey==null){ throw new \Exception("Invalid user id"); }
			//-------------------------------
			$expireInMin = env("userkey_expire_in_min", 60);
			$genrateAt   = strtotime($userKey->genrateAt);
			$expireIn    = strtotime("+{$expireInMin} min", $genrateAt);
			$now         = time();
			if($userKey->valid4ever==0){ if($expireIn<$now){ throw new \Exception("UserKey expired"); } }
			//-------------------------------
			return ['result'=>1, 'msg'=>"OK", 'userkey'=>$userKey->userKey];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>0, 'msg'=>"NO", 'detail'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}
//-------------------------------------------
