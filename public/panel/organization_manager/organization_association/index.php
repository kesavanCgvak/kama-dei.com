<style>
#editOrganizationAssociation, #addOrganizationAssociation {
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

#editOrganizationAssociation.show, #addOrganizationAssociation.show {
	display: block;
}

#editOrganizationAssociation > form, #addOrganizationAssociation > form {
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

#editOrganizationAssociation > form input, #addOrganizationAssociation > form input, #editOrganizationAssociation > form select, #addOrganizationAssociation > form select, #insertItem {
	width: 250px !important;
}
#insertItem, #saveItem { width: 250px !important; float:right; }
}

.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }

.row-actions{ text-align: center; }

.row-actions > a:first-child{ padding-right: 10px; }
#organizationAssociation table th:nth-child(2),
#organizationAssociation table td:nth-child(2){ font-size:13px; }
#organizationAssociation table th:nth-child(1),
#organizationAssociation table td:nth-child(1),
#organizationAssociation table th:nth-child(3),
#organizationAssociation table td:nth-child(3){ width:26% !important;font-size:13px; }
#organizationAssociation table th:nth-child(4),
#organizationAssociation table td:nth-child(4){ width:50px !important;font-size:12px;text-align:center }
#organizationAssociation table td:nth-child(5){ width:30px !important;font-size:11px; }
#organizationAssociation table td:nth-child(5) a{ display:block;margin-bottom:5px;padding:0; }
/*
#organizationAssociation table th:nth-child(3)
#organizationAssociation table th:nth-child(4)
#organizationAssociation table th:nth-child(5){ width:80px !important; }

#organizationAssociation table td:nth-child(2),
#organizationAssociation table td:nth-child(3),
#organizationAssociation table td:nth-child(4),
#organizationAssociation table td:nth-child(5){ width:80px !important;font-size:12px; }

#organizationAssociation table td:nth-child(2),
#organizationAssociation table td:nth-child(5){ text-align:center; }

#organizationAssociation table td:nth-child(4){ width:230px !important; }
*/
.col-relationTypeId{ width:100%; }

.form-group i.fa-search:hover{color:red;cursor:pointer; }
</style>
<div id="organizationAssociation"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
	var table;
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
$(function(){
});
</script>
