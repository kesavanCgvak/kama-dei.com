<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/30
 * Time: 下午6:29
 */

namespace App\Http\Controllers\Api\Extend\Extended_subtype;

use Illuminate\Http\Request;
use App\Models\Extend\Extended_subtype;
use App\Models\Extend\Extended_attribute;
use App\Controllers;

class ExtendedsubtypeController extends \App\Http\Controllers\Controller{

    //显示---------------------------------------
    public function show($typeID, $orgID, $id){
        $data = Extended_subtype::findExtendedSubTypeByID($typeID, $orgID, $id);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[]      ];
        }else{
            return ['result'=>0, 'msg'=>''                , 'data'=>$data   ];
        }
    }

    //搜索---------------------------------------
    public function search($typeID, $orgID, $field, $value){
        //-----------------------------------------------------------------------------------------
        $field = $this->dataFields( $field );
        if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $data = Extended_subtype::findExtendedSubType($typeID, $orgID, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0  ];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data    ];
        }
    }

    //查找$orgID的显示所有+排序---------------------------------------
    public function showAll($typeID,  $orgID ){ return $this->showAllSorted($typeID, $orgID, 'extendedSubTypeName', 'asc'); }
    public function showAllSorted($typeID, $orgID, $sort, $order){
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
        $data  = Extended_subtype::myExtendedSubTypes($typeID, $orgID, '', '')->orderBy($sort, $order)->get();
        $total = Extended_subtype::myExtendedSubTypes($typeID, $orgID, '', '')->count();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$total, 'data'=>$data];
        }
    }

    //分页查询---------------------------------------
    public function showPage($typeID, $orgID, $perPage, $page){
        $data  = Extended_subtype::myPageing($typeID, $orgID, $perPage, $page, 'extendedSubTypeId', 'asc');
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
        }
    }

    //分页查询+排序---------------------------------------
    public function showPageSorted($typeID,  $orgID, $sort, $order, $perPage, $page, $showGlobal=1){
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
        $count = Extended_subtype::myExtendedSubTypes($typeID, $orgID, '', '', $showGlobal)->count();

        $data  = Extended_subtype::myPageing($typeID, $orgID, $perPage, $page, $sort, $order, $showGlobal);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //分页查询+排序+搜索---------------------------------------
    public function showPageSortSearch($typeID, $orgID, $sort, $order, $perPage, $page, $field, $value, $showGlobal=1){
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
        $count = Extended_subtype::myExtendedSubTypes($typeID, $orgID, $field, $value, $showGlobal)->count();

        $data  = Extended_subtype::myPageingWithSearch($typeID, $orgID, $perPage, $page, $sort, $order, $field, $value, $showGlobal);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //排序字段转换---------------------------------------
    private function sortFields($sort){
        switch(strtolower($sort)){
            case 'extendedsubtypeid'        : { $sort="extendedSubTypeId";   break; }
            case 'extendedsubtypename'      : { $field="extendedSubTypeName"; break; }
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
            case 'extendedsubtypeid'        : { $field="extendedSubTypeId";   break; }
            case 'extendedsubtypename'      : { $field="extendedSubTypeName"; break; }
            case 'extendedtypeid'           : { $field="extendedTypeId";   break; }
            case 'extendedtypename'         : { $field="extendedTypeName"; break; }
            case 'chatintro'                : { $field="chatIntro";       break; }
            case 'ownership'                : { $field="ownership";         break; }
            case 'ownerid'                  : { $field="ownerId";           break; }
            case 'datecreated'              : { $field="dateCreated";       break; }
            case 'dateupdated'              : { $field="dateUpdated";       break; }
            case 'lastuserid'               : { $field="lastUserId";        break; }
            case 'memo'                     : { $field="memo";              break; }
            case 'reserved'                 : { $field="reserved";  break; }
            default                         : { $field = "";                       }
        }
        return $field;
    }

    //改---------------------------------------
    public function editRow($orgID, $id, Request $request){
        try{
            $extended_subtype = Extended_subtype::find($id);
            if(is_null($extended_subtype) ){
                return ['result'=>1, 'msg'=>"extendedSubType not found"];
            }else{
                if($extended_subtype->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't edit this extendedSubType"]; }
                $extended_subtype->extendedSubTypeName       = trim($request->input('extendedSubTypeName'              ));
                $extended_subtype->ownership              = trim($request->input('ownership'                      ));
                $extended_subtype->ownerId                = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
                $extended_subtype->chatIntro              = trim($request->input('chatIntro'                    ));
                $extended_subtype->memo                   = trim($request->input('memo'                           ));
                $extended_subtype->lastUserId             = trim($request->input('userID'                         ));
                $extended_subtype->dateUpdated            = date("Y-m-d H:i:s"                                  );
                $extended_subtype->extendedTypeId                 = trim($request->input('extendedTypeId'                         ));
                $extended_subtype->reserved                 = trim($request->input('reserved'                         ));
                if($extended_subtype->extendedSubTypeName     ==''){ return ['result'=>1, 'msg'=>'extendedSubType name is empty'];             }
                if($extended_subtype->ownership            ==''){ return ['result'=>1, 'msg'=>'extendedSubType ownership is empty'];        }
                if($extended_subtype->ownerId              ==''){ return ['result'=>1, 'msg'=>'extendedSubType owner ID is empty'];         }
                if($extended_subtype->lastUserId           ==''){ return ['result'=>1, 'msg'=>'extendedSubType last user id is empty'];     }
                if($extended_subtype->extendedTypeId               ==''){ return ['result'=>1, 'msg'=>'extendedSubType last extendedTypeId is empty']; }



                $tmp = $extended_subtype->save();

                if($tmp){
                    //-----------------
                    $data = (new Extended_attribute)->findAttributeSubTypeId_data($id);
                    if(is_null($data) ){

                    }else{

                        foreach( $data as $key=>$tmp2 ){
                            $tmp2->ownerId              = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
                            $tmp2->save();
                            //--
                        }
                    }
                    //-----------------
                    //return ['result'=>0, 'extendedSubTypeId'=>$extended_subtype->extendedSubTypeId];
                }


                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //增---------------------------------------
    public function insertRow($orgID, Request $request){
        try{
            $extendedSubTypeName = $request->input('extendedSubTypeName');
            $tmp      = Extended_subtype::where('extendedSubTypeName','=',strtolower($extendedSubTypeName) )->first();
            if(!is_null($tmp) ){
                return ['result'=>1, 'msg'=>'extendedSubType already exists'];
            }
            $extended_subtype = new Extended_subtype;
            $extended_subtype->extendedSubTypeName = trim($request->input('extendedSubTypeName'              ));
            $extended_subtype->ownership           = trim($request->input('ownership'                      ));
            $extended_subtype->ownerId             = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
            $extended_subtype->dateCreated         = date("Y-m-d H:i:s");
            $extended_subtype->dateUpdated         = date("Y-m-d H:i:s");
            $extended_subtype->lastUserId          = trim($request->input('userID'                         ));
            $extended_subtype->chatIntro           = trim($request->input('chatIntro'                    ));
            $extended_subtype->memo                = trim($request->input('memo'                           ));
            $extended_subtype->extendedTypeId   = trim($request->input('extendedTypeId'                         ));
            $extended_subtype->reserved                 = trim($request->input('reserved'                         ));

            if($extended_subtype->extendedSubTypeName  ==''){ return ['result'=>1, 'msg'=>'extendedSubType name is empty'];         }
            if($extended_subtype->ownership            ==''){ return ['result'=>1, 'msg'=>'extendedSubType ownership is empty'];    }
            if($extended_subtype->ownerId              ==''){ return ['result'=>1, 'msg'=>'extendedSubType owner ID is empty'];     }
            if($extended_subtype->lastUserId           ==''){ return ['result'=>1, 'msg'=>'extendedSubType last user id is empty']; }
            if($extended_subtype->extendedTypeId    ==''){ return ['result'=>1, 'msg'=>'extendedSubType extendedTypeId is empty'];  }


            $tmp = $extended_subtype->save();
            if($tmp){ return ['result'=>0, 'extendedSubTypeId'=>$extended_subtype->extendedSubTypeId]; }
            else{ return ['result'=>1, 'msg'=>'']; }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //复制---------------------------------------
    public function copyRow($orgID, $id,  Request $request){
        try{
            $extendedSubTypeName = $request->input('extendedSubTypeName');
            $extendedSubTypeName=$extendedSubTypeName.'_copy';
            $tmp      = Extended_subtype::where('extendedSubTypeName','=',strtolower($extendedSubTypeName) )->first();
            if(!is_null($tmp) ){
                return ['result'=>1, 'msg'=>'extendedSubType already exists'];
            }
            $extended_subtype = new Extended_subtype;
            $extended_subtype->extendedSubTypeName = trim($extendedSubTypeName);
            $extended_subtype->ownership           = trim($request->input('ownership'                      ));
            $extended_subtype->ownerId             = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
            $extended_subtype->dateCreated         = date("Y-m-d H:i:s");
            $extended_subtype->dateUpdated         = date("Y-m-d H:i:s");
            $extended_subtype->lastUserId          = trim($request->input('userID'                         ));
            $extended_subtype->chatIntro           = trim($request->input('chatIntro'                    ));
            $extended_subtype->memo                = trim($request->input('memo'                           ));
            $extended_subtype->extendedTypeId   = trim($request->input('extendedTypeId'                         ));
            $extended_subtype->reserved                 = trim($request->input('reserved'                         ));

            if($extended_subtype->extendedSubTypeName  ==''){ return ['result'=>1, 'msg'=>'extendedSubType name is empty'];         }
            if($extended_subtype->ownership            ==''){ return ['result'=>1, 'msg'=>'extendedSubType ownership is empty'];    }
            if($extended_subtype->ownerId              ==''){ return ['result'=>1, 'msg'=>'extendedSubType owner ID is empty'];     }
            if($extended_subtype->lastUserId           ==''){ return ['result'=>1, 'msg'=>'extendedSubType last user id is empty']; }
            if($extended_subtype->extendedTypeId    ==''){ return ['result'=>1, 'msg'=>'extendedSubType extendedTypeId is empty'];  }


            $tmp = $extended_subtype->save();
            if($tmp){
                //-----------------
                $data = (new Extended_attribute)->findAttributeSubTypeId($id);
                if(is_null($data) ){

                }else{

                    foreach( $data as $key=>$tmp2 ){
                        $extended_attribute = (new Extended_attribute);
                        $extended_attribute->attributeName      = $tmp2["attributeName"];
                        $extended_attribute->ownership           = $tmp2["ownership"];
                        $extended_attribute->ownerId              = $tmp2["ownerId"];
                        $extended_attribute->dateCreated          = date("Y-m-d H:i:s");
                        $extended_attribute->dateUpdated          = date("Y-m-d H:i:s");
                        $extended_attribute->lastUserId           = $tmp2["lastUserId"];
                        $extended_attribute->displayName          = $tmp2["displayName"];
                        $extended_attribute->memo                 = $tmp2["memo"];
                        $extended_attribute->extendedSubTypeId    = $extended_subtype->extendedSubTypeId;
                        $extended_attribute->attributeTypeId      = $tmp2["attributeTypeId"];
                        $extended_attribute->defaultValue         = $tmp2["defaultValue"];
                        $extended_attribute->notNullFlag          = $tmp2["notNullFlag"];
                        $extended_attribute->reserved             = $tmp2["reserved"];

                        $extended_attribute->save();
                        //--
                    }

                }
                //-----------------
                return ['result'=>0, 'extendedSubTypeId'=>$extended_subtype->extendedSubTypeId];
            }
            else{ return ['result'=>1, 'msg'=>'']; }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //删---------------------------------------
    public function deleteRow($orgID, $id){
        try{
//frd
            $extended_subtype = Extended_subtype::find($id);
            if(is_null($extended_subtype) ){
                return ['result'=>1, 'msg'=>"extendedSubType not found"];
            }else{
                if($extended_subtype->ownerId!=$orgID && $orgID!=0)
					{ return ['result'=>1, 'msg'=>"You can't delete this extendedSubType"]; }
				$isUsed = \App\Models\Extend\Extended_entity::where('extendedSubTypeId', $extended_subtype->extendedSubTypeId)->count();
				if($isUsed!=0)
					{ return ['result'=>1, 'msg'=>"You can't delete this extendedSubType [used in EXTENDED DATA]"]; }
                $tmp = $extended_subtype->delete($id);
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //---------------------------------------



    //---------------------------------------*/

}