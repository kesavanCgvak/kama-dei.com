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
	NLU
	<div class="input-group m-b-1" <?=(($orgID!=0) ?"style='display:none'" :"");?> />
		<span class="input-group-addon" style="padding-top:12px;border-right:1px solid #cfd0d2">
			<input  type="radio" name="server" id="serverS" />
			<label for="serverS">On staging</label>
		</span>

		<span class="input-group-addon" style="padding-top:12px;">
			<input  type="radio" name="server" id="serverL" <?=(($orgID!=0) ?"checked" :"");?> />
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
<?php
/*
			<input class="form-control" id="portalcode" value="3tEstNL" style="max-width:80%;display:inline-block;" disabled/>
*/
?>
			<select class="form-control" id="portalcode"style="max-width:80%;display:inline-block;" disabled>
				<option value="">Select Portal ...</option>
			</select>
		</div>
		
		<div class="divA1">
			<button type="button" class="btn btn-success" onClick="consumerIdentify()" id="btnIdentify" <?=((0==$orgID) ?"disabled" :"");?>>Identify</button>
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
		
		<span id="spellCheckMSG" style="color:red"></span>
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

	$("#portalcode option").remove();
	$("#portalcode").append("<option value=''>Select Portal ...</option>");

	callRest2();
}
function callRest2(flag=0){
	if(flag==0){
		$("#identifyMSG").html("");

		apiData.apikey = '';
		apiData.userid = 0;
		apiData.orgid  = 0;

		$("#portalcode" ).prop('disabled', false);
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
	$("#chatbox_controller_output").html('');


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

	$("#portalcode option").remove();
	$("#portalcode").append("<option value=''>Select Portal ...</option>");

	if(orgid==''){ callRest(); return; }
	
	$("#email option").remove();
	$("#email").append('<option value="">Select User</option>');

	for(let i in users){
		if(users[i].orgid==orgid){ $("#email").append('<option value="'+users[i].email+'">'+users[i].email+'</option>'); }
	}
	
	$.get("/api/dashboard/portal/portals/"+orgid, function(ret){
		if(ret.result==0){
			for(let i in ret.data){
				let portalCode = ret.data[i].portal_number+ret.data[i].code;
				let portalName = ret.data[i].name+" "+ret.data[i].portal_number+" "+ret.data[i].code;
				$("#portalcode").append("<option value='"+portalCode+"'>"+portalName+"</option>");
			}
		}
	});
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

					$("#userUtterance").prop('disabled', false);
					$("#btnSubmit"    ).prop('disabled', false);
					$("#userUtterance").focus();
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
		return '<div><label>'+tag+':</label><span>'+inVal+'</span></div>';
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
			case 'chatbox_controller_output':{
				for(let j in response[i]){
					$("#chatbox_controller_output").prepend(showObjec(j, response[i][j]));
				}
				continue;
			}
			case 'lex_controller_output':
			case 'spend time':{
				continue;
			}
			default:{
				for(let j in response[i]){ if(isNaN(j)){ $("#chatbox_controller_output").append(showObjec(j, response[i][j])); } }
				continue;
			}
		}
	}
}
//-----------------------------------------------------

//-----------------------------------------------------
<?php if($orgID==0): ?>
var urlIdentify   = null;
var urlSpellCheck = null;
<?php else: ?>
var urlIdentify   = "https://api.kama-dei.com/api/v1/chatbox/consumer_identify";
var urlSpellCheck = "https://python.kama-dei.com/python_api/v1/multiple_language";
<?php endif; ?>
$(function(){ 
	$("#orgid").focus();
	$("#userUtterance").on('keypress', function(e){ if(e.keyCode==13){ spellCheck(); } });
	
	$("#serverS").on('change', function(){
		urlIdentify   = "https://staging.kama-dei.com/api/v1/chatbox/consumer_identify";
//		urlSpellCheck = "https://staging_py.kama-dei.com/python_api/v1/that_clause";
		urlSpellCheck = "https://staging_py.kama-dei.com/python_api/v1/multiple_language";

		$("#orgid").prop("disabled", false);
		$("#email").prop("disabled", false);
		$("#btnIdentify").prop("disabled", false);
		
		callRest();
	});
		
	$("#serverL").on('change', function(){
		urlIdentify   = "https://api.kama-dei.com/api/v1/chatbox/consumer_identify";
		urlSpellCheck = "https://python.kama-dei.com/python_api/v1/multiple_language";

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