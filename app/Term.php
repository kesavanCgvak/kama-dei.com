<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'term';
	protected $primaryKey = "termId";
	protected $modifiers  = ['termId', 'termName', 'termIsReserved', 'ownership', 'ownerId', 'dateCreated', 'lastUserId'];
//	protected $dates      = ['dateCreated'];
	//--------------------------------------------------------------------
/*
	protected $casts = [
		'dateCreated' => "datetime:Y-m-d",
	];
	//--------------------------------------------------------------------
*/
	protected function myTerms($orgID, $field, $value, $ownerId=-99){
		$PUBLIC  = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD  = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE  = \Config::get('kama_dei.static.PRIVATE'  ,2);
		$kamaDEI = env('BASE_ORGANIZATION');
		$is_a_member_of_ID = \Config::get('kama_dei.static.is_a_member_of_ID', 0);
		$LINKING_TERM_ID   = \Config::get('kama_dei.static.LINKING_TERM_ID'  , 0);
		$KR_TERM_LINKS_ID  = \Config::get('kama_dei.static.KR_TERM_LINKS_ID' , 0);
		$TERM_TENSE        = \Config::get('kama_dei.static.TERM_TENSE'       , 0);
		$TERM_VALUES_ID    = \Config::get('kama_dei.static.TERM_VALUES_ID'   , 0);
		$q3 = "rightTermId in ({$LINKING_TERM_ID},{$KR_TERM_LINKS_ID},{$TERM_TENSE},{$TERM_VALUES_ID})";
		$q2 = "termId in (SELECT leftTermId FROM `relation` WHERE {$q3} and relationTypeId={$is_a_member_of_ID})";
		$q1 = "termId in ({$LINKING_TERM_ID},{$KR_TERM_LINKS_ID},{$TERM_TENSE}, {$TERM_VALUES_ID})";
		$q0 = "select termId from term where {$q1} or {$q2}";
		$systemTerm = "if(termId in ({$q0}), 1, 0) as systemTerm";
		if( $value=='' ){
			return $this
//				->with(['organization'])
				->leftJoin('kamadeiep.organization_ep as org', 'term.ownerId', '=', 'org.organizationId')
//				->leftJoin('kamadeikb.term_type', 'term.termTypeId', '=', 'term_type.id')
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){
					if($orgID==0 && $ownerId==-99){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						$q = $q
							->where('term.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
								return $q->whereIn('term.ownerId', $tmpOrgIDs)->where('term.ownership', $PRTCTD);
							});
						if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('term.ownerId', $orgID); }
						if($orgID==0 && $ownerId!=-99){ return $q->orWhere('term.ownerId', $ownerId); }
						return $q;
					}
				})
				->select(
					"term.*",
//					"term_type.name as termTypeName",
					\DB::raw("if(term.ownerId=0 or term.ownerId is null, '{$kamaDEI}', org.organizationShortName) as organizationShortName"),
					\DB::raw("if(term.ownership=0, 'Public', if(term.ownership=1, 'Protected', 'Private' )) as ownerShipText"),
					\DB::raw($systemTerm)
				);
		}else{
			return $this
				->leftJoin('kamadeiep.organization_ep as org', 'term.ownerId', '=', 'org.organizationId')
//				->leftJoin('kamadeikb.term_type', 'term.termTypeId', '=', 'term_type.id')
				->where(function($q) use($value, $kamaDEI){
/**/
					return $q
						->where(
							\DB::raw("if(term.ownership=0, 'Public', if(term.ownership=1, 'Protected', 'Private' ))") ,
							'like',
							"%{$value}%"
						)
						->orWhereRaw("term.termName like ?", ["%{$value}%"]);
/**/
/*
					return $q
						->where(
							\DB::raw("if(term.ownerId=0 or term.ownerId is null, '{$kamaDEI}', org.organizationShortName)"),
							'like',
							"%{$value}%"
						)
						->orWhereRaw("term.termName like ?", ["%{$value}%"])
//						->orWhereRaw("term_type.name like ?", ["%{$value}%"])
						->orwhere(
							\DB::raw("if(term.ownership=0, 'Public', if(term.ownership=1, 'Protected', 'Private' ))") ,
							'like',
							"%{$value}%"
						);
*/
				})
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){
					if($orgID==0 && $ownerId==-99){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						$q = $q
							->where('term.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
								return $q->whereIn('term.ownerId', $tmpOrgIDs)->where('term.ownership', $PRTCTD);
							});
						if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('term.ownerId', $orgID); }
						if($orgID==0 && $ownerId!=-99){ return $q->orWhere('term.ownerId', $ownerId); }
						return $q;
					}
				})
				->select(
					"term.*",
//					"term_type.name as termTypeName",
					\DB::raw("if(term.ownerId=0 or term.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName) as organizationShortName"),
					\DB::raw("if(term.ownership=0, 'Public', if(term.ownership=1, 'Protected', 'Private' )) as ownerShipText"),
					\DB::raw($systemTerm)
				);
		}
	}
	//--------------------------------------------------------------------
	protected function myTermsByID($orgID, $id){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		return $this
			->with(['organization'])
			->where('termId', '=', $id)
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD){
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->where('ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){ return $q->whereIn('ownerId', $tmpOrgIDs)->where('ownership', $PRTCTD); })
								->orWhere('ownerId', $orgID);
					}
			});
	}
	protected function myTermsByName_HaveP($orgID, $termName, $sort, $order){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		return $this
			->with(['organization'])
			->where('termName', '<', $termName)
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
			->orderBy($sort, $order);
	}
	protected function myTermsByName_HaveN($orgID, $termName, $sort, $order){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		return $this
			->with(['organization'])
			->where('termName', '>', $termName)
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
			->orderBy($sort, $order);
	}
	protected function myTermsByName_P($orgID, $termName, $sort, $order, $pkgLen){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		return $this
			->with(['organization'])
			->where('termName', '<=', $termName)
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
			->orderBy($sort, 'desc')
			->limit($pkgLen);
	}
	protected function myTermsByName_N($orgID, $termName, $sort, $order, $pkgLen){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		return $this
			->with(['organization'])
			->where('termName', '>=', $termName)
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
			->orderBy($sort, $order)
			->limit($pkgLen);
	}
	//--------------------------------------------------------------------
	protected function getTermsAroundMe($orgID, $termName, $sort, $order, $pkgLen, $ownerId=-1){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$retVal = [];
		$tmp1 = $this
			->with(['organization'])
			->where('termName', '<', $termName)
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
			->where(function($q) use($ownerId){
					switch($ownerId){
						case -1: return $q;
						case  0: return $q->where('ownerId', null);
						default; return $q->where('ownerId', $ownerId);
					}
			})
/*
			->whereRaw(
				"`termTypeId` in (select `id` from `term_type` where `id`=?)",
				[\Config('kama_dei.TermType.normal')]
			)
*/
//			->where("IsSystemTermOnly", 0)
			->orderBy($sort, 'desc')
			->limit($pkgLen)->get();
		$tmp2 = $this
			->with(['organization'])
			->where('termName', '=', $termName)
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
			->where(function($q) use($ownerId){
					switch($ownerId){
						case -1: return $q;
						case  0: return $q->where('ownerId', null);
						default; return $q->where('ownerId', $ownerId);
					}
			})
/*
			->whereRaw(
				"`termTypeId` in (select `id` from `term_type` where `id`=?)",
				[\Config('kama_dei.TermType.normal')]
			)
*/
//			->where("IsSystemTermOnly", 0)
			->orderBy($sort, 'asc')
			->get();
//			->limit(1)->get();
		$tmp3 = $this
			->with(['organization'])
			->where('termName', '>', $termName)
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
			->where(function($q) use($ownerId){
					switch($ownerId){
						case -1: return $q;
						case  0: return $q->where('ownerId', null);
						default; return $q->where('ownerId', $ownerId);
					}
			})
/*
			->whereRaw(
				"`termTypeId` in (select `id` from `term_type` where `id`=?)",
				[\Config('kama_dei.TermType.normal')]
			)
*/
//			->where("IsSystemTermOnly", 0)
			->orderBy($sort, 'asc')
			->limit($pkgLen)->get();
		foreach($tmp1 as $tmp){ array_unshift($retVal, $tmp); }
		foreach($tmp2 as $tmp){ $retVal[] = $tmp; }
		foreach($tmp3 as $tmp){ $retVal[] = $tmp; }
		return $retVal;
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $ownerId=-1){
		//----------------------------------------------------------------
		$data = null;
		//----------------------------------------------------------------
		switch($ownerId){
			case -1: $data = $this->myTerms($orgID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			case  0: $data = $this->myTerms($orgID, '', '')->where('ownerId', null)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			default: $data = $this->myTerms($orgID, '', '')->where('ownerId', $ownerId)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
		}
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $key=>$tmp ){
			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		//----------------------------------------------------------------
		return $retVal;
		//----------------------------------------------------------------
	}
	//--------------------------------------------------------------------
	protected function myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId){
		$data = null;
		switch($ownerId){
			case -1: $data = $this->myTerms($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			case  0: $data = $this->myTerms($orgID, $field, $value)->where('ownerId', null)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			default: $data = $this->myTerms($orgID, $field, $value)->where('ownerId', $ownerId)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
		}
		if($data->isEmpty()){ return null; }
		$retVal = [];
		foreach( $data as $key=>$tmp ){
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
	protected function findTermByID($orgID, $id){
		if($orgID==0){ return $this->with(['organization'])->where('termId', '=', $id)->get(); }
		else{ return $this->with(['organization'])->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where('termId', '=', $id)->get(); }
	}
	//--------------------------------------------------------------------
	public function findTerm($orgID, $field, $value){
		if($orgID==0){ return $this->with(['organization'])->where($field, 'like', "%{$value}%")->get(); }
		else{ return $this->with(['organization'])->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where($field, 'like', "%{$value}%")->get(); }
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
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('ownerId', $tmpOrgIDs)->where('ownership', $PRTCTD);
								})
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
			->leftJoin('kamadeiep.organization_ep', 'kamadeikb.term.ownerId', '=', 'kamadeiep.organization_ep.organizationId')
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD){
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->where('term.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('term.ownerId', $tmpOrgIDs)->where('term.ownership', $PRTCTD);
								})
								->orWhere('term.ownerId', $orgID);
					}
			})
			->groupBy('term.ownerId')
			->select(
				\DB::raw("if(term.ownerId=0 or term.ownerId is null, 0, term.ownerId) as id"),
				\DB::raw("if(term.ownerId=0 or term.ownerId is null, '".env('BASE_ORGANIZATION')."', organization_ep.organizationShortName) as text")
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
	protected function myTermsNew($orgID, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1, $showAllType=0){
		$PRIVATE = \Config::get('kama_dei.static.PRIVATE',2);
//		$tmp = $this->myTerms($orgID, $field, $value, $ownerId)->orderBy($sort, $order);

		switch($ownerId){
			case -1: $tmp = $this->myTerms($orgID, $field, $value)->orderBy($sort, $order); break;
			default: $tmp = $this->myTerms($orgID, $field, $value, $ownerId)->orderBy($sort, $order); break;
		}

		if($showAllType==0){ $tmp = $tmp->where('IsSystemTermOnly', 0); }

		if($tmp==null){ return null; }
		if($orgID!=$ownerId && $ownerId!=-1 && $orgID!=0){ $tmp = $tmp->where('term.ownership', '<>', $PRIVATE); }
		if($shwglblSTT==1){ return $tmp; }
		switch($ownerId){
			case -1: return $tmp->where('term.ownerId', $orgID);
			case  0: return $tmp->where('term.ownerId', null);
			default: return $tmp->where('term.ownerId', $ownerId);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageingNew($orgID, $perPage, $page, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1, $showAllType=0){
		//----------------------------------------------------------------
		$tmp = $this->myTermsNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT, $showAllType);
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
