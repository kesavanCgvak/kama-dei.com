import { DataTable, showError, showSuccess } from '../extend/DataTable'
import Columns from '../extend/Columns'

class Extendedsubtype extends DataTable {
	constructor(data){
		super(data);
		this.showGlobal = true;
		this.pageSort = 'extendedSubTypeName';
		this.extendedTypeId=0;
		this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;
		this.copyitemid=0
		this.getExtendedTypes();

		var icon1 = $('<a></a>').attr({ href: '#', 'data-desc': 'Copy', class: 'copy-item', 'data-onlyowner': 1 });
		var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'Attribute Link', class: 'attribute-item', 'data-onlyowner': 1 });
		this.actionIcons = this.actionIcons.concat([icon1,icon2]);

		$('body').on('click', '.copy-item', (e) => { this.showEditDialogHandler_copy(e) });
		$('body').on('click', '#copy-confirm', (e) => { this.copyconfirm() });
		$('body').on('click', '.attribute-item', (e) => { this.showEditDialogHandler_attribute(e) });

		this.showGlobalStatus=1;
		$("#showGlobal").prop('checked', true);

		let that = this;
		$('body').on('change', '#showGlobal', (e) =>{
			if($(this).prop('checked')==true){
				that.showGlobalStatus=1;
				$(this).prop('checked', false);
			}else{
				that.showGlobalStatus=0;
				$(this).prop('checked', true);
				if($("#searc_ownerId").val()=='NULL'){ $("#searc_ownerId").val(orgID); }
			}
			that.refreshOptions()
		});
		$('body').on('change', '#searc_ownerId', (e) =>{
			if($('#searc_ownerId').val()=='NULL'){ $("#showGlobal").bootstrapToggle("on"); }
		});

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
	searc_item_ownerId(e){
		var target = e.currentTarget;
		var value = target.value;
		//if(target.type == 'checkbox'){ value = (target.checked)? '1':'0'; }
		//this.editItem[target.name] = value;
		if(value=='NULL'){ value=-1; }
		this.orgID=value;
		this.refreshOptions()
	};
	get getURL(){
		let ownerID = $("#searc_ownerId").val();
		if(ownerID=='NULL'){ ownerID=-1; }
		return this.apiURL + '/page/' +
			this.extendedTypeId+'/' +
			//this.orgID + '/' +
			ownerID + '/' +
			this.pageSort + '/' +
			this.pageOrder + '/' +
			this.pageSize +'/' +
			this.pageNumber + '/' +
			'showglobal/'+this.showGlobalStatus;
	}
	get searchURL() {
		let ownerID = $("#searc_ownerId").val();
		if(ownerID=='NULL'){ ownerID=-1; }
		return this.apiURL + '/' +
			this.extendedTypeId + '/' +
			//this.orgID + '/' +
			ownerID + '/' +
			this.pageSort + '/' +
			this.pageOrder + '/' +
			this.pageSize +'/' +
			this.pageNumber + '/' +
			this.columns.searchColumn + '/' +
			this.search + '/' +
			'showglobal/'+this.showGlobalStatus;
	}
  get addURL   () { return this.apiURL+'/new/' +  this.orgID+'/'; }
  get editURL  () { return this.apiURL+'/edit/' + this.orgID+'/' + this.editItem[this.columns.primaryColumn]; }
  get copyURL  () { return this.apiURL+'/copy/' + this.orgID+'/' + this.editItem[this.columns.primaryColumn]; }
  get subtypesessionURL  () { return apiURL+'/login/subtypesession' ; }
  get deleteURL() { return this.apiURL + "/delete/" + this.orgID+'/' + this.deleteId; }

  //--------------------------------------------------------------
  getExtendedTypes() {
    $.get(this.extendedTypesURL, (res) => {
      this.extendedTypes = this.createSelectOptions(res.data, 'extendedTypeId', 'extendedTypeName');
      $("#extendedTypeId").append(this.extendedTypes);
      $("#searc_extendedTypeName").append(this.extendedTypes);
      $("#extendedTypeId_copy").append(this.extendedTypes);
      //"<option value='"+value+"'>"+label+"</option>"
    });
  }

  //--------------------------------------------------------------



  copyconfirm(){
    $('#copyDialog').fadeOut()
    var temp_copyitemid=this.copyitemid;
    if(temp_copyitemid){
      $.each(this.rows, (i, item) => {
        if(item[this.columns.primaryColumn] == temp_copyitemid){
          this.editItem = item;
          return false;
        }
      });
      console.dir(this.editItem);
      //======
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
      console.dir(data)
      //----------------------------------------------------------
      $.ajax({
        url: this.copyURL,
        type: 'put',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        data: JSON.stringify(data),
        success: function(res){
          if(res.result == 0){
            showSuccess('Item copyed.');
            $(table).bootstrapTable('refresh');
          }else{
            showError(res.msg);
          }
        },
        error: function(e){
          showError('Server error');
        }
      });
    }

  }

  showEditDialogHandler_copy(e) {
    e.preventDefault();
    $('#copyDialog').fadeIn();
    console.dir($(e.currentTarget).data('itemid'));
    this.copyitemid=$(e.currentTarget).data('itemid');
  }
  //--------------------------------------------------------------
  //--------------------------------------------------------------
  //--------------------------------------------------------------
  //--------------------------------------------------------------
  showEditDialogHandler_attribute(e) {
    e.preventDefault();
    console.dir($(e.currentTarget).data('itemid'));

    //======
    var data = {
      subtypeID: $(e.currentTarget).data('itemid')
    };
    console.dir(data)
    //----------------------------------------------------------
    /*$request->session()->put('subtype', $subtype);*/
    $.ajax({
      url: this.subtypesessionURL+'/'+$(e.currentTarget).data('itemid'),
      type: 'get',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      data: JSON.stringify(data),
      success: function(res){
        window.location.href=apiURL+'/panel/extend/attribute/';
      },
      error: function(e){
        showError('Server error');
      }
    });

  }
  //--------------------------------------------------------------
  //--------------------------------------------------------------
}

var columns = [
  { name: 'extendedSubTypeId', display: 'ID', primary: true, sortable: true },
  { name: 'extendedSubTypeName', display: 'Name', sortable: true, search: true },
  { name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
  { name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true , searchWhere: true},
  { name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },
  { name: 'extendedTypeId', display: 'TypeName', onlyFor: 0, hidden: true },
  { name: 'extendedTypeName', display: 'TypeName',sortable: true, onlyFor: 0, editable: false ,searchWhere: true},
  { name: 'chatIntro', display: 'ChatIntro', sortable: true, search: true, hidden:true, editable: false },
  { name: 'memo', display: 'Memo', hidden: true},
  { name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
  { name: 'dateUpdated', display: 'Updated', sortable: true, editable: false, date: true },
  { name: 'reserved', display: 'Reserved',sortable: true, reserved: true},
  { name: 'lastUserId', hidden: true, editable: false, default: '1'},
];
var termColumns = new Columns(columns);

var data = {
  columns: termColumns,
  apiURL: apiURL + '/api/extend/extended_subtype'
}

if($("#extendedsubtype").length != 0){
  var table = new Extendedsubtype(data);
  table.createTable('extendedsubtype');
}
