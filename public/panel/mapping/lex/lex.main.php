<style>
/*div.card-detail{ height:80vh; }*/
	#LEX_Mapping table th,
	#LEX_Mapping table td
		{font-size: 12px; }
	#LEX_Mapping td:last-child,#LEX_Bots th:last-child{ width:30px !important;font-size:12px; }
	#LEX_Mapping tr.no-records-found>td{ text-align:center; color:red; font-size:16px; font-weight:bold; }
	
	.col-personaId{ width:50%; display:inline-block; padding:0 2px; }
	.col-ownership, .col-ownershipEdit, .col-ownerId, .col-ownerIdEdit{width: 100%; }
</style>

<div id="LEX_Mapping"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<?php
/*
<script type="application/javascript">
	var myLEX;
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=$session->get('userID');?>";
</script>
<script src="/public/js/app.js"></script>
*/
?>