<?php
	$botName   = session('botName'  , 'PizzaCo');
	$botAlias  = session('botAlias' , 'PizzaCoAlias');
	$orgId     = session('orgId'    , '15');
	$lexUserId = session('lexUserId', '371');
	$inttKrId  = session('inttKrId' , '1080');
	$slotKrId  = session('slotKrId' , '3888');
	$valuKrId  = session('valuKrId' , '3890');
	$findKrId  = session('findKrId' , '997');
	$intentNm  = session('intentNm', 'OrderChickenWings');
	$slotName  = session('slotName', 'Style');
	$valuName  = session('valuName', 'Breaded');
?>
<style>
	.input-group-addon{ width:100px; text-align:left; font-size: 12px; }
	.input-group.m-b-1{ margin-bottom:10px; width: 100%; }
</style>
<div id="mapp" class="apiSources" style="width:1111px">
	<button class="btn btn-danger" onClick="callChaneParams()">Change Parameters</button>
	
	<hr width="60%"/>
	<h6>$lexTMP = new \App\Lex\LexClass('<?="bot='".$botName."', alias='".$botAlias."', orgid=".$orgId.", userid=".$lexUserId;?>)</h6>
	<?php $lexTMP = new \App\Lex\LexClass($botName, $botAlias, $orgId, $lexUserId); ?>
	<pre><?php print_r($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<h6>$lexTMP->findIntent(kr_id=<?=$inttKrId;?>)</h6>
	<?php $lexTMP->findIntent($inttKrId); ?>
	<pre><?php print_r($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<h6>$lexTMP->findSlot(slot_kr_id=<?=$slotKrId;?>)</h6>
	<?php $lexTMP->findSlot($slotKrId); ?>
	<pre><?php print_r($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<h6>$lexTMP->findSlot(slot_kr_id=<?=$slotKrId;?>, value_kr_id=<?=$valuKrId;?>)</h6>
	<?php $lexTMP->findSlot($slotKrId, $valuKrId); ?>
	<pre><?php print_r($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<h6>$lexTMP->findSlot(slot_kr_id=<?=$slotKrId;?>, value_kr_id=123)</h6>
	<?php $lexTMP->findSlot($slotKrId, 123); ?>
	<pre><?php print_r($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<h6>json_encode($lexTMP->getData())</h6>
	<pre><?=json_encode($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<hr width="80%"/>
	<hr width="60%"/>
	<h6>$lexTMP->findKR(kr_id=<?=$findKrId;?>)</h6>
	<?php $lexTMP->findKR($findKrId); ?>
	<pre><?php print_r($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<h6>json_encode($lexTMP->getData())</h6>
	<pre><?=json_encode($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<hr width="80%"/>
	<hr width="60%"/>
	<h6>$lexTMP->findKR(kr_id=[<?="123,{$valuKrId},{$findKrId}";?>])</h6>
	<?php $lexTMP->findKR([123,$valuKrId,$findKrId]); ?>
	<pre><?php print_r($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<h6>json_encode($lexTMP->getData())</h6>
	<pre><?=json_encode($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<hr width="80%"/>
	<hr width="60%"/>
	<h6>$lexTMP->findSlotName(intent='<?=$intentNm;?>', slotName='<?=$slotName;?>',valueName='<?=$valuName;?>')</h6>
	<?php $lexTMP->findSlotName($intentNm, $slotName,$valuName); ?>
	<pre><?php print_r($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<h6>json_encode($lexTMP->getData())</h6>
	<pre><?=json_encode($lexTMP->getData()); ?></pre>
</div>

<script src="/public/js/app.js"></script>
<script type="application/javascript">
	$(function(){
//		$("#mapp").hide();
//		$("#responseAPI").parent().html($("#aaa"));
	});
	function callChaneParams(){ $("#changeParams").modal({backdrop:'static', keyboard:false}); }

	function callChange(){
		$("#changeParams").modal("hide");
		var data = {};
		data.botName   = $("#botName"  ).val().trim();
		data.botAlias  = $("#botAlias" ).val().trim();
		data.orgId     = $("#orgId"    ).val().trim();
		data.lexUserId = $("#lexUserId").val().trim();
		data.inttKrId  = $("#inttKrId" ).val().trim();
		data.slotKrId  = $("#slotKrId" ).val().trim();
		data.valuKrId  = $("#valuKrId" ).val().trim();
		data.findKrId  = $("#findKrId" ).val().trim();

		data.intentNm  = $("#intentNm" ).val().trim();
		data.slotName  = $("#slotName" ).val().trim();
		data.valuName  = $("#valuName" ).val().trim();

		$.post( "<?=env('API_URL');?>/api/dashboard/lex/testing/set", data, function(retVal){ window.location.reload(); } );
	}
</script>
<div class="modal fade" id="changeParams">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Parameters</h5>
			</div>
			<div class="modal-body">
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">Bot Name</span>
					<input class="form-control" id="botName" value="<?=$botName;?>" />
				</div>
				
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">Bot Alias</span>
					<input class="form-control" id="botAlias" value="<?=$botAlias;?>" />
				</div>
				
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">Org ID</span>
					<input class="form-control" id="orgId" value="<?=$orgId;?>" />
				</div>
				
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">User ID</span>
					<input class="form-control" id="lexUserId" value="<?=$lexUserId;?>" />
				</div>
				
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">Intenet KR ID</span>
					<input class="form-control" id="inttKrId" value="<?=$inttKrId;?>" />
				</div>
				
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">Slot KR ID</span>
					<input class="form-control" id="slotKrId" value="<?=$slotKrId;?>" />
				</div>
				
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">Value KR ID</span>
					<input class="form-control" id="valuKrId" value="<?=$valuKrId;?>" />
				</div>
				<!-- ---------------------------------------------------------------- -->
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">Find KR ID</span>
					<input class="form-control" id="findKrId" value="<?=$findKrId;?>" />
				</div>
				<!-- ---------------------------------------------------------------- -->
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">Intent</span>
					<input class="form-control" id="intentNm" value="<?=$intentNm;?>" />
				</div>
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">Slot Name</span>
					<input class="form-control" id="slotName" value="<?=$slotName;?>" />
				</div>
				<div class="input-group m-b-1" style="">
					<span class="input-group-addon">Value Name</span>
					<input class="form-control" id="valuName" value="<?=$valuName;?>" />
				</div>
				<!-- ---------------------------------------------------------------- -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" onClick="callChange()">Change</button>
				<button type="button" class="btn btn-secondary" style="float:left;" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
