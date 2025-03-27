<?php
$requestRelationName = "";
if(trim($requestTableName)!=''){
	$tmp = \App\Relation::
			leftJoin('term as leftTerm', 'relation.leftTermId', '=', 'leftTerm.termId')->
            leftJoin('term as rightTerm', 'relation.rightTermId', '=', 'rightTerm.termId')->
            leftJoin('relation_type', 'relation.relationTypeId', '=', 'relation_type.relationTypeId')->
			where('relationId', trim($requestTableName));
	if($tmp->count()==0){ $requestTableName = ''; }
	else{
		$requestRelationName = $tmp->select(
                \DB::raw('CONCAT(leftTerm.termName," ",relation_type.relationTypeName," ",rightTerm.termName) as knowledgeRecordName')
		)->first()['knowledgeRecordName'];
	}
}
$requestTableName = trim($requestTableName);
$session->put('leftStaticKR', $requestTableName);
?>
<link href="https://unpkg.com/ionicons@4.2.6/dist/css/ionicons.min.css" rel="stylesheet"/>
<style>
#editRelationLink, #addRelationLink {
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

#editRelationLink.show, #addRelationLink.show {
	display: block;
}

#editRelationLink > form, #addRelationLink > form {
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

#editRelationLink > form input, #addRelationLink > form input{ width: 250px; }
.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }

#ownerList{ max-width:300px; }
.modal-footer{ padding:10px 20px !important; }
#linkLeftKRTable th, #linkLeftKRTable td{ font-size:13px; }
#linkLeftKR .btn-group .btn{ height:34px !important;width:34px !important;padding:0; }


.row-actions > a:first-child{padding-right:10px; }
#relationLink table th{ font-size:13px; }
#relationLink table td:nth-child(1){ font-size:13px; }
#relationLink table td:nth-child(2){ font-size:12px;width:100px;text-align:left;vertical-align:middle; }
#relationLink table td:nth-child(3){ font-size:13px;width:200px;text-align:left;vertical-align:middle; }
#relationLink table td:nth-child(4){ font-size:12px;width: 55px;text-align:center;vertical-align:middle; }
#relationLink table td:nth-child(5){ font-size:12px;width: 80px;text-align:center;vertical-align:middle; }
#relationLink table td:nth-child(6){ font-size:12px;width: 90px;text-align:center;vertical-align:middle; }
#relationLink table td:nth-child(7){ font-size:12px;width:130px;text-align:left;vertical-align:middle; }
#relationLink table td:nth-child(8){ font-size:12px;width: 80px;text-align:center;vertical-align:middle; }
#relationLink table td:nth-child(9){ font-size:12px;width:40px;text-align:center;vertical-align:middle; }
	
#selectListTBL th{ font-size:13px; }
#selectListTBL td{ font-size:12px; }
#selectListTBL tr:hover>td{ color:blue !important;cursor:pointer; }

#relationLink table th:nth-child(4),
#relationLink table td:nth-child(4),

#relationLink table th:nth-child(8),
#relationLink table td:nth-child(8)
	{ display:none; }
</style>

<select class="form-control" id="relationLinkOwnersList" style="display:none">
	<option value="-1" selected="selected">Owner All</option>
</select>
<div id="relationLink"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL = "<?=env('API_URL');?>";
	var orgID  = "<?=$orgID;?>";
	var userID = "<?=session()->get('userID');?>";
	var BASE_ORGANIZATION = '<?=env('BASE_ORGANIZATION');?>';
	var leftStaticKR    = '<?=$requestTableName;?>';
	var leftStaticKRTXT = '<?=$requestRelationName;?>';
	
	<?php if(trim($requestTableName)!=''): ?>
	var tableReorderRow = true;
	<?php else: ?>
	var tableReorderRow = false;
	<?php endif; ?>

</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
$(function(){
	//--------------------------------------------------------
	var tmp = $("#relationLinkOwnersList");
	$("#relationLink .pull-right.search").prepend(tmp);
	$("#relationLink .pull-right.search .form-control").css('width', '50%');
	$("#relationLink .pull-right.search .form-control").css('display', 'inline-block');
	$("#relationLink table").bootstrapTable().on('refresh.bs.table', function(){ getOwnersList($("#relationLinkOwnersList").val()); });
	//--------------------------------------------------------
	getOwnersList(-1);
	//--------------------------------------------------------
});
//------------------------------------------------------------
function getOwnersList(id){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/relation_link/relationlinkowners/<?=$orgID;?>',
		function(retVal){
			$("#relationLinkOwnersList option").remove();
			$("#relationLinkOwnersList").append('<option value="-1">Owner All</option>');
			for(var i in retVal.data){ $("#relationLinkOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); }
			$("#relationLinkOwnersList").val(id)
		}
	);
}
//-----------------------------------------------------------------------
function openSelectList(lbl, id){
	let krID = $("#"+id).val().trim();
	$("#selectList #tmpKRid").val(krID);
	$("#selectList #lorKRid").val(id);
	$("#selectList .hedarLBL").text(lbl);
	if(krID==0){ $("#selectList .hedarTXT").text(". . ."); }
	else{ $("#selectList .hedarTXT").text($("#"+id+"_txt").val()); }

	$('#selectListTBL').bootstrapTable('resetSearch', "");
	$('#selectListTBL').bootstrapTable('selectPage', 1);
	$("#selectList").modal({show:true, keyboard: false, backdrop:'static'});

	$("#ownerslctLst").val(-1);
	$('#shwglbl').bootstrapToggle('on');
}
//-----------------------------------------------------------------------
$("#ownerslctLst").ready(function(){
	$("#ownerslctLst option").remove();
	$("#ownerslctLst").append('<option value="-1">Owner All</option>');
	$.get(
		'<?=env('API_URL');?>/api/dashboard/relation/relationowners/<?=$orgID;?>',
		function(retVal){
			for(var i in retVal.data){
				$("#ownerslctLst").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); 
			}
		}
	);
	$("#ownerslctLst").val(-1)
});
//-----------------------------------------------------------------------
</script>
