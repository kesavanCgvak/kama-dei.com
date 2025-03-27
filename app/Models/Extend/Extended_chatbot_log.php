<?php
namespace App\Models\Extend;

use Illuminate\Database\Eloquent\Model;

class Extended_chatbot_log extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysqllog';
    protected $table      = 'chatbot_log';
    protected $primaryKey = "msg_id";
    protected $modifiers  = ['msg_id', 'chat_id',
        'timestamp',
        'sender',
        'raw_msg',
        'msg',
    ];
//    protected $dates      = ['dateCreated'];
    protected $dates_c      = ['dateCreated'];
    protected $dates_u      = ['dateUpdated'];
    //--------------------------------------------------------------------

    public function findExtendedRaw_msg($raw_msg)
    {
        return  ($this->where('raw_msg', 'like', "%{$raw_msg}%")->get())->toArray();
    }
    //--------------------------------------------------------------------
    protected function findExtendedChat_id($chat_id)
    {
        return
            $this
                ->where('chat_id', '=', $chat_id)->orderBy('msg_id', 'asc')->get()->toArray();
    }
    protected function findExtendedChat_id_DATA($chat_id)
    {
        return
            $this
                ->where('chat_id', '=', $chat_id)->orderBy('msg_id', 'asc')->get();
    }
    //--------------------------------------------------------------------
    public function findById($id)
    {
        return ($this->where('msg_id', '=', $id)->get())->toArray();
    }
    //--------------------------------------------------------------------
    protected function myExtendedEntitys($chat_id, $field, $value){
        if( $value=='' ){
            return $this
                ->where(function($q) use($chat_id){
                    if($chat_id==0){ return $q; }
                    else{ return $q->where('chat_id', '=', $chat_id); }
                });
        }else{
            return $this
                ->where(function($q) use($chat_id){
                    if($chat_id==0){ return $q; }
                    else{ return $q->where('chat_id', '=', $chat_id); }
                })
                ->where($field, 'like', "%{$value}%");
        }
    }
    //--------------------------------------------------------------------
    protected function myPageing($chat_id, $perPage, $page, $sort, $order){
        //----------------------------------------------------------------
        $data = null;
        //----------------------------------------------------------------
        $data = $this->myExtendedEntitys($chat_id, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage);
        //----------------------------------------------------------------
        if($data->isEmpty()){ return null; }
        //----------------------------------------------------------------
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            $retVal[] = $tmp;
        }
        //----------------------------------------------------------------
        return $retVal;
        //----------------------------------------------------------------
    }
    //--------------------------------------------------------------------
    protected function myPageingWithSearch($chat_id, $perPage, $page, $sort, $order, $field, $value){
        $data = null;

        $data = $this->myExtendedEntitys($chat_id, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
        if($data->isEmpty()){ return null; }
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            $retVal[] = $tmp;
        }
        return $retVal;
    }
    //--------------------------------------------------------------------
    public function findExtendedEntityByID( $id){

        $this->where('msg_id', '=', $id)->get();

    }
    //--------------------------------------------------------------------
    public function findExtendedEntity($chat_id, $field, $value){
        if($chat_id==0){
            return $this->where($field, 'like', "%{$value}%")->get();
        }
        else{
            return $this->where('chat_id', '=', $chat_id)->where($field, 'like', "%{$value}%")->get();
        }
    }
    //--------------------------------------------------------------------
}
