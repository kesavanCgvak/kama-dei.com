<?php
namespace App\Http\Controllers\Api\Dashboard\Organization;
//-------------------------------------------
use Illuminate\Http\Request;
use App\Controllers;
//-------------------------------------------
class CredentialInformationController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function acquisition(Request $req){
		try{
			//-------------------------------
			$validator = \Validator::make(
					$req->all(),
					[
						'org' => 'required'
					],
					[
						'org.required'=> "organization ID is required"
					]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			$data = $req->all();
			//-------------------------------
			$org = \App\Organization::where('organizationId', $data['org'])->count();
			if($org==0){ throw new \Exception("Invalid organization ID"); }
			//-------------------------------
			$cerdential = \App\Cerdential::where('orgid', $data['org'])
				->leftJoin('source_type', 'cerdential.sourceTypeId', '=', 'source_type.id')
				->select(
					'cerdential.sourceTypeId',
					'source_type.sourceType'
				)
				->groupBy('sourceTypeId')
				->get();
			//-------------------------------
			$outData[$data['org']] = [];
			if(!$cerdential->isEmpty()){
				foreach($cerdential as $itm){
					$values = \App\Cerdential::where('orgid', $data['org'])->where('sourceTypeId', $itm->sourceTypeId)->get();
					if($values->isEmpty()){ $outData[$data['org']][$itm->sourceType] = []; }
					else{
						foreach($values as $value){
							$outData[$data['org']][$itm->sourceType][$value->attribute] = $value->Value;
						}
					}
				}
			}
			return ['result'=>1, 'msg'=>"OK", 'data'=>$outData];
			//-------------------------------
		}catch(\Throwable $ex){
			return ['result'=>0, 'msg'=>$ex->getMessage(), 'data'=>[]];
		}
	}
	//---------------------------------------
}