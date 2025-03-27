<?php
//---------------------------------------------
namespace App\Lex;
use \App\LexMapBots;
//---------------------------------------------
class LexClass{
//---------------------------------------------
	private $data = ['bot'=>'', 'botversion'=>''];
	private $orgId, $lexUserId;
	private $mapId=0;
	private $inttId=0;
	//-----------------------------------------
	function __construct($botName, $botAlias, $orgId, $lexUserId){
		$tmp = \App\LexMapBots::where("bot_name", $botName)->
								where('bot_alias', $botAlias)->
								where('ownerId', $orgId)->
								where('publish_status', "Published")->
								where('lexUserID', $lexUserId)->first();
		if($tmp != null){
			$this->data['bot'       ] = $botName;
			$this->data['botversion'] = $botAlias;
			$this->orgId     = $orgId;
			$this->lexUserId = $lexUserId;
			$this->mapId     = $tmp->bot_id;
		}else{ 
			$this->mapId=0;
			$this->clearIntent();
			$this->data =['bot'=>'', 'botversion'=>''];
		}
	}
	//-----------------------------------------
	private function clearIntent(){
		$this->inttId=0;
		unset($this->data['intent']);
		unset($this->data['intentVersion']);
		unset($this->data['sampleUtterance']);
		unset($this->data['Intent-krId']);
		unset($this->data['slots']);
	}
	//-----------------------------------------
	public function findIntent($kr_id){
		if($this->mapId!=0){
			$tmp = \App\LexMapDetail::where('parent_id', $this->mapId)->
										where('type', 'intent')->
										where('kr_id', $kr_id)->first();
			$this->clearIntent();
			if($tmp!=null){
				$this->data['intent'         ]=$tmp->val1;
				$this->data['intentVersion'  ]=$tmp->val2;
				$this->data['sampleUtterance']=$tmp->val3;
				$this->data['Intent-krId'    ]=$tmp->kr_id;
				$this->inttId=$tmp->id;
			}
		}
	}
	//-----------------------------------------
	public function findSlot($slot_kr_id, $value_kr_id=null){
		if($this->inttId!=0){
			$tmp = \App\LexMapDetail::where('parent_id', $this->inttId)->
										where('type', 'slot')->
										where('kr_id', $slot_kr_id)->first();
			if($tmp!=null){
				$value = "";
				$valKRid = '';
				if($value_kr_id!=null){
					$val = \App\LexMapDetail::where('parent_id', $tmp->id)->
												where('type', 'value')->
												where('kr_id', $value_kr_id)->first();
					if($val!=null){ 
						$value   = $val->val1;
						$valKRid = $val->kr_id;
					}
				}
				$this->data['slots'][]=['name'=>$tmp->val1, 'type'=>$tmp->val2, 'Slot_krId'=>$tmp->kr_id, 'value'=>$value, 'Value_krId'=>$valKRid];
			}
		}
	}
	//-----------------------------------------
	public function getData(){ return $this->data; }
	//-----------------------------------------
	public function findKR($krId){
		$this->clearIntent();
		if(is_array($krId)){ foreach($krId as $tmpId){ if( $this->_findKR($tmpId)){ return; } } }
		else{ $this->_findKR($krId); }
	}
	private function _findKR($krId){
		if($this->mapId!=0){
			//---------------------------------
			$tmp = \App\LexMapDetail::where('parent_id', $this->mapId)->where('type', 'intent')->where('kr_id', $krId)->first();
			//---------------------------------
			if($tmp!=null){
				$this->data['intent'         ]=$tmp->val1;
				$this->data['intentVersion'  ]=$tmp->val2;
				$this->data['sampleUtterance']=$tmp->val3;
				$this->data['Intent-krId'    ]=$tmp->kr_id;
				$this->inttId=$tmp->id;
				return true;
			}
			//---------------------------------
			else{
				$this->inttId=0;
				$tmpIs = \App\LexMapDetail::where('parent_id', $this->mapId)->where('type', 'intent')->get();
				if($tmpIs!=null){
					foreach($tmpIs as $tmpI){
						//---------------------
						$tmp = \App\LexMapDetail::where('parent_id', $tmpI->id)->where('type', 'slot')->where('kr_id', $krId)->first();
						//---------------------
						if($tmp!=null){
							$this->data['intent'         ]=$tmpI->val1;
							$this->data['intentVersion'  ]=$tmpI->val2;
							$this->data['sampleUtterance']=$tmpI->val3;
							$this->data['Intent-krId'    ]=$tmpI->kr_id;
							$this->data['slots'][]=['name'=>$tmp->val1, 'type'=>$tmp->val2, 'Slot_krId'=>$tmp->kr_id, 'value'=>"", 'Value_krId'=>''];
							$this->inttId=$tmpI->id;
							return true;
						}
						//---------------------
						else{
							$tmpSs = \App\LexMapDetail::where('parent_id', $tmpI->id)->where('type', 'slot')->get();
							if($tmpSs!=null){
								foreach($tmpSs as $tmpS){
									//---------
									$tmp = \App\LexMapDetail::where('parent_id', $tmpS->id)->where('type', 'value')->where('kr_id', $krId)->first();
									//---------
									if($tmp!=null){
										$this->data['intent']=$tmpI->val1;
										$this->data['intentVersion']=$tmpI->val2;
										$this->data['sampleUtterance']=$tmpI->val3;
										$this->data['Intent-krId']=$tmpI->kr_id;
										$this->data['slots'][]=[
											'name'=>$tmpS->val1, 'type'=>$tmpS->val2, 'Slot_krId'=>$tmpS->kr_id,
											'value'=>$tmp->val1, 'Value_krId'=>$tmp->kr_id
										];
										$this->inttId=$tmpI->id;
										return true;
									}
									//---------
								}
							}
						}
						//---------------------
					}
				}
			}
			//---------------------------------
		}
		return false;
	}
	//-----------------------------------------
	public function findSlotName($intent, $slotName, $valueName){
		$intent    = strtolower($intent   );
		$slotName  = strtolower($slotName );
		$valueName = strtolower($valueName);
		if($this->mapId!=0){
			//---------------------------------
			$this->clearIntent();
			//---------------------------------
			if($intent!=''){
				$tmpI = \App\LexMapDetail::where('type', 'intent')->where('val1', $intent)->where('parent_id', $this->mapId)->first();
				if($tmpI!=null){
					$tmpS = \App\LexMapDetail::where('type', 'slot')->where('parent_id', $tmpI->id)->where('val1', $slotName)->first();
					if($tmpS!=null){ 
						$value   = '';
						$valKRid = '';
						if($valueName!=''){
							$tmpV = \App\LexMapDetail::where('type', 'value')->where('val1', $valueName)->where('parent_id', $tmpS->id)->first();
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
				$tmpS = \App\LexMapDetail::where('type', 'slot')->where('val1', $slotName)->get();
				if($tmpS!=null){ 
					foreach($tmpS as $tmps){
						$tmpI = \App\LexMapDetail::where('type', 'intent')->where('id', $tmps->parent_id)->where('parent_id', $this->mapId)->first();
						if($tmpI!=null){
							$value   = '';
							$valKRid = '';
							if($valueName!=''){
								$tmpV = \App\LexMapDetail::where('type', 'value')->where('val1', $valueName)->where('parent_id', $tmps->id)->first();
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
}
//---------------------------------------------
