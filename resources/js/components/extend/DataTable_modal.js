import "bootstrap-table"
//import "bootstrap-table/dist/extensions/editable/bootstrap-table-editable.min.js"
//import "bootstrap-table/dist/extensions/export/bootstrap-table-export.min.js"
import "bootstrap-table/dist/bootstrap-table.min.css"
import "../css/DataTable_yaolink.css"
import Toastify from 'toastify-js'
import 'jquery-ui-bundle/jquery-ui.min.css'

import "../dropdown-plugin/menu.min.css"
import "../dropdown-plugin/menu.min.js"

import "../bootstrap-toggle-master/bootstrap-toggle.min.css"
import "../bootstrap-toggle-master/bootstrap-toggle.min.js"

export default class DataTable_modal {
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
    this.hasInsertRow = false;

    this.pageSize = 2;
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
   /* $('#form_select_entityId').on('click', '.delete-item_modal', (e) => { this.showDeleteDialogHandler(e) });
    $('#form_select_entityId').on('click', '.edit-item_modal', (e) => { this.showEditDialogHandler(e) });
    $('#form_select_entityId').on('click', '#insertBtn_modal', (e) => { this.showAddDialogHandler() });
    $('#form_select_entityId').on('click', "#delete-confirm_modal", (e) => { this.deleteConfirmHandler() });
    $('#form_select_entityId').on('click', "#saveItem_modal", (e) => { this.editConfirmHandler(e) });
    $('#form_select_entityId').on('click', "#insertItem_modal", (e) => { this.addConfirmHandler(e) });*/
    $('#form_select_entityId').on('change input', '#editItem_modal [name]', (e) => { this.formInputChangeHandler(e); });

    $('#form_select_entityId').on('change select', '#searc_ownerId_modal', (e) => { this.searc_item_ownerId(e); });
    $('#form_select_entityId').on('change select', '#searc_termName_modal', (e) => { this.searc_item_termName(e); });
    $('#form_select_entityId').on('change select', '#searc_extendedTypeName_modal', (e) => { this.searc_item_extendedTypeName(e); });

    $('#form_select_entityId').on('change select', '#searc_extendedSubTypeName_modal', (e) => { this.searc_extendedSubTypeName(e); });
    $('#form_select_entityId').on('change select', '#searc_attributeTypeName_modal', (e) => { this.searc_attributeTypeName(e); });

    $('#form_select_entityId').on('change select', '#searc_extendedEntityName_modal', (e) => { this.searc_extendedEntityName(e); });
    $('#form_select_entityId').on('change select', '#searc_attributeName_modal', (e) => { this.searc_extendedAttributeName(e); });

    /*$('#searc_ownerId').on('change', () => { console.dir(this.value);
    //this.searc_item_ownerId();
    });
    $('#searc_item_termName').on('change', () => { console.dir(this.value);
      //this.searc_item_ownerId();
    });*/
    // $('#searc_item_termName').on('change', (e) => { this.searc_item_ownerId(e) });
    /*$('#form_select_entityId').on('click', "#searc_ownerId", (e) => { this.searc_item_ownerId(e) });
    $('#form_select_entityId').on('click', "#searc_termName", (e) => { this.searc_item_termName(e) });*/

    this.getOrganizations();

  }

  get addURL   () { return this.apiURL+'/new/' + ((this.orgID) ? (this.orgID + '/') : ''); }
  get editURL  () { return this.apiURL+'/edit/' + ((this.orgID) ? (this.orgID + '/') : '') + this.editItem[this.columns.primaryColumn]; }
  get deleteURL() { return this.apiURL + "/delete/" + ((this.orgID) ? (this.orgID + '/') : '') + this.deleteId; }
  get getURL   () {
    if(this.apiURL.indexOf('relation_link')>-1){
      //if(strpos(this.apiURL,'relation_link') !== false){
      return this.apiURL+'/page/'+ ((this.orgID) ? (this.orgID + '/') : '')+'-1/' + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/';
    }else{
      return this.apiURL+'/page/'+ ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/';
    }

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

  createTable(id) {
    this.container = "#" + id;
    $(this.container).html('<table></table>');
    /*$(this.container).append(this.deleteDialog());*/
    // $(this.container).append(this.actionForm());
    /*if(this.actionForm_copy){
      $(this.container).append(this.actionForm_copy());
    }*/
    /*$(this.container).append(this.actionEavForm());*/
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
      toolbar: "#tableToolbar_modal",
      pageSize: this.pageSize,
      pageNumber: this.pageNumber,
      sortName: this.pageSort,
      showRefresh: this.showRefresh,
//	  showExport: true,
//	  exportDataType: 'all',
      queryParams: function(params) {
        DataTableConstant.queryParams(params, this);
      },
      responseHandler: (res) => this.responseHandler(res),
    });
  }

  responseHandler(res){
    res.rows = res.data;
    this.rows = res.rows;
    return res;
  }

  tableToolbar(){
    var toolbar = $("<div>").attr({
      id: 'tableToolbar_modal'
    });

    if(this.hasInsertRow){
      toolbar.append(
        $("<button>Add Item</button>").attr({
          id: 'insertBtn_modal',
          class: 'btn btn-primary'
        })
      );
    }
    toolbar.append(this.searchWhere());
    return toolbar;
  }



  //--------------------------------------------------------------

  //--------------------------------------------------------------
  showAddDialogHandler(){
    //----------------------------------------------------------
    $("#form_select_entityId #editItem").fadeIn();
    this.editItem = {};
    //----------------------------------------------------------
    $.each(this.columns.data, (i, col) => {
      var el = $("#form_select_entityId [name='"+col.name+"']")[0];
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
          $("#form_select_entityId #"+this.columns.ownershipColumn+"0").click();
          this.editItem[this.columns.ownershipColumn] = '0';
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
    $("#form_select_entityId #editItem_modal [name]").each((i, el) => {
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
          $("#form_select_entityId #"+this.columns.ownershipColumn+"0").click();
          this.editItem[this.columns.ownershipColumn] = '0';
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
    if(this.subtypeID&&$("#form_select_entityId #extendedSubTypeId_modal")) {$("#form_select_entityId #extendedSubTypeId_modal").val(this.subtypeID);};
    //----------------------------------------------------------
  }
  //--------------------------------------------------------------

  //--------------------------------------------------------------
  changeFormMode(mode = 'edit'){
    if(mode == 'edit'){
      $("#form_select_entityId #editItem_modal [type='submit']").attr('id', 'saveItem_modal').val('Save Item');
    }
    else if(mode == 'add'){
      $("#form_select_entityId #editItem_modal [type='submit']").attr('id', 'insertItem_modal').val('Add Item');
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
    if(column.reserved2 === true) return this.checkCell(value, row, column);
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
    this.search = params.search;
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
    var dialog = "<div id='deleteDialog' style='display:none;z-index:10'></div>";
    var inner = "<div></div>";
    var msg = "<div>Are you sure you want to delete this item?</div>";
    var actions = "<div class='deleteActions'></div>";
    var yes = $("<button>Yes</button>").attr({
      id: 'delete-confirm',
      class: 'btn btn-primary'
    });
    var no = $("<button>Cancel</button>").attr({
      class: 'btn btn-default',
      onClick: "$('#form_select_entityId #deleteDialog_modal').fadeOut()"
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
      this.ownerId = this.orgID;
      $("#form_select_entityId #orgID_modal,#form_select_entityId #ownerId_modal").append(this.organizations);
      $("#form_select_entityId #searc_ownerId_modal").append(this.organizations);

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
    var wrapper = $("<div>").attr({ id: 'searchWhere_modal' });
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
    var row = $("<div>")
      .attr({ class: "wol-"+lable+" where-group" })
      .append("<label>"+lable+"</label>")
      .append($("<div>")
        .append($("<select>").attr({
            id: 'searc_'+where+'_modal',
            name: 'searc_'+where+'_modal',
            value: value,
            class: "where-control"
          }).append("<option value='NULL'>"+lable+"  All</option>")
        ));
    return row;
  };
  searc_item_termName(e){
    var target = e.currentTarget;
    var value = target.value;
    //if(target.type == 'checkbox'){ value = (target.checked)? '1':'0'; }
    //this.editItem[target.name] = value;
    console.dir(target);
    console.dir(value);
    if(value=='NULL'){value=0;}
    this.termId=value;
    this.refreshOptions()
  };
  searc_item_ownerId(e){
    var target = e.currentTarget;
    var value = target.value;
    //if(target.type == 'checkbox'){ value = (target.checked)? '1':'0'; }
    //this.editItem[target.name] = value;
    console.dir(target);
    console.dir(value);
    if(value=='NULL'){value=0;}
    this.orgID=value;
    this.refreshOptions()
  };
  searc_item_extendedTypeName(e){
    var target = e.currentTarget;
    var value = target.value;
    console.dir(target);
    console.dir(value);
    if(value=='NULL'){value=0;}
    this.extendedTypeId=value;
    this.refreshOptions()
  };
  searc_attributeTypeName(e){
    var target = e.currentTarget;
    var value = target.value;
    console.dir(target);
    console.dir(value);
    if(value=='NULL'){value=0;}
    this.attributetypeID=value;
    this.refreshOptions()
  };
  searc_extendedSubTypeName(e){
    var target = e.currentTarget;
    var value = target.value;
    console.dir(target);
    console.dir(value);
    if(value=='NULL'){value=0;}
    this.subtypeID=value;
    this.refreshOptions()
  };
  searc_extendedEntityName(e){
    var target = e.currentTarget;
    var value = target.value;
    console.dir(target);
    console.dir(value);
    if(value=='NULL'){value=0;}
    this.extendedEntityId=value;
    this.refreshOptions()
  };
  searc_extendedAttributeName(e){
    var target = e.currentTarget;
    var value = target.value;
    console.dir(target);
    console.dir(value);
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
      if(column.editable === false || column.primary === true || column.reserved === true || column.reserved2 === true) continue;
      var col = column.name;
      var label = column.display;
      if(column.onlyFor == null || column.onlyFor == this.orgID){
        formChildren.push(this.getActionFormInput(col, label));
      }
    }
    console.dir(123);
    console.dir(columns);
    if(columns.reservedColumn !== null) {
      console.dir(columns);
      formChildren.push(this.getActionFormInput(column.name, 'Reserved'));
    }

    var wrapper = $("<div>").attr({ id: 'editItem_modal' });
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
          class: 'btn btn-default',
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

      case this.columns.reservedColumn:
        row = this.reservedFormInput(col, label);
        break;
      case this.columns.reservedColumn2:
        row = this.reservedFormInput(col, label);
        break;

      case this.columns.ownershipColumn:
        row = this.ownershipFormInput(col, label);
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
    if(this.orgID != 0) {
      return;
    }

    var row = "<div class='col-"+col+" form-group'>";
    row += "<label>"+label+"</label>";
    row += "<div>";
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
    $('#form_select_entityId #deleteDialog_modal').fadeIn();
    this.deleteId = $(e.currentTarget).data('itemid');
  }
  //--------------------------------------------------------------

  //--------------------------------------------------------------
  showEditDialogHandler(e) {
    e.preventDefault();
    this.changeFormMode('edit');
    $('#form_select_entityId #editItem_modal').fadeIn();
    $("#form_select_entityId #editItem #saveItem_modal"  ).prop('disabled', false);
    $.each(this.rows, (i, item) => {
      if(item[this.columns.primaryColumn] == $(e.currentTarget).data('itemid')){
        this.editItem = item;
        return false;
      }
    });

    $("#form_select_entityId #editItem_modal [name]").each((i, el) => {
      var val = '';
      switch(el.name){
        case 'ownerId':
          el.value = (this.editItem[el.name] == null)? '0':this.editItem[el.name];
          break;

        case this.columns.ownershipColumn:
          $("#form_select_entityId #"+this.columns.ownershipColumn+this.editItem[el.name]).click();
          break;

        case this.columns.reservedColumn:
          var checked = (this.editItem[this.columns.reservedColumn] == 1)? true:false;
          $("#form_select_entityId #"+this.columns.reservedColumn).attr('checked', checked);
          break;

        case this.columns.reservedColumn2:
          var checked = (this.editItem[this.columns.reservedColumn2] == 1)? true:false;
          $("#form_select_entityId #"+this.columns.reservedColumn2).attr('checked', checked);
          break;

        case 'storageType':
          el.value = (this.editItem[el.name] == null)? '0':this.editItem[el.name];
          break;

        default:
          el.value = this.editItem[el.name];
      }
    });
  }
  //--------------------------------------------------------------

  //--------------------------------------------------------------
  deleteConfirmHandler(){
    $("#form_select_entityId #deleteDialog_modal").fadeOut();
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
      beforeSend: function(){ $("#form_select_entityId #editItem_modal #saveItem_modal").prop('disabled', true); },
      success: function(res){
        if(res.result == 0){
          $("#form_select_entityId #editItem_modal").fadeOut(function(){ $("#form_select_entityId #editItem_modal #saveItem_modal").prop('disabled', false); });
          showSuccess('Item saved.');
          $(table).bootstrapTable('refresh');
        }else{
          showError(res.msg);
          $("#form_select_entityId #editItem_modal #saveItem_modal").prop('disabled', false);
        }
      },
      error: function(e){
        showError('Server error');
        $("#form_select_entityId #editItem_modal #saveItem_modal").prop('disabled', false);
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
      beforeSend: function(){ $("#form_select_entityId #editItem_modal #insertItem_modal").prop('disabled', true); },
      success: function(res){
        if(res.result == 0){
          $("#form_select_entityId #editItem_modal").fadeOut(function(){ $("#editItem_modal #insertItem_modal").prop('disabled', false); });
          showSuccess('Added successfully.');
          $(table).bootstrapTable('refresh');
        }else{
          showError(res.msg);
          $("#form_select_entityId  #editItem_modal #insertItem_modal").prop('disabled', false);
        }
      },
      error: function(e){
        showError('Server error');
        $("#form_select_entityId #editItem_modal #insertItem_modal").prop('disabled', false);
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
    $("#form_select_entityId #storageType_modal").append(this.storagetypes);
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
  if($("#form_select_entityId .mySmallModalLabelBox").length>0){
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
    $('#form_select_entityId .mySmallModalLabelBox').html(div);
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
    $('#form_select_entityId').append(div);
  }
  //----------------------------------------------------------
  $(".mySmallModalLabelBox .myModalLabel").html(msg);
  $(".mySmallModalLabelBox").modal({show:true, keyboard: false, backdrop:'static'});
  //----------------------------------------------------------
  $(".mySmallModalLabelBox .btn.btn-yes").on("click", function(){
    callback(true);
    $(".mySmallModalLabelBox").modal('hide');
    //		$(".mySmallModalLabelBox").remove();
    //		$(".modal-backdrop"   ).remove();
  });
  //----------------------------------------------------------
  $(".mySmallModalLabelBox .btn.btn-no").on("click", function(){
    callback(false);
    $(".mySmallModalLabelBox").modal('hide');
    //		$(".mySmallModalLabelBox").remove();
    //		$(".modal-backdrop"   ).remove();
  });
  //----------------------------------------------------------
}
//--------------------------------------------------------------

//--------------------------------------------------------------


//--------------------------------------------------------------

export {
  DataTable_modal,
  showError,
  showSuccess,
  showConfirm
}
