import { DataTable, showError, showSuccess, showConfirm } from './DataTable'
import Columns from './Columns'
//----------------------------------------------------------------
class Messaging extends DataTable{
	//------------------------------------------------------------
	constructor(data){
		super(data);
		this.defaultLang = 'en';
		this.pageSort    = 'Name';
		this.lastLang    = this.defaultLang;
		this.userID      = userId;
		this.parentId    = 0;
		this.hasPagination = true;
		let that = this;	
		this.getType();
		
		$('#insertBtn').ready(function(){
			if(messageType=='ai') $('#insertBtn').text("Add Kama-DEI Messages");
			else $('#insertBtn').text("Add Chatbot Message");
			$('#insertBtn').prop('disabled', true);
			$('#insertBtn').css('color', "#cac7e1");
			$('#insertBtn').css('background'  , "#e7ebef");
			$('#insertBtn').css('border-color', "#e7ebef");
			if(orgID!=0){ $('#insertBtn').hide(); }
			$('#insertBtn').hide();
			$("#tabDIV").show();
		});
		$('a.delete-item').ready(function(){
			$(this).prop('disabled', true);
		});
		
		$("body").on("change", "#OrgId", function(){
			that.getLang(that.defaultLang);
			that.lastLang = that.defaultLang;
			that.getMessage(that.editItem.Code, $("#OrgId").val(), that.defaultLang);			
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
		$("body").on("change", "#KaaS3PB", function(){ 
			if($(this).prop('checked')){
				$('#OnOff').prop('checked', true).change();
				$('#OnOff').bootstrapToggle('disable');
			}else{ $('#OnOff').bootstrapToggle('enable'); }
		});


		$("body").on("click","#tabDIV button", that.changeInterface);

		
		$("body").on("change","input.stv-radio-button.edit", function(){
			if(that.lastLang!=$(this).val()){
				that.lastLang=$(this).val();
				let lang = that.lastLang;
				that.getMessage(that.editItem.Code, $("#OrgId").val(), that.lastLang);
			}
		});
		
		$("body").on("change","#emptyMessage", function(){
		});
		$("body").on("change","#Message", function(){
			if($("#Message").val().trim()!=""){ $("#emptyMessage").bootstrapToggle('off'); }
		});

		this.baseActionIcons = this.actionIcons;
		$('body').on('click', '.reset-item', (e) => { this.showResetDialogHandler(e) });
	}
	//------------------------------------------------------------
	showResetDialogHandler(e){
		let id = $(e.currentTarget).data('itemid');
		showConfirm(
			function(flag){
				if(flag==true){
					$.post(apiURL + '/api/dashboard/'+ messageType +'message/reset/' +id, {}, function(res){
						if(res.result==1){ showError(res.msg); }
						else{ $(table.table).bootstrapTable('refresh'); }
					}).fail(function(xhr){ showError(xhr.responseJSON.message); });
				}
			},
			"This will reset the message to the Kama-DEI default",
			"OK",
			"Cancel"
		);//, yes='Yes', no='No', classYes="btn-danger");
	}
	//------------------------------------------------------------
	rowActions(value, row, index, field) {
		if(row.OrgId!=null){
			this.actionIcons = this.baseActionIcons;
			let icon1 = $('<a></a>').attr({ href:'#', 'data-desc':'Reset', class:'reset-item', 'data-onlyowner':0, style:"color:red" });
			this.actionIcons = this.actionIcons.concat([icon1]);
		}else{
			this.actionIcons = this.baseActionIcons;
		}
		return super.rowActions(value, row, index, field);
	}
	//------------------------------------------------------------
	changeInterface(){
		if($(this).hasClass('active')){ return; }
		$("#tabDIV button").removeClass('active');
		$(this).addClass('active');
		messageType = $(this).data('message');
		$(table.table).bootstrapTable('refresh');
		if(messageType=='ai'){
			$('#insertBtn').text("Add Kama-DEI Messages");
			$("#botMessage table th:first-child").css("display", "none");
			$("#botMessage table td:first-child").css("display", "none");
//			$("#botMessage table th:nth-child(3)").css("display","none");
//			$("#botMessage table td:nth-child(3)").css("display","none");
			this.pageSort = 'orgName';
		}else{
			$('#insertBtn').text("Add Chatbot Message");
			$("#botMessage table th:first-child").css("display", "");
			$("#botMessage table td:first-child").css("display", "");
//			$("#botMessage table th:nth-child(3)").css("display","");
//			$("#botMessage table td:nth-child(3)").css("display","");
			this.pageSort = 'Name';
		}
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
			this.OrgId = orgID;
			$("#OrgId").append(this.organizations);
			$("#OrgId").prepend("<option value='' data-defultpersona=''>Select ...</option>");
		});
	}

	//------------------------------------------------------------
	getActionFormInput(col, label){
		var input = '';
		switch (col) {
			case 'OrgId':
			case 'Type':{	
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
			}
			case 'Description':{			
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group",
						style: "vertical-align:top",
					})
					.append( $("<label>"+label+"</label>") )
					.append(
						$("<div>").append(
							$("<input>").attr({
								id: col,
								name: col,
								class: 'form-control',
								maxlength: "64",
								placeholder: col
							})
						)
					);
				break;
			}
			case 'Lang':{
				input = $('<div>')
					.attr({class: "col-" + col + " form-group half pull-right",id:col})
					 //.append( $('<label>').text(label) )
					// .append(
					//  	$('<div>').append(
					// 		 $('<input>').attr({ id:col, name:col,  type:"radio" , value:"En" , class:"stv-radio-button"})
							
					// 	"<input type='radio' class='stv-radio-button' name='"+col+"' value='En' id='button1' checked  /><label for='button1'>En</label><input type='radio' class='stv-radio-button' name='"+col+"' value='Fr' id='button2' /><label for='button2'>Fr</label>"
					// 	) .append( $('<label>').attr({for:col}).text("En"))
					// );
				break;	
			}
				
			case 'Message':{
				input = $("<div>")
					.attr({
						class: "col-" + col + " form-group",
						style: "vertical-align:top",
					})
					.append( $("<label style='margin-top:30px;'>"+label+"</label>") )
					.append(
						$("<div>")
							.append(
								$("<textarea>").attr({
									id: col,
									name: col,
									class: 'form-control',
									maxlength: "1024",
									style: "width:100%;min-width:100%;max-width:100%;height:120px;min-height:120px;max-height:120px;",
									placeholder: col
								})
							)
					)
					.append(
						$('<div id="col-emptyMessage" style="text-align:right;margin-top:5px;">')
							.append( $("<label style='margin-right:5px;'>Allow Empty Message</label>") )
							.append(
								$('<input>').attr({ id:'emptyMessage', name:'emptyMessage', 'data-toggle':'toggle', type:"checkbox" })
							)
					);
				break;
			}

			case 'messageVoice':{
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
								maxlength: "1024",
								style: "width:100%;min-width:100%;max-width:100%;height:120px;min-height:120px;max-height:120px;",
								placeholder: col
							})
						)
					);
				break;
			}

			case 'OnOff':{
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
			case 'KaaS3PB':{
				input = $('<div>')
							.attr({class: "col-" + col + " form-group half right"})
							.append( $('<label>').text(label) )
							.append(
								$('<div>').append(
									$('<input>').attr({ id:col, name:col, 'data-toggle':'toggle', type:"checkbox" })
								)
						);
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
							}).append("<option value=''>Select ...</option>"))
						);
				break;
			}
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//----------------------------------------------------
	showAddDialogHandler(){
		this.getLang(this.defaultLang);
		super.showAddDialogHandler();
		$(
			".col-Name #Name"+
			", "+
			".col-OrgId #OrgId"+
			", "+
			".col-Type #Type"
		).prop('disabled', false);
		this.parentId = 0;
		this.lastLang = this.defaultLang;
		this.baseItem.Lang = this.defaultLang;
		$("#emptyMessage").bootstrapToggle("off");
	}
	//------------------------------------------------------------
	getMessage(Code, OrgId, Lang){
		OrgId = ((OrgId==null) ?orgID :OrgId);
		let that = this;
		let data = {Code: Code, OrgId: $("#OrgId").val(), Lang: Lang}
		$.post(apiURL+'/api/dashboard/'+messageType+'message/getmessage', data, function(res){
			if(res.result==0){
				if(res.data==null){
					that.editItem.id=0;
					that.editItem.Description='';
					that.editItem.Message='';
					that.editItem.messageVoice='';
					that.editItem.Lang=Lang;
				}else{ 
					for(let i in res.data){ that.editItem[i] = res.data[i]; } 
					if(that.editItem.is_required==1){
						$("#emptyMessage").bootstrapToggle("off");
						$("#col-emptyMessage").hide();
					}
				}
				$("#Description" ).val(that.editItem.Description);
				$("#Message"     ).val(that.editItem.Message    );
				$("#messageVoice").val(that.editItem.messageVoice    );
				that.editItem.OrgId=OrgId;
				if(that.editItem.OrgId==null){ that.editItem.OrgId=0; }
				$("#OrgId").val(that.editItem.OrgId);
			}else{ showError(res.msg); }
		});
	}
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);
		if(this.baseItem==null){ this.baseItem={}; for(let i in this.editItem){ this.baseItem[i]=this.editItem[i]; } }
		this.baseItem.OrgId = (this.baseItem.OrgId==null) ?0 :this.baseItem.OrgId;
		this.baseItem.Lang = (this.baseItem.Lang==null) ?0 :this.baseItem.Lang;
		this.getLang(this.defaultLang);
		this.getMessage(this.editItem.Code, $("#OrgId").val(), this.defaultLang);//this.editItem.Lang);
		$(
			".col-Name #Name"   + ", "+
//			".col-OrgId #OrgId" + ", "+
			".col-Type #Type"
		).prop('disabled', true);
		this.lastLang = this.defaultLang;
		if(this.editItem.OrgId==null){
			this.editItem.OrgId=0;
			$("#OrgId").val(0);
		}

		$("#emptyMessage").bootstrapToggle("off");

		if(messageType=='bot'){
			$(
				".col-Name" + ", "+
//				".col-Description" + ", "+
				".col-Type" + ", "+
				"#col-emptyMessage"
			).show();
			$(
				".col-messageVoice"
			).hide();
			$( "#Description" ).prop('disabled', true);
			if(this.editItem.Message==null){ $("#emptyMessage").bootstrapToggle("off"); }
			
			if(orgID==0){ $("#Description").prop('disabled', false); }
			$("#Description").prop('disabled', true);
		}
		if(messageType=='ai'){
			$(
				".col-Name" + ", "+
//				".col-Description" + ", "+
				".col-Type" + ", "+
				"#col-emptyMessage"
			).hide();
			$(
				".col-messageVoice"
			).show();
			$( "#Description" ).prop('disabled', true);
		}
	}
	editConfirmHandler(e){
		if($("#OrgId").val()==0 && messageType=='bot'){
			var that = this;
			showConfirm(
				function(flag){ if(flag==true){ that.sendToEdit(e); } },
				"You are updating a parent message that can affect many organizations, are you sure?",
				"Yes",
				"No"
			);
		}else{ this.sendToEdit(e); }
	}
	sendToEdit(e){ super.editConfirmHandler(e); }
	//------------------------------------------------------------
	get getURL(){
		return apiURL + '/api/dashboard/'+ messageType +'message/page/' +
			((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' +
			this.pageNumber + '/' +
			ownersList;
	}
	//------------------------------------------------------------
	get searchURL(){
		return apiURL + '/api/dashboard/'+ messageType +'message/' +
			((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + ownersList + '/' +
			this.columns.searchColumn + '/' + this.search;
	}
	//------------------------------------------------------------
	get addURL  (){ return apiURL + '/api/dashboard/'+ messageType +'message/new/'+ ((this.orgID) ?(this.orgID+'/') :''); }
	get editURL (){
		return apiURL + '/api/dashboard/'+ messageType +'message/edit/' + 
			((this.orgID) ?(this.orgID+'/') :'') + 
			this.editItem[this.columns.primaryColumn];
	}
	get deleteURL(){
		return apiURL + '/api/dashboard/'+ messageType +"message/delete/" + 
			((this.orgID) ?(this.orgID+'/') :'')+this.deleteId;
	}
	//------------------------------------------------------------
	getLang(item){
		let orgId = $("#OrgId").val();
		if(orgId==null){ orgId=orgID; }
		
		$("#Lang").empty();
		$.get(apiURL+'/api/dashboard/'+messageType+'message/getLang/'+orgId, function(res){
			if(res.result==0){
				for(let i in res.data){			
						$("#Lang")
							.append(
								$("<input>")
									.attr({
										type: 'radio',
										class: 'stv-radio-button edit',
										name: 'Lang',
										id: res.data[i].caption,
										value: res.data[i].value,
									})
							)
							.append("<label for='"+res.data[i].caption+"'>"+res.data[i].caption+"</label>");
					 if(item==res.data[i].value){
						$("#"+res.data[i].caption).attr('checked', 'checked');
					}
				}
			}
		});
	}
	//------------------------------------------------------------
	getType(){
		$("#Type option").remove();
		$("#Type").append('<option value="">Select ...</option>');
		$.get(apiURL+'/api/dashboard/'+messageType+'message/getType/', function(res){
			if(res.result==0){
				for(let i in res.data){
					$("#Type").append('<option value="'+res.data[i].value+'">'+res.data[i].caption+'</option>');
				}
			}
		});
	}
	//------------------------------------------------------------
}
//----------------------------------------------------------------
var messagingColumns = new Columns([
	{ name: 'id', primary: true, hidden: true },

	{ name: 'Name', display:"Name", editable:true, sortable:true, search:true },
	{ name: 'OrgId', display:"Organization", hidden: true },
	{ name: 'orgName', display:"Organization", editable:false, sortable:true, search:false },

	{ name: 'Description' , display:"Description"  , editable:true , sortable:true , search:true },
	{ name: 'Type'        , display:"Type"         , editable:true , sortable:false, search:false, hidden:true},
	{ name: 'Lang'        , display:"Language"     , editable:true , sortable:false, search:false, hidden:true},
	{ name: 'Message'     , display:"Message"      , editable:true , sortable:false, search:true },
	{ name: 'messageVoice', display:"Message Voice", editable:true , sortable:false, search:true },

	{ name: 'parentId'   , display:"", editable:false, sortable:false , search:false, hidden: true},

	{ name: 'LangList'   , display:"Languages"  , editable:false, sortable:false, search:false },
	{ name: 'Code'       , display:""           , editable:false, sortable:false, search:false, hidden:true },
	
	{ name: 'emptyMessage', display:"", editable:false, sortable:false, search:false, hidden:true },
	{ name: 'is_required' , display:"", editable:false, sortable:false, search:false, hidden:true },
	
]);
var data = {
	columns: messagingColumns,
	apiURL: apiURL + '/api/dashboard/botmessage'
}
//----------------------------------------------------------------
if($("#botMessage").length != 0){
	table = new Messaging(data);
	table.createTable('botMessage');
}
//----------------------------------------------------------------
