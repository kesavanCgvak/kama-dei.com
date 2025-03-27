<style>
	div.card-detail{ height:auto; }

	.divA>div{ margin-top:10px; }

	.divB{ margin-top:30px; }
	.divB>div{ margin-top:10px; }
	div.input-group.m-b-1{ margin:5px 0 5px 30px;display:inline-block;max-width:350px;vertical-align:middle; }
	div.input-group.m-b-1 label{ font-size: small; }
	div.input-group.m-b-1 label:hover{ color:red; cursor:pointer; }
	div.input-group.m-b-1 .btn.btn-default:hover>i{ color:red; }
	
	.items{ max-width:80%; display:inline-block; }
</style>
<h1 style="margin-bottom:30px;">NLU</h1>

<div class="divA">
	<div>
		<label style="display:block;">Organization:</label>
		<select class="form-control items" id="orgid">
			<?php if($orgID==0): ?>
			<option value="">Select Organization</option>
			<?php endif; ?>
			<?php
			if($orgID!=0){
				$tmpOrgIDs = \App\OrgRelations::haveAccessTo($orgID);
				$tmpOrgIDs[] = $orgID;
				$orgs = \App\Organization::whereIn('organizationId', $tmpOrgIDs)->orderBy('organizationShortName', 'asc')->get();
			}else{
				$orgs = \App\Organization::orderBy('organizationShortName', 'asc')->get();
			}
			foreach($orgs as $org){
				?>
				<option <?=(($org['organizationId']==$orgID) ?"selected" :"");?>  value="<?=$org['organizationId'];?>">
					<?=$org['organizationShortName'];?>
				</option>
				<?php
			}
			?>
		</select>
	</div>

	<div>
		<label style="display:block;">User:</label>
		<select class="form-control items" id="email">
			<?php
			if($orgID!=0){
				$users = \App\User::where('levelID', 4)->where('orgID', $orgID)->orderBy('orgID', 'asc')->orderBy('email', 'asc')->get();
				if(!$users->isEmpty()){
					foreach($users as $tmpU){
						if(strpos($tmpU->email,"@")){ ?><option value="<?=$tmpU->email;?>"><?=$tmpU->email;?></option><?php }
					}
				}
			}else{
				?><option value="">Select User</option><?php
			}
			?>
		</select>
	</div>

	<div>
		<label style="display:block;">Select Portal Code:</label>
		<select class="form-control" id="portals" style="max-width:80%;display:inline-block;">
			<?php
			if($orgID!=0){
				$portals = \App\Portal::where('organization_id', $orgID)->orderBy('organization_id', 'asc')->orderBy('name', 'asc')->get();
				if(!$portals->isEmpty()){
					foreach($portals as $tmpP){
						?>
						<option value="<?="{$tmpP->portal_number}{$tmpP->code}";?>">
							<?="({$tmpP->portal_number}{$tmpP->code}) {$tmpP->name}";?>
						</option>
						<?php
					}
				}
			}else{
				?><option value="">Select Portal Code</option><?php
			}
			?>
		</select>
	</div>
	<div>
		<label style="display:inline-block;margin-right:10px">Select Portal Type:</label>
		<div class="btn-group" role="group" aria-label="Basic example">
			<button type="button" class="btn btn-default" id="btnOriginal" onClick="setPortalType(this,'originalMode')">
				 Production Mode
			</button>
			<button type="button" class="btn btn-default" id="btnTest" onClick="setPortalType(this,'testMode')">
				 Debug Mode
			</button>
		</div>
		<input id="originalMode" type="hidden" />
		<input id="testMode" value="3" type="hidden" />
	</div>
	<div>
		<label style="display:inline-block;">Portal Code to Use:</label>
		<input
			   class="form-control"
			   id="portalcode" 
			   value="" 
			   style="max-width:120px;;display:inline-block;margin-left:10px;text-align:center" 
			   disabled
		/>
	</div>

	<div class="divA1">
		<button type="button" class="btn btn-success" onClick="consumerIdentify()" id="btnIdentify" <?=((0==$orgID) ?"disabled" :"");?>>
			Identify
		</button>
		<span id="identifyMSG" style="color:red"></span>
	</div>
</div>
<br/>
<div class="divB">
	<div>
		<label style="display:block;">User Utterance:</label>
		<input class="form-control" id="userUtterance" value="" style="max-width:80%;display:inline-block;" disabled/>
		<button type="button" class="btn btn-success" id="btnSubmit" onClick="spellCheck()" disabled>Submit</button>
	</div>
	<div>
		<label style="display:block;">Kama-DEI Translation:</label>
		<div class="form-control" id="Translation" style="max-width:80%;height:auto;min-height:34px;"></div>
	</div>
	<div>
		<label style="display:block;">NLU Output to Controller:</label>
		<div class="form-control" id="Controller" style="max-width:80%;height:auto;min-height:34px;"></div>
	</div>
	<div>
		<label style="display:block;">chatbox controller output:</label>
		<div class="form-control" id="chatbox_controller_output" style="max-width:80%;height:auto;min-height:34px;"></div>
	</div>
	<div>
		<label style="display:block;"></label>
		<div class="form-control" id="other_output" style="max-width:80%;height:auto;min-height:34px;"></div>
	</div>

	<span id="spellCheckMSG" style="color:red"></span>
</div>
<button type="button" id="resetBTN" class="btn btn-danger" style="margin-top:50px;">Reset</button>

<script type="application/javascript">
	var urlIdentify   = "<?=env('nlu_consumer_identify');?>";
	var urlSpellCheck = "<?=env('nlu_python_api');?>";
	//-------------------------------------------------
	var users = [];
	<?php
	if($orgID==0):
	$users = \App\User::where('levelID', 4)->orderBy('orgID', 'asc')->orderBy('email', 'asc')->get(); 
	foreach($users as $user):
	if(strpos($user['email'],"@")):
	?>
	users.push( {email:'<?=$user['email'];?>', userid:'<?=$user['id'];?>', orgid:'<?=$user['orgID'];?>' } );
	<?php
	endif;
	endforeach;
	endif;
	?>
	//-------------------------------------------------
	var portals = [];
	<?php
	if($orgID==0):
	$portals = \App\Portal::orderBy('organization_id', 'asc')->orderBy('name', 'asc')->get(); 
	foreach($portals as $prtl):
	?>
	portals.push( {name:'<?=$prtl['name'];?>',code:'<?=$prtl['portal_number'].$prtl['code'];?>',orgid:'<?=$prtl['organization_id'];?>'} );
	<?php
	endforeach;
	endif;
	?>
	//-------------------------------------------------
	$(function(){
		//---------------------------------------------
		$("#orgid").focus();
		//---------------------------------------------
		<?php if($orgID==0): ?>
		$("#email, #portals, #btnOriginal, #btnTest, #btnIdentify").prop("disabled", true);
		$("#portalcode").val('');
		<?php else: ?>
		$("#portalcode").val($("#portals").val());
		$("#originalMode").val($("#portals").val().substr(0,1));
		setPortalType($("#btnTest"), "testMode");
		<?php endif; ?>
		//---------------------------------------------
		$("#orgid").on('change', function(){
			$("#email, #portals, #btnOriginal, #btnTest").prop("disabled", true);
			$("#btnOriginal, #btnTest" ).attr('class', 'btn btn-default');
			
			callSelectOrg();

			if($(this).val()!=''){ $("#email, #portals").prop("disabled", false); }
			$("#portalcode").val($("#portals").val());
		});
		//---------------------------------------------
		$("#email").on('change', function(){
			$("#btnIdentify").prop("disabled", true);
			if($(this).val()!='' && $("#portalcode").val()!=''){ $("#btnIdentify").prop("disabled", false); }
		});
		//---------------------------------------------
		$("#portals").on('change', function(){
			$("#btnIdentify, #btnOriginal, #btnTest").prop("disabled", true);
			$("#portalcode").val($(this).val());
			if($(this).val()!=''){
				$("#btnOriginal, #btnTest" ).prop('disabled', false);
				$("#originalMode").val($(this).val().substr(0,1));
				setPortalType($("#btnTest"), "testMode");
				if($("#email").val()!=''){ $("#btnIdentify").prop("disabled", false); }
			}
		});
		//---------------------------------------------
		$("#userUtterance").on('keypress', function(e){ if(e.keyCode==13){ spellCheck(); } });
		//---------------------------------------------
		$("#resetBTN").on('click', function(){
			$("#userUtterance").val('');
			$("#Translation, #Controller, #chatbox_controller_output, #other_output, #identifyMSG").html('');
			$("#orgid, #email, #portals, #btnOriginal, #btnTest, #btnIdentify").prop('disabled', false);
			$("#btnSubmit, #userUtterance").prop('disabled', true);
			$("#orgid").focus();
		});
	});
	function callRest2(flag=0){
		$("#Translation, #Controller, #chatbox_controller_output, #other_output").html('');
	}
	//-------------------------------------------------
	function callSelectOrg(){
		let orgid = $("#orgid").val();
		//---------------------------------------------
		$("#email option").remove();
		$("#email").append('<option value="">Select User</option>');
		for(let i in users){
			if(users[i].orgid==orgid){ $("#email").append('<option value="'+users[i].email+'">'+users[i].email+'</option>'); }
		}
		//---------------------------------------------
		$("#portals option").remove();
		$("#portals").append('<option value="">Select Portal</option>');
		$("#btnOriginal, #btnTest" ).prop('disabled', true);
		$("#btnOriginal, #btnTest" ).attr('class', 'btn btn-default');
		for(let i in portals){
			if(portals[i].orgid==orgid){
				$("#portals").append('<option value="'+portals[i].code+'">('+portals[i].code+') '+portals[i].name+'</option>'); 
			}
		}
	}
	//-------------------------------------------------
	function setPortalType(obj, id){
		let portalCode = $("#portalcode").val().trim().substr(1,5);
		$("#portalcode").val($("#"+id).val()+portalCode);
		$("#btnOriginal, #btnTest" ).attr('class', 'btn btn-default');
		$(obj).removeClass('btn-default').addClass('btn-info');
	}
	//-------------------------------------------------
	
//-----------------------------------------------------
//-----------------------------------------------------
	
//-----------------------------------------------------
//-----------------------------------------------------
var apiData = {};
	apiData.apikey = '';
	apiData.userid = 0;
	apiData.orgid  = 0;
//-----------------------------------------------------

//-----------------------------------------------------
function consumerIdentify(){
	var data = {};
	data.portalcode = $("#portalcode").val().trim();
	data.orgid      = $("#orgid"     ).val().trim();
	data.email      = $("#email"     ).val().trim();
	
	if(data.portalcode==''){ $("#identifyMSG").html("Enter Portal Code"); return; }
	if(data.orgid==''     ){ $("#identifyMSG").html("Select Organization"); return; }
	if(data.email==''     ){ $("#identifyMSG").html("Select User"); return; }

	callRest2();
	$("#identifyMSG").html("<i class='fa fa-refresh fa-spin'></i>");
	$.ajax({
		method  :'post',
		url     : urlIdentify,
		data    : data,
		dataType: 'json',
		success: 
			function( response ){
				if(response.result==1){ $("#identifyMSG").html("Invalid User"); }
				else{
					$("#identifyMSG").html("Name:"+response.name);
					apiData.apikey=response.apikey;
					apiData.userid=response.id;
					apiData.orgid=data.orgid;

					$("#portals"    ).prop('disabled', true);
					$("#btnOriginal, #btnTest" ).prop('disabled', true );
					$("#orgid"      ).prop('disabled', true);
					$("#email"      ).prop('disabled', true);
					$("#btnIdentify").prop('disabled', true);

					$("#userUtterance").prop('disabled', false);
					$("#btnSubmit"    ).prop('disabled', false);
					$("#userUtterance").focus();
				}
			},
		error:
			function(xhr, textStatus, errorThrown ){
				if(xhr.status==400){ $("#identifyMSG").html(xhr.responseJSON.message); }
				else{ $("#identifyMSG").html(errorThrown); }
			}
	});
}
//-----------------------------------------------------
function spellCheck(){
	if(	apiData.apikey == '' || apiData.userid == 0 || apiData.orgid == 0 ){
		$("#identifyMSG").html("Plese Identify"); return;
	}
	var data = {}; 
	data.userid = apiData.userid;
	data.orgid = apiData.orgid;
/*
	data.funcseq = "spell,suggest";
	data.funcsame = "true";
	
	data.wordseq = $("#userUtterance").val().trim();
	if(data.wordseq==''){ $("#spellCheckMSG").html("Enter User Utterance"); $("#userUtterance").focus(); return; }
	data.wordseq = data.wordseq.replace(/ /gi, ",");
*/
	data.state = 0;//802

	let tmp = $("#userUtterance").val().trim();
	if(tmp==''){ $("#spellCheckMSG").html("Enter User Utterance"); $("#userUtterance").focus(); return; }
	data.inquiry = JSON.stringify({request:{type:"text", message:tmp, utterance:tmp, answers:[]}});
//	data.inquiry = JSON.stringify({request:{type:"text", message:tmp.split(" ").join("+"), utterance:tmp, answers:[]}});
//	data.inquiry = JSON.stringify({request:{type:"text", message:encodeURIComponent(tmp), utterance:encodeURIComponent(tmp), answers:[]}});
//console.log(data);
	
	callRest2(1);
	$("#spellCheckMSG").html("<i class='fa fa-refresh fa-spin'></i>");
	$.ajax({
		method  :'post',
		url     : urlSpellCheck,
		data    : data,
		dataType: 'json',
		headers : {
			apikey: apiData.apikey
		},
		success: function(response){
			$("#spellCheckMSG").html("");
			showResponse(response);
		},
		error:
			function(xhr, textStatus, errorThrown ){ 
				$("#spellCheckMSG").html(errorThrown);
				if(xhr.status==400){ showResponse(xhr.responseJSON); }
			}
	});
}
//-----------------------------------------------------
function showObjec(tag, inVal){
	if(typeof inVal === 'object'){
		let tmpDIV = '<div><label>'+tag+':</label><div style="margin-left:30px;">';
		for(let j in inVal){ tmpDIV+=showObjec(j, inVal[j]); }
		tmpDIV += '</div></div>';
		return tmpDIV;
	}else{
		return '<div><label>'+tag+':</label> <span>'+inVal+'</span></div>';
	}
}
function showResponse(response){
	for(let i in response){
		switch(i){
			case 'largestIE_output':{
				for(let j in response[i]){
					if(j=='apiKeyResponse'){
						let tmpDIV = '<div><label>'+j+':</label>';
						for(let k in response[i][j]){
							tmpDIV += ('<div style="margin-left:30px"><label>'+k+':</label> <span>'+response[i][j][k]+'</span></div>');
						}
						tmpDIV += '</div>';
						$("#Translation").append(tmpDIV);
					}else{ $("#Translation").append('<div><label>'+j+':</label> <span>'+response[i][j]+'</span></div>'); }
				}
				continue;
			}
			case 'language_processing_output':{
				$("#Controller").append('<div><label>'+i+': </label><span>'+response[i]+'</span></div>');
				continue;
			}
/*
			case 'chat_controller_pointing_to':
			case 'enterprise_words_recognition_api_pointing_to':
			case 'largestIE_pointing_to':
			case 'superterm_api_pointing_to':
			case 'term_relation_api_pointing_to':
			case 'input':
			case 'enterprise_words_loader_pointing_to':{
				$("#other_output").append('<div><label>'+i+':</label> <span>'+response[i]+'</span></div>');
				continue;
			}
*/
			case 'chat_controller_output':
			case 'chatbox_controller_output':{
				for(let j in response[i]){
					$("#chatbox_controller_output").prepend(showObjec(j, response[i][j]));
				}
				continue;
			}
/*
			case 'lex_controller_output':
			case 'spend time':
				{
				continue;
			}
*/
			default:{
				let tmpDiv = "";
				if(typeof response[i] === 'object'){
					for(let j in response[i]){
						if(isNaN(j)){
//$("#chatbox_controller_output").append(showObjec(j, response[i][j]));
						tmpDiv += ('<div style="margin-left:30px;">'+showObjec(j, response[i][j])+'</div>');
						}
					}
				}else{
					tmpDiv += '<span>'+response[i]+'</span>';
				}
				$("#other_output")
					.append('<div><label>'+i+':</label> '+tmpDiv+'</div>');
				continue;
			}
		}
	}
}
//-----------------------------------------------------

//-----------------------------------------------------
//-----------------------------------------------------
</script>
