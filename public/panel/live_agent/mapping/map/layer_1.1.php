<?php $rootType       = \App\LiveAgentType::where('parent_id', 0)->where('structure_id', $mapBotRecord->structure_id)->first(); ?>
<?php $detailRootType = \App\LiveAgentMapDetail::getData(0, $bot_id)->get(); ?>


<?php if(!$detailRootType->isEmpty()): ?>
<?php $rootType = \App\LiveAgentType::find(1); ?>
<?php foreach($detailRootType as $tmp): ?>
<div style="width:100%;text-align:left;margin:auto;">
	<div class="panel panel-primary">
		<div class="panel-heading" style="font-weight:normal;">Parameters for Live Agent API</div>
		<div class="panel-body">
			<div id="type_<?=$tmp->id;?>" class="">
				<div class="input-group m-b-1 mb2">
					<span class="input-group-addon"><?=$rootType->name;?> Name</span>
					<input class="form-control" value="<?=$tmp->intentName;?>" disabled />
				</div>
				<div class="input-group m-b-1 mb2">
					<span class="input-group-addon">API Version</span>
					<input class="form-control" value="<?=$tmp->apiVersion;?>" id="apiVersion" maxlength="16" />
				</div>
				<div class="input-group m-b-1 mb2">
					<span class="input-group-addon">API URL</span>
					<input class="form-control" value="<?=$tmp->apiUrl;?>" id="apiUrl" maxlength="300" />
				</div>
				<div class="input-group m-b-1 mb2">
					<span class="input-group-addon">Organization ID</span>
					<input class="form-control" value="<?=$tmp->organizationId;?>" id="organizationId" maxlength="32" />
				</div>
				<div class="input-group m-b-1 mb2">
					<span class="input-group-addon">Deployment ID</span>
					<input class="form-control" value="<?=$tmp->deploymentId;?>" id="deploymentId" maxlength="32" />
				</div>
				<div class="input-group m-b-1 mb2">
					<span class="input-group-addon">Button ID</span>
					<input class="form-control" value="<?=$tmp->buttonId;?>" id="buttonId" maxlength="32" />
				</div>
				<div class="input-group m-b-1 mb2">
					<span class="input-group-addon">Timeout (sec)</span>
					<input class="form-control" value="<?=$tmp->timeout;?>" id="timeout"  maxlength="6" 
						   <?=(($tmp->timeoutSwitch==1) ?'' :'disabled');?>
						   style="width: calc(100% - 110px); margin-right: 10px;"
					/>
					<div>
						<input type="checkbox"
							   id="timeoutSwitch"
							   <?=(($tmp->timeoutSwitch==1) ?'checked' :'');?>
							   data-toggle="toggle"
							   data-on="Active" data-off="Inactive"
							   data-width="100"
							   data-onstyle="info"
							   >
					</div>
				</div>
				<button class="btn btn-info" style="" onClick='callEdit(<?=$tmp->id;?>)'>
					<small>Save Changes</small>
				</button>
			</div>
		</div>
	</div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<div id="intentTabs">
	<div id="type0">
		<ul class="nav nav-tabs">
			<?php
			if(!$detailRootType->isEmpty()){
				foreach($detailRootType as $tmp){
					?>
					<li id="li_<?=$tmp->id;?>" class="type_<?=$tmp->id;?>">
						<a data-toggle="tab" href="#type__<?=$tmp->id;?>" style="text-transform: capitalize">Mapping</a>
					</li>
					<?php
				}
			}
			?>
		</ul>
	</div>

	<div class="tab-content type0">
		<?php
		if(!$detailRootType->isEmpty()){
			$rootType = \App\LiveAgentType::find(1);
			foreach($detailRootType as $tmp){
			?>
			<div id="type__<?=$tmp->id;?>" class="tab-pane fade">
<?php /*
				<div class="intent_data">
					<hr width="90%" style="margin-top:0">
					<div style="width:90%;margin:auto;">
						<div class="input-group m-b-1 mb2">
							<span class="input-group-addon"><?=$rootType->name;?> Name</span>
							<input class="form-control" value="<?=$tmp->intentName;?>" disabled />
						</div>
						<div class="input-group m-b-1 mb2">
							<span class="input-group-addon">API Version</span>
							<input class="form-control" value="<?=$tmp->apiVersion;?>" id="apiVersion" maxlength="16" />
						</div>
						<div class="input-group m-b-1 mb2">
							<span class="input-group-addon">API URL</span>
							<input class="form-control" value="<?=$tmp->apiUrl;?>" id="apiUrl" maxlength="300" />
						</div>
						<div class="input-group m-b-1 mb2">
							<span class="input-group-addon">Organization ID</span>
							<input class="form-control" value="<?=$tmp->organizationId;?>" id="organizationId" maxlength="32" />
						</div>
						<div class="input-group m-b-1 mb2">
							<span class="input-group-addon">Deployment ID</span>
							<input class="form-control" value="<?=$tmp->deploymentId;?>" id="deploymentId" maxlength="32" />
						</div>
						<div class="input-group m-b-1 mb2">
							<span class="input-group-addon">Button ID</span>
							<input class="form-control" value="<?=$tmp->buttonId;?>" id="buttonId" maxlength="32" />
						</div>
						<div class="input-group m-b-1 mb2">
							<span class="input-group-addon">Timeout (sec)</span>
							<input class="form-control" value="<?=$tmp->timeout;?>" id="timeout"  maxlength="6" 
								   <?=(($tmp->timeoutSwitch==1) ?'' :'disabled');?>
								   style="width: calc(100% - 110px); margin-right: 10px;"
							/>
							<div>
								<input type="checkbox"
									   id="timeoutSwitch"
									   <?=(($tmp->timeoutSwitch==1) ?'checked' :'');?>
									   data-toggle="toggle"
									   data-on="Active" data-off="Inactive"
									   data-width="100"
									   data-onstyle="info"
									   >
							</div>
						</div>
						<button class="btn btn-info" style="" onClick='callEdit(<?=$tmp->id;?>)'>
							<small>Save Changes</small>
						</button>
					</div>
				</div>
				<hr width="90%" />

*/ ?>
				<div class="intent_kr">
					<h3>Kama-DEI Knowledge Records</h3>
					<div id="liveAgentMapKRs">
						<?php
						$liveAgentMapKRs = \App\LiveAgentMapKR::getData($tmp->mappingBot_id)->get();
						if(!$liveAgentMapKRs->isEmpty()):
						?>
						<div style="width:100%; padding-right:20px;">
						<table style="width:100%; border:1px solid #ddd;" id="knowledgeRecordTable">
						<thead style="height:50px">
							<th style="text-align: center; border-right: none">Knowledge Record</th>
							<th style="border-left: none"></th>
							<th style="text-align: center;border-right: none">Hand Off Message</th>
							<th style="border-left: none"></th>
						</thead>
						<tbody>
						<?php
						foreach($liveAgentMapKRs as $tpMpKR):
						?>
							<tr id="row_<?=$tpMpKR->mapping_kr_id;?>">
							<td style="width:50%;border-right: none">
								<p class="form-control krVAL" id="krVAL_<?=$tpMpKR->mapping_kr_id;?>"><?=$tpMpKR->mappedTo;?></p>
							</td>
							<td style="width:102px;border-left: none">
								<button class="btn btn-info" style="height:35px;" 
									onClick='callSelectKR(<?="{$tpMpKR->kr_id},{$tpMpKR->mappingBot_id},{$tpMpKR->mapping_kr_id}";?>)'>
									<small>Select KR</small>
								</button>
							</td>
							<td style="width:50%;border-right: none">
								<div id="krMSG_SHOW_<?=$tpMpKR->mapping_kr_id;?>">
									<textarea class="form-control krMSG" disabled><?=$tpMpKR->handOffMessage;?></textarea>
									<div style="text-align: left">
										<button class="btn btn-info"
												onClick="showHideMSG(<?=$tpMpKR->mapping_kr_id;?>,0,<?=$tpMpKR->mappingBot_id;?>)">
											Edit
										</button>
									</div>
								</div>
								<div id="krMSG_EDIT_<?=$tpMpKR->mapping_kr_id;?>" style="display:none">
									<textarea class="form-control krMSG" maxlength="1000"
											  id="krMSG_<?=$tpMpKR->mapping_kr_id;?>"><?=$tpMpKR->handOffMessage;?></textarea>
									<div style="text-align: left">
										<button class="btn btn-info"
												onClick="showHideMSG(<?=$tpMpKR->mapping_kr_id;?>,1,<?=$tpMpKR->mappingBot_id;?>)">
											Save
										</button>
										<button class="btn btn-danger" style="float: right"
												onClick="showHideMSG(<?=$tpMpKR->mapping_kr_id;?>,2,<?=$tpMpKR->mappingBot_id;?>)">
											Cancel
										</button>
									</div>
								</div>
							</td>
							<td style="width:43px;border-left: none">
							<?php /*if($tpMpKR->kr_order==0): ?>
							<button class="btn btn-warning" style="height: 35px;" title="clear"
									onClick='sendToController_mapping(<?="{$tpMpKR->mapping_kr_id},0,0";?>)'>
								<i class='fa fa-close'></i>
							</button>
							<?php else: ?>
							<button class="btn btn-danger" style="height: 35px;" title="delete"
									onClick='sendToController_mapping(<?="{$tpMpKR->mapping_kr_id},0,0";?>)'>
								<i class='fa fa-trash'></i>
							</button>
							<?php endif;*/ ?>
							<button class="btn btn-danger" style="height: 35px;" title="delete"
									onClick='sendToController_mapping(<?="{$tpMpKR->mapping_kr_id},0,0";?>)'>
								<i class='fa fa-trash'></i>
							</button>
							</td>
							</tr>
						<?php
						endforeach;
						?>
						</tbody>
						</table>
						<?php
						endif;
						?>
					</div>
					<hr width="90%" style="margin-top:0">
					<div style="text-align: right">
						<button class="btn btn-success" style="height:35px; margin-right:20px;" id="newRowBTN"
								onClick='sendToController_mapping(<?="0, {$tmp->mappingBot_id},0";?>)'>
							New Row
						</button>
					</div>
				</div>
			</div>
			<?php
			}
		}
		?>
	</div>

</div>

<script type="application/javascript">
	$("#timeout").on("keyup", function(key){
		let reg     = /^[0-9]+$/;
		let timeout = $("#timeout").val().trim();
		if(timeout==''){ return; }
		if( !reg.test(timeout) ){
			myClass.showError("Invalid Timeout");
		}
	});
	$("#timeoutSwitch").on("change", function(){
		$("#timeout").prop('disabled', !($("#timeoutSwitch").prop('checked')));
		if($("#timeoutSwitch").prop('checked')==false){
			$("#timeout").prop('disabled', true);
			$("#timeout").val('');
		}else{
			$("#timeout").focus();
		}
	});
	//---------------------------------------------------------
	function callEdit(id){
		var data = {};
		data.id      = id;
		data.apiVersion     = $("#apiVersion").val().trim();
		data.apiUrl         = $("#apiUrl").val().trim();
		data.organizationId = $("#organizationId").val().trim();
		data.deploymentId   = $("#deploymentId").val().trim();
		data.buttonId       = $("#buttonId").val().trim();
		data.timeout        = $("#timeout").val().trim();
		data.user_id        = "<?=$session->get('userID');?>";
		data.timeoutSwitch  = (($("#timeoutSwitch").prop('checked')==true) ?1 :0);
		
		let reg     = /^[0-9]+$/;
		if(data.timeout!=''){
			if( !reg.test(data.timeout) ){
				myClass.showError("Invalid Timeout");
				return;
			}
		}else{ data.timeout = 0; }

		if(data.timeoutSwitch==0){
			data.timeout = 0;
			$("#timeout").prop('disabled', true);
			$("#timeout").val('');
		}

		$.ajax({
			url: apiURL+'/api/dashboard/live_agent/mapping/editrow',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ },
			success: function(res){
				if(res.result == 0){
					$("li."+res.tag+">a").text(data.val1);
					$("span."+res.tag).text(data.val1);
					myClass.showSuccess("data changed");
				}else{ myClass.showError(res.msg); }
			},
			error: function(e){ myClass.showError(e.status+" : "+e.statusText); }
		});
	}
	//---------------------------------------------------------
	function showHideMSG(mapping_kr_id, flag, mappingBot_id){
		switch(flag){
			case 0:{
				if($("#krVAL_"+mapping_kr_id).text().trim()==""){ return; }
				$("#krMSG_SHOW_"+mapping_kr_id).hide();
				$("#krMSG_EDIT_"+mapping_kr_id).show();
				$("textarea.krMSG").focus();
				return;
			}
			case 1:{
				let data = {};
				data.mapping_kr_id  = mapping_kr_id;
				data.mappingBot_id  = mappingBot_id;
				data.handOffMessage = $("#krMSG_"+mapping_kr_id).val().trim();
				
				$.ajax({
					url: apiURL+'/api/dashboard/live_agent/mapping/handoffmessage',
					type: 'put',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json'
					},
					data: JSON.stringify(data),
					beforeSend: function(){ },
					success: function(res){
						if(res.result == 0){
							$("#krMSG_EDIT_"+mapping_kr_id).hide();
							$("#krMSG_SHOW_"+mapping_kr_id).show();
							$("#krMSG_SHOW_"+mapping_kr_id+" textarea").val(data.handOffMessage);
						}else{ myClass.showError(res.msg); }
					},
					error: function(e){ myClass.showError(e.status+" : "+e.statusText); }
				});
				return;
			}
			case 2:{
				$("#krMSG_EDIT_"+mapping_kr_id).hide();
				$("#krMSG_SHOW_"+mapping_kr_id).show();
				$("#krMSG_EDIT_"+mapping_kr_id+" textarea").val($("#krMSG_SHOW_"+mapping_kr_id+" textarea").val());
				return;
			}
		}
	}
</script>
