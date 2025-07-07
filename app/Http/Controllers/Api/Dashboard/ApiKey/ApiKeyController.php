<?php
namespace App\Http\Controllers\Api\Dashboard\ApiKey;
//-------------------------------------------
use Illuminate\Http\Request;
use App\Controllers;
//-------------------------------------------
class ApiKeyController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function check(Request $req){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'apikey' => 'required',
						'page'   => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $req->all();
			//-------------------------------
			$apikey = \App\ApiKeyManager::where('api_key', $data['apikey'])->first();
			if($apikey==null){ throw new \Exception("Invalid apikey"); }
			//-------------------------------
			if(\App\ApiKeyManager\ApiKeyManagerClass::isActiveApikey($apikey->api_key)==0){ throw new \Exception("Apikey expired"); }
			//-------------------------------
			$user = \App\User::where('id', $apikey->userID)->first();
			if($user==null){ throw new \Exception("Invalid apikey-userid"); }
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
}
//-------------------------------------------
