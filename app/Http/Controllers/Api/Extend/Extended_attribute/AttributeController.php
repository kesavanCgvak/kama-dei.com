<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/31
 * Time: 下午1:55
 */

namespace App\Http\Controllers\Api\Extend\Extended_attribute;

use Illuminate\Http\Request;
use App\Models\Extend\Extended_attribute;
use App\Controllers;
class AttributeController extends \App\Http\Controllers\Controller{

    //显示---------------------------------------
    public function show($attributetypeID, $subtypeID, $orgID, $id){
        $data = Extended_attribute::findAttributeNameByID($attributetypeID, $subtypeID, $orgID, $id);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[]      ];
        }else{
            return ['result'=>0, 'msg'=>''                , 'data'=>$data   ];
        }
    }

    //搜索---------------------------------------
    public function search($attributetypeID, $subtypeID, $orgID, $field, $value){
        //-----------------------------------------------------------------------------------------
        $field = $this->dataFields( $field );
        if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $data = Extended_attribute::findAttributeName($attributetypeID, $subtypeID, $orgID, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0  ];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data    ];
        }
    }

    //查找$orgID的显示所有+排序---------------------------------------
    public function showAll($attributetypeID, $subtypeID, $orgID ){ return $this->showAllSorted($attributetypeID, $subtypeID, $orgID, 'attributeId', 'asc'); }
    public function showAllSorted($attributetypeID, $subtypeID, $orgID, $sort, $order){
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
        $data  = Extended_attribute::myAttributes($attributetypeID, $subtypeID, $orgID, '', '')->orderBy($sort, $order)->get();
        $total = Extended_attribute::myAttributes($attributetypeID, $subtypeID, $orgID, '', '')->count();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$total, 'data'=>$data];
        }
    }

    //分页查询---------------------------------------
    public function showPage($attributetypeID, $subtypeID, $orgID, $perPage, $page){
        $data  = Extended_attribute::myPageing($attributetypeID, $subtypeID, $orgID, $perPage, $page, 'attributeId', 'asc');
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
        }
    }

    //分页查询+排序---------------------------------------
    public function showPageSorted($attributetypeID, $subtypeID, $orgID, $sort, $order, $perPage, $page ){
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
        $count = Extended_attribute::myAttributes($attributetypeID, $subtypeID, $orgID, '', '')->count();

        $data  = Extended_attribute::myPageing($attributetypeID, $subtypeID, $orgID, $perPage, $page, $sort, $order);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //分页查询+排序+搜索---------------------------------------
    public function showPageSortSearch($attributetypeID, $subtypeID, $orgID, $sort, $order, $perPage, $page, $field, $value ){
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
        $count = Extended_attribute::myAttributes($attributetypeID, $subtypeID, $orgID, $field, $value)->count();

        $data  = Extended_attribute::myPageingWithSearch($attributetypeID, $subtypeID, $orgID, $perPage, $page, $sort, $order, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //排序字段转换---------------------------------------
    private function sortFields($sort){
        switch(strtolower($sort)){

            case 'attributeid'              : { $sort="attributeId";    break; }
            case 'attributename'            : { $sort="attributeName";  break; }
            case 'displayname'                : { $sort="displayName";        break; }
            case 'ownership'                : { $sort="ownership";          break; }
            case 'ownerid'                  : { $sort="ownerId";            break; }
            case 'datecreated'              : { $sort="dateCreated";        break; }
            case 'dateupdated'              : { $sort="dateUpdated";        break; }
            case 'lastuserid'               : { $sort="lastUserId";         break; }
            case 'memo'                     : { $sort="memo";               break; }
            case 'ownershipcaption'         : { $sort="ownership";          break; }
            case 'organizationshortname'    : { $sort="ownerId";            break; }

            case 'extendedsubtypeid'        : { $sort="extendedSubTypeId";  break; }
            case 'extendedsubtypename'      : { $sort="extendedSubTypeName";break; }
            case 'attributetypeid'          : { $sort="attributeTypeId";    break; }
            case 'attributetypename'        : { $sort="attributeTypeName";  break; }

            case 'defaultvalue'             : { $sort="defaultValue";  break; }
            case 'notnullflag'              : { $sort="notNullFlag";  break; }
            case 'reserved'                 : { $sort="reserved";  break; }

            default                         : { $sort = "";                        }
        }
        return $sort;
    }

    //数据字段转换---------------------------------------
    private function dataFields($field){
        switch(strtolower($field)){

            case 'attributeid'           : { $field="attributeId";   break; }
            case 'attributename'         : { $field="attributeName"; break; }
            case 'displayname'                : { $field="displayName";       break; }
            case 'ownership'                : { $field="ownership";         break; }
            case 'ownerid'                  : { $field="ownerId";           break; }
            case 'datecreated'              : { $field="dateCreated";       break; }
            case 'dateupdated'              : { $field="dateUpdated";       break; }
            case 'lastuserid'               : { $field="lastUserId";        break; }
            case 'memo'                     : { $field="memo";              break; }

            case 'extendedsubtypeid'        : { $field="extendedSubTypeId";  break; }
            case 'extendedsubtypename'      : { $field="extendedSubTypeName";break; }
            case 'attributetypeid'          : { $field="attributeTypeId";    break; }
            case 'attributetypename'        : { $field="attributeTypeName";  break; }

            case 'defaultvalue'             : { $field="defaultValue";  break; }
            case 'notnullflag'              : { $field="notNullFlag";  break; }
            case 'reserved'                 : { $field="reserved";  break; }
            default                         : { $field = "";                       }
        }
        return $field;
    }

    //改---------------------------------------
    public function editRow($orgID, $id, Request $request){
        try{
            $extended_attribute = Extended_attribute::find($id);
            if(is_null($extended_attribute) ){
                return ['result'=>1, 'msg'=>"attribute not found"];
            }else{
                if($extended_attribute->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't edit this attribute"]; }
                $extended_attribute->attributeName       = trim($request->input('attributeName'              ));
                $extended_attribute->ownership              = trim($request->input('ownership'                      ));
                $extended_attribute->ownerId                = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
                $extended_attribute->displayName              = trim($request->input('displayName'                    ));
                $extended_attribute->memo                   = trim($request->input('memo'                           ));
                $extended_attribute->lastUserId             = trim($request->input('userID'                         ));
                $extended_attribute->dateUpdated            = date("Y-m-d H:i:s"                                  );

                $extended_attribute->extendedSubTypeId                 = trim($request->input('extendedSubTypeId'));
                $extended_attribute->attributeTypeId                 = trim($request->input('attributeTypeId'));
                $extended_attribute->defaultValue                 = trim($request->input('defaultValue'));
                $extended_attribute->notNullFlag                 = trim($request->input('notNullFlag'));
                $extended_attribute->reserved                 = trim($request->input('reserved'));

                if($extended_attribute->attributeName     ==''){ return ['result'=>1, 'msg'=>'attribute name is empty'];             }
                if($extended_attribute->ownership            ==''){ return ['result'=>1, 'msg'=>'attribute ownership is empty'];        }
                if($extended_attribute->ownerId              ==''){ return ['result'=>1, 'msg'=>'attribute owner ID is empty'];         }
                if($extended_attribute->lastUserId           ==''){ return ['result'=>1, 'msg'=>'attribute last user id is empty'];     }

                if($extended_attribute->extendedSubTypeId            ==''){ return ['result'=>1, 'msg'=>'attribute extendedSubTypeId is empty'];        }
                if($extended_attribute->attributeTypeId              ==''){ return ['result'=>1, 'msg'=>'attribute attributeTypeId is empty'];         }
                if($extended_attribute->notNullFlag               ==''){ return ['result'=>1, 'msg'=>'attribute  notNullFlag is empty']; }



                $tmp = $extended_attribute->save();
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //增---------------------------------------
    public function insertRow($orgID, Request $request){
        try{
            $attributeName = $request->input('attributeName');
            $tmp      = Extended_attribute::where('attributeName','=',strtolower($attributeName) )->first();
            if(!is_null($tmp) ){
                return ['result'=>1, 'msg'=>'attribute already exists'];
            }
            $extended_attribute = new Extended_attribute;
            $extended_attribute->attributeName    = trim($request->input('attributeName'              ));
            $extended_attribute->ownership           = trim($request->input('ownership'                      ));
            $extended_attribute->ownerId             = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
            $extended_attribute->dateCreated         = date("Y-m-d H:i:s");
            $extended_attribute->dateUpdated         = date("Y-m-d H:i:s");
            $extended_attribute->lastUserId          = trim($request->input('userID'                         ));
            $extended_attribute->displayName           = trim($request->input('displayName'                    ));
            $extended_attribute->memo                = trim($request->input('memo'                           ));
            $extended_attribute->extendedSubTypeId   = trim($request->input('extendedSubTypeId'));
            $extended_attribute->attributeTypeId     = trim($request->input('attributeTypeId'));
            $extended_attribute->defaultValue        = trim($request->input('defaultValue'));
            $extended_attribute->notNullFlag         = trim($request->input('notNullFlag'));
            $extended_attribute->reserved                 = trim($request->input('reserved'));

            if($extended_attribute->attributeName     ==''){ return ['result'=>1, 'msg'=>'attribute name is empty'];         }
            if($extended_attribute->ownership            ==''){ return ['result'=>1, 'msg'=>'attribute ownership is empty'];    }
            if($extended_attribute->ownerId              ==''){ return ['result'=>1, 'msg'=>'attribute owner ID is empty'];     }
            if($extended_attribute->lastUserId           ==''){ return ['result'=>1, 'msg'=>'attribute last user id is empty']; }
            if($extended_attribute->extendedSubTypeId            ==''){ return ['result'=>1, 'msg'=>'attribute extendedSubTypeId is empty'];        }
            if($extended_attribute->attributeTypeId              ==''){ return ['result'=>1, 'msg'=>'attribute attributeTypeId is empty'];         }
            if($extended_attribute->notNullFlag               ==''){ return ['result'=>1, 'msg'=>'attribute  notNullFlag is empty']; }


            $tmp = $extended_attribute->save();
            if($tmp){ return ['result'=>0, 'attributeId'=>$extended_attribute->attributeId]; }
            else{ return ['result'=>1, 'msg'=>'']; }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //删---------------------------------------
    public function deleteRow($orgID, $id){
        try{
            $extended_attribute = Extended_attribute::find($id);
            if(is_null($extended_attribute) ){
                return ['result'=>1, 'msg'=>"attribute not found"];
            }else{
                if($extended_attribute->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this attribute"]; }
                $tmp = $extended_attribute->delete($id);
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //---------------------------------------


    //---------------------------------------*/

}