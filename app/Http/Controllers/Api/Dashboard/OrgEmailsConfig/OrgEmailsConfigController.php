<?php
namespace App\Http\Controllers\Api\Dashboard\OrgEmailsConfig;
//-------------------------------------------
use Illuminate\Http\Request;
use App\Controllers;
//-------------------------------------------
class OrgEmailsConfigController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function viewTable($orgID, $sort, $order, $perpage, $page, $ownerId, $field='', $value=''){
		$count = \App\Portal::myPortal($orgID, $ownerId, $field, $value)->where('prtType.caption', 'facebook')->count();
		$data  = \App\Portal::myPortal($orgID, $ownerId, $field, $value)
					->where('prtType.caption', 'facebook')
					->orderBy($sort, $order)
					->forPage($page, $perpage);
		return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data->get()];
	}
	//---------------------------------------
	public function editRow($orgID, $portalID, Request $req){
		try{
			$data = $req->all();
			if($data['send_format']==0){ return ['result'=>1, 'msg'=>'Please select format']; }
			if($data['emails']==''){ return ['result'=>1, 'msg'=>'Please enter emails']; }
			if($data['subject']==''){ return ['result'=>1, 'msg'=>'Please enter subject']; }
			$config = \App\logEmailsConfig::where('portal_id', $portalID)->first();
			if($config==null){
				\App\logEmailsConfig::insert([
					'portal_id'   => $portalID,
					'emails'      => $data['emails'],
					'body'        => $data['body'],
					'subject'     => $data['subject'],
					'send_format' => $data['send_format'],
					'last_update' => date("Y-m-d H:i:s")
				]);
			}else{
				\App\logEmailsConfig::where('id', $config->id)
					->update([
						'portal_id'   => $portalID,
						'emails'      => $data['emails'],
						'body'        => $data['body'],
						'subject'     => $data['subject'],
						'send_format' => $data['send_format'],
						'last_update' => date("Y-m-d H:i:s")
					]);
			}
			return ['result'=>0, 'msg'=>'Saved successful'];
		}catch(\Throwable $ex){ return ['result'=>1, 'msg'=>$ex->getMessage()]; }
		return $req;
	}
}
//-------------------------------------------
