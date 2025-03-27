import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'
//import Slider from 'jquery-ui-bundle'
//import 'jquery-ui-bundle/jquery-ui.min.css'
//--------------------------------------------------------------------------
class DataClassificationr extends DataTable {
	//----------------------------------------------------------------------
	constructor(data){
		super(data);
		this.pageSort = 'tableField';
		this.getLevels();
	}
	//----------------------------------------------------------------------
	get getURL() { 
		return this.apiURL + 
				'/page/' + 
				'tableId/' + $("#myTableList").val() + '/' +
				((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' + this.pageNumber;
	}
	get searchURL() {
		return this.apiURL + '/' + 
			'tableId/'+$("#myTableList").val() + '/' +
			((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
			this.search;
	}
	//----------------------------------------------------------------------
	rowActions(value, row, index, field){
		var tmpICN = [];
		var icons = this.actionIcons;
		for (var i in icons){ if(icons[i].attr('class')!='delete-item'){ tmpICN.push(icons[i]); } }
	    this.actionIcons = tmpICN;
		return super.rowActions(value, row, index, field);
	}
	//----------------------------------------------------------------------
	getLevels(){
		var url = apiURL+'/api/dashboard/level/all/' + this.orgID;
		$.get(url, (dataIn) => {
			for(var i=0; i<dataIn.data.length; i++){ 
				if(dataIn.data[i].id==1){ continue; }
				var tmp = "<option value='"+dataIn.data[i].id+"' >"+dataIn.data[i].levelName+"</option>";
				$("#levelId").append(tmp); 
			}
		});
	}
	//----------------------------------------------------------------------
	getOrganizations() {
		$.get(this.organizationURL, (dataIn) => {
			for(var i=0; i<dataIn.data.length; i++){ 
				if(dataIn.data[i].organizationId==0){ continue; }
				var tmp = "<option value='"+dataIn.data[i].organizationId+"' >"+dataIn.data[i].organizationShortName+"</option>";
				$("#organizationId").append(tmp); 
			}
		});
	}
	//----------------------------------------------------------------------
	getActionFormInput(col, label){
		if(label=='Reserved'){ return; }
		var input = '';
		switch (col) {
			case 'organizationId':
			case 'tableNames':
			case 'fieldName':
			case 'levelId':
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
			case 'isVisible':
			case 'isEditableByPassword':
				var input = $(
				  "<div class='col-'" + col + " form-group' style='display:inline-block;margin-right:30px;margin-bottom:15px;'>" +
					"<input type='checkbox' name='"+col+"' id='"+col+"' style='width:auto' />" +
					"<label style='margin-left:5px;cursor:pointer' for='"+col+"'>"+label+"</label>" +
				  "</div>"
				);
				break;
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//----------------------------------------------------------------------
	cellRenderer(value, row, index, field){
		var column = null;
		for(var x in this.columns.names){
			if(this.columns.names[x] == field) {
				column = this.columns.data[x];
				break;
			}
		}
		if(column.date === true) return this.dateCell(value, row, column);
		if(column.name === 'isVisible') return this.checkCell(value, row, column);
		if(column.name === 'isEditableByPassword') return this.checkCell(value, row, column);
		return value;
	}
	//----------------------------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);
		$("#editItem [name]").each((i, el) => {
			switch(el.name){
				case 'isVisible':
				case 'isEditableByPassword':
					var checked = (this.editItem[el.name] == 1)? true:false;
					$("#"+el.name).prop('checked', checked);
					if(el.name=='isVisible'){
						if(checked==false){
							$("#isEditableByPassword").prop('checked', false);
							$("#isEditableByPassword").prop('disabled', true);
							this.editItem['isEditableByPassword']=0;
						}else{
							$("#isEditableByPassword").prop('disabled', false);
						}
					}
					break;
			}
		});
		getFieldist(this.editItem['tableNames'], this.editItem['fieldName']);
	}
	//----------------------------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		$("#isVisible").prop('checked', false);
		$("#isEditableByPassword").prop('checked', false);
		$("#isEditableByPassword").prop('disabled', true);
		getFieldist('', '');
	}
	//----------------------------------------------------------------------
	editConfirmHandler(e){
		if($("#isVisible"           ).val().trim()==''){ this.editItem['isVisible'           ]=0; }
		if($("#isEditableByPassword").val().trim()==''){ this.editItem['isEditableByPassword']=0; }
		if($("#fieldName").val().trim()==''){ 
			showError('Field Name is null'); 
			$("#fieldName").focus();
			return; 
		}
		super.editConfirmHandler(e);
	}
	//----------------------------------------------------------------------
	addConfirmHandler(e){
		if($("#isVisible"           ).val().trim()==''){ this.editItem['isVisible'           ]=0; }
		if($("#isEditableByPassword").val().trim()==''){ this.editItem['isEditableByPassword']=0; }
		if($("#fieldName").val().trim()==''){ 
			showError('Field Name is null'); 
			$("#fieldName").focus();
			return; 
		}
		super.addConfirmHandler(e);
	}
	//----------------------------------------------------------------------
}
//--------------------------------------------------------------------------

//--------------------------------------------------------------------------
var columns = [
	{ name: 'dataClassificationId'     , display:'ID'           , primary:true, hidden:true, editable: false },

	{ name: 'tableField'          , display: 'Field Name'  , sortable: true, search: true, editable: false },
	{ name: 'tableNames'          , display: 'Table Name'  , hidden: true },
	{ name: 'fieldName'           , display: 'Field Name'  , hidden: true },

	{ name: 'isVisible'           , display: 'Visible'     , sortable: true },
	{ name: 'isEditableByPassword', display: 'Editable'    , sortable: true },
	
	{ name: 'levelId'             , display: 'Level'       , hidden: true},
	{ name: 'levelName'           , display: 'Level'       , sortable: true, editable: false },
	
	{ name: 'organizationId'      , display: 'Organization', hidden: true},
	{ name: 'organizationName'    , display: 'Organization', sortable: true, editable: false },
	{ name: 'ownerId'             , display: 'Owner'       , hidden: true, editable: false },
	
	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
	{ name: 'lastUserId', hidden: true, editable: false, default: '1'},
];
//--------------------------------------------------------------------------
var myColumns = new Columns(columns);
var data = {
	columns: myColumns,
	apiURL: apiURL + '/api/dashboard/data_classification'
}
//--------------------------------------------------------------------------
if($("#dataClassification").length != 0){
	table = new DataClassificationr(data);
	table.createTable('dataClassification');
}
//--------------------------------------------------------------------------
