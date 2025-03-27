<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonalityRelation extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'personality_relation';
	protected $primaryKey = "personalityRelationId";
//	protected $dates      = ['dateCreated'];
	//--------------------------------------------------------------------
	protected function myQuery($orgID, $prsnltyID){
		$parent = \App\Personality::where('personalityId', $prsnltyID)->select('parentPersonaId')->first();
		$parentID = 0;
		if($parent!=null){ $parentID=$parent->parentPersonaId; }

		$IDs  = [$prsnltyID, $parentID];
		$QRYs = [];
		foreach($IDs as $ID){
			$tmpQRY = $this
				->with(['organization'])
				->leftJoin('personality as personality', 'personality_relation.personalityId', '=', 'personality.personalityId')
				->leftJoin('relation as relation', 'personality_relation.relationId', '=', 'relation.relationId')

				->leftJoin('term as lTerm', 'relation.leftTermId', '=', 'lTerm.termId')
				->leftJoin('term as rTerm', 'relation.rightTermId', '=', 'rTerm.termId')

				->leftJoin('relation_type as relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
				->where('personality_relation.personalityId', '=', $ID);
			if($ID==$IDs[0]){
				$tmpQRY->select(
					'personality_relation.*',
					'relation_type.relationTypeName as rTypeN',
					'lTerm.termName as lTermN',
					'rTerm.termName as rTermN',
					'personality.parentPersonaId as parentId',
					\DB::raw('CONCAT(lTerm.termName," ",relation_type.relationTypeName," ",rTerm.termName) as knowledgeRecords'),
					\DB::raw(
						"(".
							"select SUM(scalarValue) ".
							"FROM personality_relation_value ".
							"where personality_relation_value.personalityRelationId = personality_relation.personalityRelationId ".
						") as netRating"
					),
					\DB::raw('(if(personality.parentPersonaId=0, 0, 0)) as isParent'),
					\DB::raw("(if(personality.parentPersonaId=0, 0, 1)) as Personalized")
				);
			}else{
				$tmpQRY->select(
					'personality_relation.*',
					'relation_type.relationTypeName as rTypeN',
					'lTerm.termName as lTermN',
					'rTerm.termName as rTermN',
					'personality.parentPersonaId as parentId',
					\DB::raw('CONCAT(lTerm.termName," ",relation_type.relationTypeName," ",rTerm.termName) as knowledgeRecords'),
					\DB::raw(
						"(".
							"select SUM(scalarValue) ".
							"FROM personality_relation_value ".
							"where personality_relation_value.personalityRelationId = personality_relation.personalityRelationId ".
						") as netRating"
					),
					\DB::raw("(if(personality.parentPersonaId=0, '{$IDs[0]}', 0)) as isParent"),
					\DB::raw("(if(personality.parentPersonaId=0, 0, 1)) as Personalized")
				);
			}
			$QRYs[] = $tmpQRY;
		}
		if(isset($QRYs[1])){
			$relationId=[];
			$tmps = $QRYs[0]->get();
			foreach($tmps as $tmp){ $relationId[] = $tmp->relationId; }
			$QRYs[1]->whereNotIn('personality_relation.relationId', $relationId);
			return $QRYs[0]->unionAll($QRYs[1]);
		}else{ return $QRYs[0]; }
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $prsnltyID, $ownerID, $perPage, $page, $sort, $order){
		$data = null;
		$data = $this->myQuery($orgID, $prsnltyID, $ownerID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage);
		/*
		switch($order){
			case 'asc' :{ $data = $this->myQuery($orgID, $prsnltyID, $ownerID, '', '')->get()->sortBy($sort)->forPage($page, $perPage); break; }
			case 'desc':{ $data = $this->myQuery($orgID, $prsnltyID, $ownerID, '', '')->get()->sortByDesc($sort)->forPage($page, $perPage); break; }
		}
		*/
		if($data->isEmpty()){ return null; }
		$retVal = [];
		foreach( $data as $tmp ){
			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
	protected function myPageingWithSearch($orgID, $prsnltyID, $ownerID, $perPage, $page, $sort, $order, $field, $value){
		$data = null;
		$data = $this->myQuery($orgID, $prsnltyID, $ownerID, $field, $value)->orderBy($sort)->get()->forPage($page, $perPage);
		/*
		switch($order){
			case 'asc' :{ $data = $this->myQuery($orgID, $prsnltyID, $ownerID, $field, $value)->get()->sortBy($sort)->forPage($page, $perPage); break; }
			case 'desc':{ $data = $this->myQuery($orgID, $prsnltyID, $ownerID, $field, $value)->get()->sortByDesc($sort)->forPage($page, $perPage); break; }
		}
		*/
//		if($data->isEmpty()){ return null; }
		if($data==null){ return null; }
		$retVal = [];
		foreach( $data as $tmp ){
			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }
	//--------------------------------------------------------------------
	protected function knowledgeRecord($rID){
		return $this
			->leftJoin('relation as relation', 'personality_relation.relationId', '=', 'relation.relationId')

			->leftJoin('term as lTerm', 'relation.leftTermId', '=', 'lTerm.termId')
			->leftJoin('term as rTerm', 'relation.rightTermId', '=', 'rTerm.termId')

			->leftJoin('relation_type as relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
			->where('personality_relation.personalityRelationId', '=', $rID)
			->select(
				\DB::raw('CONCAT(lTerm.termName," ",relation_type.relationTypeName," ",rTerm.termName) as knowledgeRecord'),
				'relation.relationID as krId'
			);
	}
	//--------------------------------------------------------------------
/**/
	protected function myNewQuery($orgID, $prsnltyID, $parentID=null){
		if($parentID==null){
			$parent = \App\Personality::where('personalityId', $prsnltyID)->select('parentPersonaId')->first();
			$parentID = 0;
			if($parent!=null){ $parentID=$parent->parentPersonaId; }
		}
		$IDs  = [$prsnltyID, $parentID];
		$QRYs = [];
		foreach($IDs as $ID){
			$tmpQRY = $this
				->with(['organization'])
				->leftJoin('personality as personality', 'personality_relation.personalityId', '=', 'personality.personalityId')
				->leftJoin('relation as relation', 'personality_relation.relationId', '=', 'relation.relationId')

				->leftJoin('term as lTerm', 'relation.leftTermId', '=', 'lTerm.termId')
				->leftJoin('term as rTerm', 'relation.rightTermId', '=', 'rTerm.termId')

				->leftJoin('relation_type as relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
				->where('personality_relation.personalityId', '=', $ID);
			if($ID==$IDs[0]){
				$tmpQRY->select(
					'personality_relation.*',
					'relation_type.relationTypeName as rTypeN',
					'lTerm.termName as lTermN',
					'rTerm.termName as rTermN',
					'personality.parentPersonaId as parentId',
					\DB::raw('CONCAT(lTerm.termName," ",relation_type.relationTypeName," ",rTerm.termName) as knowledgeRecords'),
					\DB::raw(
						"(".
							"select SUM(scalarValue) ".
							"FROM personality_relation_value ".
							"where personality_relation_value.personalityRelationId = personality_relation.personalityRelationId ".
						") as netRating"
					),
					\DB::raw('(if(personality.parentPersonaId=0, 0, 0)) as isParent'),
					\DB::raw("(if(personality.parentPersonaId=0, 0, 1)) as Personalized")
				);
			}else{
				$tmpQRY->select(
					'personality_relation.*',
					'relation_type.relationTypeName as rTypeN',
					'lTerm.termName as lTermN',
					'rTerm.termName as rTermN',
					'personality.parentPersonaId as parentId',
					\DB::raw('CONCAT(lTerm.termName," ",relation_type.relationTypeName," ",rTerm.termName) as knowledgeRecords'),
					\DB::raw(
						"(".
							"select SUM(scalarValue) ".
							"FROM personality_relation_value ".
							"where personality_relation_value.personalityRelationId = personality_relation.personalityRelationId ".
						") as netRating"
					),
					\DB::raw("(if(personality.parentPersonaId=0, '{$IDs[0]}', 0)) as isParent"),
					\DB::raw("(if(personality.parentPersonaId=0, 0, 1)) as Personalized")
				);
			}
			$QRYs[] = $tmpQRY;
		}
		if(isset($QRYs[1])){
			$relationId=[];
			$tmps = $QRYs[0]->get();
			foreach($tmps as $tmp){ $relationId[] = $tmp->relationId; }
			$QRYs[1]->whereNotIn('personality_relation.relationId', $relationId);
			return $QRYs[0]->unionAll($QRYs[1]);
		}else{ return $QRYs[0]; }
	}
/**/
	//--------------------------------------------------------------------
}
