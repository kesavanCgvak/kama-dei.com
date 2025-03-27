<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/19
 * Time: 下午6:20
 */

namespace App\Models\Extend;

use Illuminate\Database\Eloquent\Model;

class Extended_entity extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'extended_entity';
    protected $primaryKey = "extendedEntityId";
    protected $modifiers  = ['extendedEntityId', 'extendedEntityName',
        'lastUserId',//最后编辑者
        'ownerId',//system_organization 归属
        'ownership',//是否必填
        'dateCreated', //创建时间
        'dateUpdated', //更新时间
        'memo',//其他描述
        'extendedSubTypeId',//extendedSubTypeId
        'reserved',//reserved
        //'orderid'
    ];
//    protected $dates      = ['dateCreated'];
    protected $dates_c      = ['dateCreated'];
    protected $dates_u      = ['dateUpdated'];
    //--------------------------------------------------------------------

    public function findExtendedEntityName($extendedEntityName)
    {
        return $this->where('extendedEntityName', '=', $extendedEntityName)->get()->toArray();
    }
    //--------------------------------------------------------------------
    protected function findExtendedEntityNameByID($id)
    {
        return
            $this
                ->with(['extendedsubtype'])
                ->with(['organization'])
                
                ->where('extendedEntityId', '=', $id)->get()->toArray();
    }
    //--------------------------------------------------------------------
    public function findById($id)
    {
        return $this->where('extendedEntityId', '=', $id)->get()->toArray();
    }
    //--------------------------------------------------------------------
    protected function myExtendedEntitys($extendedSubTypeId,$orgID, $field, $value, $showGlobal=1){
		/*
        if( $value=='' ){
            return $this
                ->with(['extendedsubtype'])
                ->with(['organization'])
                ->where(function($q) use($extendedSubTypeId){
                    if($extendedSubTypeId==0){ return $q; }
                    else{ return $q->where('extendedSubTypeId', '=', $extendedSubTypeId); }
                })
                ->where(function($q) use($orgID){
                    if($orgID==0){ return $q; }
                    else{ return $q->where('ownerId', '=', null)->where('ownership', '=', 0)->orwhere('ownerId', '=',$orgID); }
                });
        }else{
            return $this
                ->with(['extendedsubtype'])
                ->with(['organization'])
                ->where(function($q) use($extendedSubTypeId){
                    if($extendedSubTypeId==0){ return $q; }
                    else{ return $q->where('extendedSubTypeId', '=', $extendedSubTypeId); }
                })
                ->where($field, 'like', "%{$value}%")
                ->where(function($q) use($orgID){
                    if($orgID==0){ return $q; }
                    else{ return $q->where('ownerId', '=', null)->where('ownership', '=', 0)->orwhere('ownerId', '=',$orgID); }
                });
        }
		*/
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE = \Config::get('kama_dei.static.PRIVATE'  ,2);
        if( $value=='' ){
            return $this
                ->with(['extendedsubtype'])
                ->with(['organization'])
				->leftJoin('extended_subtype', 'extended_entity.extendedSubTypeId', '=', 'extended_subtype.extendedSubTypeId')
                ->where(function($q) use($extendedSubTypeId){
                    if($extendedSubTypeId==0){ return $q; }
                    else{ return $q->where('extendedSubTypeId', '=', $extendedSubTypeId); }
                })
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $showGlobal){
					if($orgID==-1){ return $q; }
					if($orgID==0){
						if($showGlobal==1){ return $q; }
						return $q->where('extended_entity.ownerId', null)->orWhere('extended_entity.ownerId', 0);
					}
					if($showGlobal==1){
						$tmpOrgIDs = \App\OrgRelations::haveAccessTo($orgID);
						return $q
								->where('extended_entity.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){ 
									return $q
										->whereIn('extended_entity.ownerId', $tmpOrgIDs)
										->where('extended_entity.ownership', $PRTCTD); 
								})
								->orWhere('extended_entity.ownerId', $orgID);
					}
					return $q->where('extended_entity.ownerId', $orgID);
				})
				->select(
					"extended_entity.*",
					"extended_subtype.extendedSubTypeName as extnddSbTypNm",
					\DB::raw("(".
							 "select GROUP_CONCAT(attributeTypeName) as attributeType from extended_data_new ".
							 	"where extended_data_new.extendedEntityId=extended_entity.extendedEntityId".
							 ") as attributeType"
					)
/*				,
					\DB::raw('(select review_by from extended_eav where extended_entity.extendedEntityId=extended_eav.extendedEntityId limit 1) as review_by')*/
				);
        }else{
            return $this
                ->with(['extendedsubtype'])
                ->with(['organization'])
				->leftJoin('extended_subtype', 'extended_entity.extendedSubTypeId', '=', 'extended_subtype.extendedSubTypeId')
                ->where(function($q) use($extendedSubTypeId){
                    if($extendedSubTypeId==0){ return $q; }
                    else{ return $q->where('extendedSubTypeId', '=', $extendedSubTypeId); }
                })
                ->where($field, 'like', "%{$value}%")
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $showGlobal){
					if($orgID==-1){ return $q; }
					if($orgID==0){
						if($showGlobal==1){ return $q; }
						return $q->where('extended_entity.ownerId', null)->orWhere('extended_entity.ownerId', 0);
					}
					if($showGlobal==1){
						$tmpOrgIDs = \App\OrgRelations::haveAccessTo($orgID);
						return $q
								->where('extended_entity.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){ 
									return $q
										->whereIn('extended_entity.ownerId', $tmpOrgIDs)
										->where('extended_entity.ownership', $PRTCTD); 
								})
								->orWhere('extended_entity.ownerId', $orgID);
					}
					return $q->where('extended_entity.ownerId', $orgID);
				})
				->select(
					"extended_entity.*",
					"extended_subtype.extendedSubTypeName as extnddSbTypNm",
					\DB::raw("(".
							 "select GROUP_CONCAT(attributeTypeName) as attributeType from extended_data_new ".
							 	"where extended_data_new.extendedEntityId=extended_entity.extendedEntityId".
							 ") as attributeType"
					)
/*				,
					\DB::raw('(select review_by from extended_eav where extended_entity.extendedEntityId=extended_eav.extendedEntityId limit 1) as review_by')*/
				);
        }
    }
    //--------------------------------------------------------------------
    protected function myPageing($extendedSubTypeId,$orgID, $perPage, $page, $sort, $order, $showGlobal=1){
        //----------------------------------------------------------------
        $data = null;
        //----------------------------------------------------------------
		$retCount = 0;
		if($sort=='review_by'){
			$data1 = $this->myExtendedEntitys($extendedSubTypeId,$orgID, '', '', $showGlobal)
				//->leftJoin("extended_eav", "extended_entity.extendedEntityId", "extended_eav.extendedEntityId")
				->whereNotNull('extended_entity.review_by')
				->orderBy($sort, $order)
				->get();
			
			$data2 = $this->myExtendedEntitys($extendedSubTypeId,$orgID, '', '', $showGlobal)
				//->leftJoin("extended_eav", "extended_entity.extendedEntityId", '=', "extended_eav.extendedEntityId")
				->where('extended_entity.review_by', null)
				->orderBy($sort, $order)
				->get();
			
			$data = $data1->merge($data2)
				->map(function($row){
/*
					$attributeType = \DB::connection('mysql2')
						->select(
							"select GROUP_CONCAT(attributeTypeName) as attributeType ".
							"from extended_data_new ".
							"where extendedEntityId = ? ".
							"",
						[$row->extendedEntityId]
					);
					
					$row->attributeType = "";
					if($attributeType!=null){
						$row->attributeType = $attributeType[0]->attributeType;
					}
*/
					return $row;
				});
			$data = $data->forPage($page, $perPage)
				->map(function($row){
					$view = \App\Models\Extend\Extended_Data_View::where('extendedEntityId', $row->extendedEntityId)->first();
					if($view!=null && strtoupper($view->storageType)=='TEXT'){
						$row->notes=
							\App\Models\Extend\Responsiblity::where('extendedEntityId', $row->extendedEntityId)->count()+
							(($row->ownerId==0 || $row->ownerId==null)
								?1
							 	:\App\Organization::where('organizationId', $row->ownerId)->first()->RAG
							);
					}else{ $row->notes=0; }
					return $row;
				});
		}else{
			$data = $this->myExtendedEntitys($extendedSubTypeId,$orgID, '', '', $showGlobal)
				->orderBy($sort, $order)
				->get()
				->map(function($row){
/*
					$attributeType = \DB::connection('mysql2')
						->select(
							"select GROUP_CONCAT(attributeTypeName) as attributeType ".
							"from extended_data_new ".
							"where extendedEntityId = ? ".
							"",
						[$row->extendedEntityId]
					);
					
					$row->attributeType = "";
					if($attributeType!=null){
						$row->attributeType = $attributeType[0]->attributeType;
					}
*/
					return $row;
				});
			
			$data = $data->forPage($page, $perPage)
				->map(function($row){
					$view = \App\Models\Extend\Extended_Data_View::where('extendedEntityId', $row->extendedEntityId)->first();
					if($view!=null && strtoupper($view->storageType)=='TEXT'){
						$row->notes=
							\App\Models\Extend\Responsiblity::where('extendedEntityId', $row->extendedEntityId)->count()+
							(($row->ownerId==0 || $row->ownerId==null)
								?1
							 	:\App\Organization::where('organizationId', $row->ownerId)->first()->RAG
							);
					}else{ $row->notes=0; }
					return $row;
				});
		}

        //----------------------------------------------------------------
        if($data->isEmpty()){ return null; }
        //----------------------------------------------------------------
        $retVal = [];
        foreach( $data as $key=>$tmp ){

            if($tmp->ownerId==null || $tmp->ownerId==0){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{
				if($tmp->organization==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
				else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			}
/*
            if($tmp->extendedSubTypeId==null){ $tmp->extendedSubTypeName = ''; }
            else{$tmp->extendedSubTypeName = $tmp->extendedsubtype->extendedSubTypeName;}
*/
            if($tmp->extendedsubtype==null){ $tmp->extendedSubTypeName = ''; }
            else{$tmp->extendedSubTypeName = $tmp->extendedsubtype->extendedSubTypeName;}

            $retVal[] = $tmp;
        }
        //----------------------------------------------------------------
        return $retVal;
        //----------------------------------------------------------------
    }
    //--------------------------------------------------------------------
    protected function myPageingWithSearch($extendedSubTypeId,$orgID, $perPage, $page, $sort, $order, $field, $value, $showGlobal=1){
        $data = null;
/*
        $data = $this->myExtendedEntitys($extendedSubTypeId,$orgID, $field, $value, $showGlobal)->orderBy($sort, $order);
//        if($sort!='orderid'){
//            $data->orderBy('orderid','DESC');
//        }
        $data=$data->get()->forPage($page, $perPage);
*/
		if($sort=='review_by'){
			$data1 = $this->myExtendedEntitys($extendedSubTypeId,$orgID, $field, $value, $showGlobal)
//				->leftJoin("extended_eav", "extended_entity.extendedEntityId", "extended_eav.extendedEntityId")
				->whereNotNull('extended_entity.review_by')
				->orderBy($sort, $order)
				->get();
			
			$data2 = $this->myExtendedEntitys($extendedSubTypeId,$orgID, $field, $value, $showGlobal)
//				->leftJoin("extended_eav", "extended_entity.extendedEntityId", '=', "extended_eav.extendedEntityId")
				->where('extended_entity.review_by', null)
				->orderBy($sort, $order)
				->get();
			
			$data = $data1->merge($data2)
				->map(function($row){
/*
					$attributeType = \DB::connection('mysql2')
						->select(
							"select GROUP_CONCAT(attributeTypeName) as attributeType ".
							"from extended_data_new ".
							"where extendedEntityId = ? ".
							"",
						[$row->extendedEntityId]
					);
					
					$row->attributeType = "";
					if($attributeType!=null){
						$row->attributeType = $attributeType[0]->attributeType;
					}
*/
					$view = \App\Models\Extend\Extended_Data_View::where('extendedEntityId', $row->extendedEntityId)->first();
					if($view!=null && strtoupper($view->storageType)=='TEXT'){
						$row->notes=
							\App\Models\Extend\Responsiblity::where('extendedEntityId', $row->extendedEntityId)->count()+
							(($row->ownerId==0 || $row->ownerId==null)
								?1
							 	:\App\Organization::where('organizationId', $row->ownerId)->first()->RAG
							);
					}else{ $row->notes=0; }
					return $row;
				});
			$data = $data->forPage($page, $perPage);
		}else{
			$data = $this->myExtendedEntitys($extendedSubTypeId,$orgID, $field, $value, $showGlobal)
				->orderBy($sort, $order)
				->get()
				->map(function($row){
/*
					$attributeType = \DB::connection('mysql2')
						->select(
							"select GROUP_CONCAT(attributeTypeName) as attributeType ".
							"from extended_data_new ".
							"where extendedEntityId = ? ".
							"",
						[$row->extendedEntityId]
					);
					
					$row->attributeType = "";
					if($attributeType!=null){
						$row->attributeType = $attributeType[0]->attributeType;
					}
*/
					$view = \App\Models\Extend\Extended_Data_View::where('extendedEntityId', $row->extendedEntityId)->first();
					if($view!=null && strtoupper($view->storageType)=='TEXT'){
						$row->notes=
							\App\Models\Extend\Responsiblity::where('extendedEntityId', $row->extendedEntityId)->count()+
							(($row->ownerId==0 || $row->ownerId==null)
								?1
							 	:\App\Organization::where('organizationId', $row->ownerId)->first()->RAG
							);
					}else{ $row->notes=0; }
					return $row;
				});
			
			$data = $data->forPage($page, $perPage);
		}
        if($data->isEmpty()){ return null; }
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
/*
            if($tmp->extendedSubTypeId==null){ $tmp->extendedSubTypeName = ''; }
            else{$tmp->extendedSubTypeName = $tmp->extendedsubtype->extendedSubTypeName;}
*/
            if($tmp->extendedsubtype==null){ $tmp->extendedSubTypeName = ''; }
            else{$tmp->extendedSubTypeName = $tmp->extendedsubtype->extendedSubTypeName;}
            $retVal[] = $tmp;
        }
        return $retVal;
    }
    //--------------------------------------------------------------------
    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }
    public function extendedsubtype(){
        return $this->belongsTo('App\Models\Extend\Extended_subtype', 'extendedSubTypeId', 'extendedSubTypeId');
    }
    //--------------------------------------------------------------------
    public function findExtendedEntityByID($extendedSubTypeId,$orgID, $id){
        if($orgID==0){
            if($extendedSubTypeId==0){
                return $this->with(['extendedsubtype'])->with(['organization'])->where('extendedEntityId', '=', $id)->get();
            }else{
                return $this->with(['extendedsubtype'])->with(['organization'])
                    ->where('extendedSubTypeId', '=', $extendedSubTypeId)
                    ->where('extendedEntityId', '=', $id)->get();
            }
        }
        else{
            if($extendedSubTypeId==0){
                return $this->with(['extendedsubtype'])->with(['organization'])->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where('extendedEntityId', '=', $id)->get();
            }else{
                return $this->with(['extendedsubtype'])->with(['organization'])
                    ->where('extendedSubTypeId', '=', $extendedSubTypeId)
                    ->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where('extendedEntityId', '=', $id)->get();
            }

        }
    }
    //--------------------------------------------------------------------
    public function findExtendedEntity($extendedSubTypeId,$orgID, $field, $value){
        if($orgID==0){
            if($extendedSubTypeId==0){
                return $this->with(['extendedsubtype'])->with(['organization'])->where($field, 'like', "%{$value}%")->get();
            }else{
                return $this->with(['extendedsubtype'])->with(['organization'])
                    ->where('extendedSubTypeId', '=', $extendedSubTypeId)
                    ->where($field, 'like', "%{$value}%")->get();
            }

        }
        else{
            if($extendedSubTypeId==0){
                return $this->with(['extendedsubtype'])->with(['organization'])->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where($field, 'like', "%{$value}%")->get();
            }else{
                return $this->with(['extendedsubtype'])->with(['organization'])
                    ->where('extendedSubTypeId', '=', $extendedSubTypeId)
                    ->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where($field, 'like', "%{$value}%")->get();
            }
        }
    }
    //--------------------------------------------------------------------
}