<?php
namespace App\Http\Controllers\Api\Dashboard\RPA;

use Illuminate\Http\Request;

use App\Controllers;
class MappingController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function showPage( $orgID, $sort, $order, $perPage, $page, $portalID, $field='', $value='' ){
		//-----------------------------------------------------------------------------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		$count = 0;
		if($portalID==0 ) $count = \App\RPAMappingBot::count();
		else $count = \App\RPAMappingBot::where('portal_id', $portalID)->count();
		$data  = \App\RPAMappingBot::myPageing($orgID, $perPage, $page, $sort, $order, $field, $value, $portalID);

		if(is_null($data) ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data]; }
	}
	//---------------------------------------
	public function addItem($orgID, Request $req){
		//-----------------------------------
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'bot_type_id'  => 'required',
						"mappingName"  => 'required',
						"bot_name"  => 'required',
						"bot_alias"  => 'required',
						'ownerId'      => 'required',
						"portal_id"    => 'required',
						"structure_id" => 'required',
						"userID"       => 'required'
					],
					[
						"bot_type_id.required" => "The Bot Type field is required",
						"portal_id.required" => "The Portal field is required",
						"structure_id.required" => "The Structure field is required",
						"mappingName.required" => "The Mapping Name field is required",
						"bot_name.required" => "The Bot Name field is required",
						"bot_alias.required" => "The Bot Alias field is required",
						"ownerId.required" => "The Organization field is required"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			$type = \App\RPAType::where('parent_id', 0)->where('structure_id', $data['structure_id'])->first();
			if($type==null){ throw new \Exception("rpa type not found"); }
			//-------------------------------
			$id = \App\RPAMappingBot::insertGetId([
				"bot_type_id"  => $data['bot_type_id' ],
				"mappingName"  => $data['mappingName' ],
				"bot_name"     => $data['bot_name' ],
				"bot_alias"    => $data['bot_alias' ],
				"ownerId"      => $data['ownerId'     ],
				"portal_id"    => $data['portal_id'   ],
				"structure_id" => $data['structure_id'],
				"user_id"      => $data['userID'      ]
			]);
			$id2 = \App\RPAMapDetail::insertGetId([
				'mapping_header_id' => $id,
				'parent_id'      => 0,
				'type_id'        => $type->id,
				'user_id'        => $data['userID'],
				'intentName'     => $type->name,
				'kr_id'          => 0
			]);
			return ['result'=>0, 'msg'=>"", 'id'=>$id];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-----------------------------------
	}
	//---------------------------------------
	public function editrow(Request $req){
		//-----------------------------------
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'id'              => 'required',
//						"apiVersion"      => 'required',
//						'apiUrl'          => 'required',
//						"deploymentId"    => 'required',
//						"buttonId"        => 'required',
//						'intentName'      => 'required',
						"organizationId"  => 'required',
						"timeout"         => 'required',
						"timeoutSwitch"   => 'required',
						"user_id"         => 'required',
						'mappingHeaderId' => 'required',
						'type_id'         => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			if($data['id']==0){
				$id = \App\RPAMapDetail::insertGetId([
					'mapping_header_id' => $data['mappingHeaderId'],
					'parent_id'      => 0,
					'type_id'        => $data['type_id'],
					'user_id'        => $data['user_id'],
//					'intentName'     => $data['intentName'],
//					'apiVersion'     => $data['apiVersion'],
//					'apiUrl'         => $data['apiUrl'],
//					'deploymentId'   => $data['deploymentId'],
//					'buttonId'       => $data['buttonId'],
					'organizationId' => $data['organizationId'],
					'timeoutSwitch'  => $data['timeoutSwitch'],
					'timeout'        => $data['timeout'],
					'kr_id'          => 0
				]);
			}else{
				$id = $data['id'];
				\App\RPAMapDetail::where('id', $id)->
					update([
						'mapping_header_id' => $data['mappingHeaderId'],
						'parent_id'      => 0,
						'type_id'        => $data['type_id'],
						'user_id'        => $data['user_id'],
//						'intentName'     => $data['intentName'],
//						'apiVersion'     => $data['apiVersion'],
//						'apiUrl'         => $data['apiUrl'],
//						'deploymentId'   => $data['deploymentId'],
//						'buttonId'       => $data['buttonId'],
						'organizationId' => $data['organizationId'],
						'timeoutSwitch'  => $data['timeoutSwitch'],
						'timeout'        => $data['timeout'],
						'kr_id'          => 0
					]);
			}
			return ['result'=>0, 'msg'=>'OK', 'id'=>$id];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-----------------------------------
	}
	//---------------------------------------
	public function mappedTo(Request $req){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'kr_id'             => 'required',
						"mapping_detail_id" => 'required',
						'mapping_kr_id'     => 'required',
						"user_id"           => 'required',
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			$added = 1;
			$mapping_kr_id     = trim($data['mapping_kr_id'    ]);
			$mapping_detail_id = trim($data['mapping_detail_id']);
			$kr_id             = trim($data['kr_id'            ]);
			$usID              = trim($data['user_id'          ]);
			
			if($mapping_kr_id==0){
				if($kr_id==0){
					if( \App\RPAMapKR::where('mapping_detail_id', $mapping_detail_id)->where('kr_id', 0)->count()==0){
						\App\RPAMapKR::insert(
							[
								'mapping_detail_id'=> $mapping_detail_id,
								'kr_id'            => 0,
								'kr_order'         => \App\RPAMapKR::where('mapping_detail_id', $mapping_detail_id)->count()
							]);
					}
					return ['result'=>0, 'msg'=>"", 'added'=>$added];
				}
			}else{
				if($mapping_detail_id==0){
					$tmp = \App\RPAMapKR::find($mapping_kr_id);
					if($tmp!=null){
						\App\RPAPreHandoff::where('mapping_kr_id', $mapping_kr_id)->delete();
						\App\RPAMapKR::where('mapping_kr_id', $mapping_kr_id)->delete();
						
						$tmp1 = \App\RPAMapKR::where('mapping_detail_id', $tmp->mapping_detail_id)->orderBy('kr_order', 'asc')->get();
						if($tmp1->isEmpty()){
							\App\RPAMapKR::insert([
								'mapping_detail_id'=> $tmp->mapping_detail_id,
								'kr_id'            => 0,
								'kr_order'         => 0
							]);
						}else{
							$kr_order = 0;
							foreach($tmp1 as $tmp2){
								\App\RPAMapKR::where('mapping_kr_id', $tmp2->mapping_kr_id)->update(['kr_order'=>$kr_order++]);
							}
						}
						return ['result'=>0, 'msg'=>""];
					}else{ return ['result'=>1, 'msg'=>"record not found"]; }
				}else{
					if(\App\RPAMapKR::where('mapping_detail_id',$mapping_detail_id)->where('kr_id',$kr_id)->count()==0){
						\App\RPAMapKR::where('mapping_kr_id', $mapping_kr_id)->update(['kr_id'=>$kr_id]);
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
	public function preHandoffSet(Request $req){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'mapping_kr_id'       => 'required',
						"lang_code"           => 'required',
						"pre_handoff_message" => 'required',
						"data_deleted"        => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			$mapping_kr_id       = trim($data['mapping_kr_id'      ]);
			$lang_code           = trim($data['lang_code'          ]);
			$pre_handoff_message = trim($data['pre_handoff_message']);
			//-------------------------------
			if($data['data_deleted']==0){
				if($lang_code!='en'){
					if(\App\RPAPreHandoff::where('mapping_kr_id', $mapping_kr_id)->where('lang_code', 'en')->count()==0){
						return ['result'=>1, 'msg'=>"Please add the message in English first"];
					}
				}
				$row = \App\RPAPreHandoff::where('mapping_kr_id', $mapping_kr_id)->where('lang_code', $lang_code)->first();
				if($row==null){
					\App\RPAPreHandoff::insert([
						'mapping_kr_id'       => $mapping_kr_id,
						'lang_code'           => $lang_code,
						'pre_handoff_message' => $pre_handoff_message
					]);
				}else{
					\App\RPAPreHandoff::where('pre_handoff_message_id', $row->pre_handoff_message_id)
						->update(['pre_handoff_message' => $pre_handoff_message]);
				}
			}else{
				if($lang_code=='en'){
					if(\App\RPAPreHandoff::where('mapping_kr_id', $mapping_kr_id)->where('lang_code', '<>', $lang_code)->count()==0){
						\App\RPAPreHandoff::where('mapping_kr_id', $mapping_kr_id)->where('lang_code', $lang_code)->delete();
					}else{
						return ['result'=>1, 'msg'=>"First delete other messages in other languages"];
					}
				}else{
					\App\RPAPreHandoff::where('mapping_kr_id', $mapping_kr_id)->where('lang_code', $lang_code)->delete();
				}
			}
			return ['result'=>0, 'msg'=>'' ];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function preHandoffGet(Request $req){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'mapping_kr_id'       => 'required',
						"lang_code"           => 'required',
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			$mapping_kr_id       = trim($data['mapping_kr_id'      ]);
			$lang_code           = trim($data['lang_code'          ]);
			
			$row = \App\RPAPreHandoff::where('mapping_kr_id', $mapping_kr_id)->where('lang_code', $lang_code)->first();
			return ['result'=>0, 'msg'=>'', 'data'=>$row ];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function sampleUtterance(Request $req){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'mapping_kr_id'   => 'required',
						"sampleUtterance" => 'required',
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			//-------------------------------
			$mapping_kr_id  = trim($data['mapping_kr_id']);
			$sampleUtterance = trim($data['sampleUtterance']);
			$bot = \App\RPAMapKR::where('mapping_kr_id', $mapping_kr_id)
				->update(['sampleUtterance'=>$sampleUtterance]);
			return ['result'=>0, 'msg'=>'' ];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function publishedMappStatus(Request $req){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'bot_id'      => 'required',
						'mappingName' => 'required',
						'bot_name'    => 'required',
						'bot_alias'   => 'required',
						'redo'        => 'required',
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			$bot_id = trim($data['bot_id']);
			//-------------------------------
			$bot = \App\RPAMappingBot::find($bot_id);
			if($bot!=null){
				$hvPublished = \App\RPAMappingBot::where('bot_id', '<>', $bot_id)
						->where('ownerId', $bot->ownerId)
						->where('portal_id', $bot->portal_id)
						->where('publish_status', 'Published')->first();
				if($hvPublished!=null){
					if($data['redo']==0){
						return [
							'result'=>2,
							'msg'=>$hvPublished->mappingName." has already been published for the same portal, it needs to be unpublished first."
						];
					}else{
					\App\RPAMappingBot::where('bot_id', '<>', $bot_id)
							->where('ownerId', $bot->ownerId)
							->where('portal_id', $bot->portal_id)
							->where('publish_status', 'Published')
							->update(['publish_status'=>'Unpublished']);
					}
				}
				$bot->publish_status = "Published";
				$bot->mappingName    = $data['mappingName'];
				$bot->bot_name       = $data['bot_name'   ];
				$bot->bot_alias      = $data['bot_alias'  ];
				
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
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'bot_id' => 'required',
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			$bot_id = trim($data['bot_id']);
			//-------------------------------
			$bot = \App\RPAMappingBot::find($bot_id);
			if($bot!=null){
				$bot->publish_status = 'Unpublished';
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
			$validator = \Validator::make(
					$req->all(),
					[
						'id' => 'required',
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				return ['result'=>1, 'msg'=>$errors->first()];
			}
			//-------------------------------
			$data = $req->all();
			$bot_id = trim($data['id']);
			//-------------------------------
			$bot = \App\RPAMappingBot::find($bot_id);
			if($bot!=null){
				$detail = \App\RPAMapDetail::where('mapping_header_id', $bot_id)->first();
				if($detail!=null){
					$RPAMapKR =  \App\RPAMapKR::where('mapping_detail_id', $detail->id)->get();
					if(!$RPAMapKR->isEmpty()){
						foreach($RPAMapKR as $tmp){
							\App\RPAPreHandoff::where('mapping_kr_id', $tmp->mapping_kr_id)->delete();
						}
					}
					\App\RPAMapKR::where('mapping_detail_id', $detail->id)->delete();
				}
				\App\RPAMapDetail::where('mapping_header_id', $bot_id)->delete();
			}
			\App\RPAMappingBot::where('bot_id', $bot_id)->delete();
			return ['result'=>0, 'msg'=>''];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
}