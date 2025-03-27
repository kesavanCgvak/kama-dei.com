<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/11/8
 * Time: 11:35 AM
 */

namespace App\Http\Controllers\Api\Extend\Extended_link;

use Illuminate\Http\Request;
use App\Models\Extend\Extended_link;
use App\Controllers;
class ExtendedLinkController extends  \App\Http\Controllers\Controller
{
    //显示---------------------------------------
    public function show($entityId,$parentTable,$parentId,$orgID, $id){
        $data = Extended_link::findExtendedLinkByID($entityId,$parentTable,$parentId,$orgID, $id);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[]      ];
        }else{
            return ['result'=>0, 'msg'=>''                , 'data'=>$data   ];
        }
    }
    //查找$orgID的显示所有+排序---------------------------------------
    public function showAll($entityId,$parentTable,$parentId,$orgID){
        return $this->showAllSorted($entityId,$parentTable,$parentId,$orgID, 'extendedLinkId', 'asc');
    }
    public function showAllSorted($entityId,$parentTable,$parentId,$orgID, $sort, $order){
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
        $data  = Extended_link::myExtendedLinks($entityId,$parentTable,$parentId,$orgID)->orderBy($sort, $order);
        if($sort!='orderid'){
            $data->orderBy('orderid','DESC');
        }
        $data=$data->get();
        $total = Extended_link::myExtendedLinks($entityId,$parentTable,$parentId,$orgID)->count();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$total, 'data'=>$data];
        }
    }
    //分页查询---------------------------------------
    public function showPage($entityId,$parentTable,$parentId,$orgID, $perPage, $page){
        $data  = Extended_link::myPageing($entityId,$parentTable,$parentId,$orgID, $perPage, $page, 'orderid', 'DESC','');
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
        }
    }
    public function showPageSorted2($entityId,$parentTable,$parentId,$orgID, $sort, $order, $perPage, $page, $showglobal=1){
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
        $count = Extended_link::myExtendedLinks($entityId,$parentTable,$parentId,$orgID,'', $showglobal)->count();
//dd($count);
//dd($orgID);
//dd($showGlobal);

        $data  = Extended_link::myPageing($entityId,$parentTable,$parentId,$orgID, $perPage, $page, $sort, $order,'', $showglobal);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //分页查询+排序---------------------------------------
    public function showPageSorted($entityId,$parentTable,$parentId,$orgID, $sort, $order, $perPage, $page, $searc, $showglobal=1){
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
        $count = Extended_link::myExtendedLinks($entityId,$parentTable,$parentId,$orgID,$searc, $showglobal)->count();

        $data  = Extended_link::myPageing($entityId,$parentTable,$parentId,$orgID, $perPage, $page, $sort, $order,$searc, $showglobal);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }
    //排序字段转换---------------------------------------
    private function sortFields($sort){
        switch(strtolower($sort)){
            case 'parentname'            : { $sort="parentId";    break; }
            case 'parenttablename'       : { $sort="parentTable"; break; }
            case 'entityname'            : { $sort="entityId";    break; }
            case 'organizationshortname' : { $sort="ownerId";     break; }
            case 'organizationshortname' : { $sort="ownerId";     break; }

            case 'extendedlinkid'           : { $sort="extendedLinkId";           break; }
            case 'entityid'                 : { $sort="entityId";                 break; }
            case 'parenttable'              : { $sort="parentTable";              break; }
            case 'parentid'                 : { $sort="parentId";                 break; }
            case 'samplechatdisplay'        : { $sort="sampleChatDisplay";        break; }
            case 'includedextdataname'      : { $sort="includedExtDataName";      break; }
            case 'includedextdatachatintro' : { $sort="includedExtDataChatIntro"; break; }

            case 'extendedentityid'      : { $sort="extendedEntityId";   break; }
            case 'extendedentityname'    : { $sort="extendedEntityName"; break; }
            case 'ownership'             : { $sort="ownership";          break; }
            case 'ownerid'               : { $sort="ownerId";            break; }
            case 'datecreated'           : { $sort="dateCreated";        break; }
            case 'dateupdated'           : { $sort="dateUpdated";        break; }
            case 'lastuserid'            : { $sort="lastUserId";         break; }
            case 'memo'                  : { $sort="memo";               break; }
            case 'ownershipcaption'      : { $sort="ownership";          break; }
            case 'organizationshortname' : { $sort="ownerId";            break; }
            case 'chatintro'             : { $sort="chatIntro";          break; }
            case 'voiceintro'            : { $sort="voiceIntro";         break; }

            case 'extendedsubtypeid'     : { $sort="extendedSubTypeId";   break; }
            case 'extendedsubtypename'   : { $sort="extendedSubTypeName"; break; }
            case 'reserved'              : { $sort="reserved";            break; }
            case 'orderid'               : { $sort="orderid";             break; }
            default                      : { $sort = "";                         }
        }
        return $sort;
    }
    //数据字段转换---------------------------------------
    private function dataFields($field){
        switch(strtolower($field)){
            case 'extendedlinkid'           : { $field="extendedLinkId";           break; }
            case 'entityid'                 : { $field="entityId";                 break; }
            case 'parenttable'              : { $field="parentTable";              break; }
            case 'parentid'                 : { $field="parentId";                 break; }
            case 'samplechatdisplay'        : { $field="sampleChatDisplay";        break; }
            case 'includedextdataname'      : { $field="includedExtDataName";      break; }
            case 'includedextdatachatintro' : { $field="includedExtDataChatIntro"; break; }

            case 'extendedentityid'    : { $field="extendedEntityId";    break; }
            case 'extendedentityname'  : { $field="extendedEntityName";  break; }
            case 'ownership'           : { $field="ownership";           break; }
            case 'ownerid'             : { $field="ownerId";             break; }
            case 'datecreated'         : { $field="dateCreated";         break; }
            case 'dateupdated'         : { $field="dateUpdated";         break; }
            case 'lastuserid'          : { $field="lastUserId";          break; }
            case 'memo'                : { $field="memo";                break; }
            case 'chatintro'           : { $field="chatIntro";           break; }
            case 'voiceintro'          : { $field="voiceIntro";          break; }
            case 'extendedsubtypeid'   : { $field="extendedSubTypeId";   break; }
            case 'extendedsubtypename' : { $field="extendedSubTypeName"; break; }
            case 'reserved'            : { $field="reserved";            break; }
            case 'orderid'             : { $field="orderid";             break; }
            default                    : { $field = "";                         }
        }
        return $field;
    }
    //改---------------------------------------
    public function editRow($orgID, $id, Request $request){
        try{
            //---------------------------------
            $validator = \Validator::make(
                    $request->all(),
                    [
                        'parentId' => 'required|gte:1',
                    ],
                    [
                        "parentId.required"=> "parent is required",
                        "parentId.gte"=> "parent is required"
                    ]
            );
            if($validator->fails()){
                $errors = $validator->errors();
                throw new \Exception($errors->first());
            }
            //---------------------------------
            $extended_link = Extended_link::find($id);
            if(is_null($extended_link) ){
                return ['result'=>1, 'msg'=>"Extended_link not found"];
            }else{
                if($extended_link->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't edit this Extended_link"]; }
                $extended_link->ownership                 = trim($request->input('ownership'));
                $extended_link->ownerId                   = (($orgID==0)? trim($request->input('ownerId')) :$orgID);

                $extended_link->memo                      = trim($request->input('memo'));
                $extended_link->lastUserId                = trim($request->input('userID'));
//              $extended_link->chatIntro                 = trim($request->input('chatIntro'));
//              $extended_link->voiceIntro                = trim($request->input('voiceIntro'));
//              $extended_link->dateUpdated               = date("Y-m-d H:i:s");
                $extended_link->updated_at                = date("Y-m-d H:i:s");
                $extended_link->entityId                  = trim($request->input('entityId'));
                $extended_link->reserved                  = trim($request->input('reserved'))?1:0;
                $extended_link->parentTable               = trim($request->input('parentTable'));
                $extended_link->parentId                  = trim($request->input('parentId'));
                $extended_link->sampleChatDisplay         = trim($request->input('sampleChatDisplay'));
                $extended_link->includedExtDataName       = trim($request->input('includedExtDataName'))?1:0;
                $extended_link->includedExtDataChatIntro  = trim($request->input('includedExtDataChatIntro'))?1:0;

                $extended_link->orderid                 = trim($request->input('orderid'));

                if($extended_link->entityId  ==''){ return ['result'=>1, 'msg'=>'Extended_link entityId is empty' ]; }
                if($extended_link->ownership ==''){ return ['result'=>1, 'msg'=>'Extended_link ownership is empty']; }
                if($extended_link->ownerId   ==''){ return ['result'=>1, 'msg'=>'Extended_link owner ID is empty' ]; }
                if($extended_link->lastUserId==''){ return ['result'=>1, 'msg'=>'Extended_link last user id is empty']; }

                if($extended_link->parentTable==''){ return ['result'=>1, 'msg'=>'Extended_link parentTable is empty']; }
                if($extended_link->parentId   ==''){ return ['result'=>1, 'msg'=>'Extended_link parentId is empty']; }

                //if($extended_link->voiceIntro==''){ $extended_link->voiceIntro=null; }

                $extended_link->save();
                
                $langCode   = trim($request->input('languageCode'));
                $chatIntro  = trim($request->input('chatIntro' ));
                $voiceIntro = trim($request->input('voiceIntro'));
                
                if($voiceIntro==''){ $voiceIntro=null; }
                
                $Extended_link_translation = \App\Models\Extend\Extended_link_translation::where('lang',$langCode)
                    ->where('extendedLinkId',$extended_link->extendedLinkId)
                    ->first();
                if($Extended_link_translation!=null){
                    $Extended_link_translation->chatIntro  = $chatIntro;
                    $Extended_link_translation->voiceIntro = $voiceIntro;
                    $Extended_link_translation->save();
                }else{
                    if($chatIntro!=''){
                        \App\Models\Extend\Extended_link_translation::insert([
                            'extendedLinkId' => $extended_link->extendedLinkId,
                            'lang'           => $langCode,
                            'chatIntro'      => $chatIntro,
                            'voiceIntro'     => $voiceIntro
                        ]);
                    }
                }
                return ['result'=>0, 'msg'=>''];
            }
        }catch(\Throwable $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //增---------------------------------------
    public function insertRow($orgID, Request $request){
       /* 'extendedLinkId',
        'entityId',
        'parentTable',
        'parentId',//最后编辑者
        'sampleChatDisplay',
        'ownerId',//system_organization 归属
        'ownership',//是否必填
        'includedExtDataName',      //复选
        'includedExtDataChatIntro',//复选
        'dateCreated', //创建时间
        'dateUpdated', //更新时间
        'reserved',//reserved
        'lastUserId',
        'memo'//其他描述*/
        try{
            //---------------------------------
            $validator = \Validator::make(
                    $request->all(),
                    [
                        'parentId' => 'required|gte:1',
                    ],
                    [
                        "parentId.required"=> "parent is required",
                        "parentId.gte"=> "parent is required"
                    ]
            );
            if($validator->fails()){
                $errors = $validator->errors();
                throw new \Exception($errors->first());
            }
            //---------------------------------
            $extended_link = new Extended_link;
            $extended_link->ownership = trim($request->input('ownership'));
            $extended_link->ownerId   = (($orgID==0)? trim($request->input('ownerId')) :$orgID);

            $extended_link->memo                     = trim($request->input('memo'));
            $extended_link->lastUserId               = trim($request->input('userID'));
//          $extended_link->dateUpdated              = date("Y-m-d H:i:s");
//          $extended_link->dateCreated              = date("Y-m-d H:i:s");
            $extended_link->created_at               = date("Y-m-d H:i:s");
//          $extended_link->chatIntro                = trim($request->input('chatIntro'));
//          $extended_link->voiceIntro               = trim($request->input('voiceIntro'));
            $extended_link->entityId                 = trim($request->input('entityId'));
            $extended_link->reserved                 = trim($request->input('reserved'))?1:0;
            $extended_link->parentTable              = trim($request->input('parentTable'));
            $extended_link->parentId                 = trim($request->input('parentId'));
            $extended_link->sampleChatDisplay        = trim($request->input('sampleChatDisplay'));
            $extended_link->includedExtDataName      = trim($request->input('includedExtDataName'))?1:0;
            $extended_link->includedExtDataChatIntro = trim($request->input('includedExtDataChatIntro'))?1:0;

            if($extended_link->entityId   ==''){ return ['result'=>1, 'msg'=>'Extended_link entityId is empty' ]; }
            if($extended_link->ownership  ==''){ return ['result'=>1, 'msg'=>'Extended_link ownership is empty']; }
            if($extended_link->ownerId    ==''){ return ['result'=>1, 'msg'=>'Extended_link owner ID is empty' ]; }
            if($extended_link->lastUserId ==''){ return ['result'=>1, 'msg'=>'Extended_link last user id is empty']; }
            if($extended_link->parentTable==''){ return ['result'=>1, 'msg'=>'Extended_link parentTable is empty' ]; }
            if($extended_link->parentId   ==''){ return ['result'=>1, 'msg'=>'Extended_link parentId is empty']; }
//          if($extended_link->voiceIntro==''){ $extended_link->voiceIntro=null; }
            $extended_link->save();

            $extended_link->orderid=$extended_link->extendedLinkId;
            $extended_link->save();

            $lang       = trim($request->input('languageCode'));
            $chatIntro  = trim($request->input('chatIntro' ));
            $voiceIntro = trim($request->input('voiceIntro'));

            if($voiceIntro==''){ $voiceIntro=null; }
            
            if($chatIntro!=''){
                \App\Models\Extend\Extended_link_translation::insert([
                    'extendedLinkId' => $extended_link->extendedLinkId,
                    'lang'           => $lang,
                    'chatIntro'      => $chatIntro,
                    'voiceIntro'     => $voiceIntro
                ]);
            }

            return ['result'=>0, 'extendedLinkId'=>$extended_link->extendedLinkId];

            /*if($tmp){ return ['result'=>0, 'extendedLinkId'=>$extended_link->extendedLinkId]; }
            else{ return ['result'=>1, 'msg'=>'']; }*/
        }catch(\Throwable $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //删---------------------------------------
    public function deleteRow($orgID, $id){

        try{
            $extended_link = Extended_link::find($id);
            if(is_null($extended_link) ){
                return ['result'=>1, 'msg'=>"extendedEntity not found"];
            }else{
                if($extended_link->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this Extended_link"]; }
                $tmp = $extended_link->delete($id);
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //---------------------------------------
    public function upolder(Request $request){
        //$request.content;
        $temp_json=$request->input('jsonstr');
        foreach( $temp_json as $key=>$tmp ){
            //$t=$tmp['extendedLinkId'];
            $extended_link = Extended_link::find($tmp['extendedLinkId']);
            if(is_null($extended_link) ){
            }else{
                $extended_link->orderid= $tmp['orderid'];
                $extended_link->save();
            }
        }
        return ['result'=>0, 'msg'=>'ok'];
    }
    //---------------------------------------
    public function getTranslation($itemId, $orgId, $langCode){
        try{
            $data    = \App\Models\Extend\Extended_link_translation::where('extendedLinkId',$itemId)->where('lang',$langCode)->first();
            $data_en = \App\Models\Extend\Extended_link_translation::where('extendedLinkId',$itemId)->where('lang','en'     )->first();

            return ['result'=>0, 'data'=>$data, 'data_en'=>$data_en];
        }catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
        
    }
    //---------------------------------------
}
