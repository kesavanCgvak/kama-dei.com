<!DOCTYPE html>
<html>
<head>
	<title>Kama Log</title>
</head>
<body>
	<link href="https://use.fontawesome.com/3d00a0f028.css" media="all" rel="stylesheet">
	<script src="https://use.fontawesome.com/3d00a0f028.js"></script>
	<h1>Kama-DEI chat log</h1>
	<?php $kamaOrg   = \App\Organization::find($orgID); ?>
	<?php $kamaUsage = \App\KamaUsage::find($log_id); ?>
	<h3><?=str_replace("\n",'<br/>',$instructions);?></h3>
	<div style="margin-bottom:10px;"><?=str_replace("\n",'<br/>',$kamaOrg->EmailBody);?></div>
	<table width="100%" cellspacing="0" cellpadding="5" border="1">
	<tbody>
		<tr><td colspan="2" class=""><div ><?=$kamaUsage->memo;?></div></td></tr>
<!--		<tr><td><b>IP</b></td><td><?=$kamaUsage->ip;?></td></tr> -->
		<tr>
			<td><b>Chat start time</b></td>
			<td>&nbsp;<?=$kamaUsage->timestamp;?></td>
		</tr>
		<tr><td><b>User ID</b></td><td>&nbsp;<?=$kamaUsage->user_id;?></td></tr>
		<tr><td><b>User Name</b></td><td>&nbsp;<?=$kamaUsage->user_name;?></td></tr>
		<tr><td><b>Org ID</b></td><td>&nbsp;<?=$kamaUsage->org_id;?></td></tr>
		<tr><td><b>Org Name</b></td><td>&nbsp;<?=$kamaUsage->org_name;?></td></tr>
		<tr>
			<td style="width:10%;min-width:150px;"><b>Email</b></td>
			<td style="max-width:85%;word-break:break-all;">&nbsp;<?=$kamaUsage->email;?></td>
		</tr>
	</tbody>
	</table>
	<br/>
	<table width="100%" cellspacing="5" cellpadding="5" border="0">
	<tbody>
		<?php
			$kamaLogs = \App\KamaLog::where('signin_id', $log_id)
				->get();
			if($kamaLogs!=null){
				$tempdata = [];
				foreach($kamaLogs as $kamaLog){
					?><tr><?php
					if($kamaLog->sender=='AI'){
						$newM = json_decode($kamaLog->raw_msg);
						$feedback = \App\Feedback::where('message_id', $kamaLog->msg_id)->first();

						switch( $newM->response->type ){
							//-------------------------------------------------------------------------------------------------------
							case 'radiobutton':{
								$rd = '';
								if(isset($newM->response->answers) && is_array($newM->response->answers)){
									foreach( $newM->response->answers as $tmp){
										$text = (isset($tmp->text) ?$tmp->text: false);
										if($text==false){ $text = (isset($tmp->url) ?$tmp->url: false); }
										if($text==false){ continue; }
										$tempdata[$tmp->value]=$text;
										$rd .="<span style='box-sizing: border-box; border:1px solid rgb(140, 198, 63); border-radius:4px;    color:rgb(140, 198, 63); margin:2px; padding:5px; font-size:12px; display: inline-block;'>{$text}</span>";
									}
								}
								if(isset($newM->response->slidebar) && is_array($newM->response->slidebar)){
									foreach( $newM->response->slidebar as $tmp){
										$text = "{$tmp->name} [ {$tmp->value2} , {$tmp->value3} ] : {$tmp->value1}";
										$rd .="<span style='box-sizing: border-box; border:1px solid rgb(140, 198, 63); border-radius:4px;    color:rgb(140, 198, 63); margin:2px; padding:5px; font-size:12px; display: inline-block;'>{$text}</span>";
									}
								}
								if(isset($newM->response->buttons) && is_array($newM->response->buttons)){
									foreach( $newM->response->buttons as $tmp){
										$text = $tmp->text;
										$tempdata[$tmp->value]=$text;
										$rd .="<span style='box-sizing: border-box; border:1px solid rgb(140, 198, 63); border-radius:4px;    color:rgb(140, 198, 63); margin:2px; padding:5px; font-size:12px; display: inline-block;'>{$text}</span>";
									}
								}
								?>
								<?php
								$msgVal = $newM->response->message;
								try{
									if($msgVal==''){
										foreach($newM->response->messages as $newMResponseMessages){
											$msgVal .= $newMResponseMessages->value." ";
										}
									}
								}catch(\Exception $ex){}
								?>
								<td style="width:60%;color:#6f6f6f; background:#efefef; line-height:20px;">
									<?=$kamaUsage->org_name;?><br/>
									<?=$msgVal;?><br/>
									<?=$kamaLog->timestamp;?><br/>
									<?=$rd;?>
@if($feedback!=null)
	<div>
		@if($feedback->feedback==1)
		<i>feedback:<b><?=$feedback->feedback;?></b> <img src="<?=env('API_URL');?>/public/dist/images/thumbs-up.svg" /></i>
		@else
		<i>feedback:<b><?=$feedback->feedback;?></b> <img src="<?=env('API_URL');?>/public/dist/images/thumbs-down.svg" /></i>
		@endif
	</div>
@endif
								</td>
								<td style="width:40%;"></td>
								<?php
								//continue;
								break;
							}
							//-------------------------------------------------------------------------------------------------------
							case 'text':{
								?>
								<?php
								$msgVal = $newM->response->message;
								try{
									if($msgVal==''){
										foreach($newM->response->messages as $newMResponseMessages){
											$msgVal .= $newMResponseMessages->value." ";
										}
									}
								}catch(\Exception $ex){}
								?>
								<td style="width:60%;color:<?=(isset($newM->response->err) ?'#ff0000' :'#6f6f6f');?>; background:#efefef; line-height:20px;">
									<?=$kamaUsage->org_name;?><br/>
									<?=$msgVal;?><br/>
									<?=$kamaLog->timestamp;?>
@if($feedback!=null)
	<div>
		@if($feedback->feedback==1)
		<i>feedback:<b><?=$feedback->feedback;?></b> <img src="<?=env('API_URL');?>/public/dist/images/thumbs-up.svg" /></i>
		@else
		<i>feedback:<b><?=$feedback->feedback;?></b> <img src="<?=env('API_URL');?>/public/dist/images/thumbs-down.svg" /></i>
		@endif
	</div>
@endif
								</td>
								<td style="width:40%;"></td>
								<?php
								//continue;
								break;
							}
							//-------------------------------------------------------------------------------------------------------
							case 'yesno':{
								?>
								<td style="width:60%;color:#6f6f6f; background:#efefef; line-height:20px;">
									<?=$kamaUsage->org_name;?><br/>
									<?=$newM->response->message;?><br/>
									<?=$kamaLog->timestamp;?><br/>
									<span style='box-sizing: border-box; border:1px solid rgb(140, 198, 63); border-radius:4px;    color:rgb(140, 198, 63); margin:2px; padding:5px; font-size:12px; display: inline-block;'>Yes</span>
									<span style='box-sizing: border-box; border:1px solid rgb(140, 198, 63); border-radius:4px;    color:rgb(140, 198, 63); margin:2px; padding:5px; font-size:12px; display: inline-block;'>No</span>
@if($feedback!=null)
	<div>
		@if($feedback->feedback==1)
		<i>feedback:<b><?=$feedback->feedback;?></b> <img src="<?=env('API_URL');?>/public/dist/images/thumbs-up.svg" /></i>
		@else
		<i>feedback:<b><?=$feedback->feedback;?></b> <img src="<?=env('API_URL');?>/public/dist/images/thumbs-down.svg" /></i>
		@endif
	</div>
@endif
								</td>
								<td style="width:40%;"></td>
								<?php
								$tempdata["Yes"]="Yes";
								$tempdata["No" ]="No";
								//continue;
								break;
							}
							//-------------------------------------------------------------------------------------------------------
							default:{
								?>
								<td style="width:60%;color:#6f6f6f; background:#efefef; line-height:20px;">
									<?=$kamaUsage->org_name;?><br/>
									<?=$newM->response->message;?><br/>
									<?=$kamaLog->timestamp;?>
@if($feedback!=null)
	<div>
		@if($feedback->feedback==1)
		<i>feedback:<b><?=$feedback->feedback;?></b> <img src="<?=env('API_URL');?>/public/dist/images/thumbs-up.svg" /></i>
		@else
		<i>feedback:<b><?=$feedback->feedback;?></b> <img src="<?=env('API_URL');?>/public/dist/images/thumbs-down.svg" /></i>
		@endif
	</div>
@endif
								</td>
								<td style="width:40%;"></td>
								<?php
								//continue;
								break;
							}
							//-------------------------------------------------------------------------------------------------------
						}
						/*
						$feedback = \App\Feedback::where('message_id', $kamaLog->msg_id)->first();
						if($feedback!=null){
						?>
							<div>
								@if($feedback->feedback==1)
								<b>feedback:1</b>
								<i class="fa fa-thumbs-o-up"></i>
								@else
								<b>feedback:0</b>
								<i class="fa fa-thumbs-o-down"></i>
								@endif
							</div>
						<?php
						}
						*/
						continue;
					}else{
						$temp_mmsg=$kamaLog->raw_msg;
						if(isset($tempdata[$temp_mmsg])){ $temp_mmsg=$tempdata[$temp_mmsg]; }
						if($kamaLog->sender=='sender'){
						?>
							<td style="width:60%"></td>
							<td style="width:40%;color:#6f6f6f; background:#efefef; line-height:20px;">
								Kamazooie Development<br/>
								<?=$temp_mmsg;?><br/>
								<?=$kamaLog->timestamp;?>
							</td>
						<?php
						}else{
							try{ $tmpTMP = json_decode($kamaLog->raw_msg);
								if(count($tmpTMP->answers)==0){
									$temp_mmsg=$tmpTMP->utterance;
									if(isset($tempdata[$temp_mmsg])){ $temp_mmsg=$tempdata[$temp_mmsg]; }
								}else{
									$temp_mmsg = "";
									foreach($tmpTMP->answers as $tmpANS){
										$temp_mmsg.="<span>{$tmpANS->text} : [ {$tmpANS->value} ]</span><br/>";
									}
									$ttmmpp = $tmpTMP->utterance;
									if(isset($tempdata[$ttmmpp])){ $ttmmpp=$tempdata[$ttmmpp]; }
									$temp_mmsg.=$ttmmpp;
								}
							}catch(\Exception $ex){
//$temp_mmsg=$ex->getMessage();
							}
						?>
							<td style="width:60%"></td>
							<td style="width:40%;color:#fff; background:rgb(140, 198, 63); line-height:20px;">
								<?=$kamaUsage->user_name;?><br/>
								<?=$temp_mmsg;?><br/>
								<?=$kamaLog->timestamp;?>
							</td>
						<?php
						}
					}
					?></tr><?php
				}
			}
		?>
	</tbody>
	</table>
	<h4 style="margin:5px 0;"><?=$kamaOrg->Footer;?></h4>
	<?php /*
	<div style="margin:5px 0;"><?=($kamaOrg->FooterUrlDisplay==1) ?"<a href='{$kamaOrg->FooterUrl}' target='_blank'>{$kamaOrg->FooterUrl}</a>" :'';?></div>
	*/?>
	<div style="margin: 5px 0">
		<?php /* if($kamaOrg->organizationLogo!='' || $kamaOrg->organizationLogo!=null): ?>
		<img src='<?=$kamaOrg->organizationLogo;?>' style="max-width:200px;background: black "/>
		<?php / *<h4><?=$kamaOrg->Slogan;?></h4> * / ?>
		<?php endif; */?>
	</div>
</body>
</html>
