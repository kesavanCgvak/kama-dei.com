<style>
	#showError{
		position: fixed;
		top: 10px;
		left: 10px;
		color: #fff;
		background: #d25c5c;
		z-index: 9999;
		min-width: 150px;
		width: auto;
		padding: 8px 15px;
		border: 1px dotted #fff;
		border-radius: 8px;
		box-shadow: 0 0 0 3px #d25c5c;
		display:none;
	}

	#editLEX_Setting, #addLEX_Setting {
		display: none;
		position: fixed;
		z-index: 1000;
		background: rgba(0, 0, 0, 0.6);
		top: 0;
		bottom: 0;
		right: 0;
		left: 0;
		margin: auto;
	}

	#editLEX_Setting > form, #addLEX_Setting > form {
		position: absolute;
		margin: auto;
		top: 0;
		bottom: 0;
		right: 0;
		left: 0;
		width: 280px;
		height: 370px;
		width: fit-content;
		height: fit-content;
		background: white;
		padding: 15px;
	}

	#editLEX_Setting.show, #addLEX_Setting.show{ display: block; }
	#editLEX_Setting > form input, #addLEX_Setting > form input{ width: 250px; }

	.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
	.row-actions{ text-align: center; }
	.row-actions > a:first-child{ padding-right: 10px; }
	/*
	#LEX_Setting td:nth-child(1){ text-align:right;font-size:12px; }
	#LEX_Setting th:nth-child(2),
	#LEX_Setting td:nth-child(2)
		{ width:50% !important; }
	#LEX_Setting th:nth-child(4),
	#LEX_Setting td:nth-child(4)
		{ width:40px !important;font-size:13px; }
	*/
	#LEX_Setting th{ font-size:13px;text-align:center; }
	#LEX_Setting td{ font-size:12px; }
/*
	#LEX_Setting td:nth-child(1),#LEX_Setting th:nth-child(1),
	#LEX_Setting td:nth-child(2),#LEX_Setting th:nth-child(2),
	#LEX_Setting td:nth-child(3),#LEX_Setting th:nth-child(3)
		{ width:28% !important; }

	#LEX_Setting td:nth-child(4),#LEX_Setting th:nth-child(4){ width:15% !important;text-align:center; }
*/
	#LEX_Setting td:last-child,#LEX_Setting th:last-child{ width:40px !important;font-size:12px; }

	#LEX_Setting tr.no-records-found>td{ text-align:center; color:red; font-size:13px; font-weight:bold; }
</style>
<div id="LEX_Setting"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
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
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=$session->get('userID');?>";
	var lexSet;
</script>
<script src="/public/js/app.js"></script>
<script type="application/javascript">
	//----------------------------------------------------------
	$(function(){
		//------------------------------------------------------
		$("#org_id").on('change', function(){ lexSet.setPersona($(this).val()); });
		//------------------------------------------------------
	});
	//----------------------------------------------------------
</script>
