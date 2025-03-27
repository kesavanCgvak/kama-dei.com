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
	Identification/Registration
	<div class="input-group m-b-1" id="serverName"/>
		<span class="input-group-addon" style="padding-top:12px;border-right:1px solid #cfd0d2">
			<input  type="radio" name="server" id="serverS" value="serverS" checked/>
			<label for="serverS">On staging</label>
		</span>

		<span class="input-group-addon" style="padding-top:12px;border-right:1px solid #cfd0d2">
			<input  type="radio" name="server" id="serverP" value="serverP"/>
			<label for="serverP">On preproduction</label>
		</span>

		<span class="input-group-addon" style="padding-top:12px;">
			<input  type="radio" name="server" id="serverL" value="serverL"/>
			<label for="serverL">On production</label>
		</span>

		<span class="input-group-addon" style="">
			<button class="btn btn-default" style="padding: 8px 10px 6px" onClick="window.location.reload()">
				<i class="fa fa-refresh"></i>
			</button>
		</span>
	</div>
</h1>

<form onReset="window.location.reload()">
	<div class="divB">
		<div>
			<label style="display:block;">API type:</label>
			<select class="form-control" id="apiType" style="max-width:80%;display:inline-block;" onChange="callSelectAPI()">
				<option value="">Select API Type</option>
				<option value="Identification">Identification</option>
				<option value="Registration">Registration</option>
			</select>
			<br>
			<span class="error_MSG" id="apiTypeMSG" style="color:red"></span>
		</div>

		<div>
			<label style="display:block;">Portal Code:</label>
			<input class="form-control" id="portalCode" value="" style="max-width:80%;display:inline-block;" />
			<br>
			<span class="error_MSG" id="portalCodeMSG" style="color:red"></span>

		</div>
		<div>
			<label style="display:block;">Org ID:</label>
			<input class="form-control" id="orgId" value="" style="max-width:80%;display:inline-block;" />
			<br>
			<span class="error_MSG" id="orgIdMSG" style="color:red"></span>

		</div>		
		<div>
			<label style="display:block;">Email/UniqueID:</label>
			<input class="form-control" id="email" value="" style="max-width:80%;display:inline-block;" />
			<br>
			<span class="error_MSG" id="emailMSG" style="color:red"></span>
		</div>
		<div>
			<label style="display:block;">Nick Name:</label>
			<input class="form-control" id="name" value="" placeholder="optional" style="max-width:80%;display:inline-block;" />
		</div>
		<br>
		<button type="button" class="btn btn-success" id="btnSubmit" onClick="spellCheck()" >Identify</button>

		<div>
			<label style="display:block;">Response:</label>
			<pre class="form-control" id="response" style="max-width:80%;height:auto;min-height:34px;"></pre>
		</div>
		<div>
			<label style="display:block;">Time Spend (ms):</label>
			<div class="form-control" id="time_spend" style="max-width:80%;height:auto;min-height:34px;"></div>
		</div>
	</div>
	<button type="reset" class="btn btn-danger" style="margin-top:50px;">Reset</button>
</form>

<script type="application/javascript">
//-----------------------------------------------------


//-----------------------------------------------------
function callSelectAPI(){
	let apiType = $("#apiType").val();

	if(apiType==''){ callReset(); return; }

	if (apiType=='Identification') {
		console.log("id");
		$('#btnSubmit').html("Identify");
	}
	else if (apiType=='Registration') {
		console.log("reg");
		$('#btnSubmit').html('Register');
	}
}

//-----------------------------------------------------
function spellCheck(){
	$('.error_MSG').html('');

	let server = $("input[name='server']:checked").val()
	let apiType = $('#apiType').val();
	let apiURL;
	var apiData = {};
	apiData.portalcode = $('#portalCode').val();
	apiData.orgid = $('#orgId').val();
	apiData.email = $('#email').val();
	apiData.fbid = $('#email').val();
	apiData.name = $('#name').val();

	if(	apiType == ''){
		$("#apiTypeMSG").html("Plese select API type!");
		$("#apiType").focus();
		return;
	}
	// if(	apiData.portalCode == ''){
	// 	$("#portalCodeMSG").html("Plese input portal code!");
	// 	$("#portalCode").focus();
	// 	return;
	// }
	// if(	apiData.orgId == ''){
	// 	$("#orgIdMSG").html("Plese input portal code!");
	// 	$("#orgId").focus();
	// 	return;
	// }
	// if(	apiData.email == ''){
	// 	$("#emailMSG").html("Plese input email/unique user ID!");
	// 	$("#email").focus();
	// 	return;
	// }


	console.log(server);
	if (apiType == "Identification") {
		if (server == "serverS") {apiURL = "https://staging.kama-dei.com/api/v1/chatbox/consumer_identify";}
		else if (server == "serverP") {apiURL = "https://preprod.kama-dei.com/api/v1/chatbox/consumer_identify";}
		else {apiURL = "https://api.kama-dei.com/api/v1/chatbox/consumer_identify";}
	}
	else {
		if (server == "serverS") {apiURL = "https://staging.kama-dei.com/api/v1/chatbox/consumer_register";}
		else if (server == "serverP") {apiURL = "https://preprod.kama-dei.com/api/v1/chatbox/consumer_register";}
		else {apiURL = "https://api.kama-dei.com/api/v1/chatbox/consumer_register";}		
	}

let start_time = new Date().getTime();

	$.ajax({
		method  :'post',
		url     : apiURL,
		data    : apiData,
		dataType: 'json',
		success: function(response){
			console.log(response);
			showResponse(response);
			showTime(new Date().getTime() - start_time);
		},
		error:
			function(xhr, textStatus, errorThrown ){ 
				if(xhr.status==400){ showResponse(xhr.responseJSON); }
			}
	});
}
//-----------------------------------------------------
function showResponse(response){
	$("#response").html('');	
	console.log("show response");
	$("#response").html(JSON.stringify(response, null, 2));

//	$("#response").html(JSON.stringify(response));
}

function showTime(time){
	$("#time_spend").html('');	
	$('#time_spend').html(time);
}


</script>