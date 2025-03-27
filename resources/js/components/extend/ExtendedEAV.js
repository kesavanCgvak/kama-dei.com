import { DataTable, showError, showSuccess } from '../extend/DataTable'
import Columns from '../extend/Columns'

// import "../css/EAV.css"

class ExtendedEAV extends DataTable {
  constructor(data){
    super(data);
    this.pageSort = 'extendedEAVID';
   /* extendedEntityId
    extendedAttributeId*/
    this.extendedEntityId=0;
    this.extendedAttributeId=0;
    this.extendedSubTypeId=0;
    this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;
    this.editItem = {};


    $('body').on('click', '.edit-item', (e) => { this.showEditDialogHandler(e) });
    $('body').on('click', '#insertBtn', (e) => { this.showAddDialogHandler() });

    $('body').on('click', "#saveItem", (e) => { this.editItemConfirmHandler(e) });

   /* */
    // $('body').on('click', "#insertItem", (e) => { this.addConfirmHandler(e) });
    /*$('body').on('change input', '#editItem [name]', (e) => { this.formInputChangeHandler(e); });*/
    $('body').on('change select', '#extendedEntityId', (e) => { this.changeSelectextendedEntityId(e); });


    this.getExtendedTypes();

  }

  //--------------------------------------------------------------
  get getURL   () { return this.apiURL+'/page/'+this.extendedEntityId+'/'+this.extendedAttributeId+'/'+ this.orgID+'/' + this.pageSort + '/' + this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/';}
  get searchURL() {
    return this.apiURL + '/'+this.extendedEntityId+'/'+this.extendedAttributeId+'/' + this.orgID+'/' + this.pageSort + '/' +
      this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
      this.search;
  }
  get addURL   () { return this.apiURL+'/new/' +  this.orgID+'/'; }
  get editURL  () { return this.apiURL+'/edit'; }
  get deleteURL() { return this.apiURL + "/delete/" + this.orgID+'/' + this.deleteId; }
  get ExtendedDataViewsURL() { return this.apiURLBase + '/api/extend/extendeddataview/all/'+ this.extendedEntityId }
  //--------------------------------------------------------------
  changeSelectextendedEntityId(e){
    var target = e.currentTarget;
    var value = target.value;

    console.dir(target);
    console.dir(value);
    this.extendedEntityId=value;
    this.extendedSubTypeId=$("#extendedEntityId option[value$='"+value+"']").attr('extendedsubtypeid');
    $.get(this.ExtendedDataViewsURL, (res) => {
      this.editItem=res.data;
      console.dir(res.data)
      this.createAttrs(res.data);
    });
    /*if(value=='NULL'){value=0;}
    this.extendedAttributeId=value;
    this.refreshOptions()*/
  }
  getExtendedTypes() {
    $.get(this.extendedEntitysURL, (res) => {
      this.extendedEntitys = this.createSelectOptions(res.data, 'extendedEntityId', 'extendedEntityName');
      $("#extendedEntityId").append(this.extendedEntitys);
      $("#searc_extendedEntityName").append(this.extendedEntitys);
      this.extendedSubTypeId=res.data[0].extendedSubTypeId;
      this.extendedEntityId=res.data[0].extendedEntityId;
      console.dir(res.data);
      $.get(this.ExtendedDataViewsURL, (res) => {
        this.editItem=res.data;
        console.dir(res.data)
        this.createAttrs(res.data);
      });
      //"<option value='"+value+"'>"+label+"</option>"
    });
    $.get(this.attributesURL, (res) => {
      this.extendedAttributes = this.createSelectOptions(res.data, 'attributeId', 'attributeName');
      $("#extendedAttributeId").append(this.extendedAttributes);
      $("#searc_attributeName").append(this.extendedAttributes);
      //"<option value='"+value+"'>"+label+"</option>"
    });
  }
  createAttrs(data){
    var that= this;
    $("#attributes").html('')
    var for_function = function (i,oldata) {
      if(i<oldata.length){
        var row_attributes = $("<div>").attr({ class: "col-attribute_"+i+"  attributes-group",data_itemid:i });
        that.defaultAttrsFormInput(i,data[i],row_attributes,data,function (i,oldata) {
          i++;
          for_function(i,oldata)
        });
      }else{
        //指定允许上传的文件类型
        layui.use('upload', function(){
          var $ = layui.jquery
            ,upload = layui.upload;
          upload.render({
            elem: '.myuploadfile'
            ,url: '/api/upload_action'
            ,accept: 'file' //普通文件
            ,done: function(res){
              console.dir(this)
             var tmp_attr = this.item.attr('uploadattr');
              $('#'+tmp_attr).val(res.Src);
              console.log(res)
            }
          });
        });
        /*var tmp_attr=$('.col-attributes div[class$="attributes-group"]');
        for(var i=0;i<tmp_attr.length;i++){
          var tmp_items = $(tmp_attr[i]).attr('data_itemid');

          $(tmp_attr[i]).find("input[type='radio']").find("[value*='"+that.editItem[tmp_items].ownership+"']").prop('checked');
          $(tmp_attr[i]).find("option[value*='"+that.editItem[tmp_items].ownerId+"']").prop('selected');
          // this.editItem[tmp_items].valueString= $(tmp_attr[i]).find(".iput_stringvalue").val();
          // this.editItem[tmp_items].extendedEntityId=this.extendedEntityId;
        }*/
        // $(":checked").prop('selected');
        // $(":selected").prop('selected');
      }
    }
    for_function(0,data);


  }
  defaultAttrsFormInput(i,data,fdom,oldata,cb) {

    var tmp_name =data.displayName?data.displayName:data.attributeName;
    var tmp_value =data.valueString?data.valueString:data.defaultValue;
    var temp_memo= data.memo?data.memo:'';
    var ownerId= data.ownerId?data.ownerId:0;
    var tmp_ownership=data.ownership;
    fdom.append($("<label class='tmp_label'>"+tmp_name+"</label>"));


    if(data.storageType.indexOf('BLOB')>-1){
      fdom.append($("<input>").attr({
          name: tmp_name+'_'+i,
          id: tmp_name+'_'+i,
          placeholder: tmp_name,
          value:tmp_value,
          itemid:i,
          class: 'iput_stringvalue form-control',
          readonly:"readonly"

        })
      );
      fdom.append($('<button type="button" class="layui-btn myuploadfile" uploadattr="'+tmp_name+'_'+i+'" style="margin-left: 3px;" id="uploadfile_'+i+'"><i class="layui-icon"></i>Upload File</button>')
      );
    }else{
      fdom.append($("<input>").attr({
          name: tmp_name+'_'+i,
          id: tmp_name+'_'+i,
          placeholder: tmp_name,
          value:tmp_value,
          itemid:i,
          class: 'iput_stringvalue form-control'
        })
      );
    }

    $("#attributes").append(fdom);
    if(cb){
      cb(i,oldata);
    }

  }
  createSelectOptions(arr, valKey, labelKey) {
    var options = [];
    for(var i in arr){
      var value = arr[i][valKey];
      var label = arr[i][labelKey];

      if(valKey=='extendedEntityId'){
        var tmp_attr_SubTypeId = arr[i]['extendedSubTypeId'];
        options.push("<option value='"+value+"' extendedSubTypeId='"+tmp_attr_SubTypeId+"'>"+label+"</option>");
      }else{
        options.push("<option value='"+value+"'>"+label+"</option>");
      }

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
    /*for(var x in data) {
      var column = data[x];
      if(column.editable === false || column.primary === true || column.reserved === true) continue;
      var col = column.name;
      var label = column.display;
      if(column.onlyFor == null || column.onlyFor == this.orgID){
        formChildren.push(this.getActionFormInput(col, label));
      }
    }

    if(columns.reservedColumn !== null) {
      formChildren.push(this.getActionFormInput(columns.reservedColumn, 'Reserved'));
    }*/

    formChildren.push(this.getActionFormInput('extendedEntityId', 'Entity'));



    var row_attributes = $("<div>").attr({ class: "col-attributes form-group" })
      .append("<label>Attributes</label>")
      .append($("<div>").attr({ id: 'attributes' }));
    formChildren.push(row_attributes);

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
          class: 'btn btn-default',
          onClick: "$('#editItem').fadeOut()"
        })
      );

    form = $(form).append([submit, cancel]);
    wrapper = $(wrapper).append(form);

    return wrapper;
  }

  tableToolbar(){
    var toolbar = $("<div>").attr({
      id: 'tableToolbar'
    });

    if(this.hasInsertRow){
      toolbar.append(
        $("<button>Associate</button>").attr({
          id: 'insertBtn',
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
    $("#editItem").fadeIn();

    $("#extendedEntityId option[value$='"+this.extendedEntityId+"']").attr("selected",true);
    $.get(this.ExtendedDataViewsURL, (res) => {
      this.editItem=res.data;
      console.dir(res.data)
      this.createAttrs(res.data);
    });
  }

  rowActions(value, row, index, field) {
    if(row.ownerId == 0 && this.orgID != 0) return;
    //----------------------------------------------------------
    var rowAction = '<div class="row-actions"></div>';
    var deleteIcon = $("<a><small class='glyphicon glyphicon-trash'></small></a>").attr({
      href: '#',
      style: "color:#f3ae4e",
      class: 'delete-item',
      'data-itemid': row[this.columns.primaryColumn]
    });

    return $(rowAction).append(deleteIcon)[0].outerHTML;
  }

  editConfirmHandler(e){
    e.preventDefault();
  }
  editItemConfirmHandler(e){
    e.preventDefault();
    var tmp_attr=$('.col-attributes div[class$="attributes-group"]');
    for(var i=0;i<tmp_attr.length;i++){
      var tmp_items = $(tmp_attr[i]).attr('data_itemid');
      // this.editItem[tmp_items].ownership= $(tmp_attr[i]).find("input[type='radio']:checked").val()?$(tmp_attr[i]).find("input[type='radio']:checked").val():0;
      // this.editItem[tmp_items].ownerId= $(tmp_attr[i]).find("option:selected").val();
      this.editItem[tmp_items].valueString= $(tmp_attr[i]).find(".iput_stringvalue").val();
      this.editItem[tmp_items].extendedEntityId=this.extendedEntityId;
    }
   /* this.extendedEntityId=0;
    this.extendedAttributeId=0;
    this.extendedSubTypeId=0;*/
   /* $( $('.col-attributes div[class$="attributes-group"]')[i]).find("input[type='radio']:checked").val()*/

    console.dir(this.editItem);
    var that = this;
    //----------------------------------------------------------
    $.ajax({
      url: this.editURL,
      type: 'put',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      data: JSON.stringify(this.editItem),
      beforeSend: function(){ $("#editItem #saveItem").prop('disabled', true); },
      success: function(res){
        if(res.result == 0){
          $("#editItem").fadeOut(function(){ $("#editItem #saveItem").prop('disabled', false); });
          showSuccess('Item saved.');
          that.refreshOptions();
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
}

var columns = [
  { name: 'extendedEAVID', display: 'ID', primary: true, sortable: true },
  { name: 'extendedEntityId', display: 'Entity', onlyFor: 0, hidden: true },
  { name: 'extendedEntityName', display: 'Entity', onlyFor: 0, editable: false ,searchWhere: true},
  { name: 'valueString', display: 'valueString', sortable: true, search: true },
  { name: 'extendedAttributeId', display: 'Attribute', onlyFor: 0, hidden: true },
  { name: 'attributeName', display: 'Attribute',  onlyFor: 0, editable: false ,searchWhere: true},
  { name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
  { name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true , searchWhere: true},
  { name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },





  { name: 'memo', display: 'Memo', hidden: true},
  { name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true , hidden: true},
  { name: 'dateUpdated', display: 'Updated', sortable: true, editable: false, date: true , hidden: true},
  { name: 'lastUserId', hidden: true, editable: false, default: '1'},
];
var termColumns = new Columns(columns);

var data = {
  columns: termColumns,
  apiURL: apiURL + '/api/extend/eav'
}

if($("#extendedeav").length != 0){
  var table = new ExtendedEAV(data);
  table.createTable('extendedeav');
}
