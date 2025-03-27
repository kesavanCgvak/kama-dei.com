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

#editAccessLevel, #addAccessLevel {
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

#editAccessLevel > form, #addAccessLevel > form {
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

#editAccessLevel.show, #addAccessLevel.show{ display: block; }
#editAccessLevel > form input, #addAccessLevel > form input{ width: 250px; }

.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
.row-actions{ text-align: center; }
.row-actions > a:first-child{ padding-right: 10px; }
/*button.react-bs-table-add-btn{ display:none; }*/

table.table tbody td>a.green{ color:green; }
table.table tbody td>a.gray{ color:gray; }
table.table tbody td>a.green:hover,
table.table tbody td>a.gray:hover{ color:red; }
table.table tbody td:nth-child(3){ width:40px;font-size:12px; }
</style>
<div id="accesslevel"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
</script>
<script src="/public/js/app.js"></script>
