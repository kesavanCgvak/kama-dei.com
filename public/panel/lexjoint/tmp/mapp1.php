<div id="mapp" class="apiSources">
	<h2>Mapping Data:</h2>
	<div class="input-group m-b-1" style="margin:15px 0 1px;">
		<span class="input-group-addon">Bot Name</span>
		<input class="form-control" id="botName" value="OrderPizza" maxlength="50" />
	</div>
	<div class="error botName"></div>

	<div class="input-group m-b-1" style="margin:5px 0 1px;">
		<span class="input-group-addon">Bot Alias</span>
		<input class="form-control" id="botVersion" value="OrderPizzaAlias" maxlength="50" />
	</div>
	<div class="error botVersion"></div>
	
	<div class="input-group m-b-1" style="margin:5px 0 1px;">
		<span class="input-group-addon">Org ID</span>
		<input class="form-control" id="orgId" value="" maxlength="50" />
	</div>
	<div class="error orgId"></div>
	
	<div class="input-group m-b-1" style="margin:5px 0 1px;">
		<span class="input-group-addon">User ID</span>
		<input class="form-control" id="lexUserId" value="" maxlength="50" />
	</div>
	<div class="error lexUserId"></div>
	
	<button class="btn btn-info" onClick="createClass()" style="margin-top: 10px;">Create CLASS</button>
	
	<hr width="60%"/>

	<div class="input-group m-b-1" style="margin:5px 0 1px;">
		<span class="input-group-addon">Intent KR ID</span>
		<input class="form-control" id="inttKrId" value="" maxlength="50" />
	</div>
	<div class="error inttKrId"></div>

	<button class="btn btn-info" onClick="getIntent()" style="margin-top: 10px;">Get Intent</button>
	
	<hr width="60%"/>

	<div class="input-group m-b-1" style="margin:5px 0 1px;">
		<span class="input-group-addon">Slot KR ID</span>
		<input class="form-control" id="slotKrId" value="" maxlength="50" />
	</div>
	<div class="error slotKrId"></div>

	<button class="btn btn-info" onClick="getIntent()" style="margin-top: 10px;">Get Slot</button>
	
	<hr width="60%"/>

	<div class="input-group m-b-1" style="margin:5px 0 1px;">
		<span class="input-group-addon">Value KR ID</span>
		<input class="form-control" id="valueKrId" value="" maxlength="50" />
	</div>
	<div class="error valueKrId"></div>

	<button class="btn btn-info" onClick="getIntent()" style="margin-top: 10px;">Get Value</button>
	
	<hr width="60%"/>
</div>


<script type="application/javascript">
	function createClass(){
		$("div.error").html("&nbsp;");
		var data = {};
		data.bot = $("#botName").val().trim();
		data.botversion = $("#botVersion").val().trim();
		data.orgId = $("#orgId").val().trim();
		data.lexUserId = $("#lexUserId").val().trim();

		if(data.bot==""){ $("div.error.botName").html("required"); $("#botName").focus(); return; }
		if(data.botversion==""){ $("div.error.botVersion").html("required"); $("#botVersion").focus(); return; }
		if(data.orgId==""){ $("div.error.orgId").html("required"); $("#orgId").focus(); return; }
		if(data.lexUserId==""){ $("div.error.lexUserId").html("required"); $("#lexUserId").focus(); return; }
		
console.log(data);
		$.ajax({
			url: apiURL+'/api/dashboard/lex/class/createclass',
			type: 'post',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ $("button.btn").prop('disabled', true); },
			complete: function(){ $("button.btn").prop('disabled', false); },
			success: function(ret){
				console.log(ret);
			},
			error: function(e){ alert('Server error'); }
		});
	}
	
	
	function getIntent(){
		$.ajax({
			url: apiURL+'/api/dashboard/lex/class/getintent',
			type: 'post',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify({}),
			beforeSend: function(){ $("button.btn").prop('disabled', true); },
			complete: function(){ $("button.btn").prop('disabled', false); },
			success: function(ret){
				console.log(ret);
			},
			error: function(e){ alert('Server error'); }
		});
	}
</script>