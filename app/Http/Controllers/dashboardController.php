<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
class dashboardController extends Controller {
	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function __construct(){
		$this->middleware('guest');
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
	public function indexORIGINAL(Request $request, $menu='dashboard', $child=''){
		$isLogin = $request->session()->get('isLogin');
		if($isLogin==false ){ return redirect('/login'); }
		else{ return view('dashboard', ['session'=>$request->session(), 'requestMenu'=>$menu, 'requestChildMenu'=>$child]);  }
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------
//	public function index(Request $request, $menu='dashboard', $child=''){
	public function index(Request $request, $menu='dashboard', $child='',$table='',$parent=''){
		$isLogin = $request->session()->get('isLogin');
		$levelID = $request->session()->get('levelID');
		if($isLogin==false ){ return redirect('/login'); }
		else{
			$params = [
				'session'=>$request->session(), 
				'requestMenu'=>$menu, 
				'requestChildMenu'=>$child,
				'requestTableName'=>$table,
				'requestParentId'=>$parent
			];
			if($levelID==1){ return view('dashboard', $params); }
			else{
				if($menu=='dashboard' && $child==""){ return view('dashboard', $params); }
				else{
					$pageID = \App\SitePages::where('pageUrl', "/panel/{$menu}/{$child}")->
						orWhere('pageUrl', "/panel/{$menu}/{$child}/")->
						select('id')->first();
					if($pageID==null){
						$pageID = \App\SitePages::where('pageUrl', "/panel/{$menu}")->
							orWhere('pageUrl', "/panel/{$menu}/")->
							select('id')->first();
						if($pageID==null){
//							return redirect('/dashboard');
							return view('404Error', ['page'=>"/panel/{$menu}/", 'level'=>$levelID]);
						}else{
							if(\App\PageLevel::where('pageID',$pageID->id)->where('levelID',$levelID)->count()){
								return view('dashboard', $params);
							}else{
								//return redirect('/dashboard'); 
								return view('accessError', ['page'=>$pageID->id, 'level'=>$levelID]);
							}
						}
					}
					else{
						if(\App\PageLevel::where('pageID',$pageID->id)->where('levelID',$levelID)->count()){
							return view('dashboard', $params);
						}else{
							//return redirect('/dashboard'); 
							return view('accessError', ['page'=>$pageID->id, 'level'=>$levelID]);
						}
					}
				}
			}
		}
	}
	//-----------------------------------------------------------------------------------

	//-----------------------------------------------------------------------------------

//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
}
