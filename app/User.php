<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
/*

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as AuthenticatableUser;
*/
//class User extends Authenticatable
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
//    use Notifiable;
	use Authenticatable, CanResetPassword;

	public    $timestamps = false;

	protected $table = 'user';
	protected $primaryKey = "id";

//	protected $fillable = ['name', 'email', 'password'];
	protected $fillable = ['userName', 'userPass', 'email', 'orgID', 'levelID', 'createAt', 'lastLogin', 'isAdmin'];

//	protected $hidden = ['password', 'remember_token'];
	protected $hidden = ['userPass'];

	protected $salt = "K@ma";
	public function hash($pass){ return md5($pass.$this->salt); }

	//--------------------------------------------------------------------
	protected function myUser($orgID, $field='', $value=''){
		if($value==''){
			return $this
				->leftJoin('kamadeikb.consumer_user_personality as cup', 'user.id', 'consumerUserId')
				->with(['organization', 'level'])
				->where(function($q) use($orgID){
					if($orgID==0){ return $q; }
					else{ return $q->where('orgID', $orgID); }
				})
				->select(
					"user.*",
					"cup.nickname",
					\DB::raw("(if(cup.consUserPersonalityId is null, 0, 1)) as isConsumer")
				);
		}else{
			return $this
				->leftJoin('kamadeikb.consumer_user_personality as cup', 'user.id', 'consumerUserId')
				->with(['organization', 'level'])
				->where(function($q) use($orgID){
					if($orgID==0){ return $q; }
					else{ return $q->where('orgID', $orgID); }
				})
				->where(function($q) use($field, $value){
					return $q
						->where($field, 'like', "%{$value}%")
						->orWhere('userName', 'like', "%{$value}%")
						->orWhere('email', 'like', "%{$value}%")
						->orWhere('cup.nickname', 'like', "%{$value}%");
				})
				->select(
					"user.*",
					"cup.nickname",
					\DB::raw("(if(cup.consUserPersonalityId is null, 0, 1)) as isConsumer")
				);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $field='', $value='', $ownerId=-1, $level=0){
		//----------------------------------------------------------------
		$data = null;
		//----------------------------------------------------------------
		$data = $this
					->myUser($orgID, $field, $value)
					->where(function($q) use($ownerId){
						if($ownerId==-1){ return $q; }
						else{ return $q->where('orgID', $ownerId); }
					})
					->where(function($q) use($level){
						if($level==0){ return $q; }
						else{ return $q->where('levelID', $level); }
					})
					->orderBy($sort, $order)->get()->forPage($page, $perPage);
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		$retVal = [];
		foreach( $data as $key=>$tmp ){ 
			if($tmp->orgID==0){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$tmp->levelName = $tmp->level->levelName;
			if($tmp->levelID==4 || true){
				$userPersonality = $this->getUserPersonality( $tmp->id );
				$tmp->userPersonality = ((strlen($userPersonality)>1) ?substr($userPersonality,2) :'');
			}else{ $tmp->userPersonality = ""; }
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
	protected function myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value){
		$data = null;
		$data = $this->myUser($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
		if($data->isEmpty()){ return null; }
		$retVal = [];
		foreach( $data as $key=>$tmp ){ 
			if($tmp->orgID==0){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$tmp->levelName = $tmp->level->levelName;
			$tmp->userPersonality = "1";
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
	protected function getUserPersonality( $consumerUserId ){
		$tmpCUPs = \App\ConsumerUserPersonality::where('consumerUserId', $consumerUserId)->select('personalityId')->get();
		$personalityUsers = "";
		foreach($tmpCUPs as $tmpCUP){ 
			$personality = \App\Personality::where('personalityId', $tmpCUP->personalityId)->first();
			if($personality!=null){
//				$personalityUsers.=", <a class='callGetUser' onclick='callGetUser({$personality->personalityId})'>{$personality->personalityName}</a>"; 
				$personalityUsers.=", {$personality->personalityName}"; 
			}
		}
		return $personalityUsers;
	}
	//--------------------------------------------------------------------
    public function organization(){
        return $this->belongsTo('App\Organization', 'orgID', 'organizationId');
    }
	//--------------------------------------------------------------------
    public function level(){
        return $this->belongsTo('App\Level', 'levelID', 'id');
    }
	//--------------------------------------------------------------------
	public function findUserByID($orgID, $id){
		if($orgID==0){ return $this->with(['organization'])->where('userId', '=', $id)->get(); }
		else{ return $this->with(['organization'])->whereIn('orgID', [$orgID])->where('userId', '=', $id)->get(); }
	}
	//--------------------------------------------------------------------
	public function findUser($orgID, $field, $value){
		if($orgID==0){ return $this->with(['organization'])->where($field, 'like', "%{$value}%")->get(); }
		else{ return $this->with(['organization'])->whereIn('orgID', [$orgID])->where($field, 'like', "%{$value}%")->get(); }
	}
	//--------------------------------------------------------------------
	public function isValidPassKey($passKey){
		if($this->where('passKey', $passKey)->where('levelID', '<>', 4)->count()==0){ return false; }
		else{ return true; }
	}
	//--------------------------------------------------------------------
	public function createConsumerUser($email, $orgID){
		if($this->where('passKey', '=', $passKey)->count()==0){ return false; }
		else{ return true; }
	}
	//--------------------------------------------------------------------
	protected function ownersList($orgID){
		return $this
			->leftJoin('kamadeiep.organization_ep as organization', 'user.orgID', '=', 'organization.organizationId')
			->where(function($q) use($orgID){ 
					if($orgID==0){ return $q; }
					else{ return $q->where('orgID', $orgID); }
			})
			->groupBy('orgID')
			->groupBy('organizationShortName')
			->select('orgID as ownerId', 'organization.organizationShortName as organizationShortName');
	}
	//--------------------------------------------------------------------
	protected function getOwnersList($orgID){
		$data = null;
		$data = $this->ownersList($orgID)->get();
		if($data->isEmpty()){ return []; }
		$retVal = [];
		foreach( $data as $key=>$tmp ){
			$val = new \stdClass();
			if($tmp->ownerId==0){ $val->text = env('BASE_ORGANIZATION'); }
			else{ $val->text = $tmp->organizationShortName; }
			$val->id = $tmp->ownerId;
			$retVal[] = $val;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
}
