<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Personality extends Model
{
	public    $timestamps = false;

	protected $connection = 'mysql2';
	protected $table      = 'personality';
	protected $primaryKey = "personalityId";
	//--------------------------------------------------------------------
	protected function myPersonality($orgID, $field, $value, $ownerId=-99){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$PRIVTE = \Config::get('kama_dei.static.PRIVATE'  ,2);
		if( $value=='' ){
			$retVal = $this
//				->with(['organization'])
				->leftJoin('kamadeiep.organization_ep as org', 'personality.ownerId', '=', 'org.organizationId')
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){ 
					if($orgID==0 && $ownerId==-99){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						$q = $q
							->where('personality.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
								return $q->whereIn('personality.ownerId', $tmpOrgIDs)->where('personality.ownership', $PRTCTD);
							});
						if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('personality.ownerId', $orgID); }
						if($orgID==0 && $ownerId!=-99){ return $q->orWhere('personality.ownerId', $ownerId); }
						return $q;
					}
				});
		}else{
			$retVal = $this
//				->with(['organization'])
				->leftJoin('kamadeiep.organization_ep as org', 'personality.ownerId', '=', 'org.organizationId')
				->where(function($q) use($value){
					return $q
						->where(
							\DB::raw("if(personality.ownerId=0 or personality.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName)"),
							'like', 
							"%{$value}%"
						)
//						->orWhereRaw("personality.parentPersonaName like ?"     , ["%{$value}%"])
						->orWhereRaw("personality.personalityDescription like ?", ["%{$value}%"])
						->orWhereRaw("personality.personalityName like ?"       , ["%{$value}%"])
//						->orWhereRaw("personality.personalityUsers like ?"      , ["%{$value}%"])
						->orwhere(
							\DB::raw("if(personality.ownership=0, 'Public', if(personality.ownership=1, 'Protected', 'Private' ))") , 
							'like', 
							"%{$value}%"
						);
				})
				->where(function($q) use($orgID, $PUBLIC, $PRTCTD, $ownerId){ 
					if($orgID==0 && $ownerId==-99){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						$q = $q
							->where('personality.ownership', $PUBLIC)
							->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
								return $q->whereIn('personality.ownerId', $tmpOrgIDs)->where('personality.ownership', $PRTCTD);
							});
						if($orgID==$ownerId || $ownerId==-99){ return $q->orWhere('personality.ownerId', $orgID); }
						if($orgID==0 && $ownerId!=-99){ return $q->orWhere('personality.ownerId', $ownerId); }
						return $q;
					}
				});
		}
		if(strpos($_SERVER['REQUEST_URI'], '/persona/')!=false){ $retVal = $retVal->where('parentPersonaId', 0); }
		else{ $retVal = $retVal->where('parentPersonaId', '>', 0); }

		return $retVal->select(
					"personality.*",
					\DB::raw("if(personality.ownerId=0 or personality.ownerId is null, '".env('BASE_ORGANIZATION')."', org.organizationShortName) as organizationShortName"),
					\DB::raw("if(personality.ownership=0, 'Public', if(personality.ownership=1, 'Protected', 'Private' )) as ownerShipText")
				);
}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $field, $value, $ownerId){
		$data = null;
		switch($ownerId){
			case -1: $data = $this->myPersonality($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			case  0: $data = $this->myPersonality($orgID, $field, $value)->where('ownerId', null)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
			default: $data = $this->myPersonality($orgID, $field, $value)->where('ownerId', $ownerId)->orderBy($sort, $order)->get()->forPage($page, $perPage); break;
		}
		if($data->isEmpty()){ return null; }
		$retVal = [];
		foreach( $data as $tmp ){
//			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
//			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }

			if($tmp->parentPersonaId==0){ $tmp->parentPersonaName = ''; }
			else{
				$tmpFind = $this->find($tmp->parentPersonaId);
				if(is_null($tmpFind) ){ $tmp->parentPersonaName = 'parent not found'; }
				else{ $tmp->parentPersonaName = $tmpFind->personalityName; }
			}
			$personalityUsers = $this->getPersonalityUsers( $tmp->personalityId );
			$tmp->personalityUsers = ((strlen($personalityUsers)>1) ?substr($personalityUsers, 2): "");
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
	protected function getPersonalityUsers( $personalityId ){
		$tmpCUPs = \App\ConsumerUserPersonality::where('personalityId', $personalityId)->select('consumerUserId')->groupBy('consumerUserId')->get();
		$personalityUsers = "";
		foreach($tmpCUPs as $tmpCUP){ 
			$usr = \App\User::find($tmpCUP->consumerUserId);
			if($usr!=null){ $personalityUsers.=", <a class='callGetUser' onclick='callGetUser({$usr->id})'>{$usr->email}</a>"; }
		}
		return $personalityUsers;
	}
	//--------------------------------------------------------------------
    public function organization(){ return $this->belongsTo('App\Organization', 'ownerId', 'organizationId'); }
	//--------------------------------------------------------------------
    public function parentPersona(){ return $this->belongsTo('App\Personality', 'parentPersonaId', 'personalityId'); }
	//--------------------------------------------------------------------
	public  function getData($id){
        $data =
			$this
				->with(['organization'])
				->where('personalityId',$id)
				->first();
		if($data==null){ return null; }
		if($data->ownerId==null){ $data->organizationShortName = env('BASE_ORGANIZATION'); }
		else{ $data->organizationShortName = $data->organization->organizationShortName; }
		unset( $data->organization );
		return $data;
	}
	//--------------------------------------------------------------------
    public function getConsumerUser(){ return $this->belongsTo('App\ConsumerUserPersonality', 'personalityId', 'personalityId'); }
	//--------------------------------------------------------------------
	protected function personalityOwnersList($orgID){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$data = $this
			->leftJoin('kamadeiep.organization_ep', 'kamadeikb.personality.ownerId', '=', 'kamadeiep.organization_ep.organizationId')
			->where('parentPersonaId', '>', 0)
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD){ 
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->where('personality.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('personality.ownerId', $tmpOrgIDs)->where('personality.ownership', $PRTCTD);
								})
								->orWhere('personality.ownerId', $orgID);
					}
			})
			->groupBy('personality.ownerId')
			->select(
				\DB::raw("if(personality.ownerId=0 or personality.ownerId is null, 0, personality.ownerId) as id"),
				\DB::raw("if(personality.ownerId=0 or personality.ownerId is null, '".env('BASE_ORGANIZATION')."', organization_ep.organizationShortName) as text")
			)
			->orderBy("text", "asc")
			->get()->toArray();
		
		$isTrue = false;
		foreach($data as $tmp){ if($tmp['id']==$orgID){ $isTrue=true; } }
		if($isTrue==false){
			if($orgID!=0){
				$tmp = \App\Organization::find($orgID);
				if($tmp!=null){
/*
					$val         = [];
					$val['id']   = $orgID;
					$val['text'] = $tmp->organizationShortName; 
					$data[]      = $val;
*/
					$data[] = [
						"id"   => $orgID,
						"text" => $tmp->organizationShortName
					];
				}
			}else{
/*
				$val       = [];
				$val->id   = 0;
				$val->text = env('BASE_ORGANIZATION'); 
				$data[]    = $val;
*/
				$data[] = [
					"id"   => 0,
					"text" => env('BASE_ORGANIZATION')
				];
			}
		}

		$isTrue = false;
		$i = 0;
		foreach($data as $tmp){
			if($tmp['id']==0 && $isTrue){
				$isTrue=false;
				break;
			}
			if($tmp['id']==0){ $isTrue=true; }
			$i++;
		}
		if(!$isTrue){ unset($data[$i]); }

		return $data;
	}
	//--------------------------------------------------------------------
	protected function personaOwnersList($orgID){
		$PUBLIC = \Config::get('kama_dei.static.PUBLIC'   ,0);
		$PRTCTD = \Config::get('kama_dei.static.PROTECTED',1);
		$data = $this
			->leftJoin('kamadeiep.organization_ep', 'kamadeikb.personality.ownerId', '=', 'kamadeiep.organization_ep.organizationId')
			->where('parentPersonaId', '=', 0)
			->where(function($q) use($orgID, $PUBLIC, $PRTCTD){ 
					if($orgID==0){ return $q; }
					else{
						$tmpOrgIDs = OrgRelations::haveAccessTo($orgID);
						return $q
								->where('personality.ownership', $PUBLIC)
								->orWhere(function($q) use($tmpOrgIDs, $PRTCTD){
									return $q->whereIn('personality.ownerId', $tmpOrgIDs)->where('personality.ownership', $PRTCTD);
								})
								->orWhere('personality.ownerId', $orgID);
					}
			})
			->groupBy('personality.ownerId')
			->select(
				\DB::raw("if(personality.ownerId=0 or personality.ownerId is null, 0, personality.ownerId) as id"),
				\DB::raw("if(personality.ownerId=0 or personality.ownerId is null, '".env('BASE_ORGANIZATION')."', organization_ep.organizationShortName) as text")
			)
			->orderBy("text", "asc")
			->get()->toArray();
		
		$isTrue = false;
		foreach($data as $tmp){ if($tmp['id']==$orgID){ $isTrue=true; } }
		if($isTrue==false){
			if($orgID!=0){
				$tmp = \App\Organization::find($orgID);
				if($tmp!=null){
					$val         = [];
					$val['id']   = $orgID;
					$val['text'] = $tmp->organizationShortName; 
					$data[]      = $val;
				}
			}else{
				$val       = [];
				$val->id   = 0;
				$val->text = env('BASE_ORGANIZATION'); 
				$data[]    = $val;
			}
		}

		$isTrue = false;
		$i = 0;
		foreach($data as $tmp){
			if($tmp['id']==0 && $isTrue){
				$isTrue=false;
				break;
			}
			if($tmp['id']==0){ $isTrue=true; }
			$i++;
		}
		if(!$isTrue){ unset($data[$i]); }

		return $data;
	}
	//--------------------------------------------------------------------
	protected function getOwnersList($orgID, $flag){
		if( $flag==0 ){ return $this->personaOwnersList($orgID); }
		else{ return $this->personalityOwnersList($orgID); }
	}
	//--------------------------------------------------------------------
	protected function myPersonalityNew($orgID, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1){
		$PRIVATE = \Config::get('kama_dei.static.PRIVATE',2);
//		$tmp = $this->myPersonality($orgID, $field, $value)->orderBy($sort, $order);

		switch($ownerId){
			case -1: $tmp = $this->myPersonality($orgID, $field, $value)->orderBy($sort, $order); break;
			default: $tmp = $this->myPersonality($orgID, $field, $value, $ownerId)->orderBy($sort, $order); break;
		}

		if($tmp==null){ return null; }
		if($orgID!=$ownerId && $ownerId!=-1 && $orgID!=0){ $tmp = $tmp->where('personality.ownership', '<>', $PRIVATE); }
		if($shwglblSTT==1){ return $tmp; }
		switch($ownerId){
			case -1: return $tmp->where('personality.ownerId', $orgID);
			case  0: return $tmp->where('personality.ownerId', null);
			default: return $tmp->where('personality.ownerId', $ownerId);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageingNew($orgID, $perPage, $page, $sort, $order, $field='', $value='', $ownerId=-1, $shwglblSTT=1){
		//----------------------------------------------------------------
		$tmp = $this->myPersonalityNew($orgID, $sort, $order, $field, $value, $ownerId, $shwglblSTT);
		//----------------------------------------------------------------
		if($tmp==null){ return null; }
		//----------------------------------------------------------------
		$data = $tmp->get()->forPage($page, $perPage);
		//----------------------------------------------------------------
		$retVal = [];
		foreach( $data as $key=>$tmp ){ 
//			if($tmp->ownerId==null){ $tmp->organizationShortName = env('BASE_ORGANIZATION'); }
//			else{ $tmp->organizationShortName = $tmp->organization->organizationShortName; }
			$tmp->organization = new \StdClass;
			$tmp->organization->organizationShortName = $tmp->organizationShortName;

			if($tmp->parentPersonaId==0){ $tmp->parentPersonaName = ''; }
			else{
				$tmpFind = $this->find($tmp->parentPersonaId);
				if(is_null($tmpFind) ){ $tmp->parentPersonaName = 'parent not found'; }
				else{ $tmp->parentPersonaName = $tmpFind->personalityName; }
			}
			$personalityUsers = $this->getPersonalityUsers( $tmp->personalityId );
			$tmp->personalityUsers = ((strlen($personalityUsers)>1) ?substr($personalityUsers, 2): "");
			$retVal[] = $tmp;
		}
		//----------------------------------------------------------------
		return $retVal;
		//----------------------------------------------------------------
	}
	//--------------------------------------------------------------------
    public function oldParentPersona(){
		return $this->hasMany('App\ConsumerUserPersonalityLog', 'personalityId', 'personalityId')->with(['persona']);
	}
	//--------------------------------------------------------------------
}
