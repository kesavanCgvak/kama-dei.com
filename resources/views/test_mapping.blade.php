<style>
	.mainBox{
		display: flex;
		border-bottom: 1px solid #888;
		margin-bottom: 40px;
		padding-bottom: 10px;
	}
	.childBox{
		border: 1px solid #eee;
		padding: 5px 10px;
		border-radius: 5px;
		width: 33%;
		margin: 0.1%;
		overflow: auto;
	}
</style>
<h1>Mapping Test</h1>

<div class="mainBox">
	<?php
	//----------------------------------------------------
	echo "<div class='childBox'>";
	//----------------------------------------------------
	$kr = 1844;
	$pr = '1iocqx';
	//----------------------------------------------------
	echo "<p><b>&#36;map<small> = new </small>\App\Mapping\MappingClass(<small>'{$pr}'</small>);</b></p><hr/>";
	$tmp = new \App\Mapping\MappingClass($pr);
	
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php
	
	$a = $tmp->findIntent($kr);
	$a = (($a) ?'true' :'false');
	echo "<p><b>&#36;map->findIntent(<small>{$kr}</small>);</b>=<i>{$a}</i></p>";
	
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php

	$a = $tmp->findSlot($kr);
	$a = (($a) ?'true' :'false');
	echo "<p><b>&#36;map->findSlot(<small>{$kr}</small>);</b>=<i>{$a}</i></p>";
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php
	//----------------------------------------------------
	echo "</div>";
	echo "<div class='childBox'>";
	//----------------------------------------------------
	$kr = 1378;
	$pr = '1iocqx';
	//----------------------------------------------------
	echo "<p><b>&#36;map<small> = new </small>\App\Mapping\MappingClass(<small>'{$pr}'</small>);</b></p><hr/>";
	$tmp = new \App\Mapping\MappingClass($pr);
	
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php
	
	$a = $tmp->findIntent($kr);
	$a = (($a) ?'true' :'false');
	echo "<p><b>&#36;map->findIntent(<small>{$kr}</small>);</b>=<i>{$a}</i></p>";
	
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php

	$a = $tmp->findSlot($kr);
	$a = (($a) ?'true' :'false');
	echo "<p><b>&#36;map->findSlot(<small>{$kr}</small>);</b>=<i>{$a}</i></p>";
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php
	//----------------------------------------------------
	echo "</div>";
	echo "<div class='childBox'>";
	//----------------------------------------------------
	$kr = 4209;
	$pr = '2jhlos';
	//----------------------------------------------------
	echo "<p><b>&#36;map<small> = new </small>\App\Mapping\MappingClass(<small>'{$pr}'</small>);</b></p><hr/>";
	$tmp = new \App\Mapping\MappingClass($pr);
	
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php
	
	$a = $tmp->findIntent($kr);
	$a = (($a) ?'true' :'false');
	echo "<p><b>&#36;map->findIntent(<small>{$kr}</small>);</b>=<i>{$a}</i></p>";
	
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php

	$kr = 1378;
	$a  = $tmp->findSlot($kr);
	$a  = (($a) ?'true' :'false');
	echo "<p><b>&#36;map->findSlot(<small>{$kr}</small>);</b>=<i>{$a}</i></p>";
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php
	//----------------------------------------------------
	echo "</div>";
	echo "<div class='childBox'>";
	//----------------------------------------------------
	$kr = 4255;
	$pr = '2jhlos';
	//----------------------------------------------------
	echo "<p><b>&#36;map<small> = new </small>\App\Mapping\MappingClass(<small>'{$pr}'</small>);</b></p><hr/>";
	$tmp = new \App\Mapping\MappingClass($pr);
	
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php
	
	$a = $tmp->findIntent($kr);
	$a = (($a) ?'true' :'false');
	echo "<p><b>&#36;map->findIntent(<small>{$kr}</small>);</b>=<i>{$a}</i></p>";
	
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php

	$kr = 3911;
	$a  = $tmp->findSlot($kr);
	$a  = (($a) ?'true' :'false');
	echo "<p><b>&#36;map->findSlot(<small>{$kr}</small>);</b>=<i>{$a}</i></p>";
	echo "<p><i>&#36;map->getData();</i></p>";
	?><pre><?=print_r($tmp->getData(), 1);?></pre><hr/><?php
	//----------------------------------------------------
	echo "</div>";
	//----------------------------------------------------
	?>
</div>
<h3>\App\Mapping\MappingClass::isActive(org_id, portal)</h3>
<div class="mainBox">
	<?php
	//----------------------------------------------------
	echo "<div class='childBox'>";
	//----------------------------------------------------
	$org_id = 1;
	$portal = "11m7er";
	echo "<p>org_id:{$org_id} , portal:{$portal}</p>";
	if(\App\Mapping\MappingClass::isActive($org_id, $portal)){
		echo "true";
	}else{
		echo "false";
	}
	//----------------------------------------------------
	echo "</div>";
	//----------------------------------------------------
	echo "<div class='childBox'>";
	//----------------------------------------------------
	$org_id = 20;
	$portal = "11m7er";
	echo "<p>org_id:{$org_id} , portal:{$portal}</p>";
	if(\App\Mapping\MappingClass::isActive($org_id, $portal)){
		echo "true";
	}else{
		echo "false";
	}
	//----------------------------------------------------
	echo "</div>";
	//----------------------------------------------------
	echo "<div class='childBox'>";
	//----------------------------------------------------
	$org_id = 7;
	$portal = "11m7er";
	echo "<p>org_id:{$org_id} , portal:{$portal}</p>";
	if(\App\Mapping\MappingClass::isActive($org_id, $portal)){
		echo "true";
	}else{
		echo "false";
	}
	//----------------------------------------------------
	echo "</div>";
	//----------------------------------------------------
	?>
</div>
