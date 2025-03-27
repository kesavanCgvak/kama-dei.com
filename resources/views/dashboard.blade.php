<?php
	$REQUEST_URI = $_SERVER['REQUEST_URI'];
	if(strpos($REQUEST_URI,'/panel/extend/extendedlink/')!== false){
		$REQUEST_URI='/panel/extend/extendedlink';
        $REQUEST_URI_temp='/panel/extend/extendedlink';
	}else{
		if(strpos($REQUEST_URI,'/panel/kb/link_kr_to_term/')!== false){
			$REQUEST_URI='/panel/kb/link_kr_to_term';
			$REQUEST_URI_temp='/panel/kb/link_kr_to_term';
		}else{
			$REQUEST_URI_temp=$REQUEST_URI;
			if($requestChildMenu=='relation_link' && $requestTableName!=''){ $REQUEST_URI_temp = '/panel/kb/relation_link'; }
			if($requestMenu=="lexjoint" && $requestChildMenu=='mapping' && $requestTableName!='')
				{ $REQUEST_URI_temp = '/panel/lexjoint/mapping/map'; }
			if($requestMenu=="kaasmapping" && $requestChildMenu=='mapping' && $requestTableName!='')
				{ $REQUEST_URI_temp = '/panel/kaasmapping/mapping/map'; }

			if($requestMenu=="live_agent" && $requestChildMenu=='mapping' && $requestTableName=='p' )
				{ $REQUEST_URI_temp = '/panel/live_agent/mapping'; }
			if($requestMenu=="live_agent" && $requestChildMenu=='mapping' && ( $requestTableName!='' && $requestTableName!='p' ))
				{ $REQUEST_URI_temp = '/panel/live_agent/mapping/map'; }

			if($requestMenu=="rpa" && $requestChildMenu=='mapping' && ( $requestTableName!='' && $requestTableName!='p' ))
				{ $REQUEST_URI_temp = '/panel/rpa/mapping/map'; }
		}
	}
	if(substr($REQUEST_URI, -1,1)=='/'){ $REQUEST_URI = substr($REQUEST_URI_temp,0,strlen($REQUEST_URI_temp)-1); }
	$isAdmin  = $session->get('isAdmin' );
	$orgID    = $session->get('orgID'   );
	$levelID  = $session->get('levelID' );
	$userName = $session->get('userName');

	if($orgID!=0){
		$orgKaaS3PB = \App\Organization::find($orgID)['KaaS3PB'];
		if($orgKaaS3PB==null){ $orgKaaS3PB=0; }
	}else{ $orgKaaS3PB=1; }
?>
<head>
	<title>Admin Panel</title>
	<meta http-equiv="refresh" content="7201">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="0">

	<link rel="icon" href="{{ asset('public/dist/images/kama-favicon.jpg') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/css/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/dashboard/css.css') }}">
	<script type="text/javascript" src="{{ asset('public/js/jquery.js') }}"></script>

	<script src="https://use.fontawesome.com/3d00a0f028.js"></script>

	<style>
		.table>tbody>tr>td:last-child,
		.table>tbody>tr>th:last-child,
		.table>tfoot>tr>td:last-child,
		.table>tfoot>tr>th:last-child,
		.table>thead>tr>td:last-child,
		.table>thead>tr>th:last-child{
			position: sticky;
			right: 0;
			background: aliceblue;
			width: 30px !important;
			font-size: 70% !important;
			text-align: center !important;
			vertical-align: middle !important;
			padding: 0 !important;
		}
	</style>
</head>
<body>
	<div class="menu-bg"></div>
	<div class="container" style="width:100%">
		<div class="row">
			<div class="menu col-md-2">
				<div class="menu-body-bg"></div>
				<div class="menu-header row">
					<div class="btn-close-menu"><i class="fa fa-times" aria-hidden="true"></i></div>
					<i class="fa fa-wrench" aria-hidden="true"></i>
					<div class="menu-header-title" style="float:none">
						<div>Dashboard</div>
						<div>
							<?php
							$tmp = new \App\Level();
							$level = $tmp->getName($levelID);
							$tmp = new \App\Organization();
							$organization = $tmp->getName($orgID);
							$organization = ($organization=='') ?'Site' :$organization;
							?>
							<small>
								<i style="text-decoration: underline;color: yellow;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;display: block;">
									<?=$userName;?>
								</i>
							</small>
							<div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
								[<?=$organization;?>] <?=$level;?>
							</div>
						</div>
					</div>
				</div>
				<div class="menu-body row">
					<ul>
						<li class="menu-item <?=(($REQUEST_URI=='/dashboard')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/dashboard')"><i class="fa fa-dashboard" aria-hidden="true"></i><span>Dashboard</span></a>
						</li>
						<li class="menu-item <?=(($REQUEST_URI=='/collection')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/collection')"><i class="fa fa-bitbucket" aria-hidden="true"></i>
                            </i><span>Collections</span></a>
						</li>
                        <li class="menu-item <?=(($REQUEST_URI=='/collection/logs')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/collection/logs')"><i class="fa fa-file-text-o" aria-hidden="true"></i>
                            </i><span>Collection Logs</span></a>
						</li>
						<?php
							$tmpPages = new \App\SitePages();
							$pageIcon = 'fa fa-angle-double-down';

							$pageCaption = $tmpPages->getCaptionMenu($REQUEST_URI_temp);
							$pageCaption = (($pageCaption=='') ?'Dashboard' :$pageCaption);
							if($REQUEST_URI == '/panel/dashboard') {
								$pageCaption = 'Dashboard';
								$pageIcon = 'fa fa-dashboard';
							}
							if($REQUEST_URI == '/panel/collection') {
								$pageCaption = 'Collections';
								$pageIcon = 'fa fa-bitbucket';
							}
                            if($REQUEST_URI == '/panel/collection/logs') {
								$pageCaption = 'Collection Logs';
								$pageIcon = 'fa fa-file-text-o';
							}
							if($REQUEST_URI == '/panel/settings') {
								$pageCaption = 'Settings';
								$pageIcon = 'fa fa-cog';
							}
							if(strpos($REQUEST_URI, '/panel/billing') !==false ) {
								$pageCaption = 'KAMA-DEI BILLING';
								$pageIcon = 'fa fa-money';
								$REQUEST_URI='/panel/billing/';
								$REQUEST_URI_temp='/panel/billing/';
							}
							if(strpos($REQUEST_URI, '/panel/testing') !==false ) {
								$pageCaption = 'Testing';
								$pageIcon = 'fa fa-server';
							}
							if(	strpos($REQUEST_URI, '/panel/testing')!==false ) {
								$pageCaption = str_replace("/", " / ", str_replace("/panel/", "", $REQUEST_URI));
								$pageIcon = 'fa fa-server';
							}
//							$pageCaption = 'Dashboard';'Settings'
//							$pageIcon    = 'fa fa-dashboard';
/*
							if($isAdmin){
								$rootMenus = $tmpPages->getAdminRoots();
								if($rootMenus!=false){
									foreach($rootMenus as $menu){ $tmpPages->showRowMenu($menu, $REQUEST_URI, $orgID, 0); }
								}
							}
							$rootMenus = $tmpPages->nonAdminMenus($orgID, $levelID);
							if($rootMenus!=false){
								foreach($rootMenus as $menu){ $tmpPages->showRowMenu($menu, $REQUEST_URI, $orgID, 0); }
							}
*/
							$rootMenus = $tmpPages->allMenus($orgID, $levelID);
							if($rootMenus!=false){
								foreach($rootMenus as $menu){
									$tmpPages->showRowMenu($menu, $REQUEST_URI, $orgID, 0, $levelID);
								}
							}
						?>
						<?php if($levelID==1 && $orgID==0): ?>
						<li class="menu-item <?=(($REQUEST_URI=='/billing')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/billing')"><i class="fa fa-money" aria-hidden="true"></i>
								<span>KAMA-DEI BILLING</span>
							</a>
						</li>
						<li class="menu-item <?=(($REQUEST_URI=='/testing')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/testing')"><i class="fa fa-server" aria-hidden="true"></i><span>Testing</span></a>
						</li>
						<li class="menu-item isChild1<?=(($REQUEST_URI=='/testing/api')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/testing/api')"><i class="fa fa-chevron-right" aria-hidden="true"></i><span>APIs</span></a>
						</li>
						<li class="menu-item isChild1<?=(($REQUEST_URI=='/testing/nlu')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/testing/nlu')"><i class="fa fa-chevron-right" aria-hidden="true"></i><span>NLU</span></a>
						</li>
<?php /*
						<li class="menu-item isChild1<?=(($REQUEST_URI=='/testing/kaas')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/testing/kaas-kama')"><i class="fa fa-chevron-right" aria-hidden="true"></i><span>KaaS [Kama-DEI]</span></a>
						</li>
*/ ?>
						<li class="menu-item isChild1<?=(($REQUEST_URI=='/testing/kaas')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/testing/kaas')"><i class="fa fa-chevron-right" aria-hidden="true"></i><span>KaaS</span></a>
						</li>
						<li class="menu-item isChild1<?=(($REQUEST_URI=='/testing/id_reg')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/testing/id_reg')"><i class="fa fa-chevron-right" aria-hidden="true"></i><span>Identification/Registration</span></a>
						</li>
						<?php endif; ?>
						<?php if($levelID==1 && $orgID!=0): ?>
						<li class="menu-item <?=(($REQUEST_URI=='/testing')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/testing')"><i class="fa fa-server" aria-hidden="true"></i><span>Testing</span></a>
						</li>
						<li class="menu-item isChild1<?=(($REQUEST_URI=='/testing/nlu')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/testing/nlu')"><i class="fa fa-chevron-right" aria-hidden="true"></i><span>NLU</span></a>
						</li>
						<?php if($orgKaaS3PB==1): ?>
<?php /*
						<li class="menu-item isChild1<?=(($REQUEST_URI=='/testing/kaas')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/testing/kaas-kama')"><i class="fa fa-chevron-right" aria-hidden="true"></i><span>KaaS [Kama-DEI]</span></a>
						</li>
*/ ?>
						<li class="menu-item isChild1<?=(($REQUEST_URI=='/testing/kaas')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/panel/testing/kaas')"><i class="fa fa-chevron-right" aria-hidden="true"></i><span>KaaS</span></a>
						</li>
                        <li class="menu-item <?=(($REQUEST_URI=='/dashboard')?'selected' :'')?>">
							<a href="javascript:gotoMenu('/dashboard')"><i class="fa fa-dashboard" aria-hidden="true"></i><span>Dashboard</span></a>
						</li>
						<?php endif; ?>
						<?php endif; ?>
					</ul>
				</div>
			</div>
			<div class="col-md-2"></div>
			<div class="content col-md-10 col-xs-12">
				<div class="content-header row">
					<span class="content-header-title"><i class="<?=$pageIcon;?>" aria-hidden="true"></i><span><?=$pageCaption?></span></span>
					<ul class="content-header-icons">
						<li id='user-menu-icon'>
							<i class="fa fa-user" aria-hidden="true"></i>
							<ul id='user-menu'>
								<li>
									<div style='margin: 10px 5px'>
										<div><b style='color: gray'><?=$userName;?></b></div>
										<small><?=\App\User::where('id', $session->get('userID'))->first()->email;?></small>
									</div>
								</li>
								<li>
									<a href='/panel/settings'>
										<i class="fa fa-cog"></i>
										Account Settings
									</a>
								</li>
								<li>
									<a href='#' onClick="event.preventDefault(); callLogout();">
										<i class="fa fa-sign-out"></i>
										Logout
									</a>
								</li>
							</ul>
						</li>
						<li><i class="btn-menu-mob fa fa-bars" aria-hidden="true"></i></li>
					</ul>
				</div>
				<div class="content-body">
					<div class="card">
						<div class="card-header"></div>
						<div class="card-detail" style="padding:15px;">
							<?php
							$emptyPage = '<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
							try{
								if($requestMenu==''){ echo $emptyPage; }
								else{
									$url = $REQUEST_URI_temp;
									$url = substr($url, 1);
									if($requestChildMenu==''){
										if(file_exists("{$url}/index.php")){ include("{$url}/index.php"); }
										else{ throw new Exception("");/* echo $emptyPage;*/ }
									}else{
										if(file_exists("{$url}/index.php")){ include("{$url}/index.php"); }
										else{ throw new Exception(""); }
									}
								}
							}catch(Exception $ex){
								$newTmpPg = $REQUEST_URI."/index.php";
								if(file_exists($newTmpPg)){ include($newTmpPg); }
								else{ echo $emptyPage; }
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
<script type="text/javascript">
	//---------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------
	$(document).ready(function(){
		$(".btn-menu-mob, .btn-close-menu, .menu-bg").click(function(){
			$body = $("body");
			if($body.hasClass("menu-open")){
				$body.removeClass("menu-open");
			}else{
				$body.addClass("menu-open");
			}
		})
	});
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
function callLogout(){
	var data = {};
	data._token = '{{ csrf_token() }}';

	$.post('/login/out',data,
		function(retVal){
			if(retVal.trim()==1){ window.location='/'; }
			else{ alert("invalid logout"); }
		}
	).fail(
		function(){ window.location.reload(); }
	);
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
function gotoMenu( menu ){
	window.location = menu;
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
$(document).ready(function(){
	$('body').click(function(e){
		var el = e.target;
		if(el.id != 'user-menu-icon' && !$.contains($('#user-menu-icon').get()[0], el)){
			$('#user-menu').removeClass('open');
		}
	});

	$('#user-menu-icon').click(function(e){
		var el = e.target;
		if(el.id != 'user-menu' && !$.contains($('#user-menu').get()[0], el)){
			if($('#user-menu').hasClass('open')){
				$('#user-menu').removeClass('open');
			}else{
				$('#user-menu').addClass('open');
			}
		}
	})
})
</script>
