<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LexSetting extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqllex';
	protected $table      = 'setting';
	protected $primaryKey = "id";

	protected $fillable = ['id', 'org_id', 'aws_customer_id', 'personalityId', 'lexPersonalityID', 'lexUserID', 'user_id', 'last'];
	//--------------------------------------------------------------------
	protected function myQuery($orgID, $field, $value){
		if( $value=='' ){
			return $this
				->leftJoin('kamadeiep.organization_ep as ORG', 'setting.org_id', '=', 'ORG.organizationId')
				->leftJoin('kamadeikb.personality as PRS', 'setting.personalityId', '=', 'PRS.personalityId')
				->leftJoin('kamadeikb.personality as lxPRS', 'setting.lexPersonalityID', '=', 'lxPRS.personalityId')
				->leftJoin('kamadeiep.user as USR', 'setting.lexUserID', '=', 'USR.id')
				->leftJoin('kamadeiep.api_key_manager as apikeys', function($leftJoin){
					$leftJoin
						->on('setting.lexUserID', '=', 'apikeys.userID')
						->on('apikeys.portal_code', '=', \DB::raw("'2LEX!!'"));
				})
				->where(
					function($q) use($orgID){ 
						if($orgID==0){ return $q;}
						else{ return $q->where('org_id', $orgID); } 
					}
				)
				->select(
					'setting.*',
					'ORG.organizationShortName as organizationShortName',
					'PRS.personalityName as personalityName',
					'lxPRS.personalityName as lexPersonalityName',
					'USR.userName as lexUserName',
					'apikeys.api_key as apiKey'
				
				);
		}else{
			return $this
				->leftJoin('kamadeiep.organization_ep as ORG', 'setting.org_id', '=', 'ORG.organizationId')
				->leftJoin('kamadeikb.personality as PRS', 'setting.personalityId', '=', 'PRS.personalityId')
				->leftJoin('kamadeikb.personality as lxPRS', 'setting.lexPersonalityID', '=', 'lxPRS.personalityId')
				->leftJoin('kamadeiep.user as USR', 'setting.lexUserID', '=', 'USR.id')
				->leftJoin('kamadeiep.api_key_manager as apikeys', function($leftJoin){
					$leftJoin
						->on('setting.lexUserID', '=', 'apikeys.userID')
						->on('apikeys.portal_code', '=', \DB::raw("'2LEX!!'"));
				})
				->where(
					function($q) use($orgID){ 
						if($orgID==0){ return $q;}
						else{ return $q->where('org_id', $orgID); } 
					}
				)
				->where(
					function($q) use ($value){ 
						return $q
							->where('ORG.organizationShortName' , 'like', "%{$value}%")
							->orwhere('setting.aws_customer_id' , 'like', "%{$value}%")
							->orwhere('PRS.personalityName'     , 'like', "%{$value}%")
							->orwhere('lxPRS.personalityName'   , 'like', "%{$value}%")
							->orwhere('USR.userName'            , 'like', "%{$value}%");
					}
				)
				->select(
					'setting.*',
					'ORG.organizationShortName as organizationShortName',
					'PRS.personalityName as personalityName',
					'lxPRS.personalityName as lexPersonalityName',
					'USR.userName as lexUserName',
					'apikeys.api_key as apiKey'
				);
		}
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order, $field='', $value=''){
		$data = null;
		$data = $this->myQuery($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage);
		if($data->isEmpty()){ return null; }
		$retVal = [];
		foreach( $data as $key=>$tmp ){ $retVal[] = $tmp; }
		return $retVal;
	}
	//--------------------------------------------------------------------
	protected function orgList($orgID){
		return $this
			->leftJoin('kamadeiep.organization_ep as ORG', 'org_id', '=', 'ORG.organizationId')
			->where(
				function($q) use($orgID){ 
					if($orgID==0){ return $q;}
					else{ return $q->where('org_id', $orgID); } 
				}
			)
			->select(
				'setting.org_id',
				'setting.id as settingID',
//				'setting.personalityId',
				'ORG.organizationShortName as organizationShortName'
			)
			->groupBy('setting.org_id');
	}
	//--------------------------------------------------------------------
	protected function getSettingData($id){
		return self::where('setting.id', $id)->
			leftJoin('kamadeiep.organization_ep as ORG', 'setting.org_id', '=', 'ORG.organizationId')->
			leftJoin('kamadeikb.personality as PRS', 'setting.personalityId', '=', 'PRS.personalityId')->
			leftJoin('kamadeikb.personality as lxPRS', 'setting.lexPersonalityID', '=', 'lxPRS.personalityId')->
			leftJoin('kamadeiep.user as USR', 'setting.lexUserID', '=', 'USR.id')->
			select(
				'setting.*',
				'ORG.organizationShortName as organizationShortName',
				'PRS.personalityName as personaName',
				'lxPRS.personalityName as lexPersonalityName',
				'USR.userName as lexUserName'

			)->first();
	}
	//--------------------------------------------------------------------
}
