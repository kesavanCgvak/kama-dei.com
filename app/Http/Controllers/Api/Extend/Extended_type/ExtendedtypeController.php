<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/28
 * Time: 下午4:21
 */

namespace App\Http\Controllers\Api\Extend\Extended_type;

use Illuminate\Http\Request;
use App\Models\Extend\Extended_type;
use App\Controllers;
class ExtendedtypeController extends \App\Http\Controllers\Controller{
    
    //显示---------------------------------------
    public function show($termId, $orgID, $id){
        $data = Extended_type::findExtendedTypeByID($termId, $orgID, $id);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[]      ];
        }else{
            return ['result'=>0, 'msg'=>''                , 'data'=>$data   ];
        }
    }

    //搜索---------------------------------------
    public function search($termId, $orgID, $field, $value){
        //-----------------------------------------------------------------------------------------
        $field = $this->dataFields( $field );
        if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $data = Extended_type::findExtendedType($termId, $orgID, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0  ];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data    ];
        }
    }

    //查找$orgID的显示所有+排序---------------------------------------
    public function showAll($termId,  $orgID ){ return $this->showAllSorted($termId, $orgID, 'extendedTypeId', 'asc'); }
    public function showAllSorted($termId, $orgID, $sort, $order){
        //-----------------------------------------------------------------------------------------
        $sort = $this->sortFields( $sort );
        if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $order = strtolower($order);
        switch($order){
            default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
            case 'asc' :
            case 'desc':{ break; }
        }
        //-----------------------------------------------------------------------------------------
        $data  = Extended_type::myExtendedType($termId, $orgID, '', '')->orderBy($sort, $order)->get();
        $total = Extended_type::myExtendedType($termId, $orgID, '', '')->count();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$total, 'data'=>$data];
        }
    }

    //分页查询---------------------------------------
    public function showPage($termId, $orgID, $perPage, $page){
        $data  = Extended_type::myPageing($termId, $orgID, $perPage, $page, 'extendedTypeId', 'asc');
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
        }
    }

    //分页查询+排序---------------------------------------
    public function showPageSorted($termId,  $orgID, $sort, $order, $perPage, $page ){
        //-----------------------------------------------------------------------------------------
        $sort = $this->sortFields( $sort );
        if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $order = strtolower($order);
        switch($order){
            case 'asc' :
            case 'desc':{ break; }
            default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
        }
        $count = Extended_type::myExtendedType($termId, $orgID, '', '')->count();

        $data  = Extended_type::myPageing($termId, $orgID, $perPage, $page, $sort, $order);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //分页查询+排序+搜索---------------------------------------
    public function showPageSortSearch($termId, $orgID, $sort, $order, $perPage, $page, $field, $value ){
        //-----------------------------------------------------------------------------------------
        $sort = $this->sortFields( $sort );
        if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $field = $this->dataFields( $field );
        if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $order = strtolower($order);
        switch($order){
            case 'asc' :
            case 'desc':{ break; }
            default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
        }
        $count = Extended_type::myExtendedType($termId, $orgID, $field, $value)->count();

        $data  = Extended_type::myPageingWithSearch($termId, $orgID, $perPage, $page, $sort, $order, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //排序字段转换---------------------------------------
    private function sortFields($sort){
        switch(strtolower($sort)){
            case 'termid'                   : { $sort="termId";   break; }
            case 'extendedtypeid'           : { $sort="extendedTypeId";    break; }
            case 'extendedtypename'         : { $sort="extendedTypeName";  break; }
            case 'chatintro'                : { $sort="chatIntro";        break; }
            case 'ownership'                : { $sort="ownership";          break; }
            case 'ownerid'                  : { $sort="ownerId";            break; }
            case 'datecreated'              : { $sort="dateCreated";        break; }
            case 'dateupdated'              : { $sort="dateUpdated";        break; }
            case 'lastuserid'               : { $sort="lastUserId";         break; }
            case 'memo'                     : { $sort="memo";               break; }
            case 'ownershipcaption'         : { $sort="ownership";          break; }
            case 'organizationshortname'    : { $sort="ownerId";            break; }
            case 'termname'                 : { $sort="termName";            break; }
            case 'reserved'                 : { $sort="reserved";  break; }
            default                         : { $sort = "";                        }
        }
        return $sort;
    }

    //数据字段转换---------------------------------------
    private function dataFields($field){
        switch(strtolower($field)){
            case 'termid'                   : { $field="termId";   break; }
            case 'extendedtypeid'           : { $field="extendedTypeId";   break; }
            case 'extendedtypename'         : { $field="extendedTypeName"; break; }
            case 'chatintro'                : { $field="chatIntro";       break; }
            case 'ownership'                : { $field="ownership";         break; }
            case 'ownerid'                  : { $field="ownerId";           break; }
            case 'datecreated'              : { $field="dateCreated";       break; }
            case 'dateupdated'              : { $field="dateUpdated";       break; }
            case 'lastuserid'               : { $field="lastUserId";        break; }
            case 'memo'                     : { $field="memo";              break; }
            case 'reserved'                 : { $sort="reserved";  break; }
            default                         : { $field = "";                       }
        }
        return $field;
    }

    //改---------------------------------------
    public function editRow($orgID, $id, Request $request){
        try{
            $extended_type = Extended_type::find($id);
            if(is_null($extended_type) ){
                return ['result'=>1, 'msg'=>"extendedType not found"];
            }else{
                if($extended_type->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't edit this extendedType"]; }
                $extended_type->extendedTypeName       = trim($request->input('extendedTypeName'              ));
                $extended_type->ownership              = trim($request->input('ownership'                      ));
                $extended_type->ownerId                = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
                $extended_type->chatIntro              = trim($request->input('chatIntro'                    ));
                $extended_type->memo                   = trim($request->input('memo'                           ));
                $extended_type->lastUserId             = trim($request->input('userID'                         ));
                $extended_type->dateUpdated            = date("Y-m-d H:i:s"                                  );
                $extended_type->termId                 = trim($request->input('termId'                         ));
                $extended_type->reserved                 = trim($request->input('reserved'                         ));
                if($extended_type->extendedTypeName     ==''){ return ['result'=>1, 'msg'=>'extendedType name is empty'];             }
                if($extended_type->ownership            ==''){ return ['result'=>1, 'msg'=>'extendedType ownership is empty'];        }
                if($extended_type->ownerId              ==''){ return ['result'=>1, 'msg'=>'extendedType owner ID is empty'];         }
                if($extended_type->lastUserId           ==''){ return ['result'=>1, 'msg'=>'extendedType last user id is empty'];     }
                if($extended_type->termId               ==''){ return ['result'=>1, 'msg'=>'extendedType last termId is empty']; }



                $tmp = $extended_type->save();
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //增---------------------------------------
    public function insertRow($orgID, Request $request){
        try{
            $extendedTypeName = $request->input('extendedTypeName');
            $tmp      = Extended_type::where('extendedTypeName','=',strtolower($extendedTypeName) )->first();
            if(!is_null($tmp) ){
                return ['result'=>1, 'msg'=>'extendedType already exists'];
            }
            $extended_type = new Extended_type;
            $extended_type->extendedTypeName    = trim($request->input('extendedTypeName'              ));
            $extended_type->ownership           = trim($request->input('ownership'                      ));
            $extended_type->ownerId             = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
            $extended_type->dateCreated         = date("Y-m-d H:i:s");
            $extended_type->dateUpdated         = date("Y-m-d H:i:s");
            $extended_type->lastUserId          = trim($request->input('userID'                         ));
            $extended_type->chatIntro           = trim($request->input('chatIntro'                    ));
            $extended_type->memo                = trim($request->input('memo'                           ));
            $extended_type->termId              = trim($request->input('termId'                         ));
            $extended_type->reserved                 = trim($request->input('reserved'                         ));

            if($extended_type->extendedTypeName     ==''){ return ['result'=>1, 'msg'=>'extendedType name is empty'];         }
            if($extended_type->ownership            ==''){ return ['result'=>1, 'msg'=>'extendedType ownership is empty'];    }
            if($extended_type->ownerId              ==''){ return ['result'=>1, 'msg'=>'extendedType owner ID is empty'];     }
            if($extended_type->lastUserId           ==''){ return ['result'=>1, 'msg'=>'extendedType last user id is empty']; }
            if($extended_type->termId               ==''){ return ['result'=>1, 'msg'=>'extendedType termId is empty'];  }


            $tmp = $extended_type->save();
            if($tmp){ return ['result'=>0, 'extendedTypeId'=>$extended_type->extendedTypeId]; }
            else{ return ['result'=>1, 'msg'=>'']; }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //删---------------------------------------
    public function deleteRow($orgID, $id){
        try{
            $extended_type = Extended_type::find($id);
            if(is_null($extended_type) ){
                return ['result'=>1, 'msg'=>"extendedType not found"];
            }else{
                if($extended_type->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this extendedType"]; }
                $tmp = $extended_type->delete($id);
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //---------------------------------------


    //---------------------------------------*/

}