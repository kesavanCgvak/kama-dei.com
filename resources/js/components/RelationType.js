import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class RelationType extends DataTable {
	constructor(data){
		super(data);
		this.showGlobal = true;
		this.pageSort = 'relationTypeName';
		$('body').on('click', '.link-item', (e) => { this.showLinkDialogHandler(e) });
		var icon1 = $('<a></a>').attr({href: '#',class: 'link-item', 'data-desc': 'Ext. Data Link', 'data-onlyowner': 1 });
		this.actionIcons = this.actionIcons.concat([icon1]);

		let tmpThis = this;
		tmpThis.showGlobalStatus=1;
		$("#showGlobal").prop('checked', true);
		$('body').on('change', '#showGlobal', (e) => { 
			if($(this).prop('checked')==true){ 
				tmpThis.showGlobalStatus=1;
				$(this).prop('checked', false);
			}else{ 
				tmpThis.showGlobalStatus=0;
				$(this).prop('checked', true);
				if($("#relationTypeOwnersList").val()==-1){ $("#relationTypeOwnersList").val(orgID); }
			}
			$("#relationTypeOwnersList").change();
		});

		$('body').on('change', '#relationTypeOwnersList', (e) => { 
			$(tmpThis.table).bootstrapTable('selectPage', 1);
		});

		$('body').on('change', '#ownerId', (e)=>{
			if($("#ownerId").val()==0){
				$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"0").click();
				$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
			}else{
				$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").click();
				$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
			}
		});
	}
	//------------------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		if(this.ownerId==0){
			$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"0").click();
			$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
		}else{
			$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
			$("#"+this.columns.ownershipColumn+"2").click();
			$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
		}
	}
	//------------------------------------------------------------
	showEditDialogHandler(e){
		super.showEditDialogHandler(e);

		if(this.editItem.ownerId==0 || this.editItem.ownerId==null){
				$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"0").click();
				$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
			}else{
				$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
		}
	}
	//------------------------------------------------------------
	showLinkDialogHandler(e){
		e.preventDefault();
		console.dir($(e.currentTarget).data('itemid'));
		window.location.href=this.apiURLBase + '/panel/extend/extendedlink/1/'+$(e.currentTarget).data('itemid');
	}
	//------------------------------------------------------------
	get getURL() { 
		return this.apiURL + 
				'/page/' + ((this.orgID) ?(this.orgID+'/') :'') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize + '/' + this.pageNumber +
				'/ownerId/' + $("#relationTypeOwnersList").val()+'/showglobal/'+this.showGlobalStatus;
	}
	get searchURL() {
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
			this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
			this.search+ '/ownerId/'+$("#relationTypeOwnersList").val()+'/showglobal/'+this.showGlobalStatus;
	}
	//------------------------------------------------------------
}

var columns = [
  { name: 'relationTypeId', primary: true, hidden: true },
  { name: 'relationTypeIdOld', display: '', hidden: true, search: false, editable: false, default: '0' },
  { name: 'relationTypeName', display: 'Name', sortable: true, search: true },
  { name: 'relationTypeDescription', display: 'Description' },
//  { name: 'relationTypeClassificationId', display: 'Classification', sortable: true, default: '0' },
  { name: 'relationTypeIsReserved', display: 'Reserved', sortable: true, reserved: true },
  { name: 'relationTypeOperand', hidden: true, default: '0', editable: false },
  { name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true},
  { name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
  { name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },
  { name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
  { name: 'lastUserId', hidden: true, default: '1', editable: false },
];
var relationTypeColumns = new Columns(columns);

var data = {
  columns: relationTypeColumns,
  apiURL: apiURL + '/api/dashboard/relation_type'
}

if($("#relationType").length != 0){
  var table = new RelationType(data);
  table.createTable('relationType');
}
