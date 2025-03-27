<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonalityRelationValue extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'personality_relation_value';
	protected $primaryKey = "personalityRelationValueId";
//	protected $dates      = ['dateCreated'];
	//--------------------------------------------------------------------
	protected function myQuery($orgID, $personalityRelationId, $isParent=0){
		return $this
			->with(['organization'])
//			->leftJoin('personality as personality', 'personality_relation_value.personalityId', '=', 'personality.personalityId')
			->leftJoin('term as term', 'personality_relation_value.personRelationTermId', '=', 'term.termId')
			->where('personality_relation_value.personalityRelationId', '=', $personalityRelationId)
//			->where('personality_relation_value.personalityId', '=', $personalityId)

			->where(
				function($q) use($orgID){
				if($orgID==0){ return $q; }
				return $q
						->where('personality_relation_value.ownerID', '=', $orgID)
						->orWhere(function($q){ return $q->whereNull('personality_relation_value.ownerID')
						->where('personality_relation_value.ownership', 0); });
				}
			)

			->select(
				'personality_relation_value.*',
				'term.termName as value',
				\DB::raw("if(personality_relation_value.personRelationTermId=term.termId, '{$isParent}', 0) as isParent")
			);
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $prsnltyID, $ownerID, $perPage, $page, $sort, $order){
		$data = null;
		$data = $this->myQuery($orgID, $prsnltyID, $ownerID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage);
		if($data->isEmpty()){ return null; }
		$retVal = [];
		foreach( $data as $tmp ){
//			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
//			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
	protected function myPageingWithSearch($orgID, $prsnltyID, $ownerID, $perPage, $page, $sort, $order, $field, $value){
		$data = null;
		$data = $this->myQuery($orgID, $prsnltyID, $ownerID, $field, $value)->orderBy($sort)->get()->forPage($page, $perPage);
		if($data==null){ return null; }
		$retVal = [];
		foreach( $data as $tmp ){
//			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
//			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }
	//--------------------------------------------------------------------
}
