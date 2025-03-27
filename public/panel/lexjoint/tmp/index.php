<style>
	div.card-detail{ min-height:80vh; }
	.input-group-addon>label:hover{ color:red; cursor:pointer; }
	.input-group-addon>input:hover{ cursor:pointer; }
	div.source{ width:100%; display:block; border:1px solid #eee; min-height:375px; vertical-align:top; padding:0 5px; }
	div.error{ color:red; }
	#apiList option:disabled{ color:#eee; }
	div.apiSources{ display: none; }
</style>
<h1>APIs</h1>
<div style="min-width:33%; max-width:400px; display:inline-block;vertical-align: top; min-height:300px;">
	<div class="input-group m-b-1" style="margin:5px 0 15px;">
		<span class="input-group-addon">API</span>
		<select class="form-control" onChange="callAPI($(this).val())" id="apiList">
			<option value="0">Select API</option>
			<?php include_once("api.select.tpl"); ?>
		</select>
	</div>

	<div class="source">
		<?php $time = strtotime("+4 hour"); ?>
		<?php include("api.getBot.php"); ?>
		<?php include("api.getIntent.php"); ?>
		<?php include("api.getIntents.php"); ?>
		<?php include("api.getIntentVersions.php"); ?>
		<?php include("mapp.php"); ?>
	</div>
</div>

<div style="min-width:65%; max-width:65%; display:inline-block; border:1px solid #eee; min-height:485px; vertical-align:top;padding:0 5px 5px;" id="bbbb" >
	<h2>Response:</h2>
	<div id="responseAPI" style="overflow:auto;min-height:60%;padding:0 10px"></div>
</div>

<div id="LEX_TEMP"></div>
<script type="application/javascript">
	var myLEX;
	var apiURL = "<?=env('API_URL');?>";
</script>
<script src="/public/js/app.js"></script>
<script type="application/javascript">
	$(function(){
		$("div#mapp").show({slow:100});
		$("div#bbbb").hide({slow:100});
	  });
	function callAPI(api){
		$("div.apiSources").hide();
		if(api!=0){ $("div#"+api).toggle({slow:100}); }
		if(api!='mapp'){$("#bbbb").show(); }
		if(api=='mapp'){$("#bbbb").hide(); }
	}
	$(function(){
		$("div.error").html("&nbsp;");
	})
</script>
