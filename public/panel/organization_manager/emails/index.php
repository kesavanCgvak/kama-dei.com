<style>
	#org_emails_config table th,
	#org_emails_config table td{
	    font-size: smaller;
	}
	#org_emails_config table th:last-child,
	#org_emails_config table td:last-child{
	    font-size: smaller;
		text-align: center;
		width: 40px;
	}
	textarea.body{
		width: 100%; max-width: 100%; min-width: 100%;
		height: 200px; max-height: 200px; min-height: 200px;
	}
	
	.myOwnersList{
		display: inline-block;
		margin: 0 0 0 20px;
		width: auto;
		min-width:300px;
	}
	.myOwnersList label{ margin-right: 5px; }
	.myOwnersList select{
		display: inline-block;
		width: calc(100% - 90px);
		max-width: 200px;
	}
</style>
<script type="text/javascript">
	//-----------------------------------------------------------
	var apiURL  = "<?=env('API_URL');?>";
	var orgID   = "<?=$orgID;?>";
	var levelID = "<?=$levelID;?>";
	var defaultPersonaId = 0;
	var table;
	var MAIL_FROM_ADDRESS = '<?=env('MAIL_FROM_ADDRESS', '');?>';
	//-----------------------------------------------------------
</script>
<div id="org_emails_config"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
	table.createTable('org_emails_config');
	$(function(){
		$("span.content-header-title>span").text("Email Config");
		$("#insertBtn").hide();
		$("#insertBtn").parent().append('<b style="font-size:large;">Log Emails</b>');
		$("#insertBtn").parent()
			.append($('<div>')
				.attr({ class:'myOwnersList' })
				.append('<label>Ownesrs List</label>')
				.append('<select id="myOwnersList" class="form-control"></select>')
			);
		if(orgID!=0){ $(".myOwnersList").hide(); }
		$("#myOwnersList").ready(function(){
			$("#myOwnersList option").remove();
			$("#myOwnersList").append('<option value="-1">All Organization</option>');
			$.get(apiURL+'/api/dashboard/organization/all/'+orgID, function(res){
				for(let i in res.data){
					$("#myOwnersList")
						.append('<option value="'+res.data[i].organizationId+'">'+res.data[i].organizationShortName+'</option>');
				}
				if(orgID==0){ $("#myOwnersList").val(-1); }
				else{ $("#myOwnersList").val(orgID); }
			})
		});
	});
</script>
