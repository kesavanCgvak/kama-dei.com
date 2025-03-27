sep=~
<?php $kamaOrg   = \App\Organization::find($orgID); ?>
<?php $kamaUsage = \App\KamaUsage::find($log_id); ?>
<?php /*
User ID~<?=$kamaUsage->user_id."\n";?>
Memo~<?=$kamaUsage->memo."\n";?>
Org ID~<?=$kamaUsage->org_id."\n";?>
Instructions~<?=str_replace("\n",'<br/>',$instructions)."\n";?>
EmailBody~<?=$kamaOrg->EmailBody."\n";?>
*/ ?>
row~Org Name~Email~Chat start time~consumer user~datetime~Utterance~Response<?="\n";?>
<?php
//----------------------------------------------
$row           = 1;
$OrgName       = $kamaUsage->org_name;
$consumerUser  = $kamaUsage->user_name;
$Email         = $kamaUsage->email;
$ChatStartTime = $kamaUsage->timestamp;

$datetime  = "";
$Response  = "";
$utterance = "";
//----------------------------------------------
$kamaLogs = \App\KamaLog::where('signin_id', $log_id)->orderby('msg_id', 'asc')->get();
if($kamaLogs!=null){
	$tempdata = [];
	foreach($kamaLogs as $kamaLog){
		$datetime = $kamaLog->timestamp;
		if($kamaLog->sender=='user'){
			$utterance = "";
			try{
				$tmpTMP = json_decode($kamaLog->raw_msg);
				if(count($tmpTMP->answers)==0){
					$utterance=$tmpTMP->utterance;
					if(isset($tempdata[$utterance])){ $utterance=$tempdata[$utterance]; }
				}else{
					$utterance = "";
					foreach($tmpTMP->answers as $tmpANS){
						$utterance.="|{$tmpANS->text} : [ {$tmpANS->value} ]";
					}
					$ttmmpp = $tmpTMP->utterance;
					if(isset($tempdata[$ttmmpp])){ $ttmmpp=$tempdata[$ttmmpp]; }
					$utterance.="|{$ttmmpp}";
				}
			}catch(\Exception $ex){
			}
		}else{
			$Response = "";
			$newM = json_decode($kamaLog->raw_msg);
			switch($newM->response->type){
				//-------------------------------------------------------------------------------------------------------
				case 'radiobutton':{
					$Response = '';
					if(isset($newM->response->answers) && is_array($newM->response->answers)){
						foreach( $newM->response->answers as $tmp){
							$text = (isset($tmp->text) ?$tmp->text: false);
							if($text==false){ $text = (isset($tmp->url) ?$tmp->url: false); }
							if($text==false){ continue; }
							$tempdata[$tmp->value]=$text;
							$Response .=(($Response=="") ?"{$text}" :"|{$text}");
						}
					}
					if(isset($newM->response->slidebar) && is_array($newM->response->slidebar)){
						foreach( $newM->response->slidebar as $tmp){
							$text = "{$tmp->name} [ {$tmp->value2} , {$tmp->value3} ] : {$tmp->value1}";
							$Response .=(($Response=="") ?"{$text}" :"|{$text}");
						}
					}
					if(isset($newM->response->buttons) && is_array($newM->response->buttons)){
						foreach( $newM->response->buttons as $tmp){
							$text = $tmp->text;
							$tempdata[$tmp->value]=$text;
							$Response .=(($Response=="") ?"{$text}" :"|{$text}");
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
					$Response =(($msgVal=="") ?"{$Response}" :"$msgVal|{$Response}");
					break;
				}
				//-------------------------------------------------------------------------------------------------------
				case 'yesno':{
					$Response =$newM->response->message."|Yes|No";
					$tempdata["Yes"]="Yes";
					$tempdata["No" ]="No";
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
					$Response = $msgVal;
					break;
				}
				//-------------------------------------------------------------------------------------------------------
				default:{
					$Response = $newM->response->message;
					break;
				}
				//-------------------------------------------------------------------------------------------------------
			}
?>
<?=$row++;?>~<?="{$OrgName}~{$Email}~{$ChatStartTime}~{$consumerUser}~{$datetime}";?>~<?=$utterance;?>~<?=$Response;?><?="\n";?>
<?php
		}
	}
}
