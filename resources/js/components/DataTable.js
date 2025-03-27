import "bootstrap-table"
import "bootstrap-table/dist/bootstrap-table.min.css"
import "./css/DataTable.css"
import Toastify from 'toastify-js'
import Slider from 'jquery-ui-bundle'
import 'jquery-ui-bundle/jquery-ui.min.css'

import "./dropdown-plugin/menu.min.css"
import "./dropdown-plugin/menu.min.js"

import "./bootstrap-toggle-master/bootstrap-toggle.min.css"
import "./bootstrap-toggle-master/bootstrap-toggle.min.js"

import "bootstrap-table/src/extensions/reorder-rows/bootstrap-table-reorder-rows.css"
import "bootstrap-table/src/extensions/reorder-rows/bootstrap-table-reorder-rows.js"
import "./extend/jquery.tablednd.js"

export default class DataTable {
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	constructor(data) {
		this.apiURLBase = apiURL;
		this.apiURL = data.apiURL;
		this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;
		this.userID = (typeof(userID) != 'undefined')? userID:null;
		this.showRefresh = false;
		this.columns = data.columns;
		this.rows = [];

		this.addBtnCaption = "Add Item";
		this.hasRowActions = true;
		this.hasSearch = true;
		this.hasPagination = true;
		this.hasInsertRow = true;
		this.showGlobal = false;

		this.pageSize = 10;
		this.pageNumber = 1;
		this.pageSort = data.columns.primaryColumn;
		this.pageOrder = 'asc';
		this.search = '';

		this.table = '';
		this.tableReorderRow = false;

		this.deleteId = '';
		this.editItem = null;
		this.baseItem = null;

		this.totalAllResponse = -1;
		
		var deleteIcon = $("<a></a>").attr({
			href: '#',
			style: "color:#f3ae4e;",
			class: 'delete-item',
			'data-desc': 'Delete',
			'data-onlyowner': 1
		});
		var editIcon = $('<a></a>').attr({
			href: '#',
			style: "color:#2196f3;",
			class: 'edit-item',
			'data-desc': 'Edit',
			'data-onlyowner': 1
		});
		this.actionIcons = [deleteIcon, editIcon];

		$('body').on('click', '.delete-item', (e) => { this.showDeleteDialogHandler(e) });
		$('body').on('click', '.edit-item', (e) => { this.showEditDialogHandler(e) });
		$('body').on('click', '#insertBtn', (e) => { this.showAddDialogHandler() });
		$('body').on('click', "#delete-confirm", (e) => { this.deleteConfirmHandler() });
		$('body').on('click', "#saveItem", (e) => { this.editConfirmHandler(e); this.baseItem=null; });
		$('body').on('click', "#insertItem", (e) => { this.addConfirmHandler(e); this.baseItem=null; });
		$('body').on('change input', '#editItem [name]', (e) => { this.formInputChangeHandler(e); });
		$('body').on('click', '#editItem .btnclose', () => { 
			$('#editItem').fadeOut(); 
			if(this.baseItem!=null){ for(let i in this.baseItem){ this.editItem[i]=this.baseItem[i]; } }
			this.baseItem=null;
		});

		this.getOrganizations();
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	get addURL   () { return this.apiURL+'/new/' + ((this.orgID) ? (this.orgID + '/') : ''); }
	get editURL  () { return this.apiURL+'/edit/' + ((this.orgID) ? (this.orgID + '/') : '') + this.editItem[this.columns.primaryColumn]; }
	get deleteURL() { return this.apiURL + "/delete/" + ((this.orgID) ? (this.orgID + '/') : '') + this.deleteId; }
	get getURL   () { 
		return this.apiURL+'/page/'+ ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize +'/' + 
			this.pageNumber + '/';
	}
	get searchURL() {
	return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
		this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
		this.search;
	}
	get organizationURL() { return this.apiURLBase + '/api/dashboard/organization/all/' + this.orgID }
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	get getColumns() {
		var columns = [];
		for(var x in this.columns.data){
			var column = this.columns.data[x];
			if(column.hidden === true && column.primary !== true) continue;
			var obj = {
				//        editable: true,
				field: column.name,
				title: column.display,
				sortable: (column.sortable)? true:false,
				visible: (column.hidden === true)? false:true,
				formatter: (value, row, index, field) => this.cellRenderer(value, row, index, field)
			}
			if(column.width !== null) obj.width = column.width;
			columns.push(obj);
		}
		if(this.hasRowActions) {
			columns.push({
				field: 'actions',
				formatter: (value, row, index, field) => this.rowActions(value, row, index, field)
			})
		}
		return columns;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	createTable(id){
		this.container = "#" + id;
		$(this.container).html('<table></table>');
		$(this.container).append(this.deleteDialog());
		$(this.container).append(this.actionForm());
		$(this.container).append(this.tableToolbar());
		
		this.table = "#" + id + " table";
		var DataTableConstant = this;
		$(this.table).bootstrapTable({
			url: this.getURL,
			columns: this.getColumns,
			sidePagination: 'server',
			pagination: this.hasPagination,
			silentSort: false,
			cache: false,
			search: this.hasSearch,
			toolbar: "#tableToolbar",
			pageSize: this.pageSize,
			pageNumber: this.pageNumber,
			sortName: this.pageSort,
			sortOrder: this.pageOrder,
			showRefresh: this.showRefresh,
			queryParams: function(params){ DataTableConstant.queryParams(params, this); },
			responseHandler: (res) => this.responseHandler(res),
			
			rowReorder: this.tableReorderRow,
			reorderableRows: this.tableReorderRow,
			onReorderRowsDrag: this.dataTableOnReorderRowsDrag,
			onReorderRowsDrop: this.dataTableOnReorderRowsDrop,
			onReorderRow: this.dataTableOnReorderRow
		});
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	dataTableOnReorderRowsDrag(table, row){ return false; }
	dataTableOnReorderRowsDrop(table, row){ return false; }
	dataTableOnReorderRow(newData){}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	responseHandler(res){
		res.rows = res.data;
		this.rows = res.rows;
		try{ this.totalAllResponse = res.total; }
		catch(ex){ this.totalAllResponse = -1; }
		return res;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	tableToolbar(){
		var toolbar = $("<div>").attr({ id: 'tableToolbar' });
		if(this.hasInsertRow){
			toolbar.append(
				$("<button>"+this.addBtnCaption+"</button>").attr({
					id: 'insertBtn',
					class: 'btn btn-primary'
				})
			);
		}
		if(this.showGlobal){
			toolbar.append(
				$("<label>Show Global:</label>").attr({
					style:'margin:0 2px 0 7px;font-weight: normal;'
				})
			);
			toolbar.append(
				$("<input checked >").attr({
					id  : 'showGlobal',
					type: 'checkbox' ,
					'data-onstyle': 'info',
					'data-toggle' : 'toggle',
					'data-size'   : 'small',
					'data-on'     : 'Yes',
					'data-off'    : 'No'
				})
			);
		}
		return toolbar;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showAddDialogHandler(){
		//----------------------------------------------------------
		$("#editItem").fadeIn();
		this.editItem = {};
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
					$("#"+this.columns.ownershipColumn+"2").click();
					this.editItem[this.columns.ownershipColumn] = '2';
					break;

				case 'ownerId':
					el.value = orgID;
					this.editItem['ownerId'] = orgID;
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
					$("#"+this.columns.ownershipColumn+"2").click();
					this.editItem[this.columns.ownershipColumn] = '2';
					break;

				case 'ownerId':
					el.value = orgID;
					this.editItem['ownerId'] = orgID;
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

	//--------------------------------------------------------------
	changeFormMode(mode = 'edit'){
		if(mode == 'edit'){
			$("#editItem [type='submit']").attr('id', 'saveItem').val('Save Item');
		}
		else if(mode == 'add'){
			$("#editItem [type='submit']").attr('id', 'insertItem').val('Add Item');
		}
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	cellRenderer(value, row, index, field){
		var column = null;
		for(var x in this.columns.names){
			if(this.columns.names[x] == field) {
				column = this.columns.data[x];
				break;
			}
		}
		if(column.reserved === true) return this.checkCell(value, row, column);
		if(column.ownership === true) return this.ownershipCell(value, row, column);
		if(column.date === true) return this.dateCell(value, row, column);
		return value;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	rowActions(value, row, index, field) {
		//----------------------------------------------------------
		var icons = this.actionIcons;
		$("[data-menu-toggle='#actions-menu-"+index+"']").remove();
		//----------------------------------------------------------
		if( orgID!=0 ){
			if( row.ownerId==null || orgID!=row.ownerId ){
				var tmpICN = [];
				var icons = this.actionIcons;
				for (var i in icons){ if(icons[i].data('onlyowner')!=1){ tmpICN.push(icons[i]); } }
				icons = tmpICN;
			}
		}
		if(icons.length==0){ return ''; }
		//----------------------------------------------------------
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
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	checkCell(cell, row, column) {
		if(cell == '1'){
			return "<span class='glyphicon glyphicon-ok' style='color:green'></span>";
		}else{
			return "<span class='glyphicon glyphicon-minus' style='color: #adadad'></span>";
		}
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	ownershipCell(cell, row, column) {
		if (cell == 0) return 'Public';
		else if (cell == 1) return 'Protected';
		else if (cell == 2) return 'Private';
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	dateCell(cell, row, column) {
		return cell.split(' ')[0];
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	queryParams(params, bootstrapTable) {
		//----------------------------------------------------------
		let tText  = params.search;
		if(this.hasSearch){
			let tArray = tText.split(" ");
			let nArray = [];
			for(let i in tArray){
				if(tArray[i]!=''){ nArray.push(tArray[i]); }
			}
			tText = nArray.join(" ");
		}
		//----------------------------------------------------------
//		this.search = encodeURIComponent( params.search );
		this.search = encodeURIComponent( tText );
		this.pageSize = params.limit;
		this.pageNumber = (params.offset/params.limit) + 1;
		this.pageSort = params.sort;
		this.pageOrder = params.order;
		//----------------------------------------------------------
		bootstrapTable.url = (this.search == '' || this.search == null || typeof(this.search) == 'undefined')? this.getURL:this.searchURL;
		//----------------------------------------------------------
		return params;
		//----------------------------------------------------------
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	refreshOptions(){
		var url = (this.search == '')? this.getURL: this.searchURL;
		$(this.table).bootstrapTable('refreshOptions', {url: url});
	}
	//--------------------------------------------------------------

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
			class: 'btn btn-danger',
			onClick: "$('#deleteDialog').fadeOut()"
		});
		actions = $(actions).append(yes).append(no);
		inner = $(inner).append(msg).append(actions);
		dialog = $(dialog).append(inner);
		return dialog;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	getOrganizations() {
		$.get(this.organizationURL, (res) => {
			this.organizations = this.createSelectOptions(res.data, 'organizationId', 'organizationShortName');
			this.ownerId = orgID;
			$("#orgID, #ownerId").append(this.organizations);
		});
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	createSelectOptions(arr, valKey, labelKey) {
		var options = [];
		for(var i in arr){
			var value = arr[i][valKey];
			var label = arr[i][labelKey];
			options.push("<option value='"+value+"'>"+label+"</option>");
		}
		return options;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	actionForm() {
		var submitLabel = 'Save Item';
		var submitId = 'saveItem';

		var formChildren = [];
		var columns = this.columns;
		var data = columns.data;
		for(var x in data) {
			var column = data[x];
			if(column.editable === false || column.primary === true || column.reserved === true) continue;
			var col = column.name;
			var label = column.display;
			if(column.onlyFor == null || column.onlyFor == orgID){
				formChildren.push(this.getActionFormInput(col, label));
			}
		}

		if(columns.reservedColumn !== null) {
			formChildren.push(this.getActionFormInput(columns.reservedColumn, 'Reserved'));
		}

		var wrapper = $("<div>").attr({ id: 'editItem' });
		var form = $("<form>").attr({ class: 'action-form', onSubmit:"return false;" });

		$(formChildren).each(function(i, el){
			form = $(form).append(el);
		});

		var hint = $("<div><b style='color:red;font-size:18px;vertical-align:bottom'>*</b>: Editing this field<small>(s)</small> requires special password.</div>")
						.attr({style:"font-size:12px;padding:8px 0 15px;"});
		var submit = $("<div>")
						.append($("<input>")
								  .attr({
										id: submitId,
										type: 'submit',
										value: submitLabel,
										class: 'btn btn-primary'
									})
						);

		var cancel = $("<div style='margin-top: 0px'></div>")
						.append($("<input>")
								  .attr({
										type: 'button',
										value: 'Cancel',
										class: 'btn btn-danger btnclose'
									})
						);

//		form = $(form).append([hint, submit, cancel]);
		form = $(form).append([submit, cancel]);
		wrapper = $(wrapper).append(form);

		return wrapper;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	formInputChangeHandler(e){
		var target = e.currentTarget;
		var value = target.value;
		if(target.type == 'checkbox'){ value = (target.checked)? '1':'0'; }
		this.editItem[target.name] = value;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	getActionFormInput(col, label) {
		var row='';
		switch (col) {
			case this.columns.reservedColumn:
				row = this.reservedFormInput(col, label);
				break;

			case this.columns.ownershipColumn:
				row = this.ownershipFormInput(col, label);
				break;

			case 'ownerId':
				row = this.ownerIdFormInput(col, label);
				break;

			default:
				row = this.defaultFormInput(col, label);
		}
		return row;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	defaultFormInput(col, label) {
		var row = $("<div>")
			.attr({ class: 'col-' + col + ' form-group' })
			.append("<label>"+label+"</label>")
			.append($("<div>")
				.append($("<input>").attr({
					name: col,
					id: col,
					placeholder: label,
					class: 'form-control'
				})
			));

		return row;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	ownerIdFormInput(col, label) {
		return this.selectFormInput(col, label, this.organizations);
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	selectFormInput(col, label, options) {
		var value = '';

		var row = $("<div>")
					.attr({ class: "col-"+col+" form-group" })
					.append("<label>"+label+"</label>")
					.append($("<div>")
						.append($("<select>").attr({
							id: col,
							name: col,
							value: value,
							class: "form-control"
						})
					));
		return row;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	ownershipFormInput(col, label) {
		var row = "<div class='col-"+col+" form-group'>";
		  row += "<label>"+label+"</label>";
			row += "<div style='text-align:right'>";
			  row += "<div class='btn-group' data-toggle='buttons'>";
				row += "<label class='active btn btn-default'>";
				  row += "<input type='radio' name='"+col+"' id='"+col+"0' value='0' autoComplete='off' checked /> Public";
				row += "</label>";
				row += "<label class='btn btn-default'>";
				  row += "<input type='radio' name='"+col+"' id='"+col+"1' value='1' autoComplete='off' /> Protected";
				row += "</label>";
				row += "<label class='btn btn-default'>";
				  row += "<input type='radio' name='"+col+"' id='"+col+"2' value='2' autoComplete='off' /> Private";
				row += "</label>";
			  row += "</div>";
			row += "</div>";
		row += "</div>";

		return row;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	reservedFormInput(col, label) {
		var row = (
		  "<div class='col-'" + col + " form-group'>" +
			"<input type='checkbox' name='"+col+"' id='"+col+"' style='width:auto' />" +
			"<label style='margin-left:5px' for='"+col+"'>"+label+"</label>" +
		  "</div>"
		);

		return row;
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showDeleteDialogHandler(e) {
		e.preventDefault();
		$('#deleteDialog').fadeIn();
		this.deleteId = $(e.currentTarget).data('itemid');
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	showEditDialogHandler(e) {
		e.preventDefault();
		this.changeFormMode('edit');
		$('#editItem').fadeIn();
		$("#editItem #saveItem"  ).prop('disabled', false);
		$.each(this.rows, (i, item) => {
		  if(item[this.columns.primaryColumn] == $(e.currentTarget).data('itemid')){
			this.editItem = item;
			return false;
		  }
		});

		$("#editItem [name]").each((i, el) => {
		  var val = '';
		  switch(el.name){
			case 'ownerId':
			el.value = (this.editItem[el.name] == null)? '0':this.editItem[el.name];
			break;

			case this.columns.ownershipColumn:
			$("#"+this.columns.ownershipColumn+this.editItem[el.name]).click();
			break;

			case this.columns.reservedColumn:
			var checked = (this.editItem[this.columns.reservedColumn] == 1)? true:false;
			$("#"+this.columns.reservedColumn).prop('checked', checked);
			break;

			default:
			el.value = this.editItem[el.name];
		  }
		});
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
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
					showSuccess('Deleted successfully');
					$(table).bootstrapTable('refresh');
				}
			},
			error: function(e){ showError('Server Error'); }
		})
	}
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	editConfirmHandler(e){
		e.preventDefault();
		var table = this.table;
		var data = {
			orgID: this.orgID,
			userID: this.userID
		};
		for(var x in this.columns.names){
			if(this.columns.data[x].passData !== false) {
				var name = this.columns.names[x];
				var value = this.editItem[name];
				if(name == 'ownerId' && value == null){ value = '0'; }
				data[name] = value;
			}
		}
		//----------------------------------------------------------
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
	//--------------------------------------------------------------

	//--------------------------------------------------------------
	addConfirmHandler(e){
		e.preventDefault();
		var table = this.table;
		var data = {
			orgID: this.orgID,
			userID: this.userID
		};
		for(var x in this.columns.names){
			if(this.columns.data[x].passData !== false){
				var name = this.columns.names[x];
				var value = this.editItem[name];
				if(name == 'ownerId' && value == null){ value = '0'; }
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
	//--------------------------------------------------------------

	//--------------------------------------------------------------
}
//------------------------------------------------------------------
//GLOBAL FUNCTIONS--------------------------------------------------
//------------------------------------------------------------------
function showError(msg) {
	Toastify({
		text: msg,
		duration: 5000,
		close: true,
		gravity: "bottom",
		positionLeft: false,
		backgroundColor: "#f44336"
	}).showToast();
}
//------------------------------------------------------------------

//------------------------------------------------------------------
function showSuccess(msg) {
	Toastify({
		text: msg,
		duration: 5000,
		close: true,
		gravity: "bottom",
		positionLeft: false,
		backgroundColor: "#4CAF50"
	}).showToast();
}
//------------------------------------------------------------------

//------------------------------------------------------------------
function showConfirm(callback, msg, yes='Yes', no='No', classYes="btn-danger"){
	//----------------------------------------------------------
	if($(".mySmallModalLabelBox").length>0){
		var div = ''+
			'<div class="modal-dialog modal-sm">'+
				'<div class="modal-content">'+
					'<div class="modal-header">'+
						'<span class="myModalLabel"></span>'+
						'<div style="width:100%;margin-top:10px;border-top:1px dotted #ccc;padding-top:5px;" align="right">'+
							'<button type="button" class="btn btn-default btn-no" style="width:40%;">' + no + '</button>'+
							'<button type="button" class="btn '+classYes+' btn-yes" style="float:left;width:40%">' + yes + '</button>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>';
		$('.mySmallModalLabelBox').html(div);
	}else{
		var div = ''+
			'<div class="modal fade mySmallModalLabelBox" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabelBox" aria-hidden="true">'+
				'<div class="modal-dialog modal-sm">'+
					'<div class="modal-content">'+
						'<div class="modal-header">'+
							'<span class="myModalLabel"></span>'+
							'<div style="width:100%;margin-top:10px;border-top:1px dotted #ccc;padding-top:5px;" align="right">'+
								'<button type="button" class="btn btn-default btn-no" style="width:40%;">' + no + '</button>'+
								'<button type="button" class="btn '+classYes+' btn-yes" style="float:left;width:40%">' + yes + '</button>'+
							'</div>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>';
		$('body').append(div);
	}
	//----------------------------------------------------------
	$(".mySmallModalLabelBox .myModalLabel").html(msg);
	$(".mySmallModalLabelBox").modal({show:true, keyboard: false, backdrop:'static'});
	//----------------------------------------------------------
	$(".mySmallModalLabelBox .btn.btn-yes").on("click", function(){
		callback(true);
		$(".mySmallModalLabelBox").modal('hide');
	});
	//----------------------------------------------------------
	$(".mySmallModalLabelBox .btn.btn-no").on("click", function(){
		callback(false);
		$(".mySmallModalLabelBox").modal('hide');
	});
	//----------------------------------------------------------
}
//------------------------------------------------------------------

//------------------------------------------------------------------
function showAlert(callback, msg, yes='Ok'){
	//----------------------------------------------------------
	if($(".mySmallModalLabelBox").length>0){
		var div = ''+
			'<div class="modal-dialog modal-sm">'+
				'<div class="modal-content">'+
					'<div class="modal-header">'+
						'<span class="myModalLabel"></span>'+
						'<div style="width:100%;margin-top:10px;border-top:1px dotted #ccc;padding-top:5px;" align="right">'+
							'<button type="button" class="btn btn-danger btn-yes" style="width:40%">' + yes + '</button>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>';
		$('.mySmallModalLabelBox').html(div);
	}else{
		var div = ''+
			'<div class="modal fade mySmallModalLabelBox" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabelBox" aria-hidden="true">'+
				'<div class="modal-dialog modal-sm">'+
					'<div class="modal-content">'+
						'<div class="modal-header">'+
							'<span class="myModalLabel"></span>'+
							'<div style="width:100%;margin-top:10px;border-top:1px dotted #ccc;padding-top:5px;" align="right">'+
								'<button type="button" class="btn btn-danger btn-yes" style="width:40%">' + yes + '</button>'+
							'</div>'+
						'</div>'+
					'</div>'+
				'</div>'+
			'</div>';
		$('body').append(div);
	}
	//----------------------------------------------------------
	$(".mySmallModalLabelBox .myModalLabel").html(msg);
	$(".mySmallModalLabelBox").modal({show:true, keyboard: false, backdrop:'static'});
	//----------------------------------------------------------
	$(".mySmallModalLabelBox .btn.btn-yes").on("click", function(){
		callback();
		$(".mySmallModalLabelBox").modal('hide');
	});
	//----------------------------------------------------------
}
//------------------------------------------------------------------

//------------------------------------------------------------------
export {
	DataTable,
	showError,
	showSuccess,
	showConfirm,
	showAlert
}
