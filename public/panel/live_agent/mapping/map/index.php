<?php
$tmpInput = explode(".", $requestTableName);
$bot_id = $tmpInput[0];

$mapBotRecord = \App\LiveAgentMapBots::myMap($bot_id);
if($mapBotRecord==null){
	?><script type="application/javascript">window.location.href='<?=env('API_URL');?>/panel/live_agent/mapping';</script><?php
	return;
}
if($orgID!=0 && $orgID!=$mapBotRecord->ownerId){
	?><script type="application/javascript">window.location.href='<?=env('API_URL');?>/panel/live_agent/mapping';</script><?php
	return;
}

$orgLaveAgentIntegration = false ;
$orgData = \App\Organization::find($mapBotRecord->ownerId);
if($orgData!=null && $orgData->hasLiveAgent==1){ $orgLaveAgentIntegration = true ;}

$portalLaveAgentIntegration = false ;
$portalData = \App\Portal::find($mapBotRecord->portal_id);
if($portalData!=null && $portalData->hasLiveAgent==1){ $portalLaveAgentIntegration = true ;}

$tmpBaseValue = [];
?>
<style>
	div.intent_data{ width:100%; }
	div.intent_kr{ width:100%; }
	div.intent_data,
	div.intent_kr,
	div.slot_data,
	div.value_data
		{ display:block; /*min-height:356px;*/ margin:0 10px 10px;padding:10px 5px;vertical-align:top; }
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
	.input-group.m-b-1{ width:calc(100% - 175px); max-width: 700px; margin-bottom: 5px; }
	.input-group.m-b-1.mb2{ width:100%; max-width: 600px; margin-bottom: 5px; }
	.input-group-addon{ text-transform: capitalize; display: table-cell; width: 130px; text-align: left; }
	tbody tr:hover>td{ color:blue !important; cursor:pointer; }
	.krVAL{ height: auto; min-height: 35px; }
	.krVAL_DIV{ width:calc( 100% - 150px ); margin-right:0.5%;display:inline-block; }
	
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
	
	#knowledgeRecordTable th,
	#knowledgeRecordTable td{ border:1px solid #ddd; vertical-align:top; cursor:default; padding:5px;}
	textarea.krMSG,
	p.krMSG{
		width: 100%; max-width:500px; min-width:100%;
		height:10px; max-height:100px; min-height:100px;
		margin: 0 0 10px;
	}
/*
	.btn.btn-default.btn-no{ color:#fff; background-color:#cf5c60; border-color:#c9484d; }
	.btn.btn-default.btn-no:hover{ color:#fff; background-color:#9A0206; border-color:#f9484d; }
*/
</style>
<div style="width:100%;text-align:left;margin:auto;">
	<?php
	$liveAgentPortalId = \Session::get('liveAgentPortalId');
	$href  = env('API_URL')."/panel/live_agent/mapping";
	$href2 = env('API_URL')."/panel/live_agent/mapping";
	if($liveAgentPortalId!=null){ $href .="/p/{$liveAgentPortalId}"; }
//	\Session::put('liveAgentPortalId', null);
	?>
	<button class="btn btn-link" style="float: right" onClick="window.location.href='<?=$href;?>';">Back</button>
	<span style="float:right">
		<small style="margin-top:-12px;display:block" id="publish_status">Status: <?=$mapBotRecord->publish_status;?></small>
		<?php if($orgLaveAgentIntegration && $portalLaveAgentIntegration): ?>
		<button class="btn btn-success Unpublished" style="display:none;width:100%;" onClick="published('Published');">Publish</button>
		<button class="btn btn-danger Published" style="display:none;width:100%;" onClick="published('Unpublished');">Unpublish</button>
		<?php else: ?>
		<button class="btn btn-default" style="width:100%;" disabled >Publish</button>
		<?php endif; ?>
	</span>
	<div class="panel panel-primary">
		<div class="panel-heading" style="font-weight:normal;">Live Agent Integration</div>
		<div class="panel-body">
			<div class="input-group m-b-1">
				<span class="input-group-addon">Mapping Name</span>
				<input class="form-control" id="mappingName" value="<?=$mapBotRecord->mappingName;?>" maxlength="200" disabled  />
			</div>
			<div class="input-group m-b-1">
				<span class="input-group-addon">Organization</span>
				<input class="form-control" value="<?=$mapBotRecord->organizationShortName;?>" disabled 
					   style="width:calc(100% - 231px)"  />
				<p class="form-control" style="width:230px;">
					Live Agent Integration is <?=(($orgLaveAgentIntegration) ?'Active' :'OFF');?>
				</p>
			</div>
			<div class="input-group m-b-1">
				<span class="input-group-addon">Persona</span>
				<input class="form-control" value="<?=$mapBotRecord->personaName;?>" disabled  />
			</div>
			<div class="input-group m-b-1">
				<span class="input-group-addon">Portal</span>
				<input class="form-control" value="<?=$mapBotRecord->portalName;?>" disabled style="width:calc(100% - 231px)"  />
				<p class="form-control" style="width:230px;">
					Live Agent Integration is <?=(($portalLaveAgentIntegration) ?'Active' :'OFF');?>
				</p>
			</div>
		</div>
	</div>
</div>

<div id="LIVEAGENT_MappingNewEdit"></div>
<script type="application/javascript">
	var myClass;
	var apiURL             = "<?=env('API_URL');?>";
	var orgID              = "<?=$mapBotRecord->ownerId;?>";
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
	var selectKRURL = apiURL+'/api/dashboard/relation/page/'+orgID+'/knowledgeRecordName/asc'+'/10/1/ownerId/-1/showglobal/0';
	$(function(){
		//-----------------------------------------------------
		$("span.content-header-title > span").html("Live Agent Chat Mapping");
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
			let mkID = $("#mkID").val().trim();
			if(krID!=0){
				sendToController_mapping(mkID, dtID, krID);
			}
		});
		<?php if(isset($tmpInput[1])): ?>
		$("#li_<?=$tmpInput[1];?>>a").click();
		<?php else: ?>
		<?php endif; ?>
		$(".nav.nav-tabs>li:first-child>a").click();
	});
	function sendToController_mapping(mapping_kr_id, mappingBot_id, kr_id){
		var data = {};
		data.mapping_kr_id = mapping_kr_id;
		data.mappingBot_id = mappingBot_id;
		data.kr_id         = kr_id;
		data.user_id       = "<?=$session->get('userID');?>";
		if(data.mapping_kr_id!=0 && data.mappingBot_id==0 && data.kr_id==0){
			myClass.showConfirm(function(result){
				if(result){ sendToController_mapping_(data); }
			}, "Are you sure?", "Yes", "No", "btn-danger", "btn-info");
			return;
		}else{ sendToController_mapping_(data); }
	}
	function sendToController_mapping_(data){
		$.ajax({
			url: apiURL+'/api/dashboard/live_agent/mapping/mapped',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ },
			success: function(res){
				if(res.result == 0){
//					if(data.mappingBot_id==0 || data.mapping_kr_id==0){ window.location.reload(); }
					if(data.mappingBot_id==0 || data.mapping_kr_id==0){
						if(data.mappingBot_id==0 && data.mapping_kr_id!=0){ window.location.href="<?="{$href2}/{$bot_id}";?>#-1"; }
						else{
							if(res.added==1)
								window.location.href="<?="{$href2}/{$bot_id}";?>#0";
							else
								window.location.href="<?="{$href2}/{$bot_id}";?>#"+res.id;
						}
						window.location.reload();
					}else{
						window.location.href="<?="{$href2}/{$bot_id}";?>#"+res.id;
					}
					$("#krVAL_"+data.mapping_kr_id).text($("#selectList .hedarTXT").text());
					$("#selectList").modal('hide'); 
				}else{ myClass.showError(res.msg); }
			},
			error: function(e){ myClass.showError(e.status+" : "+e.statusText); }
		});
	}
	//---------------------------------------------------------
	function tmpFormatter(value, row, index, field){
		if(field=='reserved' ){ return myClass.checkCell    (value, row, field); }
		if(field=='ownership'){ return myClass.ownershipCell(value, row, field); }
		if(field=='organizationShortName'){
			if(row.ownerId!=null && row.ownerId!=0){ return row.organization.organizationShortName; }
			else{ return '<?=env('BASE_ORGANIZATION');?>'; }
		}
	}
	//---------------------------------------------------------
	function published(status){
		if(status=='Published'){
			myClass.showConfirm(function(ret){
				if(ret){ setPublishStatus(status); }
			},"Publishing this mapping will unpublish other bot mappings for the same portal.", "Publish", "Cancel", "btn-success");
		}else{
			myClass.showConfirm(function(ret){
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
			myClass.showError("invalid mapping name");
			$("#mappingName").focus();
			return;
		}
		$.ajax({
			url: apiURL+'/api/dashboard/live_agent/mapping/'+status,
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
				}else{ myClass.showError(res.msg); }
			},
			error: function(e){ myClass.showError(e.status+" : "+e.statusText); }
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
			myClass.showError("invalid value");
			$("#newVal1"+parent_id).focus();
			return;
		}
		if(data.val2==""){
			myClass.showError("invalid value");
			$("#newVal2"+parent_id).focus();
			return;
		}
		$.ajax({
			url: apiURL+'/api/dashboard/live_agent/mapping/newrow',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ },
			success: function(res){
				if(res.result == 0){
					let url = "/panel/live_agent/mapping/<?=$bot_id?>";
//					if(parent_id==0){ url+="."+res.id; }
//					else{ url+="."+parent_id+"."+res.id; }
					url+="."+res.parent;
					window.location.href= url;
//					window.location.reload();
//					window.location.href = "/panel/live_agent/mapping/<?=$bot_id?>"+((parent_id==0) ?"" :"."+parent_id);
				}else{ myClass.showError(res.msg); }
			},
			error: function(e){ myClass.showError(e.status+" : "+e.statusText); }
		});
	}
	//---------------------------------------------------------
	function callSelectKR(kr_id, mappingBot_id, mapping_kr_id){
		$("#selectList").modal({show:true, keyboard: false, backdrop:"static"});
		$('#selectListTBL td').css('color', '#000');
		$("#selectList .hedarTXT").text("...");
		if(kr_id!=0){ $("#selectList .hedarTXT").text($("#krVAL_"+mapping_kr_id).text()); }
		$("#krID").val(kr_id);
		$("#dtID").val(mappingBot_id);
		$("#mkID").val(mapping_kr_id);
	}
	//---------------------------------------------------------
	$('#knowledgeRecordTable').ready(function(){
		let hash = window.location.hash;
		if(hash!=""){
			hash = hash.replace("#", "");
			if(hash>0){
				let x = $("tr#row_"+hash).offset();
//				$("body").animate({ scrollTop: x.top }, 100);
			}
			if(hash<0){
				let x = $("#knowledgeRecordTable").offset();
				$("body").animate({ scrollTop: x.top-150 }, 500);
			}
			if(hash==0){
//				let x = $("#knowledgeRecordTable>tbody>tr:last-child").offset();
				let x = $("#newRowBTN").offset();
				$("body").animate({ scrollTop: x.top }, 900);
			}
		}
//	knowledgeRecordTable
	});
	$(function(){
		if('<?=\Session::get('menu_status');?>'==0){
			$(".menu.col-md-2, .content-header.row").hide();
			$(".content.col-md-10.col-xs-12").css("width", '100%');
			$(".content.col-md-10.col-xs-12").css("margin-top", '0');
		}
	});
</script>
<?php if($mapBotRecord->structure_id==1){ include_once("layer_1.1.php"); } ?>
