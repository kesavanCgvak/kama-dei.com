<div id="getBot" class="apiSources">
	<h2>getBot:</h2>
	<div class="input-group m-b-1" style="margin:15px 0 1px;">
		<span class="input-group-addon">name</span>
		<input class="form-control" id="botName" value="OrderPizza" maxlength="50" />
	</div>
	<div class="error botName"></div>

	<div class="input-group m-b-1" style="margin:15px 0 1px;">
		<span class="input-group-addon">versions or alias</span>
		<input class="form-control" id="botVersion" value="OrderPizzaAlias" maxlength="50" />
	</div>
	<div class="error botVersion"></div>
	
	<button class="btn btn-info" onClick="getBot()" style="float: right;margin-top: 20px;">Send Request</button>
</div>
<script type="application/javascript">
	function getBot(){
		$("div.error").html("&nbsp;");
		var botName = $("#getBot #botName").val().trim();
		var botVersion = $("#getBot #botVersion").val().trim();
		if(botName==""){
			$("div.error.botName").html("required this field");
			$("#getBot #botName").focus();
			return;
		}
		if(botVersion==""){
			$("div.error.botVersion").html("required this field");
			$("#getBot #botVersion").focus();
			return;
		}
		myLEX.callBOT(botName, botVersion, "<?=date("Ymd", $time);?>", "<?=date("Ymd\THis\Z", $time);?>");
	}
</script>