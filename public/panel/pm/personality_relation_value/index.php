<style>
	div#personalityValuesList{ text-align:left; }

	div#personalityValuesList>span{ display:inline-block; }
	div#personalityValuesList>span.left{}
	div#personalityValuesList>span.right{ width:70%;max-width:700px;;display:inline-block;float:right;text-align:right; }
	div#personalityValuesList>span>label{ cursor:pointer; }
	div#personalityValuesList>span.all{ width:100%; }
	div#personalityValuesList select#ownerList{ width:300px;display:inline-block;margin-left:10px; }
	
	div#personalityValuesList{ text-align:left; }
	#personalityList{ width:calc( 100% - 90px); display:inline-block; }
	
	ul.personalityFilter{ text-align:left;list-style:none;margin:0;padding:0; }
	ul.personalityFilter label{ cursor:pointer; }

	#valuesFor{ font-size:130%;margin-top:15px;margin-bottom:5px; }
	#valuesFor>span{
		font-weight: bold;
		max-width: calc( 100% - 100px);
    	overflow: auto;
    	white-space: nowrap;
    	display: inline-block;
    	vertical-align: top;
		height: inherit;
	}

	.table.table-hover th:nth-child(1),
	#knowledgeRecord table td:nth-child(1)
		{ width:34px !important;text-align:center !important; }
	td.font90{ font-size:90% !important; }
	td.font80{ font-size:80% !important; }
	td.font70{ font-size:70% !important; }
	.columns.columns-right.btn-group.pull-right .btn{ height:35px; }

	.ui-slider-handle {
		width: 1.8em;
		height: 1.6em;
		top: 50%;
		margin-top: -.8em;
		text-align: center;
		line-height: 1.6em;
	}
	.sliderTopValue{ text-align:center;margin-bottom:8px; }
	.sliderTopValue>#min{ float:left;margin-left:-5px; }
	.sliderTopValue>#max{ float:right;margin-right:-9px; }
	
	.sliderTopValueOnTable{ text-align:center;margin-bottom:1px;font-size:12px; }
	.sliderTopValueOnTable>#min{ float:left;margin-left:-5px; }
	.sliderTopValueOnTable>#max{ float:right;margin-right:-9px; }
	
	.slider-onTable > .ui-slider-handle{ margin-top:-1px;font-size:10px;width:1.9em;height:1.9em;background:#50051f;color:#ffffff; }
	.slider-onModal > .ui-slider-handle{ margin-top:-1px;font-size:10px;width:1.9em;height:1.9em;background:#50051f;color:#ffffff; }
	
	.sliderHolder{ margin-left:5px;margin-right:5px; }
	
	#tmpPersonalityRelationValue .btn-info.btn-top{ margin-bottom:-70px; }
	#tmpPersonalityRelationValue .btn-info{ margin-bottom:-40px; }
	#tmpPersonalityRelationValue div>.btn-info{ margin-top:25px; }
	#addKnowledgeRecord div.btns,
	#copyKRsDLG div.btns
		{ width:100%;text-align:right;padding:0;margin:0; }
	#addKnowledgeRecordValue div.btns{ width:100%;text-align:center;padding:0;margin:0; }
	#copyKRsDLG div.btns
		{ margin-top:10px; }
	#addKnowledgeRecord div.btns>.btn,
	#copyKRsDLG div.btns>.btn{ width:40%; }
	#addKnowledgeRecordValue div.btns>.btn{ width:30%; }
	#addKnowledgeRecord div.btns>.btn-cancel,
	#copyKRsDLG div.btns>.btn-cancel,
	#addKnowledgeRecordValue div.btns>.btn-cancel
		{ float:left; }
	#addKnowledgeRecord div.btns>.btn-cancel:hover,
	#copyKRsDLG div.btns>.btn-cancel:hover,
	#addKnowledgeRecordValue div.btns>.btn-cancel:hover
		{  }

	#addKnowledgeRecordBTN, #copyKRsBTN{ display:none; }
	label.w130{ width:130px; }
	label.w140{ width:140px; }
	
	#valueSelect .myClassValueSelect,
	#valueSelect .myClassValueSelect:hover,
	#knowledgeRecordSelect .myClassKnowledgeRecord,
	#knowledgeRecordSelect .myClassKnowledgeRecord:hover
		{color:red !important; }

	#knowledgeRecordSelectcopyKRs .myClassKnowledgeRecord,
	#knowledgeRecordSelectcopyKRs .myClassKnowledgeRecord:hover
		{color:#44dd88; }

	i.act:hover{ color:red;cursor:pointer; }
	
	
	span.knowledgeRecordsIDcopyKRs li>i.fa-trash:hover{ cursor:pointer;color:red; }
	span.knowledgeRecordsIDcopyKRs li>i.fa{ float: right; margin-top: 3px; }
/*	span.knowledgeRecordsIDcopyKRs li>i.fa{ margin-left:-3px;margin-right:5px; } */
	span.knowledgeRecordsIDcopyKRs li{ width: 100%; line-height: 20px; }
	span.knowledgeRecordsIDcopyKRs li:hover{ background: #ffe; }

	#knowledgeRecordSelectcopyKRs td, #knowledgeRecordSelect td{ font-size:12px; }
	#showProblemsSolutions label:hover{ color:red; cursor:pointer; }
	#knowledgeRecordSelectcopyKRs tr>td:last-child{ margin: 0; padding: 0; text-align: center; vertical-align: middle; }
	
	#valuesFor>span{ min-height: 26px; padding-bottom:3px;  }
	
	#valuesFor>span li{
		margin-bottom: 10px;
		list-style: none;
		font-weight: 100;		
	}
	#valuesFor>span li:hover{
		cursor: pointer;
		color: red;
	}
	#valuesFor>span li.active{
		cursor: pointer;
		font-weight: bold;
		list-style: circle;
	}
</style>
<div id="personalityRelationValue"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<div id="tmpPersonalityRelationValue" style="display:none">
	<div style="width:100%; text-align:right;display:none; margin-bottom:-20px;" id="showProblemsSolutions">
<?php /*
		<div class="btn-group" role="group" aria-label="Basic example">
			<button type="button" class="btn btn-primary">Show All</button>
			<button type="button" class="btn btn-primary">Problems</button>
			<button type="button" class="btn btn-primary">Solutions</button>
		</div>
*/ ?>
		<label for="showProblemsSolutions0">Show All</label>
		<input type="radio" name="showProblemsSolutions" id="showProblemsSolutions0" value="0" checked/>

		<label for="showProblemsSolutions1" style="margin-left:10px">Solution</label>
		<input type="radio" name="showProblemsSolutions" id="showProblemsSolutions1" value="1" />

		<label for="showProblemsSolutions2" style="margin-left:10px;">Problem</label>
		<input type="radio" name="showProblemsSolutions" id="showProblemsSolutions2" value="2" />
	</div>
	<button id="addKnowledgeRecordBTN" class="btn btn-info btn-top" onclick="openAddKnowledgeRecord()" style="width:200px;">Add Knowledge Records</button>
	<?php if(/*$orgID==0 && */session()->get('levelID')==1): ?>
	<button id="copyKRsBTN" class="btn btn-info btn-top" onclick="openCopyKRs(0)" style="width:200px;">Copy KRs</button>
	<?php endif; ?>
	<table 
		id="knowledgeRecord"
		data-show-refresh=true
		data-toggle="table" 
		data-smart-display=true
		data-search=true
		data-detail-view=true
		data-detail-formatter="showDetail"
		data-pagination=true
		data-sort-name="knowledgeRecords" 
		data-sort-order="asc"
		data-method='post'
		data-url='<?=env('API_URL');?>/api/dashboard/personality_relation_value/all/<?=$orgID;?>/-1/0'
		data-data-field='data'
	    data-unique-id = "personalityRelationId"
	>
	<thead>
		<tr>
			<th data-checkbox=true></th>
			<th data-field="knowledgeRecords" data-sortable=true  data-searchable=true  data-width='84%' data-align='left'  >Knowledge Record</th>
			<!--
			<th data-field="ownership"   data-sortable=true  data-searchable=false data-width='13%' data-class='font90' data-formatter='formatter' data-align='left'  >
				Ownership
			</th>
			<th data-field="ownerId"     data-sortable=true  data-searchable=false data-width='16%' data-class='font90' data-formatter='formatter' data-align='left'  >
				Owner
			</th>
			-->
			<th data-field="dateCreated" data-sortable=false data-searchable=false data-width='10%' data-class='font70' data-align='center'>Created</th>
<?php /*
			<th data-field="tmpOrganizationName" data-sortable=false data-searchable=false data-width='30%' data-class='font70' data-align='left' data-formatter='formatter'><small style="font-size:10px ">PERSONALITY KNOWLEDGE RATING OWNER</small></th>
*/ ?>
			<th data-field="netRating" data-sortable=false data-searchable=false data-width='10%' data-class='font70' data-align='left' data-formatter='formatter'><small style="font-size:10px ">Net Rating</small></th>

			<th data-field="Personalized" data-sortable=true data-searchable=false data-width='10%' data-class='font70' data-align='center' data-formatter='formatter'><small style="font-size:10px ">Personalized</small></th>

			
			<th data-field="actionH"     data-sortable=false data-searchable=false data-width='3%'  data-formatter='formatter' data-align='center'>&nbsp;</th>

			<th data-field="isParent"    data-sortable=false data-searchable=false data-visible=false>isParent</th>
			<th data-field="lTermN"      data-sortable=false data-searchable=false data-visible=false>Left Term</th>
			<th data-field="rTypeN"      data-sortable=false data-searchable=false data-visible=false>Relation Type</th>
			<th data-field="rTermN"      data-sortable=false data-searchable=false data-visible=false>Right Term</th>
			<th data-field="personalityRelationId" data-sortable=false data-searchable=false data-visible=false>personalityRelationId</th>
			<th data-field="relationId" data-sortable=false data-searchable=false data-visible=false>relationId</th>
			<th data-field="personalityId" data-sortable=false data-searchable=false data-visible=false>personalityId</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
	</table>
</div>

<div class="modal" tabindex="-1" role="dialog" id="addKnowledgeRecord">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
<?php
/*
modal-lg
			<div class="modal-header">
			</div>
*/
?>
			<div class="modal-body">
				<!--
				<div>
					<label class="w130">Owner</label> : <span class="ownerID"></span>
					<input type="hidden" id="ownerID" value="" />
					<input type="hidden" id="ownershipID" value="" />
				</div>
				-->
				<div>
					<label class="w130" id="spanAddKnowledgeRecord"></label> : <span class="personalityID"></span>
					<input type="hidden" id="personalityID" value="" />
				</div>
				<div>
					<label class="w130">Knowledge Record</label> : <span class="knowledgeRecordID"></span>
					<input type="hidden" id="knowledgeRecordID" value="" />
				</div>
				<?php if($orgID==0): ?>
				<div class="input-group m-b-1" style="margin:5px 0 15px;">
					<span class="input-group-addon">Owner</span>
					<select class="form-control" id="krDestOrgID" ></select>
				</div>
				<?php endif; ?>
				<div class="btns" style="text-align:center">
					<button type="button" class="btn btn-primary btn-add"   style="width:170px;float:none"  onclick="addKnowledgeRecord(0)">Add KR</button>
					<button type="button" class="btn btn-success btn-add"   style="width:170px;float:right" onclick="addKnowledgeRecord(1)">Add and Rate</button>
					<button type="button" class="btn btn-danger btn-cancel" style="width:170px;float:left" data-dismiss="modal">Cancel</button>
				</div>
			</div>
			<div class="modal-footer" align="left">
				<div style="float:left;margin-top: 15px;">
					<label for="showGlobalKRs" style="color: #0000008a">Show global</label>
					<input checked type="checkbox" id="showGlobalKRs" data-onstyle="info" data-toggle="toggle" data-size="small" 
						   data-on="Yes" data-off="No" style="float: left"/>
				</div>
				<table 
					id="knowledgeRecordSelect"
					data-show-refresh=false
					data-toggle="table" 
					data-search=true
					data-detail-view=false
					data-detail-formatter=""
					data-sort-name="knowledgeRecords" 
					data-sort-order="asc"
					data-method='get'
					data-url=''
					data-data-field='data'
					data-smart-display=true
					data-pagination=true
					data-single-select=true
				>
				<thead>
					<tr>
						<th data-cell-style='knowledgeRecordCellStyle' data-field="knowledgeRecords" data-sortable=true  data-searchable=true  data-width='50%' data-align='left'  >Knowledge Record</th>
						<th data-cell-style='knowledgeRecordCellStyle' data-field="ownership"   data-sortable=true  data-searchable=false data-width='13%' data-class='' data-formatter='formatter' data-align='left'  >
							Ownership
						</th>
						<th data-cell-style='knowledgeRecordCellStyle' data-field="ownerAddKR"   data-sortable=false  data-searchable=false data-class='' data-formatter='formatter' data-align='left'  >
							Owner
						</th>
<?php
/*
						<th data-field="ownerId"     data-sortable=true  data-searchable=false data-width='16%' data-class='font90' data-formatter='formatter' data-align='left'  >
							Owner
						</th>
						<th data-field="dateCreated" data-sortable=false data-searchable=false data-width='10%' data-class='font70' data-align='center'>Created</th>
*/
?>

						<th data-field="relationId" data-sortable=false data-searchable=false data-visible=false>relationId</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var apiURL  = "<?=env('API_URL');?>";
	var orgID   = "<?=$orgID;?>";
	var userID  = "<?=session()->get('userID');?>";
	var levelID = "<?=session()->get('levelID');?>";
	var myClass;
</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
	//-------------------------------------------------------------------------
	function knowledgeRecordCellStyle(value, row, index, field){
		if(row.relationId==$("input#knowledgeRecordID").val()){
			return {
				classes: 'myClassKnowledgeRecord',
				css:{ 'cursor':'pointer' }
			};
		}else{
			return {
				classes: '',
				css:{ 'cursor':'pointer' }
			};
		}
	}
	//-------------------------------------------------------------------------
	function valueCellStyle(value, row, index, field){
		if(row.termId==$("input#valueKRV").val()){
			return {
				classes: ((field!='termName') ?'myClassValueSelect font70' :'myClassValueSelect'),
				css:{ 'cursor':'pointer' }
			};
		}else{
			return {
				classes: ((field!='termName') ?'font70' :''),
				css:{ 'cursor':'pointer' }
			};
		}
	}
	//-------------------------------------------------------------------------
	var expandRowID = 0;
	var lastRowExpand = -1;
	$(function(){
		//---------------------------------------------------------------------
		$("#tmpPersonalityRelationValue").show();
		$("div.fixed-table-toolbar").hide();
		//---------------------------------------------------------------------
		$('#knowledgeRecord').on('load-success.bs.table', function(data){
			if(expandRowID){ $("#knowledgeRecord").bootstrapTable('expandRow',0); }
			return data;
		});
		$('#knowledgeRecord').on('sort.bs.table', function(index, row, detail){ 
			$('.menu-actions').removeClass('show'); $(".menu-actions.submenu").remove(); 
		});
		$('#knowledgeRecord').on('page-change.bs.table', function(obj, number, size){ 
			$('.menu-actions').removeClass('show'); $(".menu-actions.submenu").remove(); 
		});
		$('#knowledgeRecord').on('collapse-row.bs.table', function(index, row, detail){
			$('.menu-actions').removeClass('show'); $(".menu-actions.submenu").remove(); 
		});
		$('#knowledgeRecord').on('expand-row.bs.table', function(index, row, detail){
			lastRowExpand = row;
			$('.menu-actions').removeClass('show');
			$(".menu-actions.submenu").remove();
			//-----------------------------------------------------------------
			var len = $('#knowledgeRecord').bootstrapTable('getData').length;
			for(var i=0; i<len; i++){ if(i!=row){ $('#knowledgeRecord').bootstrapTable('collapseRow', i); } }
			//-----------------------------------------------------------------
			$("#knowledgeRecord-"+detail.personalityRelationId).bootstrapTable({
				showRefresh : false,
				smartDisplay: true,
				search      : true,
				pagination  : true,
				sortName    : 'value',
				sortOrder   : 'asc',
				method      : 'post',
				showRefresh : true,
				queryParams:function(params){
					params.order='desc';
					params.isparent = detail.isParent;
					return params;
				},

//				url         : "<?=env('API_URL');?>/api/dashboard/personality_relation_value/allValue/"+orgID+"/"+detail.personalityId+"/"+detail.relationId,
				url         : "<?=env('API_URL');?>/api/dashboard/personality_relation_value/allValue/"+orgID+"/"+detail.personalityRelationId,
				
				dataField   : 'data',
				columns     :[
					{checkbox: true },
					{sortable: true , searchable: true , title: 'Value'       , field: 'value'      , width:'35%', align:'left !important'   },
					{sortable: false, searchable: false, title: 'Scalar value', field: 'scalarValue', width:'25%', align:'left'  ,formatter:formatter },
					{sortable: true , searchable: true , title: 'Ownership'   , field: 'ownership'  , width:'15%', align:'left'  ,formatter:formatter, class:'font70' },
					{sortable: true , searchable: true , title: 'Owner'       , field: 'ownerId'    , width:'15%', align:'left'  ,formatter:formatter, class:'font70' },
					{sortable: false, searchable: false, title: 'Created'     , field: 'dateCreated', width:'10%', align:'center', class:'font70' },
					{sortable: false, searchable: false, title: ''            , field: 'actionD'    , width:'3%' , align:'center', formatter:formatter },
					{sortable: false, searchable: false, visible:false        , field: 'personalityRelationValueId' },
					{sortable: false, searchable: false, visible:false        , field: 'personalityRelationId'      },
				]
			});
			//-----------------------------------------------------------------
			$("#knowledgeRecord-"+detail.personalityRelationId).on('load-success.bs.table',function(data){
				$(".slider-onTable").each(function(){
					$(this).slider({
						max  : 10,
						value:  $(this).attr('data'),
						min  : -10,
						slide: function( event, ui ){ $(this).find(".ui-slider-handle").text( ui.value );  }, // $("#scalarValue").val(ui.value).change();
						stop: function( event, ui ){
							callEitScalarValue(this, ui.value, detail.isParent, detail.personalityRelationId, row); 
						}
					});
					$(this).find(".ui-slider-handle").text( $(this).attr('data') );
				});
			});
			$("#knowledgeRecord-"+detail.personalityRelationId).on('sort.bs.table',function(name, order){
				$(this).bootstrapTable('refresh');
			});
			//-----------------------------------------------------------------
			if(expandRowID){
				addKnowledgeRecordValues(expandRowID);
				expandRowID = 0;
			}
		});
		//---------------------------------------------------------------------
		$('#knowledgeRecordSelect').on('click-row.bs.table', function(row, element, field){
			$("input#knowledgeRecordID").val(element.relationId);
			$("span.knowledgeRecordID").text(element.knowledgeRecords);
//			$( '#knowledgeRecordSelect' ).bootstrapTable('resetWidth'); 
			$('.myClassKnowledgeRecord').removeClass('myClassKnowledgeRecord');
			$(field).find('td').addClass('myClassKnowledgeRecord');
		});
		$('#knowledgeRecordSelectcopyKRs').on('all.bs.table', function(row, element, field){
			knowledgeRecordSelectcopyKRsMarked();
		});
		$('#knowledgeRecordSelectcopyKRs').on('click-row.bs.table', function(row, element, field){
			let addFlag = true;
			let indxLI  = 0;
			let delID   = 0;
			$("span.knowledgeRecordsIDcopyKRs ol li").each(function(){
				let thisId = $(this).data('id');
				if(thisId==element.personalityRelationId){ 
					addFlag=false;
					$("span.knowledgeRecordsIDcopyKRs").animate({scrollTop:0} ,'fast');
					$("span.knowledgeRecordsIDcopyKRs").animate({scrollTop:indxLI} ,'slow');
					knowledgeRecordSelectcopyKRsRemoveByID(thisId);
				}else{ indxLI+=$(this).height(); }
			});
			if(addFlag){
				$("span.knowledgeRecordsIDcopyKRs ol").append(
					"<li data-id='"+element.personalityRelationId+"' data-krID='"+element.relationId+"'>"+
//						"<i onclick='$(this).parent().remove()' class='fa fa-trash'></i>"+
						"<i onclick='knowledgeRecordSelectcopyKRsRemoveItem(this)' class='fa fa-trash'></i>"+
						" "+
						element.knowledgeRecords+
					"</li>"
				);
				$("span.knowledgeRecordsIDcopyKRs").animate({scrollTop:$('span.knowledgeRecordsIDcopyKRs ol').height()} ,'slow');
				knowledgeRecordSelectcopyKRsMarked();
			}
				
//			$('.myClassKnowledgeRecord').removeClass('myClassKnowledgeRecord');
//			$(field).find('td').addClass('myClassKnowledgeRecord');
		});
		//---------------------------------------------------------------------
		$('#valueSelect').on('click-row.bs.table', function(row, element, field){
			$("input#valueKRV").val(element.termId);
			$("span.valueKRV").text(element.termName);

			$('.myClassValueSelect').removeClass('myClassValueSelect');
			$(field).find('td').addClass('myClassValueSelect');
		});
		//---------------------------------------------------------------------
	});
	//-------------------------------------------------------------------------
	function callEitScalarValue( obj, scalarValue, isParent, tagID, rowIndex ){
		var data = {};
		data.ownerId     = orgID;
		data.scalarValue = scalarValue;
		data.userID      = userID;
		data.isparent    = isParent;
		$.ajax({
			method  :'put',
			url     : apiURL+'/api/dashboard/personality_relation_value/scalarvalue/'+$(obj).attr('data-id'),
			data    : data,
			dataType: 'json',
			success: 
				function( response ){
					if(response.result==0){ 
						if(response.parentId!=0){
							let tmpRowData = $("#knowledgeRecord").bootstrapTable('getRowByUniqueId', tagID);
							tmpRowData.Personalized          = 1;
							tmpRowData.isParent              = 0;
							tmpRowData.personalityRelationId = response.personalityRelationId;
							$("#knowledgeRecord").bootstrapTable('updateByUniqueId', {id:tagID, row:tmpRowData, replace:false});
							$("#knowledgeRecord").bootstrapTable('expandRow', lastRowExpand);
						}else{
							$("#knowledgeRecord-"+tagID).bootstrapTable(

								'refresh',
								{url:"<?=env('API_URL');?>/api/dashboard/personality_relation_value/allValue/"+orgID+"/"+response.personalityRelationId}
							);
						}

//						$( $(obj).parent().parent().parent().parent().attr('id') ).bootstrapTable('refresh'); 
/*
//expandRowID = tmpRowData.personalityRelationId;
$("#knowledgeRecord").bootstrapTable('resetSearch', tmpRowData.knowledgeRecords); 
$("#knowledgeRecord").bootstrapTable('refresh'); 
//$("#knowledgeRecord").bootstrapTable('expandRowByUniqueId', tagID)
$("#knowledgeRecord").bootstrapTable('expandRow', 0)
let tmpRowData = $("#knowledgeRecord").bootstrapTable('getRowByUniqueId', tagID);
console.log(tmpRowData); 
*/
					}else{
						$(obj).slider('value', $(obj).attr('data'));
						$(obj).find(".ui-slider-handle").text( $(obj).attr('data') );
						myClass.showError("Error:["+response.msg+"]");
					}
				},
			error:
				function(xhr, textStatus, errorThrown ){ 
					$(obj).slider('value', $(obj).attr('data'));
					$(obj).find(".ui-slider-handle").text( $(obj).attr('data') );
					myClass.showError("Error:["+xhr.status+"] "+errorThrown); 
				}
		});
	}
	//-------------------------------------------------------------------------
	function changeShowValues(obj, prsnltyID, oldParentID){
		$(obj).parent().find('li').removeClass('active');
		$(obj).addClass('active');
		knowledgeRecordRefresh(oldParentID)
	}
	//-------------------------------------------------------------------------
	function knowledgeRecordRefresh(oldParentID=0){
		var ownrID = $("#ownerList").val();
		var prsID  = $("#personalityList").val();

		let showProblemsSolutions = 0;
		if(	$("#showProblemsSolutions1").prop('checked') ){ showProblemsSolutions = 1; }
		if(	$("#showProblemsSolutions2").prop('checked') ){ showProblemsSolutions = 2; }
		$("#knowledgeRecord").bootstrapTable(
			'refresh',
			{url:'<?=env('API_URL');?>/api/dashboard/personality_relation_value/all/<?=$orgID;?>/'+ownrID+'/'+prsID+'/'+showProblemsSolutions+"/"+oldParentID}
		);
	}
	function formatter(value, row, index, field){
		if( field=='trash'){
			return ''; 
		}
		if( field=='netRating'){
			if(value>=0){ return 'solution'; }
			else{ return 'problem'; }
		}
<?php /*
		if( field=='tmpOrganizationName'){
			if(row.organization!=null){ return row.organization.organizationShortName; }
			return '<?=env('BASE_ORGANIZATION');?>';;
		}
*/ ?>
		if(field=='Personalized'){
			if(row.Personalized==0){ return ''; }
			return '<i class="fa fa-check"></i>';
		}
		if(field=='actionH'){
			if($("#personalityList").val()==0){ return; }
			//----------------------------------------------------------
			let myIcons = myClass.actionIcons;
			let icons = myClass.actionIcons;
			let tmpICN = [];
			$("[data-menu-toggle='#actions-menu-"+index+"']").remove();
			//----------------------------------------------------------
			if($("#personalityList option:selected").data('parentid')==0){//Personal
				let icon0 = $('<a></a>')
								.attr({ href:'#', 'data-desc':'Delete', class:'delete-item',style:"color:red", 'data-onlyowner':1 });
				tmpICN.push(icon0);
				icon0 = $('<a></a>')
								.attr({ href:'#','data-desc':'Reset',class:'reset-item',style:'color:lightcoral','data-onlyowner':1 });
				tmpICN.push(icon0);
			}else{//Personality
				if(row.isParent==0){
					let icon0 = $('<a></a>')
								.attr({ href:'#','data-desc':'Reset',class:'reset-item',style:'color:lightcoral','data-onlyowner':1 });
					tmpICN.push(icon0);
				}
			}
			//----------------------------------------------------------
			let icon1 = $('<a></a>').attr({ href:'#', 'data-desc':'Copy', class:'copy-item', style:'color:green', 'data-onlyowner':1 });
			tmpICN.push(icon1);
			//----------------------------------------------------------
			myClass.actionIcons = tmpICN;
			//----------------------------------------------------------
			let action = '<div class="row-actions"></div>';
			//----------------------------------------------------------
			let others =
				'<ul class="menu-actions mainmenu" data-menu data-menu-toggle="#actions-menu-'+index+'" style="font-size:12px;"></ul>';
			icons = myClass.actionIcons;
			for (let i in icons){
				icons[i].attr('onclick', function(){});
				if( icons[i].attr('class')=='delete-item' ){
					icons[i].attr('onclick', 'eraseKnowlegeRecord('+row.personalityRelationId+","+index+',0)');
				}
				if( icons[i].attr('class')=='reset-item' ){
					icons[i].attr('onclick', 'eraseKnowlegeRecord('+row.personalityRelationId+","+index+',1)');
				}
				if( icons[i].attr('class')=='copy-item' ){ icons[i].attr('onclick', 'openCopyKRs('+row.personalityRelationId+')'); }
				let $icon = icons[i].clone();
				$icon = $icon.append('&nbsp;'+$icon.data('desc')+'&nbsp;');
				others = $(others).append('<li>'+$icon[0].outerHTML+'</li>');
			}
			//----------------------------------------------------------
			let toggle = ''+
				'<a href="#" class="toggle" id="actions-menu-'+index+'" style="color:dimgray">'+
					'<small class="glyphicon glyphicon-chevron-down" style="font-size:12px;"></small>'+
				'</a>';
			let othersIcon = '<span>'+toggle+'</span>';
			action = $(action).append(othersIcon);
			$("body").append(others);
			$(document).ready(function(e){ $("[data-menu]").menu(); });
			//----------------------------------------------------------
			myClass.actionIcons = myIcons;
			//----------------------------------------------------------
			if(orgID==0){ return $(action)[0].outerHTML; }
			else{
				if(orgID==row.ownerId){ return $(action)[0].outerHTML; }
				else{ return ''; }
			}
			//----------------------------------------------------------
		}

		if(field=='actionD'){
/*
console.log(row)//bhrfrd
$("#knowledgeRecord")
	.bootstrapTable(
		"updateByUniqueId",
		{
			id :row.uniqueid,
			row:{
				netRating:row.netRatingText
			}
		}
	);
	$("#knowledgeRecord").bootstrapTable('expandRow', index);
*/
			$("#knowledgeRecord tbody>tr[data-uniqueid="+row.uniqueid+"] td:nth-child(5)").text(row.netRatingText);

			if(row.isParent!=0){ return ''; }
			//----------------------------------------------------------
			let icons = myClass.actionIcons;
			let tmpICN = [];
			//----------------------------------------------------------
			for (let i in icons){
				if( icons[i].attr('class')=='delete-item' ){ icons[i].attr('data-desc', 'Delete') }
				if( icons[i].attr('class')=='delete-item' ){ tmpICN.push(icons[i]); }
			}
			myClass.actionIcons = tmpICN;
			//----------------------------------------------------------
			let action = '<div class="row-actions"></div>';
			//----------------------------------------------------------
//			$("[data-menu-toggle='#actions-submenu-"+index+"']").remove();
//			$(".menu-actions.submenu").remove();
			
			let others = '<ul class="menu-actions submenu" data-menu data-menu-toggle="#actions-submenu-'+index+'" style="font-size:12px;"></ul>';
			icons = myClass.actionIcons;
			for (let i in icons){
				icons[i].attr('onclick', 'eraseKnowlegeRecordValue('+row.personalityRelationValueId+","+row.personalityRelationId+","+index+')');
				let $icon = icons[i].clone();
				$icon = $icon.append('&nbsp;&nbsp;'+$icon.data('desc'));
				others = $(others).append('<li>'+$icon[0].outerHTML+'</li>');
			}
			let toggle = ''+
				'<a href="#" class="toggle" id="actions-submenu-'+index+'" style="color:dimgray">'+
					'<small class="glyphicon glyphicon-chevron-down" style="font-size:12px;"></small>'+
				'</a>';
			let othersIcon = '<span>'+toggle+'</span>';
			action = $(action).append(othersIcon);
			$("body").append(others);
			$(document).ready(function(e){ $("[data-menu]").menu(); });
			//----------------------------------------------------------
			if(orgID==0){ return $(action)[0].outerHTML; }
			else{
				if(orgID==row.ownerId){ return $(action)[0].outerHTML; }
				else{ return ''; }
			}
			//----------------------------------------------------------
		}

		if(field=='ownership'){
//			return row.ownerShipText;
			switch(value){
				case 0: return 'Public';
				case 1: return 'Protected';
				case 2: return 'Private';
			}
		}
	
		if(field=='ownerAddKR'){
			return row.organizationShortName;
		}
		if(field=='ownerIdcopyKRs'){//} || field=='ownerAddKR'){
			if(row.ownerId==null || row.ownerId==0) return '<?=env('BASE_ORGANIZATION');?>';
			if(isNaN(row.ownerId)) return row.ownerId;
			return row.organization.organizationShortName;
		}
		if(field=='ownerId'){
			if( row.ownerId==null || row.ownerId==0 ){ return '<?=env('BASE_ORGANIZATION');?>'; }
			else{ return row.organization.organizationShortName; }
		}

		if(field=='scalarValue'){
			var id = row.personalityRelationValueId;
			return ""+
				"<div class='sliderTopValueOnTable'><span id='min'>-10</span><span id='center'>0</span><span id='max'>10</span></div>"+
				'<div class="slider-onTable" id="slider-'+id+'" data="'+value+'" data-id="'+id+'"></div>';
		}
		return value;
	}
	function showDetail(index, row, element) {
		return '<div>'+
					'<button class="btn btn-info" onclick="addKnowledgeRecordValues('+row.personalityRelationId+')">Add Personality Knowledge Rating</button>'+
					'<table id="knowledgeRecord-'+row.personalityRelationId+'"></table>'+
				'</div>';
	}	
	//-------------------------------------------------------------------------
	function setPersonality(){
		if( $("#personalityFilter0").prop('checked') ){ personalityCheck(0); }
		if( $("#personalityFilter1").prop('checked') ){ personalityCheck(1); }
		if( $("#personalityFilter2").prop('checked') ){ personalityCheck(2); }
	}
	//-------------------------------------------------------------------------
	function selectPersonality(){
		$("#tmpPersonalityRelationValue table").bootstrapTable('selectPage',1);	
		$("#addKnowledgeRecordBTN, #copyKRsBTN, #showProblemsSolutions").hide();
		$("#valuesFor>span").html('');
		
		if( $("#personalityList").val()!=0){
			$("div#tableToolbar").show(); 
//			$("#valuesFor>span"    ).text($("#personalityList>option:selected").text());
			if($("#personalityList>option:selected").data('parentid')==0){
				knowledgeRecordRefresh();
				$("div.fixed-table-toolbar").hide();
				$("#valuesFor>span").text($("#personalityList>option:selected").data('full'));
			}else{
				let tmpPersonaName    = $("#personalityList>option:selected").data('personaname');
				let tmpParentName     = $("#personalityList>option:selected").data('parentname' );
				let tmpPortalName     = $("#personalityList>option:selected").data('portalname' );
				let oldparentspersona = $("#personalityList>option:selected").data('oldparentspersona' );

				if(tmpPortalName!=null && tmpPortalName!="null"){ tmpPortalName = " Portal:"+tmpPortalName; }
				else{ tmpPortalName=""; }
				if(oldparentspersona==''){
					$("#valuesFor>span").text(tmpPersonaName+" | Persona:"+tmpParentName+tmpPortalName);
				}else{
//BHR
					let currentParentid = $("#personalityList>option:selected").data('parentid');
					let isMain          = $("#personalityList>option:selected").data('is_main_item');
					
					let prsnltyID = $("#personalityList").val();
					
					$("#valuesFor>span").html(
						"<ul>"+
							"<li onClick='changeShowValues(this,"+prsnltyID+",0)' class='"+((isMain==1) ?'active' :'')+"'>"+tmpPersonaName+" | Persona:"+tmpParentName+tmpPortalName+"</li>"+
						"</ul>"
					);
					for(let jI in oldparentspersona){
						let oldPortal   = ((oldparentspersona[jI].portal==null) ?"" :" Portal: "+oldparentspersona[jI].portal);
						let oldParentID = oldparentspersona[jI].persona_id;
						$("#valuesFor>span>ul").append(
							"<li onClick='changeShowValues(this,"+prsnltyID+","+oldParentID+")' class='"+((currentParentid==oldParentID) ?'active' :'')+"'>"+tmpPersonaName+" | Persona:"+oldparentspersona[jI].name+oldPortal+"</li>"
						);
					}
				}
				knowledgeRecordRefresh($("#personalityList>option:selected").data('parentid'));
				$("div.fixed-table-toolbar").hide();
			}
			$("#valuesFor>span").css("overflow", "hidden");
			let aW = $("#valuesFor>span").width();
			let bW= $("#valuesFor").width();
			if(aW+100>=bW-10){ $("#valuesFor>span").css("overflow", "auto"); }
			$("#addKnowledgeRecordBTN, #copyKRsBTN, #showProblemsSolutions").show();
			$("#showProblemsSolutions0").prop("checked", true);
			$("div.fixed-table-toolbar").show();
		}
		else{ 
			knowledgeRecordRefresh();
			$("div.fixed-table-toolbar").hide();
		}
	}
	//-------------------------------------------------------------------------
	function personalityCheck(flag){
		var txtTXT = "Personality";
		if( flag==0 ){ txtTXT = "Personality or Persona"; }
		if( flag==1 ){ txtTXT = "Persona"; }
		$("select#personalityList option").remove();
		$("select#personalityList").append('<option value="0" selected="selected">Select '+txtTXT+'</option>');
		$("select#personalityList").val(0);
		$("select#personalityList").focus();
		selectPersonality();
		$("#personalityList").parent().find('label').text(txtTXT);
		var tmpOrgID = $("#ownerList").val();

		var url = apiURL+'/api/dashboard/personality/allPersonality/'+orgID+'/'+tmpOrgID+'/personalityName/asc';
		let personalityListSrch = $("#personalityListSrch").val().trim();
		if( flag==1 ){
			if(getPersonalityListAjax!=null){ getPersonalityListAjax.abort(); }
			url = apiURL+'/api/dashboard/personality/zeroPersonality/'+orgID+'/'+tmpOrgID+'/personalityName/asc';
			$("#personalityListSrch").attr("placeholder", "Search Persona");
		}
		if( flag==2 ){
			if(getPersonalityListAjax!=null){ getPersonalityListAjax.abort(); }
			url = apiURL+'/api/dashboard/personality/nonzeroPersonality/'+orgID+'/'+tmpOrgID+'/personalityName/asc';
			$("#personalityListSrch").attr("placeholder", "Search Personality");
		}
		url+= "/"+personalityListSrch;
		getPersonalityList(url, "select#personalityList");
		$("#valuesFor>span").html('');
	}
	//-------------------------------------------------------------------------
	var getPersonalityListAjax = null;
	function getPersonalityList(url, element) {
		getPersonalityListAjax = $.ajax({
			url     : url,
			data    : {},
			dataType: 'json',
			complete: function(){ getPersonalityListIsBusy=null; },
			beforeSend: function(xhr, opt){
				$("span#personalityListTotal").html("<i class='fa fa-refresh fa-spin'></i>");
				$("span#personalityListMsg").html("");
			},
			error: function(xhr){
				$("span#personalityListTotal").html("<b style='color:red'>Error code: ["+xhr.status+"]</b>");
				$("span#personalityListMsg").html(xhr.statusText);
			},
			success: 
				function( response ){
					if(response.result==0){
						for( var i in response.data ){
							if(response.data[i].parentPersonaId!=0){
//--------------------------------------------------------------------------------------------------------------------------
//BHR
//--------------------------------------------------------------------------------------------------------------------------
								let portalname = "";
								if(response.data[i].portalname!=null ){ portalname = " | "+response.data[i].portalname; }
								let fullText = response.data[i].personalityName +
												//' | ' +
												' | '+
												response.data[i].parent_persona.personalityName +
												portalname;
								let showText = fullText;
								if(showText.length>50){ showText = showText.substr(0, 47)+"..."; }
								
								let oldParentsPersona = "";
								if(response.data[i].old_parent_persona!=null ){
									oldParentsPersona = [];
									let tmpIDs = [response.data[i].parentPersonaId];
									for( let j in response.data[i].old_parent_persona){
										let old_parent_persona = response.data[i].old_parent_persona[j];
										if(tmpIDs.indexOf(old_parent_persona.persona.personalityId)==-1){
											oldParentsPersona.push({
												persona_id    : old_parent_persona.persona.personalityId,
												name          : old_parent_persona.persona.personalityName,
												portal        : old_parent_persona.persona.portalname
											});
											tmpIDs.push(old_parent_persona.persona.personalityId);
										}
									}
									oldParentsPersona = JSON.stringify(oldParentsPersona);//.toString();
								}
								$(element).append(
												"<option "+
														"data-parentID='"+response.data[i].parentPersonaId+"' "+
														"data-owner='"+response.data[i].ownerId+"' "+
														"data-full='"+fullText+"' "+
														"data-parentname='"+response.data[i].parent_persona.personalityName+"' "+
														"data-personaname='"+response.data[i].personalityName+"' "+
														"data-portalname='"+response.data[i].portalname+"' "+
														"data-oldparentspersona='"+oldParentsPersona+"' "+
														"data-is_main_item=1 "+
														"value='"+response.data[i].personalityId+"'>"+
													showText+
												"</option>"
								);
								if(response.data[i].old_parent_persona!=null ){
									//oldParentsPersona = [];
									let tmpIDs = [response.data[i].parentPersonaId];
									for( let j in response.data[i].old_parent_persona){
										let old_parent_persona = response.data[i].old_parent_persona[j];
										if(tmpIDs.indexOf(old_parent_persona.persona.personalityId)==-1){
											tmpIDs.push(old_parent_persona.persona.personalityId);
											let old_portalname = "";
											if(old_parent_persona.persona.portalname!=null )
												{ old_portalname = ' | '+old_parent_persona.persona.portalname; }
											let old_fullText = response.data[i].personalityName +
												' | ' +
												old_parent_persona.persona.personalityName +
												old_portalname;
												let old_showText = old_fullText;
											if(old_showText.length>50){ old_showText = old_showText.substr(0, 47)+"..."; }
											$(element).append(
												"<option "+
														"data-parentID='"+old_parent_persona.persona.personalityId+"' "+
														"data-owner='"+response.data[i].ownerId+"' "+
														"data-full='"+old_fullText+"' "+
														"data-parentname='"+response.data[i].parent_persona.personalityName+"' "+
														"data-personaname='"+response.data[i].personalityName+"' "+
														"data-portalname='"+response.data[i].portalname+"' "+
														"data-oldparentspersona='"+oldParentsPersona+"' "+
														"data-is_main_item=0 "+
														"value='"+response.data[i].personalityId+"'>"+
													old_showText+
												"</option>"
											);
											response.total++;
										}
									}
								}
//--------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------
/*
								var email = "";
								if(response.data[i].get_consumer_user!=null ){
									if(response.data[i].get_consumer_user.email==null){ email=''; }
									else{ email = " - " + response.data[i].get_consumer_user.email; }
								}
								let fullText = response.data[i].personalityName +
												' | ' +
												response.data[i].parent_persona.personalityName +
												email;
								let showText = fullText;
								if(showText.length>50){ showText = showText.substr(0, 47)+"..."; }
								
								let oldParentsPersona = "";
								if(response.data[i].old_parent_persona!=null ){
									oldParentsPersona = [];
									for( let j in response.data[i].old_parent_persona){
										let old_parent_persona = response.data[i].old_parent_persona[j];
										oldParentsPersona.push({
											persona_id    : old_parent_persona.persona.personalityId,
											name          : old_parent_persona.persona.personalityName,
											portal        : old_parent_persona.persona.portalname
										});
									}
									oldParentsPersona = JSON.stringify(oldParentsPersona);//.toString();
								}
								$(element).append(
												"<option "+
														"data-parentID='"+response.data[i].parentPersonaId+"' "+
														"data-owner='"+response.data[i].ownerId+"' "+
														"data-full='"+fullText+"' "+
														"data-parentname='"+response.data[i].parent_persona.personalityName+"' "+
														"data-personaname='"+response.data[i].personalityName+"' "+
														"data-portalname='"+response.data[i].portalname+"' "+
														"data-oldparentspersona='"+oldParentsPersona+"' "+
														"value='"+response.data[i].personalityId+"'>"+
													showText+
/ *
													response.data[i].personalityName + ' - '+
													response.data[i].parent_persona.personalityName +
													email +
* /
												"</option>"
								);
*/
//--------------------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------------------
							}
							else{
								let fullText = response.data[i].personalityName;
								let showText = fullText;
								if(showText.length>50){ showText = showText.substr(0, 47)+"..."; }
								$(element).append(
									"<option "+
											"data-parentID='"+response.data[i].parentPersonaId+"' "+
											"data-owner='"+response.data[i].ownerId+"' "+
											"data-full='"+fullText+"' "+
											"value='"+response.data[i].personalityId+"'>"+
											showText+
//										response.data[i].personalityName+
									"</option>"
								);
							}
//							$("select#personalityList").append('<option value="'+response.data[i].personalityId+'">'+response.data[i].personalityName+'</option>');
						}
						$(element).val(0);
						$("span#personalityListTotal").html("Records: "+response.total);
						if(response.total>response.limit){
							$("span#personalityListMsg").html("Displaying the first "+response.limit+" records.");
						}
					}
				}
		});
	}
	//-------------------------------------------------------------------------
	function scalarValue(value, row, index, field){ return index; }
	//-------------------------------------------------------------------------
	function openAddKnowledgeRecord(){
		//---------------------------------------------------------------------
		let input_personalityID = $("#personalityList option:selected").val();
		if(input_personalityID<1 || input_personalityID=='' || input_personalityID==null){
			myClass.showError("Adding Knowledge Record failed, Try Again.");
			return;
		}
		//---------------------------------------------------------------------
		$('#addKnowledgeRecord').modal({show:true, keyboard: false, backdrop:'static'});
		//---------------------------------------------------------------------
		$("input#personalityID").val(input_personalityID);
		$("span.personalityID" ).text($("#personalityList option:selected").text());
		//---------------------------------------------------------------------
		$("input#knowledgeRecordID").val(0);
		$("span.knowledgeRecordID").text('');
		if( $("#personalityList option:selected").data('parentid')=="0" ){ $("#spanAddKnowledgeRecord").text('Persona'); }
		else{ $("#spanAddKnowledgeRecord").text('Personality'); }
		//---------------------------------------------------------------------
		$("#showGlobalKRs").bootstrapToggle('on');
		//---------------------------------------------------------------------
		var ownID = $("#personalityList option:selected").data('owner');
		<?php if($orgID==0): ?>
		$("#krDestOrgID option").remove();
		for(var i in myClass.organizations){ $("#krDestOrgID").append(myClass.organizations[i]); }
		<?php endif; ?>
		if(ownID==null){ ownID=0; }
		$("#krDestOrgID").val(ownID).change();
//		$("#krDestOrgID").prop('disabled', true);
	}
	//-------------------------------------------------------------------------
	function addKnowledgeRecord(flag){
		var data = {};
		data.ownerID           = orgID;
		<?php if($orgID==0): ?>
		data.ownerID = $("#krDestOrgID").val();
		<?php endif; ?>
		//data.ownerID = $("#krDestOrgID").val();

		data.personalityID     = $("input#personalityID"    ).val().trim();
		data.knowledgeRecordID = $("input#knowledgeRecordID").val().trim();
		data.userID = userID;
		if(data.personalityID<1 || data.personalityID=='' || data.personalityID==null){
			myClass.showError("Adding Knowledge Record failed, Try Again.");
			$('#addKnowledgeRecord').modal('hide');
			return;
		}
		if(data.ownerID<0 || data.ownerID=='' || data.ownerID==null){
			myClass.showError("Adding Knowledge Record failed, Try Again.");
			$('#addKnowledgeRecord').modal('hide');
			return;
		}
		if(data.knowledgeRecordID==0){ myClass.showError("Please select Knowlege Record."); return; }
		$.ajax({
			url     : apiURL+'/api/dashboard/personality_relation_value/knowledgeRecord',
			data    : data,
			method  : 'put',
			dataType: 'json',
			success: 
				function( response ){
					if(response.result==0){
						$("#showProblemsSolutions0").prop("checked", true).change();
						$("#knowledgeRecord").bootstrapTable('refresh'); 
						$("#knowledgeRecord").bootstrapTable('resetSearch',decodeURI($("span.knowledgeRecordID").text()));
						if(flag==1){ expandRowID = response.id; }
					}else{ 
						myClass.showError("Error: "+response.msg); 
						$("#knowledgeRecord").bootstrapTable('resetSearch','');
					}
					$('#addKnowledgeRecord').modal('hide');
				},
			error:
				function(xhr, textStatus, errorThrown ){ myClass.showError("Error:["+xhr.status+"] "+errorThrown); }
		});
	}
	//-------------------------------------------------------------------------
	function resetKnowlegeRecord(knowledgeRecordID, thisRowIndex){
		myClass.showConfirm(
			function(confirm){
//alert(confirm);
			},
			"This Knowledge Record and its ratings for all Personalities derived from this Persona will be reset to the current value.",
			"RESET",
			"CANCEL"
		);
	}
	function eraseKnowlegeRecord(knowledgeRecordID, thisRowIndex, flgER=0){
		$("#knowledgeRecord").bootstrapTable('check', thisRowIndex);
		let selectedRows = $("#knowledgeRecord").bootstrapTable('getAllSelections');
		let msg = "";
		if($("#personalityList option:selected").data('parentid')=='0'){
			if(flgER==0){
				msg = "This Knowledge Record and its ratings will be deleted from this Persona and all Personalities derived from this Persona.";
			}else{
				msg = "This Knowledge Record and its ratings for all Personalities derived from this Persona will be reset to the current value.";
			}
		}else{
			if(flgER==0){ return; }
			msg = "All ratings for the selected Knowledge Records for Personalities produced from this parent will be reset to the Persona values.";
			flgER=2;
		}
		if(selectedRows.length>1){ msg +="<br/><b>"+selectedRows.length+"</b> rows selected"; }

		if($("#personalityList option:selected").data('parentid')!='0'){ eraseKnowlegeRecord_(msg, selectedRows, 0, flgER); }
		else{
			$.ajax({
				url     : apiURL+'/api/dashboard/persona/getpersonalitiesofpersona/'+orgID+'/'+$("#personalityList").val().trim(),
				data    : {},
				method  : 'get',
				dataType: 'json',
				success: 
					function( response ){
						if(response.result==0){
							if(response.total==0){ eraseKnowlegeRecord_(msg, selectedRows, 0, flgER); }
//							msg +="<br/><hr/>Delete all ratings for the Personalities derived from this Persona too?";
							msg +="<br/><hr/>The following Personalities will be affected:";
							msg +="<ul style='max-height:100px; overflow:auto;'>";
							for(let i in response.data){
								let name = response.data[i].name;
								name = ((name.length>30) ?name.substr(0, 27)+"..." :name);
								msg +="<li>"+name+"</li>"; 
							}
							msg +="</ul>";
							eraseKnowlegeRecord_(msg, selectedRows, 1, flgER);
						}else{ myClass.showError("Error: "+response.msg); }
					},
				error:
					function(xhr, textStatus, errorThrown ){ myClass.showError("Error:["+xhr.status+"] "+errorThrown); }
			});
		}
	}
	function eraseKnowlegeRecord_(msg, selectedRows, personalities, flgER ){
		let erasePersonalities = 1;

		let yes = "YES";
		let no  = "CANCEL";
		let has = "deleted";
		if(flgER==0){ yes="DELETE"; }
		if(flgER==1){ yes="RESET";  has="reseted"; }
		if(flgER==2){ yes="OK";     }
		
		myClass.showConfirm(function(confirm){
			if(confirm){
				let lastII = selectedRows.length-1;
				for(let ii in selectedRows){
					let tmpId = selectedRows[ii].personalityRelationId;
					$.ajax({
						data    : {},
						url     : apiURL
									+'/api/dashboard/personality_relation_value/knowledgeRecord/'
									+orgID+'/'
									+tmpId+"/"
//									+personalities+"/"+erasePersonalities,
									+personalities+"/"+flgER,
						method  : 'delete',
						dataType: 'json',
						success: 
							function( response ){
								if(response.result==0)
									{ myClass.showSuccess("<b>"+selectedRows[ii].knowledgeRecords+"</b> has "+has); }
								else
									{ myClass.showError("Error: "+response.msg); }
								if( lastII==ii){ $("#knowledgeRecord").bootstrapTable('refresh'); }
							},
						error:
							function(xhr, textStatus, errorThrown ){ myClass.showError("Error:["+xhr.status+"] "+errorThrown); }
					});
				}
			}else{ $("#knowledgeRecord").bootstrapTable('uncheckAll'); }

		},msg, yes, no);
	}
	//-------------------------------------------------------------------------
	var lastAddKnowledgeRecordValuesId = 0;
	var lastIsParent = 0;
	function addKnowledgeRecordValues(id){
		lastAddKnowledgeRecordValuesId = id;
		//---------------------------------------------------------------------
		lastIsParent = 0;
		let tmpData = $('#knowledgeRecord').bootstrapTable('getRowByUniqueId', lastAddKnowledgeRecordValuesId);
		lastIsParent = tmpData.isParent;
		//---------------------------------------------------------------------
		$("#addKnowledgeRecordValue .modal-header>span").text(tmpData.knowledgeRecords);
		//---------------------------------------------------------------------
/*
		$("input#ownerKRV").val( '<?=$orgID;?>' );
		$("span.ownerKRV").text( $("#ownerList option[value=<?=$orgID;?>]").text() );
*/
/**/
		if(tmpData.ownerId==0 || tmpData.ownerId==null ){ 
				$("span.ownerKRV").text( '<?=env('BASE_ORGANIZATION');?>' ); 
				$("input#ownerKRV").val( 0 );
		}else{ 
				$("span.ownerKRV").text( tmpData.organization.organizationShortName ); 
				$("input#ownerKRV").val( tmpData.ownerId );
		}
/**/
		//---------------------------------------------------------------------
		$("#slider-0").slider({
			max  : 10,
			value:  0,
			min  : -10,

			slide: function( event, ui ){ $("#slider-0").find(".ui-slider-handle").text( ui.value ); },
			stop: function( event, ui ) { $("#scalerValueKRV").val(ui.value); }
		});
		//---------------------------------------------------------------------
		$("#slider-0").find(".ui-slider-handle").text( 0 );
		$("#valueKRV"                ).val(0);
		$("#scalerValueKRV"          ).val(0);
		$("span.valueKRV"            ).text('');
		$("#personalityRelationIdKRV").val(id);
		
		//---------------------------------------------------------------------
		$("#valueSelect").bootstrapTable('refresh',{url:'<?=env('API_URL');?>/api/dashboard/term/knowledgerecordValues/'+orgID+'/'+id});
		$("#valueSelect").bootstrapTable('selectPage',1);
		$("#valueSelect").bootstrapTable('resetSearch','');
		//---------------------------------------------------------------------
		$('#addKnowledgeRecordValue').modal({show:true, keyboard: false, backdrop:'static'});
		//---------------------------------------------------------------------
	}
	//-------------------------------------------------------------------------
	function addKnowledgeRecordValue(flg){
		var data = {};
		data.ownerId               = $("#ownerKRV"                     ).val().trim();
		data.ownership             = $("#ownershipKRV"                 ).val().trim();
		data.personalityRelationId = $("input#personalityRelationIdKRV").val().trim();
		data.personRelationTermId  = $("input#valueKRV"                ).val().trim();
		data.scalarValue           = $("input#scalerValueKRV"          ).val().trim();
		data.userID   = userID;
		data.isparent = lastIsParent;
//		if(data.knowledgeRecordID   ==0){ myClass.showError("Please select Knowlege Record"); return; }
		if(data.personRelationTermId==0){ myClass.showError("Please select a value from the table."); return; }
		$.ajax({
			url     : apiURL+'/api/dashboard/personality_relation_value/create',
			data    : data,
			method  : 'put',
			dataType: 'json',
			success: 
				function( response ){
					if(response.result==0){ 
						$("#knowledgeRecord-"+data.personalityRelationId).bootstrapTable(
							'refresh',
							{url:"<?=env('API_URL');?>/api/dashboard/personality_relation_value/allValue/"+orgID+"/"+response.id}
						);
						$('#addKnowledgeRecordValue').modal('hide');
						if(flg==1){ addKnowledgeRecordValues(lastAddKnowledgeRecordValuesId); }
					}else{ myClass.showError("Error: "+response.msg); }
				},
			error:
				function(xhr, textStatus, errorThrown ){ myClass.showError("Error:["+xhr.status+"] "+errorThrown); }
		});
	}
	//-------------------------------------------------------------------------
	function eraseKnowlegeRecordValue(personalityRelationValueId, personalityRelationId, tblRowIndx){
		$("#knowledgeRecord-"+personalityRelationId).bootstrapTable('check', tblRowIndx);
		var selectedRows = $("#knowledgeRecord-"+personalityRelationId).bootstrapTable('getAllSelections');
		var msg = "Are you sure ?";
		if(selectedRows.length>1){ msg +="<br/><b>"+selectedRows.length+"</b> rows selected"; }
		myClass.showConfirm(function(confirm){
			if(confirm){
				/*
				$.ajax({
					url     : apiURL+'/api/dashboard/personality_relation_value/knowledgeRecordValue/'+orgID+'/'+personalityRelationValueId,
					data    : {},
					method  : 'delete',
					dataType: 'json',
					success: 
						function( response ){
							if(response.result==0){ myClass.showSuccess(response.msg); }
							else{ myClass.showError("Error: "+response.msg); }
							$("#knowledgeRecord-"+personalityRelationId).bootstrapTable('refresh');
						},
					error:
						function(xhr, textStatus, errorThrown ){ myClass.showError("Error:["+xhr.status+"] "+errorThrown); }
				});
				*/
				let lastII = selectedRows.length-1;
				for(let ii in selectedRows){
					let tmpId = selectedRows[ii].personalityRelationValueId;
					$.ajax({
						url     : apiURL+'/api/dashboard/personality_relation_value/knowledgeRecordValue/'+orgID+'/'+tmpId,
						data    : {},
						method  : 'delete',
						dataType: 'json',
						success: 
							function( response ){
								if(response.result==0){ myClass.showSuccess("<b>"+selectedRows[ii].value+"</b> has deleted"); }
								else{ myClass.showError("Error: "+response.msg); }
								if(lastII==ii){ $("#knowledgeRecord-"+personalityRelationId).bootstrapTable('refresh'); }
							},
						error:
							function(xhr, textStatus, errorThrown ){ myClass.showError("Error:["+xhr.status+"] "+errorThrown); }
					});
				}
			}else{ $("#knowledgeRecord-"+personalityRelationId).bootstrapTable('uncheckAll'); }
		},msg);
	}
	//-------------------------------------------------------------------------
</script>
<div class="modal" tabindex="-1" role="dialog" id="addKnowledgeRecordValue">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" style="background:#00a6b4;">
				<p style="color:black;font-size:14px;">Current Knowledge Record:</p>
				<span style="display:block;color:yellow;font-weight:bold;font-size:16px;text-indent:2em;"></span>
			</div>
			<div class="modal-body">
				<div>
					<label class="w130 ">Owner</label> : <span class="ownerKRV"></span>
					<input type="hidden" id="ownerKRV" value="<?=$orgID;?>" />
				</div>
				<div>
					<label class="w130">Ownership</label> : <span><?=(($orgID==0) ?'Public' :'Private');?></span>
					<input type="hidden" id="ownershipKRV" value="<?=(($orgID==0) ?'0' :'2');?>" />
				</div>
				<div>
					<label class="w130">Value</label> : <span class="valueKRV" style="font-weight:bold"></span>
					<input type="hidden" id="valueKRV" value="" />
				</div>
				<div>
					<label class="w130" style="vertical-align:top;">Scalar value</label> <span style="vertical-align:top;">:</span>
					<div style="width:70%;display:inline-block;margin-left:10px;">
						<div class="slider-onModal" id="slider-0" data="0"></div>
						<div class='sliderTopValueOnTable' style="margin-top:3px;"><span id='min'>-10</span><span id='center'>0</span><span id='max'>10</span></div>
					</div>
					<input type="hidden" id="scalerValueKRV" value="0" />
				</div>
				<input type="hidden" id="personalityRelationIdKRV" value="0" />
				<br />
				<div class="btns">
					<button type="button" class="btn btn-primary btn-add" onclick="addKnowledgeRecordValue(0)">Add and Exit</button>
					<button type="button" class="btn btn-primary btn-add" style="float:right" onclick="addKnowledgeRecordValue(1)">Add and Continue</button>
					<button type="button" class="btn btn-danger btn-cancel" data-dismiss="modal">Cancel</button>
				</div>
			</div>
			<div class="modal-footer" align="left">
				<table 
					id="valueSelect"
					data-show-refresh=false
					data-toggle="table" 
					data-smart-display=true
					data-search=true
					data-detail-view=false
					data-detail-formatter=""
					data-pagination=true
					data-sort-name="termName" 
					data-sort-order="asc"
					data-method='get'
					data-url=''
					data-data-field='data'
					data-single-select=true
				>
				<thead>
					<tr>
						<th data-cell-style='valueCellStyle' data-field="termName" data-sortable=true  data-searchable=true  data-width='70%' data-align='left'  >Value</th>
						<th data-cell-style='valueCellStyle' data-field="ownership"  data-searchable=false data-width='15%' data-class='font70' data-formatter='formatter' data-align='left'  >
							Ownership
						</th>
						<th data-cell-style='valueCellStyle' data-field="ownerId" data-searchable=false data-width='15%' data-class='font70' data-formatter='formatter' data-align='left'  >
							Owner
						</th>
						<th data-field="termId" data-sortable=false data-searchable=false data-visible=false>termID</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="copyKRsDLG">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div class="input-group m-b-1">
					<span class="input-group-addon" id="spanCopyKRsDLG"></span>
					<span class="personalityIDcopyKRs form-control" style="overflow:hidden;"></span>
					<input type="hidden" id="personalityIDcopyKRs" value="" />
				</div>
				<div class="input-group m-b-1" style="margin:5px 0 5px;">
					<span class="input-group-addon">Copy to Org</span>
					<select class="form-control" id="destOrgID" ></select>
				</div>
				
				<div>
					<label class="w140">Knowledge Records</label> :&nbsp;
					<span class="knowledgeRecordsIDcopyKRs" 
							style="display:inline-block;vertical-align:top;overflow:auto;width:400px;height:70px;"></span>
				</div>
				<div>
					<ul style="list-style:none;padding-left:15px;">
						<li>
							<input type="radio" id="addMergeUpdate1" name="addMergeUpdate" title="Add KRs and Ratings from source to targets but do not update Ratings where they exist" checked style="cursor:pointer;"/>
							<label for="addMergeUpdate1" title="Add KRs and Ratings from source to targets but do not update Ratings where they exist" style="cursor:pointer;">Add and Merge</label>
						</li>
						<li>
							<input type="radio" id="addMergeUpdate2" name="addMergeUpdate" title="Add KRs and Ratings from source to targets and update Ratings where they exist"  style="cursor:pointer;"/>
							<label for="addMergeUpdate2" title="Add KRs and Ratings from source to targets and update Ratings where they exist" style="cursor:pointer;">Add and Overwrite</label>
						</li>
					</ul>
				</div>
				<div class="btns">
					<button type="button" class="btn btn-primary btn-add" onclick="completeCopying()">Proceed to copy</button>
					<button type="button" class="btn btn-danger btn-cancel" data-dismiss="modal">Cancel</button>
				</div>
				<div class="input-group m-b-1" style="margin:10px 0 -10px;">
					<span class="input-group-addon">Copy To</span>
					<select class="form-control" id="copyKRsTO" ></select>
				</div>
				<div id="copyToPersonalityListWrapper" class="input-group m-b-1" style="margin:20px 0 -10px; display:none;">
					<span class="input-group-addon">Persona</span>
					<select class="form-control" id="copyToPersonalityList" ></select>
				</div>
			</div>
			<div class="modal-footer" align="left" style="padding-top:0;">

				<div class="input-group m-b-1" style="margin-bottom:-45px;margin-top:10px;width:50%;max-width:250px;">
					<span class="input-group-addon">Owner</span>
					<select class="form-control" id="owneresCopyKRs" >
						<option value="1">All </option>
					</select>
				</div>
				<table 
					id="knowledgeRecordSelectcopyKRs"
					data-show-refresh=false
					data-toggle="table" 
					data-search=true
					data-detail-view=false
					data-detail-formatter=""
					data-sort-name="knowledgeRecords" 
					data-sort-order="asc"
					data-method='post'
					data-data-field='data'
					data-smart-display=true
					data-pagination=true
					data-search-on-enter-key=false
					data-query-params=knowledgeRecordSelectcopyKRsPARAMs
					data-unique-id="personalityRelationId"


<?php /*
					data-single-select=true
					data-side-pagination='server'
					data-silent-sort=false
					data-url=''
*/ ?>
				>
				<thead>
					<tr>
						<th data-cell-style='knowledgeRecordCellStyle' data-field="knowledgeRecords" data-searchable=true  data-width='50%'>
							Knowledge Record
						</th>
						<th data-cell-style='knowledgeRecordCellStyle' data-field="ownership" data-searchable=false data-width='13%' data-formatter='formatter'>
							Ownership
						</th>
						<th data-cell-style='knowledgeRecordCellStyle' data-field="ownerIdcopyKRs" data-searchable=false data-formatter='formatter'>
							Owner
						</th>
						<th data-cell-style='knowledgeRecordCellStyle' data-field="trash" data-searchable=false data-formatter='formatter' ></th>
						<th data-field="personalityRelationId" data-sortable=false data-searchable=false data-visible=false>personalityRelationId</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	//-------------------------------------------------------------------------
	function openCopyKRs(inID){
		$('#owneresCopyKRs option').remove();
		$('#ownerList option').each(function(){ $('#owneresCopyKRs').append("<option value='"+$(this).val()+"'>"+$(this).text()+"</option>"); });
		//---------------------------------------------------------------------
		/**/
		$("#destOrgID option").remove();
		$('#destOrgID').append("<option value=''>Select Organization</option>"); 
		$.ajax({
			method  :'post',
			url     : apiURL+'/api/dashboard/personality_relation_value/getcopytoorgs/'+orgID,
			data    : {},
			dataType: 'json',
			
			complete: function(){ copyKRsCalled = false; },
			success: 
				function( response ){
					for( var i in response ){
						$('#destOrgID').append("<option value='"+response[i].organizationId+"'>"+response[i].organizationShortName+"</option>"); 
					}
				},
			error:
				function(xhr, textStatus, errorThrown ){ 
//					myClass.showError("Error:["+xhr.status+"] "+errorThrown); 
				}
		});
		/**/
		$("#addMergeUpdate1").prop('checked', true);
		$('#copyKRsDLG').modal({show:true, keyboard: false, backdrop:'static'});
		//---------------------------------------------------------------------
		$("input#personalityIDcopyKRs").val ($("#personalityList option:selected").val ());
		$("span.personalityIDcopyKRs" ).text($("#personalityList option:selected").text());
//		$("span.personalityIDcopyKRs" ).text($("#personalityList option:selected").data('full'));
		//---------------------------------------------------------------------
		var txtTXT = "Personality";
		$("#copyKRsTO option").remove();
		$("#copyKRsTO").append('<option value="0">select ... [copy to...]</option>');
/*
		$("#copyKRsTO").append('<option value="1">All Personas</option>');
		$("#copyKRsTO").append('<option value="2">All Personalities and all Personas</option>');
		if($("#personalityList option:selected").attr('data-parentID').trim()=='0'){
			$("#copyKRsTO").append('<option value="3">All Personalities produced from this Persona</option>');
			txtTXT = "Persona";
		}
		$("#copyKRsTO").append('<option value="4">A specific Persona</option>');
		$("#copyKRsTO").append('<option value="5">A specific Persona and its Personalities</option>');
		$("#copyKRsTO").append('<option value="6">A specific Personality</option>');
*/		
		if($("#personalityList option:selected").attr('data-parentID').trim()=='0'){
			$("#copyKRsTO").append('<option value="1">All Personas</option>');
			$("#copyKRsTO").append('<option value="4">A specific Persona</option>');
			txtTXT = "Persona";
		}else{
			$("#copyKRsTO").append('<option value="1">All Personas</option>');
			$("#copyKRsTO").append('<option value="4">A specific Persona</option>');
			$("#copyKRsTO").append('<option value="6">A specific Personality</option>');
		}
		$("#copyKRsTO").val(0);
		$("span.personalityIDcopyKRs" ).parent().find('label').text(txtTXT);
		$("#spanCopyKRsDLG").text(txtTXT);
		//---------------------------------------------------------------------
		$("input#knowledgeRecordsIDcopyKRs").val(0);
		$("span.knowledgeRecordsIDcopyKRs").html('<ol style="padding-left:25px !important;"></ol>');
		//---------------------------------------------------------------------
		var prsID = $("#personalityList").val();
//		$("#knowledgeRecordSelectcopyKRs").bootstrapTable('refresh',{url:'<?=env('API_URL');?>/api/dashboard/relation/allrelations'});
		if(inID!=0){
			let tmpAllSelections = $("#knowledgeRecord").bootstrapTable('getAllSelections');
			$('#copyKRsDLG .modal-footer').hide();
			$('#copyKRsDLG .modal-body>div>label.w140').text("Knowledge Record");
			if(tmpAllSelections.length==0){
				$.ajax({
					method  :'post',
					url     : apiURL+'/api/dashboard/personality_relation_value/getkrcaption/'+inID,
					data    : {},
					dataType: 'json',

					complete: function(){ copyKRsCalled = false; },
					success: 
						function( response ){
							$("span.knowledgeRecordsIDcopyKRs ol").append(
								"<li data-id='"+inID+"' data-krID='"+response.krId+"'>"+
									"<i class='fa fa-check'></i>"+
									response.knowledgeRecord+
								"</li>"
							);
						},
					error:
						function(xhr, textStatus, errorThrown ){ 
						}
				});
			}else{
				for(let tmpIndx in tmpAllSelections){
					$("span.knowledgeRecordsIDcopyKRs ol").append(
						"<li data-id='"+tmpAllSelections[tmpIndx].personalityRelationId+"' data-krID='"+tmpAllSelections[tmpIndx].relationId+"'>"+
							"<i class='fa fa-check'></i>"+
							tmpAllSelections[tmpIndx].knowledgeRecords+
						"</li>"
					);
				}
			}
		}else{
			$('#copyKRsDLG .modal-body>div>label.w140').text("Knowledge Records");
			$('#copyKRsDLG .modal-footer').show();
			let url = '<?=env('API_URL');?>/api/dashboard/personality_relation_value/all/<?=$orgID;?>/-1/'+$("#personalityList option:selected").val();
			$("#knowledgeRecordSelectcopyKRs").bootstrapTable('refresh',{url:url});
			$("#knowledgeRecordSelectcopyKRs").bootstrapTable('selectPage',1);
			$("#knowledgeRecordSelectcopyKRs").bootstrapTable('resetSearch','');
		}
		//---------------------------------------------------------------------
	}
	//-------------------------------------------------------------------------
	$("#destOrgID").on('change', function(){$("#copyKRsTO").change(); });
	$("#copyKRsTO").change(function(e){
		if(['4', '5', '6'].indexOf(this.value) != -1) {
			
			$("#copyToPersonalityListWrapper").show();
			
//			var tmpOrgID = $("#ownerList").val();
			var tmpOrgID = $("#destOrgID").val();
			var url = '';
			var txtTXT = '...';
			$("#copyToPersonalityList").html('');
			$("#copyToPersonalityList").append('<option value="0" selected="selected">Select '+txtTXT+'</option>');
			$("#copyToPersonalityList").val(0);
			if(tmpOrgID==""){ $("#destOrgID").focus(); return; }
			
			switch(this.value) {
				case '4': // A Specific persona
					txtTXT = 'Persona';
					$("#copyToPersonalityListWrapper > span").text(txtTXT);
					url = apiURL+'/api/dashboard/personality/zeroPersonality/'+orgID+'/'+tmpOrgID+'/personalityName/asc';
				break;
				
				case '5': //A specific persona and its personalities
					txtTXT = 'Persona';
					$("#copyToPersonalityListWrapper > span").text(txtTXT);
					url = apiURL+'/api/dashboard/personality/zeroPersonality/'+orgID+'/'+tmpOrgID+'/personalityName/asc';
				break;
				
				case '6': //A specific personality
					txtTXT = 'Personality';
					$("#copyToPersonalityListWrapper > span").text(txtTXT);
					url = apiURL+'/api/dashboard/personality/nonzeroPersonality/'+orgID+'/'+tmpOrgID+'/personalityName/asc';
				break;
			}
			
			$("#copyToPersonalityList").html('');
			$("#copyToPersonalityList").append('<option value="0" selected="selected">Select '+txtTXT+'</option>');
			$("#copyToPersonalityList").val(0);
			$("#copyToPersonalityList").focus();
			
			getPersonalityList(url, "#copyToPersonalityList");
			
		}else{
			$("#copyToPersonalityListWrapper").hide();
		}
	})
	//-------------------------------------------------------------------------
	function knowledgeRecordSelectcopyKRsPARAMs(params){
/*
		params.orgID=<?=$orgID;?>;
		params.ownerId=$("#owneresCopyKRs").val();
		params.page=(params.offset/params.limit)+1;
*/
		return params;
	}
	//-------------------------------------------------------------------------
	$(function(){
		$("#owneresCopyKRs").on('change', 
									function(){
										let url = '<?=env('API_URL');?>/api/dashboard/personality_relation_value/all/<?=$orgID;?>/'+
													$(this).val()+'/'+
													$("#personalityList option:selected").val();
										$("#knowledgeRecordSelectcopyKRs").bootstrapTable('refresh',{url:url}); 
									}
								);
		$( "#copyKRsDLG" ).on('shown.bs.modal', function(){
//			$("#destOrgID option").remove();
		});
	});
	//-------------------------------------------------------------------------
	var copyKRsCalled = false;
	var copyKRsShwErr = false;
	var copyKRsindxLI = 0;
	var totalKRs2Copy = 0;
	function completeCopying(){
		copyKRsShwErr = false;
		$("#copyKRsDLG button, #copyKRsDLG select, #copyKRsDLG input, #copyKRsDLG table").prop('disabled', true);
		$("span.knowledgeRecordsIDcopyKRs").animate({scrollTop:0} ,'fast');
		totalKRs2Copy = $("span.knowledgeRecordsIDcopyKRs ol li").length;
		$("span.knowledgeRecordsIDcopyKRs ol li").each(function(){
			if(!copyKRsCalled){
				$("span.knowledgeRecordsIDcopyKRs").animate({scrollTop:copyKRsindxLI} ,'fast');
				copyKRsCalled = true;
				callCopyKRs(this);
			}
		});
		$("#copyKRsDLG button, #copyKRsDLG select, #copyKRsDLG input, #copyKRsDLG table").prop('disabled', false);
	}
	//-------------------------------------------------------------------------
	function callCopyKRs(obj){
		var data = {};
		//---------------------------------------------------------------------
		data.userID = "<?=session()->get('userID');?>";
		//---------------------------------------------------------------------
		data.pID  = $("#personalityList").val();
		//---------------------------------------------------------------------
		data.addMergeUpdate = ( $("#addMergeUpdate2").prop('checked') ?2 :1 );
		//---------------------------------------------------------------------
		data.destOrgId  = $("#destOrgID").val();
		if(data.destOrgId==''){
			if(	!copyKRsShwErr ){ myClass.showError("Error: Select [Copy to Org]");  }
			copyKRsShwErr = true;
			copyKRsCalled = false;
			return;
		}
		//---------------------------------------------------------------------
		data.howToCopy = $("#copyKRsTO").val();
		if(data.howToCopy==0){
			if(	!copyKRsShwErr ){ myClass.showError("Error: Select [Copy to]");  }
			copyKRsShwErr = true;
			copyKRsCalled = false;
			return;
		}
		//---------------------------------------------------------------------
		$(obj).find('i').remove();
		$(obj).prepend('<i class="fa fa-spinner fa-spin" style="color:gray;"></i>');
		copyKRsindxLI+=$(obj).height();
		data.personalityRelationId = $(obj).data('id');
		data.krID = $(obj).data('krid');
		if(data.personalityRelationId==0){
			if($(obj).next().length!=0){
				$("span.knowledgeRecordsIDcopyKRs").animate({scrollTop:copyKRsindxLI} ,'fast');
				callCopyKRs($(obj).next()); 
			}
			return;
		}
		//--------------------------------------------------------------------
		if(['4', '5', '6'].indexOf($("#copyKRsTO").val()) != -1){
			data.destPersonalityId = $("#copyToPersonalityList").val();
			if(data.destPersonalityId==0){
				if(	!copyKRsShwErr ){ myClass.showError("Error: Select [" + $("#copyToPersonalityListWrapper > span").text() + "]");  }
				copyKRsShwErr = true;
				copyKRsCalled = false;
				return;
			}
		}
		//---------------------------------------------------------------------
		$.ajax({
			method  :'post',
			url     : apiURL+'/api/dashboard/personality_relation_value/copyKRs/'+data.personalityRelationId,
			data    : data,
			dataType: 'json',
			
			complete: function(){ copyKRsCalled = false; },
			success: 
				function( response ){
					if(response.result==0){
						$(obj).find('i').remove();
						$(obj).prepend('<i class="fa fa-check" style="color:green;"></i>');
						$(obj).attr('data-id', '0');
						if($(obj).next().length!=0){
							$("span.knowledgeRecordsIDcopyKRs").animate({scrollTop:copyKRsindxLI} ,'fast');
							callCopyKRs($(obj).next()); 
						}else{ 
							$("#copyKRsDLG").modal('hide'); 
							$("#knowledgeRecord").bootstrapTable("refresh");
						}
					}else{
						myClass.showError("Error: "+response.msg); 
						copyKRsShwErr = true;
					}
				},
			error:
				function(xhr, textStatus, errorThrown ){ 
					myClass.showError("Error:["+xhr.status+"] "+errorThrown); 
				}
		});
	}
	//-------------------------------------------------------------------------
	function knowledgeRecordSelectcopyKRsMarked(){
		$('.myClassKnowledgeRecord').removeClass('myClassKnowledgeRecord');
		$("span.knowledgeRecordsIDcopyKRs ol li").each(function(){
			let thisId = $(this).data('id');
//			$("#knowledgeRecordSelectcopyKRs tr[data-uniqueid="+thisId+"]").find('td').addClass('myClassKnowledgeRecord');
			$("#knowledgeRecordSelectcopyKRs tr[data-uniqueid="+thisId+"]>td").addClass('myClassKnowledgeRecord');
			$("#knowledgeRecordSelectcopyKRs tr[data-uniqueid="+thisId+"]>td:last-child").html('<i class="fa fa-trash" ></i>');
		});
	}
	//-------------------------------------------------------------------------
	function knowledgeRecordSelectcopyKRsRemoveByID(id){
		$("span.knowledgeRecordsIDcopyKRs ol li").each(function(){
			let thisId = $(this).data('id');
			if(thisId==id){ $(this).remove(); }
		});
		$("#knowledgeRecordSelectcopyKRs tr[data-uniqueid="+id+"]>td:last-child").html('');
		knowledgeRecordSelectcopyKRsMarked();
	}
	//-------------------------------------------------------------------------
	function knowledgeRecordSelectcopyKRsRemoveItem(obj){
		let thisId = $(obj).parent().data('id');
		$(obj).parent().remove();
		$("#knowledgeRecordSelectcopyKRs tr[data-uniqueid="+thisId+"]>td:last-child").html('');
		knowledgeRecordSelectcopyKRsMarked();
	}
	//-------------------------------------------------------------------------
	$(function(){
  		$('#knowledgeRecord').on('all.bs.table', function(row, element, field){
			//$(".menu-actions").remove();
		});
		$('#showGlobalKRs, #krDestOrgID').on('change', function(){
			var prsID = $("#personalityList").val();
			let ownID = 0;
			if(orgID==0){ ownID = $("#krDestOrgID").val(); }
			else{ ownID = $("#personalityList option:selected").data('owner'); }
			
			if(ownID==null){ return; }
			
			var url = '<?=env('API_URL');?>/api/dashboard/relation/knowledgerecordswithowner/'+orgID+'/'+prsID+'/'+ownID;
			
			if($("#showGlobalKRs").prop('checked')){
				$("#knowledgeRecordSelect").bootstrapTable( 'refresh',{ url:url+"/1" } ); }
			else{
				$("#knowledgeRecordSelect").bootstrapTable( 'refresh',{ url:url+"/0" } );
			}

			$("#knowledgeRecordSelect").bootstrapTable('selectPage',1);
			$("#knowledgeRecordSelect").bootstrapTable('resetSearch','');
			
			$("input#knowledgeRecordID").val(0);
			$("span.knowledgeRecordID").text('');
			$('.myClassKnowledgeRecord').removeClass('myClassKnowledgeRecord');
//			$(field).find('td').addClass('myClassKnowledgeRecord');
			
		});
		//---------------------------------------------------------------------
		$("#showProblemsSolutions0, #showProblemsSolutions1, #showProblemsSolutions2").on('change', function(){
			knowledgeRecordRefresh();
		})
		//---------------------------------------------------------------------
	});
	//-------------------------------------------------------------------------
	$("#knowledgeRecord div.search").ready(function(){
		$(".columns.columns-right.btn-group.pull-right")
			.prepend("<button style='padding:2px 5px;' class='btn btn-primary' onclick='$(\"#knowledgeRecord\").bootstrapTable(\"resetSearch\",\"\");'>clear search</button>");
//		$(".search>input").css("max-width", "66%");
		$(".search>input")
			.css("display", "inline-block")
			.on("keypress", function(){ $("#knowledgeRecord").bootstrapTable('refresh'); });

//		$(".search>button").css("float", "left");
//		$(".search>button").css("margin", "0");
//		$(".search>button").css("margin-right", "2%");
//		$(".search>button").css("padding", "6px 5px");
		
		//$("#knowledgeRecord div.search>input").on("keypress", function(){ console.log("AAA")});
	});

</script>
