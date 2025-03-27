import { DataTable, showError, showSuccess } from '../extend/DataTable'
import Columns from '../extend/Columns'

class ExtendedChatbotlog extends DataTable  {
  constructor(data){
    super(data);

    var icon1 = $('<a></a>').attr({ href: '#', 'data-desc': 'View', class: 'menu_view', 'data-onlyowner': 1 });
    var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'Email', class: 'menu_email', 'data-onlyowner': 1 });
    var deleteIcon = $("<a></a>").attr({
      href: '#',
      style: "color:#f3ae4e;",
      class: 'delete-item',
      'data-desc': 'Delete',
      'data-onlyowner': 1
    });
    var archiveIcon = $('<a></a>').attr({
      href: '#',
      style: "color:#2196f3;",
      class: 'archive-item',
      'data-desc': 'Archive',
      'data-onlyowner': 1
    });
    if(orgID==0||orgID=='0'){
      this.actionIcons = [icon1,icon2,archiveIcon,deleteIcon];
    }else{
      this.actionIcons = [icon1,icon2,archiveIcon];
    }


    this.chatlog_view=true;
    this.pageSort = 'chat_id';
    this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;
    this.s_time='2019-01-07 00:00:00',this.e_time='2019-01-07 23:59:59',this.user_id=0,this.org_id=0;
    this.searc_email='';
    this.archive=1;
    this.sele_s_e_time();
    $('body').on('change select', '#searc_org_id', (e) => { this.change_searc_org_id(e); });
    $('body').on('changeDate', '#start_time', (e) => { this.changeWherestart_time(e); });
    $('body').on('changeDate', '#end_time', (e) => { this.changeWhereend_time(e); });
    $('body').on('change input', '#searc_email', (e) => { this.change_searc_email(e); });
    $('body').on('click', '.menu_view', (e) => { this.showMenu_viewDialogHandler(e) });
    $('body').on('click', '.menu_email', (e) => { this.showMenu_emailDialogHandler(e) });
    $('body').on('click', '.closemode', (e) => { this.modal_default(e) });

    $('body').on('click', '.archive-item', (e) => { this.showArchiveDialogHandler(e) });
    $('body').on('click', "#archive-confirm", (e) => { this.archiveConfirmHandler() });
    $('body').on('change select', "#showArchived", (e) => { this.archiveClick(e) });
    //modal fade
    this.getorg_all();
    //this.changeWhere();
  }
  archiveClick(e){
    /*if($(' .showArchived').find('.toggle').hasClass('off')){
      this.archive=1;
    }else{
      this.archive=0;
    }*/

    var target = e.currentTarget;
    var value = target.value;
    console.dir(target);
    console.dir(value);
    if(value=='NULL'){value=1;}
    //this.entityId=value;

    this.archive=value;

    console.dir(this.archive);
    this.refreshOptions(true)

  }
  showDeleteDialogHandler(e) {
    e.preventDefault();
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
    console.dir(this.archiveId_arr)
    $('#deleteDialog_msg').html("Do you really want to delete "+this.archiveId_arr.length+" records?");
    $('#deleteDialog').fadeIn();
  }

  showArchiveDialogHandler(e) {
    e.preventDefault();

    this.archiveId = $(e.currentTarget).data('itemid');
    console.dir( this.archiveId);
    var getSelectRows = $(".btSelectItem");
    this.archiveId_arr=[];
    for(let ri=0;ri<getSelectRows.length;ri++){
      if(getSelectRows[ri].dataset.index==this.archiveId){
        getSelectRows[ri].checked=true;
      }
      if(getSelectRows[ri].checked){
        this.archiveId_arr.push(getSelectRows[ri].dataset.index)
      }
    }
    console.dir(getSelectRows)
    console.dir(this.archiveId )
    console.dir(this)

    $('#archiveDialog_msg').html("Do you really want to archive "+this.archiveId_arr.length+" records?");
    for(var ri=0;ri<this.rows.length;ri++){
      if(this.rows[ri].chat_id==this.archiveId&&this.rows[ri].archive==1){
        $('#archiveDialog_msg').html("Do you really want to restore "+this.archiveId_arr.length+" records?");
      }
    }
    $('#archiveDialog').fadeIn();
  }
  deleteConfirmHandler(){

    $("#deleteDialog").fadeOut();
    var table = this.table;

    $.ajax({
      url: this.deleteURL,
      type: 'put',
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
  archiveConfirmHandler(){
    $("#archiveDialog").fadeOut();
    var table = this.table;
    $.ajax({
      url: this.archiveURL,
      type: 'put',
      dataType: 'json',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      },
      data: JSON.stringify({archiveId_arr:this.archiveId_arr}),
      success: function(res){
        if(res.result == 1){ showError(res.msg); }
        else{
          showSuccess('Archived successfully');
          $(table).bootstrapTable('refresh');
        }
      },
      error: function(e){ showError('IN USE. DELETE RELATED RECORDS FIRST'); }
    })
  }

  deleteDialog() {
    var dialog = "<div id='deleteDialog' style='display:none;z-index:10'></div>";
    var inner = "<div></div>";
    var msg = "<div id='deleteDialog_msg'>Do you really want to delete this record?</div>";
    var actions = "<div class='deleteActions'></div>";
    var yes = $("<button>Yes</button>").attr({
      id: 'delete-confirm',
      class: 'btn btn-primary',
    });
    var no = $("<button>Cancel</button>").attr({
      class: 'btn btn-default',
      onClick: "$('#deleteDialog').fadeOut()"
    });
    actions = $(actions).append(yes).append(no);
    inner = $(inner).append(msg).append(actions);
    dialog = $(dialog).append(inner);
    return dialog;
  }

  modal_default(e){
    $('#chatbotLogModal').removeClass('in');
    $('#chatlogemailModal').removeClass('in');

    $('#chatbotLogModal').css('display','none');
    $('#chatlogemailModal').css('display','none');
  }
  changeWherestart_time(e){
    $('.datetimepicker-dropdown-bottom-right').css('display','none');
    console.dir(e.date);
    this.s_time=e.date.getTime()/1000+3600*13;
    this.pageNumber=1;
    this.refreshOptions(true)
  }
  changeWhereend_time(e){
    $('.datetimepicker-dropdown-bottom-right').css('display','none');
    console.dir(e.date);
    this.e_time=e.date.getTime()/1000+3600*13;
    this.pageNumber=1;
    this.refreshOptions(true)
  }

  change_searc_email(e){
    var target = e.currentTarget;
    var value = target.value;
    this.searc_email=value;
    this.pageNumber=1;
    this.refreshOptions(true)
    $('#searc_email').focus();
  }
  change_searc_org_id(e){
    var target = e.currentTarget;
    var value = target.value;
    if(value=='NULL'){value=0;}
    this.org_id=value;
    this.pageNumber=1;
    this.refreshOptions(true)
  }
  sele_s_e_time(){
    var curDate = new Date();
    var startDate=new Date(curDate.setDate(curDate.getDate()-7));
    var endDate=new Date();
    this.s_time=startDate.getTime()/1000+3600*13;
    this.e_time=endDate.getTime()/1000+3600*13;
    /*this.s_time=new Date('2019-01-04 00:00:00').getTime()/1000+3600*13;
    this.e_time=new Date('2019-01-04 23:59:59').getTime()/1000+3600*13;*/
  }
  //--------------------------------------------------------------
  get getURL   () { return this.apiURL+'/page/'+this.archive+ '/'+ ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/'
    + this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/';}
  get searchURL() {
    return this.apiURL+ '/'+this.archive + '/' + ((this.orgID) ? (this.orgID + '/') : '') + this.pageSort + '/' +
      this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber + '/' + this.columns.searchColumn + '/' +
      this.search;
  }

  get getURL   () {
    if(this.searc_email==''){
      this.searc_email='0';
    }
    if(this.search==''){
      this.search='0';
    }
    return this.apiURL+'/page/'+this.archive+ '/'+this.s_time+'/'+ this.e_time+'/'+this.user_id + '/'+this.org_id
    + '/'+this.pageSort + '/' + this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber
      + '/'+this.searc_email+ '/'+this.search+ '/';

  }
  get searchURL() {
    if(this.searc_email==''){
      this.searc_email='0';
    }
    if(this.search==''){
      this.search='0';
    }
    return this.apiURL+'/page/'+this.archive+ '/'+this.s_time+'/'+ this.e_time+'/'+this.user_id + '/'+this.org_id
      + '/'+this.pageSort + '/' + this.pageOrder + '/' + this.pageSize +'/' + this.pageNumber
      + '/'+this.searc_email+ '/'+this.search+ '/';

  }
  get org_allURL() { return this.apiURL + '/org_all' }
  getorg_all() {
    //'searc_'+where
    $.get(this.org_allURL, (res) => {
      this.org_all = this.createSelectOptions(res.data, 'org_id', 'org_name');
      $("#searc_org_id").append(this.org_all);
    });
  }

  tableToolbar(){
    var toolbar = $("<div>").attr({
      id: 'tableToolbar'
    });
    toolbar.append(this.searchWhere());
    toolbar.append(
      $('<input  id="searc_email" style="  height: 34px;\n' +
        '    padding: 6px 14px;\n' +
        '    font-size: 14px;\n' +
        '    line-height: 1.42857143;\n' +
        '    color: #000;\n' +
        '    background-color: #fff;\n' +
        '    background-image: none;\n' +
        '    border: 1px solid #cfd0d2;\n' +
        '    border-radius: 4px;\n' +
        '    -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);\n' +
        '    box-shadow: inset 0 1px 1px rgba(0,0,0,.075);" type="text" placeholder="Search User(email)">')
    );

    toolbar.append(
      $('<span style="\n' +
        '    margin-left: 1rem;\n' +
        '    margin-right: 0.2rem;\n' +
        '    font-weight: bolder;\n' +
        '">Between</span>')
    );
    toolbar.append(
      $('<input id="start_time" size="16" type="text"  readonly="" class="form_datetime" style="width: 176px;\n' +
        '    cursor: not-allowed;\n' +
        '    background-color: #eee;\n' +
        '    display: inline-block;\n' +
        '    height: 34px;\n' +
        '    padding: 4px 6px;\n' +
        '    font-size: 14px;\n' +
        '    line-height: 34px;\n' +
        '    color: #555;\n' +
        '    vertical-align: middle;\n' +
        '    border-radius: 4px;margin-left: 0.5rem;\n' +
        '    margin-right: 1rem;' +
        '">')
    );
    /*toolbar.append(
      $('<span style="\n' +
        '    margin-left: 1rem;\n' +
        '    margin-right: 0.2rem;\n' +
        '    font-weight: bolder;\n' +
        '">To</span>')
    );*/
    toolbar.append(
      $('<input id="end_time" size="16" type="text"  readonly="" class="form_datetime" style="width: 176px;\n' +
        '    cursor: not-allowed;\n' +
        '    background-color: #eee;\n' +
        '    display: inline-block;\n' +
        '    height: 34px;\n' +
        '    padding: 4px 6px;\n' +
        '    font-size: 14px;\n' +
        '    line-height: 34px;\n' +
        '    color: #555;\n' +
        '    vertical-align: middle;\n' +
        '    border-radius: 4px; margin-left: 0.5rem;\n' +
        '    margin-right: 1rem;' +
        '">')
    );

    return toolbar;


  }
  rowActions(value, row, index, field) {
    //----------------------------------------------------------
    var icons = this.actionIcons;
    $("[data-menu-toggle='#actions-menu-"+index+"']").remove();
    //----------------------------------------------------------




    if(icons.length==0){ return ''; }
    //----------------------------------------------------------
    var rowAction = '<div class="row-actions" ></div>';
    if(row.archive!=undefined){
       rowAction = '<div class="row-actions"  archivevalue="'+row.archive+'"></div>';
    }

    //----------------------------------------------------------
    var others = '<ul class="menu-actions" data-menu data-menu-toggle="#actions-menu-'+index+'" style="font-size:12px;"></ul>';
    for (var i in icons){
      if(icons[i].attr('class')=='archive-item'){
        console.dir(row.archive);
        if(row.archive==1){
          icons[i].attr('data-desc','Restore')
        }else{
          icons[i].attr('data-desc','Archive')
        }
      }
      icons[i].attr('data-itemid', row[this.columns.primaryColumn]);
      var $icon = icons[i].clone();
      $icon = $icon.append('&nbsp;&nbsp;'+$icon.data('desc'));
      others = $(others).append('<li>'+$icon[0].outerHTML+'</li>');
    }
    var toggle = '<a href="#" class="toggle" id="actions-menu-'+index+'" style="color:dimgray"><small class="glyphicon glyphicon-chevron-down"></small></a>';
    var othersIcon = '<span>'+toggle+'</span>';
    rowAction = $(rowAction).append(othersIcon);
    $("body").append(others);
    $(document).ready(function(e){console.dir(123); $("[data-menu]").menu(); });
    //----------------------------------------------------------
    return $(rowAction)[0].outerHTML;
  }

  showMenu_viewDialogHandler(e){
    e.preventDefault();
    //$('#deleteDialog').fadeIn();
    var tabindex = $(e.currentTarget).attr('data-itemid');
    $('#chatbotLogModal').addClass('in');
    $('#chatbotLogModal').css('display','block')
    $('#chatbotLogModal').css('overflow','auto')
    var getheadtalbe=function(data){

      let table_str='<table width="100%" cellspacing="0" cellpadding="5" border="1">\n' +
        '                    <tbody>\n' +
        '                      <tr>\n' +
        '                        <td colspan="2" class="">\n' +
        '                          <div >'+data.memo+'</div>\n' +
        '                        </td>\n' +
        '                      </tr>\n' +
        '                      <tr>\n' +
        '                        <td><b>IP</b></td>\n' +
        '                        <td>&nbsp;'+data.ip+'</td>\n' +
        '                      </tr>\n' +
        '                      <tr>\n' +
        '                        <td><b>Chat start time</b></td>\n' +
        '                        <td>&nbsp;'+data.timestamp+'</td>\n' +
        '                      </tr>\n' +
        '                      <tr>\n' +
        '                        <td><b>User ID</b></td>\n' +
        '                        <td>&nbsp;'+data.user_id+'</td>\n' +
        '                      </tr>\n' +
        '                      <tr>\n' +
        '                        <td><b>User Name</b></td>\n' +
        '                        <td>&nbsp;'+data.user_name+'</td>\n' +
        '                      </tr>\n' +
        '                      <tr>\n' +
        '                        <td><b>Org ID</b></td>\n' +
        '                        <td>&nbsp;'+data.org_id+'</td>\n' +
        '                      </tr>\n' +
        '                      <tr>\n' +
        '                        <td><b>Org Name</b></td>\n' +
        '                        <td>&nbsp;'+data.org_name+'</td>\n' +
        '                      </tr>\n' +
        '                      <tr>\n' +
        '                        <td><b>Email</b></td>\n' +
        '                        <td>&nbsp;'+data.email+'</td>\n' +
        '                      </tr>\n' +
        '                    </tbody>\n' +
        '                  </table>';

      return table_str;
    }

    var getneirongtable=function(data,ainame,username){

      let tmp_table1='<div style="width: 100%; height: auto;">';

      let tmp_table='<table width="100%" cellspacing="0" cellpadding="5" border="1">\n' +
        '                    <tbody>\n' +
        '                      <tr>\n' +
        '                        <td>\n' +
        '                          <div>Chat  Transcript</div>\n' +
        '                        </td>\n' +
        '                      </tr>\n' +
        '                      <tr>\n' +
        '                        <td>' ;



      /*'                          <span >Brittany:</span><font color=""> Enjoy your weekend Brian, cheers!</font><br>\n' +
      '                          <span >Brian:</span><font color="red"> They have live chat as part of\n' +
      '                            HubSpot and are investigating robo-chat.</font><br>\n' +
      '                          <span >Brian:</span><font color="red"> Thanks,</font><br>\n' +*/


      let temp_end= '                        </td>\n' +
        '                      </tr>\n' +
        '                    </tbody>\n' +
        '                  </table>';

      let temp_body='';
      if(data){

        var tempdata={};
        for(let i=0;i<data.length;i++){



          if(data[i].sender=='AI'){

            var newM = JSON.parse(data[i].raw_msg);

            if (newM.response.type == 'text') {
              if(newM.response.err){
                tmp_table1+='<div class="chat_v bot" style="height: auto;display: flex;justify-content: flex-start;align-items: center;padding: 0.25rem 0.25rem 0 0.25rem;">' +
                  '<div class="chat_b" style="position: relative;overflow: hidden;max-width: 100%;background: #efefef;width: auto; height: auto;min-height: 0.6rem;font-size: 12px;border-radius: 0.15rem;color: #ff0000;">' +
                  '<p style="margin: 0.15rem 0.2rem;line-height: 2rem; word-wrap: break-word;">'+ainame+'<br>' + newM.response.message+'<br>' +data[i].timestamp+ '</p></div></div>';

              }else{
                tmp_table1+='<div class="chat_v bot" style="height: auto;display: flex;justify-content: flex-start;align-items: center;padding: 0.25rem 0.25rem 0 0.25rem;">' +
                  '<div class="chat_b" style="position: relative;overflow: hidden;max-width: 100%;background: #efefef;color: #6f6f6f;width: auto; height: auto;min-height: 0.6rem;font-size: 12px;border-radius: 0.15rem;">' +
                  '<p style="margin: 0.15rem 0.2rem;line-height: 2rem; word-wrap: break-word;">'+ainame+'<br>'  + newM.response.message +'<br>' +data[i].timestamp+  '</p></div></div>';
              }
            } else if (newM.response.type == 'yesno') {
              tmp_table1+='<div class="chat_v bot" style="height: auto;display: flex;justify-content: flex-start;align-items: center;padding: 0.25rem 0.25rem 0 0.25rem;">' +
                '<div class="chat_b" style="position: relative;overflow: hidden;max-width: 100%;background: #efefef;color: #6f6f6f;width: auto; height: auto;min-height: 0.6rem;font-size: 12px;border-radius: 0.15rem;">' +
                '<p style="margin: 0.15rem 0.2rem;line-height: 2rem; word-wrap: break-word;">'+ainame+'<br>'   + newM.response.message +'<br>' +data[i].timestamp+  '</p>' +
                '<div class="rd" style="padding: 0.2rem;margin-top: -0.2rem;">' +
                '<span style="    box-sizing: border-box;border: 1px solid rgb(140, 198, 63);border-radius: 0.25rem;color: rgb(140, 198, 63);margin-right: 0.2rem;padding: 0.1rem 0.25rem 0.1rem 0.25rem;line-height: 2rem;font-size: 12px;box-shadow: 0 0 1px 1px rgba(140, 198, 63, 0.2);float: left;margin-bottom: 0.2rem;">Yes</span>' +
                '<span style="    box-sizing: border-box;border: 1px solid rgb(140, 198, 63);border-radius: 0.25rem;color: rgb(140, 198, 63);margin-right: 0.2rem;padding: 0.1rem 0.25rem 0.1rem 0.25rem;line-height: 2rem;font-size: 12px;box-shadow: 0 0 1px 1px rgba(140, 198, 63, 0.2);float: left;margin-bottom: 0.2rem;">No</span>' +
                '</div>' +
                '</div>';
            } else if (newM.response.type == 'radiobutton') {
              var rd = '';
              for (var ii = 0; ii < newM.response.answers.length; ii++) {
                rd += '<span style="box-sizing: border-box;border: 1px solid rgb(140, 198, 63);border-radius: 0.25rem;color: rgb(140, 198, 63);margin-right: 0.2rem;padding: 0.1rem 0.25rem 0.1rem 0.25rem;\n' +
                  '    line-height: 2rem;font-size: 12px;box-shadow: 0 0 1px 1px rgba(140, 198, 63, 0.2);float: left;\n' +
                  '    margin-bottom: 2rem;" type=' + newM.response.answers[ii].value + '>' + newM.response.answers[ii].text + '</span>'

                tempdata[newM.response.answers[ii].value]=newM.response.answers[ii].text;
              }
              tmp_table1+='<div class="chat_v bot" style="height: auto;display: flex;justify-content: flex-start;align-items: center;padding: 0.25rem 0.25rem 0 0.25rem;">' +
                '<div class="chat_b" style="position: relative;overflow: hidden;max-width: 100%;background: #efefef;color: #6f6f6f;width: auto; height: auto;min-height: 0.6rem;font-size: 12px;border-radius: 0.15rem;">' +
                '<p style="margin: 0.15rem 0.2rem;line-height: 2rem; word-wrap: break-word;">'+ainame+'<br>' + newM.response.message +'<br>' +data[i].timestamp+  '</p>' +
                '<div class="rd" style="padding: 0.2rem;margin-top: -0.2rem;">' + rd + '</div></div>' +
                '</div>';
            } else if (newM.response.type == 'valueslider') {
              var rd = '';
              for (var ii = 0; ii < newM.response.answers.length; ii++) {
                rd += '<div class="sd_g" type=' + newM.response.answers[ii].value + '>' +
                  '<p style="display:block;width:5rem;max-width:5rem;margin-bottom:0;margin-top:0;font-size:12px;">' + newM.response.answers[ii].text + ':' +
                  '<span class="tn">' + '【 (1 to 10), (' + newM.response.answers[ii].value + ')】' + ' </span></p>' +
                  '<div class="sd_l"></div>' +
                  '<div class="sd_s">' +
                  '<div class="sd_b" style="left:' + newM.response.answers[ii].value * 10 + '%' + '"></div></div></div>'
              }
              tmp_table1+='<div class="chat_v bot" style="height: auto;display: flex;justify-content: flex-start;align-items: center;padding: 0.25rem 0.25rem 0 0.25rem;">' +
                '<div class="chat_b" style="position: relative;overflow: hidden;max-width: 100%;background: #efefef;color: #6f6f6f;width: auto; height: auto;min-height: 0.6rem;font-size: 12px;border-radius: 0.15rem;">' +
                '<p style="margin: 0.15rem 0.2rem;line-height: 2rem; word-wrap: break-word;">'+ainame+'<br>' + newM.response.message + '</p>' +
                '<div class="sd">' + rd + '<div class="sd_sb" style="box-sizing: border-box;\n' +
                '  border: 1px solid rgb(140, 198, 63);\n' +
                '  border-radius: 0.25rem;\n' +
                '  color: rgb(140, 198, 63);\n' +
                '  margin-right: 0.2rem;\n' +
                '  height: 0.5rem;\n' +
                '  margin: 0 auto;\n' +
                '  margin-top: 0.2rem;\n' +
                '  width: 3rem;\n' +
                '  margin-bottom: 0.2rem;\n' +
                '  text-align: center;\n' +
                '  line-height: 2rem;\n' +
                '  font-size: 12px;\n' +
                '  box-shadow: 0 0 1px 1px rgba(140, 198, 63, 0.2);">click when done</div></div></div>' +
                '<br><p style="margin: 0.15rem 0.2rem;line-height: 2rem; word-wrap: break-word;">' +data[i].timestamp+ '</p></div>';
            }else{
              tmp_table1+='<div class="chat_v bot" style="height: auto;display: flex;justify-content: flex-start;align-items: center;padding: 0.25rem 0.25rem 0 0.25rem;">' +
                '<div class="chat_b" style="position: relative;overflow: hidden;max-width: 100%;background: #efefef;color: #6f6f6f;width: auto; height: auto;min-height: 0.6rem;font-size:12px;border-radius: 0.15rem;">' +
                '<p style="margin: 0.15rem 0.2rem;line-height: 2rem; word-wrap: break-word;">'+ainame+'<br>' + newM.response.message+'<br>'+data[i].timestamp + '</p></div></div>';
            }




            //temp_body=temp_body+'<b><span>'+ainame+':</span></b><br>'+data[i].raw_msg+'<br>' +

            //  '---------------------------------------timestamp:'+data[i].timestamp+'<br><br>';
          }else{
            var temp_mmsg=data[i].raw_msg
            if(tempdata[data[i].raw_msg]){
              temp_mmsg=tempdata[data[i].raw_msg]
            }
            tmp_table1+='<div class="chat_v mine" style="height: auto;display: flex;justify-content: flex-end;align-items: center;padding: 0.25rem 0.25rem 0 0.25rem;">' +
              '<div class="chat_b" style=" -webkit-transform-origin: top right;width: auto;height: auto;background: rgb(140, 198, 63);min-height: 0.6rem;font-size:12px;color: #fff;border-radius: 0.15rem;">' +
              '<p style="margin: 0.15rem 0.2rem;line-height: 2rem; word-wrap: break-word;">' +username+'<br>' +temp_mmsg +'<br>'+data[i].timestamp+ '</p></div></div>';
            //temp_body=temp_body+'<b><span>'+username+':</span></b><br><font color="red">'+data[i].raw_msg+'</font><br><font color="red">' +
            // '---------------------------------------timestamp:'+data[i].timestamp+'</font><br><br>';
          }
        }
      }
      //return tmp_table+temp_body+temp_end;
      return tmp_table1+'</div>';

    }

    $.each(this.rows, (i, item) => {
      if(item[this.columns.primaryColumn] == tabindex){
        this.editItem = item;
        $('#chatbotLogModal .modal-body').html(getheadtalbe(item));
        return false;
      }
    });
    $.get(this.apiURL+'/chatlog/'+tabindex, (res) => {
      if(res.result == 1){ showError(res.msg); }
      else{
        $('#chatbotLogModal .modal-body').html($('#chatbotLogModal .modal-body').html()+getneirongtable(res.data,this.editItem.org_name,this.editItem.user_name));
      }
    });

    //$('#chatbotLogModal .modal-body').html('Menu_view '+tabindex+' successfully');


  }
  showMenu_emailDialogHandler(e){
    e.preventDefault();
    var tabindex = $(e.currentTarget).attr('data-itemid');
    $('#chatlogemailModal').addClass('in');
    $('#chatlogemailModal').css('display','block');
    //showSuccess('Menu_email '+tabindex+' successfully');
    //$('#chatlogemailModal .modal-body').html('Menu_email '+tabindex+' successfully');
    $('#sendemailbut').off('click');

    $('#sendemailbut').on('click',
      function() {
      var tempemail =$('#chatlogemailinput').val();
      if(tempemail){
        $.ajax({
          //url: 'http://localhost:5000/email_php',
          //url: 'http://138.197.148.74:5000/email_php',
          //url: 'http://52.14.155.255:6002/email_php',
          url: 'https://kama-dei.com:6002/email_php',
          data: {chat_id:tabindex,emails:tempemail},
          type: 'POST',
          async: true,
          success: (data) => {
            $('#chatlogemailModal').modal('hide');
            showSuccess('Successful mail delivery.');
          }
        });
        $('#chatlogemailModal').removeClass('in');
        $('#chatlogemailModal').css('display','none');
      }else{
        showError('Please enter the mail.');
      }

    });

  }


}

var columns = [
  /*{ checkbox: true},*/
  {name: 'state', checkbox: true},
  { name: 'chat_id', display: 'ID',field: 'selectItem',primary: true, sortable: true },
  { name: 'email', display: 'User(email)',sortable: true, search: true },

  { name: 'user_name', display: 'Nick Name',sortable: true},
  { name: 'timestamp', display: 'Start Date/Time', sortable: true},

  { name: 'org_id', display: 'Owner', hidden: true, searchWhere: true},
  { name: 'user_id', display: 'Nick Name', hidden: true },


  { name: 'org_name', display: 'Owner', sortable: true},

  { name: 'log_s', display: 'Duration'},
  { name: 'logcount', display: 'Log Count'},

  { name: 'ip', display: 'IP',sortable: true },
  { name: 'memo', display: 'Memo',hidden: true},
];
var termColumns = new Columns(columns);

var data = {
  columns: termColumns,
  apiURL: apiURL + '/api/extend/chatbotlog'
}

if($("#chatbotlog").length != 0){
  var table = new ExtendedChatbotlog(data);
  table.createTable('chatbotlog');
  var dataFormat = function(thisdata,fmt)
  { //author: meizz
    var o = {
      "M+" : thisdata.getMonth()+1,                 //月份
      "d+" : thisdata.getDate(),                    //日
      "h+" : thisdata.getHours(),                   //小时
      "m+" : thisdata.getMinutes(),                 //分
      "s+" : thisdata.getSeconds(),                 //秒
      "q+" : Math.floor((thisdata.getMonth()+3)/3), //季度
      "S"  : thisdata.getMilliseconds()             //毫秒
    };
    if(/(y+)/.test(fmt))
      fmt=fmt.replace(RegExp.$1, (thisdata.getFullYear()+"").substr(4 - RegExp.$1.length));
    for(var k in o)
      if(new RegExp("("+ k +")").test(fmt))
        fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
    return fmt;
  }
  var curDate = new Date();
  var  endDate=dataFormat(curDate,"yyyy-MM-dd hh:mm:ss");
  var startDate = new Date(curDate.setDate(curDate.getDate()-7));
  startDate=dataFormat(startDate,"yyyy-MM-dd hh:mm:ss");
  $("#start_time").val(startDate);
  $("#end_time").val(endDate);
  $(".form_datetime").datetimepicker({format: 'yyyy-mm-dd hh:ii:ss'});
  var  chatlog_viewForm=function(){
    var htmlviewstr='<!-- 模态框（Modal） -->\n' +
      '<div class="modal fade" id="chatbotLogModal" tabindex="-1" role="dialog" aria-labelledby="chatbotLogModalLabel" aria-hidden="true">\n' +
      '\t<div class="modal-dialog">\n' +
      '\t\t<div class="modal-content">\n' +
      '\t\t\t<div class="modal-header">\n' +
      '\t\t\t\t<button type="button" class="close closemode" data-dismiss="modal" aria-hidden="true">\n' +
      '\t\t\t\t</button>\n' +
      '\t\t\t\t<h4 class="modal-title" id="chatbotLogModalLabel">Chatbot Log\n' +
      '\t\t\t\t</h4>\n' +
      '\t\t\t</div>\n' +
      '\t\t\t<div class="modal-body">\n' +
      '\t\t\t</div>\n' +
      '\t\t\t<div class="modal-footer">\n' +
      '\t\t\t\t<button type="button" class="btn btn-default closemode"  data-dismiss="modal">Cancel\n' +
      '\t\t\t\t</button>\n' +
      '\t\t\t</div>\n' +
      '\t\t</div><!-- /.modal-content -->\n' +
      '\t</div><!-- /.modal -->\n' +
      '</div>';
    if($('#chatbotlog'))
      $('#chatbotlog').append(htmlviewstr);
  }

  chatlog_viewForm();
  var  chatlogemailForm=function(){
    var htmlviewstr='<!-- 模态框（Modal） -->\n' +
      '<div class="modal fade" id="chatlogemailModal" tabindex="-1" role="dialog" aria-labelledby="chatlogemailModalLabel" aria-hidden="true">\n' +
      '\t<div class="modal-dialog">\n' +
      '\t\t<div class="modal-content">\n' +
      '\t\t\t<div class="modal-header">\n' +
      '\t\t\t\t<button type="button" class="close closemode"  data-dismiss="modal" aria-hidden="true">\n' +
      '\t\t\t\t</button>\n' +
      '\t\t\t\t<h4 class="modal-title" id="chatlogemailModalLabel">Send email(s) \n' +
      '\t\t\t\t</h4>\n' +
      '\t\t\t</div>\n' +
      '\t\t\t<div class="modal-body">\n' +
      '<div class="input-group input-group-lg">\n' +
      '            <span class="input-group-addon">Email</span>\n' +
      '            <input type="text" class="form-control" placeholder="Email Address(es)" id="chatlogemailinput">\n' +
      '        </div>'+
      '\t\t\t</div>\n' +
      '\t\t\t<div class="modal-footer">\n' +
      '\t\t\t\t<button type="button" class="btn btn-default closemode" data-dismiss="modal">Cancel\n' +
      '\t\t\t\t</button>\n' +
      '\t\t\t\t<button type="button" class="btn btn-primary" id="sendemailbut" >Send\n' +
      '\t\t\t\t</button>\n' +
      //<button type="button" className="btn btn-primary">提交更改</button>
      '\t\t\t</div>\n' +
      '\t\t</div><!-- /.modal-content -->\n' +
      '\t</div><!-- /.modal -->\n' +
      '</div>';
    if($('#chatbotlog'))
      $('#chatbotlog').append(htmlviewstr);
  }
  chatlogemailForm();


 /* var tmpshowArchiveditems=[

    "<option value=1>Show UnArchived</option>",
    "<option value=0>Show All</option>",
    "<option value=2>Show Archived</option>"
  ];
  $(".fixed-table-pagination").before($('<div class="showArchived" style="margin-left: 350px;      position: absolute;  margin-top: 20px;"></div>').append($("<label>Show Archived: </label>").attr({
    style:'margin:0 5px 0 20px;'
  }))
   /!* .append(
      $("<input checked >").attr({
        id  : 'showArchived',
        type: 'checkbox' ,
        'data-onstyle': 'info',
        'data-toggle' : 'toggle',
        'data-size'   : 'small',
        'data-on'     : 'Yes',
        'data-off'    : 'No'
      })
    )*!/
    .append($("<select>").attr({
        id: 'showArchived',
        name: 'showArchived',
        value: 1,
        class: "where-control"
      }).append(tmpshowArchiveditems)
    )
  );*/





}




