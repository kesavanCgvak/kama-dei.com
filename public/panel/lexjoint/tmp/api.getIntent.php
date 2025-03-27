<div id="getIntent" class="apiSources">
	<h2>getIntent:</h2>
	<div class="input-group m-b-1" style="margin:15px 0 1px;">
		<span class="input-group-addon">name</span>
		<input class="form-control" id="intentName" value="OrderPizza" maxlength="50" />
	</div>
	<div class="error intentName"></div>

	<div class="input-group m-b-1" style="margin:15px 0 1px;">
		<span class="input-group-addon">versions or alias</span>
		<input class="form-control" id="intentVersion" value="19" maxlength="50" />
	</div>
	<div class="error intentVersion"></div>
	
	<button class="btn btn-info" onClick="getIntent()" style="float: right;margin-top: 20px;">Send Request</button>
</div>
<script type="application/javascript">
	function getIntent(){
		$("div.error").html("&nbsp;");
		var intentName = $("#getIntent #intentName").val().trim();
		var intentVersion = $("#getIntent #intentVersion").val().trim();
		if(intentName==""){
			$("div.error.intentName").html("required this field");
			$("#getIntent #intentName").focus();
			return;
		}
		if(intentVersion==""){
			$("div.error.intentVersion").html("required this field");
			$("#getIntent #intentVersion").focus();
			return;
		}
		myLEX.callIntent(intentName, intentVersion, "<?=date("Ymd", $time);?>", "<?=date("Ymd\THis\Z", $time);?>");
	}
</script>