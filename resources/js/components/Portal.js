import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'
//----------------------------------------------------------------
class Portal extends DataTable{
	//------------------------------------------------------------
	constructor(data){
		super(data);
		this.pageSort = 'name';
		let that = this;		

		//var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'Feedback'           , class: 'feedback-item' , 'data-onlyowner': 1 });
		var icon1 = $('<a></a>').attr({ href: '#', 'data-desc': 'Live Agent Settings', class: 'liveagent-item', 'data-onlyowner': 1 });
		this.actionIcons = this.actionIcons.concat([icon1]);

		$('body').on('click', '.feedback-item', (e) => { this.feedbackDialog(e) });
		$('body').on('click', '#saveFeedback' , (e) => { this.saveFeedback() });
		$('body').on('click', '.liveagent-item', (e) => { this.liveAgentLink(e) });
		

		$("body").on("change", "#organization_id", function(){
			that.getPersonality();
			that.setModelGenAIURL_Items($("#organization_id").val());
		});
		if(orgID!=0){
			var icon1 = $('<a></a>').attr({
				href: '#',
				style: "color:#2196f3;",
				class: 'edit-item',
				'data-desc': 'Edit',
				'data-onlyowner': 0
			});
	//		this.actionIcons = [deleteIcon, editIcon];
			this.actionIcons = this.actionIcons.concat([icon1]);
		}
		this.getPortalNumbers();	
		$("body").on("change", "#KaaS3PB", function(){ 
			if($(this).prop('checked')){
				$('#OnOff').prop('checked', true).change();
				$('#OnOff').bootstrapToggle('disable');
			}else{ $('#OnOff').bootstrapToggle('enable'); }
		});
		
		this.userID = userId;
		
		
		$("body").on("change", "#portal_number", function(){ 
			$("#portalCodeMe").val($(this).val()+$("#codeMe").val().trim());
		});
		$("body").on("click", "#copyToClipboard", function(){ 
			that.copyToClipboard($("#integrationCodeValue").val().trim());
		});
		$("body").on("click", "#closeIntegrationCodeModal", function(){ 
			$("#integrationCodeModal").remove();
		});
		
		$("body").on("click", "#integrationCodeBTN", function(){
			let organization_id = $("#organization_id").val();
			let portalCodeMe    = $("#portalCodeMe").val().trim();
			$("body").append(
				$("<div>")
					.attr({
						id:'integrationCodeModal',
						style:"position:fixed;z-index:1001;background:rgba(0,0,0,0.6);top:0;bottom:0;right:0;left:0;margin:auto;"
					})
					.append(
						$("<div>")
						.attr({
							style:"position:absolute;"+
								"margin:auto;"+
								"top:0;bottom:0;right:0;left:0;"+
								"width:480px;height:350px;"+
								"background:#fefefe;"+
								"padding:15px;"+
								"border-radius:15px;"
						})
						.append(
							$('<div>')
								.append("<label>Webpage Integration Code</label>")
						)
						.append(
							$("<textarea>").
								attr({
									id:"integrationCodeValue",
									disabled: true,
									style:"width:100%;max-width:100%;min-width:100%;"+
										"height:calc(100% - 70px);max-height:calc(100% - 70px);min-height:calc(100% - 70px);"+
										"border-color: #ddd;"
								})
								.text(
									'<link href="'+webPageIntegrationURL+'/stylesheets/Kama.css" rel="stylesheet" type="text/css" />'+
									"\n"+
									'<script src="'+webPageIntegrationURL+'/javascript/jquery.js"></script>'+
									"\n"+
									'<script src="'+webPageIntegrationURL+'/javascript/chat_api_general.js?'+
									'orgid='+organization_id+'&'+
									'portalcode='+portalCodeMe+'"></script>'
								)
						)
						.append(
							$('<div>')
								.attr({style:"margin-top:15px"})
								.append(
									$("<button>Close</button>")
										.attr({
											id:"closeIntegrationCodeModal",
											class:'btn btn-danger',
											style: "width:40%"
										})
								)
								.append(
									$("<button>Copy to clipboard</button>")
										.attr({
											id:'copyToClipboard',
											class:'btn btn-success',
											style: "width:40%; float:right;"
										})
								)
						)
					)
			);

		});
		
		
		$(".col-unknownPersonalityId").ready(function(){
			let dev =$("<div>")
						.attr({
							class: "col-codeIntegration form-group",
							style: "vertical-align:top",
						})
						.append(
							$("<div>")
								.attr({
									class: "col-portalCode form-group half left",
									style: "vertical-align:top;",
								})
								.append( $("<label>Portal Code</label>") )
								.append(
									$('<div>')
										.attr({style:"display:inline-block; width:calc(100% - 90px); margin-left:9.5px"})
										.append(
											$('<input>')
												.attr({
													id:'portalCodeMe', name:'portalCodeMe', disabled:"true", class:'form-control'
												})
										)
										.append(
											$('<input>')
												.attr({
													id:'codeMe', name:'codeMe', hidden:"true"
												})
										)
									)
						)
						.append(
							$("<div>")
								.attr({
									class: "col-webpageIntegration form-group half right",
									style: "vertical-align:top;",
								})
								.append(
									$('<div>')
										.append(
											$('<button>Webpage Integration Code</button>')
												.attr({
													id:'integrationCodeBTN',
													class:'btn btn-primary',
													style:"width:100%",
//													'data-toggle':"modal",
//													'data-target':"#webpageIntegrationModal"
												})
										)
									)
						)
/*
						.append(
							$("<div>")
								.attr({ class: "col-ntfctn_mssg_cstmztn form-group", style: "vertical-align:top;" })
								.append( $("<label>Notification Message Customization</label>") )
								.append(
									$('<div>')
										.attr({ style:"display:block; width:100%; margin-left:0px" })
										.append(
											$('<input>')
												.attr({ id:'ntfctn_mssg_cstmztn', name:'ntfctn_mssg_cstmztn', class:'form-control' })
										)
									)
						)
						.append(
							$("<div>")
								.attr({ class: "col-rqst_mssg_cstmztn form-group", style: "vertical-align:top;" })
								.append( $("<label>Request Message Customization</label>") )
								.append(
									$('<div>')
										.attr({ style:"display:block; width:100%; margin-left:0px" })
										.append(
											$('<input>')
												.attr({ id:'rqst_mssg_cstmztn', name:'rqst_mssg_cstmztn', class:'form-control' })
										)
									)
						)
*/
			;
			$(".col-unknownPersonalityId").after($(dev));
		});
		$("#ntfctn_mssg_cstmztn").ready(function(){ $("#ntfctn_mssg_cstmztn").attr('maxlength', 1000); });
		$("#rqst_mssg_cstmztn"  ).ready(function(){ $("#rqst_mssg_cstmztn"  ).attr('maxlength', 1000); });
		

		$("body").on('change', '#feedback', function(){
			if($(this).prop('checked')==true){
				$("#thumbsup, #comment").bootstrapToggle("enable");
			}
			else{
				$("#thumbsup").prop("checked", false).change();
				$("#comment" ).prop("checked", false).change();
				$("#thumbsup, #comment").bootstrapToggle("disable");
			}
		});
		this.createMultiModelGenAiIsBusy = false;
		this.createCollectionBusy = false;
		this.lastPortalID = 0;
		this.collectionIsloaded = false;
		$('body').on('change', '#multi_model_gen_AI', (e) => {
			let toggle_btn_genAI = $('input[name="multi_model_gen_AI"]').parent();
			$('.col-multi_model_gen_AI .inputs').hide();
			$('.col-multi_model_gen_AI button.btnShowModal').hide();
			if(!toggle_btn_genAI.hasClass('off')){
				$('.col-multi_model_gen_AI .inputs').show();
				$('.col-multi_model_gen_AI button.btnShowModal').show();
			}
			if(that.collectionIsloaded){ that.callCollectionSetting('change', $("#multi_model_gen_AI").prop('checked')); }
			return;
		});
		
		$('body').on('click', 'button.checkboxModelGenAI', function(){
			let checked = $(this).find("input.checkboxModelGenAI");
			$("button.checkboxModelGenAI").removeClass("btn-info").removeClass("active").addClass('btn-default');
			$("button.checkboxModelGenAI>i").removeClass("fa-check-circle-o").addClass("fa-circle-o");
			if($(checked).prop('checked')){
				$(this).removeClass("btn-info").removeClass("active").addClass('btn-default');
				$(this).find("i").removeClass("fa-check-circle-o").addClass("fa-circle-o");
				$(checked).prop('checked', false);
			}else{
				$(this).removeClass("btn-default").addClass('btn-info active');
				$(this).find("i").removeClass("fa-circle-o").addClass("fa-check-circle-o");
				$(checked).prop('checked', true);
			}
		});
		
		$('body').on('click', 'button.collectionBtnItems', function(){
			let checked = $(this).find("input.collectionItems");
			let type    = $(checked).attr('type');

			if(type=='radio'){
				$("button.collectionBtnItems").removeClass("btn-info").removeClass("active").addClass('btn-default');
				$("button.collectionBtnItems>i").removeClass("fa-check-circle-o").addClass("fa-circle-o");
			}
			if(type=='checkbox'){}

			if($(checked).prop('checked')){
				$(this).removeClass("btn-info").removeClass("active").addClass('btn-default');
				$(this).find("i").removeClass("fa-check-circle-o").addClass("fa-circle-o");
				$(checked).prop('checked', false);
			}else{
				$(this).removeClass("btn-default").addClass('btn-info active');
				$(this).find("i").removeClass("fa-circle-o").addClass("fa-check-circle-o");
				$(checked).prop('checked', true);
			}
		});
	}
	//------------------------------------------------------------
	rowActions(value, row, index, field){
		let icons = this.actionIcons;
		let tmpICN = [];
		//------------------------------------------------
		for(let i in icons){
			if( icons[i].attr('class')=='feedback-item' ){
				if(row.orgFeedback==1){ tmpICN.push(icons[i]);  }
			}
			else{ tmpICN.push(icons[i]);  }
		}
		this.actionIcons = tmpICN;
		
		return super.rowActions(value, row, index, field);
	}
	//------------------------------------------------------------
	copyToClipboard(value) {
		try{
			let $temp = $("<input>");
			$("body").append($temp);
			$temp.val(value).select();
			document.execCommand("copy");
			$temp.remove();
			showSuccess("Copied to clipboard");
		}catch(ex){ showError("Error: "+ex); }
	}
	//------------------------------------------------------------
	feedbackDialog(e){
		$.get(this.apiURLBase+"/api/dashboard/portal/feedback/"+$(e.currentTarget).data('itemid'), function(res){
			$("#myFeedback").modal({backdrop:'static'});
			
			$("#portalName").val(res.data.name);
			$("#portalID"  ).val(res.data.id  );
			$("#feedback").prop("checked", res.data.feedback).change();
			$("#thumbsup").prop("checked", res.data.thumbsup).change();
			$("#comment" ).prop("checked", res.data.comment ).change();
		})
	}
	saveFeedback(){
		let data = {
			id      : $("#portalID"  ).val().trim(),
			feedback: (($("#feedback").prop("checked")) ?1 :0),
			thumbsup: (($("#thumbsup").prop("checked")) ?1 :0),
			comment : (($("#comment" ).prop("checked")) ?1 :0)
		};

		$.ajax({
			url: this.apiURLBase+"/api/dashboard/portal/feedback/",
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ $("#myFeedback .btn").prop('disabled', true ); },
			complete  : function(){ $("#myFeedback .btn").prop('disabled', false); },
			success: function(res){
				if(res.result == 0){
					showSuccess('Item saved.');
					$("#myFeedback").modal('hide');
				}else{
					showError(res.msg);
				}
			},
			error: function(e){
				showError('Server error');
			}
		});
	}
	//------------------------------------------------------------
	liveAgentLink(e){ window.location.href=this.apiURLBase + '/panel/live_agent/mapping/p/'+$(e.currentTarget).data('itemid'); }
	//------------------------------------------------------------
	get personalityURL() { return this.apiURLBase + '/api/dashboard/portal/personality/' + $("#organization_id").val().trim() }
	//------------------------------------------------------------
	get isKassActive() {return this.apiURLBase+'/api/dashboard/organization/isKaasActive/' + $("#organization_id").val().trim()}
	//------------------------------------------------------------
	getPersonality(){
		$("#unknownPersonalityId option").remove();
		$("#unknownPersonalityId").prepend("<option value=''>Select ...</option>");
		if($("#organization_id").val().trim()==""){ 
			$("#unknownPersonalityId").val("").change();
			return; 
		}
		$.get(this.personalityURL, (res) => {
			for(let i in res){
				$("#unknownPersonalityId").append("<option value='"+res[i].id+"'>"+res[i].name+"</option>");
			}
//			if(this.baseItem.unknownPersonalityId==null || this.baseItem.unknownPersonalityId=='')
			
/*
			if($("#insertItem").length!=0 || this.baseItem.unknownPersonalityId==null || this.baseItem.unknownPersonalityId=='')
				{ this.baseItem.unknownPersonalityId = $("#organization_id option:selected").data("defultpersona"); }
*/
			let doChange = false;
			let upID = this.baseItem.unknownPersonalityId
			$("#unknownPersonalityId").find('option').each(function(){
				if( $(this).attr('value').trim()==upID ){ doChange=true; }
			});
			if(doChange){ $("#unknownPersonalityId").val(this.baseItem.unknownPersonalityId).change(); }
//			else{ $("#unknownPersonalityId").val($("#organization_id option:selected").data("defultpersona")).change(); }
		});
		$.get(this.isKassActive,(res)=>{
			var data=res.data;
			if(data==null){
				//$('#KaaS3PB').parent().parent().parent().hide();
				$("#portal_number option[value='a']").remove();
				
				//$('.col-hasLiveAgent').hide();
				//$('.col-MoD, .col-ntfctn_mssg_cstmztn, .col-rqst_mssg_cstmztn').hide();
				$('#KaaS3PB, #MoD_, #hasLiveAgent').bootstrapToggle('disable');
				$('#ntfctn_mssg_cstmztn, #rqst_mssg_cstmztn').prop('disabled', true);
			}else{
				if(data.KaaS3PB==1){	
					//$('#KaaS3PB').parent().parent().parent().show();
					if(!$("#portal_number option[value='a']").length > 0){
						$("#portal_number").append("<option value='a'>KasS</option>");													
					}
					$('#KaaS3PB').bootstrapToggle('enable');
				}else{
					//$('#KaaS3PB').parent().parent().parent().hide();
					$('#KaaS3PB').bootstrapToggle('disable');
					$("#portal_number option[value='a']").remove();
				}
				$('#hasLiveAgent').bootstrapToggle('off');
				if(data.hasLiveAgent==1){
					//$('.col-hasLiveAgent').show();
					$('#hasLiveAgent').bootstrapToggle('enable');
					if(this.baseItem.hasLiveAgent==1){ $('#hasLiveAgent').bootstrapToggle('on'); }
				}else{
					//$('.col-hasLiveAgent').hide();
					$('#hasLiveAgent').bootstrapToggle('disable');
					$('#hasLiveAgent').bootstrapToggle('off');
				}
				if(data.MessageOfTheDay==1){	
					//$('.col-MoD_, .col-ntfctn_mssg_cstmztn, .col-rqst_mssg_cstmztn').show();
					$('#MoD_').bootstrapToggle('enable');
					$('#ntfctn_mssg_cstmztn, #rqst_mssg_cstmztn').prop('disabled', false);
				}else{
					//$('.col-MoD_, .col-ntfctn_mssg_cstmztn, .col-rqst_mssg_cstmztn').hide();
					$('#MoD_').bootstrapToggle('disable');
					$('#ntfctn_mssg_cstmztn, #rqst_mssg_cstmztn').prop('disabled', true);
				}

			}		
		});
	}
	//------------------------------------------------------------
	createSelectOptions(arr, valKey, labelKey) {
		var options = [];
		for(var i in arr){
			var value = arr[i][valKey];
			var label = arr[i][labelKey];
			var defultPersona = arr[i]['defultPersona'];
			options.push("<option value='"+value+"' data-defultpersona='"+defultPersona+"'>"+label+"</option>");
		}
		return options;
	}

	getOrganizations() {
		$.get(this.organizationURL, (res) => {
			this.organizations = this.createSelectOptions(res.data, 'organizationId', 'organizationShortName');
			this.organization_id = orgID;
			$("#organization_id").append(this.organizations);
			$("#organization_id").prepend("<option value='' data-defultpersona=''>Select ...</option>");
			// if(res.data!=null)
			// {
			// 	var i=0;
			// 	while(kaaSColumnHidden && i < res.data.length)
			// 	{				
			// 		if(res.data[i].KaaS3PB==1){
			// 			kaaSColumnHidden=false;
			// 			break;
			// 		}
			// 		i++;
			// 	}
			// }
		});
	}
	//------------------------------------------------------------
 	get Portals() { return this.apiURLBase + '/api/dashboard/portal/getPortalNumbers/' }
	//------------------------------------------------------------
	getPortalNumbers(){		
		$.get(this.Portals, (result) => {
			let item = result.data;
			for(let i in item){
				$("#portal_number").append("<option value='"+item[i].number+"'>"+item[i].caption+"</option>");		
			}
		});
	}
	//------------------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'organization_id':
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group",
						style: "vertical-align:top",
					})
					.append( $("<label>"+label+"</label>") )
					.append(
						$("<div>").append(
							$("<select>").attr({
								id: col,
								name: col,
								class: 'form-control'
							})
						)
					);
				break;
			case 'unknownPersonalityId':
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group half right",
						style: "vertical-align:top",
					})
					.append( 
						$("<label>")
							.text(label)
/*
							.append(
								$("<small>")
									.text("(optional)")
									.attr({style:"font-size:x-small; color:red; margin-left:5px;"})
							)
*/
					)
					.append(
						$("<div>").append(
							$("<select>").attr({
								id: col,
								name: col,
								class: 'form-control'
							}).append("<option value=''>Select ...</option>")
							
						)
					);
				break;
			case 'description':
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group",
						style: "vertical-align:top",
					})
					.append( $("<label>"+label+"</label>") )
					.append(
						$("<div>").append(
							$("<textarea>").attr({
								id: col,
								name: col,
								class: 'form-control',
								style: "width:100%;min-width:100%;max-width:100%;height:120px;min-height:120px;max-height:120px;"
							})
						)
					);
				break;
			case 'MoD_':
			case 'OnOff':
			case 'KaaS3PB':
			case 'feedback':
			case 'hasLiveAgent':
			case 'multi_model_gen_AI':
				{
				input = $('<tr>')
							.attr({class: "portalFlags col-" + col })
							.append( $('<td>').text(label) )
							.append(
								$('<td>').append(
									$('<input>').attr({
										id:col,
										name:col,
										'data-toggle':'toggle',
										'data-width':"100",
										'data-size':"small",
										type:"checkbox"
									})
								)
							)
							.append( $('<td>').text('') );

				break;
			}
			// case 'code':{
			// 	input = $('<div>')
			// 				.attr({class: "col-" + col + " form-group"})
			// 				.append( $('<label>').text(label) )
			// 				.append(
			// 					$('<div>').append(
			// 						$('<input>').attr({ id:col, name:col, maxlength:'5', type:"text", class:'form-control' })
			// 					)
			// 			);
			// 	break;
			// }
			case "portal_number":{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group half left"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>')
								.append(
									$("<select>").attr({
								id: col,
								name: col,
								class: 'form-control'
							}).append("<option value=''>Select ...</option>")				
									)
						);
				break;
			}
			case 'thumbsup':{
/*
				input = $('<div>')
							.attr({class: "col-" + col + " form-group quarter center"})
							.append( $('<label>').text(label) ).attr({style:'font-size: small;'})
							.append(
								$('<div>').append(
									$('<input>').attr({ id:col, name:col, 'data-toggle':'toggle', type:"checkbox" })
								)
						);
*/
				input = '';
				break;
			}
			case 'comment':{
/*
				input = $('<div>')
							.attr({class: "col-" + col + " form-group quarter right"})
							.append( $('<label>').text(label) ).attr({style:'font-size: small;'})
							.append(
								$('<div>').append(
									$('<input>').attr({ id:col, name:col, 'data-toggle':'toggle', type:"checkbox" })
								)
						);
*/
				input = '';
				break;
			}
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//----------------------------------------------------
	cellRenderer(value, row, index, field){
		let retVal = super.cellRenderer(value, row, index, field);
		if( field=='KaaS3PB' || field=='OnOff' || field=='hasLiveAgent' ){
			if(value>0){ return "<span class='glyphicon glyphicon-ok' style='color:green'></span>"; }
			else{ return "<span class='glyphicon glyphicon-minus' style='color: #adadad'></span>"; }
		}
		return retVal;
	}
	//----------------------------------------------------

	confirmHandler(){
		var reg = /^\d+$/;
		var data = {};
		for(var x in this.columns.names){
			if(this.columns.data[x].passData !== false) {
				var name = this.columns.names[x];
				var value = this.editItem[name];
				data[name] = value;
			}
		}
		if(data.name.trim()==""){ showError("Invalid Name."); $("#name").focus(); return false; }
		if(data.organization_id.trim()==""){ showError("Invalid Organization."); $("#organization_id").focus(); return false; }
		// if(data.code.trim()==""){ showError("Invalid Code."); $("#code").focus(); return false; }

		if(data.portal_number.trim()==""){ showError("Invalid Portal Number."); $("#portal_number").focus(); return false; }
		//if(!reg.test(data.portal_number.trim())){ showError("Invalid Portal Number."); $("#portal_number").focus(); return false; }
		return true;
	}
	addConfirmHandler(e){
		//if(this.confirmHandler()){ super.addConfirmHandler(e); }
		let that = this;
		let table = this.table;
		let data = {
			orgID: this.orgID,
			userID: this.userID,
			model_gen_ai_items: []
		};
		//------------------------------------------------
		for(let x in this.columns.names){
			if(this.columns.data[x].passData !== false){
				let name = this.columns.names[x];
				let value = this.editItem[name];
				if(name == 'ownerId' && value == null){ value = '0'; }
				data[name] = value;
			}
		}
		//------------------------------------------------
		if(data.multi_model_gen_AI==1){
			$('input[class="checkboxModelGenAI"]').each(function(){
				if($(this).prop('checked')){ data.model_gen_ai_items.push($(this).val()); }
			});
		}
		if(data.multi_model_gen_AI==1 && data.model_gen_ai_items.length==0){
			showError("Multi-Model Gen AI requires one model or more to be selected.");
			return;
		}
		//------------------------------------------------
		$('input[class="collectionItems"]').each(function(){
			if($(this).prop('checked')){ data.collections.push($(this).val()); }
		});
		if(data.collections.length==0){
			showError("Collections requires one collection or more to be selected.");
			return;
		}
		//------------------------------------------------
		$.ajax({
			url: this.addURL,
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ $("#editItem #insertItem").prop('disabled', true); },
			success: function(res){
				if(res.result == 0){
					$("#editItem").fadeOut(function(){ $("#editItem #insertItem").prop('disabled', false); });
					showSuccess('Added successfully.');
					$(table).bootstrapTable('refresh');
				}else{
					showError(res.msg);
					$("#editItem #insertItem").prop('disabled', false);
				}
			},
			error: function(e){
				showError('Server error');
				$("#editItem #insertItem").prop('disabled', false);
			}
		});
		//------------------------------------------------
	}
	editConfirmHandler(e){
		//if(this.confirmHandler()){ super.editConfirmHandler(e); }
		let that = this;
		let table = this.table;
		let data = {
			orgID: this.orgID,
			userID: this.userID,
			model_gen_ai_items: [],
			collections: []
		};
		//------------------------------------------------
		for(let x in this.columns.names){
			if(this.columns.data[x].passData !== false){
				let name = this.columns.names[x];
				let value = this.editItem[name];
				if(name == 'ownerId' && value == null){ value = '0'; }
				data[name] = value;
			}
		}
		//------------------------------------------------
		if(data.multi_model_gen_AI==1){
			$('input[class="checkboxModelGenAI"]').each(function(){
				if($(this).prop('checked')){ data.model_gen_ai_items.push($(this).val()); }
			});
		}
		if(data.multi_model_gen_AI==1 && data.model_gen_ai_items.length==0){
			showError("Multi-Model Gen AI requires one model or more to be selected.");
			return;
		}
		//------------------------------------------------
		$('input[class="collectionItems"]').each(function(){
			if($(this).prop('checked')){ data.collections.push($(this).val()); }
		});
		if(data.collections.length==0){
			showError("Collections requires one collection or more to be selected.");
			return;
		}
		//------------------------------------------------
		$.ajax({
			url: this.editURL,
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){ $("#editItem #insertItem").prop('disabled', true); },
			success: function(res){
				if(res.result == 0){
					$("#editItem").fadeOut(function(){ $("#editItem #insertItem").prop('disabled', false); });
					showSuccess('Added successfully.');
					$(table).bootstrapTable('refresh');
				}else{
					showError(res.msg);
					$("#editItem #insertItem").prop('disabled', false);
				}
			},
			error: function(e){
				showError('Server error');
				$("#editItem #insertItem").prop('disabled', false);
			}
		});
		//------------------------------------------------
	}
	//------------------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		$('#OnOff, #KaaS3PB, #hasLiveAgent, #MoD_, #feedback, #multi_model_gen_AI').bootstrapToggle('enable');
		$('#OnOff, #KaaS3PB, #hasLiveAgent, #MoD_, #feedback, #multi_model_gen_AI').bootstrapToggle('off');
		// $("#code").val("").change();
		$("#ntfctn_mssg_cstmztn, #rqst_mssg_cstmztn").prop("disabled", false);

		$("#name"               ).val("").change();
		$("#organization_id"    ).val("").change();
		$("#portal_number"      ).val("").change();
		$("#description"        ).val("").change();
		$("#ntfctn_mssg_cstmztn").val("").change();
		$("#rqst_mssg_cstmztn"  ).val("").change();
		if(this.baseItem==null){ this.baseItem={}; for(let i in this.editItem){ this.baseItem[i]=null; } }
		//$("#unknownPersonalityId").parent().parent().hide();
		$('#KaaS3PB').parent().parent().parent().show();
		if(kaaSColumnHidden){
			$('#KaaS3PB').hidden();
		}

		$(".col-codeIntegration").hide();
		$('.col-hasLiveAgent, .col-MoD_, .col-ntfctn_mssg_cstmztn, .col-rqst_mssg_cstmztn').show();
		$('#MoD_').prop('checked', false).change();

		$("#brBfeedback").remove();
		$(".col-feedback").before("<br style='line-height:0; margin-top:-15px' id='brBfeedback'/>");

		$("#feedback").prop("checked", false).change();
		$("#thumbsup").prop("checked", false).change();
		$("#comment" ).prop("checked", false).change();
		$('#feedback').bootstrapToggle('enable');

		$("#multi_model_gen_AI").prop("checked", false).change();
		this.createMultiModelGenAI(0,0);
		this.createCollection(0,0);

		
		if($("#portalFlags").length==0){
			$("<table>")
				.attr({id: "portalFlags"})
				.append("<thead><th>Service</th><th>Status</th><th>Details</th></thead><tbody></tbody>")
			.insertBefore( $("tr.col-MoD_") );
			$("tr.portalFlags").each(function(){
				let tr = $(this);
				$("#portalFlags>tbody").append(tr);
			});

			let tmpDiv = $(".col-ntfctn_mssg_cstmztn");
			$("tr.col-MoD_>td:last-child").append(tmpDiv);
			tmpDiv = $(".col-rqst_mssg_cstmztn");
			$("tr.col-MoD_>td:last-child").append(tmpDiv);

			$("<label>Value Added Services</label>").insertBefore( $("table#portalFlags") );
		}
	}
	//------------------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);
		if(this.baseItem==null){ this.baseItem={}; for(let i in this.editItem){ this.baseItem[i]=this.editItem[i]; } }
		this.baseItem.organization_id = (this.baseItem.organization_id==null) ?0 :this.baseItem.organization_id;

		$("#organization_id").val(this.baseItem.organization_id).change();
		$("#name").val(this.baseItem.name).change();
		// $("#code").val(this.baseItem.code).change();
		$("#portal_number").val(this.baseItem.portal_number).change();
		$("#description").val(this.baseItem.description).change();
		
		$('#OnOff').bootstrapToggle('off');
		if($("#OnOff").val()==1){ $('#OnOff').bootstrapToggle('on'); }

		$('#KaaS3PB').bootstrapToggle('off');
		if($("#KaaS3PB").val()==1){
			$('#KaaS3PB').prop('checked', true).change();
		}

		if(orgID!=0){
			$("#organization_id").prop('disabled', true);
			//$("#name").prop('disabled', true);
			// $("#code").parent().parent().hide();
			$("#portal_number").prop('disabled', true);
			//$("#portal_number").parent().parent().hide();
			//$(".col-portal_number").hide();
			//$("#description").prop('disabled', true);
			$("#unknownPersonalityId").prop('disabled', true);
			//$("#unknownPersonalityId").parent().parent().hide();
			//$(".col-unknownPersonalityId").hide();
		}else{
			//$("#unknownPersonalityId").parent().parent().hide();
		}

		if(kaaSColumnHidden){
			$('#KaaS3PB').hide();
		}
		
		$(".col-codeIntegration").show();
		$("#portalCodeMe").val(this.baseItem.portal_number+this.baseItem.code);
		$("#codeMe").val(this.baseItem.code);

		$('.col-hasLiveAgent').show();
		$('#hasLiveAgent').bootstrapToggle('off');
		if(this.baseItem.hasLiveAgent==1){ $('#hasLiveAgent').bootstrapToggle('on'); }
		
		$('#MoD_').bootstrapToggle('off');
		if($("#MoD_").val()==1){
			$('#MoD_').prop('checked', true).change();
		}
/*
		$("#brBfeedback").remove();
		$(".col-feedback").before("<br style='line-height:0; margin-top:-15px' id='brBfeedback'/>");
*/
		$("#feedback").prop("checked", this.baseItem.feedback).change();
		$("#thumbsup").prop("checked", this.baseItem.thumbsup).change();
		$("#comment" ).prop("checked", this.baseItem.comment ).change();
		if(this.baseItem.orgFeedback==1){
			$('#feedback').bootstrapToggle('enable');
			$("#feedback").prop("checked", this.baseItem.feedback).change();
		}else{
			$("#feedback").prop("checked", false).change();
			$('#feedback').bootstrapToggle('off').bootstrapToggle('disable');
		}
		
		if($("#portalFlags").length==0){
			$("<table>")
				.attr({id: "portalFlags"})
				.append("<thead><th>Service</th><th>Status</th><th>Details</th></thead><tbody></tbody>")
			.insertBefore( $("tr.col-MoD_") );
			$("tr.portalFlags").each(function(){
				let tr = $(this);
				$("#portalFlags>tbody").append(tr);
			});

			let tmpDiv = $(".col-ntfctn_mssg_cstmztn");
			$("tr.col-MoD_>td:last-child").append(tmpDiv);
			tmpDiv = $(".col-rqst_mssg_cstmztn");
			$("tr.col-MoD_>td:last-child").append(tmpDiv);

			$("<label>Value Added Services</label>").insertBefore( $("table#portalFlags") );
		}

		$("#multi_model_gen_AI").prop("checked", false).change();
		if(this.baseItem.multi_model_gen_AI==1){ $("#multi_model_gen_AI").prop("checked", true).change(); }
		this.createMultiModelGenAI(this.editItem['id'],this.editItem['multi_model_gen_AI']);
		this.createCollection(this.editItem['id'],this.editItem['multi_model_gen_AI']);
	}
	//------------------------------------------------------------
	get getURL() {
		return this.apiURL + 
				'/page/' + ((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' +
				this.pageNumber + '/' +
				portalOwnersList;
	}
	//------------------------------------------------------------
	get searchURL() {
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + portalOwnersList + '/' +
			this.columns.searchColumn + '/' + this.search ;
	}
	//------------------------------------------------------------
	get modelGenAIURL() { return this.apiURL+'/get/model_gen_ai/'; }
	createMultiModelGenAI(id, value){
		if(this.createMultiModelGenAiIsBusy){ return; }
		this.createMultiModelGenAiIsBusy = true;
		this.lastPortalID = id;
		let that = this;
		$('.col-multi_model_gen_AI .inputs').remove();
		$.ajax({
			url: LLM_MODELS_URL,
			method:'POST',
			headers:{ apikey: "123" },
			//processData: false,
			//contentType: false,
			data: {userkey:userKey},
			//complete: function(){ that.createMultiModelGenAiIsBusy=false; },
			error: (xhr)=>{ showError("Model Gen AI Error: "+xhr.statusText); },
			success:function(result){
				//---------------------------------------------------------------------
				let inputsDiv = $('<div>').attr({ class:"inputs", style:"margin-top:10px" });
				let openModal = $("<button>active Model Gen AI</button>")
					.attr({
						type:"button",
						class:"btn btn-info btnShowModal",
						style: "margin:0 2.5px",
						'data-toggle':"modal",
						'data-target':"#myModelGenAI"
					});
				//---------------------------------------------------------------------
				let myModelGenAI = $('<div>').attr({ id:"myModelGenAI", class:"modal fade", role:"dialog", style:"z-index:1060 !important" });
				let modalDialog  = $('<div>').attr({class:"modal-dialog", style:"z-index:1061 !important"});
				let modalContent = $('<div>').attr({class:"modal-content", style:"z-index:1062 !important"});
				let modalHeader  = $('<div>')
									.attr({class:"modal-header"})
									.append('<h4 class="modal-title">Model Gen AI</h4>');
				let modalFooter = $('<div>')
									.attr({class:"modal-footer"})
									.append('<button type="button" class="btn btn-default" data-dismiss="modal">Back</button>');

				let modalBody = $('<div>')
									.attr({class:"modal-body"})
									.append('<b>Select One Model:</b>');
				let showMLButton = 0;
				//---------------------------------------------------------------------
				let indx   = 0;
				let hdIndx = 0;
				for(let i in result){
					//if(indx!=0){ $(modalBody).append("<b class='modelTitel "+hdIndx+"' style='display:none; margin-top:20px'>"+i+"</b>"); }
					//else{ $(modalBody).append("<b class='modelTitel "+hdIndx+"' style='display:none'>"+i+"</b>"); }
					$(modalBody).append("<b class='modelTitel "+hdIndx+"' style='display:none; margin-top:20px'>"+i+"</b>");
					for(let j in result[i]){
						let attr = {
							class : "checkboxModelGenAI",
							id    : "modelGenAI"+indx,
							//name  : "modelGenAI_"+indx,
							name  : "modelGenAI_Radio",
							type  : "radio",
							value : result[i][j],
							style : "display:none; width:0;",
							//style : "width:0;",
							"data-hdindx": hdIndx,
							autocomplete : "off",
						};
						let input = $("<input>").attr(attr);
						
						//attr = {disabled:"disabled"};
						attr = {disabled:true};
						attr.class = "btn btn-default checkboxModelGenAI";
						attr.style = "width:48%; text-align:left; margin:5px 1% 5px 1%; display:none";
						//attr.style = "width:48%; text-align:left; margin:5px 1% 5px 1%; ";
						let iItem = "<i class='fa fa-circle-o' style='margin-right:10px'></i>";
						let button = $("<button>")
										.attr(attr)
										.append($(input))
										.append(iItem)
										.append(result[i][j]);
						$(modalBody).append(button);
						
						indx++;
					}
					hdIndx++;
				}
				//---------------------------------------------------------------------
				$(modalContent)
						.append($(modalHeader))
						.append($(modalBody))
						.append($(modalFooter));

				$(modalDialog).append($(modalContent));
				$(myModelGenAI).append($(modalDialog));
				//---------------------------------------------------------------------
				if($(".col-multi_model_gen_AI td:nth-child(3) .btnShowModal").length==0){
					$(".col-multi_model_gen_AI td:nth-child(3)")
						.css("text-align", "center !important")
						.prepend($(openModal));
					if(that.editItem['multi_model_gen_AI']==0 || id==0){ $(".col-multi_model_gen_AI td .btnShowModal").hide(); }
				}
				$(inputsDiv).append($(myModelGenAI));
				$('.col-multi_model_gen_AI').append( $(inputsDiv) );
				$('.col-multi_model_gen_AI .inputs').hide();
				if(value==1){ $('.col-multi_model_gen_AI .inputs').show(); }
				//---------------------------------------------------------------------
				//$(".checkboxModelGenAI").hide();return;
				//---------------------------------------------------------------------
				if(id!=0){
					let is_active = 0;
					$('#multi_model_gen_AI').bootstrapToggle('disable');
					//$("#multi_model_gen_AI").prop("checked", false).change();
					$.get(that.modelGenAIURL+id)
						.done((res)=>{
							if(res.result==1){ showError("Model Gen AI Error: "+res.msg); }
							else{
								//-----------------------------------------------------
								is_active = res.data.is_active;
								//-----------------------------------------------------
								$("input.checkboxModelGenAI[type=radio]").each((index,item)=>{
									//$(item).prop("checked", true);
									$(item).removeAttr("checked");
									$(item).parent().attr("class", "btn btn-default checkboxModelGenAI");
									$(item).parent().find("i").attr("class", "fa fa-circle-o");

									for(let ii in res.data.portal){
										if($(item).val()==res.data.portal[ii]){
											$(item).attr("checked", "checked");
											$(item).parent().attr("class", "btn btn-info checkboxModelGenAI active");
											$(item).parent().find("i").attr("class", "fa fa-check-circle-o");
										}
									}

									for(let ii in res.data.organization){
										if($(item).val()==res.data.organization[ii]){
											let hdIndx = $(item).data('hdindx');
											$(item).parent().prop("disabled", false);
											$(item).parent().show();
											$(item).parent().parent().find('b.modelTitel.'+hdIndx).css('display', 'block');
										}
									}
								})
							}
							if(is_active==0){
								$('#multi_model_gen_AI').bootstrapToggle('enable');
								$("#multi_model_gen_AI").prop("checked", false).change();
								$('#multi_model_gen_AI').bootstrapToggle('off').bootstrapToggle('disable');
							}else{
								$('#multi_model_gen_AI').bootstrapToggle('enable');
								if(res.data.portal.length!=0){
									$("#multi_model_gen_AI").prop("checked", true).change();
									//$('#multi_model_gen_AI').bootstrapToggle('off').bootstrapToggle('disable');
								}
							}
						})
						.fail((xhr)=>{
							showError("Model Gen AI Error: "+xhr.statusText);
						})
						.always(()=>{
							that.createMultiModelGenAiIsBusy=false;
						});
				}else{
					that.setModelGenAIURL_Items($("#organization_id").val());
				}
			}
		});
		
	}
	//------------------------------------------------------------
	get collectionURL() { return this.apiURL+'/get/collection/'; }
	createCollection(id, value){
		if(this.createCollectionBusy){ return; }
		this.createCollectionBusy = true;
		this.lastPortalID = id;
		let that = this;
		$('.col-multi_model_gen_AI .collections').remove();
		$.ajax({
			url: LIST_COLLECTIONS,
			method:'POST',
			headers:{ apikey: "123" },
			//processData: false,
			//contentType: false,
			data: {userkey:userKey},
			complete: function(){ that.createCollectionBusy=false; },
			error: (xhr)=>{ showError("Collections List Error: "+xhr.statusText); },
			success:function(result){
				//---------------------------------------------------------------------
				let collectionsDiv = $('<div>').attr({ class:"collections", style:"margin-top:10px" });
				let openCollection = $("<button>Collections</button>")
					.attr({
						type:"button",
						class:"btn btn-info btnShowCollections",
						style:"margin:0 2.5px",
						'data-toggle':"modal",
						'data-target':"#myModelCollections"
					});
				//---------------------------------------------------------------------
				let myModelCollections = $('<div>')
											.attr({ id:"myModelCollections",class:"modal fade",role:"dialog",style:"z-index:1060 !important" });
				let modalDialog  = $('<div>').attr({class:"modal-dialog", style:"z-index:1061 !important"});
				let modalContent = $('<div>').attr({class:"modal-content", style:"z-index:1062 !important"});
				let modalHeader  = $('<div>')
									.attr({class:"modal-header"})
									.append('<h4 class="modal-title">Collections</h4>');
				let modalFooter = $('<div>')
									.attr({class:"modal-footer"})
									.append('<button type="button" class="btn btn-default" data-dismiss="modal">Back</button>');

				let caption = ((value==1) ?"Select multiple collections" :"Select a collection");
				let modalBody = $('<div>')
									.attr({class:"modal-body"})
									.append('<b id="collectionSelectTitle" style="display:block; margin-bottom:5px">'+caption+':</b>');
				let showMLButton = 0;
				//---------------------------------------------------------------------
				let indx   = 0;
				let hdIndx = 0;
				for(let i in result[1]){
					let coolection = result[1][i];
					let attr = {
						class : "collectionItems",
						id    : "collection"+indx,
						//name  : "modelGenAI_"+indx,
						name  : ((value==1) ?"collectionCHK"+indx :"collectionRadio"),
						type  : ((value==1) ?"checkbox" :"radio"),
						value : coolection.collection_name,
						style : "display:none; width:0;",
						//style : "width:0;",
						"data-hdindx": hdIndx,
						autocomplete : "off",
					};
					let input = $("<input>").attr(attr);

					//attr = {disabled:"disabled"};
					attr = {disabled:false};
					attr.class = "btn btn-default collectionBtnItems";
					attr.style = "width:48%; text-align:left; margin:5px 1% 5px 1%;";
					//attr.style = "width:48%; text-align:left; margin:5px 1% 5px 1%; ";
					let iItem = "<i class='fa fa-circle-o' style='margin-right:10px'></i>";
					let button = $("<button>")
									.attr(attr)
									.append($(input))
									.append(iItem)
									.append(coolection.collection_name);
					$(modalBody).append(button);

					indx++;
					hdIndx++;
				}
				//---------------------------------------------------------------------
				$(modalContent)
						.append($(modalHeader))
						.append($(modalBody))
						.append($(modalFooter));

				$(modalDialog).append($(modalContent));
				$(myModelCollections).append($(modalDialog));
				//---------------------------------------------------------------------
				if($(".col-multi_model_gen_AI td:nth-child(3) .btnShowCollections").length==0){
					//$(".col-multi_model_gen_AI td:last-child")
					$(".col-multi_model_gen_AI td:nth-child(3)")
						.css("text-align", "center !important")
						.append($(openCollection));
					
				}
				$(collectionsDiv).append($(myModelCollections));
				$('.col-multi_model_gen_AI').append( $(collectionsDiv) );
				//---------------------------------------------------------------------
				that.callCollectionSetting('set 1', value);
				//---------------------------------------------------------------------
				if($("#multi_model_gen_AI").prop("disabled")){ $(".btnShowCollections").hide(); }
				else{}
				//---------------------------------------------------------------------
			}
		});
		
	}
	
	callCollectionSetting(a, ck){
		let id = $("#organization_id").val();
		//let ck = $("#multi_model_gen_AI").prop('checked');
		if(ck){
			$("#collectionSelectTitle").text("Select multiple collections");
			let indx=0;
			$(".collectionItems").each(function(){
				$(this)
					.prop("checked", false)
					.attr("name", "collectionCHK"+indx)
					.attr("type", "checkbox")
					.change();
				indx++;
			});
		}else{
			$("#collectionSelectTitle").text("Select a collection");
			$(".collectionItems")
				.prop("checked", false)
				.attr("name", "collectionRadio")
				.attr("type", "radio")
				.change();
		}
		$("button.collectionBtnItems").removeClass("btn-info").removeClass("active").addClass('btn-default');
		$("button.collectionBtnItems>i").removeClass("fa-check-circle-o").addClass("fa-circle-o");
		
		if($("#multi_model_gen_AI").prop("disabled")){ $(".btnShowCollections").hide(); }
		else{ $(".btnShowCollections").show(); }
		
		if(id!=0 && id!=""){
			let that = this;
			that.collectionIsloaded = false;
			$.get(that.collectionURL+that.lastPortalID)
				.done((res)=>{
					if(res.result==1){ showError("Collection Error: "+res.msg); }
					else{
						//-----------------------------------------------------
						$("input.collectionItems").each((index,item)=>{
							$(item).removeAttr("checked");
							$(item).parent().attr("class", "btn btn-default collectionBtnItems");
							$(item).parent().find("i").attr("class", "fa fa-circle-o");

							for(let ii in res.data){
								if($(item).val()==res.data[ii]){
									$(item).attr("checked", "checked");
									$(item).prop("checked", true);
									$(item).parent().attr("class", "btn btn-info collectionBtnItems active");
									$(item).parent().find("i").attr("class", "fa fa-check-circle-o");
								}
							}
						})
						that.collectionIsloaded = true;
					}
				})
				.fail((xhr)=>{
					showError("Collection Error: "+xhr.statusText);
				})
				.always(()=>{
					//that.createCollectionBusy=false;
				});
		}else{
			this.collectionIsloaded = true;
			$(".btnShowCollections").hide();
		}
	}
	//------------------------------------------------------------
	get modelGenAIURL_ORG() { return this.apiURL+'/get/model_gen_ai_org/'; }
	setModelGenAIURL_Items(org_id){
		let that = this;
		if(org_id==""){ org_id=0; }
		$.get(that.modelGenAIURL_ORG+org_id)
			.done((res)=>{
				if(res.result==1){ showError("Model Gen AI Error: "+res.msg); }
				else{
					$('b.modelTitel').css('display', 'none');
					$("input.checkboxModelGenAI[type=radio]").each((index,item)=>{
						$(item).removeAttr("checked");
						$(item).parent().attr("class", "btn btn-default checkboxModelGenAI");
						$(item).parent().find("i").attr("class", "fa fa-circle-o");
						$(item).parent().hide();

						for(let ii in res.data){
							if($(item).val()==res.data[ii].value){
								let indx = $(item).data('hdindx');
								$(item).parent().prop("disabled", false);
								$(item).parent().show();
								$(item).parent().parent().find('b.modelTitel.'+indx).css('display', 'block');
							}
						}
					})
				}
				$('#multi_model_gen_AI').bootstrapToggle('enable');
				$("#multi_model_gen_AI").prop("checked", false).change();
				if(res.data.length==0){
					$('#multi_model_gen_AI').bootstrapToggle('off').bootstrapToggle('disable');
				}
			})
			.fail((xhr)=>{
				showError("Model Gen AI Error: "+xhr.statusText);
			})
			.always(()=>{
				that.createMultiModelGenAiIsBusy=false;
			});
		
	}
}
//----------------------------------------------------------------
var portalColumns = new Columns([
		{ name: 'id', primary: true, hidden: true },
		{ name: 'name', display:"Portal Name", editable:true, sortable:true, search:true },
		{ name: 'organization_id', display:"Organization", hidden: true },
		{ name: 'orgName', display:"Organization", editable:false, sortable:true, search:false },

		{ name: 'description', display:"Description", hidden: true },
	
		{ name: 'portal_number', display:"Portal Type", editable:true , sortable:true, search:true },
		{ name: 'code'         , display:"Portal Code", editable:false, sortable:true, search:true },

		{ name: 'unknownPersonalityId', display:"Assigned Persona", editable:true, hidden: true },
		{ name: 'orgPersona'          , display:"Org Persona"     , editable:false, sortable:true, search:true },
		{ name: 'unknownPersonality'  , display:"Assigned Persona", editable:false, sortable:true, search:true },
	
		{ name: 'MoD_', display:"Message of The Day", hidden: true },
	
		{ name: 'OnOff', display:"Activate Portal", hidden: true },
		{ name: 'KaaS3PB', display:"KaaS 3PB", hidden: true },
		{ name: 'hasLiveAgent', display:"Live Agent Integration", hidden: true },
		{ name: 'hasLiveAgent', display:"Live Agent Integration", editable:false, sortable:true, hidden: false },
	
		{ name: 'KaaS3PB', display:"KaaS 3PB",hidden:kaaSColumnHidden, editable:false },
		{ name: 'feedback', display:"Feedback", hidden:true },

		{
			name: 'ntfctn_mssg_cstmztn',
//			display:"Notification Message Customization",
			display:"Notification Message",
			editable:true,
			sortable:false,
			search:false,
			hidden:true
		},
		{
			name: 'rqst_mssg_cstmztn',
//			display:"Request Message Customization",
			display:"Sample Utterance",
			editable:true,
			sortable:false,
			search:false,
			hidden:true
		},

		{ name: 'OnOff'  , display:"Active"   , editable:false, sortable:true, search:false },
		{ name: 'OnOffBy', display:"Last User", editable:false, sortable:true, search:false },

		//{ name: 'feedback', display:"Feedback", hidden:true },
		{ name: 'thumbsup', display:"Thumbsup", hidden:true },
		{ name: 'comment' , display:"Comment" , hidden:true },
		{ name:'multi_model_gen_AI', display:'Multi-Model Gen AI', hidden:true , editable:true , sortable:false, search:false },
]);
var data = {
	columns: portalColumns,
	apiURL: apiURL + '/api/dashboard/portal'
}
//----------------------------------------------------------------
if($("#portal").length != 0){
	table = new Portal(data);
	table.createTable('portal');
}
//----------------------------------------------------------------
