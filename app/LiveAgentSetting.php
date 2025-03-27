<?php 
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LiveAgentSetting extends Model {
	//--------------------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysqlliveagent';
	protected $table      = 'setting';
	protected $primaryKey = "id";
	//--------------------------------------------------------------------
	protected function myQuery($orgID, $field, $value){
		if( $value=='' ){
			return $this
				->leftJoin('kamadeiep.organization_ep as ORG', 'setting.org_id', '=', 'ORG.organizationId')
				->leftJoin('kamadeiep.portal as portal', 'setting.portal_id', '=', 'portal.id')
				->leftJoin('kamadeikb.personality as personality', 'portal.unknownPersonalityId', '=', 'personality.personalityId')
				->where(
					function($q) use($orgID){ 
						if($orgID==0){ return $q;}
						else{ return $q->where('org_id', $orgID); } 
					}
				)
				->select(
					'setting.*',
					'ORG.organizationShortName as organizationShortName',
					'portal.name as portalName',
					'portal.code as portalCode',
					'personality.personalityName as personalityName'
				);
		}else{
			return $this
				->leftJoin('kamadeiep.organization_ep as ORG', 'setting.org_id', '=', 'ORG.organizationId')
				->leftJoin('kamadeiep.portal as portal', 'setting.portal_id', '=', 'portal.id')
				->leftJoin('kamadeikb.personality as personality', 'portal.unknownPersonalityId', '=', 'personality.personalityId')
				->where(
					function($q) use($orgID){ 
						if($orgID==0){ return $q;}
						else{ return $q->where('org_id', $orgID); } 
					}
				)
				->where(
					function($q) use ($value){ 
						return $q
							->where('ORG.organizationShortName'    , 'like', "%{$value}%")
							->orwhere('portal.name'                , 'like', "%{$value}%")
							->orwhere('portal.code'                , 'like', "%{$value}%")
							->orwhere('personality.personalityName', 'like', "%{$value}%");
					}
				)
				->select(
					'setting.*',
					'ORG.organizationShortName as organizationShortName',
					'portal.name as portalName',
					'portal.code as portalCode',
					'personality.personalityName as personalityName'
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
				'setting.portal_id',
				'ORG.organizationShortName as organizationShortName'
			)
			->groupBy('setting.org_id');
	}
	//--------------------------------------------------------------------
	protected function getSettingData($id){
		return self::where('setting.id', $id)->
			leftJoin('kamadeiep.organization_ep as ORG', 'setting.org_id', '=', 'ORG.organizationId')->
			leftJoin('kamadeikb.personality as PRS', 'setting.personalityId', '=', 'PRS.personalityId')->
			leftJoin('kamadeikb.personality as kaasPRS', 'setting.kaasPersonalityID', '=', 'kaasPRS.personalityId')->
			leftJoin('kamadeiep.user as USR', 'setting.kaasUserID', '=', 'USR.id')->
			select(
				'setting.*',
				'ORG.organizationShortName as organizationShortName',
				'PRS.personalityName as personaName',
				'kaasPRS.personalityName as kaasPersonalityName',
				'USR.userName as kaasUserName'

			)->first();
	}
	//--------------------------------------------------------------------
	protected function getPortalData($id){
		return \App\Portal::where('portal.id', $id)->
			leftJoin('kamadeikb.personality as PRS', 'kamadeiep.portal.unknownPersonalityId', '=', 'PRS.personalityId')->
			select(
				'PRS.personalityName as personaName'
			)->first();
	}
	//--------------------------------------------------------------------
}
