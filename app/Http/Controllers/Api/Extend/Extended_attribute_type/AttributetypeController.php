<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/19
 * Time: 下午11:54
 */

namespace App\Http\Controllers\Api\Extend\Extended_attribute_type;

use Illuminate\Http\Request;
use App\Models\Extend\Extended_attribute_type;
use App\Controllers;
class AttributetypeController extends \App\Http\Controllers\Controller{
    /*public function showAll($orgID){
        echo $orgID;
    }*/

    //显示---------------------------------------
    public function show($orgID, $id){
        $data = Extended_attribute_type::findAttributeTypeByID($orgID, $id);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[]      ];
        }else{
            return ['result'=>0, 'msg'=>''                , 'data'=>$data   ];
        }
    }

    //搜索---------------------------------------
    public function search($orgID, $field, $value){
        //-----------------------------------------------------------------------------------------
        $field = $this->dataFields( $field );
        if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $data = Extended_attribute_type::findAttributeType($orgID, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0  ];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data    ];
        }
    }

    //查找$orgID的显示所有+排序---------------------------------------
    public function showAll( $orgID ){ return $this->showAllSorted($orgID, 'attributeTypeId', 'asc'); }
    public function showAllSorted($orgID, $sort, $order){
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
        $data  = Extended_attribute_type::myAttributeTypes($orgID, '', '')->orderBy($sort, $order)->get();
        $total = Extended_attribute_type::myAttributeTypes($orgID, '', '')->count();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$total, 'data'=>$data];
        }
    }

    //分页查询---------------------------------------
    public function showPage( $orgID, $perPage, $page){
        $data  = Extended_attribute_type::myPageing($orgID, $perPage, $page, 'attributeTypeId', 'asc');
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
        }
    }

    //分页查询+排序---------------------------------------
    public function showPageSorted( $orgID, $sort, $order, $perPage, $page ){
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
        $count = Extended_attribute_type::myAttributeTypes($orgID, '', '')->count();

        $data  = Extended_attribute_type::myPageing($orgID, $perPage, $page, $sort, $order);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //分页查询+排序+搜索---------------------------------------
    public function showPageSortSearch( $orgID, $sort, $order, $perPage, $page, $field, $value ){
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
        $count = Extended_attribute_type::myAttributeTypes($orgID, $field, $value)->count();

        $data  = Extended_attribute_type::myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //排序字段转换---------------------------------------
    private function sortFields($sort){
        switch(strtolower($sort)){
            case 'attributetypeid'          : { $sort="attributeTypeId";    break; }
            case 'attributetypename'        : { $sort="attributeTypeName";  break; }
            case 'storagetype'              : { $sort="storageType";        break; }
            case 'ownership'                : { $sort="ownership";          break; }
            case 'ownerid'                  : { $sort="ownerId";            break; }
            case 'datecreated'              : { $sort="dateCreated";        break; }
            case 'dateupdated'              : { $sort="dateUpdated";        break; }
            case 'lastuserid'               : { $sort="lastUserId";         break; }
            case 'memo'                     : { $sort="memo";               break; }
            case 'ownershipcaption'         : { $sort="ownership";          break; }
            case 'organizationshortname'    : { $sort="ownerId";            break; }
            case 'reserved'                 : { $sort="reserved";  break; }
            default                         : { $sort = "";                        }
        }
        return $sort;
    }

    //数据字段转换---------------------------------------
    private function dataFields($field){
        switch(strtolower($field)){
            case 'attributetypeid'          : { $field="attributeTypeId";   break; }
            case 'attributetypename'        : { $field="attributeTypeName"; break; }
            case 'storagetype'              : { $field="storageType";       break; }
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
            $attribute_type = Extended_attribute_type::find($id);
            if(is_null($attribute_type) ){
                return ['result'=>1, 'msg'=>"attribute_type not found"];
            }else{
                if($attribute_type->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't edit this attribute_type"]; }
                $attribute_type->attributeTypeName      = trim($request->input('attributeTypeName'              ));
                $attribute_type->ownership              = trim($request->input('ownership'                      ));
                $attribute_type->ownerId                = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
				$attribute_type->storageType            = trim($request->input('storageType'                    ));
				$attribute_type->memo                   = trim($request->input('memo'                           ));
                $attribute_type->lastUserId             = trim($request->input('userID'                         ));
                $attribute_type->dateUpdated            = date("Y-m-d H:i:s"                                  );
                $attribute_type->reserved               = trim($request->input('reserved'                           ));


                if($attribute_type->attributeTypeName   ==''){ return ['result'=>1, 'msg'=>'attribute_type name is empty'];             }
                if($attribute_type->ownership           ==''){ return ['result'=>1, 'msg'=>'attribute_type ownership is empty'];        }
                if($attribute_type->ownerId             ==''){ return ['result'=>1, 'msg'=>'attribute_type owner ID is empty'];         }
                if($attribute_type->lastUserId          ==''){ return ['result'=>1, 'msg'=>'attribute_type last user id is empty'];     }
                if($attribute_type->storageType         ==''){ return ['result'=>1, 'msg'=>'attribute_type last storageType is empty']; }



                $tmp = $attribute_type->save();
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //增---------------------------------------
    public function insertRow($orgID, Request $request){
        try{
            $attributeTypeName = $request->input('attributeTypeName');
            $tmp      = Extended_attribute_type::where('attributeTypeName','=',strtolower($attributeTypeName) )->first();
            if(!is_null($tmp) ){
                return ['result'=>1, 'msg'=>'attribute_type already exists'];
            }
            $attribute_type = new Extended_attribute_type;
            $attribute_type->attributeTypeName  = trim($request->input('attributeTypeName'              ));
            $attribute_type->ownership          = trim($request->input('ownership'                      ));
            $attribute_type->ownerId            = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
            $attribute_type->dateCreated        = date("Y-m-d H:i:s");
			$attribute_type->dateUpdated        = date("Y-m-d H:i:s");
            $attribute_type->lastUserId         = trim($request->input('userID'                         ));
            $attribute_type->storageType        = trim($request->input('storageType'                    ));
            $attribute_type->memo               = trim($request->input('memo'                           ));
            $attribute_type->reserved           = trim($request->input('reserved'                           ));

            if($attribute_type->attributeTypeName   ==''){ return ['result'=>1, 'msg'=>'attribute_type name is empty'];         }
            if($attribute_type->ownership           ==''){ return ['result'=>1, 'msg'=>'attribute_type ownership is empty'];    }
            if($attribute_type->ownerId             ==''){ return ['result'=>1, 'msg'=>'attribute_type owner ID is empty'];     }
            if($attribute_type->lastUserId          ==''){ return ['result'=>1, 'msg'=>'attribute_type last user id is empty']; }
            if($attribute_type->storageType         ==''){ return ['result'=>1, 'msg'=>'attribute_type storageType is empty'];  }

            //$attribute_type->ownerId = (($attribute_type->ownerId==0) ?null :$attribute_type->ownerId);

            $tmp = $attribute_type->save();
            if($tmp){ return ['result'=>0, 'attributeTypeId'=>$attribute_type->attributeTypeId]; }
            else{ return ['result'=>1, 'msg'=>'']; }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //删---------------------------------------
    public function deleteRow($orgID, $id){
        try{
            $attribute_type = Extended_attribute_type::find($id);
            if(is_null($attribute_type) ){
                return ['result'=>1, 'msg'=>"attribute_type not found"];
            }else{
                if($attribute_type->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this attribute_type"]; }
                $tmp = $attribute_type->delete($id);
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //---------------------------------------


    //---------------------------------------*/

}


