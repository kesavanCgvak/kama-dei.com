<?php
namespace App\Logs;

class KamaLogClass{
	//---------------------------------------------------------------
	
	//---------------------------------------------------------------
	public static function addUsage($apikey, $ip, $user_id, $org_id, $memo, $archive=0, $order=0){
		//-----------------------------------------------------------
		$apikey  = trim($apikey);
		$ip      = trim($ip);
		$user_id = trim($user_id);
		$org_id  = trim($org_id);
		$memo    = trim($memo);
		$archive = trim($archive);
		$order   = trim($order);
		//-----------------------------------------------------------
		if($apikey==""){ return -6; }//invalid apikey
		if($ip    ==""){ return -1; }//invalid IP
		if($memo  ==""){ return -2; }//invalid Memo
		//-----------------------------------------------------------
		$user = \App\User::where('id', $user_id)->where('orgID', $org_id)->first();
		if($user==null){ return -3; }//invalid UserId
		else{
			//-------------------------------------------------------
			$email = $user->email;
			//-------------------------------------------------------
			$consumerUser = \App\ConsumerUserPersonality::where('consumerUserId', $user_id)->where('organizationId', $org_id)->first();
			if($consumerUser==null){ $user_name = $user->userName; }
			else{ $user_name = $consumerUser->nickname; }
			//-------------------------------------------------------
		}
		//-----------------------------------------------------------
		$org = \App\Organization::find($org_id);
		if($org==null){ return -4; }//invalid OrgId
		else{ $org_name = $org->organizationShortName; }
		//-----------------------------------------------------------
		$log_exception_user_ids = env('log_exception_user_ids', "0");
		$log_exception_user_ids = explode(",", $log_exception_user_ids);
		if(in_array($user_id, $log_exception_user_ids)){ return 0; }
		
		$log_exception_apikey_letter = env("log_exception_apikey_letter", '3,t');
		$log_exception_apikey_letter = explode(",", $log_exception_apikey_letter);
		if(in_array(substr($apikey,0,1), $log_exception_apikey_letter)){ return 0; }
		//-----------------------------------------------------------
		$tmp = \App\KamaUsage::where('apikey', $apikey)->orderBy('signin_id', 'desc')->first();
		if(!is_null($tmp)){ return $tmp->signin_id; }
		//-----------------------------------------------------------
		$usage = new \App\KamaUsage();
		$usage->apikey    = $apikey;
		$usage->ip        = $ip;
		$usage->email     = $email;
		$usage->user_id   = $user_id;
		$usage->org_id    = $org_id;
		$usage->user_name = $user_name;
		$usage->org_name  = $org_name;
		$usage->timestamp = date("Y-m-d H:i:s");
		$usage->memo      = $memo;
		$usage->archive   = $archive;
		$usage->order     = $order;
		if($usage->save()){ return $usage->signin_id; }
		else{ return -5; }//can't add log record
	}
	//---------------------------------------------------------------

	//---------------------------------------------------------------
	public static function addLog($apikey, $user_id, $sender, $raw_msg, $msg){
		//-----------------------------------------------------------
		$apikey  = trim($apikey);
		$user_id = trim($user_id);
		$sender  = trim($sender);
		$raw_msg = trim($raw_msg);
		$msg     = trim($msg);
		//-----------------------------------------------------------
		if($sender ==""){ return -1; }//invalid sender
		if($raw_msg==""){ return -2; }//invalid raw_msg
		if($msg    ==""){ return -3; }//invalid msg
		//-----------------------------------------------------------
		//-----------------------------------------------------------
		$log_exception_user_ids = env('log_exception_user_ids', "0");
		$log_exception_user_ids = explode(",", $log_exception_user_ids);
		if(in_array($user_id, $log_exception_user_ids)){ return 0; }
		
		$log_exception_apikey_letter = env("log_exception_apikey_letter", '3,t');
		$log_exception_apikey_letter = explode(",", $log_exception_apikey_letter);
		if(in_array(substr($apikey,0,1), $log_exception_apikey_letter)){ return 0; }
		//-----------------------------------------------------------
		$tmp = \App\KamaUsage::where('apikey', $apikey)->where('user_id', $user_id)->orderBy('signin_id', 'desc')->first();
		if($tmp==null){ return -4; }//invalid key
		//-----------------------------------------------------------
		$log = new \App\KamaLog();
		$log->apikey    = $apikey;
		$log->signin_id = $tmp->signin_id;
		$log->timestamp = date("Y-m-d H:i:s");
		$log->sender    = $sender;
		$log->raw_msg   = $raw_msg;
		$log->msg       = $msg;
		if($log->save()){ return $log->msg_id; }
		else{ return -5; }//can't add log record
	}
	//---------------------------------------------------------------

	//---------------------------------------------------------------
}