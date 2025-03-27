<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkKrToTerm extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_term_link';
	protected $primaryKey = "relationTermLinkId";
	//--------------------------------------------------------------------
	public static function myData($orgID, $value, $ownerId=-1, $showGlobal=1){
		$kamadei = env('BASE_ORGANIZATION');
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE = \Config::get('kama_dei.static.PRIVATE'  ,2);

		return self::where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId, $showGlobal){ 
				if($showGlobal==0){ return $q->where('relation_term_link.ownerId', $ownerId); }
				if($orgID==0 && $ownerId==-1){ return $q; }
				else{
					$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
					$q = $q
						->where('relation_term_link.ownership', $PUBLIC)
						->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
							return $q
								->whereIn('relation_term_link.ownerId', $tmpOrgIDs)
								->where('relation_term_link.ownership', $PRTCTD);
						});
					if($orgID==$ownerId || $ownerId==-1){ return $q->orWhere('relation_term_link.ownerId', $orgID); }
					if($orgID==0 && $ownerId!=-1){ return $q->orWhere('relation_term_link.ownerId', $ownerId); }
					return $q;
				}
			})
			->where(function($q) use($value, $kamadei){
				if($value=='') return $q;
				return $q
					->where(
						\DB::raw("if(relation_term_link.ownerId=0 or relation_term_link.ownerId is null, '{$kamadei}', org.organizationShortName)"),
						'like', 
						"%{$value}%"
					)
					->orWhere('term.termName', 'like', "%{$value}%")
					->orWhere('linkType.termName', 'like', "%{$value}%")
					->orWhere('leftTerm.termName', 'like', "%{$value}%")
					->orWhere('relation_type.relationTypeName', 'like', "%{$value}%")
					->orWhere('rightTerm.termName', 'like', "%{$value}%");
			})
			->leftJoin("relation", "relation_term_link.relationId", "=", "relation.relationId")
			->leftJoin("term as leftTerm", "relation.leftTermId", "=", "leftTerm.termId")
			->leftJoin("term as rightTerm", "relation.rightTermId", "=", "rightTerm.termId")
			->leftJoin("relation_type", "relation.relationTypeId", "=", "relation_type.relationTypeId")
			->leftJoin("term", "relation_term_link.termId", "=", "term.termId")
			->leftJoin("term as linkType", "relation_term_link.krtermLinkId", "=", "linkType.termId")
			->leftJoin("kamadeiep.organization_ep as org", "relation_term_link.ownerId", "=", "org.organizationId")
			->select(
				"relation_term_link.*",
				"term.termName as termName",
				"linkType.termName as linkTypeName",
				\DB::raw("concat(leftTerm.termName,' ',relation_type.relationTypeName,' ',rightTerm.termName) as knowledgeRecord"),
				\DB::raw("(if(relation_term_link.ownerId is null or relation_term_link.ownerId=0, '{$kamadei}', org.organizationShortName)) as orgName")
			);
	}
	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	protected function myRelation($orgID, $field, $value, $ownerId=-99){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE = \Config::get('kama_dei.static.PRIVATE'  ,2);
		if( $value=='' ){
			return $this
//				->with(['organization'])
				->leftJoin('kamadeiep.organization_ep as org', 'relation.ownerId', '=', 'org.organizationId')
				->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
				->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
				->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){ 
						if($orgID==0 && $ownerId==-99){ return $q; }
						else{
							$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
							$q = $q
								->where('relation.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('relation.ownerId', $tmpOrgIDs)->where('relation.ownership', $PRTCTD);
								});
							if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('relation.ownerId', $orgID); }
							if($orgID==0 && $ownerId!=-99){ return $q->orWhere('relation.ownerId', $ownerId); }
							return $q;
						}
				})
				->select(
					'relation.*',
					'leftTerm.termName as leftTermName',
					'rightTerm.termName as rightTermName',
					'relation_type.relationTypeName as relationTypeName',
					\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecordName'),
					\DB::raw('( select count(*) from `relation_link` where `relation_link`.`leftRelationId` = `relation`.`relationId`) as linkingKR'),
					\DB::raw('( select count(*) from extended_link exln where exln.parentTable=2 and exln.parentId = relation.relationId) as extDataLink'),
					\DB::raw("if(relation.ownerId=0 or relation.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName) as organizationShortName"),
					\DB::raw("if(relation.ownership=0, 'Public', if(relation.ownership=1, 'Protected', 'Private' )) as ownerShipText"),
					\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecords')
				);
		}else{
			if($field != 'allFields'){ 
				return $this
//					->with(['organization'])
					->leftJoin('kamadeiep.organization_ep as org', 'relation.ownerId', '=', 'org.organizationId')
					->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
					->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
					->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
					->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){ 
						if($orgID==0 && $ownerId==-99){ return $q; }
						else{
							$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
							$q = $q
								->where('relation.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('relation.ownerId', $tmpOrgIDs)->where('relation.ownership', $PRTCTD);
								});
							if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('relation.ownerId', $orgID); }
							if($orgID==0 && $ownerId!=-99){ return $q->orWhere('relation.ownerId', $ownerId); }
							return $q;
						}
					})
					->where($field, 'like', "%{$value}%")
					->select(
						'relation.*',
						'leftTerm.termName as leftTermName',
						'rightTerm.termName as rightTermName',
						'relation_type.relationTypeName as relationTypeName',
						\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecordName'),
						\DB::raw('( select count(*) from `relation_link` where `relation_link`.`leftRelationId` = `relation`.`relationId`) as linkingKR'),
						\DB::raw('( select count(*) from extended_link exln where exln.parentTable=2 and exln.parentId = relation.relationId) as extDataLink'),
						\DB::raw("if(relation.ownerId=0 or relation.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName) as organizationShortName"),
						\DB::raw("if(relation.ownership=0, 'Public', if(relation.ownership=1, 'Protected', 'Private' )) as ownerShipText"),
						\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecords')
					);
			}else{
				return $this
//					->with(['organization'])
					->leftJoin('kamadeiep.organization_ep as org', 'relation.ownerId', '=', 'org.organizationId')
					->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
					->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
					->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
					->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){ 
						if($orgID==0 && $ownerId==-99){ return $q; }
						else{
							$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
							$q = $q
								->where('relation.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('relation.ownerId', $tmpOrgIDs)->where('relation.ownership', $PRTCTD);
								});
							if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('relation.ownerId', $orgID); }
							if($orgID==0 && $ownerId!=-99){ return $q->orWhere('relation.ownerId', $ownerId); }
							return $q;
						}
					})
					->where(
						function($q) use ($value){ 
							return $q
								->where(
									\DB::raw("if(relation.ownerId=0 or relation.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName)"),
									'like', 
									"%{$value}%"
								)
								->orwhere('leftTerm.termName' , 'like', "%{$value}%")
								->orwhere('rightTerm.termName', 'like', "%{$value}%")
								->orwhere('relationTypeName'  , 'like', "%{$value}%")
								->orwhere('shortText'         , 'like', "%{$value}%")
								
								->orwhere(
									\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName)') , 
									'like', 
									"%{$value}%"
								)
								->orwhere(
									\DB::raw("if(relation.ownership=0, 'Public', if(relation.ownership=1, 'Protected', 'Private' ))") , 
									'like', 
									"%{$value}%"
								);
						}
					)
					->select(
						'relation.*',
						'leftTerm.termName as leftTermName',
						'rightTerm.termName as rightTermName',
						'relation_type.relationTypeName as relationTypeName',
						\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecordName'),
						\DB::raw('( select count(*) from `relation_link` where `relation_link`.`leftRelationId` = `relation`.`relationId`) as linkingKR'),
						\DB::raw('( select count(*) from extended_link exln where exln.parentTable=2 and exln.parentId = relation.relationId) as extDataLink'),
						\DB::raw("if(relation.ownerId=0 or relation.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName) as organizationShortName"),
						\DB::raw("if(relation.ownership=0, 'Public', if(relation.ownership=1, 'Protected', 'Private' )) as ownerShipText"),
						\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecords')
					);
			}
		}
	}
	public function myRelationOLD(){
		return $this
			->with(['organization'])
			->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
			->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
			->select(
					'relation.*',
					'leftTerm.termName as leftTermName',
					'rightTerm.termName as rightTermName',
					'relation_type.relationTypeName as relationTypeName'
				);
	}
	//--------------------------------------------------------------------
	public function myRelationWhere($value, $orgID){
		if($orgID==0){
			return $this
				->with(['organization'])
				->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
				->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
				->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
				
				->orwhere('leftTerm.termName' , 'like', "%{$value}%")
				->orWhere('rightTerm.termName', 'like', "%{$value}%")
				->orWhere('relationTypeName'  , 'like', "%{$value}%")
				
				->select(
						'relation.*',
						'leftTerm.termName as leftTermName',
						'rightTerm.termName as rightTermName',
						'relation_type.relationTypeName as relationTypeName'
					);
		}else{
			return 
			$this
				->with(['organization'])
				->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
				->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
				->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
				->whereIn('relation.ownerId', [ $orgID, null])
				->where(function($q) use ($value){ 
					return $q
						->where('leftTerm.termName' , 'like', "%{$value}%")
						->orwhere('rightTerm.termName' , 'like', "%{$value}%")
						->orwhere('relationTypeName' , 'like', "%{$value}%");
					})
				->select(
						'relation.*',
						'leftTerm.termName as leftTermName',
						'rightTerm.termName as rightTermName',
						'relation_type.relationTypeName as relationTypeName'
					);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $ownerId){
		//----------------------------------------------------------------
		$data = null;
		//----------------------------------------------------------------
		switch($ownerId){
			case -1: $data = $this->myRelation($orgID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			case  0: $data = $this->myRelation($orgID, '', '')->where('relation.ownerId', null)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			default: $data = $this->myRelation($orgID, '', '')->where('relation.ownerId', $ownerId)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
		}
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $tmp ){
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
		//----------------------------------------------------------------
		$data = null;
		//----------------------------------------------------------------
		switch($ownerId){
			case -1: $data = $this->myRelation($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			case  0: $data = $this->myRelation($orgID, $field, $value)->where('relation.ownerId', null)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			default: $data = $this->myRelation($orgID, $field, $value)->where('relation.ownerId', $ownerId)->orderBy($sort, $order)->get()->forPage($page, $perPage); 
					break;
		}
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $tmp ){
			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
    public function organization(){ return $this->belongsTo('App\Organization', 'ownerId', 'organizationId'); }
    public function organ(){ return $this->hasOne('App\Organization', 'ownerId', 'organizationId'); }
	//--------------------------------------------------------------------
    public function termLeft(){ return $this->belongsTo('App\Term', 'leftTermId', 'termId'); }
	//--------------------------------------------------------------------
    public function termRight(){ return $this->belongsTo('App\Term', 'rightTermId', 'termId'); }
	//--------------------------------------------------------------------
    public function relationType(){ return $this->belongsTo('App\RelationType', 'relationTypeId', 'relationTypeId'); }
	//--------------------------------------------------------------------
	public function allKnowledgeRecords($orgID, $prsnaID){
		return $this
			->with(['organization'])
			->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
			->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')

			->whereNotIn('relationId', function($q) use($orgID, $prsnaID){
				$tmp = new \App\PersonalityRelation;
				if($orgID !=0){
					return $q
							->select('relationId')
							->from($tmp->getTable())
							->where('personalityId',$prsnaID);
				}else{
					return $q
							->select('relationId')
							->from($tmp->getTable())
							->where('personalityId',$prsnaID);
				}
			})
			->select(
				'relation.*',
				\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecords')
			);
	}
	//--------------------------------------------------------------------
	public function allKnowledgeRecordsWithOwner($orgID, $prsnaID, $ownrID){
		return $this
			->with(['organization'])
			->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
			->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')

			->whereNotIn('relationId', function($q) use($orgID, $prsnaID, $ownrID){
				$tmp = new \App\PersonalityRelation;
				return $q
						->select('relationId')
						->from($tmp->getTable())
						->where('personalityId',$prsnaID)
						->where('ownerId',$orgID);
			})
			->select(
				'relation.*',
				\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecords')
			);
	}
	//--------------------------------------------------------------------
	public function knowledgeRecordsAll(){
		return $this
			->with(['organization'])
			->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
			->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
			->select(
				'relation.*',
				\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecords')
			);
	}
	//--------------------------------------------------------------------
	public function relationGroupTypes($orgID, $rightTermId, $relationTypeId, $field='', $value=''){
		return $this
			->with(['organization'])
			->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
			->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
			->where('relation.rightTermId', $rightTermId)
			->where('relation.relationTypeId', $relationTypeId)
			->where(function($q) use($orgID){
				if($orgID !=0){ return $q->where('relation.ownerId', $orgID); }
				else{ return $q; }
			})
			->where(function($q) use($value){
				if( trim($value)!='' ){
					return $q
							->whereRaw("leftTerm.termName like ?", ["%{$value}%"])
							->orWhereRaw("rightTerm.termName like ?", ["%{$value}%"])
							->orWhereRaw("relation_type.relationTypeName like ?", ["%{$value}%"]);
				}else{ return $q; }
			})
			->select(
				'relation.*',
				\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as relationGroupType')
			);
	}
	//--------------------------------------------------------------------
	public function relationGroupTypesPaging($orgID, $rightTermId, $relationTypeId, $perPage, $page, $sort, $order, $field='', $value=''){
		//----------------------------------------------------------------
		$data = null;
		//----------------------------------------------------------------
		$data = $this->relationGroupTypes($orgID, $rightTermId, $relationTypeId, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $tmp ){
			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$retVal[] = $tmp;
		}
		//----------------------------------------------------------------
		return $retVal;
		//----------------------------------------------------------------
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
			->leftJoin('kamadeiep.organization_ep', 'kamadeikb.relation.ownerId', '=', 'kamadeiep.organization_ep.organizationId')
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD){ 
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->where('relation.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('relation.ownerId', $tmpOrgIDs)->where('relation.ownership', $PRTCTD);
								})
								->orWhere('relation.ownerId', $orgID);
					}
			})
			->groupBy('relation.ownerId')
			->select(
				\DB::raw("if(relation.ownerId=0 or relation.ownerId is null, 0, relation.ownerId) as id"),
				\DB::raw("if(relation.ownerId=0 or relation.ownerId is null, '".env('BASE_ORGANIZATION')."', organization_ep.organizationShortName) as text")
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
	protected function myRelationNew($orgID, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1){
		$PRIVATE = \Config::get('kama_dei.static.PRIVATE',2);
//		$tmp = $this->myRelation($orgID, $field, $value)->orderBy($sort, $order);

		switch($ownerId){
			case -1: $tmp = $this->myRelation($orgID, $field, $value)->orderBy($sort, $order); break;
			default: $tmp = $this->myRelation($orgID, $field, $value, $ownerId)->orderBy($sort, $order); break;
		}
		if($tmp==null){ return null; }
		if($orgID!=$ownerId && $ownerId!=-1 && $orgID!=0){ $tmp = $tmp->where('relation.ownership', '<>', $PRIVATE); }
		if($shwglblSTT==1){ return $tmp; }
		switch($ownerId){
			case -1: return $tmp->where('relation.ownerId', $orgID);
			case  0: return $tmp->where('relation.ownerId', null);
			default: return $tmp->where('relation.ownerId', $ownerId);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageingNew($orgID, $perPage, $page, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1){
		//----------------------------------------------------------------
		$tmp = $this->myRelationNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT);
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
	public function haveTerm($term_id, $side_enum){
		$side_enum = strtoupper($side_enum);
		return $this
			->leftJoin('kamadeiep.organization_ep as ORG', 'relation.ownerId'       , '=', 'ORG.organizationId')
			->leftJoin('term as leftTerm'                , 'relation.leftTermId'    , '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm'               , 'relation.rightTermId'   , '=', 'rightTerm.termId')
			->leftJoin('relation_type'                   , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
			->where(function($q) use($term_id, $side_enum){ 
				switch($side_enum){
					case 'L': return $q->where('relation.leftTermId' , $term_id);
					case 'R': return $q->where('relation.rightTermId', $term_id);
					case 'LR':
					case 'RL':
						return $q->where('relation.leftTermId', $term_id)->orwhere('relation.rightTermId', $term_id);
					default: return $q;
				}
			})
			->select(
				'relation.*',
				'leftTerm.termName as leftTermName',
				'rightTerm.termName as rightTermName',
				'relation_type.relationTypeName as relationTypeName',
				\DB::raw("IF(ORG.organizationShortName is not null, ORG.organizationShortName, '".env('BASE_ORGANIZATION')."') as organizationShortName"),
				\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecordName')
			);
	}
	//--------------------------------------------------------------------
}