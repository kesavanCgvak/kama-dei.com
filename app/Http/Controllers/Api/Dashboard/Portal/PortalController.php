<?php
namespace App\Http\Controllers\Api\Dashboard\Portal;
//-------------------------------------------
use Illuminate\Http\Request;
use App\Controllers;
//-------------------------------------------
class PortalController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function viewTable($orgID, $sort, $order, $perpage, $page, $ownerId, $field='', $value=''){
		$data  = \App\Portal::myPortal($orgID, $ownerId, $field, $value)
			->orderBy($sort, $order);
		$cnt = $data->count();
		$retVal = [];
		$data = $data->get()->forPage($page, $perpage);
		foreach($data as $row){ $retVal[]=$row; }
		return ['result'=>0, 'msg'=>'', 'total'=>$cnt, 'data'=>$retVal];
	}
	//---------------------------------------
	public function viewPersonality($orgID){
		try{
			$data = \App\Personality::where('parentPersonaId', 0)
									->where('ownerId', $orgID)
									->select(
										"personality.personalityId as id",
										"personality.personalityName as name"
									)
									->orderBy("personality.personalityName", "asc")
									->get();
			
			$default = \App\OrganizationPersonality::where('organizationId', $orgID)
							->leftJoin('personality as PRS', 'organization_personality.personalityId', '=', 'PRS.personalityId')
							->where('organization_personality.is_default', 1)
							->select(
								'PRS.personalityId as id',
								'PRS.personalityName as name'
							)->first();
			if(( $default==null || $default->id==null ) && $data->isEmpty()){ return []; }
			if(( $default==null || $default->id==null ) && !$data->isEmpty()){ return $data; }
			if($default->id!=null && $data->isEmpty()){ return [$default]; }
			$addDefault = true;
			foreach($data as $tmp){
				if($tmp->id==$default->id){ $addDefault=false; }
			}
			if( $addDefault ){ $data->prepend($default); }
			return $data;
		}catch(\Throwable $e){ return ['result'=>1, 'data'=>[], 'msg'=>$e->getMessage()]; }
	}
	//---------------------------------------
	public function getPortalNumbers(){
		try{
			$data = \App\PortalType::orderBy("number", "asc")->get();
			return ['result'=>1, 'data'=>$data, 'msg'=>""];
		}catch(\Throwable $e){ return ['result'=>1, 'data'=>[], 'msg'=>$e->getMessage()]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $req){
		try{
			$data = $req->all();
			$data['name'               ] = trim($data['name'         ]);
			// $data['code'            ] = trim($data['code'         ]);
			$data['portal_number'      ] = trim($data['portal_number']);
			$data['description'        ] = trim($data['description'  ]);
			$data['organization_id'    ] = (($data['organization_id']==0) ?null :$data['organization_id']);
			$data['OnOff'              ] = (($data['OnOff'       ]=='1') ?1 :0);
			$data['hasLiveAgent'       ] = (($data['hasLiveAgent']=='1') ?1 :0);
			$data['KaaS3PB'            ] = (($data['KaaS3PB'     ]=='1') ?1 :0);
			$data['MoD_'               ] = (($data['MoD_'        ]=='1') ?1 :0);
			$data['ntfctn_mssg_cstmztn'] = substr(trim($data['ntfctn_mssg_cstmztn']), 0, 1000);
			$data['rqst_mssg_cstmztn'  ] = substr(trim($data['rqst_mssg_cstmztn'  ]), 0, 1000);
			$data['feedback'           ] = $data['feedback'];

			//create random char for code 
			$code = '';
			$seed = str_split('abcdefghijklmnopqrstuvwxyz1234567890'); // and any other characters
			shuffle($seed); // probably optional since array_is randomized; this may be redundant
			foreach (array_rand($seed, 5) as $k) $code .= $seed[$k];

			$data['code'] = trim($code);
			//check while code is duplicate set $data=>code
			
			$data['unknownPersonalityId'] = trim($data['unknownPersonalityId']);
			if($data['unknownPersonalityId']==""){
				$data['unknownPersonalityId']=null;
			}
							
			while(\App\Portal::where("code", $data['code'])->where('organization_id', $data['organization_id'])->count()!=0){
				foreach (array_rand($seed, 5) as $k) $code .= $seed[$k];					
			}


			if(\App\Portal::where("name", $data['name'])->where('organization_id', $data['organization_id'])->count()!=0){
				return ['result'=>1, 'msg'=>"Name is duplicated"];
			}

			$cnt = \App\Portal::where("portal_number", $data['portal_number'])
						->where('name', $data['name'])
						->where('organization_id', $data['organization_id'])
						->count();
			if($cnt!=0){ return ['result'=>1, 'msg'=>"Portal type is duplicated"]; }

			$portal = new \App\Portal;
			$portal->organization_id      = $data['organization_id'     ];
			$portal->code                 = $data['code'                ];
			$portal->portal_number        = $data['portal_number'       ];
			$portal->name                 = $data['name'                ];
			$portal->OnOff                = $data['OnOff'               ];
			$portal->hasLiveAgent         = $data['hasLiveAgent'        ];
			$portal->OnOff_by             = $data['userID'              ];
			$portal->KaaS3PB              = $data['KaaS3PB'             ];
			$portal->MoD_                 = $data['MoD_'                ];
			$portal->ntfctn_mssg_cstmztn  = $data['ntfctn_mssg_cstmztn' ];
			$portal->rqst_mssg_cstmztn    = $data['rqst_mssg_cstmztn'   ];
			$portal->description          = $data['description'         ];
			$portal->unknownPersonalityId = $data['unknownPersonalityId'];
			
			$portal->feedback             = $data['feedback'];
			$portal->thumbsup             = $data['feedback'];
			$portal->comment              = $data['feedback'];
//			if($data['organization_id']!=null){
//				$portal->feedback = \App\Organization::find($data['organization_id'])->feedback;
//			}
			
			$portal->last                 = date("Y-m-d H:i:s");
			if($portal->save()){ return ['result'=>0, 'msg'=>"Portal added"]; }
			else{ return ['result'=>1, 'msg'=>"Error on insert record"]; }
			return $req->all();
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function editRow($orgID, $id, Request $req){
		try{
			$data = $req->all();
			$data['name'         ] = trim($data['name'         ]);
			// $data['code'         ] = trim($data['code'         ]);
			$data['portal_number'] = trim($data['portal_number']);
			$data['description'  ] = trim($data['description'  ]);
			$data['organization_id'] = (($data['organization_id']==0) ?null :$data['organization_id']);
			$data['OnOff'          ] = (($data['OnOff'       ]=='1') ?1 :0);
			$data['hasLiveAgent'   ] = (($data['hasLiveAgent']=='1') ?1 :0);
			$data['KaaS3PB'        ] = (($data['KaaS3PB'     ]=='1') ?1 :0);
			$data['MoD_'               ] = (($data['MoD_'        ]=='1') ?1 :0);
			$data['ntfctn_mssg_cstmztn'] = substr(trim($data['ntfctn_mssg_cstmztn']), 0, 1000);
			$data['rqst_mssg_cstmztn'  ] = substr(trim($data['rqst_mssg_cstmztn'  ]), 0, 1000);
			$data['feedback'           ] = $data['feedback'];

			$data['unknownPersonalityId'] = trim($data['unknownPersonalityId']);
			if($data['unknownPersonalityId']==""){
				$data['unknownPersonalityId']=null;
			}
			if(\App\Portal::where("name", $data['name'])
			   				->where("portal_number", $data['portal_number'])
			   				// ->where("code", $data['code'])
			   				->where('organization_id', $data['organization_id'])
			   				->where('id','<>',$id)->count()!=0){
				return ['result'=>1, 'msg'=>"Portal is duplicated"];
			}
			$portal = \App\Portal::find($id);
			$portal->organization_id      = $data['organization_id'     ];
			// $portal->code                 = $data['code'             ];
			$portal->portal_number        = $data['portal_number'       ];
			$portal->name                 = $data['name'                ];
			$tmpOnOff = $portal->OnOff;
			$portal->OnOff                = $data['OnOff'               ];
			$portal->hasLiveAgent         = $data['hasLiveAgent'        ];
			$portal->KaaS3PB              = $data['KaaS3PB'             ];
			$portal->MoD_                 = $data['MoD_'                ];
			$portal->ntfctn_mssg_cstmztn  = $data['ntfctn_mssg_cstmztn' ];
			$portal->rqst_mssg_cstmztn    = $data['rqst_mssg_cstmztn'   ];
			$portal->description          = $data['description'         ];
			$portal->unknownPersonalityId = $data['unknownPersonalityId'];

			$portal->feedback             = $data['feedback'];
			$portal->thumbsup             = $data['feedback'];
			$portal->comment              = $data['feedback'];
//			if($data['organization_id']!=null){
//				$portal->feedback = \App\Organization::find($data['organization_id'])->feedback;
//				if($portal->feedback==0){
//					$portal->thumbsup = 0;
//					$portal->comment  = 0;
//				}
//			}

			$portal->last                 = date("Y-m-d H:i:s");
			if($tmpOnOff != $portal->OnOff){ $portal->OnOff_by = $data['userID']; }
			if($portal->save()){
				if($portal->hasLiveAgent==0){
					\App\LiveAgentMapBots::where('ownerId', $portal->organization_id)->update(['publish_status'=>'Unpublished']);
				}
				return ['result'=>0, 'msg'=>"Portal change"];
			}else{ return ['result'=>1, 'msg'=>"Error on edit record"]; }
			return $req->all();
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow($orgID, $id, Request $req){
		try{
			$portal = \App\Portal::find($id);
			if(is_null($portal) ){ return ['result'=>1, 'msg'=>"portal not found"]; }
			else{
				if($portal->organization_id!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this portal"]; }
				$tmp = $portal->delete($id);
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(\ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function checkPortal($portal, Request $req){
		try{
			$portalNumber = substr($portal, 0, 1);
			$portalCode   = substr($portal, 1);
			$portal = \App\Portal::where("portal_number", $portalNumber)->where("code", $portalCode)->first();
			if(is_null($portal) ){ return ['result'=>1, 'msg'=>"portal not found", 'data'=>['onoff'=>0, 'msg'=>[]]]; }
			else{
				$msg = [];
/*
				$langs = \App\BotMessage::where("OrgId", NULL)
						->orWhere('OrgId', $portal->organization_id)
						->select('Lang')
						->groupBy('Lang')
						->get();
*/
				$langs = \App\OrganizationLanguage::where("org_id", $portal->organization_id)
						->select('language as Lang')
						->get();
				$orgID = $portal->organization_id;
				$langsList = [];
				if(! $langs->isEmpty()){
					foreach($langs as $lang){ $langsList[] = $lang->Lang; }
				}else{ $langsList[] = 'en'; }

				foreach($langsList as $lang){
					$msg[$lang] = [];
					$codes = [];
					$tmpMsgs = \App\BotMessage::where('OrgId', '=', $orgID)
							->where('Lang', $lang)
							->select(
								'id',
								'Name',
								'Code',
								'Type',
								'OrgId',
								'Description',
								'Message',
								'Lang'
							)
							->orderBy('Name', 'asc')
							->get();
					if(! $tmpMsgs->isEmpty()){
						foreach($tmpMsgs as $tmpMsg){
							$msg[$lang][] = $tmpMsg;
							$codes[] = $tmpMsg->Code;
						}
					}
					$tmpMsgs = \App\BotMessage::where('OrgId', '=', NULL)
							->where('Lang', $lang)
							->whereNotIn('Code', $codes)
							->select(
								'id',
								'Name',
								'Code',
								'Type',
								'OrgId',
								'description',
								'Message',
								'Lang'
							)
							->orderBy('Name', 'asc')
							->get();
					if(! $tmpMsgs->isEmpty()){
						foreach($tmpMsgs as $tmpMsg){ $msg[$lang][] = $tmpMsg; }
					}
				}
				$retVal = [
					'result'=>0,
					'msg'=>'',
					'data'=>[
						'onoff'=>0,
//						'orgId'=>$portal->organization_id,
						'msg'  => $msg,
					]
				];
				if($portalNumber==$portal->portal_number && $portalCode==$portal->code){ $retVal['data']['onoff'] = $portal->OnOff; }
				else{ $retVal['data']['onoff'] = 0; }
				
				$liveAgentMapBots = \App\LiveAgentMapBots::where('ownerId', $portal->organization_id)
										->where('portal_id', $portal->id)
										->where('publish_status', 'Published')
										->first();
				if($liveAgentMapBots!=null){
					$retVal['liveAgentMapping'] = 1;
					$liveAgentMapDetail = \App\LiveAgentMapDetail::where('mappingBot_id', $liveAgentMapBots->bot_id)
											->first();
					$retVal['IntentName'      ] = null;
					$retVal['APIVersion'      ] = null;
					$retVal['APIURL'          ] = null;
					$retVal['OrganizationID'  ] = null;
					$retVal['DeploymentID'    ] = null;
					$retVal['ButtonID'        ] = null;
					$retVal['timeout'         ] = 0;
					if($liveAgentMapDetail!=null){
						$retVal['IntentName'      ] = $liveAgentMapDetail->intentName;
						$retVal['APIVersion'      ] = $liveAgentMapDetail->apiVersion;
						$retVal['APIURL'          ] = $liveAgentMapDetail->apiUrl;
						$retVal['OrganizationID'  ] = $liveAgentMapDetail->organizationId;
						$retVal['DeploymentID'    ] = $liveAgentMapDetail->deploymentId;
						$retVal['ButtonID'        ] = $liveAgentMapDetail->buttonId;
						if($liveAgentMapDetail->timeoutSwitch!=0 && $liveAgentMapDetail->timeout!=null)
							{ $retVal['timeout'] = $liveAgentMapDetail->timeout; }
					}
				}else{
					$retVal['liveAgentMapping'] = 0;
					$retVal['timeout'         ] = 0;
					$retVal['IntentName'      ] = null;
					$retVal['APIVersion'      ] = null;
					$retVal['APIURL'          ] = null;
					$retVal['OrganizationID'  ] = null;
					$retVal['DeploymentID'    ] = null;
					$retVal['ButtonID'        ] = null;
				}

				$retVal['MoD'] = [
					'active' => $portal->MoD_,
					'notification' => $portal->ntfctn_mssg_cstmztn,
					'request' => $portal->rqst_mssg_cstmztn
				];

				$org = \App\Organization::find($orgID);
				$retVal['feedback'] = [
					'OrgAndPortalFeedback' => $portal->feedback * $org->feedback,
					'thumbsup'             => $portal->thumbsup * $org->feedback,
					'feedback_comment'     => $portal->comment * $org->feedback
				];
				
				return $retVal;
			}
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage(), 'data'=>['onoff'=>0, 'msg'=>[]]];
		}
	}
	//---------------------------------------
	public function portals($orgID, Request $req){
		try{
			$portals = \App\Portal::where("organization_id", $orgID)->get();
			return ['result'=>0, 'msg'=>'', 'data'=>$portals];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage(), 'data'=>['onoff'=>0]];
		}
	}
	//---------------------------------------
	public function ownersList($orgId=0){
		$data  = \App\Portal::ownersList($orgId);
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
	public function getPerson($portal_id){
		try{
			$tmp = \App\Portal::getPortalPerson($portal_id);
			if(is_null($tmp) ){ return ['result'=>1, 'msg'=>"Mapping can not be done, no default Persona for Org"]; }
			if($tmp->personaName==""){ return ['result'=>1, 'msg'=>"Mapping can not be done, no default Persona for Org"]; }
			return ['result'=>0, 'data'=>$tmp];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getPortalLiveAgent($orgId=0){
		$data  = \App\Portal::liveAgentList($orgId);
		return ['result'=>0, 'msg'=>'', 'total'=>0, 'data'=>$data];
	}
	//---------------------------------------
	public function getFeedbackItems($portal_id){
		try{
			$tmp = \App\Portal::find($portal_id);
			return ['result'=>0, 'data'=>$tmp];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function setFeedbackItems(Request $req){
		try{
			$validator = \Validator::make(
					$req->all(),
					[
						'id'       => 'required',
						'feedback' => 'required',
						'thumbsup' => 'required',
						'comment'  => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			$data = $req->all();
			
			$tmp = \App\Portal::where('id', $data['id'])
				->update([
					'feedback' => $data['feedback'],
					'thumbsup' => $data['thumbsup'],
					'comment'  => $data['comment' ]
				]);
			return ['result'=>0, 'msg'=>"OK"];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}
//-------------------------------------------
