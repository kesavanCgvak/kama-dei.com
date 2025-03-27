<?php
$trID = 0;
$trTX = '';
$kbID = 0;
$kbTX = '';
switch(strtolower($requestTableName)){
	case 't':
		$trID = $requestParentId;
		$term = \App\Term::find($requestParentId);
		if($term==null){ $trID=0; }
		else{ $trTX = $term->termName; }
		break;
	case 'r':
		$kbID = $requestParentId;
		$relation = \App\Relation::where('relationId', $requestParentId)
			->leftJoin('term as lTerm', 'relation.leftTermId', '=', 'lTerm.termId')
			->leftJoin('term as rTerm', 'relation.rightTermId', '=', 'rTerm.termId')
			->leftJoin('relation_type' , 'relation.relationTypeId', '=', 'relation_type.relationTypeId')
			->select(
				\DB::raw('CONCAT(lTerm.termName," ",relation_type.relationTypeName," ",rTerm.termName) as knowledgeRecordName')
			)
			->first();
		if($relation==null){ $kbID=0; }
		else{ $kbTX = $relation->knowledgeRecordName; }
		break;
	default:
}
?>
<style>
	#link_kr_to_term table th,
	#link_kr_to_term table td{ font-size:13px; }
	#link_kr_to_term table td:last-child{ font-size:11px; text-align:center; }
	#link_kr_to_term table td:nth-child(4){ text-align:center; width:100px; }
	#link_kr_to_term table td:nth-child(6){ text-align:center; width:100px; font-size:12px; }
	
	#selectListKRTBL th{ font-size:13px; }
	#selectListKRTBL td{ font-size:12px; }
	#selectListKRTBL tr:hover>td{ color:blue !important;cursor:pointer; }

	#selectListTRTBL th{ font-size:13px; }
	#selectListTRTBL td{ font-size:12px; }
	#selectListTRTBL tr:hover>td{ color:blue !important;cursor:pointer; }
</style>
<select class="form-control" id="linkkrtotermOwnersList" style="display:none">
	<option value="-1" selected="selected">Owners . . .</option>
</select>
<div id="link_kr_to_term"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
	var apiURL    = "<?=env('API_URL');?>";
	var orgID     = "<?=$orgID;?>";
	var userID    = "<?=session()->get('userID');?>";
	var userLevel = "<?=session()->get('levelID');?>";
	var BASE_ORGANIZATION = '<?=env('BASE_ORGANIZATION');?>';
	var defaultTrID = <?=$trID;?>;
	var defaultTrTX = '<?=$trTX;?>';
	var defaultKbID = <?=$kbID;?>;
	var defaultKbTX = '<?=$kbTX;?>';
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
	//-----------------------------------------------------------------------
	function openselectListKR(lbl, id){
		let krID = $("#"+id).val().trim();
		$("#selectListKR #tmpKRid").val(krID);
		$("#selectListKR #lorKRid").val(id);
		$("#selectListKR .hedarLBL").text(lbl);
		if(krID==0){ $("#selectListKR .hedarTXT").text(". . ."); }
		else{ $("#selectListKR .hedarTXT").text($("#"+id+"_txt").val()); }

		$('#selectListKRTBL').bootstrapTable('resetSearch', "");
		$('#selectListKRTBL').bootstrapTable('selectPage', 1);
		$("#selectListKR").modal({show:true, keyboard: false, backdrop:'static'});

		$("#ownerslctLstKR").val(-1);
		$('#shwglblKR').bootstrapToggle('on');
		$("#selectListKRTBL").bootstrapTable("showLoading");
	}
	//-----------------------------------------------------------------------
	function openselectListTR(lbl, id){
		let krID = $("#"+id).val().trim();
		$("#selectListTR #tmpTRid").val(krID);
		$("#selectListTR #lorTRid").val(id);
		$("#selectListTR .hedarLBL").text(lbl);
		if(krID==0){ $("#selectListTR .hedarTXT").text(". . ."); }
		else{ $("#selectListTR .hedarTXT").text($("#"+id+"_txt").val()); }

		$('#selectListTRTBL').bootstrapTable('resetSearch', "");
		$('#selectListTRTBL').bootstrapTable('selectPage', 1);
		$("#selectListTR").modal({show:true, keyboard: false, backdrop:'static'});

		$("#ownerslctLstTR").val(-1);
		$('#shwglblTR').bootstrapToggle('on');
		$("#selectListTRTBL").bootstrapTable("showLoading");
	}
	//-----------------------------------------------------------------------
</script>