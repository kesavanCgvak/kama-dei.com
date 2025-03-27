<?php
namespace App\Http\Controllers\Api\Dashboard\BotMessage;
//-------------------------------------------
use Illuminate\Http\Request;
use App\Controllers;
//-------------------------------------------
class BotMessageController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function getOwner($orgId=0){
		$data = \App\BotMessage::ownersList($orgId);
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
	public function getType(){
		$data  = \Config::get('kama_dei.BotMessage.Type');
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
	public function langList($orgID){
		$data  = \App\Http\Controllers\Api\Dashboard\BotMessage\BotMessageController::langList_($orgID);
		return [ 'result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data ];
	}
	public static function langList_($orgID=0){
		if($orgID==0){ $langs = \App\Language::select('name as caption',"code as value")->get(); }
		else{
			$langs = \App\OrganizationLanguage::where('org_id', $orgID)
						->leftJoin("language" ,"organization_language.language", '=', 'language.code')
						->select('name as caption',"code as value")
						->get();
		}
		if($langs->isEmpty()){ return \Config::get('kama_dei.BotMessage.Lang'); }
		return $langs->toArray();
	}
	//---------------------------------------
	public function viewTable($orgID, $sort, $order, $perpage, $page, $ownerId, $field='', $value=''){
		$total = \App\BotMessage::myQuery($orgID, $ownerId, $field, $value)->orderBy($sort, $order)->get();
		$qry   = \App\BotMessage::myQuery($orgID, $ownerId, $field, $value)->orderBy($sort, $order)->forPage($page, $perpage);
		$data=$qry->get();
		
		$tmpOrgId = $ownerId;
		if($ownerId==-1){ $tmpOrgId = $orgID; }
		$orglangsList = [];
/*		
		$orglangs = \App\OrganizationLanguage::where("org_id", $tmpOrgId)->get();
		if(! $orglangs->isEmpty()){
			foreach($orglangs as $orglang){ $orglangsList[] = $orglang->language; }
		}else{ $orglangsList[] = 'en'; }
*/
		$orglangs = $this->langList_($tmpOrgId);
		foreach($orglangs as $orglang){ $orglangsList[] = $orglang['value']; }
		foreach($data as $tmp){
			$tmp->LangList = "";
			foreach($orglangsList as $orglang){
				if( \App\BotMessage::where("Code", $tmp->Code)
						->where('Lang', $orglang)
						->count()!=0){
					$tmp->LangList .="{$orglang},";
				}
			}
			$tmp->LangList = substr($tmp->LangList,0,-1);
		}
		return ['result'=>0, 'msg'=>'', 'total'=>count($total), 'data'=>$data];
	}
	//---------------------------------------
	public function insertRow($messageType, $orgID, Request $req){
		try{	
			$validator = \Validator::make(
					$req->all(),
					[
						'Name'        => 'required',
						'OrgId'       => 'required',
						'Description' => 'required',
						'Message'     => 'required',
						'Type'        => 'required',
						'userID'      => 'required'
					],
					[
						"OrgId.required" => "The Organization not defined",
						"required" => "The :attribute not defined"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}

			$data = $req->all();
			
			$data['Name'       ] = trim($data['Name'       ]);
			$data['Description'] = trim($data['Description']);
			$data['Message'    ] = trim($data['Message'    ]);
			$data['Type'       ] = trim($data['Type'       ]);
			$data['Lang'       ] = (($req->has('Lang')) ?trim($data['Lang']) :'en');
			$data['userID'     ] = trim($data['userID'     ]);
			$data['OrgId']       = (($data['OrgId']==0) ?null :$data['OrgId']);

			if($messageType=='bot'){
				if(\App\BotMessage::where("Name"  , $data['Name' ])
								   ->where("OrgId", $data['OrgId'])
								   ->where("Lang" , $data['Lang' ])//Mohammad
								   ->count()!=0){
					return ['result'=>1, 'msg'=>"Message name is duplicated on this organization"];
				}
			}else{
				if(\App\Message::where("Name"  , $data['Name' ])
								   ->where("OrgId", $data['OrgId'])
								   ->where("Lang" , $data['Lang' ])//Mohammad
								   ->count()!=0){
					return ['result'=>1, 'msg'=>"Message name is duplicated on this organization"];
				}
			}

			if($messageType=='bot'){ $message = new \App\BotMessage; }
			else{ $message = new \App\Message; }
			
			$message->Name        = $data['Name'       ];
			$message->OrgId       = $data['OrgId'      ];
			$message->Description = $data['Description'];
			$message->Message     = $data['Message'    ];
			$message->Type        = $data['Type'       ];
			$message->Lang        = $data['Lang'       ];
			$message->Created_by  = $data['userID'     ];
			$message->Created_on  = date("Y-m-d H:i:s");

			if($message->save()){ return ['result'=>0, 'msg'=>"Message added"]; }
			else{ return ['result'=>1, 'msg'=>"Error on insert record"]; }
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $req){
		try{
			$validator = \Validator::make(
					$req->all(),
					[
						'Name'        => 'required',
						'OrgId'       => 'required',
						'Code'        => 'required',
						
						'Message'     => '',
						'emptyMessage'=> 'required',
						
						'Type'        => 'required',
						'Lang'        => 'required',
						'userID'      => 'required',
						'parentId'    => 'required'
					],
					[
						"Lang.required"  => "The Language not defined",
						"OrgId.required" => "The Organization not defined",
//						"Message.required" => "The Message is empty",
						"emptyMessage.required" => "The Empty Message flag not set",
						"required"       => "The :attribute not defined"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}

			$data = $req->all();
			$data['Description'] = (($req->has('Description')) ?$data['Description'] :null);
			$data['OrgId'      ] = (($data['OrgId']==0) ?null :$data['OrgId']);

			if($data['emptyMessage']==0 && $data['Message']==''){
				return ['result'=>1, 'msg'=>'The Message is empty'];
			}
			
			$isNew = true;
			$message = \App\BotMessage::where("OrgId", $data['OrgId'])
					->where("Code", $data['Code'])
					->where("Lang", $data['Lang'])
					->first();
			if($message==null){
				$defaultMsg = \App\BotMessage::where("OrgId", null)
						->where("Code", $data['Code'])
						->where("Lang", 'en')
						->first();
				$is_required = 1;
				if($defaultMsg!=null){ $is_required=$defaultMsg->is_required; }
				$message = new \App\BotMessage;
				$message->Name        = $data['Name'      ];
				$message->OrgId       = $data['OrgId'     ];
				$message->Code        = $data['Code'      ];
				$message->Lang        = $data['Lang'      ];
				$message->parentId    = $data['parentId'  ];
				$message->Type        = $data['Type'      ];
				$message->Created_by  = $data['userID'    ];
				$message->Created_on  = date("Y-m-d H:i:s");
				$message->is_required = $is_required;
			}else{
				$isNew = false;
				$message->Modified_by = $data['userID'     ];
				$message->Modified_on = date("Y-m-d H:i:s");
			}
			$message->Description = $data['Description'];
			$message->Message     = $data['Message'    ];
			$tmpSaave = $message->save();

			if($data['OrgId']!=null){
				$langs = \App\OrganizationLanguage::where('org_id', $data['OrgId'])->get();
				if(! $langs->isEmpty()){
					foreach($langs as $lang){
						if($data['Lang']!=$lang->language){
							if( \App\BotMessage::where("OrgId", $data['OrgId'])
									->where("Code", $data['Code'])
									->where("Lang", $lang->language)
									->count()==0
							 ){
								$tmpMsg1 = \App\BotMessage::where("OrgId", null)
									->where("Code", $data['Code'])
									->where("Lang", $lang->language)
									->first();
								if($tmpMsg1!=null){
									$tmpMsg2 = new \App\BotMessage;
									$tmpMsg2->Name        = $tmpMsg1->Name;
									$tmpMsg2->OrgId       = $data['OrgId'];
									$tmpMsg2->Code        = $data['Code'];
									$tmpMsg2->Lang        = $lang->language;
									$tmpMsg2->parentId    = $data['parentId'];
									$tmpMsg2->Type        = $tmpMsg1->Type;
									$tmpMsg2->Created_by  = $data['userID'];
									$tmpMsg2->Created_on  = date("Y-m-d H:i:s");
									$tmpMsg2->Description = $tmpMsg1->Description;
									$tmpMsg2->Message     = $tmpMsg1->Message;
									$tmpMsg2->is_required = $tmpMsg1->is_required;
									$tmpMsg2->save();
								}
							}
						}
					}
				}
			}

			if($tmpSaave){ return ['result'=>0, 'msg'=>(($isNew) ?"Message added" :"Message changed")]; }
			else{ return ['result'=>1, 'msg'=>(($isNew) ?"Error on add message" :"Error on change message")]; }

		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow( $orgID, $id, Request $req){
		try{
return ['result'=>1, 'msg'=>"This item temporarily disabled"];
			if($messageType=='bot'){ $message = \App\BotMessage::find($id); }
			else{ $message = \App\Message::find($id); }
			if($message==null ){ return ['result'=>1, 'msg'=>"Message not found"]; }
			else{
				if($message->OrgId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this Message"]; }
				
				if($messageType=='bot'){ \App\BotMessage::where('id', $id)->orWhere('parentId', $id)->delete(); }
				else{ \App\Message::where('id', $id)->orWhere('parentId', $id)->delete(); }
				return ['result'=>0, 'msg'=>''];
			}
		}catch(\ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getMasseage_($parentId, $lang){
		$qry = \App\BotMessage::where('Lang', $lang);
		$data = $qry->where(function($q) use($parentId){
			return $q->where('id', $parentId)->orWhere('parentId', $parentId);
		})->first();

		return ['result'=>0, 'msg'=>'', 'data'=>$data];
	}
	//---------------------------------------
	public function getMasseage(Request $req){
		try{
			$data = $req->all();
			$data['OrgId'] = ($data['OrgId']==0) ?null :$data['OrgId'];

			$tmp = \App\BotMessage::where('Code', $data['Code'])->where('OrgId', $data['OrgId'])->where('Lang', $data['Lang'])->first();
			if($tmp==null){
				$tmp = \App\BotMessage::where('Code', $data['Code'])->where('OrgId', null)->where('Lang', $data['Lang'])->first();
				if($tmp!=null){ $tmp->id=0; }
			}

			return ['result'=>0, 'msg'=>'', 'data'=>$tmp];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function viewTable_AI($orgID, $sort, $order, $perpage, $page, $ownerId, $field='', $value=''){
		$sort  = ($sort=='Name') ?'orgName' :$sort;
		$total = \App\Message::myQuery($orgID, $ownerId, $field, $value)->orderBy($sort, $order)->get();
		$qry   = \App\Message::myQuery($orgID, $ownerId, $field, $value)->orderBy($sort, $order)->forPage($page, $perpage);
		$data=$qry->get();
		$tmpOrgId = $ownerId;
		if($ownerId==-1){ $tmpOrgId = $orgID; }
		$orglangsList = [];

		$orglangs = $this->langList_($tmpOrgId);
		foreach($orglangs as $orglang){ $orglangsList[] = $orglang['value']; }

		foreach($data as $tmp){
			$tmp->LangList = "";
			foreach($orglangsList as $orglang){
				if( \App\Message::where("messageCode", $tmp->Code)
						->where('messageLanguage', $orglang)
						->count()!=0){
					$tmp->LangList .="{$orglang},";
				}
			}
			$tmp->LangList = substr($tmp->LangList,0,-1);
		}
/*
		foreach($data as $tmp){
			$tmp->LangList = "";
			$tmpOrgId = $tmp->OrgId;
			$langs = \App\Message::where("messageCode", $tmp->Code)
						->where(function($q) use($tmpOrgId){
							return $q->where("orgId", null)->orWhere("orgId", $tmpOrgId);
						})
						->groupBy('messageCode')->groupBy('messageLanguage')
						->select("messageCode as Code", "messageLanguage as Lang")
						->get();
			

			if(! $langs->isEmpty()){
				foreach($langs as $lang){ $tmp->LangList .=",{$lang->Lang}"; }
				$tmp->LangList = substr($tmp->LangList,1);
			}

			$text = \App\Message::where("messageCode", $tmp->Code)
						->where(function($q) use($tmpOrgId){
							return $q->where("orgId", null)->orWhere("orgId", $tmpOrgId);
						})
						->where('messageLanguage', 'en')
						->orderBy("messageId", "desc")
						->first();
			if($text!=null){
				$tmp->Message      = $text->messageText;
				$tmp->messageVoice = $text->messageVoice;
			}
		}
*/
		return ['result'=>0, 'msg'=>'', 'total'=>count($total), 'data'=>$data];
	}
	//---------------------------------------
	public function getOwner_AI($orgId=0){
		$data = \App\Message::ownersList($orgId);
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
	public function getMasseage_AI(Request $req){
		try{
			$data = $req->all();
			$data['OrgId'] = ($data['OrgId']==0) ?null :$data['OrgId'];
			$tmp = \App\Message::where('messageCode', $data['Code'])
					->where('orgId', $data['OrgId'])->where('messageLanguage', $data['Lang'])
					->select(
						"*",
						"description as Description",
						"messageId as id",
						"messageCode as Code",
						"orgId as OrgId",
						"messageText as Message",
						"messageLanguage as Lang"
					)
					->first();
			if($tmp==null){
				$tmp = \App\Message::where('messageCode', $data['Code'])
					->where('orgId', null)->where('messageLanguage', $data['Lang'])
					->select(
						"*",
						"description as Description",
						"messageId as id",
						"messageCode as Code",
						"orgId as OrgId",
						"messageText as Message",
						"messageLanguage as Lang"
					)
					->first();
				if($tmp!=null){ $tmp->id=0; }
			}
			return ['result'=>0, 'msg'=>'', 'data'=>$tmp];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function editRow_AI($orgID, $id, Request $req){
		try{
			$validator = \Validator::make(
					$req->all(),
					[
						'OrgId'       => 'required',
						'Code'        => 'required',
						'Message'     => 'required',
						'Lang'        => 'required',
						'userID'      => 'required'
					],
					[
						"Lang.required"  => "The Language not defined",
						"OrgId.required" => "The Organization not defined",
						"Message.required" => "The Message is empty",
						"required"       => "The :attribute not defined"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}

			$data = $req->all();
			$data['messageVoice'] = (($req->has('messageVoice')) ?$data['messageVoice'] :null);
			$data['OrgId'      ] = (($data['OrgId']==0) ?null :$data['OrgId']);

			$isNew = true;
			$message = \App\Message::where("orgId", $data['OrgId'])
					->where("messageCode", $data['Code'])
					->where("messageLanguage", $data['Lang'])
					->first();
			if($message==null){
				$message = new \App\Message;
				$message->orgId           = $data['OrgId'];
				$message->messageCode     = $data['Code' ];
				$message->messageLanguage = $data['Lang' ];
			}else{
				$isNew = false;
			}
			$message->description  = $data['Description' ];
			$message->messageVoice = $data['messageVoice'];
			$message->messageText  = $data['Message'     ];
			$tmpSaave = $message->save();

			if($data['OrgId']!=null){
				$langs = \App\OrganizationLanguage::where('org_id', $data['OrgId'])->get();
				if(! $langs->isEmpty()){
					foreach($langs as $lang){
						if($data['Lang']!=$lang->language){
							if( \App\Message::where("orgId", $data['OrgId'])
									->where("messageCode", $data['Code'])
									->where("messageLanguage", $lang->language)
									->count()==0
							 ){
								$tmpMsg1 = \App\Message::where("orgId", null)
									->where("messageCode", $data['Code'])
									->where("messageLanguage", $lang->language)
									->first();
								if($tmpMsg1!=null){
									$tmpMsg2 = new \App\Message;

									$tmpMsg2->orgId           = $data['OrgId'];
									$tmpMsg2->messageCode     = $data['Code' ];
									$tmpMsg2->messageLanguage = $lang->language;
									$tmpMsg2->description     = $tmpMsg1->description;
									$tmpMsg2->messageVoice    = $data['messageVoice'];
									$tmpMsg2->messageText     = $data['Message'     ];
									$tmpMsg2->save();
								}
							}
						}
					}
				}
			}
	
			if($tmpSaave){ return ['result'=>0, 'msg'=>(($isNew) ?"Message added" :"Message changed")]; }
			else{ return ['result'=>1, 'msg'=>(($isNew) ?"Error on add message" :"Error on change message")]; }

		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------

	//---------------------------------------
	public function upload($jsonFile){
		try{
			$userId = 0;
			$user = \App\User::where('userName', 'admin')->where('isAdmin', 1)->where('orgID', 0)->where('levelID', 1)->first();
			if($user!=null){ $userId = $user->id; }
			$file = fopen($jsonFile,"r");
			$cnt  = 0;
			$Code = 100;
			while(! feof($file)){
				$line = fgets($file);
				$row = json_decode($line, true);
				if($row==null){ continue; }
				$message = new \App\BotMessage;
				$message->Name = $row['messagename'];
				$message->Code = 0;
//				$message->Code = $Code;
				$message->Type = $row['type'];
				$message->OrgId = $row['orgid'];
				$message->description = $row['description'];
				$message->Message = $row['messagetext'];
				$message->Lang = "en";
				$message->parentId = $row['_id']['$oid'];
				$message->Created_by = $userId;
				$message->Created_on = date("Y-m-d H:i:s");
				try{
					$message->save();
					$Code++;
				}catch(\Throwable $ex){}
				
				\App\BotMessage::where('id', $message->id)->update(['Code'=>$message->id]);
				$cnt++;
				echo "<pre>parentId: {$message->parentId}<br/>Code: {$message->id}</pre>";
  			}
			fclose($file);
			return $cnt;
		}catch(\Throwable $ex){
			return $ex->getMessage();
		}
	}
	//---------------------------------------
	public function resetRow($id){
		try{
			$msg = \App\BotMessage::where('id', $id)->first();
			if($msg==null){ return ['result'=>1, 'msg'=>'message not found']; }
			\App\BotMessage::where('Code', $msg->Code)->where('OrgId', $msg->OrgId)->delete();
			return ['result'=>0, 'msg'=>'ok'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function resetRow_AI($id){
		try{
			$msg = \App\Message::where('messageId', $id)->first();
			if($msg==null){ return ['result'=>1, 'msg'=>'message not found']; }
			\App\Message::where('messageCode', $msg->messageCode)->where('orgId', $msg->orgId)->delete();
			return ['result'=>0, 'msg'=>'ok'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}
//-------------------------------------------
