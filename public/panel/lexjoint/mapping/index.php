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
	var myLEX;
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=$session->get('userID');?>";
</script>
<script src="/public/js/app.js"></script>
