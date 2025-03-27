<?php

namespace App\Http\Controllers\Api\Dashboard\Billing;

use Illuminate\Http\Request;
use App\Controllers;
//------------------------------------------------------------
//------------------------------------------------------------
class BillingController extends \App\Http\Controllers\Controller{
	//--------------------------------------------------------
    public function all($orgID, $sort, $order){
        $data = \App\KamaUsage::groupBy('org_id')->select('org_id as id')->get();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[] ];
        }else{
			for($i=0; $i<count($data); $i++){
				$data[$i]->organizationShortName=
					\App\Organization::where('organizationId', $data[$i]->id)->select('organizationShortName')->
						first()['organizationShortName'];
				$data[$i]->totalLogRow=\App\KamaUsage::where('org_id', $data[$i]->id)->count();
			}
            return ['result'=>0, 'msg'=>'', 'data'=>$data ];
        }
    }
	//--------------------------------------------------------
    public function details($orgID, $sort, $order){
        $data = \App\KamaUsage::where('org_id', $orgID)->groupBy(\DB::raw('DATE_FORMAT(timestamp, "%Y-%m %M")'))
			->select(\DB::raw('DATE_FORMAT(timestamp, "%Y-%m %M") as fullDate'))->get();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[] ];
        }else{
			for($i=0; $i<count($data); $i++){
				$tmp1 =
					\App\Organization::where('organizationId', $orgID)->select('organizationShortName')->
						first()['organizationShortName'];
				$tmp = substr($data[$i]->fullDate, 0, 7);
				$data[$i]->year  = substr($data[$i]->fullDate, 0, 4);
				$data[$i]->month = substr($data[$i]->fullDate, 8);
				$data[$i]->totalLogRow=\App\KamaUsage::where('org_id', $orgID)
					->whereBetween('timestamp', [$tmp."-01", $tmp."-31"])->count();
				$data[$i]->bill = 
					"<span class='orgBill' data-id=".$orgID." data-date=".$tmp." data-month='".$data[$i]->month."' data-year='".$data[$i]->year."' data-org='".$tmp1."'>Invoice</span>".

					"<span class='orgDetail' data-id=".$orgID." data-date=".$tmp." data-month='".$data[$i]->month."' data-year='".$data[$i]->year."' data-org='".$tmp1."' style='margin-left:10px;'>Detail</span>";
			}
            return ['result'=>0, 'msg'=>'', 'data'=>$data ];
        }
    }
	//--------------------------------------------------------
    public function bill($id, $dt){
		return view('bill.bill', ['orgID'=>$id, 'inDate'=>$dt]);
	}
	//--------------------------------------------------------
    public function detail($id, $dt){
		return view('bill.detail', ['orgID'=>$id, 'inDate'=>$dt]);
	}
	//--------------------------------------------------------
	public function getchatlog($chat_id){
		$usage = \App\KamaUsage::find($chat_id);
		if(is_null($usage) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[]]; }
		else{
			$data  = \App\KamaLog::where('apikey', $usage->apikey)->orderBy('msg_id', 'asc')->get();
	
			if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[]]; }
			else{ return ['result'=>0, 'msg'=>'', 'data'=>$data ]; }
		}
	}
	//--------------------------------------------------------
    public function showPageSorted($archive,$s_time,$e_time,$user_id,$org_id, $sort, $order, $perPage, $page ,$searc_email,$searc){
        $count = \App\KamaUsage::logData($archive,$s_time,$e_time,$user_id,$org_id,  '', '',$searc_email,$searc)->count();
        $data = \App\KamaUsage::logData($archive,$s_time,$e_time,$user_id,$org_id, '', '',$searc_email, $searc)
									->orderBy($sort, $order)
									->forPage($page, $perPage)
									->get();


        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            foreach( $data as $key=>$tmp ){
                $tmpKamaLog = new \App\KamaLog;
                $dataKamaLogCNT = $tmpKamaLog
                    ->select('apikey')
                    ->where('apikey', $tmp->apikey)
                    ->count();
                $tmp->logcount=$dataKamaLogCNT;
                $max_logtime=0;
                if($dataKamaLogCNT>0){
                    $dataKamaLogTime = $tmpKamaLog
                        ->select('timestamp')
                        ->where('apikey', $tmp->apikey)
                        ->orderBy('msg_id', 'desc')
                        ->get();
                        $max_logtime=floor((strtotime($dataKamaLogTime[0]->timestamp)-strtotime($tmp->timestamp))%86400%60);

                }
                $tmp->log_s=$max_logtime.'sec.';
            }
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
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
			if($tmp!=null){ if($tmp->AutoOnOff==1 && ($tmp->AutoEmail!='' && $tmp->AutoEmail!=null)){ $cc=true; } }
			
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
		}catch(\ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
}
//------------------------------------------------------------
//------------------------------------------------------------
