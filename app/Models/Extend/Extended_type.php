<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/19
 * Time: 下午6:23
 */

namespace App\Models\Extend;

use Illuminate\Database\Eloquent\Model;

class Extended_type extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysql2';
    protected $table      = 'extended_type';
    protected $primaryKey = "extendedTypeId";
    protected $modifiers  = ['extendedTypeId', 'extendedTypeName',
        'termId',
        'lastUserId',//最后编辑者
        'ownerId',//system_organization 归属
        'chatIntro',//属性字段类型
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
    public function findExtendedTypeName($extendedTypeName)
    {
        return ($this->where('extendedTypeName', '=', $extendedTypeName)->get())->toArray();
    }
    //--------------------------------------------------------------------
    public function findById($id)
    {
        return ($this->where('extendedTypeId', '=', $id)->get())->toArray();
    }
    //--------------------------------------------------------------------
    protected function myExtendedType($termId, $orgID, $field, $value){
        if( $value=='' ){
            if($termId==0){
                return $this
                    ->with(['organization'])
                    ->leftJoin('relation', 'extended_type.termId', '=', 'relation.relationId')
                    ->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
                    //->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
                    //->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
                    ->where(function($q) use($orgID){
                        if($orgID==0){ return $q; }
                        else{ return $q->where('extended_type.ownerId', '=', null)->where('extended_type.ownership', '=', 0)->orwhere('extended_type.ownerId', '=',$orgID); }
                    })
                    ->select(
                        'extended_type.*',
                        \DB::raw('CONCAT(leftTerm.termName) as termName')
                         //\DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as termName')
                    );
            }else{
                return $this
                    ->with(['organization'])
                    ->leftJoin('relation', 'extended_type.termId', '=', 'relation.relationId')
                    ->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
                    //->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
                    //->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
                    ->where(function($q) use($orgID){
                        if($orgID==0){ return $q; }
                        else{ return $q->where('extended_type.ownerId', '=', null)->where('extended_type.ownership', '=', 0)->orwhere('extended_type.ownerId', '=',$orgID); }
                    })
                    ->where('extended_type.termId', '=', $termId)
                    ->select(
                        'extended_type.*',
                        \DB::raw('CONCAT(leftTerm.termName) as termName')
                    );


            }
        }else{
            if($termId==0){
                return $this
                    ->with(['organization'])
                    ->leftJoin('relation', 'extended_type.termId', '=', 'relation.relationId')
                    ->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
                    //->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
                    //->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
                    ->where($field, 'like', "%{$value}%")
                    ->where(function($q) use($orgID){
                        if($orgID==0){ return $q; }
                        else{ return $q->where('extended_type.ownerId', '=', null)->where('extended_type.ownership', '=', 0)->orwhere('extended_type.ownerId', '=',$orgID); }
                    })

                    ->select(
                        'extended_type.*',
                        \DB::raw('CONCAT(leftTerm.termName) as termName')
                    );


            }else{
                return $this
                    ->with(['organization'])
                    ->leftJoin('relation', 'extended_type.termId', '=', 'relation.relationId')
                    ->leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')
                    //->leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')
                    //->leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
                    ->where($field, 'like', "%{$value}%")
                    ->where(function($q) use($orgID){
                        if($orgID==0){ return $q; }
                        else{ return $q->where('extended_type.ownerId', '=', null)->where('extended_type.ownership', '=', 0)->orwhere('extended_type.ownerId', '=',$orgID); }
                    })
                    ->where('extended_type.termId', '=', $termId)
                    ->select(
                        'extended_type.*',
                        \DB::raw('CONCAT(leftTerm.termName) as termName')
                    );



            }

        }
    }
    //--------------------------------------------------------------------

    protected function myPageing($termId, $orgID, $perPage, $page, $sort, $order){
        //----------------------------------------------------------------
        $data = null;

        $data = $this->myExtendedType($termId,$orgID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage);
        //----------------------------------------------------------------
        if($data->isEmpty()){ return null; }
        //----------------------------------------------------------------
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName;}
            /*if($tmp->termId==null){ $tmp->termName = env('BASE_ORGANIZATION'); }
            else{$tmp->termName = $tmp->term->termName;}*/

            $retVal[] = $tmp;
        }
        //----------------------------------------------------------------
        return $retVal;
        //----------------------------------------------------------------
    }
    //--------------------------------------------------------------------
    protected function myPageingWithSearch($termId, $orgID, $perPage, $page, $sort, $order, $field, $value){
        $data = null;

        $data = $this->myExtendedType($termId, $orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
        if($data->isEmpty()){ return null; }
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
            else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
            /*if($tmp->termId==null){ $tmp->termName = env('BASE_ORGANIZATION'); }
            else{$tmp->termName = $tmp->term->termName;}*/
            $retVal[] = $tmp;
        }
        return $retVal;
    }
    //--------------------------------------------------------------------
    public function organization(){
        return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }
    //--------------------------------------------------------------------
    //--------------------------------------------------------------------
    public function term(){
        return $this->belongsTo('App\Relation', 'termId', 'relationId');
    }
    //--------------------------------------------------------------------
    public function findExtendedTypeByID($termId, $orgID, $id){
        if($orgID==0){
            if($termId==0){
                return $this->with(['term'])->with(['organization'])->where('extendedTypeId', '=', $id)->get();
            } else{return $this->with(['term'])->with(['organization'])->where('termId', '=', $termId)->where('extendedTypeId', '=', $id)->get();}
        }
        else{
            if($termId==0){
                return $this->with(['term'])->with(['organization'])->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where('extendedTypeId', '=', $id)->get();
            } else{
                return $this->with(['term'])->with(['organization'])->where('termId', '=', $termId)->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where('extendedTypeId', '=', $id)->get();
            }

        }
    }
    //--------------------------------------------------------------------
    public function findExtendedType($termId, $orgID, $field, $value){
        if($orgID==0){
            if($termId==0){
                return $this->with(['term'])->with(['organization'])->where($field, 'like', "%{$value}%")->get();
            }else{
                return $this->with(['term'])->with(['organization'])->where('termId', '=', $termId)->where($field, 'like', "%{$value}%")->get();
            }

        }
        else{
            if($termId==0){
                return $this->with(['term'])->with(['organization'])->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where($field, 'like', "%{$value}%")->get();
            }else{
                return $this->with(['term'])->with(['organization'])->where('termId', '=', $termId)->whereIn('ownerId', [$orgID,0])->orwhere('ownerId', '=', null)->where($field, 'like', "%{$value}%")->get();
            }
        }
    }
    //--------------------------------------------------------------------
}