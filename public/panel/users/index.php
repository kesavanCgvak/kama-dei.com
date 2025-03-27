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

#editUser, #addUser {
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

#editUser > form, #addUser > form {
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

#editUser.show, #addUser.show{ display: block; }
#editUser > form input, #addUser > form input{ width: 250px; }

.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
.row-actions{ text-align: center; }
.row-actions > a:first-child{ padding-right: 10px; }
#users td:nth-child(3),
#users td:nth-child(4),
#users td:nth-child(5),
#users td:nth-child(6)
	{ font-size:13px; }
#users td:nth-child(7),
#users td:nth-child(8)
	{ font-size:12px; }

#users td:nth-child(6),
#users td:nth-child(7),
#users td:nth-child(8)
	{ text-align:center;vertical-align:middle; }
#users th:nth-child(5),
#users td:nth-child(6)
	{ width:50px; }
</style>
<div id="users"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL    = "<?=env('API_URL');?>";
	var orgID     = "<?=$orgID;?>";
	var KAMARONID = '<?=Config::get('kama_dei.static.KAMARONID',0);?>';
</script>
<script src="/public/js/app.js"></script>
