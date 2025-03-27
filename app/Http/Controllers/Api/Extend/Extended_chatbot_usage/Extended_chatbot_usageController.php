<?php

namespace App\Http\Controllers\Api\Extend\Extended_chatbot_usage;

use Illuminate\Http\Request;
use App\Models\Extend\Extended_chatbot_usage;
use App\Models\Extend\Extended_chatbot_log;
use App\Controllers;
class Extended_chatbot_usageController extends \App\Http\Controllers\Controller{

//
    public function getchatlog($chat_id){
        //$data = Extended_chatbot_log::findExtendedChat_id($chat_id);
        $tmpExtended_chatbot_log = new Extended_chatbot_log;
        $data = $tmpExtended_chatbot_log
        ->where('chat_id', '=', $chat_id)->orderBy('msg_id', 'asc')->get();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[]      ];
        }else{
            return ['result'=>0, 'msg'=>''                , 'data'=>$data   ];
        }
    }
    public function org_all(){
        $data = Extended_chatbot_usage::org_all();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[]      ];
        }else{
            return ['result'=>0, 'msg'=>''                , 'data'=>$data   ];
        }
    }
    //显示---------------------------------------
    public function show($id){
        $data = Extended_chatbot_usage::findExtendedChatID($id);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[]      ];
        }else{
            return ['result'=>0, 'msg'=>''                , 'data'=>$data   ];
        }
    }

    //搜索---------------------------------------
    public function search($s_time,$e_time,$user_id,$org_id, $field, $value){
        //-----------------------------------------------------------------------------------------
        $data = Extended_chatbot_usage::myExtendedEntitys($s_time,$e_time,$user_id,$org_id, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0  ];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data    ];
        }
    }

    //查找$orgID的显示所有+排序---------------------------------------
    public function showAll($archive,$s_time,$e_time,$user_id,$org_id, $perPage, $page ){
        return $this->showAllSorted($archive,$s_time,$e_time,$user_id,$org_id, $perPage, $page, 'chat_id', 'desc');
    }
    public function showAllSorted($archive,$s_time,$e_time,$user_id,$org_id, $perPage, $page, $sort, $order){

        //-----------------------------------------------------------------------------------------
        $data  = Extended_chatbot_usage::myExtendedEntitys($archive,$s_time,$e_time,$user_id,$org_id, '', '')->orderBy($sort, $order)->get();
        $total = Extended_chatbot_usage::myExtendedEntitys($archive,$s_time,$e_time,$user_id,$org_id, '', '')->count();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{



                /*$tmpExtended_chatbot_log = new Extended_chatbot_log;
                $data_chatbot_log = $tmpExtended_chatbot_log
                    ->select('chat_id')
                    ->where('chat_id',  'like', "%{$searc}%")
                    ->groupBy('chat_id')
                    ->get();
                if($data_chatbot_log->isEmpty()){ return null; }
                foreach( $data_chatbot_log as $key=>$tmp ){
                    if($tmp->chat_id){
                        array_push($retVal,$tmp->chat_id);
                    }
                }*/




            return ['result'=>0, 'msg'=>'', 'total'=>$total, 'data'=>$data];
        }
    }

    //分页查询---------------------------------------
    public function showPage($archive,$s_time,$e_time,$user_id,$org_id, $perPage, $page){
        $data  = Extended_chatbot_usage::myPageing($archive,$s_time,$e_time,$user_id,$org_id, $perPage, $page, 'chat_id', 'desc');
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
        }
    }

    //分页查询+排序---------------------------------------
    public function showPageSorted($archive,$s_time,$e_time,$user_id,$org_id, $sort, $order, $perPage, $page ,$searc_email,$searc){

        $count = Extended_chatbot_usage::myExtendedEntitys($archive,$s_time,$e_time,$user_id,$org_id,  '', '',$searc_email,$searc)->count();
        //$data  = Extended_chatbot_usage::myPageing($archive,$s_time,$e_time,$user_id,$org_id, $perPage, $page, 'chat_id', 'desc',$searc_email,$searc);
        $data  = Extended_chatbot_usage::myPageing($archive,$s_time,$e_time,$user_id,$org_id, $perPage, $page, $sort, $order,$searc_email,$searc);

        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            foreach( $data as $key=>$tmp ){
                $tmpExtended_chatbot_log = new Extended_chatbot_log;
                $data_chatbot_log_count = $tmpExtended_chatbot_log
                    ->select('chat_id')
                    ->where('chat_id',  '=', $tmp->chat_id)
                    ->count();
                $tmp->logcount=$data_chatbot_log_count;

                $max_logtime=0;
                if($data_chatbot_log_count>0){
                    $tmpExtended_max_logtime = new Extended_chatbot_log;
                    $data_chatbot_max_logtime = $tmpExtended_max_logtime
                        ->select('timestamp')
                        ->where('chat_id',  '=', $tmp->chat_id)
                        ->orderBy('msg_id', 'desc')
                        ->get();
                        $max_logtime=floor((strtotime($data_chatbot_max_logtime[0]->timestamp)-strtotime($tmp->timestamp))%86400%60);

                }
                $tmp->log_s=$max_logtime.'sec.';
            }
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //分页查询+排序+搜索---------------------------------------
    public function showPageSortSearch($archive,$extendedSubTypeId, $orgID, $sort, $order, $perPage, $page, $field, $value ){

        $count = Extended_chatbot_usage::myExtendedEntitys($archive,$extendedSubTypeId, $orgID, $field, $value)->count();

        $data  = Extended_chatbot_usage::myPageingWithSearch($archive,$extendedSubTypeId, $orgID, $perPage, $page, $sort, $order, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }
    //删---------------------------------------
    public function deleteRow($orgId,$id,Request $request){
        if($orgId!=0){ return ['result'=>1, 'msg'=>"You can't delete this Extended Chatbot Log"]; }
        $temp_json=$request->input('archiveId_arr');
        try{
            foreach ($temp_json as $valueid)
            {
                $extended_entity = Extended_chatbot_usage::find($valueid);
                if(is_null($extended_entity) ){
                    //array_push($usageArr,$extended_entity);
                }else{
                    $data =  Extended_chatbot_log::findExtendedChat_id_DATA($valueid);
                    foreach( $data as $key=>$tmp ){
                        $tmp->delete($tmp->msg_id);
                    }
                }
                $tmp = $extended_entity->delete($id);
            }
            return ['result'=>0, ''];

            /*$extended_entity = Extended_chatbot_usage::find($id);
            if(is_null($extended_entity) ){
                return ['result'=>1, 'msg'=>"Log not found"];
            }else{
                $data =  Extended_chatbot_log::findExtendedChat_id_DATA($id);
                foreach( $data as $key=>$tmp ){
                    $tmp->delete($tmp->msg_id);
                }
                $tmp = $extended_entity->delete($id);
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }*/
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
        //return ['result'=>1, ''];
    }
    //改---------------------------------------

    //archive
    public function upArchive($orgId,$id,Request $request){
        $temp_json=$request->input('archiveId_arr');
        try{

            /*foreach ($temp_json as $valueid)
            {
                $extended_entity = Extended_chatbot_usage::find($valueid);
                if(is_null($extended_entity) ){
                    //array_push($usageArr,$extended_entity);
                }else{
                    $data =  Extended_chatbot_log::findExtendedChat_id_DATA($valueid);
                    foreach( $data as $key=>$tmp ){
                        $tmp->delete($tmp->msg_id);
                    }
                }
                $tmp = $extended_entity->delete($id);
            }
            return ['result'=>0, ''];*/

            $extended_chatbot_usage =  Extended_chatbot_usage::find($id);
            if(is_null($extended_chatbot_usage)){
                return ['result'=>1, 'msg'=>"Log not found"];
            }else{
                if($extended_chatbot_usage->archive==0){
                    $extended_chatbot_usage->archive=1;
                }else{
                    $extended_chatbot_usage->archive=0;
                }
                $tmp = $extended_chatbot_usage->save();
                foreach ($temp_json as $valueid){
                    $extended_entity = Extended_chatbot_usage::find($valueid);
                    if(is_null($extended_entity) ){
                    }else{
                        $extended_entity->archive=$extended_chatbot_usage->archive;
                        $extended_entity->save();
                    }
                }
                //return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
            return ['result'=>0, ''];
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
        //return ['result'=>1, ''];
    }
    //order
    public function upOrder($orgId,$id,$order){
        try{
            $extended_chatbot_usage =  Extended_chatbot_usage::find($id);
            if(is_null($extended_chatbot_usage)){
                return ['result'=>1, 'msg'=>"Log not found"];
            }else{
                $extended_chatbot_usage->order=$order;
                $tmp = $extended_chatbot_usage->save();
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

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

                if($extended_entity->extendedEntityName     ==''){ return ['result'=>1, 'msg'=>'extendedEntity name is empty'];             }
                if($extended_entity->ownership            ==''){ return ['result'=>1, 'msg'=>'extendedEntity ownership is empty'];        }
                if($extended_entity->ownerId              ==''){ return ['result'=>1, 'msg'=>'extendedEntity owner ID is empty'];         }
                if($extended_entity->lastUserId           ==''){ return ['result'=>1, 'msg'=>'extendedEntity last user id is empty'];     }

                if($extended_entity->extendedSubTypeId            ==''){ return ['result'=>1, 'msg'=>'extendedEntity extendedSubTypeId is empty'];        }

                $tmp = $extended_entity->save();
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

}