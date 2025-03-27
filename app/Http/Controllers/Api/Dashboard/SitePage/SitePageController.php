<?php

namespace App\Http\Controllers\Api\Dashboard\SitePage;

use Illuminate\Http\Request;
use App\SitePages;
use App\PageLevel;
use App\Controllers;
//use App\Http\Resources\SitePage as SitePageResource;

class SitePageController extends \App\Http\Controllers\Controller{
	//---------------------------------------
	public function show($orgID, $id){
		$data = SitePages::findPageByID($orgID, $id);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[]];
		}else{
			return ['result'=>0, 'msg'=>'', 'data'=>$data];
		}
	}
	public function search($orgID, $field, $value){
		$field = $this->validFieldName($field);
		if( $field=='' ){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//----------------------
		$data = SitePages::findPage($orgID, $field, $value);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
		}
		//----------------------
	}
	//---------------------------------------
	public function showAll( $orgID ){ return $this->showAllSorted($orgID, 'userId', 'asc'); }
	public function showAllSorted($orgID, $sort, $order){
		$sort = $this->validFieldName($sort);
		if( $sort=='' ){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//----------------------
		$data  = SitePages::all();
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			//------------------
			$order = strtolower($order);
			switch($order){
				case 'asc' :{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data->sortBy($sort)->values()->all()]; }
				case 'desc':{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data->sortByDesc($sort)->values()->all()]; }
				default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
			}
			//------------------
		}
	}
	//---------------------------------------
	public function showPage( $orgID, $perPage, $page){
		$data  = SitePages::myPageing($orgID, $perPage, $page, 'userId', 'asc');
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data];
		}
	}
	public function showPageSorted( $orgID, $sort, $order, $perPage, $page ){
		//----------------------
		$sort = $this->validFieldName($sort);
		if( $sort=='' ){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//----------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		//----------------------
		$count = SitePages::where('isAdmin', '=', 0)->count();
		//----------------------
		$data  = SitePages::myPageing($orgID, $perPage, $page, $sort, $order);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
		//----------------------
	}
	public function showPageSortSearch( $orgID, $sort, $order, $perPage, $page, $field, $value ){
		$sort = $this->validFieldName($sort);
		if( $sort=='' ){ return ['result'=>1, 'msg'=>'invalid sort FIELD', 'data'=>[], 'total'=>0]; }
		//----------------------
		$field = $this->validFieldName($field);
		if( $field=='' ){ return ['result'=>1, 'msg'=>'invalid field name', 'data'=>[], 'total'=>0]; }
		//----------------------
		$order = strtolower($order);
		switch($order){
			case 'asc' :
			case 'desc':{ break; }
			default:{ return ['result'=>1, 'msg'=>'invalid sort ORDER', 'data'=>[], 'total'=>0]; }
		}
		//----------------------
		if($orgID==0){ $count = SitePages::where($field, 'like', "%{$value}%")->count(); }
		else{ $count = SitePages::where($field, 'like', "%{$value}%")->whereIn('orgID',[$orgID,0])->count(); }
		
		$data  = SitePages::myPageingWithSearch($orgID, $perPage, $page, $sort, $order, $field, $value);
		if(is_null($data) ){
			return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0];
		}else{
			return ['result'=>0, 'msg'=>'', 'total'=>$count, 'data'=>$data];
		}
		//----------------------
	}
	//---------------------------------------
	private function validFieldName($fieldName){
		switch(strtolower($fieldName)){
			case 'pageid'    : { return "pageID";    }
			case 'levelid'   : { return "levelID";   }
			case 'last'      : { return "last";      }
			default:{ return ''; }
		}
	}
	//---------------------------------------
	public function createRow( $orgID, $pageid, $levelid){
		try{
			$ids = [$pageid];
			
			$parent =  SitePages::where('id', $pageid)->select("isChild")->first();
			if($parent!=null && $parent->isChild!=0){ $ids[] = $parent->isChild; }
			
			$childs =  SitePages::where('isChild', $pageid)->where('isAdmin', 0)->select("id")->get();
			if(!$childs->isEmpty()){ foreach($childs as $tmp){ $ids[] = $tmp->id; } }
			
			foreach($ids as $id){
				if(PageLevel::where('pageID', $id)->where('levelID', $levelid)->first()==null){
					$pageLevel = new PageLevel;
					$pageLevel->pageID  = $id;
					$pageLevel->levelID = $levelid;
					$pageLevel->last    = date("Y-m-d H:i:s");

					$tmp = $pageLevel->save();
				}
			}
			if($tmp){ return ['result'=>0, 'id'=>1]; }
			else{ return ['result'=>1, 'msg'=>'']; }
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function deleteRow( $orgID, $pageid, $levelid){
		try{
			$pageLevel = PageLevel::where('levelID', $levelid)->
									where( function($q) use($pageid){
										$ids = [$pageid];
										$childs =  SitePages::where('isChild', $pageid)->select("id")->get();
										if(!$childs->isEmpty()){ foreach($childs as $tmp){ $ids[] = $tmp->id; } }
										return $q->whereIn('pageID', $ids);
									})->
									get();
			if(is_null($pageLevel) ){
				return ['result'=>1, 'msg'=>"page not found"];
			}else{
				$tmp = PageLevel::where('levelID', $levelid)->
									where( function($q) use($pageid){
										$ids = [$pageid];
										$childs =  SitePages::where('isChild', $pageid)->select("id")->get();
										if(!$childs->isEmpty()){ foreach($childs as $tmp){ $ids[] = $tmp->id; } }
										return $q->whereIn('pageID', $ids);
									})->
									delete();
				return ['result'=>($tmp ?0 :1), 'msg'=>''];
			}
		}catch(ErrorException $ex){
			return ['result'=>1, 'msg'=>$ex->getMessage()];
		}
	}
	//---------------------------------------
	public function showAllMenuItems( $orgID, $sort, $order, $perPage, $page ){
		$data = [];
		//----------------------
		$sitePages = new SitePages();
		//----------------------
		$dataM = $sitePages->
					where( function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->whereIn('orgID', [$orgID, 0]);
					})->
					where('isChild', 0)->
					orderBy('isAdmin','asc')->
					orderBy('showOrder','asc')->
					get();
		//----------------------
		$dataN = [];
		if(!$dataM->isEmpty()){
			foreach($dataM as $tmp){
				$dataN[] = $tmp;
				$dataC = $sitePages->
							where( function($q) use($orgID){
								if($orgID==0){ return $q; }
								return $q->whereIn('orgID', [$orgID, 0]);
							})->
							where('isChild', $tmp->id)->
							orderBy('isAdmin','asc')->
							orderBy('showOrder','asc')->
							get();
				if(!$dataC->isEmpty()){ 
					foreach($dataC as $tmp1){ 
						$tmp1->pageCaption = "&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;".$tmp1->pageCaption;
						$dataN[] = $tmp1; 
					} 
				}
			}
		}
		//----------------------
		$makeData = function($inData){
			$retVal = [];
			if(count($inData)){
				$lvls   = \App\Level::orderBy('order')->get();
				foreach( $inData as $tmp ){
					$levelCol = [];
					for( $i=0; $i<count($lvls); $i++){
						if($tmp['isAdmin']==1){
							if( $lvls[$i]->order==0 ){ $levelCol[$lvls[$i]->order] = 1; }
							else{ $levelCol[$i] = 0; }
						}else{
							if( $lvls[$i]->order==0 ){ $levelCol[$i] = 1; }
							else{ 
								$lvlTmp = \App\PageLevel::where('pageID', $tmp['id'])->where('levelID', $lvls[$i]->id)->get();
								$levelCol[$i] = ($lvlTmp->isEmpty() ?0 :1 ); 
							}
						}
					}
					$tmp->levelCol = $levelCol; 
					$retVal[] = $tmp;
				}
			}
			return $retVal;
		};
		//----------------------
		$data = $makeData($dataN);
		//----------------------
		if(count($data)==0 ){ return ['result'=>1, 'msg'=>'record not found', 'data'=>[], 'total'=>0]; }
		else{ return ['result'=>0, 'msg'=>'', 'total'=>count($data), 'data'=>$data]; }
	}
	//---------------------------------------
}