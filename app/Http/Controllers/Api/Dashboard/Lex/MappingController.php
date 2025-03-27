<?php
namespace App\Http\Controllers\Api\Dashboard\Lex;

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
		$count = \App\LexMapBots::count();

		$data  = \App\LexMapBots::myPageing($orgID, $perPage, $page, $sort, $order, $field, $value);

		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function insertRow($orgID, Request $request){
		try{
			$data = $request->input();
			$data['mappingName'     ] = trim($data['mappingName'     ]);
			$data['bot_name'        ] = trim($data['botName'         ]);
			$data['bot_alias'       ] = trim($data['botAlias'        ]);
			$data['ownerId'         ] = trim($data['ownerId'         ]);
			$data['personaId'       ] = trim($data['personaId'       ]);
			$data['userID'          ] = trim($data['userID'          ]);
			$data['lexPersonalityID'] = trim($data['lexPersonalityID']);
			$data['lexUserID'       ] = trim($data['lexUserID'       ]);
			
			$tmp = new \App\LexMapBots;
			$tmp->mappingName      = $data['mappingName'     ];
			$tmp->bot_name         = $data['bot_name'        ];
			$tmp->bot_alias        = $data['bot_alias'       ];
			$tmp->ownerId          = $data['ownerId'         ];
			$tmp->personaId        = $data['personaId'       ];
			$tmp->lexPersonalityID = $data['lexPersonalityID'];
			$tmp->lexUserID        = $data['lexUserID'       ];
			$tmp->user_id          = $data['userID'          ];
			$tmp->last             = date("Y-m-d H:i:s");

			if($tmp->save()){ return ['result'=>0, 'msg'=>'', 'id'=>$tmp->bot_id]; }
			return ['result'=>1, 'msg'=>'Error on saving data'];
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function mappedTo(Request $request){
		try{
			$added = 1;
			$data = $request->input();
			$data['ParentID'] = trim($data['ParentID']);
			$data['type'    ] = trim($data['type'    ]);
			$data['val1'    ] = trim($data['val1'    ]);
			$data['val2'    ] = trim($data['val2'    ]);
			$data['val3'    ] = trim($data['val3'    ]);
			$data['tag'     ] = trim($data['tag'     ]);
			$data['krID'    ] = trim($data['krID'    ]);
			$data['userID'  ] = trim($data['userID'  ]);
			
			$tmp = \App\LexMapDetail::where('type', $data['type'])
								->where('val1', $data['val1'])
								->where('val2', $data['val2'])
								->where('parent_id', $data['ParentID'])->first();
			if($tmp==null){
				$tmp = new \App\LexMapDetail;
				$tmp->parent_id = $data['ParentID'];
				$tmp->type      = $data['type'    ];
				$tmp->val1      = $data['val1'    ];
				$tmp->val2      = $data['val2'    ];
				$tmp->val3      = $data['val3'    ];
				$tmp->tag       = $data['tag'     ];
				$tmp->kr_id     = $data['krID'    ];
				$tmp->user_id   = $data['userID'  ];
				$tmp->last      = date("Y-m-d H:i:s");
			}else{
				$added = 0;
				$tmp->kr_id   = $data['krID'  ];
				$tmp->user_id = $data['userID'];
				$tmp->last    = date("Y-m-d H:i:s");
			}
			if($tmp->save()){ return ['result'=>0, 'msg'=>'', 'id'=>$tmp->id, 'added'=>$added]; }
			return ['result'=>1, 'msg'=>'Error on saving data'];
		}catch(ErrorException $ex){
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
			$intent = \App\LexMapDetail::getData($id, "intent")->get();
			$slots  = [];
			$values = [];
			foreach($intent as $tmpI){
				$slot = \App\LexMapDetail::getData($tmpI->id, "slot")->get();
				$slots = array_merge($slots,$slot->toArray());
				foreach($slot as $tmpS){
					$value = \App\LexMapDetail::getData($tmpS->id, "value")->get();
					$values = array_merge($values,$value->toArray());
				}
			}
			$slot = \App\LexMapDetail::getData($id, "intent")->get();
			
			return ['result'=>0, 'mapId'=>$id, 'data'=>array_merge($intent->toArray(),$slots, $values)];
		}catch(ErrorException $ex){
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
			
			\App\LexMapBots::where('bot_id', '<>', $bot_id)
						->where('ownerId', $ownerId)
						->where('bot_name', $bot_name)
						->where('bot_alias', $bot_alias)
						->update(['publish_status'=>'Unpublished']);
			
			$bot = \App\LexMapBots::find($bot_id);
			if($bot!=null){
				$bot->mappingName    = $mappingName;
				$bot->publish_status = $publish_status;
				if($bot->save()){ return ['result'=>0, 'msg'=>'' ]; }
				return ['result'=>1, 'msg'=>"Unkown error. try again."];			 
			}else{ return ['result'=>1, 'msg'=>"invalid bot mapping"]; }
			
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function unpublishedMappStatus(Request $req){
		try{
			$bot_id         = trim($req->input('bot_id'        ));
			$mappingName    = trim($req->input('mappingName'   ));
			$publish_status = trim($req->input('publish_status'));
			$bot = \App\LexMapBots::find($bot_id);
			if($bot!=null){
				$bot->mappingName    = $mappingName;
				$bot->publish_status = $publish_status;
				if($bot->save()){ return ['result'=>0, 'msg'=>'' ]; }
				return ['result'=>1, 'msg'=>"Unkown error. try again."];			 
			}else{ return ['result'=>1, 'msg'=>"invalid bot mapping"]; }
			
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteMap(Request $req){
		try{
			$id = trim($req->input('id'));
			$tmps = $this->getMappedData_($id);
			if($tmps['result']==0){
				foreach($tmps['data'] as $tmp){ \App\LexMapDetail::find($tmp['id'])->delete(); }
				\App\LexMapJson::where("mapId", $id)->delete();
				\App\LexMapBots::find($id)->delete();
				return ['result'=>0, 'msg'=>''];
			}
			return $tmps;
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function clearJson(Request $req){
		try{
			$mapID    = trim($req->input("mapID"   ));
			$botAlias = trim($req->input("botAlias"));
			$bot = \App\LexMapBots::find($mapID);
			if($bot!=null){
				$bot->bot_alias = $botAlias;
				if($bot->save()){ 
					\App\LexMapJson::where("mapId", $mapID)->delete();
					return ['result'=>0, 'msg'=>'' ]; 
				}
			}
			return ['result'=>1, 'msg'=>"Unkown error. try again."];
		}catch(ErrorException $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
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

			$tmp = \App\LexMapJson::where("mapId", $mapId)->where("type", $type)->where("name", $name)->where("version", $version)->count();
			if($tmp==0){ 
				$tmp = new \App\LexMapJson();
				$tmp->mapId = $mapId;
				$tmp->type = $type;
				$tmp->name = $name;
				$tmp->version = $version;
				$tmp->json = $json;
				if(!$tmp->save()){ return ['result'=>1, 'msg'=>"Error on {$type}"]; }
			}
			return ['result'=>0, 'msg'=>""];
		}catch(ErrorException $ex){
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

			$tmp = \App\LexMapJson::where("mapId", $mapId)->where("type", $type)->where("name", $name)->where("version", $version)->select("json")->first();
			if($tmp==null){ return ['result'=>0, 'msg'=>"", "data"=>null]; }
			return ['result'=>0, 'msg'=>"", "data"=>$tmp->json];
		}catch(ErrorException $ex){
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
			case 'lexusername'           : { $sort="lexUserName"; break; }
			case 'personalityname'       : { $sort="personalityName"; break; }
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
}
