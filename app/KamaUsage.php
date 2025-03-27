<?php 
namespace App;

use Illuminate\Database\Eloquent\Model;

class KamaUsage extends Model {
	//---------------------------------------------------------------
	use Encryptable;
	//---------------------------------------------------------------
	protected $connection = 'mysqllog';
	protected $table      = 'kama_usage';
	protected $primaryKey = "signin_id";
	public    $timestamps = false;
	//---------------------------------------------------------------
	protected $encryptable = [
		'email','user_name'
	];
	//---------------------------------------------------------------
    //public function getUserNameAttribute($value){ return \Crypt::decryptString($value); }
	//---------------------------------------------------------------
	protected static function org_all(){
        return self::
                select('org_id', 'org_name')->
                whereRaw('org_name  IS NOT NULL')->
                groupBy('org_id','org_name')->
                orderBy('org_name', 'asc')->
                get();
    }
	//---------------------------------------------------------------
	protected function logData($archive,$s_time,$e_time,$user_id,$org_id, $field, $value,$searc_email,$searc){
		//-----------------------------------------------------------
		$retVal = null;
		if($searc!='0'){
/*
			$tmpKamaLog = new \App\KamaLog;
			$dataKamaLog = $tmpKamaLog
				->select('signin_id')
//				->where('msg',  'like', "%{$searc}%")
				->groupBy('signin_id')
				->get()
				->filter(function($record) use($searc){
					if(strpos($record->msg, $searc)!==false){ return $record; }
				});
			if($dataKamaLog->isEmpty()){ return null; }
			foreach( $dataKamaLog as $key=>$tmp ){ if($tmp->signin_id){ $retVal[] = $tmp->signin_id; } }
*/
			$retVal = \App\KamaLog::getSignInIDs($searc);
			//if($retVal==null){ return $this->whereRaw("1=2"); }
//			if($tmp!=null){ =$tmp; }
		}
		//-----------------------------------------------------------
		$tempthis=null;
		//-----------------------------------------------------------
		if( $value=='' ){
			//-------------------------------------------------------
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
//->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])
						->whereBetween('timestamp', [$s_time,$e_time])
						->whereRaw("(signin_id in (select signin_id from kama_log where kama_usage.signin_id=kama_log.signin_id HAVING count(kama_log.signin_id)>?))", [0]);
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
						//->where('email', 'like', "%{$searc_email}%")
//->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])
						->whereBetween('timestamp', [$s_time,$e_time])
						->whereRaw("(signin_id in (select signin_id from kama_log where kama_usage.signin_id=kama_log.signin_id HAVING count(kama_log.signin_id)>?))", [0]);
				}
			}
			//-------------------------------------------------------
			else{
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
//						->where('email', 'like', "%{$searc_email}%")
						->whereRaw("(signin_id in (select signin_id from kama_log where kama_usage.signin_id=kama_log.signin_id HAVING count(kama_log.signin_id)>?))", [0]);
				}
			}
			//-------------------------------------------------------
		}
		//-----------------------------------------------------------
		else{
			//-------------------------------------------------------
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
						->whereRaw('unix_timestamp(timestamp) >= unix_timestamp(?) and unix_timestamp(timestamp)  <= unix_timestamp(?)',[$s_time,$e_time])
						->whereRaw("(signin_id in (select signin_id from kama_log where kama_usage.signin_id=kama_log.signin_id HAVING count(kama_log.signin_id)>?))", [0]);
				}
				else{
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
//						->where('email', 'like', "%{$searc_email}%")
						->whereRaw("(signin_id in (select signin_id from kama_log where kama_usage.signin_id=kama_log.signin_id HAVING count(kama_log.signin_id)>?))", [0]);
				}
			}
			//-------------------------------------------------------
			else{
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
//					->where('email', 'like', "%{$searc_email}%")
					->whereRaw("(signin_id in (select signin_id from kama_log where kama_usage.signin_id=kama_log.signin_id HAVING count(kama_log.signin_id)>?))", [0]);
				}
			}
			//-------------------------------------------------------
		}
		//-----------------------------------------------------------
//\Log::channel('daily')->info("TEST[{$searc}]: ");
		if($retVal!=null && count($retVal)>0){ $tempthis->whereIn('signin_id', $retVal); }
		if($archive==1){ $tempthis->where('archive',  '=', 0); }
		if($archive==2){ $tempthis->where('archive',  '=', 1); }
		//-----------------------------------------------------------
		return $tempthis;
		//-----------------------------------------------------------
	}
	//---------------------------------------------------------------
}