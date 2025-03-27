<?php

namespace App\Http\Controllers\Api\Dashboard\Charts;

use Illuminate\Http\Request;
//use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Config;

class ChartsController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function chart1Data($flag, $orgID, Request $req){
		//-----------------------------------
		try{
			//-------------------------------
			$eTime = time();
			switch($flag){
				case 1: $sTime = strtotime("+1 month", strtotime("-1 year")); break;
				case 2: $sTime = strtotime("-1 month")+(3600*24); break;
				case 3: $sTime = strtotime("-6 day"  ); break;
				case 4: $sTime = strtotime("-1 day"  ); break;
			}
			//-------------------------------
			$rows = \App\KamaUsage::logData(0, $sTime, $eTime, 0, $orgID, "", "", 0, 0)->get();
			if($rows->isEmpty()){ return ['result'=>1, 'data'=>[ 0, 0, 0, 0 ], 'msg'=>"record not fount"]; }
			//-------------------------------
			$pieA = 0;
			$pieB = 0;
			$pieC = 0;
			$pieD = 0;
            foreach( $rows as $key=>$tmp ){
                $tmpKamaLog = new \App\KamaLog;
                $dataKamaLogCNT = $tmpKamaLog
                    ->select('apikey')
                    ->where('apikey', $tmp->apikey)
                    ->count();
                $max_logtime=0;
                if($dataKamaLogCNT>0){
                    $dataKamaLogTime = $tmpKamaLog
                        ->select('timestamp')
                        ->where('apikey', $tmp->apikey)
                        ->orderBy('msg_id', 'desc')
                        ->get();
                        $max_logtime=floor((strtotime($dataKamaLogTime[0]->timestamp)-strtotime($tmp->timestamp))%86400%60);

                }
				if($max_logtime<5){ $pieA++; }
				else{
					if($max_logtime<31){ $pieB++; }
					else{
						if($max_logtime<61){ $pieC++; }
						else{ $pieD++; }
					}
				}
				
            }
			//-------------------------------
			return ['result'=>0, 'data'=>[ $pieA, $pieB, $pieC, $pieD ]];
			//-------------------------------
		}
		//-----------------------------------
		catch(\Throwable $e){ return ['result'=>1, 'data'=>[ 0, 0, 0, 0 ], 'msg'=>$e->getMessage()]; }
		//-----------------------------------
	}
	//---------------------------------------
	public function chart2Data($flag, $orgID, Request $req){
		//-----------------------------------
		try{
			//-------------------------------
			$eTime = time()+1;
			switch($flag){
				case 1:{
					//-----------------------
					$data0  = [];
					$data1  = [];
	                $labels = [];
					//-----------------------
					$sTime = strtotime("+1 month", strtotime("-1 year"));
					$tTime = $sTime;
					$i=0;
					while($tTime<$eTime){
						$labels[] = date("M Y",$tTime);
						$tTime = strtotime("+1 month", $tTime);
						$data0[$i] = 0;
						$data1[$i] = 0;
						$i++;
					}
					//-----------------------
					$rows = \App\KamaUsage::logData(0, $sTime, $eTime, 0, $orgID, "", "", 0, 0)->orderBy("timestamp", "asc")->get();
					if($rows->isEmpty()){ return ['result'=>0, 'data'=>[ $data0, $data1 ], 'labels'=>$labels]; }
					foreach( $rows as $key=>$tmp ){
						$m = date("M Y", strtotime($tmp->timestamp));
						if(!in_array($m, $labels)){
							$labels[] = $m;
							$data0[count($labels)-1] = 0;
							$data1[count($labels)-1] = 0;
						}
						$key = array_search($m, $labels);
						$data0[$key]++;
					}
					//-----------------------
					foreach( $rows as $key=>$tmp ){
						$dataKamaLogCNT = \App\KamaLog::select('apikey', 'raw_msg')
							->where('apikey', $tmp->apikey)
							->where('sender', "<>", 'user')
							->get()
							->filter(function($row){
								try{ if(str_contains(\Crypt::decryptString($row->raw_msg), "radiobutton")){ return $row; } }
								catch(\Exception $ex){ if(str_contains($row->raw_msg, "radiobutton")){ return $row; } }
							})
							//->where('raw_msg', "like", "%radiobutton%")
							->count();
						if($dataKamaLogCNT>0){ 
							$m = date("M Y", strtotime($tmp->timestamp));
							if(!in_array($m, $labels)){
								$labels[] = $m;
								$data0[count($labels)-1] = 0;
								$data1[count($labels)-1] = 0;
							}
							$key = array_search($m, $labels);
							$data1[$key]++;
						}
					}
					//-----------------------
					return ['result'=>0, 'data'=>[ $data0, $data1 ], 'labels'=>$labels];
					//-----------------------
				}
				case 2:{
					//-----------------------
					$data0  = [];
					$data1  = [];
	                $labels = [];
					//-----------------------
/*
$file = fopen("/var/www/html/login.kama-dei.com/public/aa/0-chart.2.3.txt", "w+");
fwrite($file, $labels[$i]."\r\n");
fclose($file);
*/
					$sTime = strtotime("-1 month")+(3600*24);
					$tTime = $sTime;
					$i=0;
					while($tTime<$eTime){
						$labels[] = date("M d",$tTime);
						$tTime+=(3600*24);
						$data0[$i] = 0;
						$data1[$i] = 0;
						$i++;
					}
					//-----------------------
					$rows = \App\KamaUsage::logData(0, $sTime, $eTime, 0, $orgID, "", "", 0, 0)->orderBy("timestamp", "asc")->get();
					if($rows->isEmpty()){ return ['result'=>1, 'data'=>[ [], [] ], 'labels'=>$labels, 'msg'=>"record not fount"]; }
					foreach( $rows as $tmp ){
						$N = date("M d", strtotime($tmp->timestamp));
						$key = array_search($N, $labels);
						if(isset($data0[$key]))
							{ $data0[$key]++; }
							else{ $data0[$key] = 1; $data1[$key] = 0; }
						if(!isset($data1[$key])){ $data1[$key] = 0; }
					}
					//-----------------------
					foreach( $rows as $tmp ){
						$dataKamaLogCNT = \App\KamaLog::select('apikey', 'raw_msg')
							->where('apikey', $tmp->apikey)
							->where('sender', "<>", 'user')
							->get()
							->filter(function($row){
								try{ if(str_contains(\Crypt::decryptString($row->raw_msg), "radiobutton")){ return $row; } }
								catch(\Exception $ex){ if(str_contains($row->raw_msg, "radiobutton")){ return $row; } }
							})
							//->where('raw_msg', "like", "%radiobutton%")
							->count();
						if($dataKamaLogCNT>0){ 
							$N = date("M d", strtotime($tmp->timestamp));
							$key = array_search($N, $labels);
							if(isset($data1[$key]))
								{ $data1[$key]++; }
								else{ $data0[$key] = 0; $data1[$key] = 1; }
							if(!isset($data0[$key])){ $data0[$key] = 0; }
						}
					}
					//-----------------------
					return ['result'=>0, 'data'=>[ $data0, $data1 ], 'labels'=>$labels, $data0];
					//-----------------------
				}
				case 3:{
					//-----------------------
					$data0  = [0, 0, 0, 0, 0, 0, 0];
					$data1  = [0, 0, 0, 0, 0, 0, 0];
	                $labels = [
						date("D", strtotime("-6 day")),
						date("D", strtotime("-5 day")),
						date("D", strtotime("-4 day")),
						date("D", strtotime("-3 day")),
						date("D", strtotime("-2 day")),
						date("D", strtotime("-1 day")),
						date("D", strtotime("-0 day"))
					];
					//-----------------------
					$sTime = strtotime("-6 day");
					//-----------------------
					$rows = \App\KamaUsage::logData(0, $sTime, $eTime, 0, $orgID, "", "", 0, 0)->get();
					if($rows->isEmpty()){ return ['result'=>1, 'data'=>[ $data0, $data1 ], 'labels'=>$labels]; }
					foreach( $rows as $tmp ){
						$N = date("D", strtotime($tmp->timestamp));
						$key = array_search($N, $labels);
						$data0[$key]++;
					}
					//-----------------------
					foreach( $rows as $tmp ){
						$dataKamaLogCNT = \App\KamaLog::select('apikey', 'raw_msg')
							->where('apikey', $tmp->apikey)
							->where('sender', "<>", 'user')
							->get()
							->filter(function($row){
								try{ if(str_contains(\Crypt::decryptString($row->raw_msg), "radiobutton")){ return $row; } }
								catch(\Throwable $ex){ if(str_contains($row->raw_msg, "radiobutton")){ return $row; } }
							})
							//->where('raw_msg', "like", "%radiobutton%")
							->count();
						if($dataKamaLogCNT>0){
							$N = date("D", strtotime($tmp->timestamp));
							$key = array_search($N, $labels);
							$data1[$key]++;
						}
					}
					//-----------------------
					return ['result'=>0, 'data'=>[ $data0, $data1 ], 'labels'=>$labels];
					//-----------------------
				}
				case 4:{
					//-----------------------
					$data0  = [];
					$data1  = [];
	                $labels = [];
					for($i=23 ; $i>=0; $i--){ $labels[]=date("H",strtotime("-{$i} hour")); }
					for($i=0 ; $i<24; $i++){ 
						$data0[$i] = 0;
						$data1[$i] = 0;
					}
					//-----------------------
					$sTime = strtotime("-1 day");
					//-----------------------
					$rows = \App\KamaUsage::logData(0, $sTime, $eTime, 0, $orgID, "", "", 0, 0)->orderBy("timestamp", "asc")->get();
					if($rows->isEmpty()){ return ['result'=>1, 'data'=>[ $data0, $data1 ], 'labels'=>$labels]; }
					foreach( $rows as $key=>$tmp ){
						$h = date("H", strtotime($tmp->timestamp));
						if(!in_array($h, $labels)){
							$labels[] = $h;
							$data0[count($labels)-1] = 0;
							$data1[count($labels)-1] = 0;
						}
						$key = array_search($h, $labels);
						$data0[$key]++;
					}
					//-----------------------
					foreach( $rows as $key=>$tmp ){
						$dataKamaLogCNT = \App\KamaLog::select('apikey', 'raw_msg')
							->where('apikey', $tmp->apikey)
							->where('sender', "<>", 'user')
							->get()
							->filter(function($row){
								try{ if(str_contains(\Crypt::decryptString($row->raw_msg), "radiobutton")){ return $row; } }
								catch(\Exception $ex){ if(str_contains($row->raw_msg, "radiobutton")){ return $row; } }
							})
							//->where('raw_msg', "like", "%radiobutton%")
							->count();
						if($dataKamaLogCNT>0){ 
							$h = date("H", strtotime($tmp->timestamp));
							if(!in_array($h, $labels)){
								$labels[] = $h;
								$data0[count($labels)-1] = 0;
								$data1[count($labels)-1] = 0;
							}
							$key = array_search($h, $labels);
							$data1[$key]++;
						}
					}
					//-----------------------
					return ['result'=>0, 'data'=>[ $data0, $data1 ], 'labels'=>$labels];
					//-----------------------
				}
			}
			//-------------------------------
		}
		//-----------------------------------
		catch(\Throwable $e){ return ['result'=>1, 'data'=>[ [], [] ], 'labels'=>[], 'msg'=>$e->getMessage()]; }
		//-----------------------------------
	}
	//---------------------------------------
	public function chart3Data($flag, $orgID, Request $req){
		//-----------------------------------
		ini_set('memory_limit', -1);
		try{
			$data0 = 0;
			$data1 = 0;
			//-------------------------------
			$eTime = time();
			switch($flag){
				case 1: $sTime = strtotime("+1 month", strtotime("-1 year")); break;
				case 2: $sTime = strtotime("-1 month")+(3600*24); break;
				case 3: $sTime = strtotime("-6 day"  ); break;
				case 4: $sTime = strtotime("-1 day"  ); break;
			}
			//-------------------------------
			$data0 = \App\KamaLog::select('kama_log.apikey', 'raw_msg')
				->where('sender', '<>', 'user')
				->whereBetween('kama_log.timestamp',[date("Y-m-d H:i:s", $sTime), date("Y-m-d H:i:s", $eTime)]);

			$data1 = \App\KamaLog::select('kama_log.apikey')
				->where('sender', '<>', 'user')
				->whereBetween('kama_log.timestamp',[date("Y-m-d H:i:s", $sTime), date("Y-m-d H:i:s", $eTime)]);
			if($orgID!=0){
				$data0 = $data0->where('kama_usage.org_id', $orgID)
					->leftJoin('kama_usage', 'kama_log.apikey', '=', 'kama_usage.apikey');
				$data1 = $data1->where('kama_usage.org_id', $orgID)
					->leftJoin('kama_usage', 'kama_log.apikey', '=', 'kama_usage.apikey');
				
			}
			$data0 = $data0->get()
				->filter(function($record){
					$raw_msg = json_decode($record->raw_msg,1);
					if(isset($raw_msg['response']['state'])){
						if($raw_msg['response']['state']==999 || $raw_msg['response']['state']==998){ return $record; }
					}
					/*
					if(strpos(strtolower($record->raw_msg), strtolower("did not detect a question"))!==false){ return $record; }
					if(strpos(strtolower($record->raw_msg ), strtolower("know answers"))!==false){ return $record; }
					if(strpos(strtolower($record->raw_msg ), strtolower("Thank you for sharing that"))!==false){ return $record; }
					*/
				});
			$data0  = count($data0);
			$data1  = $data1->count();
			$data1 -= $data0;
			//-------------------------------
			return ['result'=>0, 'data'=>[ $data0, $data1 ]];
			//-------------------------------
		}
		//-----------------------------------
		catch(\Throwable $e){ return ['result'=>1, 'data'=>[ 0, 0 ], 'msg'=>$e->getMessage()]; }
		//-----------------------------------
	}
	//---------------------------------------
	public function seeListData($flag, $orgID, Request $req){
		ini_set('memory_limit', -1);
		try{
			$eTime = time();
			switch($flag){
				case 1: $sTime = strtotime("+1 month", strtotime("-1 year")); break;
				case 2: $sTime = strtotime("-1 month")+(3600*24); break;
				case 3: $sTime = strtotime("-6 day"  ); break;
				case 4: $sTime = strtotime("-1 day"  ); break;
			}
			//-------------------------------
			$data = \App\KamaLog::select(
					'kama_log.msg_id as msgID',
					'raw_msg',
					'kama_log.apikey',

					\DB::raw("DATE_FORMAT(kama_log.timestamp, '%b %e, %Y %l:%i:%s %p') as startedAt"),
					\DB::raw("SUBSTRING(kama_log.apikey,1,6) as portalName")
				)
				->where('sender', '<>', 'user')
				->whereBetween('kama_log.timestamp',[date("Y-m-d H:i:s", $sTime), date("Y-m-d H:i:s", $eTime)]);
			if($orgID!=0){
				$data = $data->where('org_id', $orgID)
				->leftJoin('kama_usage', 'kama_log.apikey', '=', 'kama_usage.apikey');
			}
			$data = $data->orderBy('kama_log.timestamp', 'desc')
				->get()
				->filter(function($record){
					$raw_msg = json_decode($record->raw_msg,1);
					if(isset($raw_msg['response']['state'])){
						if($raw_msg['response']['state']==999 || $raw_msg['response']['state']==998){ return $record; }
					}
					/*
					if(strpos(strtolower($record->raw_msg), strtolower("did not detect a question"))!==false){ return $record; }
					if(strpos(strtolower($record->raw_msg ), strtolower("know answers"))!==false){ return $record; }
					if(strpos(strtolower($record->raw_msg ), strtolower("Thank you for sharing that"))!==false){ return $record; }
					*/
				})
				->map(function($row){
					//---------------------------
					$kama_usage = \App\KamaUsage::where('apikey', $row->apikey)->first();
					$row->chatID = $kama_usage->signin_id;
					$row->userID = $kama_usage->user_id;
					$row->orgID  = $kama_usage->org_id;
					//---------------------------
					$personality = \App\ConsumerUserPersonality::select('personality.personalityName')
						->leftJoin('personality', 'consumer_user_personality.personalityId', '=', 'personality.personalityId')
						->where('consumerUserId', $row->userID)
						->where('organizationId',$row->orgID)
						->first();
					if($personality!=null){ $row->user = $personality->personalityName; }
					else{$row->user = "-"; }
					//---------------------------
					$persona = \App\ConsumerUserPersonality::select('personality.parentPersonaId')
						->leftJoin('personality', 'consumer_user_personality.personalityId', '=', 'personality.personalityId')
						->where('consumerUserId', $row->userID)
						->where('organizationId',$row->orgID)
						->first();
					if($persona!=null){
						$persona = \App\Personality::select('personality.personalityName')
							->where('personalityId', $persona->parentPersonaId)
							->first();
						if($persona!=null){ $row->persona = $persona->personalityName; }
						else{$row->persona = "-"; }
					}
					else{$row->persona = "-"; }
					//---------------------------
					$failedUtterance = \App\KamaLog::find($row->msgID-1);
					if($failedUtterance!=null){ 
						try{ $row->failedUtterance = json_decode($failedUtterance->msg)->request->utterance; }
						catch(\Throwable $e){  $row->failedUtterance = $failedUtterance->msg; }
					}
					else{$row->failedUtterance = "-"; }
					//---------------------------
					$portal = \App\Portal::select('name')
						->where('portal_number', substr($row->portalName, 0, 1))
						->where('code', substr($row->portalName, 1, 5))
						->where('organization_id',$row->orgID)
						->first();
					if($portal!=null){ $row->portalName = $portal->name; }
					else{ $row->portalName = "Default"; }
					//---------------------------
					unset($row->raw_msg);
					unset($row->apikey);
					return $row;
				});
			//-------------------------------
			if($data->isEmpty()){ return ['result'=>0, 'data'=>[], "sTime"=>$sTime, "eTime"=>$eTime]; }
			$retData = [];
			//-------------------------------
			foreach($data as $key=>$tmp){ $retData[] = $tmp; }
			//-------------------------------
			return ['result'=>0, 'data'=>$retData, "sTime"=>$sTime, "eTime"=>$eTime];
			//-------------------------------
		}catch(\Throwable $e){ return ['result'=>1, 'data'=>[], 'msg'=>$e->getMessage()]; }
		//-----------------------------------
	}
	//---------------------------------------
	public function csvData($flag, $orgID, Request $req){
		try{
			if($orgID!=0){
				$orgData = \App\Organization::find($orgID);
				$orgName = $orgData->organizationShortName;
			}else{ $orgName = "All"; }
			
			$flagName = "-";
			switch($flag){
				case 1: $flagName="Yearly"; break;
				case 2: $flagName="Monthly"; break;
				case 3: $flagName="Weekly"; break;
				case 4: $flagName="Last 24 hours"; break;
			}
			
			$csvName = 
				"csv/".
				str_replace(" ", "" ,strtolower("FAQ_Performance")).
				".".
				str_replace(" ", "" ,strtolower($orgName)).
				".".
				str_replace(" ", "" ,strtolower($flagName)).
				".".
				date("YmdHis").
				".csv";
			//-------------------------------------------------------
			$csv = fopen($csvName, "w+");
			fwrite($csv, "FAQ Performance"."\r\n");
			fwrite($csv, "Organization: {$orgName}"."\r\n");
			fwrite($csv, "{$flagName}"."\r\n");
/*
			fwrite($csv, "The number of Q&amp;A pairs that were completed with the successful delivery of information versus the number where no answer/information was provided over the selected time period."."\r\n");
*/
			$data = $this->seeListData($flag, $orgID, $req);
			if($data['result']==0){
				$sTime = date("Y-m-d H:i:s", $data['sTime']);
				$eTime = date("Y-m-d H:i:s", $data['eTime']);
				fwrite($csv, "From: {$sTime},To: {$eTime}"."\r\n");
				fwrite($csv, "Portal Name,Persona,User,Started at,Failed Utterance"."\r\n");
				foreach($data['data'] as $row){
					$row['portalName'     ] = str_replace(",", " ", $row['portalName'     ]);
					$row['persona'        ] = str_replace(",", " ", $row['persona'        ]);
					$row['user'           ] = str_replace(",", " ", $row['user'           ]);
					$row['startedAt'      ] = str_replace(",", " ", $row['startedAt'      ]);
					$row['failedUtterance'] = str_replace(",", " ", $row['failedUtterance']);

					fwrite($csv, "{$row['portalName']},{$row['persona']},{$row['user']},{$row['startedAt']},{$row['failedUtterance']}"."\r\n");
				}
				fclose($csv);
			}else{
				return $data;
				fclose($csv);
			}
			return ['result'=>0, "msg"=>"", "file"=>$csvName];
		}catch(\Throwable $e){ return ['result'=>1, 'data'=>[], 'msg'=>$e->getMessage()]; }
		//-----------------------------------
	}
	//---------------------------------------
	public function chart4Data($flag, $orgID, Request $req){
		//-----------------------------------
		ini_set('memory_limit', -1);
		try{
			//-------------------------------
			$eTime = time();
			switch($flag){
				case 1: $sTime = strtotime("+1 month", strtotime("-1 year")); break;
				case 2: $sTime = strtotime("-1 month")+(3600*24); break;
				case 3: $sTime = strtotime("-6 day"  ); break;
				case 4: $sTime = strtotime("-1 day"  ); break;
			}
			//-------------------------------
			$dataA = \App\KamaLog::select('kama_log.apikey', 'raw_msg')
				->where('sender', '<>', 'user')
				->whereBetween('kama_log.timestamp',[date("Y-m-d H:i:s", $sTime), date("Y-m-d H:i:s", $eTime)]);
			$dataL = \App\KamaLog::select('kama_log.apikey', 'raw_msg')
				->where('sender', '<>', 'user')
				->whereBetween('kama_log.timestamp',[date("Y-m-d H:i:s", $sTime), date("Y-m-d H:i:s", $eTime)]);
			$dataD = \App\KamaLog::select('kama_log.apikey', 'raw_msg')
				->where('sender', '<>', 'user')
				->whereBetween('kama_log.timestamp',[date("Y-m-d H:i:s", $sTime), date("Y-m-d H:i:s", $eTime)]);
			if($orgID!=0){
				$dataA = $dataA->where('kama_usage.org_id', $orgID)
					->leftJoin('kama_usage', 'kama_log.apikey', '=', 'kama_usage.apikey');
				$dataL = $dataL->where('kama_usage.org_id', $orgID)
					->leftJoin('kama_usage', 'kama_log.apikey', '=', 'kama_usage.apikey');
				$dataD = $dataD->where('kama_usage.org_id', $orgID)
					->leftJoin('kama_usage', 'kama_log.apikey', '=', 'kama_usage.apikey');
			}

			$dataL = $dataL->leftJoin('feedback', 'kama_log.msg_id', '=', 'feedback.message_id')->where('feedback',1);
			$dataD = $dataD->leftJoin('feedback', 'kama_log.msg_id', '=', 'feedback.message_id')->where('feedback',0);
			
			$dataA = $dataA->count();
			$dataD = $dataD->count();
			$dataL = $dataL->count();
			//-------------------------------
			return ['result'=>0, 'data'=>[ $dataA-($dataL+$dataD), $dataL, $dataD ]];
			//-------------------------------
		}
		//-----------------------------------
		catch(\Throwable $e){ return ['result'=>1, 'data'=>[ 0, 0 ], 'msg'=>$e->getMessage()]; }
		//-----------------------------------
	}
	//---------------------------------------
}