<style>
	div.card-detail{ min-height:80vh; }
	div.box_half{ display:inline-block;width:45%;margin:auto;vertical-align:middle;padding:10px;text-align:center; }
	
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
	
	.left{ width: 33%; display: inline-block; font-size: large;}
	.right{ width:65%; display:inline-block; vertical-align: middle; }
	
	.btn-group button{
		border: 1ps solid;
		border-radius: 5px !important;
		margin: 0 8px 8px;
	}
</style>

<div class="panel">
<!--
	<div class="panel-heading">Chat Duration</div> 
-->
	<div class="panel-body">
		<div class="left">
			Chat Duration.... 
		</div>
		<div class="right">
			<div style="text-align: center; margin-bottom: 10px;">
				<div class="btn-group" role="group" id="chatDuration_btn">
					<button type="button" class="btn btn-default" data-flag="1">1 year</button>
					<button type="button" class="btn btn-default" data-flag="2">1 month</button>
					<button type="button" class="btn btn-primary" data-flag="3">1 week</button>
					<button type="button" class="btn btn-default" data-flag="4">Last 24 hours</button>
				</div>
			</div>
			<div id="chatDuration"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
		</div>
	</div>
</div>
<script type="application/javascript">
	$(function(){
		$("#chatDuration").ready(function(){
			$("#chatDuration").html("<canvas></canvas>");
			var ctx = $("#chatDuration canvas")[0].getContext('2d');
			var chart = new Chart(ctx, chatDuration(3));
		});
		$("#chatDuration_btn button").on("click", function(){
			$("#chatDuration").html("<canvas></canvas>");
			var ctx = $("#chatDuration canvas")[0].getContext('2d');
			var chart = new Chart(ctx, chatDuration($(this).data('flag')));
			$("#chatDuration_btn button").attr('class', 'btn btn-default');
			$(this).attr('class', 'btn btn-primary');
		});
		
	});
	function chatDuration(flag){
		var data = null;
		switch(flag){
			case 1:
				data = [ 100, 200, 100, 150 ];
				break;
			case 2:
				data = [ 50, 150, 70, 50 ];
				break;
			case 3:
				data = [ 25, 50, 50, 10 ];
				break;
			case 4:
				data = [ 15, 20, 10, 10 ];
				break;
		}
		return {
			type: 'pie',
			data: {
				datasets: [{
					data: data,
					backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0'],
				}],
				labels: [
					'Below 5 sec',
					'5-30 sec',
					'30 sec-1 min',
					'>1 min'
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
				}
			}
		};
	}
</script>

<div class="panel">
<!--
	<div class="panel-heading">Extended Data Text Intro</div>
-->
	<div class="panel-body">
		<div class="left">
			Extended Data Text Intro.... 
		</div>
		<div class="right">
			<div style="text-align: center; margin-bottom: 10px;">
				<div class="btn-group" role="group" id="extendedDataTextIntro_btn">
					<button type="button" class="btn btn-default" data-flag="1">1 year</button>
					<button type="button" class="btn btn-default" data-flag="2">1 month</button>
					<button type="button" class="btn btn-primary" data-flag="3">1 week</button>
					<button type="button" class="btn btn-default" data-flag="4">Last 24 hours</button>
				</div>
			</div>
			<div id="extendedDataTextIntro"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
		</div>
	</div>
</div>
<script type="application/javascript">
	$(function(){
		$("#extendedDataTextIntro").ready(function(){
			$("#extendedDataTextIntro").html("<canvas></canvas>");
			var ctx = $("#extendedDataTextIntro canvas")[0].getContext('2d');
			var chart = new Chart(ctx, extendedDataTextIntroConfig(3));
		});
		$("#extendedDataTextIntro_btn button").on("click", function(){
			$("#extendedDataTextIntro").html("<canvas></canvas>");
			var ctx = $("#extendedDataTextIntro canvas")[0].getContext('2d');
			var chart = new Chart(ctx, extendedDataTextIntroConfig($(this).data('flag')));
			$("#extendedDataTextIntro_btn button").attr('class', 'btn btn-default');
			$(this).attr('class', 'btn btn-primary');
		});
		
	});
	function extendedDataTextIntroConfig(flag){
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

<div class="panel">
<!--
	<div class="panel-heading">% rate of "I don&#39;t know" answers to the total Q&amp;A pairs during the chat</div>
-->
	<div class="panel-body">
		<div class="left">
			% rate of "I don&#39;t know" answers to the total Q&amp;A pairs during the chat.... 
			
			<div style="margin-top: 10px">
				<button type="button" class="btn btn-info" data-toggle="modal" data-target="#seeList">See List</button>
				<div id="seeList" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<div class="btn-group" role="group" id="seeList_btn" style="width: 100%">
									<button type="button" class="btn btn-default" data-flag="1">1 year</button>
									<button type="button" class="btn btn-default" data-flag="2">1 month</button>
									<button type="button" class="btn btn-primary" data-flag="3">1 week</button>
									<button type="button" class="btn btn-default" data-flag="4">Last 24 hours</button>
									
									<button type="button" class="btn btn-link" style="float: right">Export to CSV</button>
									
								</div>
							</div>
							<div class="modal-body">
								<table id="example" class="table table-striped table-bordered" style="width:100%">
									<thead>
										<tr>
											<th>Org ID</th>
											<th>User ID</th>
											<th>Chat ID</th>
											<th>Failed Question</th>
											<th>Failed KR</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>1</td>
											<td>1</td>
											<td>1234</td>
											<td>xxxx 1</td>
											<td>yyyy 1</td>
										</tr>
										<tr>
											<td>1</td>
											<td>2</td>
											<td>4321</td>
											<td>xxxx 2</td>
											<td>yyyy 2</td>
										</tr>
									</tfoot>
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
			</div>
			<div id="pairsDuring"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
		</div>
	</div>
</div>
<script type="application/javascript">
	$(function(){
		$("#pairsDuring").ready(function(){
			$("#pairsDuring").html("<canvas></canvas>");
			var ctx = $("#pairsDuring canvas")[0].getContext('2d');
			var chart = new Chart(ctx, pairsDuringConfig);
		});
	});
	const pairsDuringConfig = {
		type: 'pie',
		data: {
			datasets: [{
				data: [
					Math.round(Math.random() * 100),
					Math.round(Math.random() * 100)
				],
				backgroundColor: ['#ff6384', '#36a2eb'],
//				hoverBackgroundColor: ['#f55', '#5f5', '#55f'],
//				hoverBorderColor: ['#000', '#000', '#000']
			}],
			labels: [
				"I don't know answers",
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
			}
		}
	};
</script>

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
					<button type="button" class="btn btn-default" data-flag="2" style="font-size:small">interval (1 year)</button>
					<button type="button" class="btn btn-default" data-flag="2" style="font-size:small">interval (1 month)</button>
					<button type="button" class="btn btn-default" data-flag="2" style="font-size:small">interval (1 week)</button>
					<button type="button" class="btn btn-default" data-flag="2" style="font-size:small">interval (24hours)</button>
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

<?php /*
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
*/ ?>
<?php 
/*
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
*/
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
/*
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
*/
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
<script src="/public/js/app.js"></script>
