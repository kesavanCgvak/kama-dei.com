<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/19
 * Time: 下午6:21
 */

namespace App\Models\Extend;

use Illuminate\Database\Eloquent\Model;

class Extended_EAV extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'extended_eav';
    protected $primaryKey = "extendedEAVID";
    protected $modifiers  = ['extendedEAVID',
        'valueString',
        'valueBlob',
        'valueFloat',
        'valueDate',

        'extendedEntityId',
        'extendedAttributeId',


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
    //--------------------------------------------------------------------
    public function deletebyextendedEntityId($extendedEntityId){

       return  $this->where('extendedEntityId', '=', $extendedEntityId)->delete();
    }
    public function findbyextendedEntityId($extendedEntityId){

        return  $this->where('extendedEntityId', '=', $extendedEntityId)->get();
    }
    public function findExtendedEAVvalueString($valueString)
    {
        return ($this->where('valueString', '=', $valueString)->get())->toArray();
    }
    //--------------------------------------------------------------------
    public function findById($id)
    {
        return ($this->where('extendedEAVID', '=', $id)->get())->toArray();
    }
    //--------------------------------------------------------------------
    protected function myExtendedEAVs($extendedEntityId, $extendedAttributeId, $orgID, $field, $value){
        if( $value=='' ){
            return $this
                ->with(['extendedentity'])
                ->with(['extendedattribute'])
                ->with(['organization'])
                ->where(function($q) use($extendedEntityId){
                    if($extendedEntityId==0){ return $q; }
                    else{ return $q->where('extendedEntityId', '=', $extendedEntityId); }
                })
                ->where(function($q) use($extendedAttributeId){
                    if($extendedAttributeId==0){ return $q; }
                    else{ return $q->where('extendedAttributeId', '=', $extendedAttributeId); }
                })
                ->where(function($q) use($orgID){
                    if($orgID==0){ return $q; }
                    else{ return $q->where('ownerId', '=', null)->where('ownership', '=', 0)->orwhere('ownerId', '=',$orgID); }
                });
        }else{
            return $this
                ->with(['extendedentity'])
                ->with(['extendedattribute'])
                ->with(['organization'])
                ->where(function($q) use($extendedEntityId){
                    if($extendedEntityId==0){ return $q; }
                    else{ return $q->where('extendedEntityId', '=', $extendedEntityId); }
                })
                ->where(function($q) use($extendedAttributeId){
                    if($extendedAttributeId==0){ return $q; }
                    else{ return $q->where('extendedAttributeId', '=', $extendedAttributeId); }
                })
                ->where($field, 'like', "%{$value}%")
                ->where(function($q) use($orgID){
                    if($orgID==0){ return $q; }
                    else{ return $q->where('ownerId', '=', null)->where('ownership', '=', 0)->orwhere('ownerId', '=',$orgID); }
                });
        }
    }
    //--------------------------------------------------------------------
    protected function myPageing($extendedEntityId, $extendedAttributeId, $orgID, $perPage, $page, $sort, $order){
        //----------------------------------------------------------------
        $data = null;
        //----------------------------------------------------------------

        $data = $this->myExtendedEAVs($extendedEntityId, $extendedAttributeId, $orgID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage);
        //----------------------------------------------------------------
        if($data->isEmpty()){ return null; }
        //----------------------------------------------------------------
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
            if($tmp->extendedEntityId==null){ $tmp->extendedEntityName = ''; }
            else{$tmp->extendedEntityName = $tmp->extendedentity->extendedEntityName;}
            if($tmp->extendedAttributeId==null){ $tmp->attributeName = ''; }
            else{$tmp->attributeName = $tmp->extendedattribute->attributeName;}
            $retVal[] = $tmp;
        }
        //----------------------------------------------------------------
        return $retVal;
        //----------------------------------------------------------------
    }
    //--------------------------------------------------------------------
    protected function myPageingWithSearch($extendedEntityId, $extendedAttributeId, $orgID, $perPage, $page, $sort, $order, $field, $value){
        $data = null;

        $data = $this->myExtendedEAVs($extendedEntityId, $extendedAttributeId, $orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
        if($data->isEmpty()){ return null; }
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
            if($tmp->extendedEntityId==null){ $tmp->extendedEntityName = ''; }
            else{$tmp->extendedEntityName = $tmp->extendedentity->extendedEntityName;}
            if($tmp->extendedAttributeId==null){ $tmp->attributeName = ''; }
            else{$tmp->attributeName = $tmp->extendedattribute->attributeName;}
            $retVal[] = $tmp;
        }
        return $retVal;
    }
    //--------------------------------------------------------------------
    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }
    public function extendedentity(){
        return $this->belongsTo('App\Models\Extend\Extended_entity', 'extendedEntityId', 'extendedEntityId');
    }
    public function extendedattribute(){
        return $this->belongsTo('App\Models\Extend\Extended_attribute', 'extendedAttributeId', 'attributeId');
    }
    //--------------------------------------------------------------------
    public function findExtendedEAVByID($extendedEntityId, $extendedAttributeId, $orgID, $id){
        if($orgID==0){
            if($extendedEntityId==0&&$extendedAttributeId==0){
                return $this
                    ->with(['extendedentity'])
                    ->with(['extendedattribute'])
                    ->with(['organization'])
                    ->where('extendedEAVID', '=', $id)->get();
            }else{
                if($extendedEntityId==0){
                    return $this
                        ->with(['extendedentity'])
                        ->with(['extendedattribute'])
                        ->with(['organization'])
                        ->where('extendedAttributeId', '=', $extendedAttributeId)
                        ->where('extendedEAVID', '=', $id)->get();
                }else{
                    if($extendedAttributeId==0){
                        return $this
                            ->with(['extendedentity'])
                            ->with(['extendedattribute'])
                            ->with(['organization'])
                            ->where('extendedEntityId', '=', $extendedEntityId)
                            ->where('extendedEAVID', '=', $id)->get();
                    }else{
                        return $this
                            ->with(['extendedentity'])
                            ->with(['extendedattribute'])
                            ->with(['organization'])
                            ->where('extendedAttributeId', '=', $extendedAttributeId)
                            ->where('extendedEntityId', '=', $extendedEntityId)
                            ->where('extendedEAVID', '=', $id)->get();
                    }

                }
            }

        }
        else{
            if($extendedEntityId==0&&$extendedAttributeId==0){
                return $this
                    ->with(['extendedentity'])
                    ->with(['extendedattribute'])
                    ->with(['organization'])
                    ->whereIn('ownerId', [$orgID,0])
                    ->orwhere('ownerId', '=', null)
                    ->where('extendedEAVID', '=', $id)->get();
            }else{
                if($extendedEntityId==0){
                    return $this
                        ->with(['extendedentity'])
                        ->with(['extendedattribute'])
                        ->with(['organization'])
                        ->where('extendedAttributeId', '=', $extendedAttributeId)
                        ->whereIn('ownerId', [$orgID,0])
                        ->orwhere('ownerId', '=', null)
                        ->where('extendedEAVID', '=', $id)->get();
                }else{
                    if($extendedAttributeId==0){
                        return $this
                            ->with(['extendedentity'])
                            ->with(['extendedattribute'])
                            ->with(['organization'])
                            ->where('extendedEntityId', '=', $extendedEntityId)
                            ->whereIn('ownerId', [$orgID,0])
                            ->orwhere('ownerId', '=', null)
                            ->where('extendedEAVID', '=', $id)->get();
                    }else{
                        return $this
                            ->with(['extendedentity'])
                            ->with(['extendedattribute'])
                            ->with(['organization'])
                            ->where('extendedEntityId', '=', $extendedEntityId)
                            ->where('extendedAttributeId', '=', $extendedAttributeId)
                            ->whereIn('ownerId', [$orgID,0])
                            ->orwhere('ownerId', '=', null)
                            ->where('extendedEAVID', '=', $id)->get();
                    }

                }
            }

        }
    }
    //--------------------------------------------------------------------
    public function findExtendedEAV($extendedEntityId, $extendedAttributeId, $orgID, $field, $value){
        if($orgID==0){
            if($extendedEntityId==0&&$extendedAttributeId==0){
                return $this
                    ->with(['extendedentity'])
                    ->with(['extendedattribute'])
                    ->with(['organization'])
                    ->where($field, 'like', "%{$value}%")->get();
            }else{
                if($extendedEntityId==0){
                    return $this
                        ->with(['extendedentity'])
                        ->with(['extendedattribute'])
                        ->with(['organization'])
                        ->where('extendedAttributeId', '=', $extendedAttributeId)
                        ->where($field, 'like', "%{$value}%")->get();
                }else{
                    if($extendedAttributeId==0){
                        return $this
                            ->with(['extendedentity'])
                            ->with(['extendedattribute'])
                            ->with(['organization'])
                            ->where('extendedEntityId', '=', $extendedEntityId)
                            ->where($field, 'like', "%{$value}%")->get();
                    }else{
                        return $this
                            ->with(['extendedentity'])
                            ->with(['extendedattribute'])
                            ->with(['organization'])
                            ->where('extendedAttributeId', '=', $extendedAttributeId)
                            ->where('extendedEntityId', '=', $extendedEntityId)
                            ->where($field, 'like', "%{$value}%")->get();
                    }
                }
            }
        }
        else{
            if($extendedEntityId==0&&$extendedAttributeId==0){
                return $this
                    ->with(['extendedentity'])
                    ->with(['extendedattribute'])
                    ->with(['organization'])
                    ->whereIn('ownerId', [$orgID,0])
                    ->orwhere('ownerId', '=', null)
                    ->where($field, 'like', "%{$value}%")->get();
            }else{
                if($extendedEntityId==0){
                    return $this
                        ->with(['extendedentity'])
                        ->with(['extendedattribute'])
                        ->with(['organization'])
                        ->where('extendedAttributeId', '=', $extendedAttributeId)
                        ->whereIn('ownerId', [$orgID,0])
                        ->orwhere('ownerId', '=', null)
                        ->where($field, 'like', "%{$value}%")->get();
                }else{
                    if($extendedAttributeId==0){
                        return $this
                            ->with(['extendedentity'])
                            ->with(['extendedattribute'])
                            ->with(['organization'])
                            ->where('extendedEntityId', '=', $extendedEntityId)
                            ->whereIn('ownerId', [$orgID,0])
                            ->orwhere('ownerId', '=', null)
                            ->where($field, 'like', "%{$value}%")->get();
                    }else{
                        return $this
                            ->with(['extendedentity'])
                            ->with(['extendedattribute'])
                            ->with(['organization'])
                            ->where('extendedAttributeId', '=', $extendedAttributeId)
                            ->where('extendedEntityId', '=', $extendedEntityId)
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