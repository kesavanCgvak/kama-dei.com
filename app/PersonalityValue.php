<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonalityValue extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'personality_value';
	protected $primaryKey = "personalityValueId";
//	protected $dates      = ['dateCreated'];
	//--------------------------------------------------------------------
	protected function myPersonalityValue($orgID, $prsnltyID, $ownerID, $field, $value){
		$parent = \App\Personality::where('personalityId', $prsnltyID)->select('parentPersonaId')->first();
		$parentID = 0;
		if($parent!=null){ $parentID=$parent->parentPersonaId; }
		$IDs  = [$prsnltyID, $parentID];
		$QRYs = [];
		foreach($IDs as $ID){
//			if($ID==0){ continue; }
			$tmpQRY = $this
				->with(['organization'])
				->leftJoin('personality as personality', 'personality_value.personalityId', '=', 'personality.personalityId')
				->leftJoin('term as term', 'personality_value.personTermId', '=', 'term.termId')
				->where('personality_value.personalityId', '=', $ID)
				->where(function($q) use($ownerID, $orgID, $value){ 
						$qry = $q;
						if($ownerID==-1){ 
							if($orgID==0){ $qry = $q; }
							else{ $qry = $q->whereIn('personality_value.ownerId', [$orgID, 0, null]); } 
						}else{
							if($ownerID== 0){ 
								if($orgID==0){ $qry = $q; }
								else{ $qry = $q->whereIn('personality_value.ownerId', [$ownerID, 0, null]); } 
							}
							else{ 
								if($orgID==0){ $qry = $q; }
								else{ $qry = $q->whereIn('personality_value.ownerId', [$ownerID, 0, null]); } 
							}
						}
						if($value==''){ return $qry; }
						else{
							return $qry->where(function($qq) use($value){ return $qq->where('term.termName', 'like', "%{$value}%"); });
						}
				});
			if($ID==$IDs[0]){
				$tmpQRY->select(
					'personality_value.*',
					'term.termName as personTermName',
					'personality.personalityName as personalityName',
					\DB::raw('(if(personality.parentPersonaId=0, 0, 0)) as isParent'),
					\DB::raw("(if(personality_value.personalityId={$prsnltyID}, 1, 0)) as personalized")
					//\DB::raw('(if(personality_value.ownerId=0 or personality_value.ownerId is null, 0, 1)) as personalized')
				);
			}else{
				$tmpQRY->select(
					'personality_value.*',
					'term.termName as personTermName',
					'personality.personalityName as personalityName',
					\DB::raw("(if(personality.parentPersonaId=0, '{$IDs[0]}', 0)) as isParent"),
					\DB::raw("(if(personality_value.personalityId={$prsnltyID}, 1, 0)) as personalized")
					//\DB::raw('(if(personality_value.ownerId=0 or personality_value.ownerId is null, 0, 1)) as personalized')
				);
			}
			$QRYs[] = $tmpQRY;
		}
		if(isset($QRYs[1])){
			$personTermId=[];
			$tmps = $QRYs[0]->get();
			foreach($tmps as $tmp){ $personTermId[] = $tmp->personTermId; }
			$QRYs[1]->whereNotIn('personTermId', $personTermId);
			return $QRYs[0]->unionAll($QRYs[1]);
		}else{ return $QRYs[0]; }
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $prsnltyID, $ownerID, $perPage, $page, $sort, $order){
		$data = null;
		$data = $this->myPersonalityValue($orgID, $prsnltyID, $ownerID, '', '')
						->orderBy($sort, $order)->get()->forPage($page, $perPage);
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
		$data = $this->myPersonalityValue($orgID, $prsnltyID, $ownerID, $field, $value)
						->orderBy($sort)->get()->forPage($page, $perPage);
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
}
