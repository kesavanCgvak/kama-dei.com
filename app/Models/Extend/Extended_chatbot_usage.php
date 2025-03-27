<?php
namespace App\Models\Extend;
use App\Models\Extend\Extended_chatbot_log;
use Illuminate\Database\Eloquent\Model;

class Extended_chatbot_usage extends Model
{
    public    $timestamps = false;

    protected $connection = 'mysqllog';
    protected $table      = 'chatbot_usage';
    protected $primaryKey = "chat_id";
    protected $modifiers  = ['chat_id', 'ip',
        'email',
        'user_id',
        'org_id',
        'user_name',
        'org_name',
        'timestamp',
        'memo',
        'archive',
        'order'
    ];
//    protected $dates      = ['dateCreated'];
    protected $dates_c      = ['dateCreated'];
    protected $dates_u      = ['dateUpdated'];
    //--------------------------------------------------------------------


    public function findExtendedOrgName($org_name)
    {
        return ($this->where('org_name', '=', $org_name)->get())->toArray();
    }
    //--------------------------------------------------------------------
    protected function findExtendedChatID($chat_id)
    {
        return $this->where('chat_id', '=', $chat_id)->get()->toArray();
    }
    //--------------------------------------------------------------------
    public function findById($id)
    {
        return ($this->where('chat_id', '=', $id)->get())->toArray();
    }

    protected function org_all(){
        return $this
                ->select('org_id', 'org_name')
                ->whereRaw('org_name  IS NOT NULL')
                ->groupBy('org_id','org_name')
                ->orderBy('org_id', 'asc')
                ->get();
    }
   /* public function chatbotlog($raw_msg){
        return DB::table('chatbot_log')
            ->select('chat_id')
            ->where('msg',  'like', "%{$raw_msg}%")
            ->groupBy('chat_id')
            ->get();

        //return $this->belongsTo('App\Organization', 'ownerId', 'organizationId');
    }*/
    //--------------------------------------------------------------------
    protected function myExtendedEntitys($archive,$s_time,$e_time,$user_id,$org_id, $field, $value,$searc_email,$searc){
        $retVal = [];
        if($searc!='0'){
            $tmpExtended_chatbot_log = new Extended_chatbot_log;
            $data_chatbot_log = $tmpExtended_chatbot_log
                ->select('chat_id')
                ->where('msg',  'like', "%{$searc}%")
                ->groupBy('chat_id')
                ->get();
            if($data_chatbot_log->isEmpty()){ return null; }
            foreach( $data_chatbot_log as $key=>$tmp ){
                if($tmp->chat_id){
                    array_push($retVal,$tmp->chat_id);
                }
            }
        }

        $tempthis=null;

        if( $value=='' ){
            if($s_time!=''&&$e_time!=''){
                $s_time = date('Y-m-d H:i:s', $s_time);
                $e_time = date('Y-m-d H:i:s', $e_time);
                $map['timestamp'] = array('between',array($s_time,$e_time));
                if($searc_email=='0'){
                    $tempthis=$this
                        ->where(function($q) use($user_id){
                            if($user_id==0){ return $q; }
                            else{ return $q->where('user_id', '=', $user_id); }
                        })
                        ->where(function($q) use($org_id){
                            if($org_id==0){ return $q; }
                            else{ return $q->where('org_id', '=',$org_id); }
                        })
                        ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time]);
                }else{
                    $tempthis=$this
                        ->where(function($q) use($user_id){
                            if($user_id==0){ return $q; }
                            else{ return $q->where('user_id', '=', $user_id); }
                        })
                        ->where(function($q) use($org_id){
                            if($org_id==0){ return $q; }
                            else{ return $q->where('org_id', '=',$org_id); }
                        })
                        ->where('email', 'like', "%{$searc_email}%")
                        ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time]);
                }


            }else{
                if($searc_email=='0'){
                    $tempthis=$this
                        ->where(function($q) use($user_id){
                            if($user_id==0){ return $q; }
                            else{ return $q->where('user_id', '=', $user_id); }
                        })
                        ->where(function($q) use($org_id){
                            if($org_id==0){ return $q; }
                            else{ return $q->where('org_id', '=',$org_id); }
                        });
                }else{
                    $tempthis=$this
                        ->where(function($q) use($user_id){
                            if($user_id==0){ return $q; }
                            else{ return $q->where('user_id', '=', $user_id); }
                        })
                        ->where(function($q) use($org_id){
                            if($org_id==0){ return $q; }
                            else{ return $q->where('org_id', '=',$org_id); }
                        })
                        ->where('email', 'like', "%{$searc_email}%");
                }

            }
        }else{
            if($s_time!=''&&$e_time!=''){
                $s_time = date('Y-m-d H:i:s', $s_time);
                $e_time = date('Y-m-d H:i:s', $e_time);
                if($searc_email=='0'){
                    $tempthis=$this
                        ->where(function($q) use($user_id){
                            if($user_id==0){ return $q; }
                            else{ return $q->where('user_id', '=', $user_id); }
                        })
                        ->where($field, 'like', "%{$value}%")
                        ->where(function($q) use($org_id){
                            if($org_id==0){ return $q; }
                            else{ return $q->where('org_id', '=',$org_id); }
                        })
                        ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time]);
                }else{
                    $tempthis=$this
                        ->where(function($q) use($user_id){
                            if($user_id==0){ return $q; }
                            else{ return $q->where('user_id', '=', $user_id); }
                        })
                        ->where($field, 'like', "%{$value}%")
                        ->where(function($q) use($org_id){
                            if($org_id==0){ return $q; }
                            else{ return $q->where('org_id', '=',$org_id); }
                        })
                        ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])
                        ->where('email', 'like', "%{$searc_email}%");
                }

                   /* ->where('timestamp', '>=', $s_time)
                    ->where('timestamp', '<=', $e_time);*/
            }else{
                if($searc_email=='0'){
                    $tempthis=$this
                        ->where(function($q) use($user_id){
                            if($user_id==0){ return $q; }
                            else{ return $q->where('user_id', '=', $user_id); }
                        })
                        ->where($field, 'like', "%{$value}%")
                        ->where(function($q) use($org_id){
                            if($org_id==0){ return $q; }
                            else{ return $q->where('org_id', '=',$org_id); }
                        });
                }else{
                    $tempthis=$this
                        ->where(function($q) use($user_id){
                            if($user_id==0){ return $q; }
                            else{ return $q->where('user_id', '=', $user_id); }
                        })
                        ->where($field, 'like', "%{$value}%")
                        ->where(function($q) use($org_id){
                            if($org_id==0){ return $q; }
                            else{ return $q->where('org_id', '=',$org_id); }
                        })
                        ->where('email', 'like', "%{$searc_email}%");
                }

            }
        }
        if(count($retVal)>0){
            $tempthis->whereIn('chat_id', $retVal);
        }
        if($archive==1){
            $tempthis->where('archive',  '=', 0);
        }
        if($archive==2){
            $tempthis->where('archive',  '=', 1);
        }
       return $tempthis;
    }
    //--------------------------------------------------------------------
    protected function myPageing($archive,$s_time,$e_time,$user_id,$org_id, $perPage, $page, $sort, $order,$searc_email,$searc){
        //----------------------------------------------------------------
        $data = null;
        //----------------------------------------------------------------
        $data = $this->myExtendedEntitys($archive,$s_time,$e_time,$user_id,$org_id, '', '',$searc_email,$searc)->orderBy($sort, $order)->get()->forPage($page, $perPage);
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
    protected function myPageingWithSearch($archive,$s_time,$e_time,$user_id,$org_id, $perPage, $page, $sort, $order, $field, $value){
        $data = null;

        $data = $this->myExtendedEntitys($archive,$s_time,$e_time,$user_id,$org_id, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
        if($data->isEmpty()){ return null; }
        $retVal = [];
        foreach( $data as $key=>$tmp ){
            $retVal[] = $tmp;
        }
        return $retVal;
    }
    //--------------------------------------------------------------------
    public function findExtendedEntityByID($user_id,$org_id, $id){
        return $this->where('chat_id', '=', $id)->get();
    }
    //--------------------------------------------------------------------
    public function findExtendedEntity($s_time,$e_time,$user_id,$org_id, $field, $value,$searc_email){
        if($org_id==0){
            if($user_id==0){
                if($s_time&&$e_time){
                    $s_time = date('Y-m-d H:i:s', $s_time);
                    $e_time = date('Y-m-d H:i:s', $e_time);
                    if($searc_email=='0'){
                        return $this->where($field, 'like', "%{$value}%")
                            ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])
                        ->get();
                    }else{
                        return $this->where($field, 'like', "%{$value}%")
                            ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])
                            ->where('email', 'like', "%{$searc_email}%")
                        ->get();
                    }

                }else{
                    return $this->where($field, 'like', "%{$value}%")->get();
                }
            }else{
                if($s_time&&$e_time){
                    $s_time = date('Y-m-d H:i:s', $s_time);
                    $e_time = date('Y-m-d H:i:s', $e_time);
                    if($searc_email=='0'){
                        return $this
                            ->where('user_id', '=', $user_id)
                            ->where($field, 'like', "%{$value}%")
                            ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])

                            ->get();
                    }else{
                        return $this
                            ->where('user_id', '=', $user_id)
                            ->where($field, 'like', "%{$value}%")
                            ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])
                            ->where('email', 'like', "%{$searc_email}%")
                            ->get();
                    }

                }else{
                    if($searc_email=='0'){
                        return $this
                            ->where('user_id', '=', $user_id)
                            ->where($field, 'like', "%{$value}%")

                            ->get();
                    }else{
                        return $this
                            ->where('user_id', '=', $user_id)
                            ->where($field, 'like', "%{$value}%")
                            ->where('email', 'like', "%{$searc_email}%")
                            ->get();
                    }

                }
            }

        }
        else{
            if($user_id==0){
                if($s_time&&$e_time){
                    $s_time = date('Y-m-d H:i:s', $s_time);
                    $e_time = date('Y-m-d H:i:s', $e_time);
                    if($searc_email=='0'){
                        return $this->where('org_id', '=', $org_id)->where($field, 'like', "%{$value}%")
                            ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])

                            ->get();
                    }else{
                        return $this->where('org_id', '=', $org_id)->where($field, 'like', "%{$value}%")
                            ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])
                            ->where('email', 'like', "%{$searc_email}%")
                            ->get();
                    }

                }else{
                    if($searc_email=='0'){
                        return $this->where('org_id', '=', $org_id)->where($field, 'like', "%{$value}%")->get();
                    }else{
                        return $this->where('org_id', '=', $org_id)->where($field, 'like', "%{$value}%")
                            ->where('email', 'like', "%{$searc_email}%")->get();
                    }

                }
            }else{
                if($s_time&&$e_time){
                    $s_time = date('Y-m-d H:i:s', $s_time);
                    $e_time = date('Y-m-d H:i:s', $e_time);
                    if($searc_email=='0'){
                        return $this
                            ->where('org_id', '=', $org_id)
                            ->where('user_id', '=', $user_id)
                            ->where($field, 'like', "%{$value}%")
                            ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])

                            ->get();
                    }else{
                        return $this
                            ->where('org_id', '=', $org_id)
                            ->where('user_id', '=', $user_id)
                            ->where($field, 'like', "%{$value}%")
                            ->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])
                            ->where('email', 'like', "%{$searc_email}%")
                            ->get();
                    }

                }else{
                    if($searc_email=='0'){
                        return $this
                            ->where('org_id', '=', $org_id)
                            ->where('user_id', '=', $user_id)
                            ->where($field, 'like', "%{$value}%")

                            ->get();
                    }else{
                        return $this
                            ->where('org_id', '=', $org_id)
                            ->where('user_id', '=', $user_id)
                            ->where($field, 'like', "%{$value}%")
                            ->where('email', 'like', "%{$searc_email}%")
                            ->get();
                    }

                }
            }
        }
    }
    //--------------------------------------------------------------------
}
