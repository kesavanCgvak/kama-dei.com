<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/9/2
 * Time: 下午4:06
 */

namespace App\Http\Controllers\Api\Extend\Extended_Data_View;

use Illuminate\Http\Request;
use App\Models\Extend\Extended_Data_View;
use App\Controllers;
class ExtendedDataViewController extends \App\Http\Controllers\Controller{

    //显示---------------------------------------
    public function showAll($extendedEntityId, $lang='en'){
        $data = Extended_Data_View::myPageing($extendedEntityId, $lang);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'responsiblity'=>null, 'RAG'=>0];
        }else{
			$tmp = \App\Models\Extend\Extended_entity::where('extended_entity.extendedEntityId', $extendedEntityId)->first();
			if($tmp->ownerId==null || $tmp->ownerId==0){
				$RAG = 1;
				$data[0]->ownerId = 0;
				$data[0]->ownership = 0;
				//$data[0]->organizationShortName = env('');
			}else{
				$ragT = \App\Organization::find($tmp->ownerId);
				$RAG = $ragT->RAG;
				$data[0]->ownerId   = $tmp->ownerId;
				$data[0]->ownership = $tmp->ownership;
				$data[0]->organizationShortName = $ragT->organizationShortName;
				
			}
/*
			->leftJoin('kamadeiep.organization_ep', 'extended_entity.ownerId', '=', 'kamadeiep.organization_ep.organizationId')
				->first();
			$RAG = $tmp->RAG;
			$oID = $tmp->organizationId;
*/
			$responsiblity = \App\Models\Extend\Responsiblity::where('extendedEntityId', $extendedEntityId)
				->orderBy('created_on', 'desc')
				->first();
            return ['result'=>0, 'msg'=>'', 'data'=>$data, 'responsiblity'=>$responsiblity, 'RAG'=>$RAG];
        }
    }
	//------------------------------------------
    public function allNotes($extendedEntityId){
		try{
			$responsiblity = \App\Models\Extend\Responsiblity::where('extendedEntityId', $extendedEntityId)
				->leftJoin('kamadeiep.user', 'responsiblity.userid', '=', 'kamadeiep.user.id')
				->select(
					"responsiblity.*",
					"kamadeiep.user.email"
				)
				->orderBy('created_on', 'asc')
				->get();
			
			return ['result'=>0, 'msg'=>'', 'data'=>$responsiblity];
		}catch(\Throwable $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//------------------------------------------
}
//----------------------------------------------
