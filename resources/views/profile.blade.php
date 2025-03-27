<?php
//	$userID   = $session->get('userID'  );
	$clientID = $session->get('clientID');
	$levelID  = $session->get('levelID' );
	
	$row = \App\Client::where('id', '=', $clientID)->first();
	$clientName = $row->clientName;
	$row = \App\Level::where('id', '=', $levelID)->first();
	$levelName = $row->levelName;
?>
<head>
	<title>Dashboard</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/css/bootstrap.min.css') }}">
	<style>
		.container {
			margin-left: 0;
			transition: margin-left 0.5s;
		}
		.menu {
			background: white;
			min-height:100%;
		}
		
		.menu-header {
			background: #03A9F4;
			color: white;
			font-size: 20px;
			font-weight: bold;
			padding-left: 20px;
		}
		
		.menu-header > i {
			padding-right: 10px;
			float: left;
			height: 100%;
			line-height: 69px;
			opacity: 0.8;
		}
		
		.menu-header-title {
			float: left;
			line-height: 20px;
			margin-top: 15px;
		}
		
		.menu-header-title > div:first-child {
			font-size: 17px;
		}
		
		.menu-header-title > div:nth-child(2) {
			font-size: 12px;
			color: #dfe7ea;
		}
		.menu-header-title > div:nth-child(3) {
			font-size: 11px;
			color: #efd409;
			line-height:10px;
		}
		
		.content-header {
			background: #2196F3;
			background: linear-gradient(154deg, #008fe2 0, #00b29c 100%);
			color: white;
		}
		
		.menu-header, .content-header {
			height: 75px;
			line-height:70px;
		}
		
		.menu-body {
			padding-top: 20px;
		}
		
		.menu-body > ul {
			list-style: none;
			padding: 0;
		}
		
		.menu-item {
			position: relative;
			transition: all 0.33s;
		}
		
		.menu-item:hover {
			background: #f0f1f2;
		}
		
		.menu-item.selected:after {
			content: " ";
			position: absolute;
			top: 0;
			right: 0;
			border: 12px solid transparent;
			border-right-color: #f0f1f2;
			border-left: 0;
			margin-top: 8px;
		}
		
		.menu-item > a {
			display: block;
			padding: 10px 0 10px 20px;
			text-decoration: none;
		}
		
		.menu-item > a > i {
			color: #91d7f7;
			font-size: 18px;
			vertical-align: middle;
			width: 20px;
			text-align: center;
			margin-right: 15px;
		}
		
		.menu-item > a > span {
			color: #9e9e9e;
		}
		
		.content-header {
			padding-left: 20px;
			font-size: 20px;
		}
		
		.content-body {
			padding: 20px 5px;
		}
		
		.btn-menu-mob {
			display: none !important;
		}
		
		.btn-close-menu {
			display: none;
			position: absolute;
			right: 10px;
			top: 5px;
			cursor: pointer;
		}
		
		.content-header-icons {
			float: right;
			height: 100%;
			padding: 0;
			padding-right: 15px;
			list-style: none;
		}
		
		.content-header-icons > li {
			display: inline-block;
			text-align: center;
		}
		
		.content-header-icons > li > i {
			width:42px;
			border: 1px solid;
			padding: 10px;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.2);
			cursor: pointer;
			transition: all 0.33s;
		}
		
		.content-header-icons > li > i:hover {
			background: rgba(255, 255, 255, 0.4);
		}
		
		.content-header-title > i {
			padding-right: 10px;
			opacity: 0.8;
		}
		
		@media (max-width: 991px){
			.btn-close-menu {
				display: block;
			}
			
			.menu {
				position: fixed;
				z-index: 10000;
				max-width: 100%;
				width: 300px;
				left: -300px;
				transition: left 0.5s;
			}
			
			body.menu-open {
				overflow-x: hidden;
			}
			
			body.menu-open .menu {
				left: 0;
			}
			
			body.menu-open .container {
				margin-left: 300px;
			}
			
			.menu-item.selected:after {
				display: none;
			}
			
			.menu-item.selected {
				background: #f0f1f2;
			}
			
			.btn-menu-mob {
				display: inline-block !important;
			}
			
			body.menu-open .menu-bg {
				position: fixed;
				top: 0;
				bottom: 0;
				right: 0;
				left: 0;
				background: rgba(0, 0, 0, 0.5);
				z-index: 1;
			}
		}
		
		@media (max-width: 767px){
			.menu-header, .content-header {
				line-height: 75px;
			}
			
			.content-header {
				font-size: 16px;
			}
			
			.content-header-icons > li > i {
				width: 38px;
			}
		}
	</style>
	<script type="text/javascript" src="{{ asset('public/js/jquery.js') }}"></script>
	<script src="https://use.fontawesome.com/3d00a0f028.js"></script>
	
	<base href="/u/<?=$username;?>/">
</head>
<body>
	<div class="menu-bg"></div>
	<div class="container" style="width:100%">
		<div class="row">
			<div class="menu col-md-2">
				<div class="menu-header row">
					<div class="btn-close-menu"><i class="fa fa-times" aria-hidden="true"></i></div>
					<i class="fa fa-wrench" aria-hidden="true"></i>
					<div class="menu-header-title">
						<div><?=$clientName;?></div>
						<div><?=$username;?></div>
						<div><?=$levelName;?></div>
					</div>
				</div>
				<div class="menu-body row">
					<ul>
						<li class="menu-item <?=(($selectedMenu=='menu_1')?'selected' :'')?>">
							<a href=""><i class="fa fa-user" aria-hidden="true"></i><span>Profile</span></a>
						</li>
						<li class="menu-item <?=(($selectedMenu=='menu_2')?'selected' :'')?>">
							<a href="orders"><i class="fa fa-list-alt" aria-hidden="true"></i><span>Orders</span></a>
						</li>
						<li class="menu-item <?=(($selectedMenu=='menu_3')?'selected' :'')?>">
							<a href="favorites"><i class="fa fa-star" aria-hidden="true"></i><span>Favorites</span></a>
						</li>
					</ul>
				</div>
			</div>
			<div class="content col-md-10">
				<div class="content-header row">
					<?php
						$faIcon = 'fa-user';
						switch($selectedMenu){
							case 'menu_1':{ $faIcon = 'fa-user'; break; }
							case 'menu_2':{ $faIcon = 'fa-list-alt'; break; }
							case 'menu_3':{ $faIcon = 'fa-star'; break; }
						}
					?>
					<span class="content-header-title"><i class="fa <?=$faIcon;?>" aria-hidden="true"></i><span><?=$title?></span></span>
					<ul class="content-header-icons">
						<li><i class="fa fa-sign-out" title="Logout" aria-hidden="true" onClick="callLogout()"></i></li>
						<li><i class="btn-menu-mob fa fa-bars" aria-hidden="true"></i></li>
					</ul>
				</div>
				<div class="content-body">
					<div class="card">
						<div class="card-header"></div>
						<div class="card-detail" style="padding:15px;">
							<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
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
		function(){
			alert('Error');
		}
	);
}
</script>
