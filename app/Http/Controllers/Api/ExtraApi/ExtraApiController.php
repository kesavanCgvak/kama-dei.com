<?php
namespace App\Http\Controllers\Api\ExtraApi;
//-------------------------------------------
use Illuminate\Http\Request;
use App\Controllers;
//-------------------------------------------
class ExtraApiController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function term(Request $req){
		$term_id = 0;
		//-----------------------------------
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'term_id'     => 'required',
						'term'        => 'required',
						'relation'    => 'required',
						'org_id'      => 'required',
						'org_id_2'    => 'required',
						'personality' => 'required',
						'link_url'    => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $req->all();
			//-------------------------------
			$relations = json_decode($data['relation'], true);
			if($relations==null){ throw new \Exception("Invalid relation data"); }
			//-------------------------------
			$data['org_id_2'] = $data['org_id'];
			if(\App\Organization::where('organizationId', $data['org_id'  ])->count()==0){ throw new \Exception("Invalid organization id"); }
			if(\App\Organization::where('organizationId', $data['org_id_2'])->count()==0){ throw new \Exception("Invalid organization id[2]"); }
			//-------------------------------
			$hasLeft = 0;
			if(isset($relations['left'])){
				foreach($relations['left'] as $key=>$left){
					if(!is_array($left)){ $left = json_decode($left, true); }
					if(!isset($left['relationType'])){ throw new \Exception("Undefined relation type id [left:{$key}]"); }
					if(!isset($left['term'])){ throw new \Exception("Undefined left term id [left:{$key}]"); }
					if($left["term"]==0){
						if(!isset($left['name'])){ throw new \Exception("Undefined left term [left:{$key}]"); }
					}
					if(\App\RelationType::where('relationTypeId', $left['relationType'])->count()==0)
						{ throw new \Exception("Invalid relation type id [left:{$key}]"); }
					if($left["term"]!=0){
						if(\App\Term::where('termId', $left['term'])->count()==0){ throw new \Exception("Invalid term id [left:{$key}]"); }
					}
				}
				$hasLeft = 1;
			}

			$hasRight = 0;
			if(isset($relations['right'])){
				foreach($relations['right'] as $key=>$right){
					if(!is_array($right)){ $right = json_decode($right, true); }
					if(!isset($right['relationType'])){ throw new \Exception("Undefined relation type id [right:{$key}]"); }
					if(!isset($right['term'])){ throw new \Exception("Undefined right term id [right:{$key}]"); }
					if($right["term"]==0){
						if(!isset($right['name'])){ throw new \Exception("Undefined right term [right:{$key}]"); }
					}
					if(\App\RelationType::where('relationTypeId', $right['relationType'])->count()==0)
						{ throw new \Exception("Invalid relation type id [right:{$key}]"); }
					if($right["term"]!=0){
						if(\App\Term::where('termId', $right['term'])->count()==0){ throw new \Exception("Invalid term id [right:{$key}]"); }
					}
				}
				$hasRight = 1;
			}
			//-------------------------------
			if(($hasLeft+$hasRight)==0){ throw new \Exception("Undefined relation data"); }
			//-------------------------------
			if( $data['term_id']!=0 && \App\Term::where('termId', $data['term_id'])->count()==0 ){ $data['term_id']=0; }
			//-------------------------------
			if($data['term_id']==0){
				$res = self::createTerm(0,$data['term'],$data['org_id'],$data['org_id_2'],$data['personality'],$relations,$data['link_url']);
				if($res['result']==0){ $term_id=$res['term_id']; }
				return $res;
			}else{
				if(\App\Term::where('termId', $data['term_id'])->count()==0){ throw new \Exception("Invalid term id"); }
				$term_id = $data['term_id'];
				self::clearTerm($data['term_id'], 1);
				return self::createTerm(
					$data['term_id'],$data['term'],$data['org_id'],$data['org_id_2'],$data['personality'],$relations,$data['link_url']
				);
			}
			//-------------------------------
		}catch(\Throwable $ex){
			try{ if($term_id!=0){ self::clearTerm($term_id, 1); } }catch(\Throwable $ex2){}
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-----------------------------------
	}
	//---------------------------------------
	private function createTerm($termId, $term, $org_id, $org_id_2, $personality,  $relations, $linkURL){
		//-----------------------------------
		if($termId==0){
			$term_id = \App\Term::insertGetId([
				"termName"       => $term,
				'termIsReserved' => 0,
				'ownership'      => 1,
				'ownerId'        => $org_id_2,
				'dateCreated'    => date("Y-m-d H:i:s"),
				'lastUserId'     => 0
			]);
		}else{
			\App\Term::where('termId', $termId)->update(["termName" => $term]);
			$term_id = $termId;
		}
		//-----------------------------------
		if(isset($relations['left'])){
			foreach($relations['left'] as $left){
				if(!is_array($left)){ $left = json_decode($left, true); }
				self::createRelations($term_id, $term, $left, $org_id, $org_id_2, $personality, $linkURL, 'left');
			}
		}
		//-----------------------------------
		if(isset($relations['right'])){
			foreach($relations['right'] as $right){
				if(!is_array($right)){ $right = json_decode($right, true); }
				self::createRelations($term_id, $term, $right, $org_id, $org_id_2, $personality, $linkURL, 'right');
			}
		}
		//-----------------------------------
		return ['result'=>0, 'msg'=>"OK", 'term_id'=>$term_id];
		//-----------------------------------
	}
	//---------------------------------------
	public function clearTerm($term_id, $flag=0){
		try{
			//-----------------------------------
//			if(\App\Term::where('termId', $term_id)->count()==0){ throw new \Exception("Invalid term id"); }
			if(\App\Term::where('termId', $term_id)->count()==0){ throw new \Exception("Invalid term id"); }
			//-----------------------------------
			$lRelations = \App\Relation::where('leftTermId' , $term_id)->get();
			if(!$lRelations->isEmpty()){
				foreach($lRelations as $relation){
					$lPersonalityRelation = \App\PersonalityRelation::where('relationId' , $relation->relationId)->get();
					if(!$lPersonalityRelation->isEmpty()){
						foreach($lPersonalityRelation as $personalityRelation){
							\App\PersonalityRelationValue::where('personalityRelationId',$personalityRelation->personalityRelationId)->delete();
						}
					}
					\App\PersonalityRelation::where('relationId' , $relation->relationId)->delete();
					
					$extendedLinks = \App\Models\Extend\Extended_link::where('parentId', $relation->relationId)->where('parentTable', 2)->get();
					if(!$extendedLinks->isEmpty()){
						foreach($extendedLinks as $extendedLink){
							\App\Models\Extend\Extended_EAV::where('extendedEntityId' , $extendedLink->entityId)->delete();
							\App\Models\Extend\Extended_entity::where('extendedEntityId' , $extendedLink->entityId)->delete();
						}
					}
					\App\Models\Extend\Extended_link::where('parentId', $relation->relationId)->where('parentTable', 2)->delete();
				}
			}
			\App\Relation::where('leftTermId' , $term_id)->delete();

			$rRelations = \App\Relation::where('rightTermId' , $term_id)->get();
			if(!$rRelations->isEmpty()){
				foreach($rRelations as $relation){
					$rPersonalityRelation = \App\PersonalityRelation::where('relationId' , $relation->relationId)->get();
					if(!$rPersonalityRelation->isEmpty()){
						foreach($rPersonalityRelation as $personalityRelation){
							\App\PersonalityRelationValue::where('personalityRelationId',$personalityRelation->personalityRelationId)->delete();
						}
					}
					\App\PersonalityRelation::where('relationId' , $relation->relationId)->delete();
					
					$extendedLinks = \App\Models\Extend\Extended_link::where('parentId', $relation->relationId)->where('parentTable', 2)->get();
					if(!$extendedLinks->isEmpty()){
						foreach($extendedLinks as $extendedLink){
							\App\Models\Extend\Extended_EAV::where('extendedEntityId' , $extendedLink->entityId)->delete();
							\App\Models\Extend\Extended_entity::where('extendedEntityId' , $extendedLink->entityId)->delete();
						}
					}
					\App\Models\Extend\Extended_link::where('parentId', $relation->relationId)->where('parentTable', 2)->delete();
				}
			}
			\App\Relation::where('rightTermId', $term_id)->delete();
			//-----------------------------------
			if($flag==0){ \App\Term::where('termId', $term_id)->delete(); }
			return ['result'=>0, 'msg'=>"OK", 'term_id'=>$term_id];
			//-----------------------------------
		}catch(\Exception $ex){
			if($flag==1){ throw new \Exception($ex->getMessage()); }
			else{ return ['result'=>1, 'msg'=>$ex->getMessage(), 'term_id'=>$term_id]; }
		}
	}
	//---------------------------------------
	private function createRelations($term_id, $termName, $items, $org_id, $org_id_2, $personality, $linkURL, $leftRight){
		if($items['term']==0){
			$itemTerm = \App\Term::where('termName', $items['name'])->where(function($q) use($org_id){
						return $q->whereNull('ownerId')->orWhereIn('ownerId', [0,$org_id]);
					})->first();
			if($itemTerm==null){
				$items['term'] = \App\Term::insertGetId([
					"termName"       => $items['name'],
					'termIsReserved' => 0,
					'ownership'      => 1,
					'ownerId'        => $org_id,
					'dateCreated'    => date("Y-m-d H:i:s"),
					'lastUserId'     => 0
				]);
			}else{
				$items['term'] = $itemTerm->termId;
			}
		}
		$relationId = \App\Relation::insertGetId([
			"leftTermId"         => (($leftRight=='right') ?$term_id :$items['term']),
			"rightTermId"        => (($leftRight!='right') ?$term_id :$items['term']),
			'relationTypeId'     => $items['relationType'],
			'relationOperand'    => '',
			'relationIsReserved' => 0,
			'ownership'          => 1,
			'ownerId'            => $org_id_2,
			'dateCreated'        => date("Y-m-d H:i:s"),
			'lastUserId'         => 0
		]);
		$personalityRelationId = \App\PersonalityRelation::insertGetId([
			"personalityId" => $personality,
			"relationId"    => $relationId,
			"ownership"     => 1,
			"ownerId"       => $org_id_2,
			"dateCreated"   => date("Y-m-d H:i:s"),
			"lastUserId"    => 0
		]);
		if(isset($items['ratings'])){
			$ratings = $items['ratings'];
			if(!is_array($ratings)){ $ratings = json_decode($ratings, true); }
			foreach($ratings as $rating){
				if($rating['term_id']==0){
					$ratingTerm = \App\Term::where('termName', $rating['name'])->where(function($q) use($org_id){
						return $q->whereNull('ownerId')->orWhereIn('ownerId', [0,$org_id]);
					})->first();
					if($ratingTerm==null){
						$rating['term_id'] = \App\Term::insertGetId([
							"termName"       => $rating['name'],
							'termIsReserved' => 0,
							'ownership'      => 1,
							'ownerId'        => $org_id,
							'dateCreated'    => date("Y-m-d H:i:s"),
							'lastUserId'     => 0
						]);
					}else{ $rating['term_id'] = $ratingTerm->termId; }
				}
				\App\PersonalityRelationValue::insertGetId([
					"personalityRelationId" => $personalityRelationId,
					"personRelationTermId"  => $rating['term_id'],
					"scalarValue"           => $rating['value'  ],
					"ownership"             => 1,
					"ownerId"               => $org_id_2,
					"dateCreated"           => date("Y-m-d H:i:s"),
					"lastUserId"            => 0
				]);
			}
		}

		$extendedEntityId = \App\Models\Extend\Extended_entity::insertGetId([
			"extendedEntityName" => $items['entity'],
			"extendedSubTypeId"  => 37,
			"lastUserId"         => 0,
			"ownerId"            => $org_id_2,
			"ownership"          => 1,
			"dateCreated"        => date("Y-m-d H:i:s"),
			"dateUpdated"        => date("Y-m-d H:i:s")
		]);
		
		\App\Models\Extend\Extended_EAV::insertGetId([
			"valueString"         => $linkURL,
			"extendedEntityId"    => $extendedEntityId,
			"extendedAttributeId" => 68,
			"ownerId"             => $org_id_2,
			"ownership"           => 1,
			"lastUserId"          => 0,
			"dateCreated"         => date("Y-m-d H:i:s"),
			"dateUpdated"         => date("Y-m-d H:i:s")
		]);
		
		\App\Models\Extend\Extended_link::insertGetId([
			"entityId"    => $extendedEntityId,
			"parentTable" => 2,
			"parentId"    => $relationId,
			"ownerId"     => $org_id_2,
			"ownership"   => 1,
			"created_at"  => date("Y-m-d H:i:s"),
			"updated_at"  => date("Y-m-d H:i:s"),
			"orderid"     => \App\Models\Extend\Extended_link::where('entityId',$extendedEntityId)
								->where("ownerId", $org_id)
								->where("parentId", $relationId)
								->where("parentTable", 2)
								->count()+1
		]);

	}
	//---------------------------------------
	public function getApiKey(Request $req){
		$term_id = 0;
		//-----------------------------------
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'org'    => 'required',
						'user'   => 'required',
						'portal' => 'required'
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $req->all();
			//-------------------------------
			$apiKey = \App\ApiKeyManager::where('userID', $data['user'])
				->where('orgID', $data['org'])
				->where('portal_code', $data['portal'])
				->where('api_key_valid_for_ever', 1)
				->orderBy('registerOn', 'desc')
				->select("api_key")
				->first();
			//-------------------------------
			if($apiKey==null){
				$apiKey = \App\ApiKeyManager::where('userID', $data['user'])
					->where('orgID', $data['org'])
					->where('portal_code', $data['portal'])
					->orderBy('api_key_expire', 'desc')
					->select("api_key")
					->first();
				if($apiKey==null){ throw new \Exception("apikey not found"); }
			}
			return ['result'=>0, 'msg'=>"OK", 'apikey'=>$apiKey->api_key];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
		//-----------------------------------
	}
	//---------------------------------------
}
//-------------------------------------------
