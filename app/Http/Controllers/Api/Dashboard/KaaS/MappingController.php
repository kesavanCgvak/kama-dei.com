<?php
namespace App\Http\Controllers\Api\Dashboard\Kaas;

use Illuminate\Http\Request;

use App\Controllers;
class MappingController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function showPage( $orgID, $sort, $order, $perPage, $page, $field='', $value='' ){
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
		$count = \App\KaasMapBots::count();

		$data  = \App\KaasMapBots::myPageing($orgID, $perPage, $page, $sort, $order, $field, $value);

		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			$data = $request->input();
			$data['mappingName' ] = trim($data['mappingName' ]);
			$data['bot_name'    ] = trim($data['bot_name'    ]);
			$data['bot_alias'   ] = trim($data['bot_alias'   ]);
			$data['ownerId'     ] = trim($data['ownerId'     ]);
			$data['userID'      ] = trim($data['userID'      ]);
			$data['portal_id'   ] = trim($data['portal_id'   ]);
			$data['structure_id'] = trim($data['structure_id']);

			if($data['mappingName' ]==""){ return ['result'=>1, 'msg'=>"invalid mapping name"]; }
			if($data['bot_name'    ]==""){ return ['result'=>1, 'msg'=>"invalid bot name"]; }
			if($data['bot_alias'   ]==""){ return ['result'=>1, 'msg'=>"invalid bot alias"]; }
			if($data['ownerId'     ]==""){ return ['result'=>1, 'msg'=>"invalid organization"]; }
			if($data['portal_id'   ]==""){ return ['result'=>1, 'msg'=>"invalid portal"]; }
			if($data['structure_id']==""){ return ['result'=>1, 'msg'=>"invalid structure"]; }
				
			$tmp = new \App\KaasMapBots;
			$tmp->mappingName  = $data['mappingName'  ];
			$tmp->bot_name     = $data['bot_name'     ];
			$tmp->bot_alias    = $data['bot_alias'    ];
			$tmp->ownerId      = $data['ownerId'      ];
			$tmp->portal_id    = $data['portal_id'    ];
			$tmp->structure_id = $data['structure_id' ];
			$tmp->user_id      = $data['userID'       ];
			$tmp->last         = date("Y-m-d H:i:s");

			if($tmp->save()){ return ['result'=>0, 'msg'=>'', 'id'=>$tmp->bot_id]; }
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
			$krID = trim($data['krID'   ]);
			$dtID = trim($data['dtID'   ]);
			$usID = trim($data['user_id']);
			
			$tmp = \App\KaasMapDetail::find($dtID);
			if($tmp==null){ return ['result'=>1, 'msg'=>"invalid request"]; }
			else{
				$tmp->kr_id   = $krID;
				$tmp->user_id = $usID;
				$tmp->last    = date("Y-m-d H:i:s");
			}
			if($tmp->save()){ return ['result'=>0, 'msg'=>'', 'id'=>$tmp->id, 'added'=>$added]; }
			return ['result'=>1, 'msg'=>'Error on saving data'];
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
			$mapData = \App\KaasMapBots::find($id);
			if($mapData==null){ return ['result'=>1, 'msg'=>"invalid mapping"]; }
			$types  = \App\KaasType::where('structure_id', $mapData->structure_id)->orderBy('parent_id', 'asc')->get();
			if($types->isEmpty()){ return ['result'=>1, 'msg'=>"invalid structure"]; }
			$intent = \App\KaasMapDetail::getData($id, $types[0]->id)->get();
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

			$bot = \App\KaasMapBots::find($bot_id);
			if($bot!=null){
				\App\KaasMapBots::where('bot_id', '<>', $bot_id)
						->where('ownerId', $bot->ownerId)
						->where('portal_id', $bot->portal_id)
						->update(['publish_status'=>'Unpublished']);

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
			$bot = \App\KaasMapBots::find($bot_id);
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
			$id = trim($req->input('id'));
			$tmps = $this->getMappedData_($id);
			if($tmps['result']==0){
				foreach($tmps['data'] as $tmp){ \App\KaasMapDetail::find($tmp['id'])->delete(); }
				\App\KaasMapBots::find($id)->delete();
				return ['result'=>0, 'msg'=>''];
			}
			return $tmps;
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function clearJson(Request $req){
		try{
			$mapID    = trim($req->input("mapID"   ));
			$botAlias = trim($req->input("botAlias"));
			$bot = \App\KaasMapBots::find($mapID);
			if($bot!=null){
				$bot->bot_alias = $botAlias;
				if($bot->save()){ 
					\App\KaasMapJson::where("mapId", $mapID)->delete();
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

			$tmp = \App\KaasMapJson::where("mapId", $mapId)->where("type", $type)->where("name", $name)->where("version", $version)->count();
			if($tmp==0){ 
				$tmp = new \App\KaasMapJson();
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

			$tmp = \App\KaasMapJson::where("mapId", $mapId)->where("type", $type)->where("name", $name)->where("version", $version)->select("json")->first();
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
			$parent_id     = trim($req->input("parent_id"));
			$mappingBot_id = trim($req->input("mappingBot_id"));
			$type_id       = trim($req->input("type_id"      ));
			$val1          = trim($req->input("val1"         ));
			$val2          = trim($req->input("val2"         ));
			$val3          = trim($req->input("val3"         ));
			$user_id       = trim($req->input("user_id"      ));

			$tmp = new \App\KaasMapDetail;
			$tmp->parent_id     = $parent_id;
			$tmp->mappingBot_id = $mappingBot_id;
			$tmp->type_id       = $type_id;
			$tmp->val1          = $val1;
			$tmp->val2          = $val2;
			$tmp->val3          = $val3;
			$tmp->kr_id         = 0;
			$tmp->user_id       = $user_id;
			$tmp->last          = date("Y-m-d H:i:s");
			if($tmp->save()){ 
				if($parent_id!=0){
					$parent_id = 0;
					$inID = $tmp->id;
					while($parent_id==0){
						$tmpTmp = \App\KaasMapDetail::find($inID);
						if($tmpTmp->parent_id==0){ $parent_id=$tmpTmp->id; }
						else{ $inID = $tmpTmp->parent_id; }
					}
				}else{ $parent_id=$tmp->id; }
				
				$childLayer = 0;
				try{
					$layer3 = \App\KaasType::where('parent_id', $type_id)->first();
					$childLayer = $layer3->id;
				}catch(\Throwable $ex){}
				
				return ['result'=>0, 'msg'=>'', 'id'=>$tmp->id, 'parent'=>$parent_id, "childLayer"=>$childLayer];
			}
			return ['result'=>1, 'msg'=>'Error on saving data'];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function editRow(Request $req){
		try{
			$id      = trim($req->input("id"     ));
			$val1    = trim($req->input("val1"   ));
			$val2    = trim($req->input("val2"   ));
			$val3    = trim($req->input("val3"   ));
			$user_id = trim($req->input("user_id"));

			$tmp = \App\KaasMapDetail::find($id);
			if($tmp==null){ return ['result'=>1, 'msg'=>'invalid request']; }
			$tmp->val1     = $val1;
			$tmp->val2     = $val2;
			$tmp->val3     = $val3;
			$tmp->user_id = $user_id;
//			$tag = "type_".(($tmp->parent_id==0) ?0 :$tmp->type_id)."_".$id;
			$tag = "type_".$id;
			if($tmp->save()){ 
				$parent_id = 0;
				$inID = $tmp->id;
				while($parent_id==0){
					$tmp = \App\KaasMapDetail::find($inID);
					if($tmp->parent_id==0){ $parent_id=$tmp->id; }
					else{ $inID = $tmp->parent_id; }
				}
				return ['result'=>0, 'msg'=>'', 'id'=>$tmp->id, 'parent'=>$parent_id, 'tag'=>$tag];
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

			if(\App\KaasMapDetail::where('parent_id', $id)->count()!=0){ return ['result'=>1, 'msg'=>"you can't delete this item"]; }
			
			$tmp = \App\KaasMapDetail::find($id);
			if($tmp==null){ return ['result'=>1, 'msg'=>'invalid request']; }
			
//			$tag = "type_".(($tmp->parent_id==0) ?0 :$tmp->type_id)."_".$id;
			$tag = "type_".$id;
			
			\App\KaasMapDetail::where('id', $id)->delete();

			return ['result'=>0, 'msg'=>'', 'tag'=>$tag];
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
			
			$layer       = \App\KaasType::find($layer_id);
			$parentLayer = \App\KaasType::find($layer->parent_id);
			$detailData  = \App\KaasMapDetail::getData($parent_id, $bot_id, $layer_id)->get();
			$parentData  = \App\KaasMapDetail::find($parent_id);

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
			$tmp = \App\KaasStructure::orderBy('name', 'asc')->get();
			return ['result'=>0, 'data'=>$tmp];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}
