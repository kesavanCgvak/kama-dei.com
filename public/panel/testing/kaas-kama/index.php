<style>
	div.card-detail{ height:auto; }

	.divA>div{ margin-top:10px; }

	.divB{ margin-top:30px; }
	.divB>div{ margin-top:10px; }
	div.input-group.m-b-1{ margin:5px 0 5px 30px;display:inline-block;max-width:350px;vertical-align:middle; }
	div.input-group.m-b-1 label{ font-size: small; }
	div.input-group.m-b-1 label:hover{ color:red; cursor:pointer; }
	div.input-group.m-b-1 .btn.btn-default:hover>i{ color:red; }
</style>
<h1 style="margin-bottom:30px;">
	KaaS [Kama-DEI]
	<div class="input-group m-b-1" <?=(($orgID!=0) ?"style='display:none'" :"style='display:none'");?> />
		<span class="input-group-addon" style="padding-top:12px;border-right:1px solid #cfd0d2">
			<input  type="radio" name="server" id="serverS" checked />
			<label for="serverS">On staging</label>
		</span>

		<span class="input-group-addon" style="padding-top:12px;border-right:1px solid #cfd0d2">
			<input  type="radio" name="server" id="serverP" disabled />
			<label for="serverP">On preproduction</label>
		</span>

		<span class="input-group-addon" style="padding-top:12px;">
			<input  type="radio" name="server" id="serverL" <?=(($orgID!=0) ?"" :"");?> />
			<label for="serverL">On production</label>
		</span>

		<span class="input-group-addon" style="">
			<button class="btn btn-default" style="padding: 8px 10px 6px" onClick="window.location.reload()">
				<i class="fa fa-refresh"></i>
			</button>
		</span>
	</div>
</h1>

<form onReset="callRest()">
	<div class="divA">
		<div>
			<label style="display:block;">Organization:</label>
			<select class="form-control" id="orgid" style="max-width:80%;display:inline-block;" onChange="callSelectOrg()" disabled>
				<?php if($orgID==0): ?>
				<option value="">Select Organization</option>
				<option value="0"><?=env('BASE_ORGANIZATION');?></option>
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
			<select class="form-control" id="email" style="max-width:80%;display:inline-block;" <?=((0==$orgID) ?"disabled" :"");?>>
				<?php
				if($orgID!=0){
					$users = \App\User::where('levelID', 4)
										->where('orgID', $orgID)
										->orderBy('orgID', 'asc')
										->orderBy('email', 'asc')
										->get();
					if(!$users->isEmpty()){
						foreach($users as $tmpU){
							?><option value="<?=$tmpU->email;?>"><?=$tmpU->email;?></option><?php
						}
					}
				}else{
					?><option value="">Select User</option><?php
				}
				?>
			</select>
		</div>
		
		<div>
			<label style="display:block;">Portal Code:</label>
			<input class="form-control" id="portalcode" value="txxxxx" style="max-width:80%;display:inline-block;" disabled/>
		</div>
		
		<div class="divA1">
			<button type="button" class="btn btn-success" onClick="consumerIdentify()" id="btnIdentify" <?=((0==$orgID) ?"disabled" :"");?>>Identify</button>
			<span id="identifyMSG" style="color:red"></span>
		</div>
	</div>
	<br/>


	<div class="divB">

		<div class="divB">
			<div>
				<label style="display:block;">API KEY (manual):</label>
				<input class="form-control" id="apikeyManual" value="" onChange="manualKey()" style="max-width:80%;display:inline-block;" disabled/>
			</div>
			<div>
				<label style="display:block;">Bot Name:</label>
				<input class="form-control" id="botName" value="" style="max-width:80%;display:inline-block;" disabled/>
			</div>
			<div>
				<label style="display:block;">Bot Version:</label>
				<input class="form-control" id="botVersion" value="" style="max-width:80%;display:inline-block;" disabled/>
			</div>
			<div>
				<label style="display:block;">Bot Alias:</label>
				<input class="form-control" id="botAlias" value="" style="max-width:80%;display:inline-block;" disabled/>
			</div>
			<div>
				<label style="display:block;">botState/lexState:</label>
				<input class="form-control" id="botState" value="" style="max-width:80%;display:inline-block;" disabled/>
			</div>
			<div>
				<label style="display:block;">Intent Name:</label>
				<input class="form-control" id="intentName" value="" style="max-width:80%;display:inline-block;" disabled/>
			</div>
			<div>
				<label style="display:block;">Slot Name:</label>
				<input class="form-control" id="slotName" value="" style="max-width:80%;display:inline-block;" disabled/>
			</div>
			<div>
				<label style="display:block;">User Utterance:</label>
				<input class="form-control" id="originalUtterance" value="" style="max-width:80%;display:inline-block;" disabled/>
			</div>

			<div>
				<label style="display:block;">3PB Utterance:</label>
				<input class="form-control" id="userUtterance" value="" style="max-width:80%;display:inline-block;" disabled/>
			</div>
		
			<div>
				<button type="button" class="btn btn-success" id="btnSubmit" onClick="spellCheck()" disabled>Submit</button>
				<span id="spellCheckMSG" style="color:red"></span>

			</div>
		</div>
		<br/>

		<div>
			<label style="display:block;">Kama-DEI Translation:</label>
			<div class="form-control" id="Translation" style="max-width:80%;height:auto;min-height:34px;"></div>
		</div>
		<div>
			<label style="display:block;">NLU Output to Controller:</label>
			<div class="form-control" id="Controller" style="max-width:80%;height:auto;min-height:34px;"></div>
		</div>
		<div>
			<label style="display:block;">KaaS Controller Output:</label>
			<div class="form-control" id="kaas_controller_output" style="max-width:80%;height:auto;min-height:34px;"></div>
		</div>
<!-- 		<div>
			<label style="display:block;">Lex Controller Output:</label>
			<div class="form-control" id="lex_controller_output" style="max-width:80%;height:auto;min-height:34px;"></div>
		</div> -->
		<div>
			<label style="display:block;">Time Spend (sec):</label>
			<div class="form-control" id="time_spend" style="max-width:80%;height:auto;min-height:34px;"></div>
		</div>

		<div>
			<label style="display:block;"></label>
			<div class="form-control" id="other_output" style="max-width:80%;height:auto;min-height:34px;"></div>
		</div>
	</div>


	<button type="reset" class="btn btn-danger" style="margin-top:50px;">Reset</button>
</form>

<script type="application/javascript">
//-----------------------------------------------------
var apiData = {};
	apiData.apikey = '';
	apiData.userid = 0;
	apiData.orgid  = 0;
//-----------------------------------------------------
function callRest(){
	$("#userid option").remove();
	$("#userid").append('<option value="">Select User</option>');
	callRest2();
}
function callRest2(flag=0){
	if(flag==0){
		$("#identifyMSG").html("");

		apiData.apikey = '';
		apiData.userid = 0;
		apiData.orgid  = 0;

	//	$("#portalcode" ).prop('disabled', false);
		$("#orgid"      ).prop('disabled', false);
		$("#email"      ).prop('disabled', false);
		$("#btnIdentify").prop('disabled', false);
		
		$("#orgid").focus();

		$("#userUtterance").prop('disabled', true);
		$("#btnSubmit"    ).prop('disabled', true);
	}
	$("#spellCheckMSG").html("");

	$("#userUtterance").val ('');
	$("#Controller"   ).html('');
	$("#Translation"  ).html('');
	$("#kaas_controller_output").html('');
	$("#time_spend").html('');
	$("#other_output").html('');
}
//-----------------------------------------------------
var users = [];
<?php
if($orgID==0){
	$users = \App\User::where('levelID', 4)->orderBy('orgID', 'asc')->orderBy('email', 'asc')->get(); 
	foreach($users as $user){
		if(strpos($user['email'],"@")){
		?>
			users.push( {email:'<?=$user['email'];?>', userid:'<?=$user['id'];?>', orgid:'<?=$user['orgID'];?>' } );
		<?php
		}
	}
}
?>
//-----------------------------------------------------
function callSelectOrg(){
	let orgid = $("#orgid").val();
	if(orgid==''){ callRest(); return; }
	
	$("#email option").remove();
	$("#email").append('<option value="">Select User</option>');

	for(let i in users){
		if(users[i].orgid==orgid){ $("#email").append('<option value="'+users[i].email+'">'+users[i].email+'</option>'); }
	}
}
//-----------------------------------------------------
function manualKey() {
	apiData.apikey = $("#apikeyManual").val();
}

//-----------------------------------------------------
function consumerIdentify(){
	var data = {};
	data.portalcode = $("#portalcode").val().trim();
	data.orgid      = $("#orgid"     ).val().trim();
	data.email      = $("#email"     ).val().trim();
	
	if(data.portalcode==''){ $("#identifyMSG").html("Enter Portal Code"); return; }
	if(data.orgid==''){ $("#identifyMSG").html("Select Organization"); return; }
	if(data.email==''){ $("#identifyMSG").html("Select User"); return; }

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

					$("#portalcode" ).prop('disabled', true);
					$("#orgid"      ).prop('disabled', true);
					$("#email"      ).prop('disabled', true);
					$("#btnIdentify").prop('disabled', true);

					$("#apikeyManual").prop('disabled', false);
					$("#botName").prop('disabled', false);
					$("#botVersion").prop('disabled', false);
					$("#botAlias").prop('disabled', false);
					$("#botState").prop('disabled', false);
					$("#intentName").prop('disabled', false);
					$("#slotName").prop('disabled', false);
					$("#originalUtterance").prop('disabled', false);
					$("#userUtterance").prop('disabled', false);
					$("#btnSubmit"    ).prop('disabled', false);
					$("#botName").focus();
				}
			},
		error:
			function(xhr, textStatus, errorThrown ){ 
				$("#identifyMSG").html(errorThrown);
			}
	});
}
//-----------------------------------------------------
function spellCheck(){
	if(	apiData.apikey == '' || apiData.userid == 0 || apiData.orgid == 0 ){
		$("#identifyMSG").html("Please Identify"); return;
	}
	console.log("userID: " + apiData.userid + "orgID: " + apiData.orgid + "APIkey: " + apiData.apikey);
	var data = {}; 
	data.userid = apiData.userid;
	data.orgid = apiData.orgid;
	//data.orgid = apiData.apikey;
/*
	data.funcseq = "spell,suggest";
	data.funcsame = "true";
	
	data.wordseq = $("#userUtterance").val().trim();
	if(data.wordseq==''){ $("#spellCheckMSG").html("Enter User Utterance"); $("#userUtterance").focus(); return; }
	data.wordseq = data.wordseq.replace(/ /gi, ",");
*/
	data.state = 0;//802

	let tmp = $("#userUtterance").val().trim();
	let tmp_botName = $("#botName").val().trim();
	let tmp_botVersion = $("#botVersion").val().trim();
	let tmp_botAlias = $("#botAlias").val().trim();
	let tmp_botState = $("#botState").val().trim();
	let tmp_intentName = $("#intentName").val().trim();
	let tmp_slotName = $("#slotName").val().trim();
	let tmp_originalUtterance = $("#originalUtterance").val().trim();

	//if(tmp==''){ $("#spellCheckMSG").html("Enter User Utterance"); $("#userUtterance").focus(); return; }
	//if(tmp_originalUtterance==''){ $("#spellCheckMSG").html("Enter Original Utterance"); $("#originalUtterance").focus(); return; }
	if(tmp_botName==''){ $("#spellCheckMSG").html("Enter Bot Name"); $("#botName").focus(); return; }
	if(tmp_botVersion==''){ $("#spellCheckMSG").html("Enter Bot Version"); $("#botVersion").focus(); return; }
	if(tmp_botAlias==''){ $("#spellCheckMSG").html("Enter Bot Alias"); $("#botAlias").focus(); return; }
	if (tmp_botState == 'ElicitSlot') {
		if (tmp_intentName == '') { $("#spellCheckMSG").html("Enter Intent Name"); $("#intentName").focus(); return; }
		if (tmp_slotName == '') { $("#spellCheckMSG").html("Enter Slot Name"); $("#slotName").focus(); return; }
	}

	data.botName = tmp_botName;
	data.botVersion = tmp_botVersion;
	data.botAlias = tmp_botAlias;
	data.botState = tmp_botState;
	data.intentName = tmp_intentName;
	data.slotName = tmp_slotName;

	data.inquiry=JSON.stringify({request:{type:"text", message:"", utterance:"", answers:[]}});	
	data.utterance_orig = tmp_originalUtterance;
	data.utterance_3PB=tmp;
	//data.inquiry = JSON.stringify({request:{type:"text", message:tmp, utterance:tmp, answers:[]}});
//	data.inquiry = JSON.stringify({request:{type:"text", message:tmp.split(" ").join("+"), utterance:tmp, answers:[]}});
//	data.inquiry = JSON.stringify({request:{type:"text", message:encodeURIComponent(tmp), utterance:encodeURIComponent(tmp), answers:[]}});
//console.log(data);
	
	callRest2(1);

	console.log(apiData.apikey);
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
				$("#spellCheckMSG").html(xhr.status + ' ' + errorThrown + ': ' + xhr.responseJSON);
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
		return '<div><label>'+tag+':</label><span>'+inVal+'</span></div>';
	}
}
function showResponse(response){
	console.log(response);
	for(let i in response){
		switch(i){
			case 'largestIE_output':{
				for(let j in response[i]){
					if(j=='apiKeyResponse'){
						let tmpDIV = '<div><label>'+j+':</label>';
						for(let k in response[i][j]){
							tmpDIV += ('<div style="margin-left:30px"><label>'+k+':</label><span>'+response[i][j][k]+'</span></div>');
						}
						tmpDIV += '</div>';
						$("#Translation").append(tmpDIV);
					}else{ $("#Translation").append('<div><label>'+j+':</label><span>'+response[i][j]+'</span></div>'); }
				}
				continue;
			}
			case 'language_processing_output':{
				$("#Controller").append('<div><label>'+i+':</label><span>'+response[i]+'</span></div>');
				continue;
			}
			case 'kaas_controller_output':{
				for(let j in response[i]){
					$("#kaas_controller_output").prepend(showObjec(j, response[i][j]));
				}
				continue;
			}
			case 'lex_controller_output':{
				// for(let j in response[i]){
				// 	$("#lex_controller_output").prepend(showObjec(j, response[i][j]));
				// }
				continue;}
			case 'spend time':{
				for(let j in response[i]){
					$("#time_spend").append('<div><label>'+j+':</label><span>'+response[i][j]+'</span></div>'); 
				}
				continue;
			}
/*
			default:{
				for(let j in response[i]){ if(isNaN(j)){ $("#kaas_controller_output").append(showObjec(j, response[i][j])); } }
				continue;
			}
*/
			default:{
				let tmpDiv = "";
				if(typeof response[i] === 'object'){
					for(let j in response[i]){
						if(isNaN(j)){ tmpDiv += ('<div style="margin-left:30px;">'+showObjec(j, response[i][j])+'</div>'); }
					}
				}else{ tmpDiv += '<span>'+response[i]+'</span>'; }
				$("#other_output").append('<div><label>'+i+':</label> '+tmpDiv+'</div>');
				continue;
			}
		}
	}
}
//-----------------------------------------------------

//-----------------------------------------------------
<?php if($orgID==0): ?>
/*
var urlIdentify   = null;
var urlSpellCheck = null;
*/
var urlIdentify   = "https://staging.kama-dei.com/api/v1/chatbox/consumer_identify";
var urlSpellCheck = "https://staging_py.kama-dei.com/python_api/v1/multiple_language";
<?php else: ?>
/*
var urlIdentify   = "https://api.kama-dei.com/api/v1/chatbox/consumer_identify";
var urlSpellCheck = "https://python.kama-dei.com/python_api/v1/multiple_language";
*/
var urlIdentify   = "https://staging.kama-dei.com/api/v1/chatbox/consumer_identify";
var urlSpellCheck = "https://staging_py.kama-dei.com/python_api/v1/multiple_language";
<?php endif; ?>
$(function(){ 
	$("#orgid").focus();
	$("#userUtterance").on('keypress', function(e){ if(e.keyCode==13){ spellCheck(); } });
	
	$("#orgid").prop("disabled", false);
	$("#email").prop("disabled", false);
	$("#btnIdentify").prop("disabled", false);
	callRest();
	$("#serverS").on('change', function(){
		urlIdentify   = "https://staging.kama-dei.com/api/v1/chatbox/consumer_identify";
		urlSpellCheck = "https://staging_py.kama-dei.com/python_api/v1/multiple_language";

		$("#orgid").prop("disabled", false);
		$("#email").prop("disabled", false);
		$("#btnIdentify").prop("disabled", false);
		
		callRest();
	});

	$("#serverP").on('change', function(){
		urlIdentify   = "https://staging.kama-dei.com/api/v1/chatbox/consumer_identify";
		urlSpellCheck = "https://staging_py.kama-dei.com/python_api/v1/multiple_language";
/*
		urlIdentify   = "https://prep.kama-dei.com/api/v1/chatbox/consumer_identify";
		urlSpellCheck = "https://preprod_py.kama-dei.com/python_api/v1/multiple_language";
*/
		$("#orgid").prop("disabled", false);
		$("#email").prop("disabled", false);
		$("#btnIdentify").prop("disabled", false);
		
		callRest();
	});
		
	$("#serverL").on('change', function(){
		urlIdentify   = "https://staging.kama-dei.com/api/v1/chatbox/consumer_identify";
		urlSpellCheck = "https://staging_py.kama-dei.com/python_api/v1/multiple_language";
/*
		urlIdentify   = "https://api.kama-dei.com/api/v1/chatbox/consumer_identify";
		urlSpellCheck = "https://python.kama-dei.com/python_api/v1/multiple_language";
*/
		$("#orgid").prop("disabled", false);
		$("#email").prop("disabled", false);
		$("#btnIdentify").prop("disabled", false);
		
		callRest();
	});
})
//-----------------------------------------------------

//-----------------------------------------------------
</script>
<?php
/*
	https://staging.kama-dei.com/api/v1/chatbox/consumer_identify
	"=env('nlu_consumer_identify');",

	https://staging_py.kama-dei.com/python_api/v1/that_clause
	"=env('nlu_python_api');",//spell_check

*/
?>