<div id="getIntents" class="apiSources">
	<h2>getIntents:</h2>
	<div class="input-group m-b-1" style="margin:15px 0 1px;">
		<span class="input-group-addon">name</span>
		<input class="form-control" id="intentName" value="Order" maxlength="50" />
	</div>
	<div class="error intentName"></div>

	<div class="input-group m-b-1" style="margin:15px 0 1px;">
		<span class="input-group-addon">Max Result</span>
		<input class="form-control" id="maxResult" value="5" maxlength="2" />
	</div>
	<div class="error intentVersion"></div>
	
	<button class="btn btn-info" onClick="getIntents()" style="float: right;margin-top: 20px;">Send Request</button>
</div>
<script type="application/javascript">
	function getIntents(){
		$("div.error").html("&nbsp;");
		var intentName = $("#getIntents #intentName").val().trim();
		var maxResult = $("#getIntents #maxResult").val().trim();
		if(intentName==""){
			$("div.error.intentName").html("required this field");
			$("#getIntents #intentName").focus();
			return;
		}
		if(maxResult==""){
			$("div.error.maxResult").html("required this field");
			$("#getIntents #maxResult").focus();
			return;
		}
		myLEX.callIntents(intentName, maxResult, "<?=date("Ymd", $time);?>", "<?=date("Ymd\THis\Z", $time);?>");
	}
</script>