<style>
/*div.card-detail{ height:80vh; }*/
	#LIVEAGENT_Mapping table th,
	#LIVEAGENT_Mapping table td
		{font-size: 12px; }
	#LIVEAGENT_Mapping td:last-child,#LIVEAGENT_Bots th:last-child{ width:30px !important;font-size:12px; }
	#LIVEAGENT_Mapping tr.no-records-found>td{ text-align:center; color:red; font-size:16px; font-weight:bold; }
	
	.col-personaId{ width:50%; display:inline-block; padding:0 2px; }
	.col-ownership, .col-ownershipEdit, .col-ownerId, .col-ownerIdEdit{width: 100%; }
</style>

<?php
$prID = 0;
$ogID = 0;
switch(strtolower($requestTableName)){
	case 'p':
		$prID = $requestParentId;
		$portal = \App\Portal::find($requestParentId);
		if($portal==null){ $prID=0; }
		else{ $ogID = $portal->organization_id; }
		\Session::put('liveAgentPortalId', $prID);
		break;
	default:
		\Session::put('liveAgentPortalId', null);
/*
		$liveAgentPortalId = \Session::get('liveAgentPortalId');
		if($liveAgentPortalId!==null){
			$prID = $liveAgentPortalId;
			$portal = \App\Portal::find($liveAgentPortalId);
			if($portal==null){ $prID=0; }
			else{ $ogID = $portal->organization_id; }
			\Session::put('liveAgentPortalId', $prID);
		}
*/
}
?>
<div id="LIVEAGENT_Mapping"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>

<script type="application/javascript">
	if( window.addEventListener ){ window.addEventListener("message", offMenu, false); }
	else{ window.attachEvent("onmessage", offMenu); }
	function offMenu(event){
		if(event && event.data=='off'){
			$(".menu.col-md-2, .content-header.row").hide();
			$(".content.col-md-10.col-xs-12").css("width", '100%');
			$(".content.col-md-10.col-xs-12").css("margin-top", '0');
			$.post("<?=env('API_URL');?>/api/set-menu/0", null, null);
		}
	}

	$(function(){
		if('<?=\Session::get('menu_status');?>'==0){
			$(".menu.col-md-2, .content-header.row").hide();
			$(".content.col-md-10.col-xs-12").css("width", '100%');
			$(".content.col-md-10.col-xs-12").css("margin-top", '0');
		}
	});
</script>

<script type="application/javascript">
	var myClass;
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=$session->get('userID');?>";
	var defaultPortalId = <?=$prID;?>;
	var defaultOrgId    = <?=$ogID;?>;
</script>
<script src="/public/js/app.js"></script>
