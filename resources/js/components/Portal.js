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
		

		$("body").on("change", "#organization_id", function(){ that.getPersonality(); });
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
				if(data.hasLiveAgent==1){//bhr
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
			case 'hasLiveAgent':{
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
		if(this.confirmHandler()){ super.addConfirmHandler(e); }
	}
	editConfirmHandler(e){
		if(this.confirmHandler()){ super.editConfirmHandler(e); }
	}
	//------------------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		$('#OnOff, #KaaS3PB, #hasLiveAgent, #MoD_, #feedback').bootstrapToggle('enable');
		$('#OnOff, #KaaS3PB, #hasLiveAgent, #MoD_, #feedback').bootstrapToggle('off');
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
