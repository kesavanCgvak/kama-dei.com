sep=~
<?php $kamaOrg   = \App\Organization::find($orgID); ?>
<?php $kamaUsage = \App\KamaUsage::find($log_id); ?>
<?php /*
User ID~<?=$kamaUsage->user_id."\n";?>
Memo~<?=$kamaUsage->memo."\n";?>
Org ID~<?=$kamaUsage->org_id."\n";?>
Instructions~<?=str_replace("\n",'<br/>',$instructions)."\n";?>
*/ ?>
User Name~<?=$kamaUsage->user_name."\n";?>
Org Name~<?=$kamaUsage->org_name."\n";?>
Email~<?=$kamaUsage->email."\n";?>
Chat start time~<?=$kamaUsage->timestamp."\n";?>
EmailBody~<?=$kamaOrg->EmailBody."\n";?>


<?php
	$kamaLogs = \App\KamaLog::where('signin_id', $log_id)->get();
	if($kamaLogs!=null){
		$tempdata = [];
		foreach($kamaLogs as $kamaLog){
			if($kamaLog->sender=='AI'){
				$newM = json_decode($kamaLog->raw_msg);

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
								$rd .="|{$text}";
							}
						}
						if(isset($newM->response->slidebar) && is_array($newM->response->slidebar)){
							foreach( $newM->response->slidebar as $tmp){
								$text = "{$tmp->name} [ {$tmp->value2} , {$tmp->value3} ] : {$tmp->value1}";
								$rd .="|{$text}";
							}
						}
						if(isset($newM->response->buttons) && is_array($newM->response->buttons)){
							foreach( $newM->response->buttons as $tmp){
								$text = $tmp->text;
								$tempdata[$tmp->value]=$text;
								$rd .="|{$text}";
							}
						}

						$msgVal = $newM->response->message;
						try{
							if($msgVal==''){
								foreach($newM->response->messages as $newMResponseMessages){
									$msgVal .= ("{$newMResponseMessages->value}".(($msgVal!='')?"|" :""));
								}
							}
						}catch(\Exception $ex){}
?>
Org Name~<?=$kamaUsage->org_name."\n";?>
Response~<?=$msgVal;?><?=$rd;?><?="\n";?>
Date Time~<?=$kamaLog->timestamp;?><?="\n";?>
<?php
						break;
					}
					//-------------------------------------------------------------------------------------------------------
					case 'text':{
						$msgVal = $newM->response->message;
						try{
							if($msgVal==''){
								foreach($newM->response->messages as $newMResponseMessages){
									$msgVal .= ("{$newMResponseMessages->value}".(($msgVal!='')?"|" :""));
								}
							}
						}catch(\Exception $ex){}
?>
Org Name~<?=$kamaUsage->org_name."\n";?>
Response~<?=$msgVal;?><?="\n";?>
Date Time~<?=$kamaLog->timestamp;?><?="\n";?>
<?php
						break;
					}
					//-------------------------------------------------------------------------------------------------------
					case 'yesno':{
?>
Org Name~<?=$kamaUsage->org_name."\n";?>
Response~<?=$newM->response->message;?>|Yes|No<?="\n";?>
Date Time~<?=$kamaLog->timestamp;?><?="\n";?>
<?php
						$tempdata["Yes"]="Yes";
						$tempdata["No" ]="No";
						break;
					}
					//-------------------------------------------------------------------------------------------------------
					default:{
?>
Org Name~<?=$kamaUsage->org_name."\n";?>
Response~<?=$newM->response->message;?><?="\n";?>
Date Time~<?=$kamaLog->timestamp;?><?="\n";?>
<?php
						break;
					}
					//-------------------------------------------------------------------------------------------------------
				}
				continue;
			}else{
				$temp_mmsg=$kamaLog->raw_msg;
				if(isset($tempdata[$temp_mmsg])){ $temp_mmsg=$tempdata[$temp_mmsg]; }
				if($kamaLog->sender=='sender'){
?>
Kamazooie Development~<?=$temp_mmsg;?><?="\n";?>
Date Time~<?=$kamaLog->timestamp;?><?="\n";?>
<?php
				}else{
					try{ $tmpTMP = json_decode($kamaLog->raw_msg);
						if(count($tmpTMP->answers)==0){
							$temp_mmsg=$tmpTMP->utterance;
							if(isset($tempdata[$temp_mmsg])){ $temp_mmsg=$tempdata[$temp_mmsg]; }
						}else{
							$temp_mmsg = "";
							foreach($tmpTMP->answers as $tmpANS){
								$temp_mmsg.="|{$tmpANS->text} : [ {$tmpANS->value} ]";
							}
							$ttmmpp = $tmpTMP->utterance;
							if(isset($tempdata[$ttmmpp])){ $ttmmpp=$tempdata[$ttmmpp]; }
							$temp_mmsg.="|{$ttmmpp}";
						}
					}catch(\Exception $ex){
					}
?>
<?=$kamaUsage->user_name;?>~<?=$temp_mmsg;?><?="\n";?>
Date Time~<?=$kamaLog->timestamp;?><?="\n";?>
<?php
				}
			}
		}
	}
?>
Footer~<?=$kamaOrg->Footer;?><?="\n";?>
