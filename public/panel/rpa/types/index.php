<style>
	#rpa_types .table th{
		font-size: 13px;
	}
	#rpa_types .table td{
		font-size: 12px;
	}
	#rpa_types table th:nth-child(1),
	#rpa_types .table td:nth-child(1){
		width: 60px;
		max-width: 60px;
	}
	#rpa_types table th:nth-child(3),
	#rpa_types .table td:nth-child(3){
		width: 80px;
		max-width: 80px;
	}
	#rpa_types table th:last-child,
	#rpa_types .table td:last-child{
		width: 40px;
		max-width: 40px;
	}
</style>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userId = "<?=\Session::get('userID');?>";
	var table;	
</script>

<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<div id="rpa_types"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<script type="text/javascript">
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
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<script src="/public/js/app.js"></script>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
