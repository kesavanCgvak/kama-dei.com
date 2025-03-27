<style>
#showError{
    position: fixed;
    top: 10px;
    left: 10px;
    color: #fff;
    background: #d25c5c;
    z-index: 9999;
    min-width: 150px;
    width: auto;
    padding: 8px 15px;
    border: 1px dotted #fff;
    border-radius: 8px;
    box-shadow: 0 0 0 3px #d25c5c;
	display:none;
}

#editOrganization, #addOrganization {
	display: none;
	position: fixed;
	z-index: 1000;
	background: rgba(0, 0, 0, 0.6);
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	margin: auto;
}

#editOrganization > form, #addOrganization > form {
	position: absolute;
	margin: auto;
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	width: 280px;
	height: 370px;
	width: fit-content;
	height: fit-content;
	background: white;
	padding: 15px;
}

#editOrganization.show, #addOrganization.show{ display: block; }
#editOrganization > form input, #addOrganization > form input{ width: 250px; }

.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
.row-actions{ text-align: center; }
.row-actions > a:first-child{ padding-right: 10px; }
#organization td:nth-child(1){ text-align:right; }
#organization th:nth-child(2),
#organization td:nth-child(2)
	{ width:50% !important; }
#organization th:nth-child(4),
#organization td:nth-child(4)
	{ width:60px !important;font-size:13px; }
</style>
<?php if($orgID==0): ?>
<div id="organization"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
</script>
<script src="/public/js/app.js"></script>
<?php else: ?>
<div id="organization" style="min-height:75vh">
	<?php $tmp = new \App\Organization(); ?>
	<h2><?=$tmp->getName($orgID);?></h2>
	<?php
	$usersTBL = new \App\User();
	$orgAdmins = $usersTBL->where("isAdmin", "=", 1)->where("orgID", "=", $orgID)->get();
	if(!$orgAdmins->isEmpty()){ 
		?>
		<ol style="display:inline-block;vertical-align:top;">
			<?php foreach($orgAdmins as $orgAdmin){ ?><li>Admin: <?=$orgAdmin->userName;?> <?=$orgAdmin->email;?></li><?php } ?>
		</ol>
		<?php
	}
	?>
	<img src="/public/dist/images/logo/logo.png" width="200" style="display:inline-block;margin-left:20px;border:1px solid #999;" />
</div>
<?php endif; ?>
