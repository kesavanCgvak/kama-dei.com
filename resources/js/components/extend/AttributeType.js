import { DataTable, showError, showSuccess } from '../extend/DataTable'
import Columns from '../extend/Columns'

class AttributeType extends DataTable {
    constructor(data){
        super(data);
        this.termId=0;
        this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;
        this.pageSort = 'attributeTypeName';
		
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
	//------------------------------------------------------------
	showAddDialogHandler(){
		super.showAddDialogHandler();
		if($("#searc_ownerId").val()==null || $("#searc_ownerId").val()=='NULL'){ $("#ownerId").val(0); }
		else{ $("#ownerId").val($("#searc_ownerId").val()); }
		$("#ownerId").change();
	}
  //--------------------------------------------------------------
  get getURL   () { return this.apiURL+'/page/'+ this.orgID+'/' + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/';}
  get searchURL() {
    return this.apiURL +'/' + this.orgID+'/' + this.pageSort + '/' +
      this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
      this.search;
  }
  get addURL   () { return this.apiURL+'/new/' +  this.orgID+'/'; }
  get editURL  () { return this.apiURL+'/edit/' + this.orgID+'/' + this.editItem[this.columns.primaryColumn]; }
  get deleteURL() { return this.apiURL + "/delete/" + this.orgID+'/' + this.deleteId; }

  //--------------------------------------------------------------
}

var columns = [
    { name: 'attributeTypeId', display: 'ID', primary: true, sortable: true },
    { name: 'attributeTypeName', display: 'Name', sortable: true, search: true },
    { name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
    { name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true,searchWhere: true},
    { name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },
    { name: 'storageType', display: 'Storage Type', sortable: true, search: true },
    { name: 'memo', display: 'Memo', hidden: true},
    { name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
    { name: 'dateUpdated', display: 'Updated', sortable: true, editable: false, date: true },
    { name: 'reserved', display: 'Reserved',sortable: true, reserved: true},
    { name: 'lastUserId', hidden: true, editable: false, default: '1'},
];
var termColumns = new Columns(columns);

var data = {
    columns: termColumns,
    apiURL: apiURL + '/api/extend/attribute_type'
}

if($("#attributetype").length != 0){
    var table = new AttributeType(data);
    table.createTable('attributetype');
}
