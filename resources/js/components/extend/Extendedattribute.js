//------------------------------------------------------------------
import { DataTable, showError, showSuccess } from '../extend/DataTable'
import Columns from '../extend/Columns'
//------------------------------------------------------------------
class Extendedattribute extends DataTable {
	//--------------------------------------------------------------
	constructor(data){
		super(data);
		this.pageSort = 'attributeName';
		this.attributetypeID=0;
		this.subtypeID=0;
		/*console.dir($subtypeID);
		if($subtypeID){
		this.subtypeID=$subtypeID;
		}*/

		this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;
		this.getExtendedTypes();
		this.getsubtypesession();

		$('body').on('change', '#editItem #ownerId', (e)=>{
			if($("#ownerId").val()==0){
				$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
				$("#"+this.columns.ownershipColumn+"0").click();
			}else{
				$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
				$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").click();
			}
		});
	}
	//--------------------------------------------------------------
	get getURL(){
		return this.apiURL+'/page/' +
			this.attributetypeID+'/' +
			this.subtypeID+'/' +
			this.orgID+'/' +
			this.pageSort + '/' +
			this.pageOrder + '/' +
			this.pageSize +'/' +
			this.pageNumber +
			'/';
	}
	get searchURL(){
		return this.apiURL + '/' +
			this.attributetypeID + '/' +
			this.subtypeID + '/' +
			this.orgID +'/' +
			this.pageSort + '/' +
			this.pageOrder + '/' +
			this.pageSize +'/' +
			this.pageNumber + '/' +
			this.columns.searchColumn + '/' +
			this.search;
	}
	get addURL   () { return this.apiURL+'/new/' +  this.orgID+'/'; }
	get editURL  () { return this.apiURL+'/edit/' + this.orgID+'/' + this.editItem[this.columns.primaryColumn]; }
	get deleteURL() { return this.apiURL + "/delete/" + this.orgID+'/' + this.deleteId; }
	get subtypesessionURL  () { return apiURL+'/login/getsubtypesession' ; }
	//--------------------------------------------------------------
	getsubtypesession(){
		$.get(this.subtypesessionURL, (res) => {
			if(res.subtypeID){
				this.subtypeID=res.subtypeID;
				$("#searc_extendedSubTypeName").val(this.subtypeID);
				$("#extendedSubTypeId").val(this.subtypeID);//.change();
				$(this.table).bootstrapTable('refreshOptions', {url: this.getURL});
			}
		});
	}
	//--------------------------------------------------------------
	getExtendedTypes() {
		$.get(this.extendedSubTypesURL, (res) => {
			this.extendedSubTypes = this.createSelectOptions(res.data, 'extendedSubTypeId', 'extendedSubTypeName');
			$("#extendedSubTypeId").append(this.extendedSubTypes);
			$("#searc_extendedSubTypeName").append(this.extendedSubTypes);
			//"<option value='"+value+"'>"+label+"</option>"
			if(this.subtypeID){
				$("#searc_extendedSubTypeName").val(this.subtypeID);
				$("#extendedSubTypeId").val(this.subtypeID);//.change();
			}
		});
		$.get(this.attributeTypesURL, (res) => {
			this.attributeTypes = this.createSelectOptions(res.data, 'attributeTypeId', 'attributeTypeName');
			$("#attributeTypeId").append(this.attributeTypes);
			$("#searc_attributeTypeName").append(this.attributeTypes);
			//"<option value='"+value+"'>"+label+"</option>"
		});
	}
	//--------------------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();

		if($("#searc_ownerId").val()==null || $("#searc_ownerId").val()=='NULL'){ $("#ownerId").val(0); }
		else{ $("#ownerId").val($("#searc_ownerId").val()); }
		$("#ownerId").change();

		if(this.subtypeID){
			$("#searc_extendedSubTypeName").val(this.subtypeID);
			$("#extendedSubTypeId").val(this.subtypeID).change();
		}
	}
	//--------------------------------------------------------------
	actionForm() {
		var submitLabel = 'Save Item';
		var submitId    = 'saveItem';

		var formChildren = [];
		var columns = this.columns;
		var data = columns.data;
		for(var x in data) {
			var column = data[x];
			if(column.editable === false || column.primary === true || column.reserved === true|| column.reserved2 === true) continue;
			var col = column.name;
			var label = column.display;
			if(column.onlyFor == null || column.onlyFor == this.orgID){
				formChildren.push(this.getActionFormInput(col, label));
			}
		}

		if(columns.reservedColumn !== null){ formChildren.push(this.getActionFormInput(columns.reservedColumn, 'Reserved')); }

		if(columns.reservedColumn2 !== null){
			formChildren.push(this.getActionFormInput(columns.reservedColumn2, 'Not Null'));
		}

		var wrapper = $("<div>").attr({ id: 'editItem' });
		var form = $("<form>").attr({ class: 'action-form' });

		$(formChildren).each(function(i, el){ form = $(form).append(el); });

		var submit = $("<div>")
						.append(
							$("<input>")
								.attr({
									id: submitId,
									type: 'submit',
									value: submitLabel,
									class: 'btn btn-primary'
								})
						);

		var cancel = $("<div style='margin-top: 5px'></div>")
						.append(
							$("<input>")
								.attr({
									type: 'button',
									value: 'Cancel',
									class: 'btn btn-default',
									onClick: "$('#editItem').fadeOut()"
								})
						);

		form = $(form).append([submit, cancel]);
		wrapper = $(wrapper).append(form);

		return wrapper;
	}
	//--------------------------------------------------------------
}
//------------------------------------------------------------------

//------------------------------------------------------------------
var columns = [
	{ name: 'attributeId', display: 'ID', primary: true, sortable: true },
	{ name: 'attributeName', display: 'Name', sortable: true, search: true },
	{ name: 'displayName', display: 'Display Name', sortable: true, search: true },
	{ name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
	{ name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true , searchWhere: true},
	{ name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },
	{ name: 'extendedSubTypeId', display: 'Sub Type', onlyFor: 0, hidden: true },
	{ name: 'extendedSubTypeName', display: 'SubType Name',  onlyFor: 0, editable: false ,searchWhere: true},
	{ name: 'attributeTypeId', display: 'Attribute Type', onlyFor: 0, hidden: true },
	{ name: 'attributeTypeName', display: 'Attribute Type', onlyFor: 0, editable: false ,searchWhere: true},
	{ name: 'defaultValue', display: 'Default', sortable: true, search: true },
	{ name: 'notNullFlag', display: 'Not Null',sortable: true, reserved2: true},
	{ name: 'reserved', display: 'Reserved',sortable: true, reserved: true},
	{ name: 'memo', display: 'Memo', hidden: true},
	{ name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true , hidden: true},
	{ name: 'dateUpdated', display: 'Updated', sortable: true, editable: false, date: true , hidden: true},
	{ name: 'lastUserId', hidden: true, editable: false, default: '1'},
];
var termColumns = new Columns(columns);
var data = {
	columns: termColumns,
	apiURL: apiURL + '/api/extend/extended_attribute'
}
if($("#extendedattribute").length != 0){
	var table = new Extendedattribute(data);
	table.createTable('extendedattribute');
}
//------------------------------------------------------------------
