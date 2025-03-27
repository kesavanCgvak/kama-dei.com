<?php

namespace App\Http\Controllers\Api\Dashboard\User;

use Illuminate\Http\Request;
use App\User;
use App\ConsumerUser;
use App\Controllers;
//use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Config;

class UserController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function show($orgID, $id){
		$data = User::findUserByID($orgID, $id);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[]];
		}else{
			return ['result'=>0, 'msg'=>'', 'data'=>$data];
		}
	}
	public function search($orgID, $field, $value){
		$field = $this->validFieldName($field);
		if( $field=='' ){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//----------------------
		$data = User::findUser($orgID, $field, $value);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
		}
		//----------------------
	}
	//---------------------------------------
	public function showAll( $orgID ){ return $this->showAllSorted($orgID, 'userId', 'asc'); }
	public function showAllSorted($orgID, $sort, $order){
		$sort = $this->validFieldName($sort);
		if( $sort=='' ){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//----------------------
		$data  = User::all();
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			//------------------
			$order = strtolower($order);
			switch($order){
				case 'asc' :{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data->sortBy($sort)->values()->all()]; }
				case 'desc':{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data->sortByDesc($sort)->values()->all()]; }
				default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
			}
			//------------------
		}
	}
	//---------------------------------------
	public function showPage( $orgID, $perPage, $page){
		$data  = User::myPageing($orgID, $perPage, $page, 'userId', 'asc');
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
		}
	}
	public function showPageSorted( $orgID, $sort, $order, $perPage, $page, $ownerId=-1, $level=0 ){
		//----------------------
		$sort = $this->validFieldName($sort);
		if( $sort=='' ){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//----------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		//----------------------
		$count = User::myUser($orgID, '', '')
						->where(function($q) use($ownerId){
							if($ownerId==-1){ return $q; }
							else{ return $q->where('orgID', $ownerId); }
						})
						->where(function($q) use($level){
							if($level==0){ return $q; }
							else{ return $q->where('levelID', $level); }
						})
						->count(); 
		//----------------------
		$data  = User::myPageing($orgID, $perPage, $page, $sort, $order, '', '', $ownerId, $level);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
		//----------------------
	}
	public function showPageSortSearch( $orgID, $sort, $order, $perPage, $page, $field, $value, $ownerId=-1, $level=0 ){
		$sort = $this->validFieldName($sort);
		if( $sort=='' ){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//----------------------
		$field = $this->validFieldName($field);
		if( $field=='' ){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//----------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		//----------------------
		$count = User::myUser($orgID, $field, $value)
						->where(function($q) use($ownerId){
							if($ownerId==-1){ return $q; }
							else{ return $q->where('orgID', $ownerId); }
						})
						->where(function($q) use($level){
							if($level==0){ return $q; }
							else{ return $q->where('levelID', $level); }
						})
						->count(); 
		//----------------------
		$data  = User::myPageing($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId, $level);
		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
		//----------------------
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $request){
		try{
			$user = User::find($id);
			if(is_null($user) ){ return ['result'=>1, 'msg'=>"user not found"]; }
			else{
				//$userOID   = strtolower(trim($request->input('orgID')));
				//$userEmail = strtolower(trim($request->input('email')));
//				/$userName  = strtolower(trim($request->input('userName')));
				$userOID   = trim($request->input('orgID'));
				$userEmail = trim($request->input('email'));
				$userName  = trim($request->input('userName'));
				
				$tmp       = User::where('email', $userEmail )->where('orgID',$userOID )->where('id','<>',$id)->first();
				if(!is_null($tmp) ){ return ['result'=>1, 'msg'=>'This email already exists on this organization']; }
				$tmp       = User::where('userName', $userName )->where('orgID',$userOID )->where('id','<>',$id)->first();
				if(!is_null($tmp) ){ return ['result'=>1, 'msg'=>'This username already exists on this organization']; }

				$user->userName = $userName;
				$user->email    = $userEmail;
				$user->orgID    = (($orgID==0)? trim($request->input('orgID')) :$orgID);
				$user->levelID  = trim($request->input('levelID'));
				$user->isAdmin  = ($user->levelID==1) ?1 :0;
				$user->isActive = trim($request->input('isActive'));
				$pass = trim($request->input('userPass'));
				if($pass!=''){ $user->userPass = $user->hash($pass); }
				
				if($user->userName  ==''){ return ['result'=>1, 'msg'=>'User name is empty']; }
				if($user->email     ==''){ return ['result'=>1, 'msg'=>'Email is empty']; }

				$tmp = $user->save();
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			//$userOID   = strtolower(trim($request->input('orgID'   )));
			//$userEmail = strtolower(trim($request->input('email'   )));
			//$userName  = strtolower(trim($request->input('userName')));
			$userOID   = trim($request->input('orgID'   ));
			$userEmail = trim($request->input('email'   ));
			$userName  = trim($request->input('userName'));

			$tmp       = User::where('userName', $userName )->where('orgID',$userOID )->first();
			if(!is_null($tmp) ){ return ['result'=>1, 'msg'=>'This username already exists on this organization']; }
			$tmp       = User::where('email', $userEmail )->where('orgID',$userOID )->first();
			$setInsert = true;
			if(!is_null($tmp) ){
				if($tmp->levelID!=4){ return ['result'=>1, 'msg'=>'This email already exists on this organization']; }
				$setInsert = false;
			}

			if($setInsert){
				$user = new User;
				$user->userName = $userName;
				$user->email    = $userEmail;
				$user->orgID    = (($orgID==0)? trim($request->input('orgID')) :$orgID);
				$user->levelID  = trim($request->input('levelID'));
				$user->isActive = trim($request->input('isActive'));
				$user->isAdmin  = ($user->levelID==1) ?1 :0;
				$user->createAt = date("Y-m-d H:i:s");//$request->input('dateCreated'   );
				$user->userPass = $user->hash( $user->createAt );

				if($user->email     ==''){ return ['result'=>1, 'msg'=>'Email is empty']; }
				if($user->userName  ==''){ return ['result'=>1, 'msg'=>'Username is empty']; }

				$tmp = $user->save();
				if($tmp){ 
					$passKey = $user->hash( $user->id.$user->createAt.'createUser' );
					$user->passKey = strtoupper($passKey);
					$user->save();
					$tmpMail = new \App\Mail\SendMail;
					\Mail::to($user->email)
							->send($tmpMail->createPass($user));

					$consumerUser = new ConsumerUser; 
					$consumerUser->consumerUser($user, $request->session()->get('userID') );
					return ['result'=>0, 'id'=>$user->id]; 
				}
				else{ return ['result'=>1, 'msg'=>'']; }
			}else{
				if($userEmail==''){ return ['result'=>1, 'msg'=>'Email is empty']; }
				if($userName ==''){ return ['result'=>1, 'msg'=>'Username is empty']; }
				$user = new User;
				$passKey = $user->hash( $tmp->id.$tmp->createAt.'createUser' );
				User::where('id', $tmp->id)
					->update([
						"userName" => $userName,
						"levelID"  => trim($request->input('levelID')),
						"isActive" => trim($request->input('isActive')),
						"isAdmin"  => ((trim($request->input('levelID'))==1) ?1 :0),
						"userPass" => $user->hash( $tmp->createAt ),
						"passKey"  => $passKey
					]);
				$user = User::find($tmp->id);
				$tmpMail = new \App\Mail\SendMail;
				\Mail::to($user->email)
							->send($tmpMail->createPass($user));
				return ['result'=>0, 'id'=>$user->id]; 
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function resetPass($id){
		try{
			//-------------------------------
			$user = User::find($id);
			//-------------------------------
			if(is_null($user) ){ return ['result'=>1, 'msg'=>"user not found"]; }
			//-------------------------------
			else{
				$passKey = $user->hash( $user->id.$user->createAt.'resetUser'.$user->id );
				$user->userPass = $user->hash( $user->createAt.'--' );
				$user->passKey  = strtoupper($passKey);
				$user->save();
				$tmpMail = new \App\Mail\SendMail;
				\Mail::to($user->email)
						->send($tmpMail->resetPass($user));
				return ['result'=>0, 'id'=>$user->id, 'msg'=>'OK reset password.']; 
			}
			//-------------------------------
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow($orgID, $id){
		try{
			if( \App\LexSetting::where('lexUserID', '=', $id)->count()!=0 )
				{ return ['result'=>1, 'msg'=>"This user is used in LEX SETTING, it can not be deleted."]; }
			if( \App\LexMapBots::where('lexUserID', '=', $id)->count()!=0 )
				{ return ['result'=>1, 'msg'=>"This user is used in LEX MAP, it can not be deleted."]; }

			$user = User::find($id);
			if(is_null($user) ){
				return ['result'=>1, 'msg'=>"user not found"];
			}else{
				//---------------------------
				if(\App\ConsumerUser::where('consumerUserId',$user->id)->count()>0){//consumer data....
					//-----------------------
					$msg = "Deleted successfully";
					//-----------------------
					$consumerUserPersonality = \App\ConsumerUserPersonality::where('consumerUserId',$user->id)->first();
					if($consumerUserPersonality!=null){
						if(
							\App\ConsumerUserPersonality::where('personalityId',$consumerUserPersonality->personalityId)->count()>1
							||
							$consumerUserPersonality->personalityId==Config::get('kama_dei.static.No_Persona',0)
						){ $msg = "This personality is used by another user and can be deleted."; }
						else{
							$consumerUserClass = new \App\Consumer\ConsumerUserClass;
							$consumerUserClass->deletePersonalityRecords( $consumerUserPersonality->personalityId );
							\App\Personality::where('personalityId',$consumerUserPersonality->personalityId)->delete();
						}
					}
					//-----------------------
					\App\ConsumerUserPersonality::where('consumerUserId',$user->id)->delete();
					\App\ConsumerUser::where('consumerUserId',$user->id)->delete();
					$tmp = $user->delete($id);
					return ['result'=>($tmp ?0 :1), 'msg'=>$msg];
					//-----------------------
				}
				//---------------------------
				else{
					$tmp = $user->delete($id);
					return ['result'=>($tmp ?0 :1), 'msg'=>'Deleted successfully'];
				}
				//---------------------------
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------}
	private function validFieldName($fieldName){
		switch(strtolower($fieldName)){
			case 'userid'    : { return "id";        }
			case 'id'        : { return "id";        }
			case 'username'  : { return "userName";  }
			case 'userpass'  : { return "userPass";  }
			case 'email'     : { return "email";     }
			case 'isadmin'   : { return "isAdmin";   }
			case 'orgid'     : { return "orgID";     }
			case 'levelid'   : { return "levelID";   }
			case 'createat'  : { return "createAt";  }
			case 'lastlogin' : { return "lastLogin"; }
			case 'last'      : { return "last";      }

			case 'levelname' : { return "levelID";   }
			
			case 'nickname'  : { return 'nickname';  }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	public function getOwnersList($orgID){
		$data  = User::getOwnersList($orgID);
		if($data!=null){
			$tmp = array_map('strtolower',  array_column($data, 'text'));
			array_multisort($tmp, SORT_NATURAL, $data);
		}
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
	public function getLevelsList($orgID){
		$data  = \App\Level::select('levelName as text','id')->orderBy('order', 'asc')->get();
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
}