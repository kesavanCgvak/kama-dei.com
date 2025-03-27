<style>
div.card-detail{ height:80vh; }
</style>
<h1>Testing</h1>
<?php if($levelID==1 && $orgID==0): ?>
<h3 style="margin-left:20px;"><a href="/panel/testing/api">APIs</a></h3>
<?php endif; ?>
<h3 style="margin-left:20px;"><a href="/panel/testing/nlu">NLU</a></h3>
<?php if($orgKaaS3PB==1): ?>
<?php /*
<h3 style="margin-left:20px;"><a href="/panel/testing/kaas-kama">KaaS [Kama-DEI]</a></h3>
*/ ?>
<h3 style="margin-left:20px;"><a href="/panel/testing/kaas">KaaS</a></h3>
<?php endif; ?>
<?php if($levelID==1 && $orgID==0): ?>
<h3 style="margin-left:20px;"><a href="/panel/testing/id_reg">Identification_Registration</a></h3>
<?php endif; ?>
