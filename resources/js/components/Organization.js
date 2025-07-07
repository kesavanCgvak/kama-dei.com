import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class Organization extends DataTable {
	//----------------------------------------------------
	constructor(data){
		super(data);
		this.defaultPersona = [];
		this.getAllPersona();
		this.upload = false;
		let that=this;
		this.createMyltiLanguageIsBusy = false;
		this.createMultiModelGenAiIsBusy = false;
		$('body').on('change', '#organizationLogo-upload', function () {
			that.upload = false;
			if(this.files.length==1){
				let file = this.files[0];
				if( file.size < 1024 ){ showError('min upload size is 1K'); }
				else{
					if( file.size < 1024 || file.size > 1048576 ){ showError('max upload size is 1M'); }
					else{
						that.upload = true;
					}
				}
			}
			// .name, .type
		});
		var icon1 = $('<a></a>')
						.attr({
							href:'#',
							'data-desc':'Kama-Log emails',
							class:'email-item',
							'data-onlyowner':1,
							style:'color:#2fe02f'
						});
		this.actionIcons = this.actionIcons.concat([icon1]);
		this.showOrgFlags = false;
		$('body').on('click', '.edit-item', (e) => { 
			$("body .autoEmailPanel").hide();
			$("body .col-organizationShortName, body .col-personalityId, body .col-Descripiton, body .col-Billable, body .col-RPA, body .col-MultiLanguage, .col-KaaS3PB, .col-hasLiveAgent, .col-MessageOfTheDa, .col-mfa, .col-feedback, .col-multi_model_gen_AI")
				.show();
			$("#organizationShortName").attr('disabled', false);
			$(".action-form input[type='submit']").css('margin-top', "5px");
			this.showOrgFlags = true;
			this.showEditDialogHandler(e);
		});
		$(document).on('click' , '.btnclos' , function(){
			$('.col-MultiLanguage .inputs').empty();
		});
		
		$('body').on('click', '.email-item', (e) => {
			$("body .col-organizationShortName, body .col-personalityId, body .col-Descripiton, body .col-Billable, body .col-RPA, body .col-MultiLanguage, .col-KaaS3PB, .col-hasLiveAgent, .col-MessageOfTheDay, .col-mfa, .col-feedback, .col-multi_model_gen_AI")
				.hide();
			$("body .col-organizationShortName").show();
			$("#organizationShortName").attr('disabled', true);
			$("body .autoEmailPanel").show();
			$(".action-form input[type='submit']").css('margin-top', "0px");
			this.showOrgFlags = false;
			this.showEditDialogHandler(e)
		});
		
		$('body').on('change', '#MultiLanguage', (e) => {
			let toggle_btn_language = $('input[name="MultiLanguage"]').parent();
			$('.col-MultiLanguage .inputs').hide();
			$('.col-MultiLanguage button').hide();
			if(!toggle_btn_language.hasClass('off')){
				$('.col-MultiLanguage .inputs').show();
				$('.col-MultiLanguage button').show();
			}
			return;
		});
		$('body').on('change', '#multi_model_gen_AI', (e) => {
			let toggle_btn_genAI = $('input[name="multi_model_gen_AI"]').parent();
			$('.col-multi_model_gen_AI .inputs').hide();
			$('.col-multi_model_gen_AI button').hide();
			if(!toggle_btn_genAI.hasClass('off')){
				$('.col-multi_model_gen_AI .inputs').show();
				$('.col-multi_model_gen_AI button').show();
			}
			return;
		});
//MessageOfTheDay
		$('body').on('click', 'button.checkboxMultiLanguage', function(){
			let checked = $(this).find("input.checkboxMultiLanguage")
			if($(checked).prop('checked')){
				$(this).removeClass("btn-primary").removeClass("active").addClass('btn-default');
				$(this).find("i").removeClass("fa-check-circle-o").addClass("fa-circle-o");
				$(checked).prop('checked', false);
			}else{
				$(this).removeClass("btn-default").addClass('btn-primary active');
				$(this).find("i").removeClass("fa-circle-o").addClass("fa-check-circle-o");
				$(checked).prop('checked', true);
			}
		});
		$('body').on('click', 'button.checkboxModelGenAI', function(){
			if($(this).hasClass("inUse")){
				showError("This model is currently being used by a portal, it has to be ON.");
				return;
			}
			let checked = $(this).find("input.checkboxModelGenAI");
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
		
		$('body').on('change', '#AutoOnOff', function(){
			$(".col-send_chat_format").hide();
			$(".col-chat_logs_sent").hide();

			if($("#AutoOnOff").prop("checked")){
				$(".col-send_chat_format").show();
				$(".col-chat_logs_sent").show();
			}
		});
		$('body').on('click', 'button.send_chat_format', function(){
			$("button.send_chat_format").removeClass('active');
			$(this).addClass('active');
			$("#send_chat_format").val($(this).val()).change();
		});
		$('body').on('click', 'button.chat_logs_sent', function(){
			$("button.chat_logs_sent").removeClass('active');
			$(this).addClass('active');
			$("#chat_logs_sent").val($(this).val()).change();
		});
		
	}
	//----------------------------------------------------
	get langsURL() { return this.apiURL+'/get/language/'; }
	createMyltiLanguage(id, value){
		if(this.createMyltiLanguageIsBusy){ return; }
//console.log("----------------")
//console.log(this.createMyltiLanguageIsBusy)
//console.log(this.editItem['MultiLanguage'])
//console.log("id:"+id+" | value:"+value)
		this.createMyltiLanguageIsBusy = true;
		let that = this;
		$('.col-MultiLanguage .inputs').remove();
		$.ajax({
			url: this.langsURL+id,
			method:'GET',
			complete: function(){ that.createMyltiLanguageIsBusy=false; },
			success:function(result){
				let inputsDiv = $('<div>').attr({ class:"inputs", style:"margin-top:10px" });
				let openModal = $("<button>active languages</button>").attr({
					type:"button",
					class:"btn btn-info",
					'data-toggle':"modal",
					'data-target':"#myLangs"
				});

				let myLangs = $('<div>').attr({ id:"myLangs", class:"modal fade", role:"dialog", style:"z-index:1060 !important" });
				let modalDialog = $('<div>').attr({class:"modal-dialog", style:"z-index:1061 !important"});
				let modalContent = $('<div>').attr({class:"modal-content", style:"z-index:1062 !important"});
				let modalHeader = $('<div>')
									.attr({class:"modal-header"})
									.append('<h4 class="modal-title">Languages</h4>');
				let modalFooter = $('<div>')
									.attr({class:"modal-footer"})
									.append('<button type="button" class="btn btn-default" data-dismiss="modal">Back</button>');

				let modalBody = $('<div>').attr({class:"modal-body"});
//									.append('<p>Some text in the modal.</p>');
				let showMLButton = 0;
				for(let i=0; i<result.data.length; i++){
					let attr = {
						class : "checkboxMultiLanguage",
						id    : "multiLanguage_"+result.data[i].code,
						name  : "multiLanguage_"+result.data[i].code,
						type  : "checkbox",
						value : result.data[i].code,
						style : "display:none; width:0;",
						autocomplete : "off",
					};
					if( result.data[i].code=='en'||result.data[i].isActive==1 ){ attr.checked="checked"; }
					let input = $("<input>").attr(attr);

					showMLButton+=result.data[i].isActive;
						
					attr = {};
					attr.class = ((result.data[i].code=='en'||result.data[i].isActive==1) 
										?'btn btn-primary checkboxMultiLanguage active'
										:"btn btn-default checkboxMultiLanguage"
								 );
					if(result.data.length>1){ attr.style = "width: 48%; text-align:left; margin: 1px 1% 1px 1%"; }
					else{ attr.style = "width: 98%; text-align:left; margin: 1px 1% 1px 1%"; }
					
					let iItem = "<i class='fa fa-circle-o' style='margin-right:10px'></i>";
					if( result.data[i].code=='en'||result.data[i].isActive==1 ){
						attr.checked="checked";
						iItem = "<i class='fa fa-check-circle-o' style='margin-right:10px'></i>";
					}
					if(result.data[i].code=='en'){ attr.disabled="disabled"; }
					
					let button = $("<button>")
									.attr(attr)
									.append($(input))
									.append(iItem)
									.append(result.data[i].name);
					$(modalBody).append(button);
				}
				
				$(modalContent)
						.append($(modalHeader))
						.append($(modalBody))
						.append($(modalFooter));

				$(modalDialog).append($(modalContent));
				$(myLangs).append($(modalDialog));
				
//console.log("showMLButton:"+showMLButton)
//console.log($(".col-MultiLanguage td:last-child button").length)
//console.log(this.editItem['MultiLanguage'])
//console.log(that.editItem['MultiLanguage'])

				if($(".col-MultiLanguage td:last-child button").length==0){
					$(".col-MultiLanguage td:last-child").append($(openModal)).css("text-align", "center !important");
					//if(showMLButton==1 || id==0){ $(".col-MultiLanguage td:last-child button").hide(); }
					if(that.editItem['MultiLanguage']==0 || id==0){ $(".col-MultiLanguage td:last-child button").hide(); }
				}
				$(inputsDiv).append($(myLangs));

				//$('.col-MultiLanguage').css('vertical-align' , 'top');
				$('.col-MultiLanguage').append( $(inputsDiv) );

				$('.col-MultiLanguage .inputs').hide();
				if(value==1){ $('.col-MultiLanguage .inputs').show(); }
			}
		});
		
	}
	//----------------------------------------------------
	get modelGenAIURL() { return this.apiURL+'/get/model_gen_ai/'; }
	createMultiModelGenAI(id, value){
		if(this.createMultiModelGenAiIsBusy){ return; }
		this.createMultiModelGenAiIsBusy = true;
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
			error: (xhr)=>{
				showError("Model Gen AI Error: "+xhr.statusText);
			},
			success:function(result){
				//---------------------------------------------------------------------
				let inputsDiv = $('<div>').attr({ class:"inputs", style:"margin-top:10px" });
				let openModal = $("<button>active Model Gen AI</button>").attr({
					type:"button",
					class:"btn btn-info",
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
									.append('<b>Select multiple models:</b>');
				let showMLButton = 0;
				//---------------------------------------------------------------------
				let indx = 0;
				for(let i in result){
					//if(indx!=0){ $(modalBody).append("<b style='display:block; margin-top:20px'>"+i+"</b>"); }
					//else{ $(modalBody).append("<b style='display:block'>"+i+"</b>"); }
					$(modalBody).append("<b style='display:block; margin-top:20px'>"+i+"</b>");
					for(let j in result[i]){
						let attr = {
							class : "checkboxModelGenAI",
							id    : "modelGenAI"+indx,
							name  : "modelGenAI_"+indx,
							type  : "checkbox",
							value : result[i][j],
							style : "display:none; width:0;",
							autocomplete : "off",
						};
						let input = $("<input>").attr(attr);
						
						attr = {};
						attr.class = "btn btn-default checkboxModelGenAI";
						attr.style = "width: 48%; text-align:left; margin: 1px 1% 5px 1%";
						let iItem = "<i class='fa fa-circle-o' style='margin-right:10px'></i>";
						let button = $("<button>")
										.attr(attr)
										.append($(input))
										.append(iItem)
										.append(result[i][j]);
						$(modalBody).append(button);
						
						indx++;
					}
				}
				//---------------------------------------------------------------------
				$(modalContent)
						.append($(modalHeader))
						.append($(modalBody))
						.append($(modalFooter));

				$(modalDialog).append($(modalContent));
				$(myModelGenAI).append($(modalDialog));
				//---------------------------------------------------------------------
				if($(".col-multi_model_gen_AI td:last-child button").length==0){
					$(".col-multi_model_gen_AI td:last-child").append($(openModal)).css("text-align", "center !important");
					if(that.editItem['multi_model_gen_AI']==0 || id==0){ $(".col-multi_model_gen_AI td:last-child button").hide(); }
				}
				$(inputsDiv).append($(myModelGenAI));
				$('.col-multi_model_gen_AI').append( $(inputsDiv) );
				$('.col-multi_model_gen_AI .inputs').hide();
				if(value==1){ $('.col-multi_model_gen_AI .inputs').show(); }
				//---------------------------------------------------------------------
				
				//---------------------------------------------------------------------
				$.get(that.modelGenAIURL+id)
					.done((res)=>{
						if(res.result==1){ showError("Model Gen AI Error: "+res.msg); }
						else{
							$("input.checkboxModelGenAI[type=checkbox]").each((index,item)=>{
								//$(item).prop("checked", true);
								$(item).removeAttr("checked");
								$(item).parent().attr("class", "btn btn-default checkboxModelGenAI");
								$(item).parent().find("i").attr("class", "fa fa-circle-o");
								  
								for(let ii in res.data){
									if($(item).val()==res.data[ii].value){
										$(item).attr("checked", "checked");
										$(item).parent()
											.attr("class", "btn btn-info checkboxModelGenAI active "+((res.data[ii].inUse==0) ?"" :"inUse"));
										$(item).parent().find("i").attr("class", "fa fa-check-circle-o");
									}
								}
							})
						}
					})
					.fail((xhr)=>{
						showError("Model Gen AI Error: "+xhr.statusText);
					})
					.always(()=>{
						that.createMultiModelGenAiIsBusy=false;
					});
			}
		});
		
	}
	//----------------------------------------------------

	rowActions(value, row, index, field) {
		//------------------------------------------------
		var icons = this.actionIcons;
		var tmpICN = [];
		//------------------------------------------------
		for (var i in icons){
			if( icons[i].attr('class')=='edit-item' ){ icons[i].attr('data-desc','Org Config'); }
			tmpICN.push(icons[i]); 
		}
		this.actionIcons = tmpICN;
		//------------------------------------------------
		return super.rowActions(value, row, index, field);
		//------------------------------------------------
	}
	//----------------------------------------------------
	getAllPersona(){
		$("select#personalityId option").remove();
		$("select#defaultPersona option").remove();
		$("select#defaultPersona").append("<option value=''>Select Default Persona</option>");
		$.get(apiURL+'/api/dashboard/personality/parents/'+orgID, (obj) => {
			var tmpOptions = [];
			for(var i in obj.data){ tmpOptions.push("<option value='"+obj.data[i].personalityId+"'>"+obj.data[i].personalityName+"</option>"); }
			this.defaultPersona = tmpOptions;
			$("select#personalityId").append(this.defaultPersona);
			$("select#defaultPersona").append(this.defaultPersona);
			$("select#defaultPersona").val(defaultPersonaId);
		});
	}
	//----------------------------------------------------
	createTable(id){
		super.createTable(id);
		
		$("#"+id+" #editItem form .col-Descripiton.form-group").after(
			$('<div>')
				.attr({class:'panel panel-default autoEmailPanel'})
				.append(
					$('<div>')
						.attr({class:'panel-heading', style:'background:#edeff0; font-weight:normal; font-size:15px;'})
						.append('Auto Email')
				)
				.append(
					$('<div>')
						.attr({class:'panel-body', style:'border: 1px solid #64768740;'})
						.append($('.col-EmailTheme.form-group'))
						.append($('.col-AutoEmail.form-group'))
						.append($('.col-EmailBody.form-group'))
						.append($('.col-Footer.form-group'))
						.append($('.col-AutoOnOff.form-group'))
						.append($('.col-send_chat_format.form-group'))
						.append($('.col-chat_logs_sent.form-group'))
				)
		);
	}
	//----------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'organizationShortName':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group half left"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
				break;
			}
			case 'personalityId':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group half right"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<select>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
				break;
			}
			/*
			case 'EmailTheme':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group half left"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
				break;
			}
			*/
			case 'AutoEmail':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
				break;
			}
			case 'EmailBody':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<textarea>').attr({ id: col, name: col, class: 'form-control fixed' })
								)
						);
				break;
			}
			/*
			case 'OverTime':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group third left"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
				break;
			}
			*/
			/*
			case 'Theme':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group third center"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<select>')
										.attr({ id: col, name: col, class: 'form-control' })
										.append('<option value="blue">Blue</option>')
										.append('<option value="green">Green</option>')
										.append('<option value="red">Red</option>')
										.append('<option value="orange">Orange</option>')
								)
						);
				break;
			}
			*/
			case 'Footer':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
				break;
			}
			/*
			case 'NeedRegister':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group half left"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id:col, name:col, 'data-toggle':'toggle', type:"checkbox" })
								)
						);
				break;
			}
			*/
			case 'AutoOnOff':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group half left"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id:col, name:col, 'data-toggle':'toggle', type:"checkbox" })
								)
						);
				break;
			}
			case 'send_chat_format':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group half right"})
							.append(
								$(
									'<input type="hidden" id="send_chat_format" name="send_chat_format">'+
									'<div class="btn-group" role="group" aria-label="Basic example">'+
									'<button type="button" class="btn btn-primary send_chat_format" value="csv">CSV</button>'+
									'<button type="button" class="btn btn-primary send_chat_format" value="pdf">PDF</button>'+
//									'<button type="button" class="btn btn-primary send_chat_format" value="json">JSON</button>'+
//									'<button type="button" class="btn btn-primary send_chat_format" value="xml">XML</button>'+
									'</div>'
								)
							);
				break;
			}
			case 'chat_logs_sent':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group center"})
							.append(
								$(
									'<input type="hidden" id="chat_logs_sent" name="chat_logs_sent">'+
									'<div class="btn-group" role="group" aria-label="Basic example">'+
									'<button type="button" class="btn btn-primary chat_logs_sent" value="1">As individual emails</button>'+
									'<button type="button" class="btn btn-primary chat_logs_sent" value="2">As several attachments to one single email</button>'+
									'</div>'
								)
							)
							.append(
								$('<div>Please configure your email system to not block Kama-DEI emails for spam. The kama-log emails will be sent to you from this email address: <b onclick="copyEmail2Clipboard(this)">'+MAIL_FROM_ADDRESS+'</b></div>')
									.attr({class: "emailSenderAddress"})
							);
				break;
			}
			/*
			case 'FooterUrlDisplay':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group third right"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id:col, name:col, 'data-toggle':'toggle', type:"checkbox" })
								)
						);
				break;
			}
			*/
			/*
			case 'FooterUrl':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group half left"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
				break;
			}
			*/
			/*
			case 'Slogan':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group half right"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id: col, name: col, class: 'form-control' })
								)
						);
				break;
			}
			*/
			/*
			case 'organizationLogo':{
				input = $('<div>').attr({class: "form-group"})
							.append(
								$('<div>')
									.attr({class: "col-" + col + " form-group half left"})
									.append( $('<label>').text(label) )
									.append(
										$('<div>').append(
											$('<input>').attr({ id: col, name: col, class: 'form-control', disabled:'disabled' })
										)
									)
							)
							.append(
								$('<div>')
									.attr({class: "col-" + col + " form-group half right"})
									.append( $('<label>').text("") )
									.append(
										$('<div>').append(
											$('<form>').attr({enctype:"multipart/form-data", id:'fileUploader'})
											.append(
												$('<input>').attr({
													id:col+'-upload',
													name:col+'-upload',
													class:'form-control',
													type:'file'
												})
											)
											.append(
												$('<input>').attr({
													id:col+'-orgID',
													name:col+'-orgID',
													type:'hidden'
												})
											)
										)
									)
							);
				break;
			}
			*/
			case 'Billable':
			case 'RPA':
			case 'KaaS3PB':
			case 'hasLiveAgent':
			case 'MultiLanguage':
			case 'MessageOfTheDay':
			case 'mfa':
			case 'feedback':
			case 'multi_model_gen_AI':
				{
				input = $('<tr>')
							.attr({class: "orgFlags col-" + col })
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

			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//----------------------------------------------------
	showError(msg){ showError(msg); }
	showSuccess(msg){ showSuccess(msg); }
	//----------------------------------------------------
	showAddDialogHandler(){
		this.upload=false;
		super.showAddDialogHandler();
		$('#chat_logs_sent').val(2);
		$('#send_chat_format').val('csv');
		
		$(".chat_logs_sent").removeClass('active');
		$(".chat_logs_sent [value=2]").addClass('active');

		$(".send_chat_format").removeClass('active');
		$(".send_chat_format [value='csv']").addClass('active');
		
		$('#AutoOnOff'         ).bootstrapToggle('off');
		$('#Billable'          ).bootstrapToggle('off');
		$('#RPA'               ).bootstrapToggle('off');
		$('#MultiLanguage'     ).bootstrapToggle('off');
		$('#MessageOfTheDay'   ).bootstrapToggle('off');
		$('#KaaS3PB'           ).bootstrapToggle('off');
		$('#hasLiveAgent'      ).bootstrapToggle('off');
		$('#mfa'               ).bootstrapToggle('off');
		$('#feedback'          ).bootstrapToggle('off');
		$('#multi_model_gen_AI').bootstrapToggle('off');
		

		$("body .autoEmailPanel").hide();
		$("body .col-organizationShortName, body .col-personalityId, body .col-Descripiton, body .col-Billable, body .col-RPA, body .col-MultiLanguage, .col-KaaS3PB, .col-hasLiveAgent, .col-MessageOfTheDay, .col-mfa, .col-feedback, .col-multi_model_gen_AI")
			.show();
		$("#organizationShortName").attr('disabled', false);
		$(".action-form input[type='submit']").css('margin-top', "5px");

		if(levelID!=1){
			$(
				"#organizationShortName,"+
				"#personalityId,"+
				"#Descripiton,"+
				
				"#EmailTheme,"+
				"#AutoEmail,"+
				"#EmailBody,"+
				"#Footer,"+

				".chat_logs_sent,"+
				".send_chat_format,"+
				
				"#insertItem"
			).prop('disabled', true);

			$(
				'#AutoOnOff,'+
				'#Billable,'+
				'#RPA,'+
				'#MultiLanguage,'+
				'#MessageOfTheDay,'+
				'#KaaS3PB,'+
				'#hasLiveAgent,'+
				'#mfa,'+
				'#feedback,'+
				'#multi_model_gen_AI'
			).bootstrapToggle('disable');
		}
		this.createMyltiLanguage(0, 0);
		this.createMultiModelGenAI(0, 0);
		if($("#orgFlags").length==0){
			$("<table>")
				.attr({id: "orgFlags"})
				.append("<thead><th>Service</th><th>Status</th><th>Details</th></thead><tbody></tbody>")
			.insertBefore( $("tr.col-Billable") );
			$("tr.orgFlags").each(function(){
				let tr = $(this);
				$("#orgFlags>tbody").append(tr);
			});

			$("<label class='orgFlasLabel'>Value Added Services</label>").insertBefore( $("table#orgFlags") );
		}
		$("#orgFlags").show();
		$(".orgFlasLabel").show();
		$(".orgFlags.col-MultiLanguage button").hide();
		$("form .btnclose").css("margin-top","5px");
	}
	//----------------------------------------------------
	showEditDialogHandler(e){
		this.upload=false;
		//$('#fileUploader')[0].reset();
		try{ super.showEditDialogHandler(e); }catch(e){}
		//$('#NeedRegister'    ).bootstrapToggle('off');
		//$('#FooterUrlDisplay').bootstrapToggle('off');
		//if($("#NeedRegister"    ).val()==1){ $('#NeedRegister'    ).bootstrapToggle('on'); }

		let tmp = $("#chat_logs_sent").val();
		$(".chat_logs_sent").removeClass('active');
		$(".chat_logs_sent[value="+tmp+"]").addClass('active');

		tmp = $("#send_chat_format").val();
		$(".send_chat_format").removeClass('active');
		$(".send_chat_format[value='"+tmp+"']").addClass('active');

		$('#AutoOnOff'      ).bootstrapToggle('off');
		$('#Billable'       ).bootstrapToggle('off');
		$('#RPA'            ).bootstrapToggle('off');
		$('#MultiLanguage'  ).bootstrapToggle('off');
		$('#MessageOfTheDay').bootstrapToggle('off');
		$('#KaaS3PB'        ).bootstrapToggle('off');
		$('#hasLiveAgent'   ).bootstrapToggle('off');
		$('#mfa'            ).bootstrapToggle('off');
		$('#feedback'       ).bootstrapToggle('off');
		$('#multi_model_gen_AI'    ).bootstrapToggle('off');
		
		if($("#AutoOnOff"      ).val()==1){ $('#AutoOnOff'      ).bootstrapToggle('on'); }
		if($("#Billable"       ).val()==1){ $('#Billable'       ).bootstrapToggle('on'); }
		if($("#RPA"            ).val()==1){ $('#RPA'            ).bootstrapToggle('on'); }
		if($("#MultiLanguage"  ).val()==1){ $('#MultiLanguage'  ).bootstrapToggle('on'); }
		if($("#MessageOfTheDay").val()==1){ $('#MessageOfTheDay').bootstrapToggle('on'); }
		if($("#KaaS3PB"        ).val()==1){ $('#KaaS3PB'        ).bootstrapToggle('on'); }
		if($("#hasLiveAgent"   ).val()==1){ $('#hasLiveAgent'   ).bootstrapToggle('on'); }
		if($("#mfa"            ).val()==1){ $('#mfa'            ).bootstrapToggle('on'); }
		if($("#feedback"       ).val()==1){ $('#feedback'       ).bootstrapToggle('on'); }
		if($("#multi_model_gen_AI"    ).val()==1){ $('#multi_model_gen_AI'    ).bootstrapToggle('on'); }
		
		if(levelID!=1){
			$(
				"#organizationShortName,"+
				"#personalityId,"+
				"#Descripiton,"+
				
				"#EmailTheme,"+
				"#AutoEmail,"+
				"#EmailBody,"+
				"#Footer,"+

				".chat_logs_sent,"+
				".send_chat_format,"+
				
				"#saveItem"
			).prop('disabled', true);

			$(
				'#AutoOnOff,'+
				'#Billable,'+
				'#RPA,'+
				'#MultiLanguage,'+
				'#MessageOfTheDay,'+
				'#KaaS3PB,'+
				'#hasLiveAgent,'+
				'#mfa,'+
				'#feedback,'+
				'#multi_model_gen_AI'
			).bootstrapToggle('disable');
		}
		this.createMyltiLanguage(this.editItem['organizationId'],this.editItem['MultiLanguage']);
		this.createMultiModelGenAI(this.editItem['organizationId'],this.editItem['multi_model_gen_AI']);
		if($("#orgFlags").length==0){
			$("<table>")
				.attr({id: "orgFlags"})
				.append("<thead><th>Service</th><th>Status</th><th>Details</th></thead><tbody></tbody>")
			.insertBefore( $("tr.col-Billable") );

			$("tr.orgFlags").each(function(){
				let tr = $(this);
				$("#orgFlags>tbody").append(tr);
			});

			$("<label class='orgFlasLabel'>Value Added Services</label>").insertBefore( $("table#orgFlags") );
		}
		if(this.showOrgFlags){
			$("#orgFlags").show();
			$(".orgFlasLabel").show();
			$("#saveItem").text("Save");
			$(".orgFlags.col-MultiLanguage button").hide();

			if($("#MultiLanguage").val()==1){ $(".orgFlags.col-MultiLanguage>td>button").show(); }
			$("#MultiLanguage").change();
			//$(".orgFlags.col-MultiLanguage button").show();
			$("form .btnclose").css("margin-top","5px");
		}else{
			$("#orgFlags").hide();
			$(".orgFlasLabel").hide();
			$("#saveItem").text("Save Item");
			$(".orgFlags.col-MultiLanguage button").hide();
			$("form .btnclose").css("margin-top","0px");
		}
	}
	//----------------------------------------------------
	editConfirmHandler(e){
		//------------------------------------------------
		e.preventDefault();
		//------------------------------------------------
		let digit = new RegExp('^[0-9]+$');
		let email = /\S+@\S+\.\S+/;

		//let OverTime  = $("#OverTime" ).val().trim();
		let AutoEmail = $("#AutoEmail").val().trim();
		
		if($("#organizationShortName").val().trim()==''){ showError('Organization Short Name is empty.'); return; }
		//if(!digit.test(OverTime)){ showError('Invalid OverTime value.'); return; }

		if(AutoEmail!=''){
			let tmpEmails = AutoEmail.split(";");
			AutoEmail = "";
			for( let ii in tmpEmails){
				let tmp = tmpEmails[ii].trim();
				if(tmp!='' && !email.test(tmp)){ showError('Invalid AutoEmail value ['+tmp+'].'); return; }
				else{
					if(tmp!=''){ AutoEmail +=(tmp+";"); }
				}
			}
		}
		$("#AutoEmail").val(AutoEmail).change();
		//------------------------------------------------
		//super.editConfirmHandler(e);
		//------------------------------------------------
		let that = this;
		let table = this.table;
		
		let data = {
			orgID: this.orgID,
			userID: this.userID,
			language: [],
			modelGenAI: []
		};
		//------------------------------------------------
		for(var x in this.columns.names){
			if(this.columns.data[x].passData !== false) {
				var name = this.columns.names[x];
				var value = this.editItem[name];
				if(name == 'ownerId' && value == null){ value = '0'; }
				data[name] = value;
			}
		}
		//------------------------------------------------
		$('input[class="checkboxMultiLanguage"]').each(function(){
			if($(this).prop('checked')){ data.language.push($(this).val()); }
		});
		//------------------------------------------------
		if(data.multi_model_gen_AI==1){
			$('input[class="checkboxModelGenAI"]').each(function(){
				if($(this).prop('checked')){ data.modelGenAI.push($(this).val()); }
			});
			if(data.modelGenAI.length==0){
				showError("Multi-Model Gen AI requires one model or more to be selected.");
				return;
			}
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
			beforeSend: function(){ $("#editItem #saveItem").prop('disabled', true); },
			success: function(res){
				if(res.result == 0){
					$("#editItem").fadeOut(function(){ $("#editItem #saveItem").prop('disabled', false); });
					showSuccess('Item saved.');
					if(!that.upload){ $(table).bootstrapTable('refresh'); }
					else{
						showSuccess('Uploading...');
						that.fileUploading(res.orgID);
					}
				}else{
					showError(res.msg);
					$("#editItem #saveItem").prop('disabled', false);
				}
			},
			error: function(e){
				showError(e.statusText);
				$("#editItem #saveItem").prop('disabled', false);
			}
		});		
	}
	//----------------------------------------------------
	addConfirmHandler(e){
		//------------------------------------------------
		e.preventDefault();
		//------------------------------------------------
		let digit = new RegExp('^[0-9]+$');
		let email = /\S+@\S+\.\S+/;
		
		//let OverTime  = $("#OverTime" ).val().trim();
		let AutoEmail = $("#AutoEmail").val().trim();
		
		if($("#organizationShortName").val().trim()==''){ showError('Organization Short Name is empty.'); return; }
		//if(!digit.test(OverTime)){ showError('Invalid OverTime value.'); return; }

		if(AutoEmail!=''){
			let tmpEmails = AutoEmail.split(";");
			AutoEmail = "";
			for( let ii in tmpEmails){
				let tmp = tmpEmails[ii].trim();
				if(tmp!='' && !email.test(tmp)){ showError('Invalid AutoEmail value.'); return; }
				else{
					if(tmp!=''){ AutoEmail +=(tmp+";"); }
				}
			}
		} 
		$("#AutoEmail").val(AutoEmail).change();
		//------------------------------------------------
		//super.addConfirmHandler(e);
		//------------------------------------------------
		let that = this;
		let table = this.table;
		let data = {
			orgID: this.orgID,
			userID: this.userID,
			language: [],
			modelGenAI: []
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
		$('input[class="checkboxMultiLanguage"]').each(function(){
			if($(this).prop('checked')){ data.language.push($(this).val()); }
		});
		//------------------------------------------------
		if(data.multi_model_gen_AI==1){
			$('input[class="checkboxModelGenAI"]').each(function(){
				if($(this).prop('checked')){ data.modelGenAI.push($(this).val()); }
			});
			if(data.modelGenAI.length==0){
				showError("Multi-Model Gen AI requires one model or more to be selected.");
				return;
			}
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
					if(!that.upload){ $(table).bootstrapTable('refresh'); }
					else{
						showSuccess('Uploading...');
						that.fileUploading(res.orgID);
					}
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
	//----------------------------------------------------
	get uploadURL   () { return this.apiURL+'/uploadlogo/'; }
	fileUploading(orgID){
		var table = this.table;
		$("#organizationLogo-orgID").val(orgID);
		let fileData = new FormData($('#fileUploader')[0]);
		//------------------------------------------------
		$.ajax({
			url: this.uploadURL,
			type: 'POST',
			data: fileData,

			cache: false,
			contentType: false,
			processData: false,

			xhr: function (){
				let myXhr = $.ajaxSettings.xhr();
				if( myXhr.upload ){
					myXhr.upload.addEventListener('progress', function(e){
						//if(myXhr.status!=200){ return; }
						if(e.lengthComputable){
//							showSuccess(((e.loaded/e.total)*100)+"% uploaded");
						}
					}, false);
				}
				return myXhr;
			},
			success:function(ret){
				if(ret.result=='0'){ 
					showSuccess("Uploading completed.");
					$(table).bootstrapTable('refresh'); 
				}
				else{ showError(ret.msg); }
			},
			error:function(xhr){
				showError(xhr.statusText);
			}
		});
	}
	//----------------------------------------------------
	cellRenderer(value, row, index, field){
		value = super.cellRenderer(value, row, index, field);
		var column = null;
		for(var x in this.columns.names){
			if(this.columns.names[x] == field) {
				column = this.columns.data[x];
				break;
			}
		}
		if(column.name === 'KaaS3PB'      && !column.hidden) return this.checkCell(value, row, column);
		if(column.name === 'hasLiveAgent' && !column.hidden) return this.checkCell(value, row, column);
		return value;
	}
	//----------------------------------------------------
}

var columns = [
	{ name: 'organizationId', display:'ID', primary:true, sortable:true, search:true, editable:false, width:'80px', class:'right' },

	{ name:'organizationShortName', display:'Organization Name' , hidden:false, editable:true , sortable:true , search:true   },
	{ name:'personalityId'        , display:'Default Persona'   , hidden:true },
	{ name:'personalityName'      , display:'Default Persona'   , hidden:false, editable:false, sortable:false, search:false },
	
	{ name:'Descripiton', display:'Organization Description (Optional)', hidden:true , editable:true , sortable:false, search:false  },
	
	{ name:'EmailTheme'           , display:'Email Subject'     , hidden:true , editable:true , sortable:false, search:false  },
	
	{ name:'AutoEmail', display:'Auto Email Address(es)', hidden:false, editable:true , sortable:false, search:false  },
	
	{ name:'EmailBody'            , display:'Email Body'        , hidden:true , editable:true , sortable:false, search:false  },
//	{ name:'OverTime'             , display:'Over Time'         , hidden:false, editable:true , sortable:false, search:false  },
//	{ name:'Theme'                , display:'Theme'             , hidden:true , editable:true , sortable:false, search:false  },
	{ name:'Footer'               , display:'Footer'            , hidden:true , editable:true , sortable:false, search:false  },
//	{ name:'NeedRegister'         , display:'Need Register'     , hidden:true , editable:true , sortable:false, search:false  },
	{ name:'AutoOnOff'            , display:'Email Chats'       , hidden:true , editable:true , sortable:false, search:false  },
//	{ name:'FooterUrlDisplay'     , display:'Footer Url Display', hidden:true , editable:true , sortable:false, search:false  },
//	{ name:'FooterUrl'            , display:'Footer Url'        , hidden:true , editable:true , sortable:false, search:false  },
//	{ name:'Slogan'               , display:'Slogan'            , hidden:false, editable:true , sortable:false, search:false  },
//	{ name:'organizationLogo'     , display:'Logo Path'         , hidden:true , editable:true , sortable:false, search:false  },
	
//	{ name:'organizationLogo-upload', display:'', hidden:true , editable:false, sortable:false, search:false  },

	{ name:'Billable'          , display:'Billable'                   , hidden:true , editable:true , sortable:false, search:false },
	{ name:'RPA'               , display:'RPA'                        , hidden:true , editable:true , sortable:false, search:false },
	{ name:'KaaS3PB'           , display:'KaaS 3PB'                   , hidden:true , editable:true , sortable:false, search:false },
	{ name:'hasLiveAgent'      , display:'Has Live Agent'             , hidden:true , editable:true , sortable:false, search:false },
	{ name:'MessageOfTheDay'   , display:'Message of the Day'         , hidden:true , editable:true , sortable:false, search:false },
	{ name:'MultiLanguage'     , display:'Multi Language'             , hidden:true , editable:true , sortable:false, search:false },
	{ name:'mfa'               , display:'Multi-Factor Authentication', hidden:true , editable:true , sortable:false, search:false },
	{ name:'feedback'          , display:'Feedback'                   , hidden:true , editable:true , sortable:false, search:false },
	{ name:'multi_model_gen_AI', display:'Multi-Model Gen AI'         , hidden:true , editable:true , sortable:false, search:false },
	{ name:'chat_logs_sent'    , display:'-'                          , hidden:true , editable:true , sortable:false, search:false },
	{ name:'send_chat_format'  , display:'Send chat in this format'   , hidden:true , editable:true , sortable:false, search:false },
];
var organizationColumns = new Columns(columns);

var data = {
	columns: organizationColumns,
	apiURL: apiURL + '/api/dashboard/organization'
}

if($("#organization").length != 0){
	table = new Organization(data);
}
