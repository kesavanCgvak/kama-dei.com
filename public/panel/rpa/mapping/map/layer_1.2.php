<?php
	$layer2 = \App\LiveAgentType::where('parent_id', $baseTypeID)->where('structure_id', $mapBotRecord->structure_id)->first();
	$layer3 = null;//\App\LiveAgentType::where('parent_id', $layer2->id)->where('structure_id', $mapBotRecord->structure_id)->first();
	$detailLayer2Type = \App\LiveAgentMapDetail::getData($baseDetail->id, $bot_id, $layer2->id)->get();
?>
<div class="slot_data" style="width:calc(100% - 32px);">
	<h3 style="text-transform:capitalize"><?=$layer2->name;?></h3>
	<div style="width:100px; display:inline-block; vertical-align:top; text-align:right; margin: -40px 0 10px; float: right">
		<button style="width:100%;" class="btn btn-success" 
				onClick="callAddLayerModal(<?=$baseDetail->id;?>, <?=$layer2->id;?>, <?=$bot_id;?>, '<?=$layer2->name;?>', 2)">
			<small style="text-transform:capitalize;">Add <?=$layer2->name;?></small>
		</button>
	</div>
	<hr width="100%" style="margin-top:0">
	<div style="width:90%;margin:auto;">

		<div class="fixed-table-container" style="width: 100%;">
			<div class="fixed-table-header" style="margin-right: 0px;">
				<table class="valueTBL" id="tbl_<?=$baseDetail->id;?>" >
					<thead>
						<tr style="height: 40px;">
						<th style="width:70%; text-transform:capitalize; padding-left: 10px;">
							<?=$rootType->name;?>: <span class="type_<?=$baseDetail->id;?>" style="font-weight: 100">
							<?=$baseDetail->val1;?>
							</span>
						</th>
						<th style="width: 30%; text-transform:capitalize; font-size:small;" data-field="slotTypeKR">
						</th>
						</tr>
					</thead>
					<tbody>
					<?php
					if(!$detailLayer2Type->isEmpty()){
						foreach($detailLayer2Type as $tmpVal){
						?>
							<tr id="type_<?=$tmpVal->id;?>">
								<td>
									<input class="form-control" id="val1_<?=$tmpVal->id?>" value="<?=$tmpVal->val1?>"
										   style="width:calc(100% - 150px); display: inline-block;"/>
									<button class="btn btn-info" style="float:right; margin-right:5px;"
											onClick='callEdit(<?=$tmpVal->id;?>)'>
										<small>Save Changes</small>
									</button>
								</td>
								<td>
									<button class="btn btn-danger" 
											style="float:left; text-transform: capitalize; margin-left:5px;"
											onClick='callDelete(<?=$tmpVal->id;?>)'>
										<small>Delete</small>
									</button>
									<button class="btn btn-info" style="float:right; margin-right:5px;" 
											onClick='loadLayer3(<?="{$baseDetail->id},{$tmpVal->id},{$bot_id},{$layer3->id}";?>)'>
										<small>Show Values</small>
									</button>
								</td>
							</tr>
						<?php
						}
					}
					?>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</div>

<div class="value_data" id="valueData<?=$baseDetail->id;?>" style="width:calc(100% - 32px);">
	<h3 style="text-transform:capitalize"><?=$layer3->name;?></h3>
	<hr width="100%" style="margin-top:0">
	<div style="width:90%;margin:auto;">
		<div class="fixed-table-container" style="padding-bottom: 40px;">
			<div class="fixed-table-header" style="margin-right: 0px;">
				<table class="valueTBL" id="tbl_0" >
					<thead>
						<tr style="height: 40px;">
						<th style="width:40%; text-transform:capitalize; font-size:small; padding-left: 10px;">
							<?=$layer3->name;?>
						</th>
						<th style="width: 60%; text-transform:capitalize; font-size:small; padding-left: 10px;" >
							Kama-DEI Knowledge Records
						</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
