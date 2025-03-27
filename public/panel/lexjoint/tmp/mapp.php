<?php
$botName = 'OrderPizza';
$botAlias = 'OrderPizzaAlias';
$orgId = 2;
$lexUserId = 278;
$inttKrId = 1080;
?>
<div id="mapp" class="apiSources" style="width:1111px">

	<h6>$lexTMP = new \App\Lex\LexClass('<?="bot='".$botName."', alias='".$botAlias."', orgid=".$orgId.", userid=".$lexUserId;?>)</h6>
	<?php $lexTMP = new \App\Lex\LexClass($botName, $botAlias, $orgId, $lexUserId); ?>
	<pre><?php print_r(($lexTMP->getData())); ?></pre>

	<hr width="60%"/>
	<h6>$lexTMP->findIntent(kr_id=<?=$inttKrId;?>)</h6>
	<?php $lexTMP->findIntent($inttKrId); ?>
	<pre><?php print_r(($lexTMP->getData())); ?></pre>

	<?php $slotKrId=1892; ?>
	<hr width="60%"/>
	<h6>$lexTMP->findSlot(slot_kr_id=<?=$slotKrId;?>)</h6>
	<?php $lexTMP->findSlot($slotKrId); ?>
	<pre><?=print_r($lexTMP->getData()); ?></pre>

	<?php $slotKrId=1892; $valuKrId=1380; ?>
	<hr width="60%"/>
	<h6>$lexTMP->findSlot(slot_kr_id=<?=$slotKrId;?>, value_kr_id=<?=$valuKrId;?>)</h6>
	<?php $lexTMP->findSlot($slotKrId, $valuKrId); ?>
	<pre><?=print_r($lexTMP->getData()); ?></pre>

	<?php $slotKrId=367; $valuKrId=2027; ?>
	<hr width="60%"/>
	<h6>$lexTMP->findSlot(slot_kr_id=<?=$slotKrId;?>, value_kr_id=<?=$valuKrId;?>)</h6>
	<?php $lexTMP->findSlot($slotKrId, $valuKrId); ?>
	<pre><?=print_r($lexTMP->getData()); ?></pre>

	<?php $slotKrId=367; $valuKrId=123; ?>
	<hr width="60%"/>
	<h6>$lexTMP->findSlot(slot_kr_id=<?=$slotKrId;?>, value_kr_id=<?=$valuKrId;?>)</h6>
	<?php $lexTMP->findSlot($slotKrId, $valuKrId); ?>
	<pre><?=print_r($lexTMP->getData()); ?></pre>

	<hr width="60%"/>
	<h6>json_encode($lexTMP->getData())</h6>
	<pre><?=json_encode($lexTMP->getData()); ?></pre>
</div>

<script type="application/javascript">
	$(function(){
//		$("#mapp").hide();
//		$("#responseAPI").parent().html($("#aaa"));
	});
</script>