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
	</div>
	<script type="application/javascript">
		var apiURL = '<?=env('API_URL');?>/api/charts/';
	</script>
</div>

<div class="panel">
<!--
	<div class="panel-heading">Extended Data Text Intro</div>
-->
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
<!--
	<div class="panel-heading">Chat Duration</div> 
-->
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

<?php /*
<div class="panel">
<!--
	<div class="panel-heading">Chat Volume</div>
-->
	<div class="panel-body">
		<div class="left">
			# of Chats per organization occurred within a period of selectable chart interval.... 
		</div>
		<div class="right">
			<div style="text-align: center; margin-bottom: 10px;">
				<div class="btn-group" role="group" id="ChatVolume_btn">
					<button type="button" class="btn btn-primary" data-flag="1" style="font-size:small">Over last week per Org</button>
					<button type="button" class="btn btn-default" data-flag="2" style="font-size:small">interval (24hours)</button>
					<button type="button" class="btn btn-default" data-flag="2" style="font-size:small">interval (1 week)</button>
					<button type="button" class="btn btn-default" data-flag="2" style="font-size:small">interval (1 month)</button>
					<button type="button" class="btn btn-default" data-flag="2" style="font-size:small">interval (1 year)</button>
				</div>
			</div>
			<div id="ChatVolume"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
		</div>
	</div>
</div>
<script type="application/javascript">
	$(function(){
		$("#ChatVolume").ready(function(){
			$("#ChatVolume").html("<canvas></canvas>");
			var ctx = $("#ChatVolume canvas")[0].getContext('2d');
			var chart = new Chart(ctx, ChatVolumeConfig(3));
		});
		$("#ChatVolume_btn button").on("click", function(){
			$("#ChatVolume").html("<canvas></canvas>");
			var ctx = $("#ChatVolume canvas")[0].getContext('2d');
			var chart = new Chart(ctx, ChatVolumeConfig($(this).data('flag')));
			$("#ChatVolume_btn button").attr('class', 'btn btn-default');
			$(this).attr('class', 'btn btn-primary');
		});
		
	});
	function ChatVolumeConfig(flag){
		var data   = [];
		var labels = [];
		switch(flag){
			case 1:
				data = [ 1500, 2000, 1700, 1100, 3000, 2600, 3100, 2850, 1987, 2377, 1234, 2358 ];
				labels = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
				break;
			case 2:
				data = [ 500, 1000, 700, 1100 ];
				labels = [ 'week 1', 'week 2', 'week 3', 'week 4' ];
				break;
			case 3:
				data = [ 250, 500, 500, 100, 300, 190, 300 ];
				labels = [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ];
				break;
			case 4:
				data = [5, 7, 15, 3, 20, 12, 20, 30, 50, 150, 155, 150, 190, 160, 50, 55, 60, 80, 90, 190, 188, 100, 110, 50 ];
				for(let i=0; i<24; i++){
					labels.push(i);
				}
				break;
		}
		return {
			type: 'line',
			data: {
				datasets: [
					{
						label: 'total',
						backgroundColor: '#36a2eb',
						borderColor: '#0000ff',
						data: data,
						fill: 'start',
					}
				],
				labels: labels
			},
			options: {
				responsive: true,
				title: {
					display: false,
					text: 'Extended Data Text Intro'
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				elements: {
					line: {
						tension: 0.000001
					}
				},
				scales: {
					x: {
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Month'
						}
					},
					y: {
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Value'
						}
					},
					yAxes: [{
						stacked: true
					}]
				},
				legend: {
					display: false,
					position: 'bottom'
				}
			}
		};
	}
</script>

<?php / *
<h3>Recent Chats</h3>

<label style="font-size:16px;">
	Consumer:&nbsp;
	<b>
	<?php
		$tmp = \App\User::where("levelID", '=', 4);
		if($orgID!=0){ $tmp = $tmp->where("orgID", '=', $orgID); }
		echo $tmp->count();
	?>
	</b>&nbsp;
	user
	<small style="font-size:12px">(s)</small>
</label>
* / ?>
<?php 
/ *
	$activeChats = App\Models\Extend\Extended_chatbot_usage::where('archive',0)
						->where(function($q) use($orgID){
							if($orgID==0){ return $q; }
							return $q->where('org_id', $orgID);
						})
						->select(DB::raw('count(*)'))
						->groupBy('user_id', 'org_id')
						->get(); 
if($activeChats==null){ $activeChats=0; }
else{ $activeChats= count( $activeChats ); }
* /
	$activeChats = App\ApiKeyManager\ApiKeyManagerClass::active_users();
?>
<div class="box_half" style="padding-top:30px;font-size:18px;">
	Active Chats:
	<span style="font-size:30px;background:blue;color:white;padding:10px 20px;margin-left:10px;border:1px solid black;">
		<?=$activeChats;?>
	</span>
</div>
<div class="box_half">
	<div id="passWeekChats"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
</div>
<div class="box_half">
	<div id="last24HRS"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
</div>
<div class="box_half">
	<div id="7DayChat"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
</div>

<?php 
	//---------------------------------------------------------------------------
	//---------------------------------------------------------------------------
	//---------------------------------------------------------------------------
	$last0HT = date("Y-m-d H:i:s");
	$last6HT = date("Y-m-d H:i:s", strtotime('-6 hour' ));
	$last12T = date("Y-m-d H:i:s", strtotime('-12 hour'));
	$last18T = date("Y-m-d H:i:s", strtotime('-18 hour'));
	$last24T = date("Y-m-d H:i:s", strtotime('-24 hour'));
	//---------------------------------------------------------------------------
	$last6HC = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$last6HT, $last0HT] );
	$last12C = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$last12T, $last6HT] );
	$last18C = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$last18T, $last12T] );
	$last24C = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$last24T, $last18T] );
	//---------------------------------------------------------------------------
	$last6HC = $last6HC
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	$last12C = $last12C
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	$last18C = $last18C
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	$last24C = $last24C
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	//---------------------------------------------------------------------------
	if($last6HC==null){ $last6HC=0; }
	else{ $last6HC = count( $last6HC ); }
	if($last12C==null){ $last12C=0; }
	else{ $last12C = count( $last12C ); }
	if($last18C==null){ $last18C=0; }
	else{ $last18C = count( $last18C ); }
	if($last24C==null){ $last24C=0; }
	else{ $last24C = count( $last24C ); }
	//---------------------------------------------------------------------------
	//---------------------------------------------------------------------------
	//---------------------------------------------------------------------------
	$day0 = date("Y-m-d H:i:s");
	$day1 = date("Y-m-d H:i:s", strtotime('-1 day' ));
	$day2 = date("Y-m-d H:i:s", strtotime('-2 day'));
	$day3 = date("Y-m-d H:i:s", strtotime('-3 day'));
	$day4 = date("Y-m-d H:i:s", strtotime('-4 day'));
	$day5 = date("Y-m-d H:i:s", strtotime('-5 day'));
	$day6 = date("Y-m-d H:i:s", strtotime('-6 day'));
	$day7 = date("Y-m-d H:i:s", strtotime('-7 day'));
	//---------------------------------------------------------------------------
	$day1c = date("D", strtotime($day1));
	$day2c = date("D", strtotime($day2));
	$day3c = date("D", strtotime($day3));
	$day4c = date("D", strtotime($day4));
	$day5c = date("D", strtotime($day5));
	$day6c = date("D", strtotime($day6));
	$day7c = date("D", strtotime($day7));
	//---------------------------------------------------------------------------
	$last1D = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$day1, $day0] );
	$last2D = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$day2, $day1] );
	$last3D = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$day3, $day2] );
	$last4D = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$day4, $day3] );
	$last5D = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$day5, $day4] );
	$last6D = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$day6, $day5] );
	$last7D = App\Models\Extend\Extended_chatbot_usage::where('archive',0)->whereBetween('timestamp',[$day7, $day6] );
	//---------------------------------------------------------------------------
	$last1D = $last1D
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	$last2D = $last2D
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	$last3D = $last3D
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	$last4D = $last4D
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	$last5D = $last5D
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	$last6D = $last6D
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	$last7D = $last7D
					->where(function($q) use($orgID){
						if($orgID==0){ return $q; }
						return $q->where('org_id', $orgID);
					})
					->select(DB::raw('count(*) as total'))
					->groupBy('user_id', 'org_id')
					->get(); 
	//---------------------------------------------------------------------------
	if($last1D==null){ $last1D=0; }
	else{ $last1D = count( $last1D ); }
	if($last2D==null){ $last2D=0; }
	else{ $last2D = count( $last2D ); }
	if($last3D==null){ $last3D=0; }
	else{ $last3D = count( $last3D ); }
	if($last4D==null){ $last4D=0; }
	else{ $last4D = count( $last4D ); }
	if($last5D==null){ $last5D=0; }
	else{ $last5D = count( $last5D ); }
	if($last6D==null){ $last6D=0; }
	else{ $last6D = count( $last6D ); }
	if($last7D==null){ $last7D=0; }
	else{ $last7D = count( $last7D ); }
	//---------------------------------------------------------------------------
	//---------------------------------------------------------------------------
	//---------------------------------------------------------------------------
?>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	//-----------------------------------------------------------------------
	const passWeekChatsData = {
		labels: ['<?=$day7c;?>', '<?=$day6c;?>', '<?=$day5c;?>', '<?=$day4c;?>', '<?=$day3c;?>', '<?=$day2c;?>', '<?=$day1c;?>'],
		datasets: [
			{
				label: 'Past Week Chats',
				fill: false,
				lineTension: 0.1,
				backgroundColor: 'rgba(75,192,192,0.4)',
				borderColor: 'rgba(75,192,192,1)',
				borderCapStyle: 'butt',
				borderDash: [],
				borderDashOffset: 0.0,
				borderJoinStyle: 'miter',
				pointBorderColor: 'rgba(75,192,192,1)',
				pointBackgroundColor: '#fff',
				pointBorderWidth: 1,
				pointHoverRadius: 5,
				pointHoverBackgroundColor: 'rgba(75,192,192,1)',
				pointHoverBorderColor: 'rgba(220,220,220,1)',
				pointHoverBorderWidth: 2,
				pointRadius: 1,
				pointHitRadius: 10,
				data: [<?=$last7D;?>, <?=$last6D;?>, <?=$last5D;?>, <?=$last4D;?>, <?=$last3D;?>, <?=$last2D;?>, <?=$last1D;?>, 1]
			}
		],
	};
	//-----------------------------------------------------------------------
	const last24HRSData = {
		labels: ['Last 6 hrs', '6-12 hrs ago', '12-18 hrs ago', '18-24 hrs ago'],
		datasets: [
			{
				label: 'Chats in last 24  hrs.',
				backgroundColor: 'rgba(99,99,255,0.5)',
				borderColor: 'rgba(132,99,255,0.2)',
				borderWidth: 1,
				hoverBackgroundColor: 'rgba(255,99,132,0.4)',
				hoverBorderColor: 'rgba(255,99,132,1)',
				data: [<?=$last6HC;?>, <?=$last12C;?>, <?=$last18C;?>, <?=$last24C;?>, 1]
			}
		]
	};
	//-----------------------------------------------------------------------
	const dayChatData = {
		labels: ['Very satisfied', 'Somewhat satisfied', 'Not satisfied', 'Did not answer'],
		datasets: [
			{
				label: '7 Day Chat Statisfacation [**under construction**]',
				backgroundColor: 'rgba(99,99,255,0.5)',
				borderColor: 'rgba(132,99,255,0.2)',
				borderWidth: 1,
				hoverBackgroundColor: 'rgba(255,99,132,0.4)',
				hoverBorderColor: 'rgba(255,99,132,1)',
				data: [0, 0, 0, 0],
				displays: ["Expert", "Advanced", "Intermediate", "Beginner"]
			}
		]
	};
	const dayChatOption =  {
		maintainAspectRatio: false,
/ *
pieceLabel: {
	mode: 'percentage',
	render: 'percentage',
//    fontColor: '#000',
//    position: 'outside'
	/ *
	mode: 'percentage',
	mode: 'value',
	render: function (args) {
	return args.value + '%';
	}
	* /
},
* /
		scales: {
			yAxes: [{
				scaleLabel: {
					display: true,
					labelString: 'Percent'
				},
				ticks: {
					beginAtZero:true,
					min: 0,
					max: 100
				}
			}],
			xAxes: [{
				scaleLabel: {
					display: false,
					labelString: 'X Axes'
				}
			}],
		}
	}
	//-----------------------------------------------------------------------
</script>
*/?>
<script src="/public/js/app.js"></script>

<script type="application/javascript">
	$("#orgID").on("change", function(){
		chatDuration(apiURL, chart1Config, chart1, chart1FLG);
		extendedDataTextIntroConfig(apiURL, chart2Config, chart2, chart2FLG);
		pairsDuring(apiURL, chart3Config, chart3, chart3FLG);
	});
</script>
