<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/19
 * Time: 下午6:19
 */

namespace App\Models\Extend;

use Illuminate\Database\Eloquent\Model;

class Extended_attribute_type extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'extended_attribute_type';
    protected $primaryKey = "attributeTypeId";
    protected $modifiers  = ['attributeTypeId', 'attributeTypeName',
        'lastUserId',//最后编辑者
        'ownerId',//system_organization 归属
        'storageType',//属性字段类型
        'ownership',//是否必填
        'dateCreated', //创建时间
        'dateUpdated', //更新时间
        'memo',//其他描述
        'reserved',//reserved
    ];
//    protected $dates      = ['dateCreated'];
    protected $dates_c      = ['dateCreated'];
    protected $dates_u      = ['dateUpdated'];
    //--------------------------------------------------------------------
    public function findAttributeTypeName($attributeTypeName)
    {
        return ($this->where('attributeTypeName', '=', $attributeTypeName)->get())->toArray();
    }
    //--------------------------------------------------------------------
    public function findById($id)
    {
        return ($this->where('attributeTypeId', '=', $id)->get())->toArray();
    }
    //--------------------------------------------------------------------
    protected function myAttributeTypes($orgID, $field, $value){
        if( $value=='' ){
            return $this
                ->with(['organization'])
                ->where(function($q) use($orgID){
                    if($orgID==0){ return $q; }
                    else{ return $q->where('ownerId', '=', null)->where('ownership', '=', 0)->orwhere('ownerId', '=',$orgID); }
                });
        }else{
            return $this
                ->with(['organization'])
                ->where($field, 'like', "%{$value}%")
                ->where(function($q) use($orgID){
                    if($orgID==0){ return $q; }
                    else{ return $q->where('ownerId', '=', null)->where('ownership', '=', 0)->orwhere('ownerId', '=',$orgID); }
                });
        }
    }
    //--------------------------------------------------------------------
    protected function myPageing($orgID, $perPage, $page, $sort, $order){
        //----------------------------------------------------------------
        $data = null;
        //----------------------------------------------------------------
        /*
        switch($order){
            case 'asc' :{ $data = $this->myTerms($orgID, '', '')->get()->sortBy    ($sort)->forPage($page, $perPage); break; }
            case 'desc':{ $data = $this->myTerms($orgID, '', '')->get()->sortByDesc($sort)->forPage($page, $perPage); break; }
        }
        */
        $data = $this->myAttributeTypes($orgID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage);
        //----------------------------------------------------------------
        if($data->isEmpty()){ return null; }
        //----------------------------------------------------------------
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
            /*
                        switch($tmp->ownership){
                            case 0:{ $tmp->ownershipCaption = 'Public'; break; }
                            case 1:{ $tmp->ownershipCaption = 'Protected'; break; }
                            case 2:{ $tmp->ownershipCaption = 'Private'; break; }
                            default:{ $tmp->ownershipCaption = 'Other'; break; }
                        }
            */
            $retVal[] = $tmp;
        }
        //----------------------------------------------------------------
        return $retVal;
        //----------------------------------------------------------------
    }
    //--------------------------------------------------------------------
    protected function myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value){
        $data = null;
        /*
        switch($order){
            case 'asc' :{ $data = $this->myTerms($orgID, $field, $value)->get()->sortBy    ($sort)->forPage($page, $perPage); break; }
            case 'desc':{ $data = $this->myTerms($orgID, $field, $value)->get()->sortByDesc($sort)->forPage($page, $perPage); break; }
        }
        */
        $data = $this->myAttributeTypes($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
        if($data->isEmpty()){ return null; }
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
            /*
                        switch($tmp->ownership){
                            case 0:{ $tmp->ownershipCaption = 'Public'; break; }
                            case 1:{ $tmp->ownershipCaption = 'Protected'; break; }
                            case 2:{ $tmp->ownershipCaption = 'Private'; break; }
                            default:{ $tmp->ownershipCaption = 'Other'; break; }
                        }
            */
            $retVal[] = $tmp;
        }
        return $retVal;
    }
    //--------------------------------------------------------------------
    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }
    //--------------------------------------------------------------------
    public function findAttributeTypeByID($orgID, $id){
        if($orgID==0){ return $this->with(['organization'])->where('attributeTypeId', '=', $id)->get(); }
        else{ return $this->with(['organization'])->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where('attributeTypeId', '=', $id)->get(); }
    }
    //--------------------------------------------------------------------
    public function findAttributeType($orgID, $field, $value){
        if($orgID==0){ return $this->with(['organization'])->where($field, 'like', "%{$value}%")->get(); }
        else{ return $this->with(['organization'])->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where($field, 'like', "%{$value}%")->get(); }
    }
    //--------------------------------------------------------------------
}