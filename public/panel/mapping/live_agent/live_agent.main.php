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
