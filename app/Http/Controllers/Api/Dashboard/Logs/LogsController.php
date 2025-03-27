<?php

namespace App\Http\Controllers\Api\Dashboard\Logs;

//require dirname(__FILE__).'/../../../../../PHPMailer/Exception.php';
//require dirname(__FILE__).'/../../../../../PHPMailer/PHPMailer.php';
//require dirname(__FILE__).'/../../../../../PHPMailer/SMTP.php';

//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

use Illuminate\Http\Request;
use App\Controllers;
//------------------------------------------------------------
//------------------------------------------------------------
class LogsController extends \App\Http\Controllers\Controller{
	//--------------------------------------------------------
    public function org_all(){
        $data = \App\KamaUsage::org_all();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[] ];
        }else{
            return ['result'=>0, 'msg'=>'', 'data'=>$data ];
        }
    }
	//--------------------------------------------------------
	public function getchatlog($chat_id){
		$usage = \App\KamaUsage::find($chat_id);
		if(is_null($usage) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[]]; }
		else{
//			$data  = \App\KamaLog::where('apikey', $usage->apikey)->orderBy('msg_id', 'asc')->get();
			$data  = \App\KamaLog::where('signin_id', $usage->signin_id)
				->orderBy('msg_id', 'asc')
				->get()
				->map(function($item){
					$item->showRawMsg = $item->raw_msg;
					$item->showMsg    = $item->msg;
					
					$feedback = \App\Feedback::where('message_id', $item->msg_id)->first();
					if($feedback==null){ $item->feedback==''; }
					else{ $item->feedback = $feedback->feedback; }
					return $item;
				});
	
			if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[]]; }
			else{ return ['result'=>0, 'msg'=>'', 'data'=>$data ]; }
		}
	}
	//--------------------------------------------------------
    public function showPageSorted($archive,$s_time,$e_time,$user_id,$org_id, $sort, $order, $perPage, $page ,$searc_email,$searc){
set_time_limit(0);
ini_set('memory_limit', '8000M');
		$s_time = str_replace("_", "-", substr($s_time, 0, 10))." ".str_replace("_", ":", substr($s_time, -8));
		$s_time = strtotime($s_time);
		$e_time = str_replace("_", "-", substr($e_time, 0, 10))." ".str_replace("_", ":", substr($e_time, -8));
		$e_time = strtotime($e_time);
		switch($sort){
			case 'nickname':
				$sort = 'user_name';
				break;
			case 'showEmail':
				$sort = 'email';
				break;
			default:
		}

		$count = \App\KamaUsage::logData($archive,$s_time,$e_time,$user_id,$org_id,  '', '',$searc_email,$searc)
									->get()
									->filter(function($record) use($searc_email, $searc){
										if($searc_email=='0' && $searc=='0'){ return $record; }
										if($searc_email!='0'){
											if(strpos($record->email, $searc_email)!==false){ return $record; }
										}
										if($searc!='0'){
											if(strpos(strtolower($record->user_name), strtolower($searc))!==false){ return $record; }
											if(strpos(strtolower($record->org_name ), strtolower($searc))!==false){ return $record; }
										}
									});
		$count = count($count);
									
        $data = \App\KamaUsage::logData($archive,$s_time,$e_time,$user_id,$org_id, '', '',$searc_email, $searc)
									->orderBy($sort, $order)
//									->forPage($page, $perPage)
									->get()

									->filter(function($record) use($searc_email, $searc){
										if($searc_email=='0' && $searc=='0'){ return $record; }
										if($searc_email!='0'){
											if(strpos($record->email, $searc_email)!==false){ return $record; }
										}
										if($searc!='0'){
											if(strpos(strtolower($record->user_name), strtolower($searc))!==false){ return $record; }
											if(strpos(strtolower($record->org_name ), strtolower($searc))!==false){ return $record; }
										}
									})
									->forPage($page, $perPage)
									->map(function($item){
										$item->showEmail = $item->email;
										$item->nickname  = $item->user_name;
/*
										$item->swhoEmail = $item->email;
\Log::channel('daily')->info("email: {$item->email}");

										$item->email = \Illuminate\Support\Facades\Crypt::decryptString('eyJpdiI6IlpIb0RQcUFIS2pwL3BwOGYvaERGaUE9PSIsInZhbHVlIjoiR2Y1aVdEZ1Z2TVkzZmh0WVExQnZrSlJNdG0ya09PcVdTRFNFUHZicGZWWT0iLCJtYWMiOiI0MzJmNTEzOWExOGY0Y2NkYTRmOTUxODlmOGE3ODgzYTBhMDU2NGMzOGZmZDYxNmY1MWM4MWY1MzU1MDQzMzM4In0');
										//$item->email = \Illuminate\Support\Facades\Crypt::decrypt($item->email);
*/
										return $item;
									});

        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
			$retData = [];
            foreach( $data as $key=>$tmp ){
				//$tmp->ip = $tmp->email;
				$tmpKamaLog = new \App\KamaLog;
                $dataKamaLogCNT = $tmpKamaLog
                    ->select('apikey')
//                    ->where('apikey', $tmp->apikey)
                    ->where('signin_id', $tmp->signin_id)
                    ->count();
                $tmp->logcount=$dataKamaLogCNT;
                $max_logtime=0;
                if($dataKamaLogCNT>0){
                    $dataKamaLogTime = $tmpKamaLog
                        ->select('timestamp')
                        ->where('apikey', $tmp->apikey)
                        ->orderBy('msg_id', 'desc')
                        ->get();
//						$max_logtime=floor((strtotime($dataKamaLogTime[0]->timestamp)-strtotime($tmp->timestamp))%86400%60);
                        $max_logtime=floor((strtotime($dataKamaLogTime[0]->timestamp)-strtotime($tmp->timestamp)));
                }
//				$tmp->log_s=$max_logtime.'sec.';
				if($max_logtime<60){ $tmp->log_s=$max_logtime.'sec.'; }
				else{
					if($max_logtime<3600){
						$m = round($max_logtime/60, 0);
						$s = $max_logtime%60;
						$tmp->log_s="{$m}min {$s}sec.";
					}else{
						$h = round($max_logtime/3600, 0);
						$max_logtime_new = $max_logtime%3600;
						$m = round($max_logtime_new/60, 0);
						$s = $max_logtime_new%60;
						$tmp->log_s="{$h}h {$m}m {$s}sec.";
					}
				}
				$retData[] = $tmp;
            }
/*			
            foreach( $data as $key=>$tmp ){
				$tmp->ip = $tmp->email;
				$tmp->email = $tmp->ip;
			}
*/
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$retData];
        }
    }
	//--------------------------------------------------------
    public function upArchive($orgId,$id,Request $request){
		try{
//        	$temp_json=$request->input('archiveId_arr');
			$log =  \App\KamaUsage::find($id);
			if(is_null($log)){ return ['result'=>1, 'msg'=>"Log not found"]; }
			else{
				if($log->archive==0){ $log->archive=1; }
				else{ $log->archive=0; }
				$tmp = $log->save();
				/*
				foreach ($temp_json as $valueid){
					$extended_entity = Extended_chatbot_usage::find($valueid);
					if(is_null($extended_entity) ){}
					else{
						$extended_entity->archive=$extended_chatbot_usage->archive;
						$extended_entity->save();
					}
				}
				*/
			}
			return ['result'=>0, 'archive'=>$log->archive];
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//--------------------------------------------------------
    public function deleteRow($orgId,$id,Request $request){
        try{
			if($orgId!=0){ return ['result'=>1, 'msg'=>"You can't delete this Log"]; }
//			$temp_json=$request->input('archiveId_arr');
			$usage =  \App\KamaUsage::find($id);
			if(is_null($usage)){ return ['result'=>1, 'msg'=>"Log not found"]; }
			else{
				$data =  \App\KamaLog::where('signin_id', $usage->signin_id)->delete();
				$usage->delete();
	            return ['result'=>0, ''];
			}
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//--------------------------------------------------------
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
				\Mail::to($req->input('email'))->
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
	//--------------------------------------------------------
    public function sendAutoEmails(Request $req){
        try{
			$orgs   = \App\Organization::where('AutoOnOff', 1)->get();
			$tmpMail = [];
			if(!$orgs->isEmpty()){
set_time_limit(0);
ini_set('memory_limit', '4096M');

				$retVal = [];

				foreach($orgs as $org){
					$routePath = "../storage/app/public/autoemail/{$org->send_chat_format}";
					if(!file_exists($routePath)){
						mkdir($routePath, 0777);
					}
//if($org->organizationId!=1){ continue; }
					if($org->AutoEmail=='' || $org->AutoEmail==null){ continue; }

					$path = "{$routePath}/{$org->organizationId}";
					if(!file_exists($path)){
						mkdir($path, 0777);
					}
					
					$maxLogSend = (($org->chat_logs_sent==2) ?50 :10);
//$maxLogSend = 2;
					$timestamp = date('Y-m-d H:i:s', strtotime("-3 minutes"));
					$all        = \App\KamaUsage::where('isSend', 0)
									->where('org_id', $org->organizationId)
									->where('timestamp', '<=', $timestamp)
									->count();
					$kamaUsages = \App\KamaUsage::where('org_id', $org->organizationId)
									->where('isSend', 0)
									->where('timestamp', '<=', $timestamp)
//->orderBy('timestamp', 'desc')
									->take($maxLogSend)
									->get();
					if($kamaUsages->count()==0){ continue; }
					$retVal[] = [
						'org'     => $org->organizationShortName,
						'id'      => $org->organizationId,
						'filetype'=> $org->send_chat_format,
						'logs'    => $kamaUsages->count()." of {$all}",
						'before'  => $timestamp
					];
					$attached = [];
					if(!$kamaUsages->isEmpty()){
						$attached = [];
						foreach($kamaUsages as $tmpUsg){
							$name = preg_replace(
								'/[^a-z0-9-.]+/',
								'_',
								strtolower($tmpUsg->user_name."-".$tmpUsg->timestamp."-".$tmpUsg->signin_id)
							);
							if($org->send_chat_format=='csv'){
								$csvTEXT =
									view(
										'emails.kamaLogCSV',
										['log_id'=>$tmpUsg->signin_id, 'instructions'=>"", 'orgID'=>$org->organizationId]
									);
								$csvName = "{$path}/{$name}.csv";
								$attached[] = "{$name}.csv";
								@unlink($csvName);
								file_put_contents($csvName, $csvTEXT);
							}
							if($org->send_chat_format=='pdf'){
								$pdf = \App::make('dompdf.wrapper');
								$pdf->loadHTML(
									view(
										'emails.kamaLog',
										['log_id'=>$tmpUsg->signin_id, 'instructions'=>"", 'orgID'=>$org->organizationId]
									)
								);
								$pdfName = "{$path}/{$name}.pdf";
								$attached[] = "{$name}.pdf";
								@unlink($pdfName);
								$pdf->save($pdfName);
							}
						}
						
						$emails = [];
						$tmps = explode(";", $org->AutoEmail);
						foreach($tmps as $tmp){ if(trim($tmp)!=''){ $emails[]=trim($tmp);} }

//$emails = ["test2@behroozdarabi.com"];
						if($org->chat_logs_sent==2){
							$tmpMail[$org->organizationId] = new \App\Mail\SendMail;
							\Mail::to($emails)->
									send(
										$tmpMail[$org->organizationId]->kamaautoLog(
											"Kama-DEI chat logs: {$org->organizationShortName}",
											$org->organizationId,
											$kamaUsages->count(),
											$timestamp,
											$path,
											$attached
										)
									);
						}else{
							$tmpMail[$org->organizationId] = [];
							for($i=0; $i<count($attached); $i++){
								$tmpMail[$org->organizationId][$i] = new \App\Mail\SendMail;
								\Mail::to($emails)->
										send(
											$tmpMail[$org->organizationId][$i]->kamaautoLog(
												"Kama-DEI chat logs: {$org->organizationShortName}",
												$org->organizationId,
												1,
												$timestamp,
												$path,
												[$attached[$i]]
											)
										);
							}
						}
						foreach($kamaUsages as $tmpUsg){
							\App\KamaUsage::where('signin_id', $tmpUsg->signin_id)
								->update(['isSend'=>1]);
						}
					}
				}
				
				return [
					'result'=>0,
					'msg'=>'',
					'data'=>$retVal
				];
			}else{
				return [
					'result'=>0,
					'msg'=>'',
					'data'=>[]
				];
			}
			
		}catch(\Trowable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//--------------------------------------------------------
    public function sendMylog(Request $req){
        try{
			//------------------------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'portal'     => 'required|min:6|max:6',
						'userId'     => 'gt:0',
						'facebookId' => 'gt:0',
					],
					[
						"required" => "The :attribute not defined"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors];
			}
			//------------------------------------------------
			$data       = $req->all();
			$portalCode = $data['portal'];
			$retVal     = ['user'=>[], 'portal'=>[], 'log'=>[]];
			//------------------------------------------------
			$code = substr($portalCode, 1, 5);
			$nmbr = substr($portalCode, 0, 1);
			$portal = \App\Portal::where('code',$code)->where('portal_number',$nmbr)->first();
			//------------------------------------------------
			if($portal==null){ return ['result'=>1, 'msg'=>['apikey'=>['Invalid portal code. portal not found']]]; }
			//------------------------------------------------
			if(isset($data['facebookId'])){
				$userData = \App\User::where('email', $data['facebookId'])->first();
				if($userData!=null){ $userID = $userData->id; }
				else{ $userID = 0; }
			}else{
				if(isset($data['userId'])){ $userID = $data['userId']; }
				else{ $userID = 0; }
			}
			//------------------------------------------------
			$apiKeyManger = \App\ApiKeyManager::where('userID', $userID)->where('portal_code', $portalCode)->first();
			if($apiKeyManger==null){ return ['result'=>1, 'msg'=>['apikey'=>['Invalid portal or user id. apikey not found']]]; }
			$apikey = $apiKeyManger->api_key;
			//------------------------------------------------
			if($portal==null){ $retVal['portal']['name'] = "Test"; }
			else{ $retVal['portal']['name'] = $portal->name; }
			if($nmbr==4){ $retVal['portal']['name'] = "Facebook"; }
			$pType = \App\PortalType::where('number', $nmbr)->first();
			if($pType==null)
				{ $retVal['portal']['type'] = "Test"; }
			else
				{ $retVal['portal']['type'] = $pType->caption; }
			//------------------------------------------------
			$logEmailsConfig = \App\logEmailsConfig::where('portal_id', $portal->id)->first();
			if($logEmailsConfig==null){ return ['result'=>1, 'msg'=>['config'=>['config not found']]]; }
			//------------------------------------------------
			$uLog = \App\KamaUsage::where('apikey', $apikey)->first();
			if($uLog==null){ return ['result'=>1, 'msg'=>['log'=>['Log not found']]]; }
			$retVal['user']['id'          ] = $uLog->user_id;
			$retVal['user']['nickname'    ] = $uLog->user_name;
			$retVal['user']['organization'] = $uLog->org_name;
			
			$retVal['log']['id'  ] = $uLog->signin_id;
			$retVal['log']['date'] = date("M d Y H:i:s", strtotime($uLog->timestamp));
			//------------------------------------------------
			$log = \App\KamaLog::where('signin_id', $uLog->signin_id);
			$retVal['log']['request_response'] = $log->count();
			//------------------------------------------------
			$routePath = "../storage/app/public/autoemail/logs";
			if(!file_exists($routePath)){
				mkdir($routePath, 0777);
			}
			$path = "{$routePath}/{$uLog->org_id}";
			if(!file_exists($path)){
				mkdir($path, 0777);
			}
			//------------------------------------------------
			$attached = "";
			$name = preg_replace(
				'/[^a-z0-9-.]+/',
				'_',
				strtolower($uLog->user_name."-".$uLog->timestamp."-".$uLog->signin_id)
			);

			if($logEmailsConfig->send_format==1){//'csv'
				$csvTEXT =
					view(
						'emails.kamaLogCSV',
						['log_id'=>$uLog->signin_id, 'instructions'=>"", 'orgID'=>$uLog->org_id]
					);
				$csvName = "{$path}/{$name}.csv";
				$attached = "{$name}.csv";
				@unlink($csvName);
				file_put_contents($csvName, $csvTEXT);
				$retVal['log']['format'] = 'CSV';
			}
			if($logEmailsConfig->send_format==2){//'pdf'
				$pdf = \App::make('dompdf.wrapper');
				$pdf->loadHTML(
					view(
						'emails.kamaLog',
						['log_id'=>$uLog->signin_id, 'instructions'=>"", 'orgID'=>$uLog->org_id]
					)
				);
				$pdfName = "{$path}/{$name}.pdf";
				$attached = "{$name}.pdf";
				@unlink($pdfName);
				$pdf->save($pdfName);
				$retVal['log']['format'] = 'PDF';
			}
			//------------------------------------------------
			$mail = new \App\Mail\SendMail;
			$to = explode(";", $logEmailsConfig->emails);
//$to = ['test2@behroozdarabi.com'];
			if($logEmailsConfig->send_format==1 || $logEmailsConfig->send_format==2){
				\Mail::to($to)->
						//cc('test4@behroozdarabi.com')->
						//bcc(['test4@behroozdarabi.com'])->
						send(
							$mail->kamaMyLogWithAttache(
								$logEmailsConfig->subject,
								$uLog->org_id,
								1,
								$uLog,
								$path,
								$attached,
								$logEmailsConfig->body
							)
						);
			}
			if($logEmailsConfig->send_format==3){
				\Mail::to($to)->
						send(
							$mail->kamaLog(
								$logEmailsConfig->subject,
								$uLog->signin_id,
								$logEmailsConfig->body,
								$uLog->org_id
							)
						);
				
				$retVal['log']['format'] = 'HTML';
			}
			//------------------------------------------------
			return ['result'=>0, 'data'=>$retVal];
			//------------------------------------------------
		}catch(\Trowable $ex){ return ['result'=>1, 'msg'=>['exception'=>[$ex->getMessage()]]]; }
	}
	//--------------------------------------------------------
}
//------------------------------------------------------------
//------------------------------------------------------------
