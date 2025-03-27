<style>
	div.card-detail{ min-height:80vh; }
	div.box_half{ display:inline-block;width:45%;margin:auto;vertical-align:middle;padding:10px;text-align:center; }
	
	.panel{
		border: none !important;
		box-shadow: none !important;
		margin-bottom:16px !important;
	}
	.panel-heading{
		color: #fff;
	    background: #2a94d6;
    	border-radius: 8px 8px 0 0;
    	padding: 20px !important;
    	margin: 0;
    	border: none;
	}
	.panel-body{
	    border: 1px solid #eee;
		color: #000 !important;
	}
	.btn-group-vertical>.btn, .btn-group>.btn{ padding:7px 11px; }
	
	.left{ width: 33%; display: inline-block; font-size: large; vertical-align: top;}
	.right{ width:65%; display:inline-block; vertical-align: middle; }
	
	.btn-group button{
		border: 1ps solid;
		border-radius: 5px !important;
		margin: 0 8px 8px;
	}
	.chartName{
		background: linear-gradient(154deg, #008fe2 0, #00b29c 100%);
		color: #FFF;
		padding: 10px;
		border-radius: 5px;
		text-align: center;
		width: fit-content;
		margin-left: -15px;
		margin-top: -15px;
	}
	.chartDescription{
		margin-top: 20px;
		font-size: smaller;
		text-align: justify;
	}
	.seeListOrg{
		max-width: 250px;
		display: inline-block;
		vertical-align: text-bottom;
	}
	#seeList_btn{
		width: calc(100% - 270px);
		display: inline-block;
		margin-bottom: 0;
		margin-top: -10px;
		margin-left: 10px;
	}
	@media screen and (max-width:800px){
		.seeListOrg{
			max-width: 100%;
			width: 100%;
			display: block;
			vertical-align: baseline;
		}
		#seeList_btn{
			width: 100%;
			display: block;
			margin-bottom: 5px;
			margin-top: 10px;
			margin-left: 0;
		}
		.modal-header{
			height: 140px;
			display: block;
		}
	}
</style>
<div class="panel">
	<div class="panel-body">
		<div class="left">
			<label>Select Organization</label>
			<select class="form-control" id="orgID">
				<?php if($orgID==0):?>
				<option value="0" data-feedback='1'>All</option>
				<?php
					$orgs = \App\Organization::orderBy("organizationShortName", 'asc')->get();
					if(!$orgs->isEmpty()){
						foreach($orgs as $org){
							?>
							<option value="<?=$org->organizationId;?>" data-feedback='<?=$org->feedback;?>'>
								<?=$org->organizationShortName;?>
							</option>
							<?php
						}
					}
				?>
				<?php else:?>
				<?php
					$org = \App\Organization::find($orgID);
					?>
					<option value="<?=$org->organizationId;?>" data-feedback='<?=$org->feedback;?>'>
						<?=$org->organizationShortName;?>
					</option>
					<?php 
				?>
				<?php endif;?>
			</select> 
		</div>
	</div>
	<script type="application/javascript">
		var apiURL = '<?=env('API_URL');?>/api/charts/';
	</script>
</div>

<div class="panel">
	<div class="panel-body">
		<div class="left">
			<div class="chartName">Chat Volume &amp; Performance</div>
			<p class="chartDescription">
				Total chats over a period versus chats where information was delivered 'successfully' over the selected time period.
			</p>
		</div>
		<div class="right">
			<div style="text-align: center; margin-bottom: 10px;">
				<div class="btn-group" role="group" id="extendedDataTextIntro_btn">
					<button type="button" class="btn btn-default" data-flag="4">Last 24 hours</button>
					<button type="button" class="btn btn-danger" data-flag="3">1 week</button>
					<button type="button" class="btn btn-default" data-flag="2">1 month</button>
					<button type="button" class="btn btn-default" data-flag="1">1 year</button>
					<i class="fa fa-refresh fa-spin fa-2x" style="margin:auto; display:none" id="extendedDataTextIntroWait"></i>
				</div>
				<small style="font-style:italic; display:block">* To refresh, click on the same button again.</small>
			</div>
			<div id="extendedDataTextIntro"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
		</div>
	</div>
</div>
<script type="application/javascript">
	var chart2Config = {
		type: 'line',
		data: {
			datasets: [
				{ label: 'total', backgroundColor: '#0342f461', borderColor: 'blue', data: null, fill: true },
				{ label: 'successful delivery', backgroundColor: '#008fe2', borderColor: 'green', data: null, fill: true },
			],
			labels: null
		},
		options: {
			responsive: true,
			title   : { display: false, text: 'Extended Data Text Intro' },
			tooltips: { mode: 'index', intersect: false, },
			hover   : { mode: 'nearest', intersect: true },
			elements: { line: { tension: 0.000001 } },
			scales  : {
				x: { display: true, scaleLabel: { display: true, labelString: 'Month' } },
				y: { display: true, scaleLabel: { display: true, labelString: 'Value' } },
				yAxes: [{ stacked: false }]
			},
			legend: { display: true, position: 'bottom' }
		}
	};
	var chart2, chart2FLG=3;
	$(function(){
		$("#extendedDataTextIntro").ready(function(){
			$("#extendedDataTextIntro").html("<canvas></canvas>");
			var ctx = $("#extendedDataTextIntro canvas")[0].getContext('2d');
			chart2 = new Chart(ctx, chart2Config);
			extendedDataTextIntroConfig(apiURL, chart2Config, chart2, chart2FLG);
		});
		$("#extendedDataTextIntro_btn button").on("click", function(){
			$("#extendedDataTextIntro_btn button").attr('class', 'btn btn-default');
			$(this).attr('class', 'btn btn-danger');
			chart2FLG = $(this).data('flag');
			extendedDataTextIntroConfig(apiURL, chart2Config, chart2, chart2FLG);
		});
		
	});
	function extendedDataTextIntroConfig(apiURL, chart2Config, chart2, flag){
		$("#extendedDataTextIntroWait").show();
		chart2Config.data.datasets[0].data = null;
		chart2Config.data.datasets[1].data = null;
		chart2Config.data.labels           = [];
		chart2.update();
		$.get(apiURL+"2/"+flag+"/"+$("#orgID").val(),
			function(ret){
				chart2Config.data.datasets[0].data = ret.data[0];
				chart2Config.data.datasets[1].data = ret.data[1];
				chart2Config.data.labels           = ret.labels;
				chart2.update();
				$("#extendedDataTextIntroWait").hide();
			}
		);
		return 
	}
</script>
		
<div class="panel">
	<div class="panel-body">
		<div class="left">
			<div class="chartName">Chat Duration</div>
			<p class="chartDescription">
				Chat stop time minus chat start time in various duration buckets over the selected time period.
			</p>
		</div>
		<div class="right">
			<div style="text-align: center; margin-bottom: 10px;">
				<div class="btn-group" role="group" id="chatDuration_btn">
					<button type="button" class="btn btn-default" data-flag="4">Last 24 hours</button>
					<button type="button" class="btn btn-danger" data-flag="3">1 week</button>
					<button type="button" class="btn btn-default" data-flag="2">1 month</button>
					<button type="button" class="btn btn-default" data-flag="1">1 year</button>
					<i class="fa fa-refresh fa-spin fa-2x" style="margin:auto; display:none" id="chatDurationWait"></i>
				</div>
				<small style="font-style:italic; display:block">* To refresh, click on the same button again.</small>
			</div>
			<div id="chatDuration"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
		</div>
	</div>
</div>
<script type="application/javascript">
	var chart1Config = {
		type: 'pie',
		data: {
			datasets: [{
				data: null,
				backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0'],
			}],
			labels: [ 'Below 5 sec', '5-30 sec', '30 sec-1 min', '>1 min' ]
		},
		options: {
			tooltips:{
				callbacks:{
					label: function(tooltipItem, chartData){
						return chartData.labels[tooltipItem.index]+": "+chartData.datasets[0].data[tooltipItem.index]+" chats";
					}
				}	
			},
			title: { display: false, text: 'Chat Duration' },
			responsive: true,
			legend: { position: 'bottom' },
			plugins:{
				labels: [
					{ render: 'label', position: 'outside', arc: true, },
					{ render: 'percentage', fontSize: 14, fontStyle: 'bold', fontColor: '#000' }
				]
			}
		}
	};
	var chart1, chart1FLG=3;

	$(function(){
		$("#chatDuration").ready(function(){
			$("#chatDuration").html("<canvas></canvas>");
			var ctx = $("#chatDuration canvas")[0].getContext('2d');
			chart1 = new Chart(ctx, chart1Config);
			chatDuration(apiURL, chart1Config, chart1, chart1FLG);
		});

		$("#chatDuration_btn button").on("click", function(){
			$("#chatDuration_btn button").attr('class', 'btn btn-default');
			$(this).attr('class', 'btn btn-danger');
			chart1FLG = $(this).data('flag');
			chatDuration(apiURL, chart1Config, chart1, chart1FLG);
		});

	});
	function chatDuration(apiURL, chart1Config, chart1, flag){
		$("#chatDurationWait").show();
		chart1Config.data.datasets[0].data = null;
		chart1.update();
		$.get(apiURL+"1/"+flag+"/"+$("#orgID").val(),
			function(ret){
				chart1Config.data.datasets[0].data = ret.data;
				chart1.update();
				$("#chatDurationWait").hide();
			}
		);
		return ;
	}
</script>

<div class="panel">
<!--
	<div class="panel-heading">% rate of "I don&#39;t know" answers to the total Q&amp;A pairs during the chat</div>
-->
	<div class="panel-body">
		<div class="left">
			<div class="chartName">FAQ Performance</div>
			<p class="chartDescription">
				The number of Q&amp;A pairs that were completed with the successful delivery of information versus the number where no answer/information was provided over the selected time period.
			</p>
			
			<div style="margin-top: 10px">
				<button type="button" class="btn btn-success" data-toggle="modal" data-target="#seeList">See List</button>
				<div id="seeList" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<div class="seeListOrg">
									<label>Select Organization</label>
									<select class="form-control" id="seeListOrgID">

						<?php if($orgID==0):?>
										<option value="0">All</option>
										<?php
											$orgs = \App\Organization::orderBy("organizationShortName", 'asc')->get();
											if(!$orgs->isEmpty()){
												foreach($orgs as $org){ ?><option value="<?=$org->organizationId;?>"><?=$org->organizationShortName;?></option><?php }
											}
										?>
						<?php else:?>
										<?php
											$org = \App\Organization::find($orgID);
											?><option value="<?=$org->organizationId;?>"><?=$org->organizationShortName;?></option><?php 
										?>
						<?php endif;?>
									</select> 
								</div>
								<div class="btn-group" role="group" id="seeList_btn">
									<button type="button" class="btn btn-default" data-flag="4">Last 24 hours</button>
									<button type="button" class="btn btn-danger" data-flag="3">1 week</button>
									<button type="button" class="btn btn-default" data-flag="2">1 month</button>
									<button type="button" class="btn btn-default" data-flag="1">1 year</button>
									
									<button class="btn btn-info" data-flag="0" style="float: right">
										Export to CSV
									</button>
								</div>
							</div>
							<div class="modal-body">
								<div class="alert alert-danger" id="seeListMSG" 
									 style="display:none; font-size:small; box-shadow:5px 5px #bfb4b4;">
								</div>
								<table 
									id="seeListTBL"
									data-show-refresh=true
									data-toggle="table" 
									data-smart-display=true
									data-search=true
									data-detail-view=false
									data-detail-formatter=""
									data-pagination=true
									data-sort-name="ChatID" 
									data-sort-order="asc"
									data-method='get'
									data-url=''
									data-data-field='data'
									data-single-select=true
									class="table table-striped table-bordered"
									style="width:100%"
								>
									<thead style="background:#42bbe8d1; color:#fff;">
										<tr>
											<th data-field="portalName" data-sortable="true">Portal Name</th>
											<th data-field="persona" data-sortable="true">Persona</th>
											<th data-field="user" data-sortable="true">User</th>
											<th data-field="startedAt" data-sortable="true">Started at</th>
											<th data-field="failedUtterance">Failed Utterance</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="right">
			<div style="text-align: center; margin-bottom: 10px;">
				<div class="btn-group" role="group" id="pairsDuring_btn">
					<button type="button" class="btn btn-default" data-flag="4">Last 24 hours</button>
					<button type="button" class="btn btn-danger" data-flag="3">1 week</button>
					<button type="button" class="btn btn-default" data-flag="2">1 month</button>
					<button type="button" class="btn btn-default" data-flag="1">1 year</button>
					<i class="fa fa-refresh fa-spin fa-2x" style="margin:auto; display:none" id="pairsDuringWait"></i>
				</div>
				<small style="font-style:italic; display:block">* To refresh, click on the same button again.</small>
			</div>
			<div id="pairsDuring"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
		</div>
	</div>
</div>
<script type="application/javascript">
	var chart3Config = {
		type: 'pie',
		data: {
			datasets: [{
				data: [],
				backgroundColor: ['#ff6384', '#36a2eb'],
//				hoverBackgroundColor: ['#f55', '#5f5', '#55f'],
//				hoverBorderColor: ['#000', '#000', '#000']
			}],
			labels: [
				"\"I don't know\" answers",
				'Found answers'
			]
		},
		options: {
			title: {
				display: false,
				text: 'Chat Duration'
			},
			responsive: true,
			legend: {
				position: 'bottom'
			},
			plugins:{
				labels: [
					{
						render: 'label',
						position: 'outside',
						arc: true,
					},
					{
						render: 'percentage',
						fontSize: 14,
						fontStyle: 'bold',
						fontColor: '#000',
						//fontFamily: '"Helvetica Neue",Helvetica,Arial,sans-serif'
					}
				]
			}
		}
	};
	var chart3, chart3FLG=3;
	$(function(){
		$("#pairsDuring").ready(function(){
			$("#pairsDuring").html("<canvas></canvas>");
			var ctx = $("#pairsDuring canvas")[0].getContext('2d');
			chart3 = new Chart(ctx, chart3Config);
			pairsDuring(apiURL, chart3Config, chart3, chart3FLG);
		});
		$("#pairsDuring_btn button").on("click", function(){
			$("#pairsDuring_btn button").attr('class', 'btn btn-default');
			$(this).attr('class', 'btn btn-danger');
			chart3FLG = $(this).data('flag');
			pairsDuring(apiURL, chart3Config, chart3, chart3FLG);
		});

		$('#seeListTBL').ready(function(){
			$('#seeListTBL').bootstrapTable('refresh',{url:apiURL+'4/'+chart3FLG+'/'+$("#seeListOrgID").val()});
		});
		$("#seeListOrgID").on('change', function(){
			$('#seeListTBL').bootstrapTable('refresh',{url:apiURL+'4/'+chart3FLG+'/'+$("#seeListOrgID").val()});
		})

		$("#seeList_btn .btn").on("click", function(){
			if($(this).data('flag')==0){
				//BHR
				$.get(apiURL+'5/'+chart3FLG+'/'+$("#seeListOrgID").val(),
					function(ret){
						if(ret.result==0){
							window.open("/"+ret.file, "_blank")
						}else{
							$("#seeListMSG").html(ret.msg);
							$("#seeListMSG").show();
							setTimeout(function(){ $("#seeListMSG").hide(); }, 3000);
						}
						
//						$("#downloadCSV").attr('href', "/csv/seelist.20200905.1599281995.csv");
//						$("#downloadCSV").click(function(){
					}
				);
				return;
			}
			$("#seeList_btn button").each(function(){
				if($(this).data('flag')!=0){ $(this).attr('class', 'btn btn-default'); }
			});
			$(this).attr('class', 'btn btn-danger');
			chart3FLG = $(this).data('flag');
			$('#seeListTBL').bootstrapTable('refresh',{url:apiURL+'4/'+chart3FLG+'/'+$("#seeListOrgID").val()});
		});
	});
	function pairsDuring(apiURL, chart3Config, chart3, flag){
		$("#pairsDuringWait").show();
		chart3Config.data.datasets[0].data = null;
		chart3.update();
		$.get(apiURL+"3/"+flag+"/"+$("#orgID").val(),
			function(ret){
				$("#pairsDuringWait").hide();
				chart3Config.data.datasets[0].data = ret.data;
				chart3.update();
			}
		);
		return ;
	}
</script>


<div class="panel" id="feedbackDuringPanel">
	<div class="panel-body">
		<div class="left">
			<div class="chartName">Feedback</div>
			<p class="chartDescription">
				The number of Q&amp;A pairs that were completed within the selected time period, and whether the user provided positive,
				&nbsp;negative, or no feedback indicator to the information provided.
			</p>
		</div>
		<div class="right">
			<div style="text-align: center; margin-bottom: 10px;">
				<div class="btn-group" role="group" id="feedbackDuring_btn">
					<button type="button" class="btn btn-default" data-flag="4">Last 24 hours</button>
					<button type="button" class="btn btn-danger" data-flag="3">1 week</button>
					<button type="button" class="btn btn-default" data-flag="2">1 month</button>
					<button type="button" class="btn btn-default" data-flag="1">1 year</button>
					<i class="fa fa-refresh fa-spin fa-2x" style="margin:auto; display:none" id="feedbackDuringWait"></i>
				</div>
				<small style="font-style:italic; display:block">* To refresh, click on the same button again.</small>
			</div>
			<div id="feedbackDuring"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
		</div>
	</div>
</div>

<script type="application/javascript">
	var chart4Config = {
		type: 'bar',
		data: {
			datasets: [{
				data: [],
				backgroundColor: ['#efefef', '#36a2eb', '#ff6384'],
				borderColor: ['#efefef', '#36a2eb', '#ff6384'],
			}],
			labels: [
				"None",
				"Thumbs-Up",
				'Thumbs-Down'
			]
		},
		options: {
			title: {
				display: false,
				text: 'Feedback'
			},
			responsive: true,
			legend: {
				position: 'none'
			},
			//aspectRatio: 5 / 3,
			layout: {
				padding: {
					top: 32
				}
			},
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true
					}
				}]
			},
			plugins:{
				labels: {
					render: 'value',
					align: 'top',
					anchor: 'top',
					fontSize: 12,
					fontStyle: 'bold',
					fontColor: '#000',
				}
			}
		}
	};
	var chart4, chart4FLG=3;
	$(function(){
		$("#feedbackDuring").ready(function(){
			$("#feedbackDuring").html("<canvas></canvas>");
			var ctx = $("#feedbackDuring canvas")[0].getContext('2d');
			chart4 = new Chart(ctx, chart4Config);
			feedbackDuring(apiURL, chart4Config, chart4, chart4FLG);
		});
		$("#feedbackDuring_btn button").on("click", function(){
			$("#feedbackDuring_btn button").attr('class', 'btn btn-default');
			$(this).attr('class', 'btn btn-danger');
			chart4FLG = $(this).data('flag');
			feedbackDuring(apiURL, chart4Config, chart4, chart4FLG);
		});
		
		if($("#orgID option:selected").data("feedback")==1){ $("#feedbackDuringPanel").show(); }
		else{ $("#feedbackDuringPanel").hide(); }
	});
	function feedbackDuring(apiURL, chart4Config, chart4, flag){
		$("#feedbackDuringWait").show();
		chart4Config.data.datasets[0].data = null;
		chart4.update();
		$.get(apiURL+"6/"+flag+"/"+$("#orgID").val(),
			function(ret){
				$("#feedbackDuringWait").hide();
				chart4Config.data.datasets[0].data = ret.data;
				chart4.update();
			}
		);
		return ;
	}
</script>


<script src="/public/js/app.js"></script>

<script type="application/javascript">
	$("#orgID").on("change", function(){
		chatDuration(apiURL, chart1Config, chart1, chart1FLG);
		extendedDataTextIntroConfig(apiURL, chart2Config, chart2, chart2FLG);
		pairsDuring(apiURL, chart3Config, chart3, chart3FLG);
		feedbackDuring(apiURL, chart4Config, chart4, chart4FLG);

		if($("#orgID option:selected").data("feedback")==1){ $("#feedbackDuringPanel").show(); }
		else{ $("#feedbackDuringPanel").hide(); }
	});
</script>
