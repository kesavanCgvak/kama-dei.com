<style>
	#editItem > form, #addItem > form{
		max-height: 634px;
		overflow: auto;
	}
	.col-name.form-group{
		width:49%;
		margin-right:.8%;
		display: inline-block;
	}
	.col-organization_id.form-group{
		width:49%;
		margin-left:.8%;
		display: inline-block;
	}
	#portal table th{ font-size:smaller; }
	#portal table td{ font-size:small; }

	#portal table td:last-child  { font-size:x-small; text-align:center; width:40px; }

	<?php if($orgID!=0):?>
	#portal table td:nth-child(1){ min-width:100px; }
	#portal table td:nth-child(7){ width:81px; text-align:center; }
	#portal table td:nth-child(8){ width:81px; text-align:center; }
	#portal table td:nth-child(9){ width:81px; text-align:center; }
/*	#portal table th:nth-child(2),*/
/*	#portal table td:nth-child(2),*/
	#portal table th:nth-child(3),
	#portal table td:nth-child(3),
	#portal table th:nth-child(4),
	#portal table td:nth-child(4),
	#portal table th:nth-child(5),
	#portal table td:nth-child(5),
	#portal table th:nth-child(6),
	#portal table td:nth-child(6)
		{ display: none; }
	<?php
	$org = \App\Organization::find($orgID);
	if($org!=null && $org->hasLiveAgent==0):
	?>
	#portal table th:nth-child(7),
	#portal table td:nth-child(7)
		{ display: none; }
	<?php endif; ?>
/*
	#portal table th:nth-child(5),
	#portal table td:nth-child(5)
*/
	#tableToolbar{ display: none; }

	
	.half{
		width:48%;
		display: inline-block;
	}
	.third{
		width:32%;
		display: inline-block;
	}
	.right{ text-align: left; }
	.half.right{ margin-left: 4%; }
	.third.center{ margin-left: 1%; }
	.third.right{ margin-left: 2%; }
	.quarter{
		width:24.5%;
		display: inline-block;
	}
	.quarter.center{ margin-left: 0.5%; }
	.quarter.right{ margin-left: 0.5%; }
	@media screen and (max-width:400px){
		.half{
			width:100%;
			display: block;
		}
		.third{
			width:100%;
			display: block;
		}
		.quarter{
			width:100%;
			display: block;
		}
		.right{ text-align: left; }
	}
	<?php else: ?>
	#portal table td:nth-child(1){ width:25%; min-width:100px; }
	#portal table td:nth-child(2){ width:25%; min-width:100px; }
	#portal table th:nth-child(3)
		{ width:80px; min-width:80px; }
	#portal table td:nth-child(4)
		{ width:80px; min-width:80px; text-align: center; }
	#portal table th:nth-child(5)
		{ min-width:110px; }
	#portal table td:nth-child(5)
		{ min-width:110px; text-align: left; }
	#portal table th:nth-child(6)
		{ width:110px; min-width:110px; }
	#portal table td:nth-child(6)
		{ width:110px; min-width:110px; text-align: left; }
	#portal table td:nth-child(7)
		{ text-align: center; }
	#portal table td:nth-child(8)
		{ text-align: center; }
	#portal table td:nth-child(9)
		{ text-align: center; }
	#portal table td:last-child{ width:40px; }
	.half{
		width:48%;
		display: inline-block;
	}
	.third{
		width:32%;
		display: inline-block;
	}
	.right{ text-align: left; }
	.half.right{ margin-left: 4%; }
	.third.center{ margin-left: 1%; }
	.third.right{ margin-left: 2%; }
	.quarter{
		width:24.5%;
		display: inline-block;
	}
	.quarter.center{ margin-left: 0.5%; }
	.quarter.right{ margin-left: 0.5%; }
	@media screen and (max-width:400px){
		.half{
			width:100%;
			display: block;
		}
		.third{
			width:100%;
			display: block;
		}
		.quarter{
			width:100%;
			display: block;
		}
		.right{ text-align: left; }
	}
	div.pull-right.search>select{ width:49%; display:inline-block; margin-right:1%; }
	div.pull-right.search>input{ width:49%; display:inline-block; margin-left:1%; }
	#portal table th:nth-child( 3)>.th-inner,
	#portal table th:nth-child( 4)>.th-inner,
	#portal table th:nth-child( 8)>.th-inner
	{
		text-align: center;
		white-space: pre-wrap;
		font-size: small;
		width: 75px;
	}
	#portal table th:nth-child( 9)>.th-inner
	{
		text-align: center;
		white-space: pre-wrap;
		font-size: small;
		width: 80px;
	}
	#portal table th:nth-child( 6)>.th-inner,
	#portal table th:nth-child( 7)>.th-inner,
	#portal table th:nth-child(10)>.th-inner
	{
		text-align: center;
		white-space: pre-wrap;
		font-size: small;
		width: 110px;
	}
	<?php endif; ?>
	#portalFlags{ width: 100%; border: 1px solid #ddd; }
	
	#portalFlags th{ text-align: center; padding: 10px 0; font-size:13px !important; background: #eee; border-bottom: 1px solid #ddd; }
	#portalFlags th:nth-child(1){ width:30% !important; border-right: 1px solid #bbb; }
	#portalFlags th:nth-child(2){ width:10% !important; max-width:40px !important; border-right:1px solid #bbb; }
	#portalFlags th:nth-child(3){ width:60% !important; }
	
	#portalFlags td{ padding: 8px 2px; text-align:left !important; border-bottom: none; }
	#portalFlags td:nth-child(1){ border:1px solid #eee; border-left:none; }
	#portalFlags td:nth-child(2){ border:1px solid #eee; border-left:none; text-align:center !important; vertical-align:middle; }
	#portalFlags td:nth-child(3){ border:1px solid #eee; border-left:none; font-size:13px !important;}
</style>
<?php

$kaaSColumnHidden='true';
if($orgID!=0)
{
	$orgs = \App\Organization::find($orgID);
		if($orgs->KaaS3PB==1){
			$kaaSColumnHidden='false';
		}
}else{
	$kaaSColumnHidden='false';
}
?>
<script type="text/javascript">
	var apiURL  = "<?=env('API_URL');?>";
	var orgID   = "<?=$orgID;?>";
	var userId  = "<?=\Session::get('userID');?>";
	var table;	
	var kaaSColumnHidden = <?=$kaaSColumnHidden; ?>;
	var portalOwnersList = "-1";
	var webPageIntegrationURL = '<?=env("webpage_integration_url",'');?>';
</script>

<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<div id="portal"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<script src="/public/js/app.js"></script>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<script type="application/javascript">
	<?php if($orgID==0): ?>
	$(function(){
		$("div.pull-right.search").prepend(
			'<select class="form-control" id="portalOwnersList">'+
				'<option value="-1" selected="selected">All Owners . . .</option>'+
			'</select>'
		);
		
		$("#portalOwnersList").on('change', function(){
			portalOwnersList=$("#portalOwnersList").val();
			$(table.table).bootstrapTable('refresh');
		});
	});
	$("#portalOwnersList").ready(function(){
		$.get(apiURL+'/api/dashboard/portal/getPortalOwner/', function(res){
			if(res.result==0){
				for(let i in res.data){
					$("#portalOwnersList").append('<option value="'+res.data[i].orgId+'">'+res.data[i].orgName+'</option>');
				}
			}
		});
	});
	<?php endif; ?>
</script>




<div id="myFeedback" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
				<div class="form-group">
					<label>Portal Name</label>
					<div>
						<input id="portalName" placeholder="Portal Name" class="form-control" readonly />
						<input id="portalID"   type="hidden"/>
					</div>
				</div>

				<div class="form-group quarter left" style="font-size: small;">
					<label>Feedback</label>
					<div>
						<input id="feedback" class="form-control" data-toggle='toggle' type="checkbox" />
					</div>
				</div>
				<div class="form-group quarter left" style="font-size: small;">
					<label>Thumbsup</label>
					<div>
						<input id="thumbsup" class="form-control" data-toggle='toggle' type="checkbox" />
					</div>
				</div>
				<div class="form-group quarter left" style="font-size: small;">
					<label>Comment</label>
					<div>
						<input id="comment" class="form-control" data-toggle='toggle' type="checkbox" />
					</div>
				</div>
				
			</div>
			<div class="modal-footer" style="display:flex; flex-wrap:wrap">
				<button type="button" style="width:33%" class="btn btn-danger" data-dismiss="modal">Cancel</button>
				<span style="display:inline-block; width:34%"></span>
				<button type="button" style="width:33%" class="btn btn-info" id="saveFeedback" >Save Item</button>
			</div>
		</div>
	</div>
</div>