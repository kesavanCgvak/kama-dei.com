<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelationType extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_type';
	protected $primaryKey = "relationTypeId";
	protected $modifiers  = ['relationTypeId', 'relationTypeName', 'relationTypeIsReserved', 'relationTypeOperand', 'ownership', 'ownerId', 'dateCreated','lastUserId'];
//	protected $dates      = ['dateCreated'];
	//--------------------------------------------------------------------
	protected function myelationTypes($orgID, $field, $value, $ownerId=-99){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE = \Config::get('kama_dei.static.PRIVATE'  ,2);
		if( $value=='' ){
			return $this
//				->with(['organization'])
				->leftJoin('kamadeiep.organization_ep as org', 'relation_type.ownerId', '=', 'org.organizationId')
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){
					if($orgID==0 && $ownerId==-99){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						$q = $q
							->where('relation_type.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
								return $q->whereIn('relation_type.ownerId', $tmpOrgIDs)->where('relation_type.ownership', $PRTCTD);
							});
						if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('relation_type.ownerId', $orgID); }
						if($orgID==0 && $ownerId!=-99){ return $q->orWhere('relation_type.ownerId', $ownerId); }
						return $q;
					}
				})
				->select(
					"relation_type.*",
					\DB::raw("if(relation_type.ownerId=0 or relation_type.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName) as organizationShortName"),
					\DB::raw("if(relation_type.ownership=0, 'Public', if(relation_type.ownership=1, 'Protected', 'Private' )) as ownerShipText")
				);
		}else{
			return $this
//				->with(['organization'])
				->leftJoin('kamadeiep.organization_ep as org', 'relation_type.ownerId', '=', 'org.organizationId')
//				->where($field, 'like', "%{$value}%")
				->where(function($q) use($value){
					return $q
						->where(
							\DB::raw("if(relation_type.ownerId=0 or relation_type.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName)"),
							'like', 
							"%{$value}%"
						)
						->orWhereRaw("relation_type.relationTypeName like ?"       , ["%{$value}%"])
						->orWhereRaw("relation_type.relationTypeDescription like ?", ["%{$value}%"])
						->orwhere(
							\DB::raw("if(relation_type.ownership=0,'Public',if(relation_type.ownership=1,'Protected','Private'))") , 
							'like', 
							"%{$value}%"
						);
				})
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){
					if($orgID==0 && $ownerId==-99){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						$q = $q
							->where('relation_type.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
								return $q->whereIn('relation_type.ownerId', $tmpOrgIDs)->where('relation_type.ownership', $PRTCTD);
							});
						if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('relation_type.ownerId', $orgID); }
						if($orgID==0 && $ownerId!=-99){ return $q->orWhere('relation_type.ownerId', $ownerId); }
						return $q;
					}
				})
				->select(
					"relation_type.*",
					\DB::raw("if(relation_type.ownerId=0 or relation_type.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName) as organizationShortName"),
					\DB::raw("if(relation_type.ownership=0, 'Public', if(relation_type.ownership=1, 'Protected', 'Private' )) as ownerShipText")
				);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $ownerId){
		$data = null;
		switch($ownerId){
			case -1: $data = $this->myelationTypes($orgID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			case  0: $data = $this->myelationTypes($orgID, '', '')->where('ownerId', null)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			default: $data = $this->myelationTypes($orgID, '', '')->where('ownerId', $ownerId)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
		}
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
	protected function myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId){
		$data = null;
		switch($ownerId){
			case -1: $data = $this->myelationTypes($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			case  0: $data = $this->myelationTypes($orgID, $field, $value)->where('ownerId', null)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			default: $data = $this->myelationTypes($orgID, $field, $value)->where('ownerId', $ownerId)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
		}
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
    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }
	//--------------------------------------------------------------------
	protected function ownersList($orgID){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		return $this
			->with(['organization'])
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD){ 
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->where('ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){ return $q->whereIn('ownerId', $tmpOrgIDs)->where('ownership', $PRTCTD); })
								->orWhere('ownerId', $orgID);
					}
			})
			->groupBy('ownerId')
			->select('ownerId');
	}
	//--------------------------------------------------------------------
	protected function getOwnersList($orgID){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$data = $this
			->leftJoin('kamadeiep.organization_ep', 'kamadeikb.relation_type.ownerId', '=', 'kamadeiep.organization_ep.organizationId')
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD){ 
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->where('relation_type.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('relation_type.ownerId', $tmpOrgIDs)->where('relation_type.ownership', $PRTCTD);
								})
								->orWhere('relation_type.ownerId', $orgID);
					}
			})
			->groupBy('relation_type.ownerId')
			->select(
				\DB::raw("if(relation_type.ownerId=0 or relation_type.ownerId is null, 0, relation_type.ownerId) as id"),
				\DB::raw("if(relation_type.ownerId=0 or relation_type.ownerId is null, '".env('BASE_ORGANIZATION')."', organization_ep.organizationShortName) as text")
			)
			->orderBy("text", "asc")
			->get()->toArray();
		
		$isTrue = false;
		foreach($data as $tmp){ if($tmp['id']==$orgID){ $isTrue=true; } }
		if($isTrue==false){
			if($orgID!=0){
				$tmp = \App\Organization::find($orgID);
				if($tmp!=null){
					$val         = [];
					$val['id']   = $orgID;
					$val['text'] = $tmp->organizationShortName; 
					$data[]      = $val;
				}
			}else{
				$val       = [];
				$val->id   = 0;
				$val->text = env('BASE_ORGANIZATION'); 
				$data[]    = $val;
			}
		}

		$isTrue = false;
		$i = 0;
		foreach($data as $tmp){
			if($tmp['id']==0 && $isTrue){
				$isTrue=false;
				break;
			}
			if($tmp['id']==0){ $isTrue=true; }
			$i++;
		}
		if(!$isTrue){ unset($data[$i]); }

		return $data;
	}
	//--------------------------------------------------------------------
	protected function myRelationTypesNew($orgID, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1){
		$PRIVATE = \Config::get('kama_dei.static.PRIVATE',2);
//		$tmp = $this->myelationTypes($orgID, $field, $value)->orderBy($sort, $order);

		switch($ownerId){
			case -1: $tmp = $this->myelationTypes($orgID, $field, $value)->orderBy($sort, $order); break;
			default: $tmp = $this->myelationTypes($orgID, $field, $value, $ownerId)->orderBy($sort, $order); break;
		}

		if($tmp==null){ return null; }
		if($orgID!=$ownerId && $ownerId!=-1 && $orgID!=0){ $tmp = $tmp->where('relation_type.ownership', '<>', $PRIVATE); }
		if($shwglblSTT==1){ return $tmp; }
		switch($ownerId){
			case -1: return $tmp->where('relation_type.ownerId', $orgID);
			case  0: return $tmp->where('relation_type.ownerId', null);
			default: return $tmp->where('relation_type.ownerId', $ownerId);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageingNew($orgID, $perPage, $page, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1){
		//----------------------------------------------------------------
		$tmp = $this->myRelationTypesNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT);
		//----------------------------------------------------------------
		if($tmp==null){ return null; }
		//----------------------------------------------------------------
		$data = $tmp->get()->forPage($page, $perPage);
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $key=>$tmp ){
			$tmp->organization = new \StdClass;
			$tmp->organization->organizationShortName = $tmp->organizationShortName;
			$retVal[] = $tmp;
		}
		return $retVal;
		//----------------------------------------------------------------
	}
	//--------------------------------------------------------------------
}
