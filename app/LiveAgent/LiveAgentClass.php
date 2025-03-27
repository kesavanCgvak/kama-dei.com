<?php
//---------------------------------------------
namespace App\LiveAgent;
//---------------------------------------------
class LiveAgentClass{
//---------------------------------------------
	private $data = [];
	private $orgId, $portalCode, $portal_id, $structure_id;
	private $mapId=0;
	private $inttId=0;
	private $isLiveAgent3PBActive = false;
	//-----------------------------------------
	function __construct($orgId, $portalCode){
		if(strlen($portalCode)==6 && substr($portalCode, 0, 1)=='1'){			
			$tmp = \App\LiveAgentMapBots::leftJoin("kamadeiep.portal as portal", "mapping_bot.portal_id", "=", "portal.id")
					->where('mapping_bot.ownerId', $orgId)
					->where('mapping_bot.publish_status', "Published")
					->where(function($q) use($portalCode){
						$code          = substr($portalCode, 1, 5);
						$portal_number = substr($portalCode, 0, 1);
						return $q
							->where("portal.code"         , $code         )
							->where("portal.portal_number", $portal_number);
					})
					->first();
		}else{ $tmp=null; }

		if($tmp != null){
			$this->data['org_id']     = $orgId;
			$this->data['bot_id']     = $tmp->bot_id;
			$this->data['portal_id']  = $tmp->portal_id;
			$this->orgId              = $orgId;
			$this->portalCode         = $portalCode;
			$this->mapId              = $tmp->bot_id;
			$this->portal_id          = $tmp->portal_id;
			$this->structure_id       = $tmp->structure_id; 
		}else{ 
			$this->mapId=0;
			$this->portal_id    = 0;
			$this->structure_id = 0;
			$this->clearIntent();
			$this->data =[];
		}
	}
	//-----------------------------------------
	private function clearIntent(){
		$this->inttId=0;
		unset($this->data['intent']);
		unset($this->data['intentVersion']);
		unset($this->data['sampleUtterance']);
		unset($this->data['Intent-krId']);
		unset($this->data['handOffMessage']);
		unset($this->data['all-krIDs']);
		unset($this->data['slots']);
		$this->data = [];
	}
	//-----------------------------------------
	public function findIntent($kr_id){
		if($this->mapId!=0){
			$tmp = \App\LiveAgentMapKR::where('mapping_kr.mappingBot_id', $this->mapId)
						->where('mapping_kr.kr_id', $kr_id)
						->leftJoin("mapping_detail", "mapping_kr.mappingBot_id", "mapping_detail.mappingBot_id")
						->select(
							"mapping_kr.*",
							"mapping_detail.intentName",
							"mapping_detail.apiVersion",
							"mapping_detail.apiUrl",
							"mapping_detail.organizationId",
							"mapping_detail.deploymentId",
							"mapping_detail.buttonId",
							"mapping_detail.timeout",
							"mapping_detail.timeoutSwitch",
							"mapping_detail.id"
						)
						->first();

			$this->clearIntent();
			if($tmp!=null){
				$this->data['intent'        ] = $tmp->intentName;
				$this->data['apiVersion'    ] = $tmp->apiVersion;
				$this->data['apiUrl'        ] = $tmp->apiUrl;
				$this->data['organizationId'] = $tmp->organizationId;
				$this->data['deploymentId'  ] = $tmp->deploymentId;
				$this->data['buttonId'      ] = $tmp->buttonId;
				$this->data['timeout'       ] = $tmp->timeout;
				$this->data['timeoutSwitch' ] = (($tmp->timeoutSwitch==1) ?'true' :'false');
				$this->data['Intent-krId'   ] = 0;
			    $this->data['handOffMessage'] = $tmp->handOffMessage;
				$this->data['all-krIDs'     ] = [];
				$this->inttId=$tmp->id;

				$tmps = \App\LiveAgentMapKR::where('mappingBot_id', $this->mapId)->orderBy('kr_order', 'asc')->get();
				foreach($tmps as $tmp){ $this->data['all-krIDs'][] = $tmp->kr_id; }
			}
		}
	}
	//-----------------------------------------
	public function findSlot($slot_kr_id, $value_kr_id=null){}
	//-----------------------------------------
	public function getData(){ return $this->data; }
	
	//-----------------------------------------
	public function findKR($krId){
		$this->clearIntent();
		if(is_array($krId)){ foreach($krId as $tmpId){ if( $this->_findKR($tmpId)){ return; } } }
		else{ $this->_findKR($krId); }
	}

	//-----------------------------------------
	private function _findKR($krId){
		if($this->mapId!=0){
			//---------------------------------
			$tmp = \App\LiveAgentMapKR::where('mapping_kr.mappingBot_id', $this->mapId)
						->where('mapping_kr.kr_id', $krId)
						->leftJoin("mapping_detail", "mapping_kr.mappingBot_id", "mapping_detail.mappingBot_id")
						->select(
							"mapping_kr.*",
							"mapping_detail.intentName",
							"mapping_detail.apiVersion",
							"mapping_detail.apiUrl",
							"mapping_detail.organizationId",
							"mapping_detail.deploymentId",
							"mapping_detail.buttonId",
							"mapping_detail.timeout",
							"mapping_detail.timeoutSwitch",
							"mapping_detail.id"
						)
						->first();
			$this->clearIntent();
			if($tmp!=null){
				$this->data['intent'        ] = $tmp->intentName;
				$this->data['apiVersion'    ] = $tmp->apiVersion;
				$this->data['apiUrl'        ] = $tmp->apiUrl;
				$this->data['organizationId'] = $tmp->organizationId;
				$this->data['deploymentId'  ] = $tmp->deploymentId;
				$this->data['buttonId'      ] = $tmp->buttonId;
				$this->data['timeout'       ] = $tmp->timeout;
				$this->data['timeoutSwitch' ] = (($tmp->timeoutSwitch==1) ?'true' :'false');
				$this->data['Intent-krId'   ] = 0;
			    $this->data['handOffMessage'] = $tmp->handOffMessage;
				$this->data['all-krIDs'     ] = [];
				$this->inttId=$tmp->id;
				
				$tmps = \App\LiveAgentMapKR::where('mappingBot_id', $this->mapId)->orderBy('kr_order', 'asc')->get();
				foreach($tmps as $tmp){ $this->data['all-krIDs'][] = $tmp->kr_id; }
				return true;
			}
			//---------------------------------
		}
		return false;
	}
	//-----------------------------------------
	public function findSlotName($intent, $slotName, $valueName){ return false; }
	//-----------------------------------------
	public static function isActive($orgID, $portal){
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
	//-----------------------------------------
}
//---------------------------------------------
