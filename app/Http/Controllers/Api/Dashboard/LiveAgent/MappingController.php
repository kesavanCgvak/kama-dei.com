<?php
namespace App\Http\Controllers\Api\Dashboard\LiveAgent;

use Illuminate\Http\Request;

use App\Controllers;
class MappingController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function showPage( $orgID, $sort, $order, $perPage, $page, $portalID, $field='', $value='' ){
		//-----------------------------------------------------------------------------------------
		$sort = $this->sortFields( $sort );
		if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//-----------------------------------------------------------------------------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		$count = 0;
		if($portalID==0 ) $count = \App\LiveAgentMapBots::count();
		else $count = \App\LiveAgentMapBots::where('portal_id', $portalID)->count();
		$data  = \App\LiveAgentMapBots::myPageing($orgID, $perPage, $page, $sort, $order, $field, $value, $portalID);

		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			$data = $request->input();
			$data['mappingName' ] = trim($data['mappingName' ]);
			$data['ownerId'     ] = trim($data['ownerId'     ]);
			$data['userID'      ] = trim($data['userID'      ]);
			$data['portal_id'   ] = trim($data['portal_id'   ]);
			$data['structure_id'] = trim($data['structure_id']);

			if($data['mappingName' ]==""){ return ['result'=>1, 'msg'=>"invalid mapping name"]; }
			if($data['ownerId'     ]==""){ return ['result'=>1, 'msg'=>"invalid organization"]; }
			if($data['portal_id'   ]==""){ return ['result'=>1, 'msg'=>"invalid portal"]; }
			if($data['structure_id']==""){ return ['result'=>1, 'msg'=>"invalid structure"]; }
				
			$tmp = new \App\LiveAgentMapBots;
			$tmp->mappingName  = $data['mappingName'  ];
			$tmp->ownerId      = $data['ownerId'      ];
			$tmp->portal_id    = $data['portal_id'    ];
			$tmp->structure_id = $data['structure_id' ];
			$tmp->user_id      = $data['userID'       ];
			$tmp->last         = date("Y-m-d H:i:s");

			if($tmp->save()){ 
				\App\LiveAgentMapDetail::insert([
					'mappingBot_id'=> $tmp->bot_id,
					'intentName'   => 'Live Agent Chat',
					'created_by'   => $tmp->user_id,
					'last'         => $tmp->last
				]);
				\App\LiveAgentMapKR::insert([
					'mappingBot_id'=> $tmp->bot_id,
					'kr_id'        => 0,
					'kr_order'     => 0
				]);
				return ['result'=>0, 'msg'=>'', 'id'=>$tmp->bot_id]; 
			}
			return ['result'=>1, 'msg'=>'Error on saving data'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function mappedTo(Request $request){
		try{
			$added = 1;
			$data = $request->input();
			$mapping_kr_id = trim($data['mapping_kr_id']);
			$mappingBot_id = trim($data['mappingBot_id']);
			$kr_id         = trim($data['kr_id'        ]);
			$usID          = trim($data['user_id'      ]);
			
			if($mapping_kr_id==0){
				if($kr_id==0){
					if( \App\LiveAgentMapKR::where('mappingBot_id', $mappingBot_id)->where('kr_id', 0)->count()==0){
						\App\LiveAgentMapKR::insert(
							[
								'mappingBot_id'=> $mappingBot_id,
								'kr_id'        => 0,
								'kr_order'     => \App\LiveAgentMapKR::where('mappingBot_id', $mappingBot_id)->count()
							]);
					}
					return ['result'=>0, 'msg'=>"", 'added'=>$added];
				}
			}else{
				if($mappingBot_id==0){
					$tmp = \App\LiveAgentMapKR::find($mapping_kr_id);
					if($tmp!=null){
						\App\LiveAgentMapKR::where('mapping_kr_id', $mapping_kr_id)->delete();
						
						$tmp1 = \App\LiveAgentMapKR::where('mappingBot_id', $tmp->mappingBot_id)->orderBy('kr_order', 'asc')->get();
						if($tmp1->isEmpty()){
							\App\LiveAgentMapKR::insert([
								'mappingBot_id'=> $tmp->mappingBot_id,
								'kr_id'        => 0,
								'kr_order'     => 0
							]);
						}else{
							$kr_order = 0;
							foreach($tmp1 as $tmp2){
								\App\LiveAgentMapKR::where('mapping_kr_id', $tmp2->mapping_kr_id)->update(['kr_order'=>$kr_order++]);
							}
						}
						return ['result'=>0, 'msg'=>""];
					}else{ return ['result'=>1, 'msg'=>"record not found"]; }
				}else{
					if(\App\LiveAgentMapKR::where('mappingBot_id',$mappingBot_id)->where('kr_id',$kr_id)->count()==0){
						\App\LiveAgentMapKR::where('mapping_kr_id', $mapping_kr_id)->update(['kr_id'=>$kr_id]);
						return ['result'=>0, 'msg'=>'', 'id'=>$mapping_kr_id, 'added'=>0];
					}else{
						return ['result'=>1, 'msg'=>'This Knowledge Records has already been added'];
					}
				}
			}
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getMappedData(Request $req){
		$id = trim($req->input('id'));
		return $this->getMappedData_($id);
	}
	private function getMappedData_($id){
		try{
			$mapData = \App\LiveAgentMapBots::find($id);
			if($mapData==null){ return ['result'=>1, 'msg'=>"invalid mapping"]; }
			$types  = \App\LiveAgentType::where('structure_id', $mapData->structure_id)->orderBy('parent_id', 'asc')->get();
			if($types->isEmpty()){ return ['result'=>1, 'msg'=>"invalid structure"]; }
			$intent = \App\LiveAgentMapDetail::getData($id, 1)->get();
			$slots  = [];
			$values = [];

			return ['result'=>0, 'mapId'=>$id, 'data'=>array_merge($intent->toArray(),$slots, $values)];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function publishedMappStatus(Request $req){
		try{
			$bot_id         = trim($req->input('bot_id'        ));
			$mappingName    = trim($req->input('mappingName'   ));
			$publish_status = trim($req->input('publish_status'));
			$ownerId        = trim($req->input('ownerId'       ));
			$bot_name       = trim($req->input('bot_name'      ));
			$bot_alias      = trim($req->input('bot_alias'     ));

			$bot = \App\LiveAgentMapBots::find($bot_id);
			if($bot!=null){
				if($publish_status=="Published"){
					$firstPublished = \App\LiveAgentMapBots::where('bot_id', '<>', $bot_id)
							->where('ownerId', $bot->ownerId)
							->where('portal_id', $bot->portal_id)
							->where('publish_status', 'Published')->first();
					if($firstPublished!=null){
						return [
							'result'=>1,
							'msg'=>$firstPublished->mappingName." has already been published for the same portal, unpublish it first."
						];
					}
				}
				$bot->mappingName    = $mappingName;
				$bot->publish_status = $publish_status;
				if($bot->save()){ return ['result'=>0, 'msg'=>'' ]; }
				return ['result'=>1, 'msg'=>"Unkown error. try again."];			 
			}else{ return ['result'=>1, 'msg'=>"invalid bot mapping"]; }
			
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function unpublishedMappStatus(Request $req){
		try{
			$bot_id         = trim($req->input('bot_id'        ));
			$mappingName    = trim($req->input('mappingName'   ));
			$publish_status = trim($req->input('publish_status'));
			$bot = \App\LiveAgentMapBots::find($bot_id);
			if($bot!=null){
				$bot->mappingName    = $mappingName;
				$bot->publish_status = $publish_status;
				if($bot->save()){ return ['result'=>0, 'msg'=>'' ]; }
				return ['result'=>1, 'msg'=>"Unkown error. try again."];			 
			}else{ return ['result'=>1, 'msg'=>"invalid bot mapping"]; }
			
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteMap(Request $req){
		try{
			$id = trim($req->input('id'));//bhr
			\App\LiveAgentMapKR::where('mappingBot_id', $id)->delete();
			\App\LiveAgentMapDetail::where('mappingBot_id', $id)->delete();
			\App\LiveAgentMapBots::where('bot_id', $id)->delete();
			return ['result'=>0, 'msg'=>''];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function clearJson(Request $req){
		try{
			$mapID    = trim($req->input("mapID"   ));
			$botAlias = trim($req->input("botAlias"));
			$bot = \App\LiveAgentMapBots::find($mapID);
			if($bot!=null){
				$bot->bot_alias = $botAlias;
				if($bot->save()){ 
					\App\LiveAgentMapJson::where("mapId", $mapID)->delete();
					return ['result'=>0, 'msg'=>'' ]; 
				}
			}
			return ['result'=>1, 'msg'=>"Unkown error. try again."];
		}catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
	}
	//---------------------------------------
	public function setJson($mapId, $type, $name, $version, Request $req){
		try{
			$mapId   = trim($mapId  );
			$type    = trim($type   );
			$name    = trim($name   );
			$version = trim($version);
//			$json    = trim(request()->getContent());
			$json    = json_encode($req->input("json"));

			$tmp = \App\LiveAgentMapJson::where("mapId", $mapId)->where("type", $type)->where("name", $name)->where("version", $version)->count();
			if($tmp==0){ 
				$tmp = new \App\LiveAgentMapJson();
				$tmp->mapId = $mapId;
				$tmp->type = $type;
				$tmp->name = $name;
				$tmp->version = $version;
				$tmp->json = $json;
				if(!$tmp->save()){ return ['result'=>1, 'msg'=>"Error on {$type}"]; }
			}
			return ['result'=>0, 'msg'=>""];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getJson(Request $req){
		try{
			$mapId   = trim($req->input("mapId"  ));
			$type    = trim($req->input("type"   ));
			$name    = trim($req->input("name"   ));
			$version = trim($req->input("version"));

			$tmp = \App\LiveAgentMapJson::where("mapId", $mapId)->where("type", $type)->where("name", $name)->where("version", $version)->select("json")->first();
			if($tmp==null){ return ['result'=>0, 'msg'=>"", "data"=>null]; }
			return ['result'=>0, 'msg'=>"", "data"=>$tmp->json];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	
	//---------------------------------------
	public function showTerms(){
		return \App\Term::select("termId", "termName")->limit(100)->get();
	}
	public function showRelationTypes(){
		return \App\RelationType::select("relationTypeId", "relationTypeName")->limit(100)->get();
	}
	//---------------------------------------
	public function searchKrs(Request $req){
		$searchItem = trim($req->input('searchItem'));
		$tmp  = \App\Relation::myRelation(0, 'allFields', $searchItem)->count();
		if($tmp==0 || $tmp>40){ return[]; }
		$tmp  = \App\Relation::myRelation(0, 'allFields', $searchItem)->
					select(
						'relationId as id',
						\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as value'),
						\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as label')
					)->	get();
		return $tmp;
	}
	//---------------------------------------
	public function getRateValue($orgID, $personID, $relationID, $userID, $ownerID){
		$personalityRelationId = \App\PersonalityRelation::where('personalityId', $personID)->
										where('relationId', $relationID)->
//										where('relationId', $relationID)->
										select('personalityRelationId')->first();
		if($personalityRelationId!=null){
			$qry = \App\PersonalityRelationValue::myQuery($orgID, $personalityRelationId->personalityRelationId);
			return ['result'=>0, 'msg'=>'', 'id'=>$personalityRelationId->personalityRelationId , 'total'=>$qry->count(), 'data'=>$qry->get() ];
		}
		$request = new Request();
		$request->headers->set('content-type', 'application/json');     
		$request->initialize([ 'personalityID'=>$personID, 'knowledgeRecordID'=>$relationID, 'ownerID'=>$orgID, 'userID'=>$userID ]);
		$TMP = new \App\Http\Controllers\Api\Dashboard\PersonalityRelationValue\PersonalityRelationValueController();
		$tmp  = $TMP->addKnowledgeRecord($request);
		if($tmp!=null){ if($tmp['result']==0){ return ['result'=>0, 'msg'=>'', 'id'=>$tmp['id'], 'total'=>0, 'data'=>[] ]; } }
		return ['result'=>0, 'msg'=>'', 'id'=>0, 'total'=>0, 'data'=>[] ];
	}
	//---------------------------------------
	
	//---------------------------------------
	private function dataFields($field){
		switch(strtolower($field)){
			case 'intent_id' : { $field="intent_id"; break; }

			case 'mappingname'      : { $field="mappingName"; break; }
			case 'intent'           : { $field="intent"; break; }
			case 'version'          : { $field="version"; break; }
			case 'sample_utterance' : { $field="sample_utterance"; break; }
				
			case 'relationid'     : { $field="relationId"; break; }
			case 'publish_status' : { $field="publish_status"; break; }
			case 'ownerid'        : { $field="ownerId"; break; }
			case 'user_id'        : { $field="user_id"; break; }
			case 'last'           : { $field="last"; break; }

			default:{ $field = ""; }
		}
		return $field;
	}
	//---------------------------------------
	private function sortFields($sort){
		switch(strtolower($sort)){
			case 'bot_id' : { $sort="bot_id"; break; }

			case 'mappingname' : { $sort="mappingName"; break; }
			case 'bot_name'    : { $sort="bot_name"; break; }
			case 'bot_alias'   : { $sort="bot_alias"; break; }
				
			case 'organizationshortname' : { $sort="organizationShortName"; break; }
			case 'portalname'            : { $sort="portalName"; break; }
			case 'structurename'         : { $sort="structureName"; break; }
			case 'personaname'           : { $sort="personaName"; break; }

				
			case 'publish_status' : { $sort="publish_status"; break; }
			case 'ownerid'        : { $sort="ownerId"; break; }
			case 'user_id'        : { $sort="user_id"; break; }
			case 'last'           : { $sort="last"; break; }
				
			default:{ $sort = ""; }
		}
		return $sort;
	}
	//---------------------------------------

	//---------------------------------------
	public function newRow(Request $req){
		try{
			return ['result'=>1, 'msg'=>'Error on saving data'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function editRow(Request $req){
		try{
			$id             = trim($req->input("id"            ));
			$apiVersion     = trim($req->input("apiVersion"    ));
			$apiUrl         = trim($req->input("apiUrl"        ));
			$organizationId = trim($req->input("organizationId"));
			$deploymentId   = trim($req->input("deploymentId"  ));
			$buttonId       = trim($req->input("buttonId"      ));
			$timeout        = trim($req->input("timeout"       ));
			$timeoutSwitch  = trim($req->input("timeoutSwitch" ));
			$user_id        = trim($req->input("user_id"));

			$tmp = \App\LiveAgentMapDetail::find($id);
			if($tmp==null){ return ['result'=>1, 'msg'=>'invalid request']; }
			$tmp->apiVersion     = $apiVersion;
			$tmp->apiUrl         = $apiUrl;
			$tmp->organizationId = $organizationId;
			$tmp->deploymentId   = $deploymentId;
			$tmp->buttonId       = $buttonId;
			$tmp->timeout        = $timeout;
			$tmp->timeoutSwitch  = $timeoutSwitch;
//			$tmp->user_id        = $user_id;
//			$tag = "type_".(($tmp->parent_id==0) ?0 :$tmp->type_id)."_".$id;
//			$tag = "type_".$id;
			if($tmp->save()){ 
				return ['result'=>0, 'msg'=>'', 'id'=>$tmp->id, 'parent'=>0, 'tag'=>""];
			}
			return ['result'=>1, 'msg'=>'Error on saving data'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow(Request $req){
		try{
			$id = trim($req->input("id"));
			return ['result'=>0, 'msg'=>'', 'tag'=>""];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getLayer(Request $req){
		try{
			$parent_id = trim($req->input("parent_id"));
			$bot_id    = trim($req->input("bot_id"));
			$layer_id  = trim($req->input("layer_id"));
			
			$layer       = \App\LiveAgentType::find($layer_id);
			$parentLayer = \App\LiveAgentType::find($layer->parent_id);
			$detailData  = \App\LiveAgentMapDetail::getData($parent_id, $bot_id, $layer_id)->get();
			$parentData  = \App\LiveAgentMapDetail::find($parent_id);

			return [
				'result'     => 0,
				'msg'        => '',
				'data'       => $detailData,
				"layer"      => $layer,
				"parentLayer"=> $parentLayer,
				"parentData" => $parentData
			];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function getStructure(){
		try{
			$tmp = \App\LiveAgentStructure::orderBy('name', 'asc')->get();
			return ['result'=>0, 'data'=>$tmp];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function show(){
		return view("test_liveagentclass");
	}
	//---------------------------------------
	public function handOffMessage(Request $req){
		try{
			$mapping_kr_id  = trim($req->input('mapping_kr_id' ));
			$mappingBot_id  = trim($req->input('mappingBot_id' ));
			$handOffMessage = trim($req->input('handOffMessage'));
			$bot = \App\LiveAgentMapKR::where('mapping_kr_id', $mapping_kr_id)
				->where('mappingBot_id', $mappingBot_id)
				->update(['handOffMessage'=>$handOffMessage]);
			return ['result'=>0, 'msg'=>'' ];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}
