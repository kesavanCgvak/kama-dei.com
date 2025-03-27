<style>
	#tabDIV{ width: 100%; text-align: center; display: block}
	#tabDIV button{ color: #000; background-color: #f8f8f8; border-color: #f0f0f0; font-weight: 100; width: 200px; }
	#tabDIV button.active{ color: #fff; background-color: #2a94d6; border-color: #2585c1; }
	
	#mapDIVs>div{ display: none; width: 100%; }
</style>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<div id="tabDIV">
	<div class="btn-group btn-group-lg" role="group" aria-label="Basic example">
		<button type="button" class="btn active" data-map_type="rpa">RPA</button>
		<button type="button" class="btn" data-map_type="liveAgent">Live Agent</button>
<!--
		<button type="button" class="btn" data-map_type="lex">Lex</button>
-->
		<button type="button" class="btn" data-map_type="kass">KaaS</button>
	</div>
</div>
<div id="mapDIVs">
	<div id="rpa" style="display: block; padding-top:10px">
		<div id="rpa_wait" align="center"><i class="fa fa-refresh fa-spin fa-5x" style="margin:auto"></i></div>
		<div id="rpa_others">
			<div class="btn-group" role="group" aria-label="Basic example">
				<button type="button" class="btn btn-info" onClick="openRpaMapping()" id="rpaFrameBTN">Mapping</button>
				<button type="button" class="btn btn-secondary" onClick="openRpaType()" id="rpaTypeFrameBTN">Types</button>
			</div>
			<iframe src="/panel/rpa/mapping" width="100%" height="100%" frameborder="0" id="rpaFrame"></iframe>
			<iframe src="/panel/rpa/types" width="100%" height="100%" frameborder="0" id="rpaTypeFrame" style="display: none"></iframe>
		</div>
	</div>
	<div id="liveAgent" style="padding-top:10px">
		<div id="liveAgent_wait" align="center"><i class="fa fa-refresh fa-spin fa-5x" style="margin:auto"></i></div>
		<div id="liveAgent_others">
			<div class="btn-group" role="group" aria-label="Basic example">
			</div>
			<iframe src="/panel/live_agent/mapping" width="100%" height="100%" frameborder="0" id="liveAgentFrame"></iframe>
		</div>
	</div>
<!--	
	<div id="lex" style="padding-top:10px">
		<div id="lex_wait" align="center"><i class="fa fa-refresh fa-spin fa-5x" style="margin:auto"></i></div>
		<div id="lex_others">
			<div class="btn-group" role="group" aria-label="Basic example">
				<button type="button" class="btn btn-info" onClick="openLexMapping()" id="lexFrameBTN">Mapping</button>
				<button type="button" class="btn btn-secondary" onClick="openLexSetting()" id="lexSettingFrameBTN">Setting</button>
			</div>
			<iframe src="/panel/lexjoint/mapping" width="100%" height="100%" frameborder="0" id="lexFrame"></iframe>
			<iframe src="/panel/lexjoint/settings" width="100%" height="100%" frameborder="0" id="lexSettingFrame" style="display: none"></iframe>
		</div>
	</div>
-->
	<div id="kass" style="padding-top:10px">
		<div id="kass_wait" align="center"><i class="fa fa-refresh fa-spin fa-5x" style="margin:auto"></i></div>
		<div id="kass_others">
			<div class="btn-group" role="group" aria-label="Basic example">
				<button type="button" class="btn btn-info" onClick="openKaaSMapping()" id="kaasFrameBTN">Mapping</button>
				<button type="button" class="btn btn-secondary" onClick="openKaaSSetting()" id="kaasSettingFrameBTN">Setting</button>
			</div>
			<iframe src="/panel/kaasmapping/mapping" width="100%" height="100%" frameborder="0" id="kaasFrame"></iframe>
			<iframe src="/panel/kaasmapping/settings" width="100%" height="100%" frameborder="0" id="kaasSettingFrame" style="display: none"></iframe>
		</div>
	</div>
</div>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<script type="application/javascript">
	$("#liveAgentFrame").ready(function(){
		$.post("<?=env('API_URL');?>/api/set-menu/0", null, null);
		$("#liveAgent_other").hide();
		$("#liveAgent_wait").show();
		setTimeout(
			function(){
				document.getElementById('liveAgentFrame').contentWindow.postMessage('off','<?=env('API_URL');?>')
			}, 2000);
		setTimeout(
			function(){
				document.getElementById('liveAgentFrame').location.reload(true);
			}, 2200);
		setTimeout(
			function(){
				$("#liveAgent_other").show();
				$("#liveAgent_wait").hide();
			}, 2500);
	});
/*
	$("#lexFrame").ready(function(){
		$.post("<?=env('API_URL');?>/api/set-menu/0", null, null);
		$("#lex_others").hide();
		$("#lex_wait").show();
		setTimeout(
			function(){
				document.getElementById('lexFrame'       ).contentWindow.postMessage('off','<?=env('API_URL');?>')
				document.getElementById('lexSettingFrame').contentWindow.postMessage('off','<?=env('API_URL');?>')
			}, 2000);
		setTimeout(
			function(){
				document.getElementById('lexFrame'       ).contentDocument.location.reload(true);
				document.getElementById('lexSettingFrame').contentDocument.location.reload(true);
			}, 2200);
		setTimeout(
			function(){
				$("#lex_wait").hide();
				$("#lex_others").show();
			}, 2500);
	});
*/
	$("#kaasFrame").ready(function(){
		$.post("<?=env('API_URL');?>/api/set-menu/0", null, null);
		$("#kaas_others").hide();
		$("#kass_wait").show();
		setTimeout(
			function(){
				document.getElementById('kaasFrame').contentWindow.postMessage('off','<?=env('API_URL');?>')
				document.getElementById('kaasSettingFrame').contentWindow.postMessage('off','<?=env('API_URL');?>')
			}, 2000);
		setTimeout(
			function(){
				document.getElementById('kaasFrame'    ).contentDocument.location.reload(true);
				document.getElementById('kaasSettingFrame').contentDocument.location.reload(true);
			}, 2200);
		setTimeout(
			function(){
				$("#kass_wait").hide();
				$("#kaas_others").show();
			}, 2500);
	});

	$("#rpaFrame").ready(function(){
		$.post("<?=env('API_URL');?>/api/set-menu/0", null, null);
		$("#rpa_others").hide();
		$("#rpa_wait").show();
		setTimeout(
			function(){
				document.getElementById('rpaFrame'    ).contentWindow.postMessage('off','<?=env('API_URL');?>')
				document.getElementById('rpaTypeFrame').contentWindow.postMessage('off','<?=env('API_URL');?>')
			}, 2000);
		setTimeout(
			function(){
				document.getElementById('rpaFrame'    ).contentDocument.location.reload(true);
				document.getElementById('rpaTypeFrame').contentDocument.location.reload(true);
			}, 2200);
		setTimeout(
			function(){
				$("#rpa_wait").hide();
				$("#rpa_others").show();
			}, 2500);
	});

	$(function(){
		$(window).bind('beforeunload', function(){
			$.post("<?=env('API_URL');?>/api/set-menu/1", null, null);
		});
		
		$("#tabDIV button").on('click', function(){
			$("#tabDIV button").removeClass('active');
			$(this).addClass('active');
			
			$("#mapDIVs>div").hide();
			$("#mapDIVs #"+$(this).data('map_type')).show();
		});
		
	});
	
	
	function openRpaMapping(){
		$("#rpaFrame").show();
		$("#rpaTypeFrame").hide();
		$('#rpaFrameBTN').removeClass('btn-secondary').addClass('btn-info');
		$('#rpaTypeFrameBTN').removeClass('btn-info').addClass('btn-secondary');
	}
	function openRpaType(){
		$("#rpaFrame").hide();
		$("#rpaTypeFrame").show();
		$('#rpaFrameBTN').removeClass('btn-info').addClass('btn-secondary');
		$('#rpaTypeFrameBTN').removeClass('btn-secondary').addClass('btn-info');
	}
/*
	function openLexMapping(){
		$("#lexFrame").show();
		$("#lexSettingFrame").hide();
		$('#lexFrameBTN').removeClass('btn-secondary').addClass('btn-info');
		$('#lexSettingFrameBTN').removeClass('btn-info').addClass('btn-secondary');
	}
	function openLexSetting(){
		$("#lexFrame").hide();
		$("#lexSettingFrame").show();
		$('#lexFrameBTN').removeClass('btn-info').addClass('btn-secondary');
		$('#lexSettingFrameBTN').removeClass('btn-secondary').addClass('btn-info');
	}
*/
	function openKaaSMapping(){
		$("#kaasSettingFrame").hide();
		$("#kaasFrame").show();
		$('#kaasSettingFrameBTN').removeClass('btn-info').addClass('btn-secondary');
		$('#kaasFrameBTN').removeClass('btn-secondary').addClass('btn-info');
	}
	function openKaaSSetting(){
		$("#kaasFrame").hide();
		$("#kaasSettingFrame").show();
		$('#kaasFrameBTN').removeClass('btn-info').addClass('btn-secondary');
		$('#kaasSettingFrameBTN').removeClass('btn-secondary').addClass('btn-info');
	}
</script>
<?php
/*
				<button type="button" class="btn btn-info" onClick="openKaaSMapping()" id="kaasFrameBTN">Mapping</button>
				<button type="button" class="btn btn-secondary" onClick="openKaaSSetting()" id="kaasSettingFrameBTN">Setting</button>
			</div>
			<iframe src="/panel/kaasmapping/mapping" width="100%" height="100%" frameborder="0" id="kaasFrame"></iframe>
			<iframe src="/panel/kaasmapping/settings" width="100%" height="100%" frameborder="0" id="kaasFrame" style="display: none">*/