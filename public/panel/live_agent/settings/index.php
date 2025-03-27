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

	#editLIVEAGENT_Setting, #addLIVEAGENT_Setting {
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

	#editLIVEAGENT_Setting > form, #addLIVEAGENT_Setting > form {
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

	#editLIVEAGENT_Setting.show, #addLIVEAGENT_Setting.show{ display: block; }
	#editLIVEAGENT_Setting > form input, #addLIVEAGENT_Setting > form input{ width: 250px; }

	.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
	.row-actions{ text-align: center; }
	.row-actions > a:first-child{ padding-right: 10px; }
	#LIVEAGENT_Setting th{ font-size:13px;text-align:center; }
	#LIVEAGENT_Setting td{ font-size:12px; }
	#LIVEAGENT_Setting td:last-child,#LIVEAGENT_Setting th:last-child{ width:40px !important;font-size:12px; }

	#LIVEAGENT_Setting tr.no-records-found>td{ text-align:center; color:red; font-size:13px; font-weight:bold; }
</style>
<div id="LIVEAGENT_Setting"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=$session->get('userID');?>";
	var myDataSet;
</script>
<script src="/public/js/app.js"></script>
<script type="application/javascript">
	//----------------------------------------------------------
	$(function(){
		//------------------------------------------------------
		$("#org_id").on('change', function(){ myDataSet.setPortal($(this).val()); });
		//------------------------------------------------------
	});
	//----------------------------------------------------------
</script>
