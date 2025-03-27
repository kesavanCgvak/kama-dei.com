<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelationLink extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'relation_link';
	protected $primaryKey = "relationLinkId";
	protected $modifiers  = 
						['relationLinkId','leftRelationId','linkTermId','rightRelationId','linkTypeName','linkOrder','ownership','ownerId','dateCreated','lastUserId'];
//	protected $dates      = ['dateCreated'];
	//--------------------------------------------------------------------
	protected function myRelationLink($orgID, $field, $value, $ownerId=-99){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE = \Config::get('kama_dei.static.PRIVATE'  ,2);
		$leftStaticKR = \Session::get('leftStaticKR');
		if( $value=='' ){
			return $this
//				->with(['organization'])
				->leftJoin('kamadeiep.organization_ep as org', 'relation_link.ownerId', '=', 'org.organizationId')

				->leftJoin('relation as leftKR', 'relation_link.leftRelationId', '=', 'leftKR.relationId')
				->leftJoin('term as leftKRTermL', 'leftKR.leftTermId', '=', 'leftKRTermL.termId')
				->leftJoin('relation_type as leftKRRelationType', 'leftKR.relationTypeId', '=', 'leftKRRelationType.relationTypeId')
				->leftJoin('term as leftKRTermR', 'leftKR.rightTermId', '=', 'leftKRTermR.termId')

				->leftJoin('relation as rightKR', 'relation_link.rightRelationId', '=', 'rightKR.relationId')
				->leftJoin('term as rightKRTermL', 'rightKR.leftTermId', '=', 'rightKRTermL.termId')
				->leftJoin('relation_type as rightKRRelationType', 'rightKR.relationTypeId', '=', 'rightKRRelationType.relationTypeId')
				->leftJoin('term as rightKRTermR', 'rightKR.rightTermId', '=', 'rightKRTermR.termId')
				
				->leftJoin('term', 'relation_link.linkTermId', '=', 'term.termId')
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){ 
					if($orgID==0 && $ownerId==-99){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						$q = $q
							->where('relation_link.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
								return $q->whereIn('relation_link.ownerId', $tmpOrgIDs)->where('relation_link.ownership', $PRTCTD);
							});
						if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('relation_link.ownerId', $orgID); }
						if($orgID==0 && $ownerId!=-99){ return $q->orWhere('relation_link.ownerId', $ownerId); }
						return $q;
					}
				})
				->where(function($q) use($leftStaticKR){
					if($leftStaticKR==''){ return $q; }
//					else{ return$q->where('leftRelationId', $leftStaticKR)->orwhere('rightRelationId', $leftStaticKR); }
					else{ return$q->where('leftRelationId', $leftStaticKR); }
				})
				->select(
					'relation_link.*',
					\DB::raw('CONCAT(leftKRTermL.termName," ",leftKRRelationType.relationTypeName," ",leftKRTermR.termName) as leftKRName'),
					\DB::raw('CONCAT(rightKRTermL.termName," ",rightKRRelationType.relationTypeName," ",rightKRTermR.termName) as rightKRName'),
					
					'term.termName as termName',
					\DB::raw("if(relation_link.ownerId=0 or relation_link.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName) as organizationShortName"),
					\DB::raw("if(relation_link.ownership=0, 'Public', if(relation_link.ownership=1, 'Protected', 'Private' )) as ownerShipText"),
					\DB::raw("if((select count(*) from extended_link where parentTable=1 and parentId=relation_link.relationLinkId), 1, 0) as extDataLink ")
				);
		}else{
			return $this
//				->with(['organization'])
				->leftJoin('kamadeiep.organization_ep as org', 'relation_link.ownerId', '=', 'org.organizationId')
				
				->leftJoin('relation as leftKR', 'relation_link.leftRelationId', '=', 'leftKR.relationId')
				->leftJoin('term as leftKRTermL', 'leftKR.leftTermId', '=', 'leftKRTermL.termId')
				->leftJoin('relation_type as leftKRRelationType', 'leftKR.relationTypeId', '=', 'leftKRRelationType.relationTypeId')
				->leftJoin('term as leftKRTermR', 'leftKR.rightTermId', '=', 'leftKRTermR.termId')

				->leftJoin('relation as rightKR', 'relation_link.rightRelationId', '=', 'rightKR.relationId')
				->leftJoin('term as rightKRTermL', 'rightKR.leftTermId', '=', 'rightKRTermL.termId')
				->leftJoin('relation_type as rightKRRelationType', 'rightKR.relationTypeId', '=', 'rightKRRelationType.relationTypeId')
				->leftJoin('term as rightKRTermR', 'rightKR.rightTermId', '=', 'rightKRTermR.termId')
				
				->leftJoin('term', 'relation_link.linkTermId', '=', 'term.termId')

//					->leftJoin('relation as leftRelation', 'relation_link.leftRelationId', '=', 'leftRelation.relationId')
//					->leftJoin('relation as rightRelation', 'relation_link.rightRelationId', '=', 'rightRelation.relationId')
//					->leftJoin('term', 'relation_link.linkTermId', '=', 'term.termId')
//					->leftJoin('relation_type as rightRelationType', 'rightRelation.relationTypeId', '=', 'rightRelationType.relationTypeId')
//					->leftJoin('relation_type as leftRelationType', 'leftRelation.relationTypeId', '=', 'leftRelationType.relationTypeId')
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){ 
					if($orgID==0 && $ownerId==-99){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						$q = $q
							->where('relation_link.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
								return $q->whereIn('relation_link.ownerId', $tmpOrgIDs)->where('relation_link.ownership', $PRTCTD);
							});
						if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('relation_link.ownerId', $orgID); }
						if($orgID==0 && $ownerId!=-99){ return $q->orWhere('relation_link.ownerId', $ownerId); }
						return $q;
					}
				})
				->where(
					function($q) use ($value){ 
						return $q
							->where(
								\DB::raw("if(relation_link.ownerId=0 or relation_link.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName)"),
								'like', 
								"%{$value}%"
							)
							->orwhere('term.termName'        , 'like', "%{$value}%")
							->orwhere('leftKRTermR.termName' , 'like', "%{$value}%")
							->orwhere('rightKRTermL.termName', 'like', "%{$value}%")
							->orwhere('rightKRTermR.termName', 'like', "%{$value}%")
							->orwhere('leftKRRelationType.relationTypeName' , 'like', "%{$value}%")
							->orwhere('rightKRRelationType.relationTypeName', 'like', "%{$value}%")
							->orwhere('rightKRRelationType.relationTypeName', 'like', "%{$value}%")
							->orwhere(
								\DB::raw('CONCAT(leftKRTermL.termName," ",leftKRRelationType.relationTypeName," ",leftKRTermR.termName)') , 
								'like', 
								"%{$value}%"
							)
							->orwhere(
								\DB::raw('CONCAT(rightKRTermL.termName," ",rightKRRelationType.relationTypeName," ",rightKRTermR.termName)') , 
								'like', 
								"%{$value}%"
							)
							->orwhere(
								\DB::raw("if(relation_link.ownership=0, 'Public', if(relation_link.ownership=1, 'Protected', 'Private' ))") , 
								'like', 
								"%{$value}%"
							);
					}
				)
				->select(
					'relation_link.*',
//					'rightRelationType.relationTypeName as rightRelationTypeName',
//					'leftRelationType.relationTypeName as leftRelationTypeName',
					\DB::raw('CONCAT(leftKRTermL.termName," ",leftKRRelationType.relationTypeName," ",leftKRTermR.termName) as leftKRName'),
					\DB::raw('CONCAT(rightKRTermL.termName," ",rightKRRelationType.relationTypeName," ",rightKRTermR.termName) as rightKRName'),

					'term.termName as termName',
					\DB::raw("if(relation_link.ownerId=0 or relation_link.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName) as organizationShortName"),
					\DB::raw("if(relation_link.ownership=0, 'Public', if(relation_link.ownership=1, 'Protected', 'Private' )) as ownerShipText"),
					\DB::raw("if((select count(*) from extended_link where parentTable=1 and parentId=relation_link.relationLinkId), 1, 0) as extDataLink ")
				);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId){
		//----------------------------------------------------------------
		$data = null;
		switch($ownerId){
			case -1: 
				$data = $this->myRelationLink($orgID, $field, $value)->orderBy('leftKRName', 'asc')
							->orderBy('linkOrder', 'asc')->get()->forPage($page, $perPage); break;
			case  0: 
				$data = $this->myRelationLink($orgID, $field, $value)->where('relation_link.ownerId', null)->orderBy('leftKRName', 'asc')
							->orderBy('linkOrder', 'asc')->get()->forPage($page, $perPage); break;
			default: 
				$data = $this->myRelationLink($orgID, $field, $value)->where('relation_link.ownerId', $ownerId)->orderBy('leftKRName', 'asc')
							->orderBy('linkOrder', 'asc')->get()->forPage($page, $perPage); break;
		}
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $tmp ){ $retVal[] = $tmp; }
		return $retVal;
		//----------------------------------------------------------------
	}
	//--------------------------------------------------------------------
    public function organization(){ return $this->belongsTo('App\Organization', 'ownerId', 'organizationId'); }
    public function organ(){ return $this->hasOne('App\Organization', 'ownerId', 'organizationId'); }
	//--------------------------------------------------------------------
    public function getTerms($orgID){
		$relatioTypeId   = \Config::get('kama_dei.static.is_a_member_of_ID', 0);
		$LINKING_TERM_ID = \Config::get('kama_dei.static.LINKING_TERM_ID'  , 0);

		return \App\Term::whereIn('termid', 
								function($q) use($relatioTypeId, $LINKING_TERM_ID){
									return $q
										->select('leftTermId')
										->from(with(new \App\Relation)->getTable())
										->where('relationTypeId', $relatioTypeId)
										->where('rightTermId'   , $LINKING_TERM_ID);
								}
							)
							->orderBy('termName', 'asc')
							->get();
/*
		return \App\Term::whereIn('termid', 
								function($q){
									return $q
										->select('leftTermId')
										->from(with(new \App\Relation)->getTable())
										->whereIn('relationTypeId',
												function($q){
													return $q
														->select('relationTypeId')
														->from(with(new \App\RelationType)->getTable())
														->where('relationtypename','is a member of');
												}
										)
										->whereIn('rightTermId',
												function($q){
													return $q
														->select('termid')
														->from(with(new \App\Term)->getTable())
														->where('termname','Linking Term');
												}
										);
								}
							)
							->get();
*/
/*
select * 
from term 
WHERE termid in ( 
 SELECT lefttermid 
 FROM `relation` 
 WHERE relationTypeId in (
  SELECT relationTypeId 
  FROM relation_type 
  WHERE relationtypename = 'is a member of'
 ) 
 AND 
 rightTermId in (
  SELECT termid 
  from term 
  where termname='Linking Term'
 ) 
)
*/
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
    protected function findByIdprotected($id){
        return $this
            ->with(['organization'])
            ->leftJoin('relation as leftKR', 'relation_link.leftRelationId', '=', 'leftKR.relationId')
            ->leftJoin('term as leftKRTermL', 'leftKR.leftTermId', '=', 'leftKRTermL.termId')
            ->leftJoin('relation_type as leftKRRelationType', 'leftKR.relationTypeId', '=', 'leftKRRelationType.relationTypeId')
            ->leftJoin('term as leftKRTermR', 'leftKR.rightTermId', '=', 'leftKRTermR.termId')

            ->leftJoin('relation as rightKR', 'relation_link.rightRelationId', '=', 'rightKR.relationId')
            ->leftJoin('term as rightKRTermL', 'rightKR.leftTermId', '=', 'rightKRTermL.termId')
            ->leftJoin('relation_type as rightKRRelationType', 'rightKR.relationTypeId', '=', 'rightKRRelationType.relationTypeId')
            ->leftJoin('term as rightKRTermR', 'rightKR.rightTermId', '=', 'rightKRTermR.termId')

            ->leftJoin('term', 'relation_link.linkTermId', '=', 'term.termId')
            ->where(
                function($q) use($id){
                    if($id==0){ return $q;}
                    else{ return $q->where('relation_link.relationLinkId', '=', $id); }
                }
            )
            ->select(
                'relation_link.*',
//					'leftRelationType.relationTypeName as leftRelationTypeName',
//					'rightRelationType.relationTypeName as rightRelationTypeName',
                \DB::raw('CONCAT(leftKRTermL.termName," ",leftKRRelationType.relationTypeName," ",leftKRTermR.termName) as leftKRName'),
                \DB::raw('CONCAT(rightKRTermL.termName," ",rightKRRelationType.relationTypeName," ",rightKRTermR.termName) as rightKRName'),

                'term.termName as termName'
            )->get();
    }
    public function findById($id){
        return $this
            ->with(['organization'])
            ->leftJoin('relation as leftKR', 'relation_link.leftRelationId', '=', 'leftKR.relationId')
            ->leftJoin('term as leftKRTermL', 'leftKR.leftTermId', '=', 'leftKRTermL.termId')
            ->leftJoin('relation_type as leftKRRelationType', 'leftKR.relationTypeId', '=', 'leftKRRelationType.relationTypeId')
            ->leftJoin('term as leftKRTermR', 'leftKR.rightTermId', '=', 'leftKRTermR.termId')

            ->leftJoin('relation as rightKR', 'relation_link.rightRelationId', '=', 'rightKR.relationId')
            ->leftJoin('term as rightKRTermL', 'rightKR.leftTermId', '=', 'rightKRTermL.termId')
            ->leftJoin('relation_type as rightKRRelationType', 'rightKR.relationTypeId', '=', 'rightKRRelationType.relationTypeId')
            ->leftJoin('term as rightKRTermR', 'rightKR.rightTermId', '=', 'rightKRTermR.termId')

            ->leftJoin('term', 'relation_link.linkTermId', '=', 'term.termId')
            ->where(
                function($q) use($id){
                    if($id==0){ return $q;}
                    else{ return $q->where('relation_link.relationLinkId', '=', $id); }
                }
            )
            ->select(
                'relation_link.*',
//					'leftRelationType.relationTypeName as leftRelationTypeName',
//					'rightRelationType.relationTypeName as rightRelationTypeName',
                \DB::raw('CONCAT(leftKRTermL.termName," ",leftKRRelationType.relationTypeName," ",leftKRTermR.termName) as leftKRName'),
                \DB::raw('CONCAT(rightKRTermL.termName," ",rightKRRelationType.relationTypeName," ",rightKRTermR.termName) as rightKRName'),

                'term.termName as termName'
            );
    }
	//--------------------------------------------------------------------
	protected function myPageingNew($orgID, $perPage, $page, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1){
		//----------------------------------------------------------------
		$tmp = $this->myRelationLinkNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT);
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
	protected function myRelationLinkNew($orgID, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1){
		$PRIVATE = \Config::get('kama_dei.static.PRIVATE',2);
//		$tmp = $this->myRelationLink($orgID, $field, $value)->orderBy($sort, $order);

		switch($ownerId){
			case -1: $tmp = $this->myRelationLink($orgID, $field, $value)->orderBy($sort, $order); break;
			default: $tmp = $this->myRelationLink($orgID, $field, $value, $ownerId)->orderBy($sort, $order); break;
		}

		if($tmp==null){ return null; }
		if($orgID!=$ownerId && $ownerId!=-1 && $orgID!=0){ $tmp = $tmp->where('relation_link.ownership', '<>', $PRIVATE); }
		if($shwglblSTT==1){ return $tmp; }
		switch($ownerId){
			case -1: return $tmp->where('relation_link.ownerId', $orgID);
			case  0: return $tmp->where('relation_link.ownerId', null);
			default: return $tmp->where('relation_link.ownerId', $ownerId);
		}
	}
	//--------------------------------------------------------------------
	protected function getOwnersList($orgID){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$data = $this
			->leftJoin('kamadeiep.organization_ep', 'kamadeikb.relation_link.ownerId', '=', 'kamadeiep.organization_ep.organizationId')
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD){ 
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->where('relation_link.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('relation_link.ownerId', $tmpOrgIDs)->where('relation_link.ownership', $PRTCTD);
								})
								->orWhere('relation_link.ownerId', $orgID);
					}
			})
			->groupBy('relation_link.ownerId')
			->select(
				\DB::raw("if(relation_link.ownerId=0 or relation_link.ownerId is null, 0, relation_link.ownerId) as id"),
				\DB::raw("if(relation_link.ownerId=0 or relation_link.ownerId is null, '".env('BASE_ORGANIZATION')."', organization_ep.organizationShortName) as text")
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
}
