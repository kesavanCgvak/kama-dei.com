<style>
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
	
	.slider-onTable, #slider-0{ overflow:unset !important; }
	.slider-onTable > .ui-slider-handle{ margin-top:-1px;font-size:10px;width:1.9em;height:1.9em;background:#50051f;color:#ffffff; }
	.slider-onModal > .ui-slider-handle{ margin-top:-1px;font-size:10px;width:1.9em;height:1.9em;background:#50051f;color:#ffffff; }
	
	.sliderHolder{ margin-left:5px;margin-right:5px; }

	#valueSelect .myClassValueSelect,
	#valueSelect .myClassValueSelect:hover,
	#knowledgeRecordSelect .myClassKnowledgeRecord,
	#knowledgeRecordSelect .myClassKnowledgeRecord:hover
		{color:red !important; }
</style>
<div class="modal fade" role="dialog" id="addKnowledgeRecordValue" style="z-index:9999">
	<div class="modal-dialog" >
		<div class="modal-content">
			<div class="modal-header" style="background:#00a6b4;">
				<p style="color:black;font-size:14px;">Current Knowledge Record:</p>
				<span style="display:block;color:yellow;font-weight:bold;font-size:16px;text-indent:2em;"></span>
			</div>
			<div class="modal-body">
				<div>
					<label class="w130 ">Owner</label> : <span class="ownerKRV"></span>
					<input type="hidden" id="ownerKRV" value="<?=$mapBotRecord->ownerId;?>" />
				</div>
				<div>
					<label class="w130">Ownership</label> : <span><?=(($mapBotRecord->ownerId==0) ?'Public' :'Private');?></span>
					<input type="hidden" id="ownershipKRV" value="<?=(($mapBotRecord->ownerId==0) ?'0' :'2');?>" />
				</div>
				<div>
					<label class="w130">Value</label> : <span class="valueKRV" style="font-weight:bold"></span>
					<input type="hidden" id="valueKRV" value="" />
				</div>
				<div>
					<label class="w130" style="vertical-align:top;">Scaler value</label> <span style="vertical-align:top;">:</span>
					<div style="width:70%;display:inline-block;margin-left:10px;">
						<div class="slider-onModal" id="slider-0" data="0"></div>
						<div class='sliderTopValueOnTable' style="margin-top:3px;"><span id='min'>-10</span><span id='center'>0</span><span id='max'>10</span></div>
					</div>
					<input type="hidden" id="scalerValueKRV" value="0" />
				</div>
				<input type="hidden" id="personalityRelationIdKRV" value="0" />
				<br />
				<div style="text-align:center;">
					<button type="button" class="btn btn-info"   style="width:150px;" onclick="addKnowledgeRecordValue(0)">Add and Exit</button>
					<button type="button" class="btn btn-info"   style="width:150px;float:right" onclick="addKnowledgeRecordValue(1)">
						Add and Continue
					</button>
					<button type="button" class="btn btn-danger" style="width:150px;float:left;" onClick="closeModal('addKnowledgeRecordValue')">
						Cancel
					</button>
				</div>
			</div>
			<div class="modal-footer" align="left">
				<table 
					id="valueSelect"
					data-toggle="table" 
					data-height=400
					data-search=true
					data-sort-name="termName" 
					data-sort-order="asc"
					data-method='get'
					data-url=''
					data-data-field='data'
					data-single-select=true
<?php /*
					data-smart-display=true
					data-show-refresh=false
					data-pagination=true
*/ ?>
				>
				<thead>
					<tr>
						<th data-cell-style='valueCellStyle' data-field="termName" data-sortable=true  data-searchable=true  data-width='60%'>
							Value
						</th>
						<th data-cell-style='valueCellStyle' data-field="ownership" data-searchable=false data-width='15%' data-formatter='newValformatter'>
							Ownership
						</th>
						<th data-cell-style='valueCellStyle' data-field="ownerId" data-searchable=false data-width='25%' data-formatter='newValformatter'>
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
<div id="rateValueDLG" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header" style="background:#00a6b4">
				<p style="color:black;font-size:14px;">Current Knowledge Record:</p>
				<span style="display:block;color:yellow;font-weight:bold;font-size:16px;text-indent:2em;" id="rateValueKR"></span>
			</div>
			<div class="modal-body">
				<button class="btn btn-info" style="float:left;" onClick="callViewAddKRValue()">Add Persona Knowledge Rating</button>
				<table 
					id="rateValueList"
					class="table table-hover"
					data-toggle="table"
					data-height="400"
					data-url=""
					data-method="POST"
					data-show-refresh=true
					data-cache=false
					data-sort-name="value"
				>
					<thead>
						<th data-width='22%' data-formatter="retValFormatter" data-sortable=true  data-field="value">Value</th>
						<th data-width='35%' data-formatter="retValFormatter" data-sortable=false data-field="scalarValue">Scaler value</th>
						<th data-width='10%' data-formatter="retValFormatter" data-sortable=false data-field="ownership">Ownership</th>
						<th data-width='20%' data-formatter="retValFormatter" data-sortable=false data-field="owner">Owner</th>
						<th data-width='13%' data-formatter="retValFormatter" data-sortable=false data-field="dateCreated">Created</th>
					</thead>
				</table>
			</div>
			<div class="modal-footer">
				<button class="btn btn-danger" style="float:left;width:91px;" onClick="$('#rateValueDLG').modal('hide');">Close</button>
			</div>
		</div>
	</div>
</div>
<script type="application/javascript">
	//---------------------------------------------------------
	$(function(){
		//-----------------------------------------------------
		$("#rateValueDLG").on('load-success.bs.table',function(a, data){ 
			lastPersonalityRelationId = data.id; 
			if( lastPersonalityRelationId==0 ){
				closeModal('rateValueDLG');
				myLEX.showError("inavlid personality knowledge.....try again");
				return;
			}
		});
		$("#rateValueDLG").on('all.bs.table',function(name, args){
			$(".slider-onTable").each(function(){
				$(this).slider({
					max  : 10,
					value:  $(this).attr('data'),
					min  : -10,
					slide: function( event, ui ){ $(this).find(".ui-slider-handle").text( ui.value );  }, 
					stop: function( event, ui ) {myLEX.editScalarValue(this, ui.value); }
				});
				$(this).find(".ui-slider-handle").text( $(this).attr('data') );
			});
		});
		//-----------------------------------------------------
		$('#valueSelect').on('click-row.bs.table', function(row, element, field){
			$("input#valueKRV").val(element.termId);
			$("span.valueKRV").text(element.termName);

			$('.myClassValueSelect').removeClass('myClassValueSelect');
			$(field).find('td').addClass('myClassValueSelect');
		});
		//-----------------------------------------------------
	});
	//---------------------------------------------------------
	var lastKnowledgeRecordCaption = "";
	function callRateValues(itemID){
		lastKnowledgeRecordCaption = "";
		var relationID = $("#"+itemID).data('itemid');
		if(relationID==0){ myLEX.showError("invalid knowledge record"); }
		else{
			lastKnowledgeRecordCaption = $("#"+itemID).val();
			$("#rateValueDLG").modal({backdrop:'static', keyboard:false});
			$("#rateValueKR").html( lastKnowledgeRecordCaption );
			$("#rateValueList")
				.bootstrapTable(
					'refresh',
					{url:apiURL+'/api/dashboard/lex/mapping/getratevalue/'+defaultOrgID+'/'+defaultPersonality+'/'+relationID+'/'+userID+'/'+orgID}
				);
		}
	}
	function retValFormatter(value, row, index, field){
		if(field=='ownership'){
			switch(value){
				case 0: return 'Public';
				case 1: return 'Protected';
				case 2: return 'Private';
			}
		}
		if(field=='owner'){
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
	//---------------------------------------------------------
	function callViewAddKRValue(){
		//-----------------------------------------------------
		$("#addKnowledgeRecordValue .modal-header>span").text(lastKnowledgeRecordCaption);
		<?php if($orgID==0): ?>
		$("span.ownerKRV").text('<?=env('BASE_ORGANIZATION');?>');
		<?php else: ?>
		$("span.ownerKRV").text($("#organizationName").text());
		<?php endif; ?>
		//
		//-----------------------------------------------------
		$("#addKnowledgeRecordValue").modal({backdrop:'static', keyboard:false});
		$("div.modal-backdrop.fade.in:last-child").css("z-index", 9991);
		//-----------------------------------------------------
		$("#slider-0").slider({
			max  : 10,
			value:  0,
			min  : -10,
			slide: function( event, ui ){ $("#slider-0").find(".ui-slider-handle").text( ui.value ); },
			stop: function( event, ui ) { $("#scalerValueKRV").val(ui.value); }
		});
		//-----------------------------------------------------
		$("#slider-0").find(".ui-slider-handle").text( 0 );
		$("#valueKRV"                ).val(0);
		$("#scalerValueKRV"          ).val(0);
		$("span.valueKRV"            ).text('');
		$("#personalityRelationIdKRV").val(lastPersonalityRelationId);
		//-----------------------------------------------------
		$("#valueSelect")
			.bootstrapTable('refresh',{url:'<?=env('API_URL');?>/api/dashboard/term/knowledgerecordValues/'+orgID+'/'+lastPersonalityRelationId});
		$("#valueSelect").bootstrapTable('selectPage',1);
		$("#valueSelect").bootstrapTable('resetSearch','');
	}
	function newValformatter(value, row, index, field){
		if(field=='ownership'){
			switch(value){
				case 0: return 'Public';
				case 1: return 'Protected';
				case 2: return 'Private';
			}
		}
		if(field=='ownerId'){
			if( row.ownerId==null || row.ownerId==0 ){ return '<?=env('BASE_ORGANIZATION');?>'; }
			else{ return row.organization.organizationShortName; }
		}
		return value;
	}
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
	function addKnowledgeRecordValue(flag){ myLEX.addKnowledgeRecordValue(flag); }
	//---------------------------------------------------------
</script>