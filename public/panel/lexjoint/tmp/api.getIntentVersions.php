<div id="getIntentVersions" class="apiSources">
	<h2>getIntentVersions:</h2>
	<div class="input-group m-b-1" style="margin:15px 0 1px;">
		<span class="input-group-addon">name</span>
		<input class="form-control" id="intentName" value="OrderPizza" maxlength="50" />
	</div>
	<div class="error intentName"></div>

	<div class="input-group m-b-1" style="margin:15px 0 1px;">
		<span class="input-group-addon">Max Result</span>
		<input class="form-control" id="maxResult" value="5" maxlength="2" />
	</div>
	<div class="error intentVersion"></div>
	
	<button class="btn btn-info" onClick="getIntentVersions()" style="float: right;margin-top: 20px;">Send Request</button>
</div>
<script type="application/javascript">
	function getIntentVersions(){
		$("div.error").html("&nbsp;");
		var intentName = $("#getIntentVersions #intentName").val().trim();
		var maxResult = $("#getIntentVersions #maxResult").val().trim();
		if(intentName==""){
			$("div.error.intentName").html("required this field");
			$("#getIntentVersions #intentName").focus();
			return;
		}
		if(maxResult==""){
			$("div.error.maxResult").html("required this field");
			$("#getIntentVersions #maxResult").focus();
			return;
		}
		myLEX.callIntentVersions(intentName, maxResult, "<?=date("Ymd", $time);?>", "<?=date("Ymd\THis\Z", $time);?>");
	}
</script>