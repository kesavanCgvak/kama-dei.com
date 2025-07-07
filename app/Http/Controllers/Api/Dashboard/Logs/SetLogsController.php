<?php
namespace App\Http\Controllers\Api\Dashboard\Logs;

use Illuminate\Http\Request;
use App\Controllers;
//------------------------------------------------------------
//------------------------------------------------------------
class SetLogsController extends \App\Http\Controllers\Controller{
	//--------------------------------------------------------
    private function create_kama_usage($apikey, $ip){
		//------------------------------------------------
		$kamaUsage = \App\KamaUsage::where('apikey', $apikey)->first();
		if($kamaUsage!=null){ return $kamaUsage->signin_id; }
		//------------------------------------------------
		$signin_id = 0;
		//------------------------------------------------
		$api_key = \App\ApiKeyManager::where('api_key', $apikey)->first();
		if($api_key==null){ throw new \Exception("Invalid apikey"); }
		$apikey = $api_key->api_key;
		//------------------------------------------------
		$user = \App\User::where('id', $api_key->userID)->first();
		if($user==null){ throw new \Exception("Invalid user"); }
		$user_id = $user->id;
		$email   = $user->email;
		//------------------------------------------------
		$organization = \App\Organization::where('organizationId', $api_key->orgID)->first();
		if($organization==null){ throw new \Exception("Invalid organization"); }
		$org_name = $organization->organizationShortName;
		$org_id   = $organization->organizationId;
		//------------------------------------------------
		$consumerUser = \App\ConsumerUserPersonality::where('consumerUserId', $user_id)->first();
		if($consumerUser==null){ throw new \Exception("Invalid consumer user"); }
		$user_name = $consumerUser->nickname;
		//------------------------------------------------
		$kamaUsage = new \App\KamaUsage;
		$kamaUsage->apikey    = $apikey;
		$kamaUsage->ip        = $ip;
		$kamaUsage->email     = $email;
		$kamaUsage->user_id   = $user_id;
		$kamaUsage->org_id    = $org_id;
		$kamaUsage->user_name = $user_name;
		$kamaUsage->org_name  = $org_name;
		$kamaUsage->timestamp = date("Y-m-d H:i:s");
		$kamaUsage->memo      = $user_name;
		$kamaUsage->save();
		return $kamaUsage->signin_id;
	}
	//--------------------------------------------------------
    public function setLog(Request $req){
        try{
			//------------------------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'apikey'    => 'required',
						'signin_id' => 'required',

						"ip"        => "",
						
						'sender'    => 'required',
						'raw_msg'   => 'required',
						'msg'       => 'required',
						
						'Generative_response' => "",
						'GR_Enduser_delivery' => "",
						'Model_used'          => "",
						'Collection_used'     => ""
					],
					[
						//"required" => "The :attribute not defined"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			//------------------------------------------------
			$data     = $req->all();
			$signinID = $data['signin_id'];
			$ip       = ((isset($data['ip'])) ?$data["ip"] :"");
			//------------------------------------------------
			if($data['signin_id']==0){ $signinID = self::create_kama_usage($data['apikey'], $ip); }
			else{
				$signinID = $data['signin_id'];
			}
			$kamaUsage = \App\KamaUsage::where('signin_id', $signinID)->first();
			if($kamaUsage==null){ throw new \Exception("Invalid signin_id"); }
			if($kamaUsage->apikey!=$data['apikey']){ throw new \Exception("Invalid signin_id"); }
			//------------------------------------------------
			$kamaLog = new \App\KamaLog;
			$kamaLog->signin_id = $kamaUsage->signin_id;
			$kamaLog->apikey    = $kamaUsage->apikey;
			$kamaLog->timestamp = date("Y-m-d H:i:s");
			$kamaLog->sender    = $data['sender'];
			$kamaLog->raw_msg   = $data['raw_msg'];
			$kamaLog->msg       = $data['msg'];

			$kamaLog->Generative_response = ((isset($data['Generative_response'])) ?$data['Generative_response'] :null);
			$kamaLog->GR_Enduser_delivery = ((isset($data['GR_Enduser_delivery'])) ?$data['GR_Enduser_delivery'] :null);
			$kamaLog->Model_used          = ((isset($data['Model_used'         ])) ?$data['Model_used'         ] :null);
			$kamaLog->Collection_used     = ((isset($data['Collection_used'    ])) ?$data['Collection_used'    ] :null);
			
			$kamaLog->save();
			//------------------------------------------------
			return['result'=>0, 'signin_id'=>$kamaLog->signin_id, 'msg_id'=>$kamaLog->msg_id];
			//------------------------------------------------
		}catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//--------------------------------------------------------
    public function getLog($signin_id){
        try{
			$retVal = [];
			//------------------------------------------------
			$kamaLog = \App\KamaLog::where('signin_id', $signin_id)
				->orderBy('msg_id', 'asc')
				->select(
					"msg_id",
					"sender",
					"raw_msg",
					"msg",
					'Generative_response',
					'GR_Enduser_delivery',
					'Model_used',
					'Collection_used'

				)
				->get();
			//------------------------------------------------
			if(!$kamaLog->isEmpty()){
				foreach($kamaLog as $log){
					$retVal[] = [
						"id"=>$log->msg_id,
						"sender"=>$log->sender,
						"rawMsg"=>$log->raw_msg,
						"msg"=>$log->msg,
						'Generative_response'=>$log->Generative_response,
						'GR_Enduser_delivery'=>$log->GR_Enduser_delivery,
						'Model_used'=>$log->Model_used,
						'Collection_used'=>$log->Collection_used
					];
				}
			}
			//------------------------------------------------
			return
				[
					'result' => 0,
					'count'  => $kamaLog->count(),
					"data"   => $retVal
				];
			//------------------------------------------------
		}catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//--------------------------------------------------------
	public function setFeedback(Request $req){
        try{
			//------------------------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						//'apikey'     => 'required',
						'messageId' => 'required',
						
						'comment'    => 'required',
						'feedback'   => 'required',
						'is_general' => '',
						
						'Generative_response' => "",
						'GR_Enduser_delivery' => "",
						'Rating_for_GR'       => "",
						'Notes_for_GR'        => "",
						'Model_used'          => "",
						'Collection_used'     => ""
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $req->all();
			//------------------------------------------------
			//$apikey = \App\ApiKeyManager::where('api_key', $data['api_key'])->first();
			//if($apikey==null){ throw new \Exception("Invalid apikey"); }
			//------------------------------------------------
			$feedBack = \App\Feedback::where('message_id', $data['messageId'])->first();
			if($feedBack!=null){ throw new \Exception("Duplicated message id"); }
			//------------------------------------------------
			$is_general = 0;
			if(isset($data['is_general'])){
				if($data['is_general']!=1){ $is_general=0; }
				else{ $is_general=1; }
			}
			$data['comment'] = substr($data['comment'], 0, 1024);
			
			$feedBack = new \App\Feedback;
			$feedBack->message_id = $data['messageId'];
			$feedBack->created_on = date("Y-m-d H:i:s");
			$feedBack->comment    = $data['comment'];
			$feedBack->feedback   = (($data['feedback'  ]=="1") ?1 :0);
			$feedBack->is_general = $is_general;
			
			$GR_Enduser_delivery = (
				(isset($data['GR_Enduser_delivery']))
						?(($data['GR_Enduser_delivery']=="1") ?1 :0)
						:null
			);
			$feedBack->Generative_response = (isset($data['Generative_response']) ?$data['Generative_response'] :null);
			$feedBack->GR_Enduser_delivery = $GR_Enduser_delivery;
			$feedBack->Rating_for_GR       = (isset($data['Rating_for_GR'      ]) ?$data['Rating_for_GR'      ] :null);
			$feedBack->Notes_for_GR        = (isset($data['Notes_for_GR'       ]) ?$data['Notes_for_GR'       ] :null);
			$feedBack->Model_used          = (isset($data['Model_used'         ]) ?$data['Model_used'         ] :null);
			$feedBack->Collection_used     = (isset($data['Collection_used'    ]) ?$data['Collection_used'    ] :null);
			
			$feedBack->save();
			//------------------------------------------------
			return['result'=>0, "msg"=>"ok", 'id'=>$feedBack->msg_id];
			//------------------------------------------------
		}catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//--------------------------------------------------------
}
//------------------------------------------------------------
//------------------------------------------------------------
