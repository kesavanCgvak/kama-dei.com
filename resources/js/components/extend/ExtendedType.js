import { DataTable, showError, showSuccess } from '../extend/DataTable'
import Columns from '../extend/Columns'

class ExtendedType extends DataTable {
	constructor(data){
		super(data);
		this.pageSort = 'extendedTypeName';
		this.termId=0;
		this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;
		this.getTems();

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
  get getURL   () { return this.apiURL+'/page/'+this.termId+'/'+ this.orgID+'/' + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/';}
  get searchURL() {
    return this.apiURL + '/'+this.termId+'/' + this.orgID+'/' + this.pageSort + '/' +
      this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
      this.search;
  }
  get addURL   () { return this.apiURL+'/new/' +  this.orgID+'/'; }
  get editURL  () { return this.apiURL+'/edit/' + this.orgID+'/' + this.editItem[this.columns.primaryColumn]; }
  get deleteURL() { return this.apiURL + "/delete/" + this.orgID+'/' + this.deleteId; }
  get termsURL () { return this.apiURLBase + '/api/dashboard/relation/0/leftTermName/asc/1000/1/allFields/extended%20data%20type'; }
  //--------------------------------------------------------------
  getTems() {
    $.get(this.termsURL, (res) => {
      this.tems = this.createSelectOptions2(res.data, 'relationId', 'leftTermName');
      $("#termId").append(this.tems);
      $("#searc_termName").append(this.tems);
      //"<option value='"+value+"'>"+label+"</option>"
    });
  }
  createSelectOptions2(arr, valKey, labelKey) {
    var options = [];
    for(var i in arr){
      var value = arr[i][valKey];
      //var label = arr[i]['leftTermName']+' '+arr[i]['relationTypeName']+' '+arr[i]['rightTermName'];
      var label = arr[i]['leftTermName'];
      options.push("<option value='"+value+"'>"+label+"</option>");
    }
    return options;
  }
  defaultFormInput(col, label) {
    if(col=='memo'){
      var row = $("<div>")
        .attr({ class: 'col-' + col + ' form-group' ,style:'width: 100%;'})
        .append("<label>"+label+"</label>")
        .append($("<div>")
          .append($("<textarea>").attr({
              name: col,
              id: col,
              placeholder: label,
              class: 'form-control',
            rows:"2"
            })
          ));

      return row;
    }else{
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

  }
  //--------------------------------------------------------------
}

var columns = [
  { name: 'extendedTypeId', display: 'ID', primary: true, sortable: true },
  { name: 'extendedTypeName', display: 'Name', sortable: true, search: true },
  { name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
  { name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true , searchWhere: true},
  { name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },
  { name: 'termId', display: 'Reference Term', onlyFor: 0, hidden: true },
  { name: 'termName', display: 'Reference Term', sortable: true, onlyFor: 0, editable: false ,searchWhere: true},
  /*{ name: 'chatIntro', display: 'Chat Intro', sortable: true, search: true },*/
  { name: 'memo', display: 'Memo', hidden: true},
  { name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
  { name: 'dateUpdated', display: 'Updated', sortable: true, editable: false, date: true },
  { name: 'reserved', display: 'Reserved',sortable: true, reserved: true},
  { name: 'lastUserId', hidden: true, editable: false, default: '1'},
];
var termColumns = new Columns(columns);

var data = {
  columns: termColumns,
  apiURL: apiURL + '/api/extend/extended_type'
}

if($("#extendedtype").length != 0){
  var table = new ExtendedType(data);
  table.createTable('extendedtype');
}
