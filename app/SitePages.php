<?php 
namespace App;

use Illuminate\Database\Eloquent\Model;

class SitePages extends Model {

	protected $table = 'site_page';

	protected $fillable = ['pageCaption', 'pageUrl', 'pageIcon', 'showOrder', 'isAdmin'];

	//--------------------------------------------------------------------
	public function nonAdminMenus($orgID, $levelID){
		//if($orgID==0 || $levelID==1){
		if($levelID==1){
			return $this
						->where('isAdmin', '=', 0)
						->where('isChild', '=', 0)
						->where(function($q) use($orgID){
							if($orgID==0){ return $q; }
							else{
								return $q
									->where('isGeneral', 1)
									->orWhere(function($q) use($orgID){
										return $q
											->where('isGeneral', 0)
											->whereIn('orgID',[$orgID,0]);
									});
							}
						})
						->orderBy('showOrder','asc')
						->get();
		}else{
			$pageIDs = \App\PageLevel::with(['level'])
										->where('levelID','=',0)
										->orwhere('levelID','=',$levelID)
										->get(['pageID'])->toArray();
			$menus = new class{};
			if($pageIDs!=false){
				$menuIDs = [];
				foreach($pageIDs as $pageID){ $menuIDs[]=$pageID['pageID']; }
				$menus = $this
							->whereIn('id', $menuIDs)
							->whereIn('orgID',[$orgID,0])
							->where('isAdmin', '=', 0)
							->where('isChild', '=', 0)
							->orwhere('isGeneral','=',1)
							->orderBy('showOrder','asc')
							->get();
			}
			return $menus;
		}
	}
	//--------------------------------------------------------------------
	protected function myPageing($orgID, $perPage, $page, $sort, $order){
		if($orgID==0){ $data = $this->where('isChild', '=', 0)->orderBy('isAdmin','asc')->orderBy('showOrder','asc')->get(); }
		else{ $data = $this->whereIn('orgID', [$orgID, 0])->where('isChild', '=', 0)->orderBy('isAdmin','asc')->orderBy('showOrder','asc')->get(); }
		if($data->isEmpty()){ return null; }
		$retVal = [];
		$lvls   = \App\Level::orderBy('order')->get();
		foreach( $data as $tmp ){
			$levelCol = [];
			for( $i=0; $i<count($lvls); $i++){
				if($tmp['isAdmin']==1){
					if( $lvls[$i]->order==0 ){ $levelCol[$lvls[$i]->order] = 1; }
					else{ $levelCol[$i] = 0; }
//					else{ $levelCol[$lvls[$i]->order] = 0; }
				}else{
					if( $lvls[$i]->order==0 ){ $levelCol[$i] = 1; }
					else{ 
						$lvlTmp = \App\PageLevel::where('pageID', '=', $tmp['id'])->where('levelID', '=', $lvls[$i]->id)->get();
						$levelCol[$i] = ($lvlTmp->isEmpty() ?0 :1 ); 
					}
/*
					if( $lvls[$i]->order==0 ){ $levelCol[$lvls[$i]->order] = 1; }
					else{ 
						$lvlTmp = \App\PageLevel::where('pageID', '=', $tmp['id'])->where('levelID', '=', $lvls[$i]->id)->get();
						$levelCol[$lvls[$i]->order] = ($lvlTmp->isEmpty() ?0 :1 ); 
					}
*/
				}
			}
			$tmp->levelCol = $levelCol; 
			$retVal[] = $tmp;
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
	public function getCaptionMenu( $url ){
		$tmp = $this->where('pageUrl', '=', $url)->first();
		if(is_null($tmp)){
		    return '/panel/extend/extendedlink';
		}
		return $tmp->pageCaption;
	}
	//--------------------------------------------------------------------
	public function getchildsMenu( $url ){
		$tmp = $this->where('pageUrl', '=', $url)->first();
		if(is_null($tmp)){ return []; }
		$tmp = $this->where('isChild', '=', $tmp->id)
					->where(function($q){ 
						if(session('orgID', -1)==0){ return $q; }
						return $q->whereIn('orgID',[session('orgID', -1),0]);
					})->get()->sortBy('showOrder');
		if(is_null($tmp)){ return []; }
		$retVal = [];
		foreach($tmp as $i){ 
			if(
				\App\PageLevel::where('pageID', $i->id)->where('levelID', session('levelID', 0))->first()!=null
				||
				session('orgID', -1)==0
			){ $retVal [] = ['caption'=>$i->pageCaption, 'url'=>$i->pageUrl]; }
		}
		return $retVal;
	}
	//--------------------------------------------------------------------
	public function getAdminRoots(){
		return $this
					->where('isAdmin', '=', 1)
					->where('isChild', '=', 0)
					->get()
					->sortBy('showOrder');
	}
	//--------------------------------------------------------------------
	public function getMenuRoots(){
		return $this
					->where('isAdmin', '=', 0)
					->where('isChild', '=', 0)
					->get()
					->sortBy('showOrder');
	}
	//--------------------------------------------------------------------
	public function haveChild($pageID){
		return $this
					->where('isChild', '=', $pageID)->count();
	}
	//--------------------------------------------------------------------
	public function getChild($pageID){
		return $this
				->where('isChild', '=', $pageID)
				->where(function($q){ 
					if(session('orgID', -1)==0){ return $q; }
					return $q->whereIn('orgID',[session('orgID', -1),0]);
				})
				->get()
				->sortBy('showOrder');
	}
	//--------------------------------------------------------------------
	public function showRowMenu($menu, $REQUEST_URI, $orgID, $isChild, $levelID){
		if($orgID!=0 && $isChild==0){ if($menu->orgID!=0 && $menu->orgID!=$orgID){ return; } }
		$selected = (($REQUEST_URI==$menu->pageUrl)?'selected' :'');
		$showIsChild = (($isChild==0) ?'' :'isChild'.$isChild);
		if($orgID!=0){
			$orgData  = \App\Organization::find($orgID);
			if( ($menu->id==\Config('kama_dei.static.KaaSRootPageID', 0) && $orgData->KaaS3PB!=1) &&  $orgID!=0 ){ return; }
		}
		if(
			\App\PageLevel::where('pageID', $menu->id)->where('levelID', $levelID)->first()==null
			&&
			$levelID!=1
		){ return; }
		echo
			"<li class='menu-item {$selected} {$showIsChild}'>".
				"<a href='javascript:gotoMenu(\"{$menu->pageUrl}\")'>".
					"<i class='{$menu->pageIcon}' aria-hidden='true'></i><span>{$menu->pageCaption}</span>".
				"</a>".
			"</li>";
		if($this->haveChild($menu->id)>0){
			$subMenus = $this->getChild($menu->id);
			$isChild++;
			if($subMenus!=false){
				foreach($subMenus as $subMenu){ $this->showRowMenu($subMenu, $REQUEST_URI, $orgID, $isChild, $levelID); } 
			}
		}
		return;
	}
	//--------------------------------------------------------------------
	public function allMenus($orgID, $levelID){
		if($levelID==1){
			return $this
					->where('isChild', 0)
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						else{
							return $q
								->where('isGeneral', 1)
								->orWhere(function($q) use($orgID){
									return $q
										->where('isGeneral', 0)
										->whereIn('orgID',[$orgID,0]);
								});
						}
					})
					->orderBy('showOrder','asc')
					->get();
		}else{
			$pageIDs = \App\PageLevel::with(['level'])
										->where('levelID', 0)
										->orwhere('levelID', $levelID)
										->get(['pageID'])->toArray();
			$menus = new class{};
			if($pageIDs!=false){
				$menuIDs = [];
				foreach($pageIDs as $pageID){ $menuIDs[]=$pageID['pageID']; }
				$menus = $this
							->whereIn('id', $menuIDs)
							->whereIn('orgID',[$orgID,0])
							->where('isAdmin', 0)
							->where('isChild', 0)
							->orwhere('isGeneral', 1)
							->orderBy('showOrder','asc')
							->get();
			}
			return $menus;
		}
	}
	//--------------------------------------------------------------------
}
