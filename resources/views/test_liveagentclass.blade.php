<?php
$orgId  = $_GET['org_id'];
$portal = $_GET['portal'];
$kr     = $_GET['kr'];
$krs    = explode(",", $kr);
?>
<h1>LiveAgentClass Test</h1>

<div>$orgId=<?=$orgId;?>;</div>
<div>$portalCode='<?=$portal;?>';</div>
<b>new App\LiveAgent\LiveAgentClass(<?=$orgId;?>, '<?=$portal;?>');</b>
<?php
$liveAgent = new App\LiveAgent\LiveAgentClass($orgId, $portal);
?>
<h3>construct</h3>
<pre><?=print_r($liveAgent->getData(), 1);?></pre>

<h3>isActive(<?=$orgId;?>, '<?=$portal;?>') = <?=(($liveAgent->isActive($orgId, $portal)) ?'true' :'false');?></h3>

<?php
$liveAgent->findIntent($krs[0]);
?>
<h3>findIntent( <?=$krs[0];?> )</h3>
<pre><?=print_r($liveAgent->getData(), 1);?></pre>

<?php
$liveAgent->findKR($krs);
?>
<h3>findKR( <?=(count($krs)==1) ?$kr :"[{$kr}]";?> )</h3>
<pre><?=print_r($liveAgent->getData(), 1);?></pre>


<?php
//$a = new App\Models\SolutionRelation;
//echo $a->getUniqueProblem(573);
?>