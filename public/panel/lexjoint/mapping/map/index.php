<?php
$bot_id = $requestTableName;
$mapBotRecord = \App\LexMapBots::myMap($bot_id);
if($mapBotRecord==null){
	?><script type="application/javascript">window.location.href='<?=env('API_URL');?>/panel/lexjoint/mapping';</script><?php
	return;
}
if($orgID!=0 && $orgID!=$mapBotRecord->ownerId){
	?><script type="application/javascript">window.location.href='<?=env('API_URL');?>/panel/lexjoint/mapping';</script><?php
	return;
}
?>
<style>
	div.intent_data{ width:35%; }
	div.intent_kr{ width:55%; }
	div.intent_data,
	div.intent_kr{ display:inline-block; min-height:356px; margin:0 2%;padding:10px 5px;vertical-align:top; }
	textarea.sampleUtterance{ max-height:100px;height:100px;min-height:100px;width:100%;min-width:100%;max-width:100%; }
	
	.btn.btn-info.btn-mapping{ padding:1px 5px;font-size:12px; }

	.th-inner, .table.table-hover td{ font-size:13px; }
	ul.ui-menu.ui-widget.ui-widget-content.ui-autocomplete.ui-front{ max-height:200px; overflow:auto; }
	button.updateBOT{ margin:10px; padding:3px 15px; font-size:12px; }
</style>
<div style="width:100%;text-align:left;margin:auto;">
	<button class="btn btn-link" style="float: right" onClick="window.location.href='<?=env('API_URL');?>/panel/lexjoint/mapping';">Back</button>
	<span style="float:right">
		<small style="margin-top:-12px;display:block" id="publish_status">Status: <?=$mapBotRecord->publish_status;?></small>
		<button class="btn btn-success Unpublished" style="display:none;width:100%;" onClick="published('Published');">Publish</button>
		<button class="btn btn-danger Published" style="display:none;width:100%;" onClick="published('Unpublished');">Unpublish</button>
	</span>
	<div class="input-group m-b-1" style="max-width:70%">
		<span class="input-group-addon">Mapping Name</span>
		<input class="form-control" id="mappingName" value="<?=$mapBotRecord->mappingName;?>" maxlength="200" disabled  />
	</div>
	<div class="input-group m-b-1" style="width:100%;margin-top:15px;">
		<label>Organization: </label><span><?=$mapBotRecord->organizationShortName;?></span>&nbsp;&nbsp;&nbsp;
		<label>Persona: </label><span><?=$mapBotRecord->personaName;?></span>
	</div>
	<div class="input-group m-b-1" style="width:100%;margin-top:15px;">
		<label>Personality: </label><span><?=$mapBotRecord->lexPersonalityName;?></span>&nbsp;&nbsp;&nbsp;
		<label>User: </label><span><?=$mapBotRecord->lexUserName;?></span>&nbsp;&nbsp;&nbsp;
		<label>User ID: </label><span><?=$mapBotRecord->lexUserID;?></span>
	</div>
	<div class="input-group m-b-1" style="max-width:70%;margin-top:10px;">
		<label>Bot: </label><span><?=$mapBotRecord->bot_name;?></span>&nbsp;&nbsp;&nbsp;
		<label>Alias: </label><span><?=$mapBotRecord->bot_alias;?></span>
		<?php /*
		<button class="btn btn-danger updateBOT" onClick="openUpdate()">Update Bot</button>
		*/ ?>
	</div>
</div>

<div id="intentTabs">
	<ul></ul>
</div>
<div id="LEX_MappingNewEdit"></div>
<script type="application/javascript">
	var myLEX;
	var apiURL             = "<?=env('API_URL');?>";
	var orgID              = "<?=$orgID;?>";
	var botMapID           = '<?=$bot_id;?>';
	var userID             = "<?=$session->get('userID');?>";
	var tabID              = 'intentTabs';
	var BotName            = '<?=$mapBotRecord->bot_name;?>';
	var BotAlias           = '<?=$mapBotRecord->bot_alias;?>';
	var termPerPage        = 100;
	var defaultOrgID       = '<?=$mapBotRecord->ownerId;?>';
	var defaultPersona     = '<?=$mapBotRecord->personaiD;?>';
	var defaultPersonality = '<?=$mapBotRecord->lexPersonalityID;?>';
	var lastPersonalityRelationId = 0;
</script>
<script src="/public/js/app.js"></script>
<script type="application/javascript">
	//---------------------------------------------------------
	function closeModal(id){ $("#"+id).modal('hide'); }
	//---------------------------------------------------------
	$(function(){
		//-----------------------------------------------------
		$("span.content-header-title > span").html("BOT MAPPING <small style='font-size:12px'><?=$mapBotRecord->bot_name;?></small>");
		//-----------------------------------------------------
		$("button.btn.<?=$mapBotRecord->publish_status;?>").show();
		$("#mappingName").prop('disabled', false);
		if('<?=$mapBotRecord->publish_status;?>'=='Published'){ $("#mappingName").prop('disabled', true); }
		//-----------------------------------------------------
	});
	//---------------------------------------------------------
	function published(status){
		if(status=='Published'){
			myLEX.showConfirm(function(ret){
				if(ret){ setPublishStatus(status); }
			},"Publishing this mapping will unpublish other mappings for this bot.", "Publish", "Cancel", "btn-success");
		}else{
			myLEX.showConfirm(function(ret){
				if(ret){ setPublishStatus(status); }
			},"Unpublish mapping, are you sure?");
		}
	}
	function setPublishStatus(status){
		var data = {};
		data.mappingName    = $("#mappingName").val().trim();
		data.bot_id         = botMapID;
		data.publish_status = status;
		data.ownerId        = defaultOrgID;
		data.bot_name       = BotName;
		data.bot_alias      = BotAlias;

		if(data.mappingName==""){
			myLEX.showError("invalid mapping name");
			$("#mappingName").focus();
			return;
		}
		$.ajax({
			url: apiURL+'/api/dashboard/lex/mapping/'+status,
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ $("#editItem #saveItem").prop('disabled', true); },
			success: function(res){
				if(res.result == 0){
					$("button.btn.Published").hide();
					$("button.btn.Unpublished").hide();
					$("button.btn."+status).show();
					$("#mappingName").prop('disabled', false);
					if(status=='Published'){ $("#mappingName").prop('disabled', true); }
					$("#publish_status").html("Status: "+status);
				}else{ showError(res.msg); }
			},
			error: function(e){ showError('Server error'); }
		});
	}

	$(function(){
		if('<?=\Session::get('menu_status');?>'==0){
			$(".menu.col-md-2, .content-header.row").hide();
			$(".content.col-md-10.col-xs-12").css("width", '100%');
			$(".content.col-md-10.col-xs-12").css("margin-top", '0');
		}
	});
</script>
<?php include('addKRdiv.php'); ?>
<?php include('rateDIV.php'); ?>
<?php include('updateBOT.php'); ?>
