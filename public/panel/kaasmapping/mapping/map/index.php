<?php
$tmpInput = explode(".", $requestTableName);
$bot_id = $tmpInput[0];

$mapBotRecord = \App\KaasMapBots::myMap($bot_id);
if($mapBotRecord==null){
	?><script type="application/javascript">window.location.href='<?=env('API_URL');?>/panel/kaasmapping/mapping';</script><?php
	return;
}
if($orgID!=0 && $orgID!=$mapBotRecord->ownerId){
	?><script type="application/javascript">window.location.href='<?=env('API_URL');?>/panel/kaasmapping/mapping';</script><?php
	return;
}
$tmpBaseValue = [];
?>
<style>
	div.intent_data{ width:35%; }
	div.intent_kr{ width:60%; }
	div.intent_data,
	div.intent_kr,
	div.slot_data,
	div.value_data
		{ display:inline-block; min-height:356px; margin:0 1%;padding:10px 5px;vertical-align:top; }
	textarea.sampleUtterance{
		max-height:100px;min-height:100px;min-width:100%;max-width:100%;
		height:100px !important;
		width:100% !important;
	}
	
	.btn.btn-info.btn-mapping{ padding:1px 5px;font-size:12px; }

	.th-inner, .table.table-hover td{ font-size:13px; }
	ul.ui-menu.ui-widget.ui-widget-content.ui-autocomplete.ui-front{ max-height:200px; overflow:auto; }
	button.updateBOT{ margin:10px; padding:3px 15px; font-size:12px; }
	
	.titleSpan{ margin-left: 10px; }
	
	.nav.nav-tabs{ line-height: 40px; background: #f1f1f1; }
	.nav-item.nav-link{
		padding: 10px;
	}
	.nav.nav-tabs> li {
		float: left;
    	margin-bottom: -1px;
    	border: 1px solid #ddd;
    	border-radius: 5px 5px 0 0;
    	margin-right: 5px;
		background: #f6f6f6;
		margin-top: 5px;
	}
	.nav.nav-tabs> li>a{ color: #000; }
	.nav.nav-tabs> li.active{ background: #2a94d6; }
	.nav.nav-tabs> li.active>a{ color: #fff; }
	.input-group-addon{ text-transform: capitalize; }
	tbody tr:hover>td{ color:blue !important; cursor:pointer; }
	.krVAL{ height: auto; min-height: 35px; }
	.krVAL_DIV{ min-width:80%;max-width:82%; margin-right:0.5%;display:inline-block; }
	
	.valueTBL{ width: 100%; }
	.valueTBL tr{ height: auto; border-bottom: 1px solid #ddd; }
	.valueTBL th{ 5px 10px !important; }
	.valueTBL tbody tr{ height: auto; }
	.valueTBL tbody tr>td{ height: 100%; vertical-align: top; padding: 5px 3px 10px; }
	.valueTBL tbody tr>td>p,
	.valueTBL tbody tr>td>input
		{ margin-bottom: 5px !important; }
	.valueTBL tbody tr:hover{ default !important; }
	.valueTBL tbody tr>td:hover{ cursor: default !important; }
	#intentTabs{ border: 1px solid #ddd; }
</style>
<div style="width:100%;text-align:left;margin:auto;">
	<button class="btn btn-link" style="float: right" onClick="window.location.href='<?=env('API_URL');?>/panel/kaasmapping/mapping';">Back</button>
	<span style="float:right">
		<small style="margin-top:-12px;display:block" id="publish_status">Status: <?=$mapBotRecord->publish_status;?></small>
		<button class="btn btn-success Unpublished" style="display:none;width:100%;" onClick="published('Published');">Publish</button>
		<button class="btn btn-danger Published" style="display:none;width:100%;" onClick="published('Unpublished');">Unpublish</button>
	</span>
	<div class="input-group m-b-1" style="max-width:70%">
		<span class="input-group-addon">Mapping Name</span>
		<input class="form-control" id="mappingName" value="<?=$mapBotRecord->mappingName;?>" maxlength="200" disabled  />
	</div>
	<div class="input-group m-b-1" style="width:100%;margin-top:15px;">
		<label>Organization:</label><span class="titleSpan"><?=$mapBotRecord->organizationShortName;?></span>&nbsp;&nbsp;&nbsp;
		<label>Persona:</label><span class="titleSpan"><?=$mapBotRecord->personaName;?></span>
	</div>
	<div class="input-group m-b-1" style="width:100%;margin-top:15px;">
		<label>Portal:</label><span class="titleSpan"><?=$mapBotRecord->portalName;?></span>&nbsp;&nbsp;&nbsp;
		<label>Structure:</label><span class="titleSpan"><?=$mapBotRecord->structureName;?></span>&nbsp;&nbsp;&nbsp;
	</div>
	<div class="input-group m-b-1" style="max-width:70%;margin-top:10px;">
		<label>Bot:</label><span class="titleSpan"><?=$mapBotRecord->bot_name;?></span>&nbsp;&nbsp;&nbsp;
		<label>Alias:</label><span class="titleSpan"><?=$mapBotRecord->bot_alias;?></span>
		<?php /*
		<button class="btn btn-danger updateBOT" onClick="openUpdate()">Update Bot</button>
		*/ ?>
	</div>
</div>

<div id="KAAS_MappingNewEdit"></div>
<script type="application/javascript">
	var myKAAS;
	var apiURL             = "<?=env('API_URL');?>";
	var orgID              = "<?=$orgID;?>";
	var botMapID           = '<?=$bot_id;?>';
	var userID             = "<?=$session->get('userID');?>";
	var tabID              = 'intentTabs';
	var BotName            = '<?=$mapBotRecord->bot_name;?>';
	var BotAlias           = '<?=$mapBotRecord->bot_alias;?>';
	var termPerPage        = 100;
	var defaultOrgID       = '<?=$mapBotRecord->ownerId;?>';
	var defaultPersona     = null;//'<?=$mapBotRecord->personaiD;?>';
	var defaultPersonality = null;//'<?=$mapBotRecord->lexPersonalityID;?>';
	var lastPersonalityRelationId = 0;
</script>
<script src="/public/js/app.js"></script>
<script type="application/javascript">
	//---------------------------------------------------------
	function closeModal(id){ $("#"+id).modal('hide'); }
	//---------------------------------------------------------
	var selectKRURL = apiURL+'/api/dashboard/relation/page/'+orgID+'/knowledgeRecordName/asc'+'/10/1/ownerId/-1/showglobal/1';
	$(function(){
		//-----------------------------------------------------
		$("span.content-header-title > span").html("BOT MAPPING <small style='font-size:12px'><?=$mapBotRecord->bot_name;?></small>");
		//-----------------------------------------------------
		$("button.btn.<?=$mapBotRecord->publish_status;?>").show();
		$("#mappingName").prop('disabled', false);
		if('<?=$mapBotRecord->publish_status;?>'=='Published'){ $("#mappingName").prop('disabled', true); }
		//-----------------------------------------------------
		$("#selectListTBL").bootstrapTable({
			columns: [
				{sortable:true, searchable:true , title:'Knowledge Record', field:'knowledgeRecordName', width:'51%', align:'left   !important' },
				{sortable:false, searchable:false, title:'Ownership', field:'ownership'            , width:'17%', align:'left !important', formatter:tmpFormatter },
				{sortable:false, searchable:false, title:'Owner'    , field:'organizationShortName', width:'31%', align:'left !important', formatter:tmpFormatter },

				{sortable:false, searchable:false , title:'relationId' , field:'relationId', width:'0', visible:false },
			],
			url         : selectKRURL,
			showRefresh : true,
			search      : true,
			pagination  : true,
			sidePagination: 'server',
			dataField: 'data',
			sortName: 'knowledgeRecordName',
			sortOrder: 'asc',
			rowStyle: function(row, index){ 
				if(row.relationId==$("#tmpKRid").val()){ 
					return { css:{ color:'red' } };
				}
				return { css:{ color:'#000' } };
			},
			queryParams:function(params){
				//----------------------------------------------------------
				let page = params.offset/params.limit;
				page++;
				if(params.search == '' || params.search == null || typeof(params.search) == 'undefined'){
					selectKRURL = apiURL+
											'/api/dashboard/relation/page'+
											'/'+orgID+
											'/'+params.sort+
											'/'+params.order+
											'/'+params.limit+
											'/'+page+
											'/ownerId/-1/showglobal/1';
				}else{
					selectKRURL = apiURL+
											'/api/dashboard/relation'+
											'/'+orgID+
											'/'+params.sort+
											'/'+params.order+
											'/'+params.limit+
											'/'+page+
											'/allFields'+
											'/'+params.search+
											'/ownerId/-1/showglobal/1';
				}
				this.url = selectKRURL;
				//----------------------------------------------------------
				return params;
				//----------------------------------------------------------
			}
		})	
		.on('click-row.bs.table', function(e, row, a, b){
			$("#selectList .hedarTXT").text(row.knowledgeRecordName);
			$("#selectList #krID").val(row.relationId);
			$('#selectListTBL td').css('color', '#000');
			$(a[0]).find('td').css('color', 'red');
		})
		.on('dbl-click-row.bs.table', function(e, row, a, b){
			$("#selectList .hedarTXT").text(row.knowledgeRecordName);
			$("#selectList #krID").val(row.relationId);
			$('#selectListTBL td').css('color', '#000');
			$(a[0]).find('td').css('color', 'red');

			$("#selectList .btn-select").click();
		});
		$('body').on('click', '#selectList .btn-select', (e) => { 
			let krID = $("#krID").val().trim();
			let dtID = $("#dtID").val().trim();
			if(krID!=0){
				var data = {};
				data.krID    = krID;
				data.dtID    = dtID;
				data.user_id = "<?=$session->get('userID');?>";;
				$.ajax({
					url: apiURL+'/api/dashboard/kaas/mapping/mapped',
					type: 'put',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json'
					},
					data: JSON.stringify(data),
					beforeSend: function(){ },
					success: function(res){
						if(res.result == 0){
							$("#krVAL_"+dtID).text($("#selectList .hedarTXT").text());
							$("#selectList").modal('hide'); 
						}else{ myKAAS.showError(res.msg); }
					},
					error: function(e){ myKAAS.showError(e.status+" : "+e.statusText); }
				});
			}
		});
		<?php if(isset($tmpInput[1])): ?>
		$("#li_<?=$tmpInput[1];?>>a").click();
		<?php endif; ?>
	});
	//---------------------------------------------------------
	function tmpFormatter(value, row, index, field){
		if(field=='reserved' ){ return myKAAS.checkCell    (value, row, field); }
		if(field=='ownership'){ return myKAAS.ownershipCell(value, row, field); }
		if(field=='organizationShortName'){
			if(row.ownerId!=null && row.ownerId!=0){ return row.organization.organizationShortName; }
			else{ return '<?=env('BASE_ORGANIZATION');?>'; }
		}
	}
	//---------------------------------------------------------
	function published(status){
		if(status=='Published'){
			myKAAS.showConfirm(function(ret){
				if(ret){ setPublishStatus(status); }
			},"Publishing this mapping will unpublish other bot mappings for the same portal.", "Publish", "Cancel", "btn-success");
		}else{
			myKAAS.showConfirm(function(ret){
				if(ret){ setPublishStatus(status); }
			},"Unpublish mapping, are you sure?");
		}
	}
	function setPublishStatus(status){
		var data = {};
		data.mappingName    = $("#mappingName").val().trim();
		data.bot_id         = botMapID;
		data.publish_status = status;
		data.ownerId        = defaultOrgID;
		data.bot_name       = BotName;
		data.bot_alias      = BotAlias;

		if(data.mappingName==""){
			myKAAS.showError("invalid mapping name");
			$("#mappingName").focus();
			return;
		}
		$.ajax({
			url: apiURL+'/api/dashboard/kaas/mapping/'+status,
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ $("#editItem #saveItem").prop('disabled', true); },
			success: function(res){
				if(res.result == 0){
					$("button.btn.Published").hide();
					$("button.btn.Unpublished").hide();
					$("button.btn."+status).show();
					$("#mappingName").prop('disabled', false);
					if(status=='Published'){ $("#mappingName").prop('disabled', true); }
					$("#publish_status").html("Status: "+status);
				}else{ myKAAS.showError(res.msg); }
			},
			error: function(e){ myKAAS.showError(e.status+" : "+e.statusText); }
		});
	}
	//---------------------------------------------------------
	function callAddNew(parent_id, other=""){
		var data = {};
		data.parent_id     = parent_id;
		data.mappingBot_id = $("#newMappingBot_id"+parent_id).val().trim();
		data.type_id       = $("#newType_id"+parent_id).val().trim();
		data.val1          = $("#newVal1"+parent_id).val().trim();
		data.val2          = $("#newVal2"+parent_id).val().trim();
		data.val3          = ((parent_id==0) ?$("#newVal3"+parent_id).val().trim() :null);
		data.user_id       = "<?=$session->get('userID');?>";;

		if(data.val1==""){
			myKAAS.showError("invalid value");
			$("#newVal1"+parent_id).focus();
			return;
		}
		if(data.val2==""){
			myKAAS.showError("invalid value");
			$("#newVal2"+parent_id).focus();
			return;
		}
		$.ajax({
			url: apiURL+'/api/dashboard/kaas/mapping/newrow',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ },
			success: function(res){
				if(res.result == 0){
					let url = "/panel/kaasmapping/mapping/<?=$bot_id?>";
//					if(parent_id==0){ url+="."+res.id; }
//					else{ url+="."+parent_id+"."+res.id; }
					url+="."+res.parent;
					window.location.href= url;
//					window.location.reload();
//					window.location.href = "/panel/kaasmapping/mapping/<?=$bot_id?>"+((parent_id==0) ?"" :"."+parent_id);
				}else{ myKAAS.showError(res.msg); }
			},
			error: function(e){ myKAAS.showError(e.status+" : "+e.statusText); }
		});
	}
	//---------------------------------------------------------
	function callSelectKR(krID, dtID){
		$("#selectList").modal({show:true, keyboard: false, backdrop:"static"});
		$('#selectListTBL td').css('color', '#000');
		$("#selectList .hedarTXT").text("...");
		if(krID!=0){ $("#selectList .hedarTXT").text($("#krVAL_"+dtID).text()); }
		$("#krID").val(krID);
		$("#dtID").val(dtID);
	}
	//---------------------------------------------------------
	$(function(){
		if('<?=\Session::get('menu_status');?>'==0){
			$(".menu.col-md-2, .content-header.row").hide();
			$(".content.col-md-10.col-xs-12").css("width", '100%');
			$(".content.col-md-10.col-xs-12").css("margin-top", '0');
		}
	});
</script>
<?php if($mapBotRecord->structure_id==1){ include_once("layer_1.1.php"); } ?>
