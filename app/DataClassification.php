<?php
namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
//-------------------------------------------------------------
class DataClassification extends Model {
	//---------------------------------------------------------
	public    $timestamps = false;
	protected $connection = 'mysql';
	protected $table      = 'data_classification';
	protected $primaryKey = "dataClassificationId";
	//---------------------------------------------------------
	protected function myData($orgID, $field='', $value=''){
		$tmpQry = $this
			->leftJoin('kamadeiep.level', 'data_classification.levelId', '=', 'level.id')
			->leftJoin('kamadeiep.organization_ep', 'data_classification.organizationId', '=', 'organization_ep.organizationId')
			->where(
				function($q) use($orgID){ 
					if($orgID==0){ return $q;}
					else{ return $q->where('data_classification.organizationId', $orgID); } 
				}
			);
		if( $value!='' ){
			$tmpQry->where(
					function($q) use ($value){ 
						return $q
							->where('data_classification.tableField' , 'like', "%{$value}%")
							->orwhere('organization_ep.organizationShortName' , 'like', "%{$value}%")
							->orwhere('level.levelName' , 'like', "%{$value}%");
					}
				);
		}
		return $tmpQry->select(
					'data_classification.*',
					'data_classification.organizationId as ownerId',
					'organization_ep.organizationShortName as organizationName',
					'level.levelName as levelName',
	                \DB::raw('substring(data_classification.tableField,1,(position("." in data_classification.tableField)-1)) as tableNames'),
	                \DB::raw('substring(data_classification.tableField,(position("." in data_classification.tableField)+1)) as fieldName')
				);
	}
	//---------------------------------------------------------
	protected function myPageing($orgID, $sort, $order, $perPage, $page, $field='', $value='', $tableName){
		//-----------------------------------------------------
		$data = null;
		if($tableName==-1){ $data = DataClassification::myData($orgID, $field, $value)->orderBy($sort, $order)->get()->forPage($page, $perPage); }
		else{ 
			$data = DataClassification::myData($orgID, $field, $value)
						->where('tableField', 'like', "{$tableName}.%")
						->orderBy($sort, $order)->get()->forPage($page, $perPage); 
		}
		//-----------------------------------------------------
		if($data->isEmpty()){ return null; }
		//-----------------------------------------------------
		$retVal = [];
		foreach( $data as $key=>$tmp ){ $retVal[] = $tmp; }
		return $retVal;
		//-----------------------------------------------------
	}
	//---------------------------------------------------------
}
//-------------------------------------------------------------
