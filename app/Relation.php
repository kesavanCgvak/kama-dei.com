<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation';
	protected $primaryKey = "relationId";
	protected $modifiers  = [
							'leftTermId','leftTermName','relationTypeId','relationTypeName','rightTermId','rightTermName','relationOperand','ownership','ownerId',
							 'dateCreated','lastUserId'
							];
//	protected $dates      = ['dateCreated'];
	//--------------------------------------------------------------------
    protected function findByIdprotected($id){
        return $this
            ->with(['organization'])
            ->leftJoin('term as leftTerm' , 'relation.leftTermId'    , '=', 'leftTerm.termId')
            ->leftJoin('term as rightTerm', 'relation.rightTermId'   , '=', 'rightTerm.termId')
            ->leftJoin('relation_type'    , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
            ->where(
                function($q) use($id){
                    if($id==0){ return $q;}
                    else{ return $q->where('relation.relationId', '=', $id); }
                }
            )
            ->select(
                'relation.*',
                'leftTerm.termName as leftTermName',
                'rightTerm.termName as rightTermName',
                'relation_type.relationTypeName as relationTypeName',
                \DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecordName')
            )->get();
    }
    public function findById($id){
        return $this
            ->with(['organization'])
            ->leftJoin('term as leftTerm' , 'relation.leftTermId'    , '=', 'leftTerm.termId')
            ->leftJoin('term as rightTerm', 'relation.rightTermId'   , '=', 'rightTerm.termId')
            ->leftJoin('relation_type'    , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
            ->where(
                function($q) use($id){
                    if($id==0){ return $q;}
                    else{ return $q->where('relation.relationId', '=', $id); }
                }
            )
            ->select(
                'relation.*',
                'leftTerm.termName as leftTermName',
                'rightTerm.termName as rightTermName',
                'relation_type.relationTypeName as relationTypeName',
                \DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecordName')
            );
    }
	//--------------------------------------------------------------------------------------------------------------------------------
	protected function myRelation($orgID, $field, $value, $ownerId=-99){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE = \Config::get('kama_dei.static.PRIVATE'  ,2);
		$retQuery = $this
				->leftJoin('kamadeiep.organization_ep as org', 'relation.ownerId', '=', 'org.organizationId')
				->leftJoin('term as leftTerm' , 'relation.leftTermId'    , '=', 'leftTerm.termId')
				->leftJoin('term as rightTerm', 'relation.rightTermId'   , '=', 'rightTerm.termId')
				->leftJoin('relation_type'    , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
/*
				->leftJoin('relation_language', function($join){
					$join->on('relation.relationId', '=', 'relation_language.relationId')
						->on('relation_language.relationId', '=', \DB::raw("'en'"));
					
				})//bhr
*/
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){ 
						if($orgID==0 && $ownerId==-99){ return $q; }
						else{
							$tmpOrgIDs = OrgRelations::haveAccessTo((($orgID==0) ?$ownerId :$orgID));
							$q = $q
								->where('relation.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('relation.ownerId', $tmpOrgIDs)->where('relation.ownership', $PRTCTD);
								});
							if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('relation.ownerId', $orgID); }
							if($orgID==0 && $ownerId!=-99){ return $q->orWhere('relation.ownerId', $ownerId); }
							return $q;
						}
				});

		if( $value=='' ){
			$retQuery = $retQuery;
		}else{
			if($field != 'allFields'){ 
				$retQuery = $retQuery
					->where($field, 'like', "%{$value}%");
			}else{
				$retQuery = $retQuery
					->where(
						function($q) use ($value){ 
							return $q
								->where(
									\DB::raw("if(relation.ownerId=0 or relation.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName)"),
									'like', 
									"%{$value}%"
								)
								->orwhere('leftTerm.termName'          , 'like', "%{$value}%")
								->orwhere('rightTerm.termName'         , 'like', "%{$value}%")
								->orwhere('relationTypeName'           , 'like', "%{$value}%")
								
								->orwhere(
									\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName)') , 
									'like', 
									"%{$value}%"
								)
								->orwhere(
									\DB::raw("if(relation.ownership=0, 'Public', if(relation.ownership=1, 'Protected', 'Private' ))") , 
									'like', 
									"%{$value}%"
								)
								->orWhereRaw(
									"relationId in (select relationId from relation_language where optionalText like ? and language_code='en')",
									["%{$value}%"]
								);
						}
					);
			}
		}
		$ltn = "( select termName from term where relation.leftTermId = term.termId)";
		$rtn = "( select termName from term where relation.rightTermId = term.termId)";
		return $retQuery
				->select(
					'relation.*',
					'leftTerm.termName as leftTermName',
					'rightTerm.termName as rightTermName',
					'relation_type.relationTypeName as relationTypeName',
			
					\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecordName'),

					\DB::raw('(select count(*) from relation_link where relation_link.leftRelationId=relation.relationId) as linkingKR'),
					\DB::raw('( select count(*) from extended_link exln where exln.parentTable=2 and exln.parentId = relation.relationId) as extDataLink'),
					\DB::raw('( select count(*) from personality_relation pr where pr.relationId = relation.relationId) as PKR'),
					\DB::raw('( select count(*) from relation_term_link rtl where rtl.relationId = relation.relationId) as KRTermLink'),
			
					\DB::raw("if(relation.ownerId=0 or relation.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName) as organizationShortName"),
			
					\DB::raw("if(relation.ownership=0, 'Public', if(relation.ownership=1, 'Protected', 'Private' )) as ownerShipText"),
			
					\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecords'),

					\DB::raw('if(relation.ownerId is null, ( select optionalText from relation_language rl where rl.relationId = relation.relationId and rl.orgId is null and rl.language_code="en" limit 1), ( select optionalText from relation_language rl where rl.relationId = relation.relationId and rl.orgId=relation.ownerId and rl.language_code="en" limit 1))
					 as optionalText')
				);
	}
	//--------------------------------------------------------------------------------------------------------------------------------
	public function myRelationOLD(){
		return $this
			->with(['organization'])
			->leftJoin('term as leftTerm' , 'relation.leftTermId'    , '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm', 'relation.rightTermId'   , '=', 'rightTerm.termId')
			->leftJoin('relation_type'    , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
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
				->leftJoin('term as leftTerm' , 'relation.leftTermId'    , '=', 'leftTerm.termId')
				->leftJoin('term as rightTerm', 'relation.rightTermId'   , '=', 'rightTerm.termId')
				->leftJoin('relation_type'    , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')

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
				->leftJoin('term as leftTerm' , 'relation.leftTermId'    , '=', 'leftTerm.termId')
				->leftJoin('term as rightTerm', 'relation.rightTermId'   , '=', 'rightTerm.termId')
				->leftJoin('relation_type'    , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
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
			->leftJoin('term as leftTerm' , 'relation.leftTermId'    , '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm', 'relation.rightTermId'   , '=', 'rightTerm.termId')
			->leftJoin('relation_type'    , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')

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
			->leftJoin('term as leftTerm' , 'relation.leftTermId'    , '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm', 'relation.rightTermId'   , '=', 'rightTerm.termId')
			->leftJoin('relation_type'    , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')

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
			->leftJoin('term as leftTerm' , 'relation.leftTermId'    , '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm', 'relation.rightTermId'   , '=', 'rightTerm.termId')
			->leftJoin('relation_type'    , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
			->select(
				'relation.*',
				\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecords')
			);
	}
	//--------------------------------------------------------------------
	public function relationGroupTypes($orgID, $rightTermId, $relationTypeId, $field='', $value=''){
		return $this
			->with(['organization'])
			->leftJoin('term as leftTerm' , 'relation.leftTermId'    , '=', 'leftTerm.termId')
			->leftJoin('term as rightTerm', 'relation.rightTermId'   , '=', 'rightTerm.termId')
			->leftJoin('relation_type'    , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
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