import "bootstrap-table"

import "bootstrap-table/dist/bootstrap-table.min.css"

import "../css/DataTable_yao.css"
import "../css/bootstrap-datetimepicker.min.css"
import './bootstrap-datetimepicker.min.js'
import Toastify from 'toastify-js'
import 'jquery-ui-bundle/jquery-ui.min.css'
import "../extend/jquery.tablednd.js"
import "bootstrap-table/src/extensions/reorder-rows/bootstrap-table-reorder-rows.css"
import "bootstrap-table/src/extensions/reorder-rows/bootstrap-table-reorder-rows.js"

import "../dropdown-plugin/menu.min.css"
import "../dropdown-plugin/menu.min.js"

import "../bootstrap-toggle-master/bootstrap-toggle.min.css"
import "../bootstrap-toggle-master/bootstrap-toggle.min.js"

export default class DataTable  {
	constructor(data) {
		this.apiURLBase = apiURL;
		this.apiURL = data.apiURL;
		this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;
		this.userID = (typeof(userID) != 'undefined')? userID:null;
		this.showRefresh = false;
		this.columns = data.columns;
		this.rows = [];

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

		this.deleteId = '';
		this.editItem = null;

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
		$('body').on('click', "#saveItem", (e) => { this.editConfirmHandler(e) });
		$('body').on('click', "#insertItem", (e) => { this.addConfirmHandler(e) });
		$('body').on('change input', '#editItem [name]', (e) => { this.formInputChangeHandler(e); });
		$('body').on('change select', '#editItem [name]', (e) => { this.formInputChangeHandler(e); });

		$('body').on('change select', '#searc_ownerId', (e) => { this.searc_item_ownerId(e); });
		$('body').on('change select', '#searc_termName', (e) => { this.searc_item_termName(e); });
		$('body').on('change select', '#searc_extendedTypeName', (e) => { this.searc_item_extendedTypeName(e); });

		$('body').on('change select', '#searc_extendedSubTypeName', (e) => { this.searc_extendedSubTypeName(e); });
		$('body').on('change select', '#searc_attributeTypeName', (e) => { this.searc_attributeTypeName(e); });

		$('body').on('change select', '#searc_extendedEntityName', (e) => { this.searc_extendedEntityName(e); });
		$('body').on('change select', '#searc_attributeName', (e) => { this.searc_extendedAttributeName(e); });

		$('body').on('change select', '#searc_entityName', (e) => { this.searc_entityName(e); });
		$('body').on('change select', '#searc_parentName', (e) => { this.searc_parentName(e); });

		this.getOrganizations();
	}
	
	searc_entityName(e){
		var target = e.currentTarget;
		var value = target.value;
		if(value=='NULL'){value=0;}
		this.entityId=value;
		this.refreshOptions()
	};

	searc_parentName(e){
		var target = e.currentTarget;
		var value = target.value;
		if(value=='NULL'){value=0;}
		this.parentId=value;
		this.refreshOptions()
	};
	
	get orderURL  () { return this.apiURL + "/upOrder/" + ((this.orgID) ? (this.orgID + '/') : '') + this.archiveId; }
	get archiveURL() { return this.apiURL + "/upArchive/" + ((this.orgID) ? (this.orgID + '/') : '') + this.archiveId; }
	get addURL    () { return this.apiURL+'/new/' + ((this.orgID) ? (this.orgID + '/') : ''); }
	get editURL   () {
		return this.apiURL+'/edit/' + ((this.orgID) ? (this.orgID + '/') : '') + this.editItem[this.columns.primaryColumn];
	}
	get deleteURL() { return this.apiURL + "/delete/" + ((this.orgID) ? (this.orgID + '/') : '') + this.deleteId; }
	get getURL   () {
		return this.apiURL+'/page/' +
			  			((this.orgID) ?(this.orgID + '/') :'') +
			  			this.pageSort + '/' +
			  			this.pageOrder + '/' +
			  			this.pageSize +'/' +
			  			this.pageNumber + '/';
	}
	get searchURL() {
		return this.apiURL + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
						this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
						this.search;
	}
	get organizationURL() { return this.apiURLBase + '/api/dashboard/organization/all/' + this.orgID }
	//get termsURL() { return this.apiURLBase + '/api/dashboard/term/all/' + this.orgID }
	get termsURL() { return this.apiURLBase + '/api/dashboard/relation/0/leftTermName/asc/10/1/allFields/extended%20data%20type' }
	get extendedTypesURL() { return this.apiURLBase + '/api/extend/extended_type/all/'+ this.extendedTypeId +'/'+ this.orgID }
	get extendedSubTypesURL() { return this.apiURLBase + '/api/extend/extended_subtype/all/0/'+ this.orgID }
	get attributeTypesURL() { return this.apiURLBase + '/api/extend/attribute_type/all/'+ this.orgID }
	get extendedEntitysURL() { return this.apiURLBase + '/api/extend/extended_entity/all/0/'+ this.orgID }
	get attributesURL() { return this.apiURLBase + '/api/extend/extended_attribute/all/0/0/'+ this.orgID }
	get upolderURL  () { return this.apiURLBase+'/api/extend/extended_link/upolder'; }
	get getColumns() {
		var columns = [];
		for(var x in this.columns.data){
			var column = this.columns.data[x];
			if(column.hidden === true && column.primary !== true) continue;
			if(column)
				var obj = {
					//editable: true,
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

  createTable(id,isrowReorder) {
    var temp_this=this;
    this.container = "#" + id;
    $(this.container).html('<table></table>');
    $(this.container).append(this.archiveDialog());
    $(this.container).append(this.deleteDialog());
    $(this.container).append(this.copyDialog());
    $(this.container).append(this.actionForm());
    if(this.actionForm_copy){
      $(this.container).append(this.actionForm_copy());
    }

    $(this.container).append(this.actionEavForm());
    $(this.container).append(this.tableToolbar());



    this.table = "#" + id + " table";
    var DataTableConstant = this;
    var temp_bootstrapTable_attr={
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
      showRefresh: this.showRefresh,
      clickToSelect: true,

//	  showExport: true,
//	  exportDataType: 'all',
      onPostBody:function(){

        for(var ai=0;ai<$('[archivevalue*=1]').length;ai++){
          $('[archivevalue*=1]')[ai].parentElement.parentElement.style.background='lightgray';
        }
        if(temp_this.archive!=undefined){
          $(".fixed-table-pagination").css('display','block')
          var tmpshowArchiveditems=[

            "<option value=1>Show UnArchived</option>",
            "<option value=0>Show All</option>",
            "<option value=2>Show Archived</option>"
          ];
          if(temp_this.archive==0){
            tmpshowArchiveditems=[
              "<option value=0>Show All</option>",
              "<option value=1>Show UnArchived</option>",
              "<option value=2>Show Archived</option>"
            ];
            $(".fixed-table-pagination").before($('<div class="showArchived" style="margin-left: 350px;      position: absolute;  margin-top: 20px;"></div>').append($("<label>Show Archived: </label>").attr({
                style:'margin:0 5px 0 20px;'
              }))
              /* .append(
                 $("<input checked >").attr({
                   id  : 'showArchived',
                   type: 'checkbox' ,
                   'data-onstyle': 'info',
                   'data-toggle' : 'toggle',
                   'data-size'   : 'small',
                   'data-on'     : 'Yes',
                   'data-off'    : 'No'
                 })
               )*/
                .append($("<select>").attr({
                    id: 'showArchived',
                    name: 'showArchived',
                    value: 0,
                    class: "where-control"
                  }).append(tmpshowArchiveditems)
                )
            );
          }
          if(temp_this.archive==1){
            tmpshowArchiveditems=[
              "<option value=1>Show UnArchived</option>",
              "<option value=0>Show All</option>",
              "<option value=2>Show Archived</option>"
            ];
            $(".fixed-table-pagination").before($('<div class="showArchived" style="margin-left: 350px;      position: absolute;  margin-top: 20px;"></div>').append($("<label>Show Archived: </label>").attr({
                style:'margin:0 5px 0 20px;'
              }))
              /* .append(
                 $("<input checked >").attr({
                   id  : 'showArchived',
                   type: 'checkbox' ,
                   'data-onstyle': 'info',
                   'data-toggle' : 'toggle',
                   'data-size'   : 'small',
                   'data-on'     : 'Yes',
                   'data-off'    : 'No'
                 })
               )*/
                .append($("<select>").attr({
                    id: 'showArchived',
                    name: 'showArchived',
                    value: 1,
                    class: "where-control"
                  }).append(tmpshowArchiveditems)
                )
            );
          }
          if(temp_this.archive==2){
            tmpshowArchiveditems=[
              "<option value=2>Show Archived</option>",
              "<option value=1>Show UnArchived</option>",
              "<option value=0>Show All</option>",
            ];
            $(".fixed-table-pagination").before($('<div class="showArchived" style="margin-left: 350px;      position: absolute;  margin-top: 20px;"></div>').append($("<label>Show Archived: </label>").attr({
                style:'margin:0 5px 0 20px;'
              }))
              /* .append(
                 $("<input checked >").attr({
                   id  : 'showArchived',
                   type: 'checkbox' ,
                   'data-onstyle': 'info',
                   'data-toggle' : 'toggle',
                   'data-size'   : 'small',
                   'data-on'     : 'Yes',
                   'data-off'    : 'No'
                 })
               )*/
                .append($("<select>").attr({
                    id: 'showArchived',
                    name: 'showArchived',
                    value: 2,
                    class: "where-control"
                  }).append(tmpshowArchiveditems)
                )
            );
          }

        }

      },
      queryParams: function(params) {
        DataTableConstant.queryParams(params, this);
      },
      responseHandler: (res) => this.responseHandler(res)
    };
    if(isrowReorder){
      temp_bootstrapTable_attr={
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
        showRefresh: this.showRefresh,

        reorderableRows: true,
        striped: true,
        useRowAttrFunc: true,
        rowReorder: true,
//	  showExport: true,
//	  exportDataType: 'all',
        onPostBody:function(){
          for(var ai=0;ai<$('[archivevalue*=1]').length;ai++){
            $('[archivevalue*=1]')[ai].parentElement.parentElement.style.background='lightgray';
          }
        },
        queryParams: function(params) {
          DataTableConstant.queryParams(params, this);

        },
        responseHandler: (res) => this.responseHandler(res),

        onReorderRowsDrag: function (table, row) {
          return false;
        },
        onReorderRowsDrop: function (table, row) {
          return false;
        },
        onReorderRow: function (newData) {
          let temp_upolderl=[];
          var this_rows = JSON.parse(JSON.stringify(newData));
          for(let temp_j = 0;temp_j<temp_this.rows.length;temp_j++){
            this_rows[temp_j].orderid=temp_this.rows[temp_j].orderid;
            temp_upolderl.push({extendedLinkId:this_rows[temp_j].extendedLinkId,orderid:this_rows[temp_j].orderid})
          }
          temp_this.rows=this_rows;
          $.ajax({
            url: temp_this.upolderURL,
            type: 'post',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            data: JSON.stringify({jsonstr:temp_upolderl}),
            success: function(res){
              if(res.result == 0){
                showSuccess('Sort success.');
                //$(temp_this.table).bootstrapTable('refresh');
              }else{
                showError(res.msg);
              }
            },
            error: function(e){
              showError('Server error');
            }
          });

          //return false;
        }
      };
    }
    $(this.table).bootstrapTable(temp_bootstrapTable_attr);
  }

  responseHandler(res){
    res.rows = res.data;
    this.rows = res.rows;
    return res;
  }

	//----------------------------------------------------
	tableToolbar(){
		var toolbar = $("<div>").attr({ id: 'tableToolbar' });

		if(this.hasInsertRow){
			toolbar.append(
				$("<button>Add Item</button>").attr({ id: 'insertBtn', class: 'btn btn-primary' })
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
		toolbar.append(this.searchWhere());
		return toolbar;
	}
	//----------------------------------------------------



  //--------------------------------------------------------------
 /* rowsdrop(){
    $("#getdata").click(function () {

      alert(JSON.stringify($('#tabletest').bootstrapTable('getData')));

    });
  }*/


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

				case this.columns.reservedColumn2:
					el.checked = false;
					this.editItem[this.columns.reservedColumn2] = '0';
					break;

				case this.columns.ownershipColumn:
					if(orgID==0){
						$("#"+this.columns.ownershipColumn+"0").click();
						this.editItem[this.columns.ownershipColumn] = '0';
					}else{
						$("#"+this.columns.ownershipColumn+"2").click();
						this.editItem[this.columns.ownershipColumn] = '2';
					}
					break;

				case 'ownerId':
					el.value = this.orgID;
					this.editItem['ownerId'] = this.orgID;
					break;

				case 'parentTable':
					this.editItem['parentTable'] = this.parentTable;
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
			if(el.name)
				switch(el.name){
					case this.columns.reservedColumn:
						el.checked = false;
						this.editItem[this.columns.reservedColumn] = '0';
						break;
					case this.columns.reservedColumn2:
						el.checked = false;
						this.editItem[this.columns.reservedColumn2] = '0';
						break;

					case this.columns.ownershipColumn:
						if(orgID==0){
							$("#"+this.columns.ownershipColumn+"0").click();
							this.editItem[this.columns.ownershipColumn] = '0';
						}else{
							$("#"+this.columns.ownershipColumn+"2").click();
							this.editItem[this.columns.ownershipColumn] = '2';
						}
						break;

					case 'ownerId':
						el.value = this.orgID;
						this.editItem['ownerId'] = this.orgID;
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
		if(this.subtypeID&&$("#extendedSubTypeId")) {$("#extendedSubTypeId").val(this.subtypeID);};
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
      if(this.columns.data[x]&&this.columns.names[x] == field) {
        column = this.columns.data[x];
        break;
      }
    }
    if(column){
      if(column.checkbox === true) return '<input data-index="'+row.chat_id+'" class="btSelectItem" type="checkbox">';
      if(column.reserved2 === true) return this.checkCell(value, row, column);
      if(column.reserved === true) return this.checkCell(value, row, column);
      if(column.ownership === true) return this.ownershipCell(value, row, column);
      if(column.date === true) return this.dateCell(value, row, column);
    }

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
    this.search = params.search;
    this.pageSize = params.limit;
    this.pageNumber = (params.offset/params.limit) + 1;
    this.pageSort = params.sort;
    this.pageOrder = params.order;
    if(bootstrapTable.pageNumber){
      this.pageNumber=bootstrapTable.pageNumber;
    }
    //----------------------------------------------------------
    bootstrapTable.url = (this.search == '' || this.search == null || typeof(this.search) == 'undefined')? this.getURL:this.searchURL;
    //----------------------------------------------------------
    return params;
    //----------------------------------------------------------
  }
  //--------------------------------------------------------------

  //--------------------------------------------------------------
  refreshOptions(pageNumber){
    var url = (this.search == '')? this.getURL: this.searchURL;
    if(pageNumber){
      $(this.table).bootstrapTable('refreshOptions', {url: url,pageNumber:1});
    }else{
      $(this.table).bootstrapTable('refreshOptions', {url: url});
    }

  }
  //--------------------------------------------------------------
  //--------------------------------------------------------------
  copyDialog() {
    var dialog = "<div id='copyDialog' style='display:none;z-index:10'></div>";
    var inner = "<div></div>";
    var msg = "<div>Are you sure you want to copy this item?</div>";
    var actions = "<div class='copyActions'></div>";
    var yes = $("<button>Yes</button>").attr({
      id: 'copy-confirm',
      class: 'btn btn-primary'
    });
    var no = $("<button>Cancel</button>").attr({
      class: 'btn btn-danger',//btn btn-default
      onClick: "$('#copyDialog').fadeOut()"
    });
    actions = $(actions).append(yes).append(no);
    inner = $(inner).append(msg).append(actions);
    dialog = $(dialog).append(inner);
    return dialog;
  }
  //--------------------------------------------------------------
  copyDialog() {
    var dialog = "<div id='copyDialog' style='display:none;z-index:10'></div>";
    var inner = "<div></div>";
    var msg = "<div>Are you sure you want to copy this item?</div>";
    var actions = "<div class='copyActions'></div>";
    var yes = $("<button>Yes</button>").attr({
      id: 'copy-confirm',
      class: 'btn btn-primary'
    });
    var no = $("<button>Cancel</button>").attr({
      class: 'btn btn-danger',//btn btn-default
      onClick: "$('#copyDialog').fadeOut()"
    });
    actions = $(actions).append(yes).append(no);
    inner = $(inner).append(msg).append(actions);
    dialog = $(dialog).append(inner);
    return dialog;
  }
  //--------------------------------------------------------------
  deleteDialog() {
    var dialog = "<div id='deleteDialog' style='display:none;z-index:10'></div>";
    var inner = "<div></div>";
    var msg = "<div id='deleteDialog_msg' >Are you sure you want to delete this item?</div>";
    var actions = "<div class='deleteActions'></div>";
    var yes = $("<button>Yes</button>").attr({
      id: 'delete-confirm',
      class: 'btn btn-primary',
    });
    var no = $("<button>Cancel</button>").attr({
      class: 'btn btn-danger',//btn btn-default
      onClick: "$('#deleteDialog').fadeOut()"
    });
    actions = $(actions).append(yes).append(no);
    inner = $(inner).append(msg).append(actions);
    dialog = $(dialog).append(inner);
    return dialog;
  }
  //--------------------------------------------------------------
  //--------------------------------------------------------------
  archiveDialog() {
    var dialog = "<div id='archiveDialog' style='display:none;z-index:10'></div>";
    var inner = "<div></div>";
    var msg = "<div id='archiveDialog_msg' >Do you really want to archive  records?</div>";
    var actions = "<div class='archiveActions'></div>";
    var yes = $("<button>Yes</button>").attr({
      id: 'archive-confirm',
      class: 'btn btn-primary',
    });
    var no = $("<button>Cancel</button>").attr({
      class: 'btn btn-danger',//btn btn-default
      onClick: "$('#archiveDialog').fadeOut()"
    });
    actions = $(actions).append(yes).append(no);
    inner = $(inner).append(msg).append(actions);
    dialog = $(dialog).append(inner);
    return dialog;
  }
  //--------------------------------------------------------------
  getOrganizations() {
    $.get(this.organizationURL, (res) => {
      this.organizations = this.createSelectOptions(res.data, 'organizationId', 'organizationShortName');
      this.ownerId = this.orgID;
      $("#orgID, #ownerId,#modal_ownerId").append(this.organizations);
      $("#searc_ownerId").append(this.organizations);

      this.getStorageTypes();
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
  searchWhere(){
    var wrapper = $("<div>").attr({ id: 'searchWhere' });
    var formChildren = [];
    var columns = this.columns;
    var data = columns.data;
    for(var x in data) {
      var column = data[x];
      if(!column.searchWhere) continue;
      var col = column.name;
      var label = column.display;
      formChildren.push(this.searchWhereItem(col, label));
    }
    $(formChildren).each(function(i, el){
      wrapper.append(el);
    });
    return wrapper;
  }
  searchWhereItem(where,lable){
    var value = '';
    //searc_parentTableName
    var tmpitems=[]
    if(this.orgID==0){
      tmpitems=["<option value='NULL'>"+lable+"  All</option>"]
    }
    if(where=='parentTableName'){
       tmpitems=[
        "<option value='0'>Term</option>",
        "<option value='1'>RelationType</option>",
        "<option value='2'>Relation</option>"
      ];
      var row = $("<div>")
        .attr({ class: "wol-"+lable+" where-group" })
        .append("<label>"+lable+"</label>")
        .append($("<div>")
          .append($("<select>").attr({
              id: 'searc_'+where,
              name: 'searc_'+where,
              value: value,
              class: "where-control"
            }).append(tmpitems)
          ));
      return row;
    }else if(where=='entityName'){

      var row = $("<div>")
        .attr({ class: "wol-"+lable+" where-group" })
        .append("<label>"+lable+"</label>")
        .append($("<div>")
          .append($("<select>").attr({
              id: 'searc_'+where,
              name: 'searc_'+where,
              value: value,
              class: "where-control"
            }).append(tmpitems)
          )
          .append($('<button id="select_entityId_where"  data-toggle="modal" data-target="#form_select_entityId"  type="button" class="btn btn-primary" style="float: right;"><span class="glyphicon glyphicon-zoom-in"></span></button>'))

        );

      return row;
    }
    else if(where=='parentName'){
      var row = $("<div>")
        .attr({ class: "wol-"+lable+" where-group" })
        .append("<label>"+lable+"</label>")
        .append($("<div>")
          .append($("<select>").attr({
              id: 'searc_'+where,
              name: 'searc_'+where,
              value: value,
              class: "where-control"
            }).append(tmpitems)
          )
          .append($('<button id="select_parentId_where"  data-toggle="modal" data-target="#form_select_parentId"  type="button" class="btn btn-primary"  style="float: right;"><span class="glyphicon glyphicon-zoom-in"></span></button>'))

        );
      return row;


    }else{
      var row = $("<div>")
        .attr({ class: "wol-"+lable+" where-group" })
        .append("<label>"+lable+"</label>")
        .append($("<div>")
          .append($("<select>").attr({
              id: 'searc_'+where,
              name: 'searc_'+where,
              value: value,
              class: "where-control"
            }).append(tmpitems)
          ));
      return row;
    }


  };
  searc_item_termName(e){
    var target = e.currentTarget;
    var value = target.value;
    //if(target.type == 'checkbox'){ value = (target.checked)? '1':'0'; }
    //this.editItem[target.name] = value;
    if(value=='NULL'){value=0;}
    this.termId=value;
    this.refreshOptions()
  };
  searc_item_ownerId(e){
    var target = e.currentTarget;
    var value = target.value;
    //if(target.type == 'checkbox'){ value = (target.checked)? '1':'0'; }
    //this.editItem[target.name] = value;
    if(value=='NULL'){value=0;}
    this.orgID=value;
    this.refreshOptions()
  };
  searc_item_extendedTypeName(e){
    var target = e.currentTarget;
    var value = target.value;
    if(value=='NULL'){value=0;}
    this.extendedTypeId=value;
    this.refreshOptions()
  };
  searc_attributeTypeName(e){
    var target = e.currentTarget;
    var value = target.value;
    if(value=='NULL'){value=0;}
    this.attributetypeID=value;
    this.refreshOptions()
  };
  searc_extendedSubTypeName(e){
    var target = e.currentTarget;
    var value = target.value;
    if(value=='NULL'){value=0;}
    this.subtypeID=value;
    this.refreshOptions()
  };
  searc_extendedEntityName(e){
    var target = e.currentTarget;
    var value = target.value;
    if(value=='NULL'){value=0;}
    this.extendedEntityId=value;
    this.refreshOptions()
  };
  searc_extendedAttributeName(e){
    var target = e.currentTarget;
    var value = target.value;
    if(value=='NULL'){value=0;}
    this.extendedAttributeId=value;
    this.refreshOptions()
  };
  //--------------------------------------------------------------
  actionEavForm(){return '';}
  actionForm() {
    var submitLabel = 'Save Item';
    var submitId = 'saveItem';

    var formChildren = [];
    var columns = this.columns;
    var data = columns.data;
    for(var x in data) {
      var column = data[x];
      if(column.editable === false || column.primary === true ) continue;
      var col = column.name;
      var label = column.display;
      if(column.onlyFor == null || column.onlyFor == this.orgID){
        formChildren.push(this.getActionFormInput(col, label));
      }
    }

    /*if(columns.reservedColumn !== null) {
      formChildren.push(this.getActionFormInput(columns.reservedColumn, columns.reservedColumn));
    }*/

    var wrapper = $("<div>").attr({ id: 'editItem' });
    var form = $("<form>").attr({ class: 'action-form' });



    $(formChildren).each(function(i, el){
      form = $(form).append(el);
    });

    var submit = $("<div>")
      .append($("<input>")
        .attr({
          id: submitId,
          type: 'submit',
          value: submitLabel,
          class: 'btn btn-primary'
        })
      );

    var cancel = $("<div style='margin-top: 5px'></div>")
      .append($("<input>")
        .attr({
          type: 'button',
          value: 'Cancel',
          class: 'btn btn-danger',//btn btn-default
          onClick: "$('#editItem').fadeOut()"
        })
      );

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

      case this.columns.ownershipColumn:
        row = this.ownershipFormInput(col, label);
        break;
      case 'notNullFlag':
        row = this.reservedFormInput(col, label);
        break;
      case 'reserved':
        row = this.reservedFormInput(col, label);
        break;
      case 'includedExtDataName':
        row = this.reservedFormInput(col, label);
        break;
      case 'includedExtDataChatIntro':
        row = this.reservedFormInput(col, label);
        break;

      case 'ownership_entity':
        row = this.ownershipFormInput(col, label);
        break;
      case 'ownerId_entity':
        row = this.ownerIdFormInput(col, label,this.organizations);
        break;
      case 'ownerId':
        row = this.ownerIdFormInput(col, label,this.organizations);
        break;
      case 'termId':
        row = this.ownerIdFormInput(col, label,this.tems);
        break;
      case 'extendedTypeId':
        row = this.ownerIdFormInput(col, label,this.extendedTypes);
        break;
      case 'extendedSubTypeId':
        row = this.ownerIdFormInput(col, label,this.extendedSubTypes);
        break;
      case 'attributeTypeId':
        row = this.ownerIdFormInput(col, label,this.attributeTypes);
        break;

      case 'extendedEntityId':
        row = this.ownerIdFormInput(col, label,this.extendedEntitys);
        break;
      case 'extendedAttributeId':
        row = this.ownerIdFormInput(col, label,this.extendedAttributes);
        break;

      case 'storageType':
        row = this.ownerIdFormInput(col, label,this.storagetypes);
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
  ownerIdFormInput(col, label,options) {
    return this.selectFormInput(col, label, options);
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
		row += "<div>";
		row += "<div class='btn-group' data-toggle='buttons'>";
		if(orgID==0){
			row += "<label class='active btn btn-default'>";
			row += "<input type='radio' name='"+col+"' id='"+col+"0' value='0' autoComplete='off' checked /> Public";
			row += "</label>";
		}
		else{
			row += "<label class='btn btn-default disabled'>";
			row += "<input type='radio' name='"+col+"' id='"+col+"0' value='0' autoComplete='off' disabled /> Public";
			row += "</label>";
		}
		row += "<label class='btn btn-default'>";
		row += "<input type='radio' name='"+col+"' id='"+col+"1' value='1' autoComplete='off' /> Protected";
		row += "</label>";

		if(orgID==0){
			row += "<label class='btn btn-default'>";
			row += "<input type='radio' name='"+col+"' id='"+col+"2' value='2' autoComplete='off' /> Private";
			row += "</label>";
		}else{
			row += "<label class='btn btn-default active'>";
			row += "<input type='radio' name='"+col+"' id='"+col+"2' value='2' autoComplete='off' checked /> Private";
			row += "</label>";
		}
		row += "</div>";
		row += "</div>";
		row += "</div>";
		return row;
	}
  //--------------------------------------------------------------

  //--------------------------------------------------------------
  reservedFormInput(col, label) {
    var row = (
      "<div class='col-" + col + " form-group'>" +
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
    var getSelectRows = $(".btSelectItem");
    this.archiveId_arr=[];
    for(let ri=0;ri<getSelectRows.length;ri++){
      if(getSelectRows[ri].dataset.index==this.deleteId){
        getSelectRows[ri].checked=true;
      }
      if(getSelectRows[ri].checked){
        this.archiveId_arr.push(getSelectRows[ri].dataset.index)
      }
    }
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
        $("#editItem [name]").each((i, el) => {
          var val = '';
          if(el.name)
          switch(el.name){
            case 'includedExtDataName':
              var checked= (this.editItem['includedExtDataName'] == 1)? true:false;
              $("#includedExtDataName").prop('checked', checked);
              break;
            case 'includedExtDataChatIntro':
              var checked= (this.editItem['includedExtDataChatIntro'] == 1)? true:false;
              $("#includedExtDataChatIntro").prop('checked', checked);
              break;
            case 'includedExtDataChatIntro':
              var checked= (this.editItem['includedExtDataChatIntro'] == 1)? true:false;
              $("#includedExtDataChatIntro").prop('checked', checked);
              break;
            case 'reserved':
              var checked = (this.editItem['reserved'] == 1)? true:false;
              $("#reserved").prop('checked', checked);
              break;
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

            case this.columns.reservedColumn2:
              var checked = (this.editItem[this.columns.reservedColumn2] == 1)? true:false;
              $("#"+this.columns.reservedColumn2).prop('checked', checked);
              break;

            case 'storageType':
              el.value = (this.editItem[el.name] == null)? '0':this.editItem[el.name];
              break;

            default:
              el.value = this.editItem[el.name];
          }
        });
        return false;
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
      data: JSON.stringify({archiveId_arr:this.archiveId_arr}),
      success: function(res){
        if(res.result == 1){ showError(res.msg); }
        else{
          showSuccess('Deleted successfully');
          $(table).bootstrapTable('refresh');
        }
      },
      error: function(e){ showError('IN USE. DELETE RELATED RECORDS FIRST'); }
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
        if(name == 'parentTable'){ value = this.parentTable; }

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
  getStorageTypes() {
    this.storagetypes = [];
    var arr=['CHAR','VARCHAR','TINYBLOB','TINYTEXT','BLOB','TEXT','MEDIUMBLOB','MEDIUMTEXT','LONGBLOB','LONGTEXT',
      'TINYINT','SMALLINT','MEDIUMINT','INT','INTEGER','BIGINT','FLOAT','DOUBLE','DATE','TIME','YEAR','DATETIME',
      'TIMESTAMP'];
    for(var i in arr){
      var value = arr[i];
      this.storagetypes.push("<option value='"+value+"'>"+value+"</option>");
    }
    /*this.storagetypes = ["<option value='longtext'>longtext</option>","<option value='datetime'>datetime</option>",
		"<option value='blob'>blob</option>"];*/
    $("#storageType").append(this.storagetypes);
  }
  //--------------------------------------------------------------

}
//--------------------------------------------------------------
//GLOBAL FUNCTIONS----------------------------------------------
//--------------------------------------------------------------
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
//--------------------------------------------------------------

//--------------------------------------------------------------
function showSuccess(msg) {
  Toastify({
    text: msg,
    duration: 5000,
    close: false,
    gravity: "bottom",
    positionLeft: false,
    backgroundColor: "#4CAF50"
  }).showToast();
}
//--------------------------------------------------------------

//--------------------------------------------------------------
function showConfirm(callback, msg, yes='Yes', no='No'){
  //----------------------------------------------------------
  if($(".mySmallModalLabelBox").length>0){
    var div = ''+
      '<div class="modal-dialog modal-sm">'+
      '<div class="modal-content">'+
      '<div class="modal-header">'+
      '<span class="myModalLabel"></span>'+
      '<div style="width:100%;margin-top:10px;border-top:1px dotted #ccc;padding-top:5px;" align="right">'+
      '<button type="button" class="btn btn-danger btn-yes" style="width:40%">' + yes + '</button>'+
      '<button type="button" class="btn btn-default btn-no" style="float:left;width:40%;">' + no + '</button>'+
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
      '<button type="button" class="btn btn-default btn-no" style="float:left;width:40%;">' + no + '</button>'+
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
//--------------------------------------------------------------
export {
  DataTable,
  showError,
  showSuccess,
  showConfirm
}
