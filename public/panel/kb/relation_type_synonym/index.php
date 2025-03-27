<style>
#editRelation, #addRelation {
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

#editRelation.show, #addRelation.show {
	display: block;
}

#editRelation > form, #addRelation > form {
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

#editRelation > form input, #addRelation > form input, #editRelation > form select, #addRelation > form select{ width: 250px; }
.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
.row-actions{ text-align: center; }
.row-actions > a:first-child{ padding-right: 10px; }


#relation_type_synonym th{ font-size:13px; }
#relation_type_synonym td{ font-size:13px;vertical-align:middle;text-align:left; }
#relation_type_synonym td:nth-child(6),
#relation_type_synonym td:nth-child(7),
#relation_type_synonym td:nth-child(9)
	{ font-size:12px;text-align:center !important; }
#relation_type_synonym td:nth-child(8)
	{ font-size:12px;text-align:left !important; }

#relation_type_synonym td:nth-child(1){ vertical-align:top; }
#relation_type_synonym td:nth-child(10){ font-size:12px;text-align:center;vertical-align:middle;width:40px; }

.form-group i.fa-search:hover{color:red;cursor:pointer; }

</style>
<select class="form-control" id="relationTypeSynonymOwnersList" style="display:none">
	<option value="-1" selected="selected">Owners . . .</option>
</select>
<div id="relation_type_synonym"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
//------------------------------------------------------------
var apiURL     = "<?=env('API_URL');?>";
var orgID      = "<?=$orgID;?>";
var userID     = "<?=session()->get('userID');?>";
var TERM_TENSE = <?=config('kama_dei.static.TERM_TENSE',1984);?>;
var table, termPerPage=100;
//------------------------------------------------------------
$(function(){
	//--------------------------------------------------------
	$("#searchItemText").on('change', function(){ 
		table.searchTermByName($("#objID").val().trim(), $(this).val().trim()); 
	});
	//--------------------------------------------------------
	$("#searchBox").on('shown.bs.modal', function(){ $("#searchItemText").focus();} );
	//--------------------------------------------------------
	var tmp = $("#relationTypeSynonymOwnersList");
	$(".pull-right.search").prepend(tmp);
	$(".pull-right.search .form-control").css('width', '50%');
	$(".pull-right.search .form-control").css('display', 'inline-block');
	$("#relation_type_synonym table").bootstrapTable().on('refresh.bs.table', function(){ getOwnersList($("#relationTypeSynonymOwnersList").val()); });
	getOwnersList(-1);
	//--------------------------------------------------------
});
//------------------------------------------------------------
function getOwnersList(id){
	$.get(
		'<?=env('API_URL');?>/api/dashboard/relation_type_synonym/relationtypesynonymowners/<?=$orgID;?>',
		function(retVal){
			$("#relationTypeSynonymOwnersList option").remove();
			$("#relationTypeSynonymOwnersList").append('<option value="-1">Owners . . .</option>');
			for(var i in retVal.data){ $("#relationTypeSynonymOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); }
			$("#relationTypeSynonymOwnersList").val(id)
		}
	);
}
//------------------------------------------------------------
function getSelectTerm(obj){
	//--------------------------------------------------------
	var prev = $(obj).find("option:selected").data('prev');
	var next = $(obj).find("option:selected").data('next');
	if( prev!='0' ){ table.getTerms(prev, $(obj), 'p'); return; }
	if( next!='0' ){ table.getTerms(next, $(obj), 'n'); return; }
	//--------------------------------------------------------
	var val = $(obj).find('option:selected').text();
	if( table.isEditMode==false ){
		$("#rtSynonymDisplayName").val(val).change();
		$("#rtSynonymDescription").val(val).change();
	}else{
		$("#rtSynonymDisplayName").change();
		$("#rtSynonymDescription").change();
	}
	//--------------------------------------------------------
}
//------------------------------------------------------------
function selectTermItem(){
	$("#"+$("#objID").val().trim()).val( $('#termsList').val() ).change();
	$("input#rtSynonymTermIdTEMP").val($("select#termsList option:selected").text());
	$("#searchBox").modal('hide');
}
//------------------------------------------------------------
function showSearchBox(objID, label){
	//--------------------------------------------------------
	$("#searchItemText").val('');
	$("#objID").val(objID);
//	$("#searchBox .modal-header").text('enter term');
	$("#searchBox .modal-header").text('Select Term');
	//--------------------------------------------------------
	$("#termsList option").remove();
	$("#"+objID+" option").each(function(){
		var value = $(this).attr('value');
		var text  = $(this).text();
		$("#termsList").append("<option onDblClick='selectTermItem()' value='"+value+"' >"+text+"</option>");
	});
	 $('#searchBox .btn-yes').prop('disabled', true);
	//--------------------------------------------------------
	$("#searchBox").modal({backdrop:'static', keyboard:false});
	//--------------------------------------------------------
	$("#termsList").on('change', function(){
//		$("#"+$("#objID").val().trim()).val( $(this).val() ).change();
		 $('#searchBox .btn-yes').prop('disabled', false);
		$("#searchItemText").val( $(this).find('option:selected').text() );
	});
	//--------------------------------------------------------
}
//------------------------------------------------------------
</script>
<script src="/public/js/app.js"></script>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="cloneItem" aria-hidden="true" id="searchBox">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header"><h3 style="margin:2px 10px"></h3></div>
			<div class="modal-body">
				<div>
					<input class='form-control' id='searchItemText' value="" style="display:inline-block;width:80%;" />
					<button class="btn btn-info" id="findTermsBTN" onclick="$('#searchItemText').change()">go</button>
					<i class="fa fa-refresh fa-spin" id="wait4terms" style="display:none;font-size:22px;margin-left:10px;"></i>
					<input type='hidden' id='objID' value="" />
					
				</div>
				<div style="margin-top:20px;">
					<select size="10" class="form-control" id="termsList"></select>
				</div>
			</div>
			<div class="modal-fotter">
				<div style="width:100%;border-top:1px dotted #ccc;padding:12px 20px 16px;" align="right">
					<button type="button" class="btn btn-success btn-yes" style="width:100%;margin-bottom:5px;" onclick="selectTermItem()" >Select</button>
					<button type="button" class="btn btn-danger btn-no" style="width:100%;" data-dismiss="modal">Cancel</button>
				</div>
			</div>
		</div>
	</div>
</div>
