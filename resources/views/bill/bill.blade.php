<?php
ob_start();
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
?>
<div style="font-size:24px; font-weight:bold;">Invoice</div>
<div>
	<table>
		<tr>
			<td style="width:50%; font-size:20px;">
				Customer Name: <?=$orgData->organizationShortName;?>
			</td>
			<td style="width:25%">&nbsp;</td>
			<td style="width:25%">&nbsp;</td>
		</tr>

		<tr>
			<td style="width:50%; font-size:14px;">
				Customer number: <b><?=$orgID;?></b>
			</td>
			<td style="width:25%">&nbsp;</td>
			<td style="width:25%">&nbsp;</td>
		</tr>

<?php
$tmp = new DateTime( "{$inDate}-01" );
$endDate = $tmp->format( 'Y-m-t' );
?>
		<tr>
			<td style="width:50%; font-size:20px;">
				Report date <?=date("F d Y");?> 
			</td>
			<td style="width:50%; font-size:14px;" colspan="2">
				Report Start date: <?=date("F d Y", strtotime("{$inDate}-01"));?>&nbsp;
				Report End Date: <?=date("F d Y", strtotime($endDate));?>
			</td>
		</tr>

		<tr>
			<td style="width:50%; font-size:20px;">&nbsp;</td>
			<td style="width:25%">&nbsp;</td>
			<td style="width:25%">&nbsp;</td>
		</tr>

		<tr>
			<td style="width:50%; font-size:18px;">&nbsp;
				Conversational Volume (Q&A Pairs)
			</td>
			<td style="width:25%">&nbsp;</td>
			<td style="width:25%;font-size:18px;">Virtual Agents</td>
		</tr>
<?php
	$logs=\App\KamaUsage::where('org_id', $orgID)
				->whereBetween('timestamp', [$inDate."-01", $inDate."-31"])->count();

	$tiers = \App\Tier::where('orgID', $orgID)->orderBy('sequence', 'asc')->get();
	if($tiers->isEmpty()){
		$tiers = \App\Tier::where('orgID', 0)->orderBy('sequence', 'asc')->get();
	}
	if(!$tiers->isEmpty()){
		$last = 0;
		$setNull = false;
		$tmpVT = 0;
		foreach($tiers as $tier){
			$tmpC = 0;
			$tmpR = 0;
			if(!$setNull){
				if($tier->high!=0){
					if($logs>$tier->high){
						if(($logs-($last+$tier->high))>0){
							$tmpC = $tier->high;
							$tmpR = $tier->high*$tier->rate;
						}else{
							$tmpC = ($logs-$last);
							$tmpR = ($logs-$last)*$tier->rate;
						}
					}else{
						$tmpC = ($logs-$last);
						$tmpR = ($logs-$last)*$tier->rate;
					}
				}else{
					$tmpR = ($logs-$last)*$tier->rate;
					$tmpC = ($logs-$last);
				}
				if($tmpC<=0){
					$setNull = true;
					continue;
				}
				$last += $tier->high;
				if($tier->high==0){
					$tmpV = ceil($tmpC/$tier->low);
				}else{
					$tmpV = ceil($tmpC/$tier->high);
				}
				$tmpVT += $tmpV;
				$tmpC = number_format($tmpC,0,'.',',');
				$tmpR = number_format($tmpR,0,'.',','). '$';
			}
			?>
			<?php if(!$setNull): ?>
			<tr>
				<td style="width:50%; font-size:18px; padding-left:10px;">
					<?=$tier->caption;?>
				</td>
				<td style="width:25%; font-size:18px; text-align:left">
					<div style="max-width:50px; text-align: right;"><?=$tmpC;?></div>
				</td>
				<td style="width:25%; font-size:18px; text-align:left">
					<div style="max-width:50px; text-align: right;"><?=$tmpV;?></div>
				</td>
			</tr>
			<?php endif; ?>
			<?php
			if($logs<$tier->high){ $setNull = true; }
		}
	}
?>
		<tr>
			<td style="width:50%; font-size:18px; padding-left:10px;">
				Total:
			</td>
			<td style="width:25%; font-size:18px; text-align:left" >
				<div style="max-width:50px; text-align: right;"><?=number_format($logs,0,'.',',');?></div>
			</td>
			<td style="width:25%; font-size:18px; text-align:left" >
				<div style="max-width:50px; text-align: right;"><?=number_format($tmpVT,0,'.',',');?></div>
			</td>
		</tr>

		<tr>
			<td style="width:50%; font-size:18px; padding-left:10px;">
				RPA Status:
			</td>
			<td style="width:50%; font-size:18px; text-align:left" colspan="2">
				<?=(($orgData->RPA==1) ?"ON" :"OFF");?>
			</td>
		</tr>

		<tr>
			<td style="width:50%; font-size:18px; padding-left:10px;">
				Multi-Language Status:
			</td>
			<td style="width:50%; font-size:18px; text-align:left" colspan="2">
				<?=(($orgData->MultiLanguage==1) ?"ON" :"OFF");?>
			</td>
		</tr>

		<tr>
			<td style="width:100%; font-size:20px;" colspan="3">&nbsp;</td>
		</tr>

		<tr>
			<td style="width:50%; font-size:18px; padding-left:10px;">
				Percentage Usage Report by Portal:
			</td>
			<td style="width:50%; font-size:14px; text-align:left" colspan="2">&nbsp;</td>
		</tr>

<?php
	$list = [];
	$portalName = [
		' '=>'unknown',
		'1'=>'Chatbot',
		'2'=>'LEX',
		'3'=>'test',
		'4'=>'Facebook',
		'z'=>'Alexa'
	];
$usages =
	\App\KamaUsage::where('org_id', $orgID)->
						whereBetween('timestamp', [$inDate."-01", $inDate."-31"])->
						select(
							"apikey as key"
						)->
						get();
foreach($usages as $usage){
	$portal = strtolower(substr($usage->key, 0, 6));
	if(!array_key_exists($portal, $list)){
		$list[$portal]=1;
	}else{
		$list[$portal]++;
	}
}
ksort($list);
foreach($list as $key=>$value){
	if($value==0){ continue; }
	?>
		<tr>
			<td style="width:50%; font-size:14px; padding-left:30px; height:20px;">
				Portal ID: <span style="width:50px; display:inline-block; text-align:left; padding-left:7px;vertical-align:bottom; height:18px;"><?=$key;?></span>
				<?php if(isset($portalName[substr($key, 0, 1)])): ?>
				Portal Type: <b><?=$portalName[substr($key, 0, 1)];?></b>
				<?php else: ?>
				Portal Type: <b><?=$key;?></b>
				<?php endif; ?>
			</td>
			<td style="width:50%; font-size:14px; text-align:left" colspan="2">
				<div style="max-width:50px; text-align: right;"><?=number_format(($value/$logs)*100, 2, '.', '');?>%</div>
			</td>
		</tr>
	<?php
}
?>

	</table>
</div>

<?php /*
<div>
	<ul>
		<li>
			Percentage Usage Report by Portal:
			<ol>
			<?php
				$list = [
					[' ', 'unknown', 0],
					['1', 'chatbot', 0],
					['2', 'lex', 0],
					['3', 'test', 0],
					['4', 'facebook', 0],
					['z', 'alexa', 0],
				];
			$usages =
				\App\KamaUsage::where('org_id', $orgID)->
									whereBetween('timestamp', [$inDate."-01", $inDate."-31"])->
									select('apikey')->
									get();
			foreach($usages as $usage){
				switch(strtolower(substr($usage->apikey, 0, 1))){
					case '1':{ //chatbot
						$list[1][2]++;
						continue;
					}
					case '2':{ //lex
						$list[2][2]++;
						continue;
					}
					case '3':{ //test
						$list[3][2]++;
						continue;
					}
					case '4':{ //facebook
						$list[4][2]++;
						continue;
					}
					case 'z':{ //alexa
						$list[5][2]++;
						continue;
					}
					default:{
						$list[0][2]++;
						continue;
					}
				}
			}
			foreach($list as $tmp){
				if($tmp[2]==0){ continue; }
				?>
				<li>Portal ID:[<?=$tmp[0];?>] Portal Type:[<?=$tmp[1];?>] %<?=number_format(($tmp[2]/$logs)*100, 2, '.', '');?></li>
				<?php
			}
			?>
			</ol>
		</li>
	</ul>
</div>
*/ ?>
<?php
$output = ob_get_clean();
ob_end_clean();

$pdf = App::make('dompdf.wrapper');
$pdf->loadHTML($output);
$pdfName = 
	"pdf/".
	str_replace(" ", "" ,strtolower($orgData->organizationShortName)).
	".".
	date("Ymd", strtotime("{$inDate}-01")).
	".".
	date("Ymd", strtotime($endDate)).
	".pdf";
$pdf->save($pdfName);
echo $output;
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
<a href="/<?=$pdfName;?>" download class="btn">Download PDF</a>