import { showError, showSuccess, showConfirm, showAlert } from '../DataTable'
//import Columns from '../Columns'
import {LEXBase} from './LEXBase'
import 'jquery-ui/ui/widgets/autocomplete.js'
import 'jquery-ui/ui/widgets/tabs.js'

class LEXMappingNewEdit{
	//----------------------------------------------------
	constructor(tabID, BotName, BotAlias){
		this.lexBase  = new LEXBase(false);
		this.tabID    = tabID;
		this.mapId    = 0;
		this.BotName  = BotName;
		this.BotAlias = BotAlias;
		this.mappedData  = [];
		this.getData();
		this.botData = [];

		this.getAllRelationTypes();
		this.getOrganizations(orgID);
		this.getTermOwners(-1);
//		super();
/*
		this.terms = [];
		this.relationTypes = [];
		this.organizations = [];
	
		this.getOrganizations();

		$("#organizationID").ready(function(){ 
			$("#organizationID").on('change', function(){ myLEX.getPersona($(this).val(), $("#organizationID option:selected").data('persona')); }); 
			if(orgID!=0){ myLEX.getPersona(orgID, defaultPersona); }
		});
*/
	}
	//----------------------------------------------------
	showSuccess(msg){ showSuccess(msg); }
	showError(msg){ showError(msg); }
	showConfirm(callback, msg, yes='Yes', no='No', classYes="btn-danger"){ showConfirm(callback, msg, yes, no, classYes); }
	showAlert(callback, msg, yes='Ok'){ showAlert(callback, msg, yes); }
	//----------------------------------------------------
	getKeys(){
		var lexClass = this;
		$.ajax({
			url: apiURL+"/api/dashboard/lex/setting/getkey",
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: {},
			beforeSend: function(){ },
			success: function(retVal){
				lexClass.lexBase.AccessKey = retVal.keys[2];
				lexClass.lexBase.SecretKey = retVal.keys[4];
				lexClass.careateIntentsTab();
			},
			error: function(e){ showError('Server error'); }
		});
		
	}
	//----------------------------------------------------
	careateIntentsTab(){
		var tabID = this.tabID;
		var tmpThis = this;
		var calBackBot = function(retVal, stackBot){
			tmpThis.setJson(tmpThis.mapId, 'bot', tmpThis.BotName, tmpThis.BotAlias, retVal);
			for(var i in retVal.intents){
				var name     = retVal.intents[i].intentName.replace(/\./gi, '').replace(/ /gi, '').replace(/\'/gi, '').replace(/\"/gi, '').trim();
				var version  = retVal.intents[i].intentVersion;
				var newTabID = 'tabs-'+name;
				stackBot[i]  = {tag:name, name: retVal.intents[i].intentName, version:retVal.intents[i].intentVersion, data:[]};

				var div = ''+
					'<div id="'+newTabID+'">'+
						'<div class="intent_data">'+
							'<h3>Intent</h3>'+
							'<hr width="90%" style="margin-top:0"/>'+
							'<div style="width:90%;margin:auto;">'+
								'<div class="input-group m-b-1" style="margin-bottom:15px">'+
									'<span class="input-group-addon">Intent Name</span>'+
									'<span id="val1_'+stackBot[i].tag+'" class="form-control">'+retVal.intents[i].intentName+'</span>'+
//									'<input class="" value="'+retVal.intents[i].intentName+'" disabled />'+
								'</div>'+
								'<div class="input-group m-b-1" style="margin-bottom:15px">'+
									'<span class="input-group-addon">Intent Versions / Alias</span>'+
									'<span id="val2_'+stackBot[i].tag+'" class="form-control">'+retVal.intents[i].intentVersion+'</span>'+
//									'<input class="form-control" value="'+retVal.intents[i].intentVersion+'" disabled />'+
								'</div>'+
								'<div class="input-group m-b-1" style="margin-bottom:15px">'+
									'<span class="input-group-addon" style="vertical-align: top">Sample Utterance</span>'+
									'<textarea class="form-control sampleUtterance" id="sampleUtterance'+stackBot[i].tag+'" readonly></textarea>'+
								'</div>'+
							'</div>'+
						'</div>'+
						'<div class="intent_kr">'+
							'<h3>Kama-DEI Knowledge Records</h3>'+
							'<hr width="90%" style="margin-top:0"/>'+
							'<div id="searchDIV_'+stackBot[i].tag+'" style=""></div>'+
						'</div>'+
						'<h3>Slots</h3>'+
						'<table '+
							'id="slotList'+stackBot[i].tag+'"'+
							'style="display:"'+
							'class="table table-hover"'+
							'data-toggle="table"'+
							'data-height="300"'+
						'>'+
							'<thead>'+
								'<th data-width="35%" data-field="slotName"></th>'+
								'<th data-width="55%" data-field="slotKR">Kama-DEI Knowledge Records</th>'+
							'</thead>'+
						'</table>'+
						'<hr width="0%"/>'+
						'<h3>Values</h3>'+
						'<table '+
							'id="slotTypeList'+stackBot[i].tag+'"'+
							'style="display:"'+
							'class="table table-hover"'+
							'data-toggle="table"'+
							'data-height="200"'+
						'>'+
							'<thead>'+
								'<th data-width="35%" data-field="slotTypeName"></th>'+
								'<th data-width="55%" data-field="slotTypeKR">Kama-DEI Knowledge Records</th>'+
							'</thead>'+
						'</table>'+
					'</div>';
				
				$("#"+tabID+" ul").append( '<li><a href="#'+newTabID+'">'+stackBot[i].tag+'</a></li>' );
				$("#"+tabID).append(div);
				tmpThis.createSearchKRsItem('searchDIV_'+stackBot[i].tag, stackBot[i].tag, stackBot[i].name, 'intent', "");
				$('#krVAL_'+stackBot[i].tag).data('parentid', botMapID);
				$('#krVAL_'+stackBot[i].tag).prop('disabled', false);
				$("#searchHolder_"+stackBot[i].tag+" button").prop('disabled', false);
				$("#slotList"+stackBot[i].tag+", #slotTypeList"+stackBot[i].tag).bootstrapTable();

				var calBackIntent = function(retVal, stackIntt){
					tmpThis.setJson(tmpThis.mapId, 'intent', retVal.name, retVal.version, retVal);
					var out = "";
					for(var i in retVal.sampleUtterances){ out+=((out=="") ?retVal.sampleUtterances[i] :", "+retVal.sampleUtterances[i]); }			
					$("#sampleUtterance"+stackIntt.tag).val(out);
					var tmpSlotsData = [];
					for(var i in retVal.slots){
						var slotType = retVal.slots[i].slotType.replace(/\./gi, '').replace(/ /gi, '').replace(/\'/gi, '').replace(/\"/gi, '').trim();
						var slotTypeVersion = retVal.slots[i].slotTypeVersion;
						tmpSlotsData.push({ tag:stackIntt.tag+slotType, name:retVal.slots[i].name, type:retVal.slots[i].slotType, data:[] });
						
						var calBackSlotType = function(retVal,stackType){
							tmpThis.setJson(tmpThis.mapId, 'slot', retVal.name, retVal.version, retVal);
							var indx2 = 0;
							var tmpData = [];
							for(var i in retVal.enumerationValues){ 
								var val = retVal.enumerationValues[i].value.replace(/\./gi, '').replace(/ /gi, '').replace(/\'/gi, '').replace(/\"/gi, '').trim();
								var itm ={ tag:stackType.tag+val, name: retVal.enumerationValues[i].value };
								tmpData.push(itm);
								indx2++;
							}
							stackType.data = tmpData;
						}
						tmpThis.getJson(tmpThis.mapId, 'slot', retVal.slots[i].slotType, retVal.slots[i].slotTypeVersion, tmpSlotsData[i], calBackSlotType);
					}
					stackIntt.data = tmpSlotsData;
					var tmpData=[];
					for(var i in stackIntt.data){
						var itm ={
							slotName:
									'<label style="font-size:13px;">Name: </label>'+
									'<button class="btn btn-info btn-mapping" style="float:right;"'+
										'onClick="myLEX.showSlotTypeValue(\''+stackIntt.tag+'\', \''+stackIntt.data[i].tag+'\')">'+
										'Values'+
									'</button>'+
									'<span id="val1_'+stackIntt.data[i].tag+'">'+stackIntt.data[i].name+'</span>'+
									'<br/>'+
									'<label style="font-size:13px;">Type: </label>'+
									'<span id="val2_'+stackIntt.data[i].tag+'">'+stackIntt.data[i].type+'</span>',
							slotKR: '<div id="searchDIV_'+stackIntt.data[i].tag+'"></div>'
						};
						tmpData.push(itm);
					}
					$("#slotList"+stackIntt.tag).bootstrapTable( 'resetView' , {height: '300'} );
					$("#slotList"+stackIntt.tag+" th[data-field=slotName] > div.th-inner").html("Intent: "+stackIntt.name);
					$(".fixed-table-body").css('height', '85%')
					$("#slotList"+stackIntt.tag).bootstrapTable("load", tmpData);
					for(var i in stackIntt.data){
						tmpThis.createSearchKRsItem('searchDIV_'+stackIntt.data[i].tag, stackIntt.data[i].tag, stackIntt.data[i].name, 'slot', stackIntt.tag);
					}
					tmpThis.callParentSlot(stackIntt.tag);
				}
				tmpThis.getJson(tmpThis.mapId, 'intent', retVal.intents[i].intentName, retVal.intents[i].intentVersion, stackBot[i], calBackIntent);
			}
			$("#"+tabID).tabs();
		}
		this.getJson(this.mapId, 'bot', this.BotName, this.BotAlias, this.botData, calBackBot);
	}
	//------------------------------------------------------------
	showSlotTypeValue(tagI, tagS){
		$("#slotTypeList"+tagI).bootstrapTable("load", []);
		$("#slotTypeList"+tagI+" th[data-field=slotTypeName] > div.th-inner").html("");
		for(var i in this.botData){
			var tmpA = this.botData[i];
			if(tmpA.tag==tagI){
				for(var j in tmpA.data){
					var tmpB = tmpA.data[j];
					if(tmpB.tag==tagS){
						var tmpData = [];
						for(var k in tmpB.data){
							var tmpC = tmpB.data[k];
							var itm ={
								slotTypeName: '<span id="val1_'+tmpC.tag+'">'+tmpC.name+'</span><span id="val2_'+tmpC.tag+'"></span>',
								slotTypeKR: '<div id="searchDIV_'+tmpC.tag+'"></div>'
							};
							tmpData.push(itm);
						}
						$("#slotTypeList"+tmpA.tag).bootstrapTable( 'resetView' , {height: '200'} );
						$("#slotTypeList"+tmpA.tag+" th[data-field=slotTypeName] > div.th-inner").html("Slot: "+tmpB.name);
//						$(".fixed-table-body").css('height', '85%')
						$("#slotTypeList"+tmpA.tag).bootstrapTable("load", tmpData);
						for(var h in tmpB.data){ 
							this.createSearchKRsItem('searchDIV_'+tmpB.data[h].tag, tmpB.data[h].tag, tmpB.data[h].name, "value", tmpB.tag); 
							if($("#krVAL_"+tmpB.tag).data('myid')!='0'){
								$('#krVAL_'+tmpB.data[h].tag).prop('disabled', false);
								$('#krVAL_'+tmpB.data[h].tag).data('parentid', $("#krVAL_"+tmpB.tag).data('myid'));
								$("#searchHolder_"+tmpB.data[h].tag+" button").prop('disabled', false);
							}
						}
						$('html, body').animate({ scrollTop: $("#slotTypeList"+tmpA.tag).offset().top }, 2110);
					}
				}
			}
		}
	}
	//------------------------------------------------------------
	createSearchKRsItem(parentID, itemID, val1, type, classTag){
		var tmpThis = this;
		$("#"+parentID).html('');
		var div =''+
				'<div style="width:100%" id="searchHolder_'+itemID+'">'+
					'<div style="width:82%;margin-right:1.5%;display:inline-block;">'+
						'<div class="input-group m-b-1">'+
							'<span class="input-group-addon">Knowledge Record</span>'+
							'<input class="form-control searchKR '+classTag+'" id="krVAL_'+itemID+'" '+
									'data-itemid="0" '+
									'data-parentid="0" '+
									'data-myid="0" '+
									'data-type="'+type+'" '+
									'value="" placeholder="search knowledge records ....." maxlength="100" disabled />'+
						'</div>'+
					'</div>'+
					'<div style="width:15%;display:inline-block;">'+
						'<button class="btn btn-info btn-mapping '+classTag+'" style="width:100%;" id="addKR_'+itemID+'" onclick="callAddNewKR(\''+itemID+'\',\''+val1+'\')" disabled >'+
							'Add New KR'+
						'</button>'+
						'<button class="btn btn-info btn-mapping '+classTag+'" style="width:100%;margin-top:5px" id="rateVal_'+itemID+'" onclick="callRateValues(\'krVAL_'+itemID+'\')" disabled >'+
							'Rate'+
						'</button>'+
					'</div>'+
					'<small style="display:block;margin:-10px 0 0 15px">mapped to: <i style="color:green" id="mappedTO'+itemID+'"></i></small>'+
				'<div>';
		$("#"+parentID).html(div);
//		$("#krVAL_"+itemID).on('keypress', function(e){ if(e.keyCode!=13){ $("#"+itemID).data('itemid', 0); } });
//		$("#krVAL_"+itemID).on('keyup', function(){ $("#"+itemID).data('itemid', 0); });
//		$("#krVAL_"+itemID).on('keydown', function(){ $("#"+itemID).data('itemid', 0); });
		$("#krVAL_"+itemID).autocomplete({
			source: function( request, response ) {
				$.ajax( {
					url: apiURL+"/api/dashboard/lex/mapping/searckkr",
					dataType: "json",
					data: { searchItem: request.term },
					success: function( data ){ response( data ); }
				} );
			},
			minLength: 2,
			select: function( event, ui ){
				tmpThis.mappedTo(itemID, ui.item.id);
			}
		});
		this.setData(itemID);
	}
	//------------------------------------------------------------
	mappedTo(itemID, krID){
		var tmpThis = this;
		var data = {}; 
		data.type     = $("#krVAL_"+itemID).data('type');
		data.ParentID = $("#krVAL_"+itemID).data('parentid');
		data.val1     = $("#val1_"+itemID).text();
		data.val2     = $("#val2_"+itemID).text();
		data.krID     = krID;
		data.tag      = itemID;
		data.userID   = userID;
		if(data.type=="intent"){ data.val3 = $("#sampleUtterance"+itemID).val(); }
		else{ data.val3 = ""; }
		$.ajax({
			url: apiURL+'/api/dashboard/lex/mapping/mapped',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ $("#editItem #saveItem").prop('disabled', true); },
			success: function(res){
				if(res.result == 0){
					$("#mappedTO"+itemID).html( $("#krVAL_"+itemID).val() );
					$("#krVAL_"+itemID).data('itemid', krID);
					$("#krVAL_"+itemID).data('myid', res.id);
					$("input.searchKR."+itemID).data('parentid', res.id);
					$("input.searchKR."+itemID).prop('disabled', false);
					$("button.btn-mapping."+itemID).prop('disabled', false);
					if(res.added==0){//update
						tmpThis.updateData(itemID, krID, $("#krVAL_"+itemID).val());
					}else{//Insert
						tmpThis.mappedData.push({ id:res.id, kr_id:krID, last: "", mappedTo:$("#krVAL_"+itemID).val(), parent_id:data.ParentID, tag:itemID, type:data.type, user_id:userID, val1:data.val1, val2:data.val2 });
					}
				}else{ showError(res.msg); }
			},
			error: function(e){ showError('Server error'); }
		});
	}
	//------------------------------------------------------------
	getData(){
		var tmpThis = this;
		$.ajax({
			url: apiURL+'/api/dashboard/lex/mapping/getmapped',
			type: 'post',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify({id:botMapID}),
			beforeSend: function(){ $("#editItem #saveItem").prop('disabled', true); },
			success: function(res){
				if(res.result == 0){ 
					tmpThis.mappedData  = res.data; 
					tmpThis.mapId       = res.mapId;
					tmpThis.getKeys();
				}
				else{ showError(res.msg); }
			},
			error: function(e){
				if(e.status==404){ showError('404: '+e.statusText); }
				else{ showError(e.status+': '+e.responseJSON.message); }
				$("button.btn.Unpublished, button.btn.Published").prop('disabled', true);
			}
		});
	}
	setData(tag){
		for(var i in this.mappedData){ 
			if(this.mappedData[i].tag==tag){
				var tmp = this.mappedData[i];
				$("#krVAL_"+tmp.tag).val(tmp.mappedTo);
				$("#mappedTO"+tmp.tag).html( tmp.mappedTo );
				$("#krVAL_"+tmp.tag        ).data('itemid', tmp.kr_id);
				$("#krVAL_"+tmp.tag        ).data('myid', tmp.id);
				$("#krVAL_"+tmp.tag        ).data('parentid', tmp.parent_id);
				$("input.searchKR."+tmp.tag).data('parentid', tmp.id);
				$("#krVAL_"+tmp.tag            ).prop('disabled', false);
				$("#addKR_"+tmp.tag            ).prop('disabled', false);
				$("#rateVal_"+tmp.tag          ).prop('disabled', false);
				$("input.searchKR."+tmp.tag    ).prop('disabled', false);
				$("button.btn-mapping."+tmp.tag).prop('disabled', false);
			}
		}
	}
	updateData(tag, krID, mappedTO){ 
		for(var i in this.mappedData){ 
			if(this.mappedData[i].tag==tag){ 
				this.mappedData[i].kr_id = krID;
				this.mappedData[i].mappedTo = mappedTO; 
			} 
		} 
	}
	callParentSlot(tag){
		var parent_id = $("#krVAL_"+tag).data('myid');
		if(parent_id!=0){
			$("input.searchKR."+tag).data('parentid', parent_id);
			$("input.searchKR."+tag).prop('disabled', false);
			$("button.btn-mapping."+tag).prop('disabled', false);
		}
	}
	//------------------------------------------------------------
/*
	getOrganizations() {
		$.get(apiURL + '/api/dashboard/lex/setting/organization/'+orgID , (res) => {
			if(orgID==0){
				var tmp = "<option value='' data-persona=''>Select organization</option>";
				$("#ownerId"       ).append(tmp);
				$("#organizationID").append(tmp);
			}
			if(res.result==1){
				showError(res.msg);
				return;
			}
			for(var i in res.data ){ 
				tmp = "<option value='"+res.data[i].org_id+"' data-persona='"+res.data[i].personalityId+"'>"+res.data[i].organizationShortName+"</option>";
				$("#ownerId"       ).append(tmp);
				$("#organizationID").append(tmp);
				myLEX.organizations.push({id:res.data[i].organizationId, caption:res.data[i].organizationShortName});
			}
			if(res.data.length==0){ 
				$("#personalityID").prop('disabled', true);
				$("#continueBTN").hide();
				showError('Setting not defined for this Organization'); 
			}
		});
	}
*/
	//------------------------------------------------------------
/*
	getPersona(inOrgID, personaID) {
		$("#personalityID option").remove();
		if(inOrgID==''){ return; }
		$.get(apiURL + '/api/dashboard/personality/zeroPersonality/'+inOrgID+'/-1/personalityName/asc', (res) => {
			for(var i in res.data){ 
				$("#personalityID").append("<option value='"+res.data[i]['personalityId']+"'>"+res.data[i]['personalityName']+"</option>");
			}
			$("#personalityID").val(personaID);
		});
	}
*/
	//----------------------------------------------------
	//------------------------------------------------------------
	getOrganizations(id){
		$.get(
			apiURL+'/api/dashboard/organization/all/'+orgID,
			function(retVal){
				for(var i in retVal.data){ 
					$("#addKRItem #ownerId")
						.append('<option value="'+retVal.data[i].organizationId+'">'+retVal.data[i].organizationShortName+'</option>'); 
				}
				$("#addKRItem #ownerId").val(id);
			}
		);
	}
	//------------------------------------------------------------
	getTermOwners(id){
		$.get(
//			apiURL+'/api/dashboard/term/termowners/'+orgID,
			apiURL+'/api/dashboard/organization/all/'+orgID,
			function(retVal){
				$("#termOwnersList option").remove();
				$("#termOwnersList").append('<option value="-1">Owner All</option>');
				for(var i in retVal.data){ 
//					$("#searchBox #termOwnersList").append('<option value="'+retVal.data[i].id+'">'+retVal.data[i].text+'</option>'); 
					$("#searchBox #termOwnersList")
						.append('<option value="'+retVal.data[i].organizationId+'">'+retVal.data[i].organizationShortName+'</option>'); 
				}
				$("#searchBox #termOwnersList").val(id);
			}
		);
	}
	//------------------------------------------------------------
	searchTermByName(objID, val){
		$("#findTermsBTN").hide();
		$("#wait4terms").show();
		val = val.trim();
		if(val==''){ return; }
		var obj = $("select#"+objID);
		$("select#"+objID+" option").remove();
		$("#termsList option").remove();
		$.post(apiURL+'/api/dashboard/term/gettermsaroundme/'+orgID+'/'+val+'/'+termPerPage+'/ownerId/'+$('#termOwnersList').val(), (dataIn) => {
			$("#termsList option").remove();
			$("select#"+objID+" option").remove();
			for(var i=0; i<dataIn.data.length; i++){
				var tmp = "<option data-prev='0' onDblClick='selectTermItem()' data-next='0' value='"+dataIn.data[i].termId+"' >"+dataIn.data[i].termName+"</option>";
				$(obj).append(tmp);
				$("#termsList").append(tmp);
			}
		})
		.always(function() {
			$("#wait4terms").hide();
			$("#findTermsBTN").show();
		});
		$("#termsList").focus();
		$(obj).focus();
	}
	//------------------------------------------------------------
	getTerms(id, obj, direction){
		$("#findTermsBTN").hide();
		$("#wait4terms").show();
		var owner = $('#termOwnersList').val();
		$(obj).find("option").remove();
		$("#termsList option").remove();
		if(id==''){ id=0; }
		$.post(apiURL+'/api/dashboard/term/gettermsaroundme/'+orgID+'/'+id+'/'+termPerPage+'/'+direction+'/ownerId/'+owner, (dataIn) => {
			for(var i=0; i<dataIn.data.length; i++){ 
				var tmp = "<option data-prev='0' onDblClick='selectTermItem()' data-next='0' value='"+dataIn.data[i].termId+"' >"+dataIn.data[i].termName+"</option>";
				if(direction=='n'){ 
					$(obj).append(tmp); 
					$("#termsList").append(tmp); 
				}else{ 
					$(obj).prepend(tmp); 
					$("#termsList").prepend(tmp); 
				}
			}
			if( id!=0 ){ $(obj).val(id).change(); }
			else{
				let lbl = $(obj).parent().parent().find("label").text();
				$(obj).prepend("<option data-prev='0' value='' selected='selected' >Select "+lbl+"</option>").change();
			}
		})
		.always(function() {
			$("#wait4terms").hide();
			$("#findTermsBTN").show();
		});
	}	
	//------------------------------------------------------------
	getAllRelationTypes(){
		$.get(apiURL+'/api/dashboard/relation_type/all/'+orgID+'/relationTypeName/asc', (obj) => {
			var relationTypeOptions = [];
			for(var i=0; i< obj.data.length; i++){ 
				relationTypeOptions.push("<option value='"+obj.data[i].relationTypeId+"'>"+obj.data[i].relationTypeName+"</option>");
			}
			this.relationTypes = relationTypeOptions;
			$("select#relationTypeId").append(this.relationTypes);
			$("select#relationTypeId").prepend("<option value='' selected='selected' >Select Relation Type</option>");
		});
	}
	//------------------------------------------------------------
	saveNewKR(itemID, data){
		var tmpThis = this;
		$.ajax({
			url: apiURL+'/api/dashboard/relation/new/0',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ $("#editItem #saveItem").prop('disabled', true); },
			success: function(res){
				if(res.result == 0){
					showSuccess('Item saved.');
					$("#krVAL_"+itemID).val(res.knowledgeRecordName);
					tmpThis.mappedTo(itemID, res.relationId);
					$("#addKRItem").modal('hide');
				}else{ showError(res.msg); }
			},
			error: function(e){ showError('Server error'); }
		});
	}
	//------------------------------------------------------------
	editScalarValue( obj, scalarValue ){
		var data = {};
		data.ownerId     = orgID;
		data.scalarValue = scalarValue;
		data.userID      = userID;
		var ID = $(obj).data('id');
		$.ajax({
			method  :'put',
			url     : apiURL+'/api/dashboard/personality_relation_value/scalarvalue/'+ID,
			data    : data,
			dataType: 'json',
			success: 
				function( response ){
					if(response.result==0){ 
					}else{
						$(obj).slider('value', $(obj).attr('data'));
						$(obj).find(".ui-slider-handle").text( $(obj).attr('data') );
						showError("Error:["+response.msg+"]");
					}
				},
			error:
				function(xhr, textStatus, errorThrown ){ 
					$(obj).slider('value', $(obj).attr('data'));
					$(obj).find(".ui-slider-handle").text( $(obj).attr('data') );
					showError("Error:["+xhr.status+"] "+errorThrown); 
				}
		});
	}
	//------------------------------------------------------------
	addKnowledgeRecordValue(flg){
		var data = {};
		data.ownerId               = $("#ownerKRV"                     ).val().trim();//defaultOrgID;
		data.ownership             = $("#ownershipKRV"                 ).val().trim();
		data.personalityRelationId = $("input#personalityRelationIdKRV").val().trim();
		data.personRelationTermId  = $("input#valueKRV"                ).val().trim();
		data.scalarValue           = $("input#scalerValueKRV"          ).val().trim();
		data.userID = userID;
		if(data.personRelationTermId==0){ showError("Please select Value"); return; }
		$.ajax({
			url     : apiURL+'/api/dashboard/personality_relation_value/create',
			data    : data,
			method  : 'put',
			dataType: 'json',
			success: 
				function( response ){
					if(response.result==0){ 
						$("#rateValueList").bootstrapTable('refresh'); 
						$("#valueSelect").bootstrapTable('refresh'); 
						
						$("#slider-0").find(".ui-slider-handle").text( 0 );
						$("#slider-0").slider( "option", "value", 0 );
						$("#valueKRV"      ).val(0);
						$("#scalerValueKRV").val(0);
						$("span.valueKRV"  ).text('');

						if(flg==0){ $('#addKnowledgeRecordValue').modal('hide'); }
					}else{ myClass.showError("Error: "+response.msg); }
				},
			error:
				function(xhr, textStatus, errorThrown ){ myClass.showError("Error:["+xhr.status+"] "+errorThrown); }
		});
	}
	//------------------------------------------------------------
	//------------------------------------------------------------
	//------------------------------------------------------------
	setJson(mapId, type, name, version, json){
		$.ajax({
			url: apiURL+"/api/dashboard/lex/mapping/setjson/"+mapId+"/"+type+"/"+name+"/"+version,
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify({json: json}),
			beforeSend: function(){ },
			success: function(retVal){
			},
			error: function(e){
				if(e.status==404){ showError('404: '+e.statusText); }
				else{ showError(e.status+': '+e.responseJSON.message); }
			}
		});
	}
	//------------------------------------------------------------
	getJson(mapId, type, name, version, inData, calBackFunc){
		var tmpThis = this;
		var data = {};
		data.mapId = mapId;
		data.type  = type;
		data.name  = name;
		data.version = version;
		$.ajax({
			url: apiURL+"/api/dashboard/lex/mapping/getjson",
			type: 'get',
			headers: {
//				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: data,
			beforeSend: function(){ },
			success: function(retVal){
				if(retVal.result==0){ 
					if(retVal.data==null){ 
						switch(type){
							case "bot":{ tmpThis.lexBase.callBot(tmpThis.BotName, tmpThis.BotAlias, inData, calBackFunc); return; }
							case "intent":{ tmpThis.lexBase.callIntent(name, version, inData, calBackFunc); return; }
							case "slot":{ tmpThis.lexBase.callSlotType(name, version, inData, calBackFunc); return; }
						}
					}
					else{ calBackFunc(JSON.parse(retVal.data), inData); }
				}
				else{ showError(retVal.msg); }
			},
			error: function(e){
				if(e.status==404){ showError('404: '+e.statusText); }
				else{ showError(e.status+': '+e.responseJSON.message); }
			}
		});
	}
	//------------------------------------------------------------
}

if($("#LEX_MappingNewEdit").length != 0){
	myLEX = new LEXMappingNewEdit(tabID, BotName, BotAlias);
}
