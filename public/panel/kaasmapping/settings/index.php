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

	#editKAAS_Setting, #addKAAS_Setting {
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

	#editKAAS_Setting > form, #addKAAS_Setting > form {
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

	#editKAAS_Setting.show, #addKAAS_Setting.show{ display: block; }
	#editKAAS_Setting > form input, #addKAAS_Setting > form input{ width: 250px; }

	.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
	.row-actions{ text-align: center; }
	.row-actions > a:first-child{ padding-right: 10px; }
	#KAAS_Setting th{ font-size:13px;text-align:center; }
	#KAAS_Setting td{ font-size:12px; }
	#KAAS_Setting td:last-child,#KAAS_Setting th:last-child{ width:40px !important;font-size:12px; }

	#KAAS_Setting tr.no-records-found>td{ text-align:center; color:red; font-size:13px; font-weight:bold; }
</style>
<div id="KAAS_Setting"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>


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


<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=$session->get('userID');?>";
	var kaasSet;
</script>
<script src="/public/js/app.js"></script>
<script type="application/javascript">
	//----------------------------------------------------------
	$(function(){
		//------------------------------------------------------
		$("#org_id").on('change', function(){ kaasSet.setPortal($(this).val()); });
		//------------------------------------------------------
	});
	//----------------------------------------------------------
</script>
