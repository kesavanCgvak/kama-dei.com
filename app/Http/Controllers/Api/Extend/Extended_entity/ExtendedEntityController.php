<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/8/31
 * Time: 下午7:33
 */

namespace App\Http\Controllers\Api\Extend\Extended_entity;

use Illuminate\Http\Request;
use App\Models\Extend\Extended_entity;
use App\Models\Extend\Extended_EAV;
use App\Controllers;
class ExtendedEntityController extends \App\Http\Controllers\Controller{

    //显示---------------------------------------
    public function show($extendedSubTypeId, $orgID, $id){
        $data = Extended_entity::findExtendedEntityNameByID($id);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[]      ];
        }else{
            return ['result'=>0, 'msg'=>''                , 'data'=>$data   ];
        }
    }

    //搜索---------------------------------------
    public function search($extendedSubTypeId, $orgID, $field, $value){
        //-----------------------------------------------------------------------------------------
        $field = $this->dataFields( $field );
        if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $data = Extended_entity::findExtendedEntityName($extendedSubTypeId, $orgID, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0  ];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data    ];
        }
    }

    //查找$orgID的显示所有+排序---------------------------------------
    public function showAll($extendedSubTypeId, $orgID ){
		return $this->showAllSorted($extendedSubTypeId, $orgID, 'extendedEntityId', 'asc');
	}
    public function showAllSorted($extendedSubTypeId, $orgID, $sort, $order){
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


        $data  = Extended_entity::myExtendedEntitys($extendedSubTypeId, $orgID, '', '')->orderBy($sort, $order);
//        if($sort!='orderid'){
//            $data->orderBy('orderid','DESC');
//        }
        $data=$data->get();
        $total = Extended_entity::myExtendedEntitys($extendedSubTypeId, $orgID, '', '')->count();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$total, 'data'=>$data];
        }
    }

    //分页查询---------------------------------------
    public function showPage($extendedSubTypeId, $orgID, $perPage, $page){
        $data  = Extended_entity::myPageing($extendedSubTypeId, $orgID, $perPage, $page, 'extendedEntityId', 'asc');
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
        }
    }

    //分页查询+排序---------------------------------------
    public function showPageSorted($extendedSubTypeId, $orgID, $sort, $order, $perPage, $page ,$showGlobal=1){
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
        $count = Extended_entity::myExtendedEntitys($extendedSubTypeId, $orgID, '', '', $showGlobal)->count();
        $data  = Extended_entity::myPageing($extendedSubTypeId, $orgID, $perPage, $page, $sort, $order, $showGlobal);
		
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
			foreach($data as $tmp){
				$tmp->review_by_p = 1;
				if($tmp->review_by==null || strtotime($tmp->review_by." 23:59:59")>=time())
					{$tmp->review_by_p = 0;}
			}
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //分页查询+排序+搜索---------------------------------------
    public function showPageSortSearch($extendedSubTypeId, $orgID, $sort, $order, $perPage, $page, $field, $value ,$showGlobal=1){
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
        $count = Extended_entity::myExtendedEntitys($extendedSubTypeId, $orgID, $field, $value, $showGlobal)->count();
        $data  = Extended_entity::myPageingWithSearch($extendedSubTypeId,$orgID,$perPage,$page,$sort,$order,$field,$value,$showGlobal);
		
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //排序字段转换---------------------------------------
    private function sortFields($sort){
        switch(strtolower($sort)){

            case 'extendedentityid'              : { $sort="extendedEntityId";    break; }
            case 'extendedentityname'            : { $sort="extendedEntityName";  break; }
            case 'ownership'                : { $sort="ownership";          break; }
            case 'ownerid'                  : { $sort="ownerId";            break; }
            case 'datecreated'              : { $sort="dateCreated";        break; }
            case 'dateupdated'              : { $sort="dateUpdated";        break; }
            case 'lastuserid'               : { $sort="lastUserId";         break; }
            case 'memo'                     : { $sort="memo";               break; }
            case 'ownershipcaption'         : { $sort="ownership";          break; }
            case 'organizationshortname'    : { $sort="ownerId";            break; }

            case 'extendedsubtypeid'        : { $sort="extendedSubTypeId";  break; }
            case 'extendedsubtypename'      : { $sort="extnddSbTypNm";break; }
            case 'attributetype'            : { $sort="attributeType";break; }
            case 'reserved'                 : { $sort="reserved";  break; }
            //case 'orderid'                 : { $sort="orderid";  break; }
            case 'review_by'                 : { $sort="review_by";  break; }

            default                         : { $sort = "";                        }
        }
        return $sort;
    }

    //数据字段转换---------------------------------------
    private function dataFields($field){
        switch(strtolower($field)){

            case 'extendedentityid'           : { $field="extendedEntityId";   break; }
            case 'extendedentityname'         : { $field="extendedEntityName"; break; }
            case 'ownership'                : { $field="ownership";         break; }
            case 'ownerid'                  : { $field="ownerId";           break; }
            case 'datecreated'              : { $field="dateCreated";       break; }
            case 'dateupdated'              : { $field="dateUpdated";       break; }
            case 'lastuserid'               : { $field="lastUserId";        break; }
            case 'memo'                     : { $field="memo";              break; }

            case 'extendedsubtypeid'        : { $field="extendedSubTypeId";  break; }
            case 'extendedsubtypename'      : { $field="extendedSubTypeName";break; }
            case 'reserved'                 : { $field="reserved";  break; }
            //case 'orderid'                 : { $field="orderid";  break; }
            default                         : { $field = "";                       }
        }
        return $field;
    }

    //改---------------------------------------
    public function editRow($orgID, $id, Request $request){
        try{
            $extended_entity = Extended_entity::find($id);
            if(is_null($extended_entity) ){
                return ['result'=>1, 'msg'=>"extendedEntity not found"];
            }else{
                if($extended_entity->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't edit this extendedEntity"]; }
                $extended_entity->extendedEntityName       = trim($request->input('extendedEntityName'              ));
                $extended_entity->ownership              = trim($request->input('ownership'                      ));
                $extended_entity->ownerId                = (($orgID==0)? trim($request->input('ownerId')) :$orgID);

                $extended_entity->memo                   = trim($request->input('memo'                           ));
                $extended_entity->lastUserId             = trim($request->input('userID'                         ));
                $extended_entity->dateUpdated            = date("Y-m-d H:i:s"                                  );

                $extended_entity->extendedSubTypeId                 = trim($request->input('extendedSubTypeId'));
                $extended_entity->reserved                 = trim($request->input('reserved'));
                //$extended_entity->orderid                 = trim($request->input('orderid'));

				if($extended_entity->extendedEntityName ==''){ return ['result'=>1, 'msg'=>'extendedEntity name is empty'];              }
                if($extended_entity->ownership          ==''){ return ['result'=>1, 'msg'=>'extendedEntity ownership is empty'];         }
                if($extended_entity->ownerId            ==''){ return ['result'=>1, 'msg'=>'extendedEntity owner ID is empty'];          }
                if($extended_entity->lastUserId         ==''){ return ['result'=>1, 'msg'=>'extendedEntity last user id is empty'];      }
                if($extended_entity->extendedSubTypeId  ==''){ return ['result'=>1, 'msg'=>'extendedEntity extendedSubTypeId is empty']; }




                $tmp = $extended_entity->save();
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //增---------------------------------------
    public function insertRow($orgID, Request $request){
        try{
            $extendedEntityName = $request->input('extendedEntityName');
            $ownership = $request->input('ownership');
            $ownerId = $request->input('ownerId');
            $extendedSubTypeId = $request->input('extendedSubTypeId');
            $tmp      = Extended_entity::where('extendedEntityName','=',strtolower($extendedEntityName) )
                ->where('ownership', '=', $ownership)
                ->where('ownerId', '=', $ownerId)
                ->where('extendedSubTypeId', '=', $extendedSubTypeId)
                ->first();
            if(!is_null($tmp) ){
                return ['result'=>1, 'msg'=>'extendedEntity already exists'];
            }
            $extended_entity = new Extended_entity;
            $extended_entity->extendedEntityName    = trim($request->input('extendedEntityName'              ));
            $extended_entity->ownership           = trim($request->input('ownership'                      ));
            $extended_entity->ownerId             = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
            $extended_entity->dateCreated         = date("Y-m-d H:i:s");
            $extended_entity->dateUpdated         = date("Y-m-d H:i:s");
            $extended_entity->lastUserId          = trim($request->input('userID'                         ));

            $extended_entity->memo                = trim($request->input('memo'                           ));
            $extended_entity->extendedSubTypeId   = trim($request->input('extendedSubTypeId'));
            $extended_entity->reserved                 = trim($request->input('reserved'));


            if($extended_entity->extendedEntityName     ==''){ return ['result'=>1, 'msg'=>'extendedEntity name is empty'];         }
            if($extended_entity->ownership            ==''){ return ['result'=>1, 'msg'=>'extendedEntity ownership is empty'];    }
            if($extended_entity->ownerId              ==''){ return ['result'=>1, 'msg'=>'extendedEntity owner ID is empty'];     }
            if($extended_entity->lastUserId           ==''){ return ['result'=>1, 'msg'=>'extendedEntity last user id is empty']; }
            if($extended_entity->extendedSubTypeId            ==''){ return ['result'=>1, 'msg'=>'extendedEntity extendedSubTypeId is empty'];        }


            $tmp = $extended_entity->save();
//            if($tmp){
//                $extended_entity->orderid=$extended_entity->extendedEntityId;
//                $extended_entity->save();
//                return ['result'=>0, 'extendedEntityId'=>$extended_entity->extendedEntityId];
//            }
//            else{ return ['result'=>1, 'msg'=>'']; }


            if($tmp){ return ['result'=>0, 'extendedEntityId'=>$extended_entity->extendedEntityId]; }
            else{ return ['result'=>1, 'msg'=>'']; }

        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //删---------------------------------------
    public function deleteRow($orgID, $id){
        try{
			if( \App\Models\Extend\Extended_link::where('entityId', $id)->count()>0 ){
				return ['result'=>1, 'msg'=>'This Extended Entity has been used in a Knowledge Record and can not be deleted.'];
			}
/*
            $data_Extended_EAV = new Extended_EAV;
            $data = $data_Extended_EAV->findbyextendedEntityId($id);
            foreach( $data as $key=>$tmp ){
                $tmp->delete($tmp->extendedEAVID);
            }
*/
            $extended_entity = Extended_entity::find($id);
            if(is_null($extended_entity) ){
                return ['result'=>1, 'msg'=>"extendedEntity not found"];
            }else{
                if($extended_entity->ownerId!=$orgID && $orgID!=0)
					{ return ['result'=>1, 'msg'=>"You can't delete this extendedEntity"]; }
				
				Extended_EAV::where('extendedEntityId', $id)->delete();
				Extended_entity::where('extendedEntityId', $id)->delete();
                return ['result'=>0, 'msg'=>'OK'];
/*
                $tmp = $extended_entity->delete($id);
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
*/
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }
	
	public function otherLang($extendedEntityId){
        try{
			return [
				'result'=>0,
				'msg'=>'OK',
				'count'=>Extended_EAV::where('extendedEntityId',$extendedEntityId)->where('lang','<>','en')->count()
			];
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
	}
    //---------------------------------------
    /*public function upolder(Request $request){
        //$request.content;
        $temp_json=$request->input('jsonstr');
        foreach( $temp_json as $key=>$tmp ){
            $t=$tmp['extendedEntityId'];
            $extended_entity = Extended_entity::find($tmp['extendedEntityId']);
            if(is_null($extended_entity) ){
            }else{
                $extended_entity->orderid= $tmp['orderid'];
                $extended_entity->save();
            }
        }
        return ['result'=>0, 'msg'=>'ok'];
    }*/
    //---------------------------------------*/

}