<?php
/**
 * Created by PhpStorm.
 * User: yaofeiliang
 * Date: 2018/9/1
 * Time: 下午5:53
 */

namespace App\Http\Controllers\Api\Extend\Extended_EAV;

use Illuminate\Http\Request;
use App\Models\Extend\Extended_EAV;
use App\Models\Extend\Extended_entity;
use App\Controllers;
class ExtendedEAVController extends \App\Http\Controllers\Controller{

    //显示---------------------------------------
    public function show($extendedEntityId, $extendedAttributeId, $orgID, $id){
        $data = Extended_EAV::findExtendedEAVByID($extendedEntityId, $extendedAttributeId, $orgID, $id);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[]      ];
        }else{
            return ['result'=>0, 'msg'=>''                , 'data'=>$data   ];
        }
    }

    //搜索---------------------------------------
    public function search($extendedEntityId, $extendedAttributeId, $orgID, $field, $value){
        //-----------------------------------------------------------------------------------------
        $field = $this->dataFields( $field );
        if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $data = Extended_EAV::findExtendedEAVvalueString($extendedEntityId, $extendedAttributeId, $orgID, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0  ];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data    ];
        }
    }

    //查找$orgID的显示所有+排序---------------------------------------
    public function showAll($extendedEntityId, $extendedAttributeId, $orgID ){ return $this->showAllSorted($extendedEntityId, $extendedAttributeId, $orgID, 'extendedEAVID', 'asc'); }
    public function showAllSorted($extendedEntityId, $extendedAttributeId, $orgID, $sort, $order){
        //-----------------------------------------------------------------------------------------
        $sort = $this->sortFields( $sort );
        if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $order = strtolower($order);
        switch($order){
            default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
            case 'asc' :
            case 'desc':{ break; }
        }
        //-----------------------------------------------------------------------------------------
        $data  = Extended_EAV::myExtendedEAVs($extendedEntityId, $extendedAttributeId, $orgID, '', '')->orderBy($sort, $order)->get();
        $total = Extended_EAV::myExtendedEAVs($extendedEntityId, $extendedAttributeId, $orgID, '', '')->count();
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$total, 'data'=>$data];
        }
    }

    //分页查询---------------------------------------
    public function showPage($extendedEntityId, $extendedAttributeId, $orgID, $perPage, $page){
        $data  = Extended_EAV::myPageing($extendedEntityId, $extendedAttributeId, $orgID, $perPage, $page, 'extendedEAVID', 'asc');
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
        }
    }

    //分页查询+排序---------------------------------------
    public function showPageSorted($extendedEntityId, $extendedAttributeId, $orgID, $sort, $order, $perPage, $page ){
        //-----------------------------------------------------------------------------------------
        $sort = $this->sortFields( $sort );
        if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $order = strtolower($order);
        switch($order){
            case 'asc' :
            case 'desc':{ break; }
            default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
        }
        $count = Extended_EAV::myExtendedEAVs($extendedEntityId, $extendedAttributeId, $orgID, '', '')->count();

        $data  = Extended_EAV::myPageing($extendedEntityId, $extendedAttributeId, $orgID, $perPage, $page, $sort, $order);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //分页查询+排序+搜索---------------------------------------
    public function showPageSortSearch($extendedEntityId, $extendedAttributeId, $orgID, $sort, $order, $perPage, $page, $field, $value ){
        //-----------------------------------------------------------------------------------------
        $sort = $this->sortFields( $sort );
        if($sort==''){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $field = $this->dataFields( $field );
        if($field==''){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
        //-----------------------------------------------------------------------------------------
        $order = strtolower($order);
        switch($order){
            case 'asc' :
            case 'desc':{ break; }
            default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
        }
        $count = Extended_EAV::myExtendedEAVs($extendedEntityId, $extendedAttributeId, $orgID, $field, $value)->count();

        $data  = Extended_EAV::myPageingWithSearch($extendedEntityId, $extendedAttributeId, $orgID, $perPage, $page, $sort, $order, $field, $value);
        if(is_null($data) ){
            return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
        }else{
            return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
        }
    }

    //排序字段转换---------------------------------------
    private function sortFields($sort){
        switch(strtolower($sort)){

            case 'extendedeavid'              : { $sort="extendedEAVID";    break; }
            case 'valuestring'            : { $sort="valueString";  break; }

            case 'valueblob'            : { $sort="valueBlob";  break; }
            case 'valuefloat'            : { $sort="valueFloat";  break; }
            case 'valuedate'            : { $sort="valueDate";  break; }

            case 'extendedentityid'              : { $sort="extendedEntityId";    break; }
            case 'extendedentityname'            : { $sort="extendedEntityName";  break; }

            case 'extendedattributeId'              : { $sort="extendedAttributeId";    break; }
            //case 'attributeid'              : { $sort="attributeId";    break; }
            case 'attributename'            : { $sort="attributeName";  break; }

            case 'ownership'                : { $sort="ownership";          break; }
            case 'ownerid'                  : { $sort="ownerId";            break; }
            case 'datecreated'              : { $sort="dateCreated";        break; }
            case 'dateupdated'              : { $sort="dateUpdated";        break; }
            case 'lastuserid'               : { $sort="lastUserId";         break; }
            case 'memo'                     : { $sort="memo";               break; }
            case 'ownershipcaption'         : { $sort="ownership";          break; }
            case 'organizationshortname'    : { $sort="ownerId";            break; }
            case 'reserved'                 : { $sort="reserved";  break; }
            default                         : { $sort = "";                        }
        }
        return $sort;
    }

    //数据字段转换---------------------------------------
    private function dataFields($field){
        switch(strtolower($field)){

            case 'extendedeavid'              : { $field="extendedEAVID";    break; }
            case 'valuestring'            : { $field="valueString";  break; }

            case 'valueblob'            : { $field="valueBlob";  break; }
            case 'valuefloat'            : { $field="valueFloat";  break; }
            case 'valuedate'            : { $field="valueDate";  break; }

            case 'extendedentityid'              : { $field="extendedEntityId";    break; }
            case 'extendedentityname'            : { $field="extendedEntityName";  break; }

            case 'extendedattributeId'              : { $field="extendedAttributeId";    break; }
            //case 'attributeid'              : { $field="attributeId";    break; }
            case 'attributename'            : { $field="attributeName";  break; }

            case 'ownership'                : { $field="ownership";         break; }
            case 'ownerid'                  : { $field="ownerId";           break; }
            case 'datecreated'              : { $field="dateCreated";       break; }
            case 'dateupdated'              : { $field="dateUpdated";       break; }
            case 'lastuserid'               : { $field="lastUserId";        break; }
            case 'memo'                     : { $field="memo";              break; }
            case 'reserved'                 : { $field="reserved";  break; }


            default                         : { $field = "";                       }
        }
        return $field;
    }

    //改---------------------------------------
    public function editRow(Request $request){
        $json2 = $request->input();
        $arrlength=count($json2);
         //Extended_EAV::deletebyextendedEntityId(trim($json2[0]['extendedEntityId']));
		$lang             = trim($json2[0]['lang']);
		$extendedEntityId = trim($json2[0]['extendedEntityId']);
		
		if($lang=='en'){
			foreach($json2 as $tmp){
				if(trim($tmp['valueString'])==''){ return ['result'=>1, 'msg'=>"{$tmp['displayName']} cannot be blank" ]; }
			}
		}else{
			foreach($json2 as $tmp){
				$cnt = Extended_EAV::where('extendedEntityId', $extendedEntityId)
						->where('extendedAttributeId', trim($tmp['attributeId']))
						->where('lang', 'en')
						->count();
				if($cnt==0){ return ['result'=>1, 'msg'=>"{$tmp['displayName']} [* Language:English *] cannot be blank" ]; }
				if(trim($tmp['valueString'])==''){
					Extended_EAV::where('extendedEntityId', $extendedEntityId)->where('lang', $lang)->delete();
					return ['result'=>0];
				}
			}
		}
		
        $extended_EAV_del = Extended_EAV::where('extendedEntityId', $extendedEntityId)->where('lang', $lang)->delete();
        for($item=0;$item<$arrlength;$item++){
            //============================
            $extended_EAV = new Extended_EAV;
            $extended_EAV->valueString       = trim($json2[$item]['valueString']);
			if(
				strtolower($json2[$item]['attributeTypeName'])=='url' ||
				strtolower($json2[$item]['attributeTypeName'])=='text-url'
			)
				{ $extended_EAV->valueString = str_replace('+', '%20', $extended_EAV->valueString); } 

            $extended_EAV->ownership           = 0;
            $extended_EAV->ownerId             = 0;
            $extended_EAV->dateCreated         = date("Y-m-d H:i:s");
            $extended_EAV->dateUpdated         = date("Y-m-d H:i:s");
            $extended_EAV->lastUserId          = 0;
            $extended_EAV->reserved            = 0;
            $extended_EAV->memo                = '';
            $extended_EAV->lang                = $lang;
            $extended_EAV->extendedEntityId    = trim($json2[$item]['extendedEntityId']);
            $extended_EAV->extendedAttributeId = trim( $json2[$item]['attributeId']);
			
            if(trim($json2[$item]['notNullFlag'])==1){
                if($extended_EAV->valueString=='' && $lang=='en'){
                    return ['result'=>1, 'msg'=>'extendedEAV '.trim( $json2[$item]['attributeName']).' valueString is empty'];
                }
            }
            $tmp = $extended_EAV->save();

            //============================
        }
		$review_by = NULL;
		if(isset($json2[0]['review_by'])){
			$review_by = trim( $json2[0]['review_by']);
			if($review_by==''){ $review_by = NULL; }
		}
		Extended_entity::where('extendedEntityId', $extendedEntityId)->update(['review_by'=>$review_by]);

		if(isset($json2[0]['certify']) && $json2[0]['certify']==1){
			$notes = ((isset($json2[0]['notes'])) ?$json2[0]['notes'] : '');
			\App\Models\Extend\Responsiblity::insert([
				"userid"           => $json2[0]['userid'],
				"extendedEntityId" => $extendedEntityId,
				"note"             => $notes,
				"created_on"       => date("Y-m-d H:i:s")
			]);
		}
        return ['result'=>0];
    }

    //增---------------------------------------
    public function insertRow($orgID, Request $request){
        try{
            $extended_EAV = new Extended_EAV;
            $extended_EAV->valueString = trim($request->input('valueString'));
			if(strtolower($request->input('attributeTypeName'))=='url')
				{ $extended_EAV->valueString = str_replace('+', '%20', $extended_EAV->valueString); }
			
            $extended_EAV->ownership   = trim($request->input('ownership'));
            $extended_EAV->ownerId     = (($orgID==0)? trim($request->input('ownerId')) :$orgID);
            $extended_EAV->dateCreated = date("Y-m-d H:i:s");
            $extended_EAV->dateUpdated = date("Y-m-d H:i:s");
            $extended_EAV->lastUserId  = trim($request->input('userID'));
            $extended_EAV->reserved    = 0;
            $extended_EAV->memo        = trim($request->input('memo'));


            $extended_EAV->extendedEntityId    = trim($request->input('extendedEntityId'));
            $extended_EAV->extendedAttributeId = trim($request->input('extendedAttributeId'));



            if($extended_EAV->valueString ==''){ return ['result'=>1, 'msg'=>'extendedEAV valueString is empty'];  }
            if($extended_EAV->ownership   ==''){ return ['result'=>1, 'msg'=>'extendedEAV ownership is empty'];    }
            if($extended_EAV->ownerId     ==''){ return ['result'=>1, 'msg'=>'extendedEAV owner ID is empty'];     }
            if($extended_EAV->lastUserId  ==''){ return ['result'=>1, 'msg'=>'extendedEAV last user id is empty']; }


            if($extended_EAV->extendedEntityId    ==''){ return ['result'=>1, 'msg'=>'extendedEAV extendedEntityId is empty'];    }
            if($extended_EAV->extendedAttributeId ==''){ return ['result'=>1, 'msg'=>'extendedEAV extendedAttributeId is empty']; }


            $tmp = $extended_EAV->save();
            if($tmp){ return ['result'=>0, 'extendedEAVID'=>$extended_EAV->extendedEAVID]; }
            else{ return ['result'=>1, 'msg'=>'']; }
        }catch(ErrorException $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //删---------------------------------------
    public function deleteRow($orgID, $id, $lang){
        try{
			$extended_entity = Extended_entity::find($id);
			if($extended_entity==null)
				{ return ['result'=>1, 'msg'=>"Record not found. [Extended_entity record]"]; }
			
			if($extended_entity->ownerId!=$orgID && $orgID!=0)
				{ return ['result'=>1, 'msg'=>"You can't delete this record"]; }
			
			if($lang=='all'){
				Extended_EAV::where('extendedEntityId', $id)->delete();
				return ['result'=>0, 'msg'=>''];
			}

			if($lang!='en'){
				Extended_EAV::where('extendedEntityId', $id)->where('lang', $lang)->delete();
				return ['result'=>0, 'msg'=>''];
			}
			
			if(Extended_EAV::where('extendedEntityId', $id)->where('lang', '<>', $lang)->count()!=0)
				{ return ['result'=>1, 'msg'=>"English extended data records cannot be deleted as long as this record has other languages"]; }
			
			Extended_EAV::where('extendedEntityId', $id)->where('lang', $lang)->delete();
			Extended_entity::where('extendedEntityId', $id)->delete();
			return ['result'=>0, 'msg'=>''];
/*
			if($lang=='en'){ return ['result'=>1, 'msg'=>"English extended data records cannot be deleted"]; }
			$extended_EAV_del = Extended_EAV::where('extendedEntityId', $extendedEntityId)->where('lang', $lang)->count();
            if($extended_EAV_del==0){
                return ['result'=>1, 'msg'=>"extendedEAV not found"];
            }else{
				$extended_EAV = Extended_EAV::where('extendedEntityId', $extendedEntityId)->where('lang', $lang)->first();
                if($extended_EAV->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this extendedEAV"]; }
				Extended_EAV::where('extendedEntityId', $extendedEntityId)->where('lang', $lang)->delete();
				return ['result'=>0, 'msg'=>''];
			}
*/
			/*
            $extended_EAV = Extended_EAV::find($id);
            if(is_null($extended_EAV) ){
                return ['result'=>1, 'msg'=>"extendedEAV not found"];
            }else{
                if($extended_EAV->ownerId!=$orgID && $orgID!=0){ return ['result'=>1, 'msg'=>"You can't delete this extendedEAV"]; }
                $tmp = $extended_EAV->delete($id);
                return ['result'=>($tmp ?0 :1), 'msg'=>''];
            }
			*/
        }catch(\Throwable $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }

    //---------------------------------------
	public function draft(Request $request){
		try{
			//---------------------------------------------------------------------------
			$validator = \Validator::make(
					$request->all(),
					[
						'extendedEntityId' => 'required',
						'lang'             => 'required',
						'inquiry'          => 'required',
						'selections'       => 'required',
						'urlText'          => '',
						'result'           => 'required'
						//'notes'            => '',
					],
					[]
			);
			if($validator->fails()){
				$errors = $validator->errors();
				throw new \Exception($errors->first());
			}
			//---------------------------------------------------------------------------
			$data = $request->all();
			//---------------------------------------------------------------------------
			$draft = \App\Models\Extend\Draft::where('extendedEntityId', $data['extendedEntityId'])->where('lang', $data['lang'])->first();
			if($draft==null){
				$data['certifiedby' ] = \Session('userID');
				$data['createdon'   ] = date("Y-m-d");
				\App\Models\Extend\Draft::insert($data);
			}else{
				if(!isset($data['urlText'])){ $data['urlText']=null; }
				if(!isset($data['notes'  ])){ $data['notes'  ]=null; }
				\App\Models\Extend\Draft::where('id_draft', $draft->id_draft)->update($data);
			}
			//---------------------------------------------------------------------------
			return ['result'=>0, 'msg'=>"ok"];
        }catch(\Throwable $ex){
            return ['result'=>1, 'msg'=>$ex->getMessage()];
        }
    }
    //---------------------------------------
}