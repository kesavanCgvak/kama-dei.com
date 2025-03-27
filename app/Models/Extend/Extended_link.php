<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/11/8
 * Time: 1:19 PM
 */

namespace App\Models\Extend;
use App\Relation;
use App\RelationLink;
use Illuminate\Database\Eloquent\Model;

class Extended_link extends Model
{
    protected $connection = 'mysql2';
    protected $table      = 'extended_link';
    protected $primaryKey = "extendedLinkId";
    protected $modifiers  = ['extendedLinkId',
        'entityId',
        'parentTable',
        'parentId',//最后编辑者
       /* 'sampleChatDisplay',*/
        'ownerId',//system_organization 归属
        'ownership',//是否必填
        'includedExtDataName',      //复选
        'includedExtDataChatIntro',//复选
		'updated_at', //创建时间
		'created_at', //更新时间
        'reserved',//reserved
        'lastUserId',
        'memo',//其他描述
//		'chatIntro',
//		'voiceIntro',
        'orderid'
    ];
//	protected $dates   = ['dateCreated'];
	
//	protected $dates_c = ['dateCreated'];
//	protected $dates_u = ['dateUpdated'];
	protected $dates_c = ['created_at'];
	protected $dates_u = ['updated_at'];

    //--------------------------------------------------------------------
    public function findById($id){ return $this->where('extendedLinkId', '=', $id)->get()->toArray(); }
    //--------------------------------------------------------------------
    //--------------------------------------------------------------------
    protected function myExtendedLinks($entityId,$parentTable,$parentId,$orgID,$searc, $showGlobal=1){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE = \Config::get('kama_dei.static.PRIVATE'  ,2);

        $parentTableName='term';
        $tmpcloumId='termId';
        switch ($parentTable){
            case 0:
                //Terms
                $parentTableName='term';
                $tmpcloumId='termId';
                break;
            case 1:
                //Relation Types
                /*$parentTableName='relationType';
                $tmpcloumId='relationTypeId';
                break;*/
                //relationlink
                $parentTableName='relationlink';
                $tmpcloumId='relationLinkId';
                break;
            case 2:
                //Knowledge Records
                $parentTableName='relation';
                $tmpcloumId='relationId';
                break;
            default:
                $parentTableName='term';
                $tmpcloumId='termId';
        }
//->where($field, 'like', "%{$value}%")


        if($searc){
            $retVal = [];
            $tmpOrgIDs = \App\Models\Extend\Extended_entity::where('extendedEntityName', 'like', "%{$searc}%")->get();
            foreach( $tmpOrgIDs as $tmp){ $retVal[] = $tmp->extendedEntityId; }

            return $this
                ->with([$parentTableName])
                ->with(['extendedentity'])
                ->with(['organization'])
                ->Where(function($q) use($retVal){ return $q->whereIn('entityId', $retVal); })
                //->where('extendedEntityName', 'like', "%{$searc}%")
/*
				->whereRaw("extended_link.ownerId in (select kamadeiep.organization_ep.organizationId from kamadeiep.organization_ep) or extended_link.ownerId=0 or extended_link.ownerId is null")
*/
                ->where(function($q) use($orgID, $showGlobal){
					if($showGlobal==1){
						return $q->whereRaw("extended_link.ownerId in (select kamadeiep.organization_ep.organizationId from kamadeiep.organization_ep) or extended_link.ownerId=0 or extended_link.ownerId is null");
					}else{
						if($orgID!=0){
							return $q->whereRaw("extended_link.ownerId in (select kamadeiep.organization_ep.organizationId from kamadeiep.organization_ep)");
						}else{
							return $q->whereRaw("extended_link.ownerId=0 or extended_link.ownerId is null");
						}
					}
				})
                ->where(function($q) use($parentId,$tmpcloumId){
                    if($parentId==0){ return $q; }
                    else{return $q->where('parentId', '=', $parentId); }
                })
                ->where(function($q) use($orgID, $PRTCTD, $PUBLIC, $showGlobal){
					if($orgID==-1){ return $q; }
                    if($orgID==0){
						if($showGlobal==1){ return $q; }
						return $q->where("extended_link.ownerId", null)->orWhere("extended_link.ownerId", 0);
					}

					if($showGlobal==1){
						$tmpOrgIDs = \App\OrgRelations::haveAccessTo($orgID);
						return $q
								->where('extended_link.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){ 
									return $q
										->whereIn('extended_link.ownerId', $tmpOrgIDs)
										->where('extended_link.ownership', $PRTCTD); 
								})
								->orWhere('extended_link.ownerId', $orgID);
					}
					return $q->where('extended_link.ownerId', $orgID);
                })
                ->where(function($q) use($entityId){
                    if($entityId==0){ return $q; }
                    else{ return $q->where('entityId', '=', $entityId); }
                })
                ->where(function($q) use($parentTable){
                    return $q->where('parentTable', '=', $parentTable);
                });
        }else{
            return $this
                ->with([$parentTableName])
                ->with(['extendedentity'])
                ->with(['organization'])
                ->where(function($q) use($parentId,$tmpcloumId){
                    if($parentId==0){ return $q; }
                    else{return $q->where('parentId', '=', $parentId); }
                })
                ->where(function($q) use($orgID, $PRTCTD, $PUBLIC, $showGlobal){
					if($orgID==-1){ return $q; }
                    if($orgID==0){
						if($showGlobal==1){ return $q; }
						return $q->where("extended_link.ownerId", null)->orWhere("extended_link.ownerId", 0);
					}

					if($showGlobal==1){
						$tmpOrgIDs = \App\OrgRelations::haveAccessTo($orgID);
						return $q
								->where('extended_link.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){ 
									return $q
										->whereIn('extended_link.ownerId', $tmpOrgIDs)
										->where('extended_link.ownership', $PRTCTD); 
								})
								->orWhere('extended_link.ownerId', $orgID);
					}
					return $q->where('extended_link.ownerId', $orgID);
                })
                ->where(function($q) use($orgID, $showGlobal){
					if($showGlobal==1){
						return $q->whereRaw("extended_link.ownerId in (select kamadeiep.organization_ep.organizationId from kamadeiep.organization_ep) or extended_link.ownerId=0 or extended_link.ownerId is null");
					}else{
						if($orgID!=0){
							return $q->whereRaw("extended_link.ownerId in (select kamadeiep.organization_ep.organizationId from kamadeiep.organization_ep)");
						}else{
							return $q->whereRaw("extended_link.ownerId=0 or extended_link.ownerId is null");
						}
					}
				})
/*
->whereRaw("extended_link.ownerId in (select kamadeiep.organization_ep.organizationId from kamadeiep.organization_ep) or extended_link.ownerId=0 or extended_link.ownerId is null")
*/

                ->where(function($q) use($entityId){
                    if($entityId==0){ return $q; }
                    else{ return $q->where('entityId', '=', $entityId); }
                })
                ->where(function($q) use($parentTable){
                    return $q->where('parentTable', '=', $parentTable);
                });
        }



    }
    //--------------------------------------------------------------------
    protected function myPageing($entityId,$parentTable,$parentId,$orgID, $perPage, $page, $sort, $order, $searc, $showGlobal=1){
        //----------------------------------------------------------------
        $data = null;
        //----------------------------------------------------------------
        $data = $this->myExtendedLinks($entityId,$parentTable,$parentId,$orgID,$searc,$showGlobal)->orderBy($sort, $order);
        if($sort!='orderid'){
            $data->orderBy('orderid','DESC');
        }
        $data=$data->get()->forPage($page, $perPage);


        //----------------------------------------------------------------
        if($data->isEmpty()){ return null; }
        //----------------------------------------------------------------
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{
				if($tmp->organization==null){ $tmp->organizationShortName="-"; }
				else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			}

            if($tmp->entityId==null||$tmp->extendedentity==null){ $tmp->entityName = ''; }
            else{$tmp->entityName = $tmp->extendedentity->extendedEntityName;}
            if($tmp->parentId==null){ $tmp->parentName = ''; }
            else{
                switch ($tmp->parentTable){
                    case 0:
                        //Terms
                        $tmp->parentTableName = 'Term';
                        $tmp->parentName = $tmp->term->termName;
                        break;
                    case 1:
                        //Relation Types
                        /*$tmp->parentTableName = 'Relation Types';
                        $tmp->parentName = $tmp->relationType->relationTypeName;*/
                        //relationlink
                        $tmp->parentTableName = 'Knowledge Link';
                        $tmpRelationLink = new RelationLink;
                        $arrRelationLink= $tmpRelationLink->findById($tmp->parentId)->get();
                        if($arrRelationLink->isEmpty()){
                            $tmp->parentName = '';
                        }else{
                            foreach( $arrRelationLink as $key3=>$tmp3 ){
                                if($tmp3->leftKRName!=null){
                                    $tmp->parentName = $tmp3->leftKRName.'-'.$tmp3->termName.'-'.$tmp3->rightKRName;

                                    //$tmp->parentName = $tmp2->knowledgeRecordName;
                                }
                            }
                        }
                       // $tmp->parentName = $tmp->relationlink->leftKRName.'-'.$tmp->relationlink->termName.'-'.$tmp->relationlink->rightKRName;

                        break;
                    case 2:
                        //Knowledge Records
                        $tmp->parentTableName = 'Knowledge Record';
                        $tmpRelation = new Relation;
                       $arrRelation= $tmpRelation->findById($tmp->parentId)->get();
                        if($arrRelation->isEmpty()){
                            /*$tmp->parentName = $tmp->relation->knowledgeRecordName;*/
                            $tmp->parentName = '';
                        }else{
                            foreach( $arrRelation as $key2=>$tmp2 ){
                                if($tmp2->knowledgeRecordName!=null){ $tmp->parentName = $tmp2->knowledgeRecordName; }
                            }
                        }

                        break;
                    default:
                        $tmp->parentTableName = 'term';
                        $tmp->parentName = $tmp->term->termName;
                }
            }
            $retVal[] = $tmp;
        }
        //----------------------------------------------------------------
        return $retVal;
        //----------------------------------------------------------------
    }
    //--------------------------------------------------------------------


    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }
    public function extendedentity(){
        return $this->belongsTo('App\Models\Extend\Extended_entity', 'entityId', 'extendedEntityId');
    }
    public function term(){
        return $this->belongsTo('App\Models\Term', 'parentId', 'termId');
    }
    public function relationType(){
        return $this->belongsTo('App\Models\RelationType', 'parentId', 'relationTypeId');
    }
    public function relation(){
        return $this->belongsTo('App\Relation', 'parentId', 'relationId');
    }

    public function relationlink(){
        return $this->belongsTo('App\RelationLink', 'parentId', 'relationLinkId');
    }


    //--------------------------------------------------------------------
    protected function findExtendedLinkByID($entityId,$parentTable,$parentId,$orgID, $id){
        $this->myExtendedLinks($entityId,$parentTable,$parentId,$orgID)->where('extendedLinkId', '=', $id)->get();
    }
    //--------------------------------------------------------------------
}