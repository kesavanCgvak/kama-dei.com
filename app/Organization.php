<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Auth\Passwords\CanResetPassword;
//use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
//use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Organization extends Model {

	public    $timestamps = false;
	protected $connection = 'mysql';
	protected $table      = 'organization_ep';
	protected $primaryKey = "organizationId";

//	protected $fillable = ['organizationShortName', 'last'];
//	protected $hidden = ['userPass'];
/*
    public function pageOrganizationLevel(){
        return $this->hasMany('App\PageOrganizationLevel');
    }
*/
	//--------------------------------------------------------------------
    public function getName($id){ 
		try{
			if($id==0){ return env('BASE_ORGANIZATION');}
			$tmp = $this->find($id);
			if(is_null($tmp)){ return ''; }
			return $tmp->organizationShortName;
		}catch(Exception  $ex){ return ''; }
    }
	//--------------------------------------------------------------------
	protected function myOrganization($orgID, $field, $value){
		if( $value=='' ){
			return $this
				->leftJoin('kamadeikb.organization_personality as orgPersonality' ,function($join){
					$join->on('organization_ep.organizationId', '=', 'orgPersonality.organizationId')->where('orgPersonality.is_default', '=', 1);
				})
				->leftJoin('kamadeikb.personality as personality', 'orgPersonality.personalityId', '=', 'personality.personalityId')
				->where(
					function($q) use($orgID){ 
						if($orgID==0){ return $q;}
						else{ return $q->where('organization_ep.organizationId', $orgID); } 
					}
				)
				->select(
					'organization_ep.*',
					'orgPersonality.personalityId as personalityId',
					'personality.personalityName as personalityName'
				);
		}else{
			return $this
				->leftJoin('kamadeikb.organization_personality as orgPersonality' ,function($join){
					$join->on('organization_ep.organizationId', '=', 'orgPersonality.organizationId')->where('orgPersonality.is_default', '=', 1);
				})
				->leftJoin('kamadeikb.personality as personality', 'orgPersonality.personalityId', '=', 'personality.personalityId')
				->where(
					function($q) use($orgID){ 
						if($orgID==0){ return $q;}
						else{ return $q->where('organization_ep.organizationId', $orgID); } 
					}
				)
				->where(
					function($q) use ($value){ 
						return $q
							->where('organization_ep.organizationId' , 'like', "%{$value}%")
							->orwhere('organization_ep.organizationShortName' , 'like', "%{$value}%")
							->orwhere('personality.personalityName' , 'like', "%{$value}%");
					}
				)
				->select(
					'organization_ep.*',
					'orgPersonality.personalityId as personalityId',
					'personality.personalityName as personalityName'
				);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order){
		//----------------------------------------------------------------
		$data = null;
		//----------------------------------------------------------------
		/*
		switch($order){
			case 'asc' :{ $data = $this->get()->sortBy    ($sort)->forPage($page, $perPage); break; }
			case 'desc':{ $data = $this->get()->sortByDesc($sort)->forPage($page, $perPage); break; }
		}
		*/
		$data = $this->myOrganization($orgID, '', '')->orderBy($sort, $order)->get()->forPage($page, $perPage);
		//----------------------------------------------------------------
		if($data->isEmpty()){ return null; }
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $key=>$tmp ){ $retVal[] = $tmp; }
		return $retVal;
		//----------------------------------------------------------------
	}
	//--------------------------------------------------------------------
	protected function myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value){
		$data = null;
		/*
		switch($order){
			case 'asc' :{ $data = $this->where($field, 'like', "%{$value}%")->get()->sortBy    ($sort)->forPage($page, $perPage); break; }
			case 'desc':{ $data = $this->where($field, 'like', "%{$value}%")->get()->sortByDesc($sort)->forPage($page, $perPage); break; }
		}
		*/
		$data = $this->myOrganization($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
		if($data->isEmpty()){ return null; }
		$retVal = [];
		foreach( $data as $key=>$tmp ){ $retVal[] = $tmp; }
		return $retVal;
	}
	//--------------------------------------------------------------------
	protected static function myOrgRelated($orgID){
		return self::
			where(function($q) use($orgID){ 
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->Where('organizationId', $orgID)
								->orWhere(function($q) use($tmpOrgIDs){ return $q->whereIn('organizationId', $tmpOrgIDs); });
					}
			});
	}
	//--------------------------------------------------------------------
}
