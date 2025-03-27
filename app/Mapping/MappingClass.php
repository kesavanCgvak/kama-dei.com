<?php
/**
  MappingClass release 3.0
  Created by  : Behrooz Darabiha
  Updated by  : Gabriel Carrillo
 */ 
//---------------------------------------------
namespace App\Mapping;
//---------------------------------------------
class MappingClass{
	//-----------------------------------------
	private $data         = [];
	private $isActive     = false;
	private $portalID     = 0;
	private $orgID        = 0;
	private $organization = [];
	private $portal       = [];
	private $mapId        = 0;
	//-----------------------------------------
	function __construct($portalCode){


		if(strlen($portalCode)==6){//} && substr($portalCode, 0, 1)=='1'){
			$portal = \App\Portal::where(function($q) use($portalCode){
						$code          = substr($portalCode, 1, 5);
						$portal_number = substr($portalCode, 0, 1);
						return $q
							->where("portal.code"         , $code         )
							->where("portal.portal_number", $portal_number);
					})->
					leftJoin("organization_ep", "portal.organization_id", "=", "organization_ep.organizationId")->
					select(
						"portal.*",
						"organization_ep.organizationShortName as organizationtName"
					)->
					first();
			if($portal!=null){
				$this->portalID = $portal->id;
				$this->orgID    = $portal->organization_id;
				$this->organization = ["name"=>$portal->organizationtName, "id"=>$portal->organization_id];
				$this->portal       = ["name"=>$portal->name, "id"=>$portal->id];
				$this->data['organization'] = $this->organization;
//				$this->data['portal'      ] = $this->portal;

				return true;
			}
			return false;
		}else{
			return false;
		}
	}
	//-----------------------------------------
	public function getData(){ return $this->data; }
	//-----------------------------------------
	private function isPublic($kr_id){
		$tmp = \App\Relation::find($kr_id);
		if($tmp==null){ return false; }
		if($tmp->ownerId==0 || $tmp->ownerId==null){ return true; }

		return true;		

	}
	//-----------------------------------------
	//-----------------------------------------
	//-- findIntent ---------------------------
	//-----------------------------------------
	public function findIntent($kr_id){
		$isPublic = $this->isPublic($kr_id);
		$tmp = $this->findIntent_RPA($kr_id, $isPublic);
		if($tmp==false){ $tmp = $this->findIntent_LiveAgent($kr_id, $isPublic); }
		if($tmp==false){ $tmp = $this->findIntent_KaaS($kr_id, $isPublic); }
		if($tmp==false){ $tmp = $this->findIntent_LEX($kr_id, $isPublic); }
		return $tmp;
	}
	//-----------------------------------------
	public function findIntent_RPA($kr_id, $isPublic){//RPA

		$orgID    = $this->orgID;
		$portalID = $this->portalID;
		$tmp = \App\RPAMapKR::where('mapping_kr.kr_id', $kr_id)->
					where(function($q) use($isPublic, $orgID, $portalID){
						if($isPublic){ return $q; }
						return $q->
							where('mapping_bot.ownerId', $orgID)->
							where('mapping_bot.portal_id', $this->portalID);
					})->
					where('mapping_bot.publish_status', "Published")->
					leftJoin("mapping_detail", "mapping_kr.mapping_detail_id", "mapping_detail.id")->
					leftJoin("mapping_bot", "mapping_detail.mapping_header_id", "=", "mapping_bot.bot_id")->
					leftJoin("bot_type", "mapping_bot.bot_type_id", "=", "bot_type.id")->
					leftJoin("kamadeiep.organization_ep", "mapping_bot.ownerId", "=", "kamadeiep.organization_ep.organizationId")->
					select(
						"mapping_kr.*",
//						"mapping_detail.intentName",
//						"mapping_detail.apiVersion",
//						"mapping_detail.apiUrl",
//						"mapping_detail.deploymentId",
//						"mapping_detail.buttonId",
//						"mapping_detail.organizationId",
//						"mapping_detail.timeout",
//						"mapping_detail.timeoutSwitch",
						"mapping_detail.id",
						"mapping_detail.id as mapping_detail_id",
						"kamadeiep.organization_ep.organizationShortName",
						"mapping_bot.ownerId",
						"mapping_bot.bot_name",
						"mapping_bot.bot_alias",
						"mapping_bot.bot_id",
						"bot_type.name as mappingName",
						"bot_type.show_order as showOrder",
						"bot_type.id as mappingType"
					)->
					first();

		$data = [];
		if($tmp!=null){
			$data['id'                ] = $tmp->bot_id;
			$data['kr_id'             ] = $kr_id;
			$data['mapping'           ] = ["name"=>$tmp->mappingName, "type"=>$tmp->mappingType, "showOrder"=>$tmp->showOrder ];
			$data['organization'      ] = ["name"=>$tmp->organizationShortName, "id"=>$tmp->ownerId];
			$data['bot_name'          ] = $tmp->bot_name;
			$data['bot_alias'         ] = $tmp->bot_alias;
//			$data['intent'            ] = $tmp->intentName;
//			$data['apiVersion'        ] = $tmp->apiVersion;
//			$data['apiUrl'            ] = $tmp->apiUrl;
//			$data['deploymentId'      ] = $tmp->deploymentId;
//			$data['buttonId'          ] = $tmp->buttonId;
//			$data['organizationId'    ] = $tmp->organizationId;
//			$data['timeout'           ] = $tmp->timeout;
//			$data['timeoutSwitch'     ] = (($tmp->timeoutSwitch==1) ?'true' :'false');
//			$data['Intent-krId'       ] = 0;
			$data['handOffMessage'    ] = null;//$tmp->handOffMessage;
			$data['sampleUtterance'   ] = $tmp->sampleUtterance;
			$data['all-krIDs'         ] = [];
			$data['pre_handoffMessage'] = [];
			
			$tmps = \App\RPAMapKR::where('mapping_detail_id', $tmp->mapping_detail_id)->orderBy('kr_order', 'asc')->get();
			foreach($tmps as $tmp){
				$data['all-krIDs'][] = $tmp->kr_id;
				
				$data['pre_handoffMessage'][$tmp->kr_id] = [];
				$tmpss = \App\RPAPreHandoff::where('mapping_kr_id', $tmp->mapping_kr_id)->orderBy('pre_handoff_message_id', 'asc')->get();
				foreach($tmpss as $tmp2)
					{ $data['pre_handoffMessage'][$tmp->kr_id][$tmp2->lang_code] =$tmp2->pre_handoff_message; }
			}
			
			$this->data = $data;
			return true;
		}else{
			$this->data = [];
			$this->data['organization'] = $this->organization;
		}

		return false;
	}
	//-----------------------------------------
	public function findIntent_LiveAgent($kr_id, $isPublic){//Live Agent
		$orgID    = $this->orgID;
		$portalID = $this->portalID;
		$tmp = \App\LiveAgentMapKR::where('mapping_kr.kr_id', $kr_id)->
					where(function($q) use($isPublic, $orgID, $portalID){
						if($isPublic){ return $q; }
						return $q->
							where('mapping_bot.ownerId', $orgID)->
							where('mapping_bot.portal_id', $this->portalID);
					})->
					where('mapping_bot.publish_status', "Published")->
					leftJoin("mapping_detail", "mapping_kr.mappingBot_id", "mapping_detail.mappingBot_id")->
					leftJoin("mapping_bot", "mapping_detail.mappingBot_id", "=", "mapping_bot.bot_id")->
					leftJoin("kamadeiep.organization_ep", "mapping_bot.ownerId", "=", "kamadeiep.organization_ep.organizationId")->
					select(
						"mapping_kr.*",
						"mapping_detail.intentName",
						"mapping_detail.apiVersion",
						"mapping_detail.apiUrl",
						"mapping_detail.organizationId",
						"mapping_detail.deploymentId",
						"mapping_detail.buttonId",
						"mapping_detail.timeout",
						"mapping_detail.timeoutSwitch",
						"mapping_detail.id",
						"mapping_detail.mappingBot_id",
						"kamadeiep.organization_ep.organizationShortName",
						"mapping_bot.ownerId"
					)->
					first();
		$data = [];
		if($tmp!=null){


			$botType  = ["name"=>"Live Agent", "type"=>3];
			$botTypes = \App\RPATypes::find($botType["type"]);
			if($botType!=null){ $botType['name'] = $botTypes->name; }
			$data['kr_id'         ] = $kr_id;

			//$data['mapping'       ] = $botType;
			$data['mapping'       ] = ["name"=>$botType['name'], "type"=>$botType["type"], "showOrder"=>$botType["type"] ];
			$data['organization'  ] = ["name"=>$tmp->organizationShortName, "id"=>$tmp->ownerId];
			$data['intent'        ] = $tmp->intentName;
			$data['apiVersion'    ] = $tmp->apiVersion;
			$data['apiUrl'        ] = $tmp->apiUrl;
			$data['organizationId'] = $tmp->organizationId;
			$data['deploymentId'  ] = $tmp->deploymentId;
			$data['buttonId'      ] = $tmp->buttonId;
			$data['timeout'       ] = $tmp->timeout;
			$data['timeoutSwitch' ] = (($tmp->timeoutSwitch==1) ?'true' :'false');
//			$data['Intent-krId'   ] = 0;
			$data['handOffMessage'] = $tmp->handOffMessage;
			$data['all-krIDs'     ] = [];
			
			$tmps = \App\LiveAgentMapKR::where('mappingBot_id', $tmp->mappingBot_id)->orderBy('kr_order', 'asc')->get();
			foreach($tmps as $tmp){ $data['all-krIDs'][] = $tmp->kr_id; }
			
			$this->data = $data;
			return true;
		}else{
			$this->data = [];
			$this->data['organization'] = $this->organization;
		}
		return false;
	}
	//-----------------------------------------
	public function findIntent_KaaS($kr_id, $isPublic){//KaaS
		$orgID    = $this->orgID;
		$portalID = $this->portalID;
		$tmp = \App\KaasMapDetail::where('kr_id', $kr_id)->
					where('type_id', 1)->
					where(function($q) use($isPublic, $orgID, $portalID){
						if($isPublic){ return $q; }
						return $q->
							where('mapping_bot.ownerId', $orgID)->
							where('mapping_bot.portal_id', $this->portalID);
					})->
					where('mapping_bot.publish_status', "Published")->
					leftJoin("mapping_bot", "mapping_detail.mappingBot_id", "=", "mapping_bot.bot_id")->
					leftJoin("kamadeiep.organization_ep", "mapping_bot.ownerId", "=", "kamadeiep.organization_ep.organizationId")->
					first();
		$data = [];
		if($tmp!=null){
			$botType  = ["name"=>"KaaS", "type"=>2];
			$slotParentId = $tmp->id;
			$botTypes = \App\RPATypes::find($botType["type"]);
			if($botType!=null){ $botType['name'] = $botTypes->name; }
			$data['kr_id'          ] = $tmp->kr_id;
			$data['mapping'        ] = $botType;
			$data['organization'   ] = ["name"=>$tmp->organizationShortName, "id"=>$tmp->ownerId];
			$data['bot_name'       ] = $tmp->bot_name;
			$data['bot_alias'      ] = $tmp->bot_alias;
			$data['intent'         ] = $tmp->val1;
			$data['sampleUtterance'] = $tmp->val3;

            /// Gabriel Carrillo:  ADD child slot records  ///////////////////////////

			$slottmp = \App\KaasMapDetail::where('parent_id', $slotParentId)->
                                           where('type_id', 2)->get();
            if ($slottmp!=null) { 
				foreach($slottmp as $slt){
				    $data['slot'][] = ['slotname'=>$slt->val1, "slotkrid"=>$slt->kr_id ,"slotid"=>$slt->id];
			    }
		    }

            ////////////////////////////////////////////////////////////////////////	

			$this->data = $data;
			return true;
		}else{
			$this->data = [];
			$this->data['organization'] = $this->organization;
		}
		return false;
	}
	//-----------------------------------------
	public function findIntent_LEX($kr_id, $isPublic){//LEX
		$orgID = $this->orgID;
		$tmp = \App\LexMapDetail::where('kr_id', $kr_id)->
					where('type', 'intent')->
					where(function($q) use($isPublic, $orgID){
						if($isPublic){ return $q; }
						return $q->
							where('mapping_bot.ownerId', $orgID);
					})->
					where('mapping_bot.publish_status', "Published")->
					leftJoin("mapping_bot", "mapping_detail.parent_id", "=", "mapping_bot.bot_id")->
					leftJoin("kamadeiep.organization_ep", "mapping_bot.ownerId", "=", "kamadeiep.organization_ep.organizationId")->
					first();
		$data = [];
		if($tmp!=null){
			$botType  = ["name"=>"Lext Joint", "type"=>1];
			$botTypes = \App\RPATypes::find($botType["type"]);
			if($botType!=null){ $botType['name'] = $botTypes->name; }
			$data['kr_id'          ] = $tmp->kr_id;
			$data['mapping'        ] = $botType;
			$data['organization'   ] = ["name"=>$tmp->organizationShortName, "id"=>$tmp->ownerId];
			$data['bot_name'       ] = $tmp->bot_name;
			$data['bot_alias'      ] = $tmp->bot_alias;
			$data['intent'         ] = $tmp->val1;
			$data['intentVersion'  ] = $tmp->val2;
			$data['sampleUtterance'] = $tmp->val3;
			
			$this->data = $data;
			return true;
		}else{
			$this->data = [];
			$this->data['organization'] = $this->organization;
		}
		return false;
	}
	//-----------------------------------------
	//-- findSlot -----------------------------
	//-----------------------------------------
	public function findSlot($slot_kr_id, $value_kr_id=null){
		//$isPublic = $this->isPublic($slot_kr_id);
                $isPublic = 1;
		$tmp = $this->findSlot_RPA($slot_kr_id, $value_kr_id, $isPublic);
		//if($tmp==false){ $tmp = $this->findSlot_LiveAgent($slot_kr_id, $value_kr_id, $isPublic); }
		if($tmp==false){ $tmp = $this->findSlot_KaaS($slot_kr_id, $value_kr_id, $isPublic); }
		//if($tmp==false){ $tmp = $this->findSlot_LEX($slot_kr_id, $value_kr_id, $isPublic); }
		return $tmp;
	}
	//-----------------------------------------
	public function findSlot_RPA($slot_kr_id, $value_kr_id, $isPublic){
		$this->data = [];
		$this->data['organization'] = $this->organization;
		return false;
	}
	//-----------------------------------------
	public function findSlot_LiveAgent($slot_kr_id, $value_kr_id, $isPublic){
		$this->data = [];
		$this->data['organization'] = $this->organization;
		return false;
	}
	//-----------------------------------------
	public function findSlot_KaaS($slot_kr_id, $value_kr_id, $isPublic){
		$orgID    = $this->orgID;
		$portalID = $this->portalID;
		$data = [];
		$tmp = \App\KaasMapDetail::where('type_id', 2)->
					//where('id', $slot_id)->
					where('kr_id', $slot_kr_id)->
					where(function($q) use($isPublic, $orgID, $portalID){
						if($isPublic){ return $q; }
						return $q->
							where('mapping_bot.ownerId', $orgID)->
							where('mapping_bot.portal_id', $this->portalID);
					})->
					where('mapping_bot.publish_status', "Published")->
					leftJoin("mapping_bot", "mapping_detail.mappingBot_id", "=", "mapping_bot.bot_id")->
					select("mapping_detail.*")->
					first();
		if($tmp!=null){
			$slot = \App\KaasMapDetail::where('id', $tmp->id)->first();
			$intent = \App\KaasMapDetail::where('id', $slot->parent_id)->
					where(function($q) use($isPublic, $orgID, $portalID){
						if($isPublic){ return $q; }
						return $q->
							where('mapping_bot.ownerId', $orgID)->
							where('mapping_bot.portal_id', $this->portalID);
					})->
					where('mapping_bot.publish_status', "Published")->
					leftJoin("mapping_bot", "mapping_detail.mappingBot_id", "=", "mapping_bot.bot_id")->
					leftJoin("kamadeiep.organization_ep", "mapping_bot.ownerId", "=", "kamadeiep.organization_ep.organizationId")->
					first();
			$botType  = ["name"=>"KaaS", "type"=>2];
			$botTypes = \App\RPATypes::find($botType["type"]);
			if($botType!=null){ $botType['name'] = $botTypes->name; }
			$data['isPiblic'] = $isPublic;
			$data['kr_id'          ] = $tmp->kr_id;
			$data['mapping'        ] = $botType;
			$data['organization'   ] = ["name"=>$intent->organizationShortName, "id"=>$intent->ownerId];
			$data['bot_name'       ] = $intent->bot_name;
			$data['bot_alias'      ] = $intent->bot_alias;
			$data['intent'         ] = $intent->val1;
			$data['sampleUtterance'] = $intent->val3;
			$data['slot'] =
				[ 'name'=>$slot->val1, 'values'=>[] ];
			$values = \App\KaasMapDetail::where('parent_id', $slot->id)->
										where('type_id', 3)->
										get();
			if(!$values->isEmpty()){ 
				foreach($values as $value){
					$data['slot']['values'][] = ['value'=>$value->val1, "kr_id"=>$value->kr_id];
				}
			}
			$this->data = $data;
			return true;
		}else{
			$this->data = [];
			$this->data['organization'] = $this->organization;
		}
		return false;
	}
	//-----------------------------------------
	public function findSlot_LEX($slot_kr_id, $value_kr_id, $isPublic){
		$orgID    = $this->orgID;
		$tmp = \App\LexMapDetail::where('type', 'slot')->
				where('kr_id', $slot_kr_id)->
				first();
		$data = [];
		if($tmp!=null){
			$intent = \App\LexMapDetail::where('id', $tmp->parent_id)->
					where(function($q) use($isPublic, $orgID){
						if($isPublic){ return $q; }
						return $q->
							where('mapping_bot.ownerId', $orgID);
					})->
					where('mapping_bot.publish_status', "Published")->
					leftJoin("mapping_bot", "mapping_detail.parent_id", "=", "mapping_bot.bot_id")->
					leftJoin("kamadeiep.organization_ep", "mapping_bot.ownerId", "=", "kamadeiep.organization_ep.organizationId")->
					first();
			
			$botType  = ["name"=>"Lext Joint", "type"=>1];
			$botTypes = \App\RPATypes::find($botType["type"]);
			if($botType!=null){ $botType['name'] = $botTypes->name; }
			$data['mapping'        ] = $botType;
			$data['organization'   ] = ["name"=>$intent->organizationShortName, "id"=>$intent->ownerId];
			$data['bot_name'       ] = $intent->bot_name;
			$data['bot_alias'      ] = $intent->bot_alias;
			$data['intent'         ] = $intent->val1;
			$data['intentVersion'  ] = $intent->val2;
			$data['sampleUtterance'] = $intent->val3;
			$value = "";
			$valKRid = '';
			
			$val = \App\LexMapDetail::where('parent_id', $tmp->id)->
										where('type', 'value')->
										first();
			if($val!=null){ 
				$value   = $val->val1;
				$valKRid = $val->kr_id;
			}
			$data['slot'] =
				[ 'name'=>$tmp->val1, 'type'=>$tmp->val2, 'Slot_krId'=>$tmp->kr_id, 'value'=>$value, 'Value_krId'=>$valKRid ];
			
			$this->data = $data;
			return true;
		}else{
			$this->data = [];
			$this->data['organization'] = $this->organization;
		}
		return false;
	}
	//-----------------------------------------
	//-- findSlotName -------------------------
	//-----------------------------------------
	public function findSlotName($slot_kr_id, $value_kr_id=null){
		$isPublic = $this->isPublic($slot_kr_id);
		$tmp = $this->findSlotName_RPA($slot_kr_id, $value_kr_id, $isPublic);
		if($tmp==false){ $tmp = $this->findSlotName_LiveAgent($slot_kr_id, $value_kr_id, $isPublic); }
		if($tmp==false){ $tmp = $this->findSlotName_KaaS($slot_kr_id, $value_kr_id, $isPublic); }
		if($tmp==false){ $tmp = $this->findSlotName_LEX($slot_kr_id, $value_kr_id, $isPublic); }
		return $tmp;
	}



	//-- findSlotName_KaaS -------------------
	public function findSlotName_KaaS($intent, $slotName, $valueName){
		/*
		$intent    = strtolower($intent   );
		$slotName  = strtolower($slotName );
		$valueName = strtolower($valueName);
		*/

		if($this->mapId!=0){
			//---------------------------------
			$this->clearIntent();
			//---------------------------------
			if($intent!=''){
				
//				$tmpI = \App\KaasMapDetail::where('type', 'intent')->where('val1', $intent)->where('parent_id', $this->mapId)->first();
				$tmpI = \App\KaasMapDetail::where('type_id', 1)->where('val1', $intent)->where('mappingBot_id', $this->mapId)->first();
				if($tmpI!=null){
//					$tmpS = \App\KaasMapDetail::where('type', 'slot')->where('parent_id', $tmpI->id)->where('val1', $slotName)->first();
					$tmpS = \App\KaasMapDetail::where('type_id', 2)->where('parent_id', $tmpI->id)->where('val1', $slotName)->first();
					if($tmpS!=null){ 
						$value   = '';
						$valKRid = '';
						if($valueName!=''){
//							$tmpV = \App\KaasMapDetail::where('type', 'value')->where('val1', $valueName)->where('parent_id', $tmpS->id)->first();
							$tmpV = \App\KaasMapDetail::where('type_id', 3)->where('val1', $valueName)->where('parent_id', $tmpS->id)->first();
							if($tmpV!=null){ 
								$value   = $tmpV->val1; 
								$valKRid = $tmpV->kr_id;
							}
						}
						$this->data['intent'         ]=$tmpI->val1;
						$this->data['intentVersion'  ]=$tmpI->val2;
						$this->data['sampleUtterance']=$tmpI->val3;
						$this->data['Intent_krId'    ]=$tmpI->kr_id;
						$this->data['slots'][]=[
							'name'=>$tmpS->val1, 'type'=>$tmpS->val2, 'Slot_krId'=>$tmpS->kr_id,
							'value'=>$value, 'Value_krId'=>$valKRid
						];
						$this->inttId=$tmpI->id;
						return true;
					}
				}
			}else{
//				$tmpS = \App\KaasMapDetail::where('type', 'slot')->where('val1', $slotName)->get();
				$tmpS = \App\KaasMapDetail::where('type_id', 2)->where('val1', $slotName)->get();
				if($tmpS!=null){ 
					foreach($tmpS as $tmps){
//						$tmpI = \App\KaasMapDetail::where('type', 'intent')->where('id', $tmps->parent_id)->where('parent_id', $this->mapId)->first();
						$tmpI = \App\KaasMapDetail::where('type_id', 1)->where('id', $tmps->parent_id)->where('mappingBot_id', $this->mapId)->first();
						if($tmpI!=null){
							$value   = '';
							$valKRid = '';
							if($valueName!=''){
//								$tmpV = \App\KaasMapDetail::where('type', 'value')->where('val1', $valueName)->where('parent_id', $tmps->id)->first();
								$tmpV = \App\KaasMapDetail::where('type_id', 3)->where('val1', $valueName)->where('parent_id', $tmps->id)->first();
								if($tmpV!=null){
									$value   = $tmpV->val1;
									$valKRid = $tmpV->kr_id;
								}
							}
							$this->data['intent'         ]=$tmpI->val1;
							$this->data['intentVersion'  ]=$tmpI->val2;
							$this->data['sampleUtterance']=$tmpI->val3;
							$this->data['Intent_krId'    ]=$tmpI->kr_id;
							$this->data['slots'][]=[
								'name'=>$tmps->val1, 'type'=>$tmps->val2, 'Slot_krId'=>$tmps->kr_id,
								'value'=>$value, 'Value_krId'=>$valKRid
							];
							$this->inttId=$tmpI->id;
							return true;
						}
					}
				}
			}
		}
		return false;
	}




	//-----------------------------------------
	//-- findSlotId--- G.C. -------------------
	//-----------------------------------------
	public function findSlotId($slot_id, $value_kr_id=null){
		//$isPublic = $this->isPublic($slot_kr_id);
		$isPublic = 1;
		$tmp = $this->findSlotId_KaaS($slot_id, $value_kr_id, $isPublic); 
		return $tmp;
	}


	//-----------------------------------------
	public function findSlotId_KaaS($slot_id, $value_kr_id, $isPublic){
		$orgID    = $this->orgID;
		$portalID = $this->portalID;
		$data = [];
		$tmp = \App\KaasMapDetail::where('type_id', 2)->
					where('id', $slot_id)->
					where(function($q) use($isPublic, $orgID, $portalID){
						if($isPublic){ return $q; }
						return $q->
							where('mapping_bot.ownerId', $orgID)->
							where('mapping_bot.portal_id', $this->portalID);
					})->
					where('mapping_bot.publish_status', "Published")->
					leftJoin("mapping_bot", "mapping_detail.mappingBot_id", "=", "mapping_bot.bot_id")->
					select("mapping_detail.*")->
					first();
		if($tmp!=null){
			$slot = \App\KaasMapDetail::where('id', $tmp->id)->first();
			$intent = \App\KaasMapDetail::where('id', $slot->parent_id)->
					where(function($q) use($isPublic, $orgID, $portalID){
						if($isPublic){ return $q; }
						return $q->
							where('mapping_bot.ownerId', $orgID)->
							where('mapping_bot.portal_id', $this->portalID);
					})->
					where('mapping_bot.publish_status', "Published")->
					leftJoin("mapping_bot", "mapping_detail.mappingBot_id", "=", "mapping_bot.bot_id")->
					leftJoin("kamadeiep.organization_ep", "mapping_bot.ownerId", "=", "kamadeiep.organization_ep.organizationId")->
					first();
			$botType  = ["name"=>"KaaS", "type"=>2];
			$botTypes = \App\RPATypes::find($botType["type"]);
			if($botType!=null){ $botType['name'] = $botTypes->name; }
			$data['isPiblic'] = $isPublic;
			$data['kr_id'          ] = $tmp->kr_id;
			$data['mapping'        ] = $botType;
			$data['organization'   ] = ["name"=>$intent->organizationShortName, "id"=>$intent->ownerId];
			$data['bot_name'       ] = $intent->bot_name;
			$data['bot_alias'      ] = $intent->bot_alias;
			$data['intent'         ] = $intent->val1;
			$data['sampleUtterance'] = $intent->val3;
			$data['slot'] =
				[ 'name'=>$slot->val1, 'values'=>[] ];
			$values = \App\KaasMapDetail::where('parent_id', $slot->id)->
										where('type_id', 3)->
										get();
			if(!$values->isEmpty()){ 
				foreach($values as $value){
					$data['slot']['values'][] = ['value'=>$value->val1, "kr_id"=>$value->kr_id];
				}
			}
			$this->data = $data;
			return true;
		}else{
			$this->data = [];
			$this->data['organization'] = $this->organization;
		}
		return false;
	}


	//-----------------------------------------
	//-----------------------------------------
	//-----------------------------------------
	//-- isActive -----------------------------
	//-----------------------------------------
	public static function isActive($orgID, $portal){
		$tmp = self::isActive_liveAgent($orgID, $portal);
		return $tmp;
	}
	//-----------------------------------------
	public static function isActive_liveAgent($orgID, $portal){
		//-------------------------------------
		$orgData = \App\Organization::find($orgID);
		if($orgData==null){ return false; }
		if($orgData->hasLiveAgent!=1){ return false; }
		//-------------------------------------
		if(strlen($portal)!=6){ return false; }
		if(substr($portal, 0, 1)!='1'){ return false; }
		//-------------------------------------
		$portalData = \App\Portal::where('portal_number', substr($portal, 0, 1))
								->where('code', substr($portal, 1, 5))
								->where('organization_id', $orgData->organizationId)
								->first();
		if($portalData==null){ return false; }
		if($portalData->hasLiveAgent!=1){ return false; }
		//-------------------------------------
		$mapData = \App\LiveAgentMapBots::where('publish_status', "Published")
				->where('ownerId', $orgData->organizationId)
				->where('portal_id', $portalData->id)
				->first();
		if($mapData==null){ return false; }
		//-------------------------------------
		return true;
	}
}
//---------------------------------------------
