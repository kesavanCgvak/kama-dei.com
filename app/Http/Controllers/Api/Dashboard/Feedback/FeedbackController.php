<?php

namespace App\Http\Controllers\Api\Dashboard\Feedback;

use Illuminate\Http\Request;

class FeedbackController extends \App\Http\Controllers\Controller{
	//---------------------------------------
     public function org_all(){
		$rows = \App\Feedback::leftJoin("kama_log", "feedback.message_id", "kama_log.msg_id")
			->leftJoin("kama_usage", "kama_log.signin_id", "kama_usage.signin_id")
			->where('kama_usage.org_id', '<>', null)
			//->leftJoin("kamadeiep.organization_ep", "kama_usage.org_id", "organization_ep.organizationId")
			->select(
				"kama_usage.org_id as id",
				"kama_usage.org_name as name"
				//"organization_ep.organizationId as id",
				//"organization_ep.organizationShortName as name"
			)
//			->groupBy("organization_ep.organizationId")
			->groupBy("kama_usage.org_id")
//			->orderBy("organization_ep.organizationShortName", 'asc')
			->orderBy("kama_usage.org_name", 'asc')
			->get()
			->map(function($row){
				$org = \App\Organization::find($row->id);
				if($org!=null){ $row->name = $org->organizationShortName; }
				return $row;
			});
        if($rows->isEmpty() ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[] ];
        }else{
            return ['result'=>0, 'msg'=>'', 'data'=>$rows ];
        }
    }
	//---------------------------------------
	public function feedback(Request $request){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$request->all(),
					[
						'messageId'  => 'required',
						'feedback'   => 'required',
						'comment'    => 'required',
						'is_general' => ''
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $request->all();
			//-------------------------------
			if($data['feedback']=='true'){ $data['feedback']=1; }
			if($data['feedback']!=1     ){ $data['feedback']=0; }
			
			$is_general = 0;
			if(isset($data['is_general'])){
				if($data['is_general']!=1){ $is_general=0; }
				else{ $is_general=1; }
			}

			$data['comment'] = substr($data['comment'], 0, 1024);
			//-------------------------------
			\App\Feedback::insert([
				'message_id' => $data['messageId'],
				'created_on' => date("y-m-d H:i;s"),
				'comment'    => $data['comment'],
				'feedback'   => $data['feedback'],
				'is_general' => $is_general
			]);
			//-------------------------------
			return ['result'=>0, "msg"=>"ok"];
		}catch(\Throwable $ex){
			return ['result'=>1, "msg"=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getData($orgid, $sort, $dir, $per_page, $page_no, $searc_email, $sTime, $eTime, $ownerid, $showglobal,$archvd){
		return self::getData_($orgid, $sort, $dir, $per_page, $page_no, $ownerid, $showglobal, $searc_email, $sTime, $eTime, '', $archvd);
	}
	public function getDataS($orgid, $sort, $dir, $per_page, $page_no, $searc_email, $sTime, $eTime, $srch_val, $ownerid, $showglobal, $archvd){
		return self::getData_($orgid, $sort, $dir, $per_page, $page_no, $ownerid, $showglobal, $searc_email, $sTime, $eTime, $srch_val, $archvd);
	}
	private static function getData_($orgid,$sort,$dir,$per_page,$page_no,$ownerid,$showglobal,$searc_email,$sTime,$eTime,$srch_val,$archvd){
		try{
			//---------------------------------------------------------------------------
			$tTime = explode("_", $sTime);
			$sTime = "{$tTime[0]}-{$tTime[1]}-{$tTime[2]} {$tTime[3]}:{$tTime[4]}:{$tTime[5]}";
			$sTime = strtotime($sTime);
			$tTime = explode("_", $eTime);
			$eTime = "{$tTime[0]}-{$tTime[1]}-{$tTime[2]} {$tTime[3]}:{$tTime[4]}:{$tTime[5]}";
			$eTime = strtotime($eTime);
			//---------------------------------------------------------------------------
			$rows = \App\Feedback::leftJoin("kama_log", "feedback.message_id", "kama_log.msg_id")
				->leftJoin("kama_usage", "kama_log.signin_id", "kama_usage.signin_id")
/*
				->leftJoin("kamadeiep.portal", function($j){
					$j->on(
						\DB::raw("SUBSTR(kama_log.apikey,1,6)"),
						"=",
						\DB::raw("CONCAT(kamadeiep.portal.portal_number,kamadeiep.portal.code)")
					);
				})
*/
				->where( function($q) use($archvd){
					if($archvd==2){ return $q->where('feedback.archived', 1); }
					if($archvd==1){ return $q->where('feedback.archived', 0); }
					return $q;
				})
				->where( function($q) use($orgid){
					if($orgid==0){ return $q; }
					return $q->where('kama_usage.org_id', $orgid);
				})
				->select(
					"feedback.id as feedback_id",
					"feedback.created_on as chat_date",
					"feedback.comment",
					"feedback.is_general",
					"feedback.feedback as thumbs",
					"feedback.archived",
					"kama_log.raw_msg",
					"kama_usage.user_id as uid",
					"kama_usage.org_id as oid",
					"kama_usage.org_id as ownerId",
					"kama_usage.memo",
					"kama_usage.apikey",
					"kama_usage.email",
					"kama_usage.signin_id",
					//"kama_usage.timestamp",
					\DB::raw("SUBSTR(kama_usage.apikey,1,6) as portal"),
					"kama_usage.org_name as orgName"
					//"kamadeiep.portal.name as portalName"
				)
				->orderBy($sort, $dir);
			
			if($ownerid!=0){ $rows = $rows->where('kama_usage.org_id', $ownerid); }
			if($srch_val!=''){
				$rows = $rows
					->where('feedback.created_on'    , 'like', "%{$srch_val}%")
					->orwhere('feedback.comment'     , 'like', "%{$srch_val}%")
					->orwhere('kama_usage.memo'      , 'like', "%{$srch_val}%")
					->orwhere('kama_usage.org_name'  , 'like', "%{$srch_val}%");
					//->orwhere('kamadeiep.portal.name', 'like', "%{$srch_val}%");
					//->orwhereRaw("SUBSTR(kama_usage.apikey,1,6) like ?", ["%{$srch_val}%"]);
			}
			$rows = $rows
				->get()
				
				->filter(function($record) use($searc_email, $sTime, $eTime){
					$lTime = strtotime($record->chat_date);
					if($lTime>=$sTime && $lTime<=$eTime){
						if($searc_email=='0'){ return $record; }
						$email = \Crypt::decryptString($record->email);
						if(strpos($email, $searc_email)!==false){ return $record; }
					}
				})
				->map(function($row){
					$user = \App\User::find($row->uid);
					if($user==null){ $row->user_name = $row->memo; }
					else{ $row->user_name = $user->email; }
					
					$org = \App\Organization::find($row->oid);
					if($org==null){ $row->org_name = $row->orgName; }
					else{ $row->org_name = $org->organizationShortName; }
					
					if(strlen($row->comment)>25){ $row->comment = substr($row->comment, 0, 25)."..."; }
					
					if($row->is_general==1){ $row->q_a_pair = "General"; }
					else{
/*
						$q_a_pair = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($row->raw_msg), true);
						
						$row->q_a_pair = print_r($q_a_pair['response'], 1);
*/
						$row->q_a_pair = "";
					}
					
					$code          = substr($row->apikey, 1, 5);
					$portal_number = substr($row->apikey, 0, 1);
					$portal = \App\Portal::where("code", $code)->where("portal_number", $portal_number)->first();
					if($portal==null){ $row->portalName = $portal_number.$code; }
					else{ $row->portalName = $portal->name; }
					
					return $row;
				});
			$count = $rows->count();
			//---------------------------------------------------------------------------
			$data = [];
			$rows = $rows->forPage($page_no, $per_page);
			foreach($rows as $row){ $data[]=$row; }
			//---------------------------------------------------------------------------
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data ]; 
		}catch(\Throwable $ex){
			return ['result'=>1, "msg"=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getchatlog($chat_id){
		$feedback = \App\Feedback::where('id', $chat_id)->first();
		$kamaLog  = \App\KamaLog::find($feedback->message_id);
		if(is_null($kamaLog) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[]]; }
		$chat_id  = $kamaLog->signin_id;
		$usage = \App\KamaUsage::find($chat_id);
		if(is_null($usage) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[]]; }
		else{
//			$data  = \App\KamaLog::where('apikey', $usage->apikey)->orderBy('msg_id', 'asc')->get();
			$data  = \App\KamaLog::where('signin_id', $usage->signin_id)
				->orderBy('msg_id', 'asc')
				->get()
				->map(function($item) use($usage){
					$item->showRawMsg = $item->raw_msg;
					$item->showMsg    = $item->msg;
					$item->org_name   = $usage->org_name;
					
					$feedback = \App\Feedback::where('message_id', $item->msg_id)->first();
					if($feedback!=null){
						$item->feedback   = $feedback->feedback;
						$item->comment    = $feedback->comment;
						$item->is_general = $feedback->is_general;
					}
					return $item;
				});
	
			$usage->showEmail = $usage->email;
			$usage->nickname  = $usage->user_name;
			
			if($data->isEmpty() ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[]]; }
			else{ return ['result'=>0, 'msg'=>'', 'data'=>$data, 'usage'=>$usage ]; }
		}
	}
	//---------------------------------------
    public function sendEmail(Request $req){
        try{
			$tmpMail    = new \App\Mail\SendMail;
			$orgSetting = new \App\Organization();
			$tmp = $orgSetting->where('organizationId', $req->input('orgId'))->first();
			
			$cc = false;
			//if($tmp!=null){ if($tmp->AutoOnOff==1 && ($tmp->AutoEmail!='' && $tmp->AutoEmail!=null)){ $cc=true; } }
			
			if($cc){
				$tmpCC = [];
				$tmps = explode(";", $tmp->AutoEmail);
				foreach($tmps as $tmp){ if(trim($tmp)!=''){ $tmpCC[]=trim($tmp);} }
				
				\Mail::to($req->input('email'))->
						cc($tmpCC)->
						send(
							$tmpMail->kamaLog(
								$req->input('subject'), $req->input('log_id'), $req->input('instructions'), $req->input('orgId')
							)
						);
			}else{
				$mailTo   = $req->input('email');
				$tmpsMail = explode(",", $mailTo);
				$mainEmail = "";
				$ccMails  = [];
				
				foreach($tmpsMail as $mailTo){
					$mailTo = trim($mailTo);
					if($mailTo!=''){
						if($mainEmail==""){ $mainEmail=$mailTo; }
					}
				}

				foreach($tmpsMail as $i=>$mailTo){
					$mailTo = trim($mailTo);
					if($mailTo!='' && $mailTo!=$mainEmail){ $ccMails[] = $mailTo; }
				}
				\Mail::to($mainEmail)->
						cc($ccMails)->
						send(
							$tmpMail->kamaLog(
								$req->input('subject'), $req->input('log_id'), $req->input('instructions'), $req->input('orgId')
							)
						);
			}
            return ['result'=>0, 'msg'=>null];
//            return ['result'=>0, 'msg'=>null];
		}catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function setArchive(Request $request){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$request->all(),
					[
						'feedback_id' => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $request->all();
			//-------------------------------
			$feddback = \App\Feedback::where('id', $data['feedback_id'])->first();
			if($feddback==null){ throw new \Exception("Message not found"); }
			//-------------------------------
			$archived = 1;
			if($feddback->archived==1){ $archived=0; }
			//-------------------------------
			\App\Feedback::where('id', $feddback->id)->update(['archived'=>$archived, 'created_on'=>$feddback->created_on]);
			//-------------------------------
			return ['result'=>0, "msg"=>"ok"];
		}catch(\Throwable $ex){
			return ['result'=>1, "msg"=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}