<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/19
 * Time: 下午6:17
 */

namespace App\Models\Extend;

use Illuminate\Database\Eloquent\Model;

class Extended_attribute extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'extended_attribute';
    protected $primaryKey = "attributeId";
    protected $modifiers  = ['attributeId', 'attributeName',
        'displayName',//displayName
        'extendedSubTypeId',
        'attributeTypeId',
        'defaultValue',
        'notNullFlag',

        'lastUserId',//最后编辑者
        'ownerId',//system_organization 归属
        'ownership',//是否必填
        'dateCreated', //创建时间
        'dateUpdated', //更新时间
        'memo',//其他描述
        'reserved',//reserved
    ];
//    protected $dates      = ['dateCreated'];
    protected $dates_c      = ['dateCreated'];
    protected $dates_u      = ['dateUpdated'];
    //------------------------------------------------------------------
    public function getThis()
    {
        return $this;
    }
    //--------------------------------------------------------------------
    public function findAttributeName($attributeName)
    {
        return ($this->where('attributeName', '=', $attributeName)->get())->toArray();
    }
    //--------------------------------------------------------------------
    public  function findAttributeSubTypeId($extendedSubTypeId)
    {
        return ($this->where('extendedSubTypeId', '=', $extendedSubTypeId)->get())->toArray();
    }
    public  function findAttributeSubTypeId_data($extendedSubTypeId)
    {
        return ($this->where('extendedSubTypeId', '=', $extendedSubTypeId)->get());
    }
    //--------------------------------------------------------------------
    public function findById($id)
    {
        return ($this->where('attributeId', '=', $id)->get())->toArray();
    }
    //--------------------------------------------------------------------
    protected function myAttributes($attributetypeID, $subtypeID, $orgID, $field, $value){
        if( $value=='' ){
            return $this
                ->with(['extendedsubtype'])
                ->with(['extendedattributetype'])
                ->with(['organization'])
                ->where(function($q) use($attributetypeID){
                    if($attributetypeID==0){ return $q; }
                    else{ return $q->where('attributeTypeId', '=', $attributetypeID); }
                })
                ->where(function($q) use($subtypeID){
                    if($subtypeID==0){ return $q; }
                    else{ return $q->where('extendedSubTypeId', '=', $subtypeID); }
                })
                ->where(function($q) use($orgID){
                    if($orgID==0){ return $q; }
                    else{ return $q->where('ownerId', '=', null)->where('ownership', '=', 0)->orwhere('ownerId', '=',$orgID); }
                });
        }else{
            return $this
                ->with(['extendedsubtype'])
                ->with(['extendedattributetype'])
                ->with(['organization'])
                ->where(function($q) use($attributetypeID){
                    if($attributetypeID==0){ return $q; }
                    else{ return $q->where('attributeTypeId', '=', $attributetypeID); }
                })
                ->where(function($q) use($subtypeID){
                    if($subtypeID==0){ return $q; }
                    else{ return $q->where('extendedSubTypeId', '=', $subtypeID); }
                })
                ->where($field, 'like', "%{$value}%")
                ->where(function($q) use($orgID){
                    if($orgID==0){ return $q; }
                    else{ return $q->where('ownerId', '=', null)->where('ownership', '=', 0)->orwhere('ownerId', '=',$orgID); }
                });
        }
    }
    //--------------------------------------------------------------------
    protected function myPageing($attributetypeID,$subtypeID,$orgID, $perPage, $page, $sort, $order){
        //----------------------------------------------------------------
        $data = null;
        //----------------------------------------------------------------
        /*
        switch($order){
            case 'asc' :{ $data = $this->myTerms($orgID, '', '')->get()->sortBy    ($sort)->forPage($page, $perPage); break; }
            case 'desc':{ $data = $this->myTerms($orgID, '', '')->get()->sortByDesc($sort)->forPage($page, $perPage); break; }
        }
        */
        $data = $this->myAttributes($attributetypeID,$subtypeID,$orgID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage);
        //----------------------------------------------------------------
        if($data->isEmpty()){ return null; }
        //----------------------------------------------------------------
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
            if($tmp->attributeTypeId==null){ $tmp->attributeTypeName = ''; }
            else{$tmp->attributeTypeName = $tmp->extendedattributetype->attributeTypeName;}
            if($tmp->extendedSubTypeId==null){ $tmp->extendedSubTypeName = ''; }
            else{
				if($tmp->extendedsubtype!=null){ $tmp->extendedSubTypeName = $tmp->extendedsubtype->extendedSubTypeName;}
				else{ $tmp->extendedSubTypeName = ''; }
			}
            $retVal[] = $tmp;
        }
        //----------------------------------------------------------------
        return $retVal;
        //----------------------------------------------------------------
    }
    //--------------------------------------------------------------------
    protected function myPageingWithSearch($attributetypeID,$subtypeID,$orgID, $perPage, $page, $sort, $order, $field, $value){
        $data = null;
        /*
        switch($order){
            case 'asc' :{ $data = $this->myTerms($orgID, $field, $value)->get()->sortBy    ($sort)->forPage($page, $perPage); break; }
            case 'desc':{ $data = $this->myTerms($orgID, $field, $value)->get()->sortByDesc($sort)->forPage($page, $perPage); break; }
        }
        */
        $data = $this->myAttributes($attributetypeID,$subtypeID,$orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
        if($data->isEmpty()){ return null; }
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
            if($tmp->attributeTypeId==null){ $tmp->attributeTypeName = ''; }
            else{$tmp->attributeTypeName = $tmp->extendedattributetype->attributeTypeName;}
/*
            if($tmp->extendedSubTypeId==null){ $tmp->extendedSubTypeName = ''; }
            else{$tmp->extendedSubTypeName = $tmp->extendedsubtype->extendedSubTypeName;}
*/
            if($tmp->extendedSubTypeId==null){ $tmp->extendedSubTypeName = ''; }
            else{
				if($tmp->extendedsubtype!=null){ $tmp->extendedSubTypeName = $tmp->extendedsubtype->extendedSubTypeName;}
				else{ $tmp->extendedSubTypeName = ''; }
			}
            $retVal[] = $tmp;
        }
        return $retVal;
    }
    //--------------------------------------------------------------------
    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }

    public function extendedattributetype(){
        return $this->belongsTo('App\Models\Extend\Extended_attribute_type', 'attributeTypeId', 'attributeTypeId');
    }
    public function extendedsubtype(){
        return $this->belongsTo('App\Models\Extend\Extended_subtype', 'extendedSubTypeId', 'extendedSubTypeId');
    }
    //--------------------------------------------------------------------
    public function findAttributeByID($attributetypeID,$subtypeID,$orgID, $id){
        if($orgID==0){
            if($attributetypeID==0&&$subtypeID==0){
                return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])->where('attributeId', '=', $id)->get();
            }else{
               if($attributetypeID==0){
                   return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                       ->where('extendedSubTypeId', '=', $subtypeID)
                       ->where('attributeId', '=', $id)->get();
               }else{
                   if($subtypeID==0){
                       return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                           ->where('attributeTypeId', '=', $attributetypeID)
                           ->where('attributeId', '=', $id)->get();
                   }else{
                       return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                           ->where('attributeTypeId', '=', $attributetypeID)
                           ->where('extendedSubTypeId', '=', $subtypeID)
                           ->where('attributeId', '=', $id)->get();
                   }

               }
            }
        }
        else{
            if($attributetypeID==0&&$subtypeID==0){
                return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                    ->whereIn('ownerId', [$orgID,0])
                    ->orwhere('ownerId', '=', null)
                    ->where('attributeId', '=', $id)->get();
            }else{
                if($attributetypeID==0){
                    return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                        ->where('extendedSubTypeId', '=', $subtypeID)
                        ->whereIn('ownerId', [$orgID,0])
                        ->orwhere('ownerId', '=', null)
                        ->where('attributeId', '=', $id)->get();
                }else{
                    if($subtypeID==0){
                        return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                            ->where('attributeTypeId', '=', $attributetypeID)
                            ->whereIn('ownerId', [$orgID,0])
                            ->orwhere('ownerId', '=', null)
                            ->where('attributeId', '=', $id)->get();
                    }else{
                        return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                            ->where('attributeTypeId', '=', $attributetypeID)
                            ->where('extendedSubTypeId', '=', $subtypeID)
                            ->whereIn('ownerId', [$orgID,0])
                            ->orwhere('ownerId', '=', null)
                            ->where('attributeId', '=', $id)->get();
                    }
                }
            }
        }
    }
    //--------------------------------------------------------------------
    public function findAttribute($attributetypeID,$subtypeID,$orgID, $field, $value){
        if($orgID==0){
            if($attributetypeID==0&&$subtypeID==0){
                return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                    ->where($field, 'like', "%{$value}%")->get();
            }else{
                if($attributetypeID==0){
                    return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                        ->where('extendedSubTypeId', '=', $subtypeID)
                        ->where($field, 'like', "%{$value}%")->get();
                }else{
                    if($subtypeID==0){
                        return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                            ->where('attributeTypeId', '=', $attributetypeID)
                            ->where($field, 'like', "%{$value}%")->get();
                    }else{
                        return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                            ->where('extendedSubTypeId', '=', $subtypeID)
                            ->where('attributeTypeId', '=', $attributetypeID)
                            ->where($field, 'like', "%{$value}%")->get();
                    }
                }
            }

        }
        else{
            if($attributetypeID==0&&$subtypeID==0){
                return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                    ->whereIn('ownerId', [$orgID,0])
                    ->orwhere('ownerId', '=', null)
                    ->where($field, 'like', "%{$value}%")->get();
            }else{
                if($attributetypeID==0){
                    return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                        ->where('extendedSubTypeId', '=', $subtypeID)
                        ->whereIn('ownerId', [$orgID,0])
                        ->orwhere('ownerId', '=', null)
                        ->where($field, 'like', "%{$value}%")->get();
                }else{
                    if($subtypeID==0){
                        return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                            ->where('attributeTypeId', '=', $attributetypeID)
                            ->whereIn('ownerId', [$orgID,0])
                            ->orwhere('ownerId', '=', null)
                            ->where($field, 'like', "%{$value}%")->get();
                    }else{
                        return $this->with(['extendedsubtype'])->with(['extendedattributetype'])->with(['organization'])
                            ->where('extendedSubTypeId', '=', $subtypeID)
                            ->where('attributeTypeId', '=', $attributetypeID)
                            ->whereIn('ownerId', [$orgID,0])
                            ->orwhere('ownerId', '=', null)
                            ->where($field, 'like', "%{$value}%")->get();
                    }
                }
            }
        }
    }
    //--------------------------------------------------------------------
}