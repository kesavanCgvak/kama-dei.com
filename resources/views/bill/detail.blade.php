<?php
/*
portalCoed:
	1:chatbot
	2:lex
	3:test
	4:facebook
	z:alexa
*/
//-------------------------------------------------------
$orgData = \App\Organization::find($orgID);
//-------------------------------------------------------
$tmp = new DateTime( "{$inDate}-01" );
$endDate = $tmp->format( 'Y-m-t' );
//-------------------------------------------------------
$csvName = 
	"csv/".
	str_replace(" ", "" ,strtolower($orgData->organizationShortName)).
	".".
	date("Ymd", strtotime("{$inDate}-01")).
	".".
	date("Ymd", strtotime($endDate)).
	".csv";
//-------------------------------------------------------
$csv = fopen($csvName, "w+");
fwrite($csv, "Low Level Customer Volume Report (for invoice validation)"."\r\n");
?>
<div style="font-size:22px; font-weight:bold;">
	Low Level Customer Volume Report (<small style="font-weight:normal;">for invoice validation</small>)
</div>
<div>
	<table>
		<tr>
			<td style="width:50%; font-size:20px;">
				Customer Name: <?=$orgData->organizationShortName;?>
				<?php fwrite($csv, "Customer Name: ".$orgData->organizationShortName."\r\n"); ?>
			</td>
			<td style="width:50%">&nbsp;
				
			</td>
		</tr>

		<tr>
			<td style="width:50%; font-size:14px;">
				Customer number: <b><?=$orgID;?></b>
				<?php fwrite($csv, "Customer Number: ".$orgID."\r\n"); ?>
			</td>
			<td style="width:50%">&nbsp;
				
			</td>
		</tr>

		<tr>
			<td style="width:50%; font-size:20px;">
				Report date <?=date("F d Y");?> 
				<?php fwrite($csv, "Report date ".date("F d Y").","); ?>
			</td>
			<td style="width:50%; font-size:14px;">
				Report Start date: <?=date("F d Y", strtotime("{$inDate}-01"));?>&nbsp;
				<?php fwrite($csv, "Report Start date: ".date("F d Y", strtotime("{$inDate}-01"))." "); ?>
				Report End Date: <?=date("F d Y", strtotime($endDate));?>
				<?php fwrite($csv, "Report End Date: ".date("F d Y", strtotime($endDate))."\r\n"); ?>
			</td>
		</tr>

		<tr>
			<td style="width:50%; font-size:20px;">&nbsp;
				
			</td>
			<td style="width:50%; font-size:14px;">&nbsp;
				
			</td>
		</tr>

	</table>
</div>

<div>
	<table border="1" cellpadding="5" cellspacing="5" style="width: 700px;">
		<thead>
			<tr>
			<th style="width:100px;">Date</th>
			<th style="width:100px;">Time</th>
			<th style="width:100px;">Portal ID</th>
			<th style="width:120px;">Portal Type</th>
			<th style="width: 80px;">User ID</th>
			<th style="width:200px;">No. of Request/Response</th>
			</tr>
			<?php
				fwrite($csv, "Date,Time,Portal ID,Portal Type,User ID,No. of Request/Response"."\r\n");
			?>
		</thead>
		<tbody>
			<?php
				$portalName = [
					' '=>'unknown',
					'1'=>'Chatbot',
					'2'=>'LEX',
					'3'=>'test',
					'4'=>'Facebook',
					'z'=>'Alexa'
				];
				$usages=\App\KamaUsage::where('org_id', $orgID)->whereBetween('timestamp', [$inDate."-01", $inDate."-31"])->get();
				if(!$usages->isEmpty()){
					foreach($usages as $usage){
						$logs=\App\KamaLog::where('signin_id', $usage->signin_id)->count();
						?>
						<tr>
							<td><?=date("Y-m-d", strtotime($usage->timestamp));?></td>
							<td><?=date("H:i:s", strtotime($usage->timestamp));?></td>
							<td><?=substr($usage->apikey, 0, 6);?></td>
							<td>
								<?php if(isset($portalName[strtolower(substr($usage->apikey, 0, 1))])): ?>
								<?=$portalName[strtolower(substr($usage->apikey, 0, 1))];?>
								<?php else: ?>
								<?=substr($usage->apikey, 0, 6);?>
								<?php endif; ?>
							</td>
							<td><?=$usage->user_id;?></td>
							<td>
								<?php if($logs>0): ?>
								<?=$logs;?>
								<?php else: ?>
								&nbsp;
								<?php endif; ?>

							</td>
						</tr>
						<?php
						fwrite($csv, date("Y-m-d", strtotime($usage->timestamp)).",");
						fwrite($csv, date("H:i:s", strtotime($usage->timestamp)).",");
						fwrite($csv, substr($usage->apikey, 0, 6).",");
						if(isset($portalName[strtolower(substr($usage->apikey, 0, 1))])){
							fwrite($csv, $portalName[strtolower(substr($usage->apikey, 0, 1))].",");
						}else{
							fwrite($csv, substr($usage->apikey, 0, 6).",");
						}
						fwrite($csv, $usage->user_id.",");
						if($logs>0){
							fwrite($csv, $logs."\r\n");
						}else{
							fwrite($csv, "0"."\r\n");
						}
					}
				}
			?>
		</tbody>
	</table>
</div>
<?php
fclose($csv);
?>
<style>
	a.btn{
		position: fixed;
		bottom: 0;
		right: 5px;
		display: inline-block;
		width: 120px;
		background: #27a030;
		color: white;
		padding: 10px 15px 10px 10px;
		text-align: center;
		text-decoration: none;
		border-radius: 5px;
		cursor: pointer;
	}
	a.btn:hover{
		cursor: pointer;
		background: #12d421;
	}
</style>
<a href="/<?=$csvName;?>" download class="btn">Download CSV</a>