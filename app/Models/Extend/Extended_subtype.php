<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/19
 * Time: 下午6:22
 */

namespace App\Models\Extend;

use Illuminate\Database\Eloquent\Model;

class Extended_subtype extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'extended_subtype';
    protected $primaryKey = "extendedSubTypeId";
    protected $modifiers  = ['extendedSubTypeId', 'extendedSubTypeName',
        'lastUserId',//最后编辑者
        'ownerId',//system_organization 归属
        'chatIntro',//chatIntro
        'ownership',//是否必填
        'dateCreated', //创建时间
        'dateUpdated', //更新时间
        'memo',//其他描述
        'extendedTypeId',//extendedTypeId
        'reserved',//reserved
    ];
//    protected $dates      = ['dateCreated'];
    protected $dates_c      = ['dateCreated'];
    protected $dates_u      = ['dateUpdated'];
    //--------------------------------------------------------------------
    public function findExtendedSubTypeName($extendedSubTypeName)
    {
        return ($this->where('extendedSubTypeName', '=', $extendedSubTypeName)->get())->toArray();
    }
    //--------------------------------------------------------------------
    public function findById($id)
    {
        return ($this->where('extendedSubTypeId', '=', $id)->get())->toArray();
    }
    //--------------------------------------------------------------------
    protected function myExtendedSubTypes($typeID,$orgID, $field, $value, $showGlobal=1){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE = \Config::get('kama_dei.static.PRIVATE'  ,2);

		return $this
		   /* ->with(['extendedtype'])*/
			->with(['organization'])
			->leftJoin('extended_type', 'extended_subtype.extendedTypeId', '=', 'extended_type.extendedTypeId')
			->where(function($q) use($typeID){
				if($typeID==0){ return $q; }
				else{ return $q->where('extended_subtype.extendedTypeId', '=', $typeID); }
			})
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $showGlobal){
				if($orgID==-1){ return $q; }
				if($orgID==0){
					if($showGlobal==1){ return $q; }
					return $q->where('extended_subtype.ownerId', null)->orWhere('extended_subtype.ownerId', 0);
				}
				if($showGlobal==1){
					$tmpOrgIDs = \App\OrgRelations::haveAccessTo($orgID);
					return $q
							->where('extended_subtype.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){ 
								return $q
									->whereIn('extended_subtype.ownerId', $tmpOrgIDs)
									->where('extended_subtype.ownership', $PRTCTD); 
							})
							->orWhere('extended_subtype.ownerId', $orgID);
				}
				return $q->where('extended_subtype.ownerId', $orgID);
			})
			->where(function($q) use($field, $value){
				if($value==""){ return $q; }
				return $q->where($field, 'like', "%{$value}%");
			})
			->select(
				'extended_subtype.*',
				'extended_type.extendedTypeName'
			);
    }
    //--------------------------------------------------------------------
    protected function myPageing($typeID, $orgID, $perPage, $page, $sort, $order, $showGlobal=1){
        //----------------------------------------------------------------
        $data = null;
        //----------------------------------------------------------------
        $data = $this
			->myExtendedSubTypes($typeID, $orgID, '', '', $showGlobal)
			->orderBy($sort, $order)
			->get()
			->forPage($page, $perPage);
        //----------------------------------------------------------------
        if($data->isEmpty()){ return null; }
        //----------------------------------------------------------------
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
            /*if($tmp->extendedTypeId==null){ $tmp->extendedTypeName = ''; }
            else{$tmp->extendedTypeName = $tmp->extendedtype->extendedTypeName;}*/
            $retVal[] = $tmp;
        }
        //----------------------------------------------------------------
        return $retVal;
        //----------------------------------------------------------------
    }
    //--------------------------------------------------------------------
    protected function myPageingWithSearch($typeID, $orgID, $perPage, $page, $sort, $order, $field, $value, $showGlobal=1){
        $data = null;
        $data = $this
			->myExtendedSubTypes($typeID, $orgID, $field, $value, $showGlobal)
			->orderBy($sort, $order)
			->get()
			->forPage($page, $perPage);
        if($data->isEmpty()){ return null; }
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
            /*if($tmp->extendedTypeId==null){ $tmp->extendedTypeName = ''; }
            else{$tmp->extendedTypeName = $tmp->extendedtype->extendedTypeName;}*/
            $retVal[] = $tmp;
        }
        return $retVal;
    }
    //--------------------------------------------------------------------
    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }
    //--------------------------------------------------------------------
    public function extendedtype(){
        return $this->belongsTo('App\Models\Extend\Extended_type', 'extendedTypeId', 'extendedTypeId');
    }
    //--------------------------------------------------------------------
    public function findExtendedSubTypeByID($typeID, $orgID, $id){
        if($orgID==0){
            if($typeID==0){
                return $this->with(['extendedtype'])->with(['organization'])->where('extendedSubTypeId', '=', $id)->get();
            }else{
                return $this->with(['extendedtype'])->with(['organization'])->where('extendedTypeId', '=', $typeID)->where('extendedSubTypeId', '=', $id)->get();
            }
        }
        else{
            if($typeID==0){
                return $this->with(['extendedtype'])->with(['organization'])->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where('extendedSubTypeId', '=', $id)->get();
            }else{
                return $this->with(['extendedtype'])->with(['organization'])->where('extendedTypeId', '=', $typeID)->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where('extendedSubTypeId', '=', $id)->get();
            }

        }
    }
    //--------------------------------------------------------------------
    public function findExtendedSubType($typeID, $orgID, $field, $value){
        if($orgID==0){
            if($typeID==0){
                return $this->with(['extendedtype'])->with(['organization'])->where($field, 'like', "%{$value}%")->get();
            }else{
                return $this->with(['extendedtype'])->with(['organization'])->where('extendedTypeId', '=', $typeID)->where($field, 'like', "%{$value}%")->get();
            }

        }
        else{
            if($typeID==0){
                return $this->with(['extendedtype'])->with(['organization'])->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where($field, 'like', "%{$value}%")->get();
            }else{
                return $this->with(['extendedtype'])->with(['organization'])->where('extendedTypeId', '=', $typeID)->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where($field, 'like', "%{$value}%")->get();
            }

        }
    }
    //--------------------------------------------------------------------
}