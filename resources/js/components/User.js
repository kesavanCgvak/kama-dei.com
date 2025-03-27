import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'
//-------------------------------------------------------------
class User extends DataTable {
	//---------------------------------------------------------
	constructor(data){
		super(data);
		this.showGlobal = false;
		this.resetId = 0;
		this.sensitiveID = 0;
		this.deleteMessage = "";
		this.levels = [];
		this.pageSort = 'id';
		this.getLevels();
		$("#insertBtn").text('Add User');
		$('body').on('click', '.reset-item', (e) => { this.showRestDialogHandler(e) });
		$('body').on('click', '.sensitive-item', (e) => { this.showSensitiveDialogHandler(e) });
		$('body').on('click', "#reset-confirm", (e) => { this.resetConfirmHandler() });
		$('body').on('click', "#sensitive-confirm", (e) => { this.sensitiveConfirmHandler() });
		var icon1 = $('<a></a>').attr({ href: '#', 'data-desc': 'Reset pass', class: 'reset-item' });
		var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'Set password for sensitive data', class: 'sensitive-item' });
		this.actionIcons = this.actionIcons.concat([icon1, icon2]);
		
		$('body').on('change', '#showGlobal', (e) => { 
			if($(this).prop('checked')==true){ 
				$(this).prop('checked', false);
				$("#myOwnersList").prop('disabled', false); 
				$("#myOwnersList").val(-1).change(); 
			}else{ 
				$(this).prop('checked', true);
				$("#myOwnersList").prop('disabled', true); 
				$("#myOwnersList").val(orgID).change(); 
			}
		});
	}
	//---------------------------------------------------------
	rowActions(value, row, index, field) {
		if( orgID!=0 ){ if( orgID!=row.orgID ){ return ''; } }
		if(row.id == KAMARONID) return '';
		//-----------------------------------------------------
		var icons = this.actionIcons;
		//-----------------------------------------------------
		if( row.levelID==1 || row.levelID==4 ){ //row.levelID==1::Administrator      row.levelID==4::Consumer
			var tmpICN = [];
			for (var i in icons){ if(icons[i].attr('class')!='sensitive-item'){ tmpICN.push(icons[i]); } }
			icons = tmpICN;
		}
		if(icons.length==0){ return ''; }
		//-----------------------------------------------------
		for (var i in icons){ if( icons[i].attr('class')=='reset-item' ){ icons[i].attr('data-itemid', row[this.columns.primaryColumn]); } }
		//-----------------------------------------------------
		var rowAction = '<div class="row-actions"></div>';
		//----------------------------------------------------------
		var others = '<ul class="menu-actions" data-menu data-menu-toggle="#actions-menu-'+index+'" style="font-size:12px;"></ul>';
		for (var i in icons){
			icons[i].attr('data-itemid', row[this.columns.primaryColumn]);
			var $icon = icons[i].clone();
			$icon = $icon.append('&nbsp;&nbsp;'+$icon.data('desc'));
			others = $(others).append('<li>'+$icon[0].outerHTML+'</li>');
		}
		var toggle = '<a href="#" class="toggle" id="actions-menu-'+index+'" style="color:dimgray"><small class="glyphicon glyphicon-chevron-down"></small></a>';
		var othersIcon = '<span>'+toggle+'</span>';
		rowAction = $(rowAction).append(othersIcon);
		$("body").append(others);
		$(document).ready(function(e){ $("[data-menu]").menu(); });
		//----------------------------------------------------------
		return $(rowAction)[0].outerHTML;
		//----------------------------------------------------------
	}
	//---------------------------------------------------------

	//---------------------------------------------------------
	isValidEmail( data ){ return /^([\w\-\.]+)@((\[([0-9]{1,3}\.){3}[0-9]{1,3}\])|(([\w\-]+\.)+)([a-zA-Z]{2,4}))$/.test( data.trim() ); }
	//---------------------------------------------------------

	//---------------------------------------------------------
	getLevels() {
		var url = apiURL+'/api/dashboard/level/all/' + this.orgID;

		$.get(url, (res) => {
			this.levels = this.createSelectOptions(res.data, 'id', 'levelName');
			$("#levelID").append(this.levels);
			$("#levelID option[value=4]").prop('disabled', true);
		});
	}
	//---------------------------------------------------------
	cellRenderer(value, row, index, field) {
		if(field == 'isAdmin') return this.checkCell(value);
		if(field == 'isActive') return this.checkCell(value);
		if(field == 'isConsumer') return this.checkCell(value);
		if(field == 'createAt'){
			if(value==null || value.length<11) return value;
			return value.substr(0,10)+'<br/>'+value.substr(11);
		}
		return super.cellRenderer(value, row, index, field);
	}
	//---------------------------------------------------------
	checkCell(value){ if(value == '1'){ return "<span class='glyphicon glyphicon-ok' style='color:green'></span>"; }else{ return ""; } }
	//---------------------------------------------------------
	getActionFormInput(col, label) {
		switch(col) {
			case 'orgID':
				return this.ownerIdFormInput(col, label);
			break;

			case 'levelID':
				return this.selectFormInput(col, label, this.levels);
			break;

			case 'isActive':
				return this.isActive(col, label);
			case 'isConsumer':
				return this.isConsumer(col, label);
			break;

			default:
				return super.getActionFormInput(col, label);
		}
	}
	//---------------------------------------------------------
	isActive(col, label) {
		var row = (
		  "<div class='col-" + col + " form-group'>" +
			"<label style='margin-right:5px' for='"+col+"1' class='isactive'>Active</label>" +
			"<input type='radio' name='"+col+"' id='"+col+"1' style='width:auto !important' value='1' />" +

			"<label style='margin-right:5px;margin-left:50px' for='"+col+"0' class='isactive'>Disabled</label>" +
			"<input type='radio' name='"+col+"' id='"+col+"0' style='width:auto !important' value='0' />" +
		  "</div>"
		);

		return row;
	}
	//---------------------------------------------------------
	isConsumer(col, label) {
		var row = (
		  "<div class='col-" + col + " form-group'>" +
			"<input type='checkbox' name='"+col+"' id='"+col+"_1' style='width:auto !important' disabled />"+
			"<label style='margin-left:5px' for='"+col+"_1' class='isConsumer'>Is Consumer</label>" +
		  "</div>"
		);

		return row;
	}
	//---------------------------------------------------------
	showDeleteDialogHandler(e) {
		e.preventDefault();
		this.deleteId = $(e.currentTarget).data('itemid');
		if($(e.currentTarget).data('userpersonality')==''){
			$('#deleteDialog .msg').html('Are you sure you want to delete this item?');
			$('#deleteDialog #delete-confirm').html("Yes");
		}
		else{
			$('#deleteDialog .msg').html("The personality associated with this user will also be deleted. Please click on 'okay' to proceed.");
			$('#deleteDialog #delete-confirm').html("Okay");
		}
		$('#deleteDialog').fadeIn();
	}
	//---------------------------------------------------------
	deleteDialog() {
		var dialog = "<div id='deleteDialog' style='display:none'></div>";
		var inner = "<div></div>";
		var msg = "<div  class='msg'></div>";
		var actions = "<div class='deleteActions'></div>";
		var yes = $("<button>Yes</button>").attr({
			id: 'delete-confirm',
			class: 'btn btn-primary',
			style:'width:75px;'
		});
		var no = $("<button>Cancel</button>").attr({
			class: 'btn btn-default',
			onClick: "$('#deleteDialog').fadeOut()",
			style:'width:75px;'
		});
		actions = $(actions).append(yes).append(no);
		inner = $(inner).append(msg).append(actions);
		dialog = $(dialog).append(inner);
		return dialog;
	}
	//---------------------------------------------------------
	deleteConfirmHandler(){
		$("#deleteDialog").fadeOut();
		var table = this.table;

		$.ajax({
			url: this.deleteURL,
			type: 'delete',
			dataType: 'json',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			success: function(res){
				if(res.result == 1){ showError(res.msg); }
				else{
					showSuccess(res.msg);
					$(table).bootstrapTable('refresh');
				}
			},
			error: function(e){ showError('Server Error'); }
		})
	}
	//---------------------------------------------------------
	actionForm() {
		var submitLabel = 'Save Item';
		var submitId = 'saveItem';
		//-----------------------------------------------------
		var formChildren = [];
		var columns = this.columns;
		var data = columns.data;
		for(var x in data) {
			var column = data[x];
			if(column.editable === false || column.reserved === true) continue;
			var col = column.name;
			var label = column.display;
			if(column.onlyFor == null || column.onlyFor == this.orgID){ formChildren.push(this.getActionFormInput(col, label)); }
			if(col=='email'){ formChildren.push(this.getActionFormInput('personalityTemp', "Personality")); }
		}
		//-----------------------------------------------------
		if(columns.reservedColumn !== null){ formChildren.push(this.getActionFormInput(columns.reservedColumn, 'Reserved')); }
		//-----------------------------------------------------
		var wrapper = $("<div>").attr({ id: 'editItem' });
		var form = $("<form>").attr({ class: 'action-form' });
		//-----------------------------------------------------
		$(formChildren).each(function(i, el){ form = $(form).append(el); });
		//-----------------------------------------------------
		var submit = $("<div>")
						.append($("<input>")
								  .attr({
										id: submitId,
										type: 'submit',
										value: submitLabel,
										class: 'btn btn-primary'
									})
						);
		//-----------------------------------------------------
		var cancel = $("<div style='margin-top: 5px'></div>")
						.append($("<input>")
								  .attr({
										type: 'button',
										value: 'Cancel',
										class: 'btn btn-default',
										onClick: "$('#editItem').fadeOut()"
									})
						);
		//-----------------------------------------------------
		form = $(form).append([submit, cancel]);
		wrapper = $(wrapper).append(form);
		//-----------------------------------------------------
		return wrapper;
		//-----------------------------------------------------
	}
	//---------------------------------------------------------

	//---------------------------------------------------------
	formInputChangeHandler(e){
		var target = e.currentTarget;
		if(
			target.name=='id' ||
			target.name=='userPersonality' ||
			target.name=='userID'
		){ this.editItem[target.name] = target.value; }

/*
		var target = e.currentTarget;
		var value = target.value;
		if(target.type == 'checkbox'){ value = (target.checked)? '1':'0'; }
		this.editItem[target.name] = value;
*/
	}
	//---------------------------------------------------------

	//---------------------------------------------------------
	editConfirmHandler(e){
		e.preventDefault();
//		if(!this.isValidEmail(this.editItem['email'])){
		if( !this.isValidEmail( $('#email').val().trim() ) ){
			showError('Invalid email address');
			return;
		}
		var table = this.table;
		var data = {
			orgID: this.orgID,
			userID: this.userID
		};
		for(var x in this.columns.names){
			if(this.columns.data[x].passData !== false) {
				var name = this.columns.names[x];
				var value = '';
				switch(name){
					case 'id':
					case 'userID':
					case 'userPersonality':
						value = this.editItem[name];
					break;
					case 'isActive':
						if( $("#isActive1").prop('checked') ){ value = 1; }
						if( $("#isActive0").prop('checked') ){ value = 0; }
					break;
					case 'ownerId':
						value = $("#ownerId").val().trim();
						if( value == null ){ value = '0'; }
					break;
					case 'levelID':
						value = $("#levelID").val();
						if(value==null){ value=4; }
					break;
					default:
						value = $("#"+name).val().trim();
					break;
				}
				data[name] = value;
			}
		}
		//-----------------------------------------------------
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
					$(table).bootstrapTable('refresh');
				}else{
					showError(res.msg);
					$("#editItem #saveItem").prop('disabled', false);
				}
			},
			error: function(e){
				showError('Server error');
				$("#editItem #saveItem").prop('disabled', false);
			}
		});
	}
	//---------------------------------------------------------

	//---------------------------------------------------------
	addConfirmHandler(e){
		e.preventDefault();
//		if(!this.isValidEmail(this.editItem['email'])){
		if( !this.isValidEmail( $('#email').val().trim() ) ){
			showError('Invalid email address');
			return;
		}
		var table = this.table;
		var data = {
			orgID: this.orgID,
			userID: this.userID
		};
		for(var x in this.columns.names){
			if(this.columns.data[x].passData !== false) {
				var name = this.columns.names[x];
				var value = '';
				switch(name){
					case 'id':
					case 'userID':
					case 'userPersonality':
						value = this.editItem[name];
					break;
					case 'isActive':
						if( $("#isActive1").prop('checked') ){ value = 1; }
						if( $("#isActive0").prop('checked') ){ value = 0; }
					break;
					case 'ownerId':
						value = $("#ownerId").val().trim();
						if( value == null ){ value = '0'; }
					break;
					default:
						value = $("#"+name).val().trim();
					break;
				}
				data[name] = value;
			}
		}
		//----------------------------------------------------------
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
	}
	//---------------------------------------------------------

	//---------------------------------------------------------
	showEditDialogHandler(e){
		//-----------------------------------------------------
		e.preventDefault();
		$(".col-personalityTemp.form-group").show();
		$(".col-id.form-group").show();
		this.changeFormMode('edit');
		$('#editItem').fadeIn();
		$("#editItem #saveItem"  ).prop('disabled', false);
		$.each(this.rows, (i, item) => {
			if(item[this.columns.primaryColumn] == $(e.currentTarget).data('itemid')){
				this.editItem = item;
				return false;
			}
		});
		//-----------------------------------------------------
		$("#editItem [name]").each((i, el) => {
			var val = '';
			switch(el.name){
				case 'id':
					el.value = this.editItem[el.name];
					el.disabled=true;
					break;

				case 'personalityTemp':
					el.value = this.editItem['userPersonality'];
					el.disabled=true;
					break;

				case 'ownerId':
					el.value = (this.editItem[el.name] == null)? '0':this.editItem[el.name];
					break;

				case 'isActive':
					if(this.editItem[el.name]==1){ $("#isActive1").prop('checked', true); }
					else{ $("#isActive0").prop('checked', true); }
					break;
				case 'isConsumer':
					if(this.editItem[el.name]==1){ $("#isConsumer_1").prop('checked', true); }
					else{ $("#isConsumer_1").prop('checked', false); }
					break;

				case this.columns.ownershipColumn:
					$("#"+this.columns.ownershipColumn+this.editItem[el.name]).click();
					break;

				case this.columns.reservedColumn:
					var checked = (this.editItem[this.columns.reservedColumn] == 1)? true:false;
					$("#"+this.columns.reservedColumn).attr('checked', checked);
					break;

				default:
					el.value = this.editItem[el.name];
			}
		});
		//-----------------------------------------------------
	}
	//---------------------------------------------------------
	showAddDialogHandler(){
		//----------------------------------------------------------
		$("#editItem").fadeIn();
		this.editItem = {};
		$(".col-personalityTemp.form-group").hide();
		$(".col-id.form-group").hide();
		//----------------------------------------------------------
		$.each(this.columns.data, (i, col) => {
			var el = $("[name='"+col.name+"']")[0];
			if(!el) el = {};
			switch(col.name){
				case this.columns.reservedColumn:
					el.checked = false;
					this.editItem[this.columns.reservedColumn] = '0';
					break;

				case this.columns.ownershipColumn:
					$("#"+this.columns.ownershipColumn+"0").click();
					this.editItem[this.columns.ownershipColumn] = '0';
					break;

				case 'isActive':
					$("#isActive1").prop('checked', true);
					break;

				case 'ownerId':
					el.value = this.orgID;
					this.editItem['ownerId'] = this.orgID;
					break;

				default:
					if(el.tagName != 'SELECT'){
						el.value = (col.default != '')? col.default:'';
						this.editItem[col.name] = (col.default != '')? col.default:'';
					}else{
						el.value = $(el).find('option:first-child').val();
						this.editItem[col.name] = $(el).find('option:first-child').val();
					}
			}
		});
		//----------------------------------------------------------
		$("#editItem [name]").each((i, el) => {
			switch(el.name){
				case this.columns.reservedColumn:
					el.checked = false;
					this.editItem[this.columns.reservedColumn] = '0';
					break;

				case this.columns.ownershipColumn:
					$("#"+this.columns.ownershipColumn+"0").click();
					this.editItem[this.columns.ownershipColumn] = '0';
					break;

				case 'ownerId':
					el.value = this.orgID;
					this.editItem['ownerId'] = this.orgID;
					break;

				case 'isConsumer':
					$("#isConsumer_1").prop('checked', false);
					break;
				default:
					if(el.tagName != 'SELECT'){
						el.value = '';
						this.editItem[el.name] = '';
					}else{
						el.value = $(el).find('option:first-child').val();
						this.editItem[el.name] = $(el).find('option:first-child').val();
					}
			}
		});
		//----------------------------------------------------------
		this.changeFormMode('add');
		//----------------------------------------------------------
	}
	//--------------------------------------------------------------
	deleteDialog() {
		var dialog = "<div id='deleteDialog' style='display:none'></div>";
		var inner = "<div></div>";
		var msg = "<div>Are you sure you want to delete this item?</div>";
		var actions = "<div class='deleteActions'></div>";
		var yes = $("<button>Yes</button>").attr({
			id: 'delete-confirm',
			class: 'btn btn-primary'
		});
		var no = $("<button>Cancel</button>").attr({
			class: 'btn btn-default',
			onClick: "$('#deleteDialog').fadeOut()"
		});
		actions = $(actions).append(yes).append(no);
		inner = $(inner).append(msg).append(actions);
		dialog = $(dialog).append(inner);
		return dialog;
	}
	//--------------------------------------------------------------
	resetDialog() {
		var dialog = "<div id='resetDialog' style='display:none'></div>";
		var inner = "<div></div>";
		var msg = "<div>Are you sure you want to reset the password of this user?</div>";
		var actions = "<div class='resetActions'></div>";
		var yes = $("<button>Yes</button>").attr({
			id: 'reset-confirm',
			class: 'btn btn-primary'
		});
		var no = $("<button>No</button>").attr({
			class: 'btn btn-default',
			onClick: "$('#resetDialog').fadeOut()"
		});
		actions = $(actions).append(yes).append(no);
		inner = $(inner).append(msg).append(actions);
		dialog = $(dialog).append(inner);
		return dialog;
	}
	//--------------------------------------------------------------
	sensitiveDialog() {
		var dialog = "<div id='sensitiveDialog' style='display:none'></div>";
		var inner = "<div></div>";
		var header = "<div style='padding:10px 0 15px;'>Set sensitive password</div>";
		var inputDIV = "<div style='padding:10px 0 15px;'></div>";
		var input = "<input type='text' class='form-control' value='' placeholder='sensiive password' id='sensitivePasswordValue' disabled maxlength=10 />";
		
		var actions = "<div class='sensitiveActions'></div>";
		var yes = $("<button>Yes</button>").attr({
			id: 'sensitive-confirm',
			class: 'btn btn-primary'
		});
		var no = $("<button>No</button>").attr({
			class: 'btn btn-default',
			onClick: "$('#sensitiveDialog').fadeOut()"
		});
		actions = $(actions).append(yes).append(no);
		inputDIV = $(inputDIV).append(input);
		inner = $(inner).append(header).append(inputDIV).append(actions);
		dialog = $(dialog).append(inner);
		return dialog;
	}
	//--------------------------------------------------------------
	showRestDialogHandler(e) {
		e.preventDefault();
		this.resetId = $(e.currentTarget).data('itemid');
		$('#resetDialog').fadeIn();
	}
	//--------------------------------------------------------------
	showSensitiveDialogHandler(e) {
		$("#sensitivePasswordValue").val('');
		$("#sensitivePasswordValue").prop('disabled', true);
		e.preventDefault();
		this.sensitiveID = $(e.currentTarget).data('itemid');
		$.post(apiURL+'/api/dashboard/data_classification/getpass/'+this.sensitiveID,(dataIn) => {
			$("#sensitivePasswordValue").val(dataIn.password);
			$("#sensitivePasswordValue").prop('disabled', false);
			$("#sensitivePasswordValue").focus();
		});
		$('#sensitiveDialog').fadeIn();
	}
	//--------------------------------------------------------------
	resetConfirmHandler(){
		$("#resetDialog").fadeOut();
		var table = this.table;

		$.ajax({
			url: this.apiURL + "/reset/" + this.resetId,
			type: 'post',
			dataType: 'json',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			success: function(res){
				if(res.result == 1){ showError(res.msg); }
				else{ showSuccess(res.msg); }
			},
			error: function(e){ showError('Server Error'); }
		})
	}
	//---------------------------------------------------------
	sensitiveConfirmHandler(){
		if( $("#sensitivePasswordValue").val().trim()=='' ){
			showError('Invalid password');
			return;
		}

		$("#sensitiveDialog").fadeOut();
		var table = this.table;

		$.ajax({
			url: apiURL + "/api/dashboard/data_classification/setpass/" + this.sensitiveID,
			type: 'put',
			data: JSON.stringify({ pass: $("#sensitivePasswordValue").val().trim(), userID: this.userID }),
			dataType: 'json',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			success: function(res){
				if(res.result == 1){ showError(res.msg); }
				else{ showSuccess('Item saved.'); }
			},
			error: function(e){ showError('Server Error'); }
		})
	}
	//---------------------------------------------------------
	createTable(id){
		super.createTable(id);
	    $(this.container).append(this.resetDialog());
	    $(this.container).append(this.sensitiveDialog());
	}
	//--------------------------------------------------------------
	get getURL() { 
		return this.apiURL + 
				'/page/' + ((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' + this.pageNumber +
				'/ownerId/' + $("#myOwnersList").val() + '/level/'+$("#myFilterBy").val();
	}
	get searchURL() {
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
			this.search+ '/ownerId/'+$("#myOwnersList").val() + '/level/'+$("#myFilterBy").val();

	}
}
//------------------------------------------------------------------
var columns = [
	{ name: 'id', display: 'User ID', primary: true, hidden: false, search:false, editable:false, sortable:true },
	{ name: 'userName', display: 'Name', sortable: true, search: true },
	
	{ name: 'nickname', display: 'Nickname', sortable: true, search: true, editable:false, passData: false },
	{ name: 'userPersonality', display: 'Personality Name', sortable:false, search:false, editable:false, passData: false },
	{ name: 'levelName', display: 'Level', sortable: true, editable: false, passData: false },
	{ name: 'isConsumer', display: 'is Consumer', sortable: false, editable: false, passData: false },
	
	{ name: 'email', display: 'Email', sortable: true },
	{ name: 'orgID', display: 'Organization', hidden: true, default: 0 },
	{ name: 'organizationShortName', display: 'Organization', editable: false, passData: false },
	{ name: 'levelID', display: 'Level', hidden: true },
//	{ name: 'isAdmin', display: 'Admin', sortable: true, editable: false, passData: false },
	{ name: 'isActive', display: 'Active', sortable: false, editable: true },
	{ name: 'isConsumer', display: 'Is Consumer', sortable: false, editable: true, passData: false, hidden: true },
	{ name: 'createAt', display: 'Created', sortable: true, editable: false, passData: false },
	{ name: 'organization', hidden: true, editable: false, passData: false },
	{ name: 'level', hidden: true, editable: false, passData: false },
//  { name: 'userPass', hidden: true, display: 'Password', editable: true, passData: true },
];
var userColumns = new Columns(columns);
var data = {
	columns: userColumns,
	apiURL: apiURL + '/api/dashboard/user'
}
//-------------------------------------------------------------
if($("#users").length != 0){
	var table = new User(data);
	table.createTable('users');
}
//-------------------------------------------------------------
