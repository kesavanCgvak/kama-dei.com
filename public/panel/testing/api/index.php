<style>
	div.card-detail{ min-height:80vh; }
	.input-group-addon>label:hover{ color:red; cursor:pointer; }
	.input-group-addon>input:hover{ cursor:pointer; }
	div.source{ width:100%; display:block; border:1px solid #eee; min-height:375px; vertical-align:top; padding:0 5px; }
	div.error{ color:red; }
	#apiList option:disabled{ color:#eee; }
</style>
<h1>API&nbsp;<small style="color:cadetblue;margin-left: 10px;">To test the internal APIs of the system.</small></h1>
<div style="min-width:33%; max-width:400px; display:inline-block;vertical-align: top; min-height:300px;">
	<div class="input-group m-b-1" style="margin:5px 0 15px;">
		<span class="input-group-addon" style="padding-top:12px;border-right:1px solid #cfd0d2">
			<input  type="radio" name="server" id="serverS" checked value="https://staging.kama-dei.com" />
			<label for="serverS">On staging</label>
		</span>

		<span class="input-group-addon" style="padding-top:12px;">
			<input  type="radio" name="server" id="serverL" value="https://api.kama-dei.com" />
			<label for="serverL">On production</label>
		</span>

		<span class="input-group-addon" style="">
			<button class="btn btn-default" style="" onClick="callAPI($('#apiList').val())">
				<i class="fa fa-refresh"></i>
			</button>
		</span>
	</div>

	<div class="input-group m-b-1" style="margin:5px 0 15px;">
		<span class="input-group-addon">API</span>
		<select class="form-control" onChange="callAPI($(this).val())" id="apiList">
			<option value="0">Select API</option>
			<?php include_once("api.select.tpl"); ?>
		</select>
	</div>

	<div class="source">
		<h2>Source:</h2>
		<div id="sourceAPI" style="overflow:auto;"></div>
	</div>
</div>
<div style="min-width:65%; max-width:65%; display:inline-block; border:1px solid #eee; min-height:485px; vertical-align:top;padding:0 5px 5px;" >
	<div id="responseAPI" style="overflow:auto;min-height:60%;padding:0 10px"></div>
</div>
<div id="apiScript" style="display:none;"></div>
<script type="application/javascript">
	var APIURL = '<?=env('API_URL');?>';
	//-----------------------------------------------------------------
	$(function(){
		$('#serverS, #serverL').change(function(){ callAPI($("#apiList").val()); });
	});
	//-----------------------------------------------------------------
	var settings = {};
	var isdDefault = true;
	function callAPI(apiID){
		$("#sourceAPI, #responseAPI, #apiScript").html("");
		var server = (($("#serverL").prop('checked')) ?$("#serverL").val().trim() :$("#serverS").val().trim() );
		switch(apiID){
			case '0':{ $("#responseAPI").text(""); break; }
			default:{
				isdDefault = true;
				settings = {};
				var apiScript  = "<?=env('API_URL');?>/public/panel/testing/api/api_script/"+apiID+".script";
				var apiSetting = "<?=env('API_URL');?>/public/panel/testing/api/api_setting/"+apiID+".setting";
				$.post( apiScript, {}, 
					function(retVal){ 
						$("#apiScript").html(retVal); 
						if( isdDefault ){ getSettingDefault(apiSetting, server); }
						else{ getSetting(apiSetting, server); }
					} 
				)
				.fail(function (xhr){ $("#sourceAPI").text(xhr.status+": "+xhr.statusText); $("#responseAPI").html(""); });
				break;
			}
		}
	}
	//-----------------------------------------------------------------
	function getSettingDefault(inURL, server){
		$.post(
			inURL,
			{},
			function(retVal){ 
				settings=JSON.parse(retVal);
				settings.url = server+settings.url;
				var oData = "";
				if(typeof  settings.data!=='undefined'){
					for(var i in settings.data){ 
						oData+= '<div><div class="input-group m-b-1" style="margin:5px 0 15px;"><span class="input-group-addon">'+i+'</span>';
						oData+="<input type='text' class='form-control' id='"+i+"' value='"+settings.data[i]+"'/></div></div>"; 
					}
					oData = "Data: <pre>"+oData+"</pre>";
				}
				$("#sourceAPI").html(
					"URL: "+settings.url+"<br/><br/>"+
					"Method: "+settings.method+"<br/><br/>"+
					oData+
					'<button class="btn btn-info form-control" onClick="sendRequest()">Send request</button>'
				);
			}
		).fail(function (xhr){ $("#sourceAPI").text(xhr.status+": "+xhr.statusText); $("#responseAPI").html(""); });
	}
	//-----------------------------------------------------------------
	function validateData(){
		$("div.error").remove();
		var retVal=false;
		$("#sourceAPI pre input").each(function(){
			if($(this).val().trim()==''){
				$(this).parent().parent().prepend("<div class='error'>Error: requird this value</div>")
				retVal=true;
			}
		});
		return retVal;
	}
	//-----------------------------------------------------------------
	function callEraseThis(obj){ $(obj).parent().parent().remove(); }
	//-----------------------------------------------------------------
	function callMoreItem(obj, caption, name){
		var moreIiem = "";
		moreIiem+= '<div><div class="input-group m-b-1" style="margin:5px 0 15px;"><span class="input-group-addon">'+caption+'</span>';
		moreIiem+="<input type='text' class='form-control' name='"+name+"' value='' style='max-width:86%;margin-right:10px;'/>"; 
		moreIiem+='<button class="btn btn-danger" style="padding:8px;" onClick="callEraseThis($(this))"><i class="fa fa-trash"></i></button></div></div>';
		$("#moreIiem").append(moreIiem);

		$("#moreIiem>div:last-child input").focus();
	}
	//-----------------------------------------------------------------
</script>
