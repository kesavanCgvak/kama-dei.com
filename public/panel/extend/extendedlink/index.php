<?php
$chatIntro_voiceIntro_maxlength = \Config('kama_dei.static.chatIntro_voiceIntro_maxlength', 1000);
?>
<style>
    #searchWhere,.where-group{
        display: inline-block;
        line-height: 10px;
        margin: 0px 10px 0px 10px;
    }
    .where-group select{
        width: 100%;
        height: 34px;

        display: block;
        height: 34px;
        padding: 3px;
        font-size: 12px;
        line-height: 1.42857143;
        color: #000;
        background-color: #fff;
        background-image: none;
        border: 1px solid #cfd0d2;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        -webkit-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    }

    #searchWhere{
        float: right;
        /*margin-right: 186px;*/
    }
    .where-group label{ display: none; }
    .pull-left{ width: 100%; }
    .pull-left.pagination-detail{ width: 50%; }
    .pull-right{ top: -58px; }
    .fixed-table-container{ top: -58px; }

    #editExtendedLink, #addExtendedLink {
        display: none;
        position: fixed;
        z-index: 1000;
        background: rgba(0, 0, 0, 0.6);
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
        margin: auto;
    }

    #editExtendedLink.show, #addExtendedLink.show { display: block; }

    #editExtendedLink > form, #addExtendedLink > form {
        position: absolute;
        margin: auto;
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
        width: 280px;
        height: 370px;
        width: fit-content;
        height: fit-content;
        background: white;
        padding: 15px;
    }

    #editExtendedLink > form input, #addExtendedLink > form input, #editExtendedLink > form select, #addExtendedLink > form select, #insertItem {
        width: 250px !important;
    }
    #extendedlink .action-form{ min-height:500px; max-height:80vh; overflow: auto; }
    .divUnderButtons{
        position: sticky;
        bottom: 0;
        border: 1px solid #eee;
        left: 0;
        right: 0;
        padding: 5px;
        background: #fff;
    }
    @media (max-height: 650px){
        #extendedlink .action-form{ max-height:95vh; overflow: auto; min-height:60vh; }
        .col-reserved.form-group{ margin-top:-10px; }
    }

    #insertItem, #saveItem { width: 250px !important; float:right; }


    .react-bs-table-bordered, .react-bs-container-body { height: auto !important; }

    .row-actions { text-align: center; }

    .row-actions > a:first-child { padding-right: 10px; }
    #extendedlink table th{ font-size:13px; }
    #extendedlink table td{ font-size:12px; }
    
    #extendedlink table td:nth-child(1)
    #extendedlink table th:nth-child(2){ width:330px !important; }
    #extendedlink table th:nth-child(3){ width:270px !important; }
    #extendedlink table th:nth-child(4)
    #extendedlink table th:nth-child(5){ width:250px !important; }
    #extendedlink table th:nth-child(6){ width:80px !important; }
    #extendedlink table th:nth-child(7),
    #extendedlink table th:nth-child(8),
    #extendedlink table th:nth-child(9){ width:40px !important; }

    #extendedlink table td:nth-child(2){ width:330px !important;}
    #extendedlink table td:nth-child(3){ width:270px !important;}
    #extendedlink table td:nth-child(4)
    #extendedlink table td:nth-child(5){ width:250px !important; }
    #extendedlink table td:nth-child(6){ width:80px !important;text-align: center; }
    #extendedlink table td:nth-child(7),
    #extendedlink table td:nth-child(8),
    #extendedlink table td:nth-child(9){ width:40px !important;text-align: center; }

    .form-group i.fa-search:hover{ color:red; cursor:pointer; }

    .reorder_rows_onDragClass td {
        background-color: #eee;
        -webkit-box-shadow: 11px 5px 12px 2px #333, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset;
        -webkit-box-shadow: 20px 3px 7px -4px #4dbfb5, 18px 3px 9px #35afa2, 19px -1px 14px 0px #3291e0;
        -moz-box-shadow: 6px 4px 5px 1px #555, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset;
        -box-shadow: 6px 4px 5px 1px #555, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset;
    }

    .reorder_rows_onDragClass td:last-child {
        -webkit-box-shadow: 8px 7px 12px 0 #333, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset;
        -webkit-box-shadow: 20px 3px 7px -4px #4dbfb5, 18px 3px 9px #35afa2, 19px -1px 14px 0px #3291e0;
        -moz-box-shadow: 0 9px 4px -4px #555, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset, -1px 0 0 #ccc inset;
        -box-shadow: 0 9px 4px -4px #555, 0 1px 0 #ccc inset, 0 -1px 0 #ccc inset, -1px 0 0 #ccc inset;
    }
    
    #tableToolbar{ max-width:83% !important; vertical-align:top; }
    #searchWhere{ margin:0!important; max-width:63%; }
    #searchWhere>.where-group{ margin:0 0 5px 1% !important; width:23%; }
    .where-group i:hover{ color:red; font-size:large; }
    .where-group .form-control{ padding: 0 10px; font-size: 12px; }
    
    #extendedlink .nav-tabs{ text-transform:none; border-bottom:none; }
    #extendedlink .nav-tabs>li>a{ color: #3c3d3e; background-color: #f5f6f7; border-color: #2a94d6; padding:6px 10px; font-size:small; }
    #extendedlink .nav-tabs li.active a{ border-bottom-color:transparent; background-color:#2a94d6; color:#fff; }
    
    #extendedlink .col-sampleChatDisplay,
    #extendedlink .col-sampleVoiceText label{ width:100%; text-align:center; margin-top:-18px; }
    
    .pull-right.search{ max-width: 18%; }
    
    .hint{
        width: 100%;
        text-align: left;
        display: block;
        margin-top: 5px;
        font-style: italic;
    }
    select#termsList{ overflow-x: auto; }
    
    #searchItemText{
        width    : calc(100% - 55px);
        min-width: calc(100% - 55px);
        max-width: calc(100% - 55px);
        
        height    : 80px;
        min-height: 80px;
        max-height: 80px;
        
        vertical-align: top;
        display: inline-block;
    }
</style>

<div id="languageItems" style="display: none">
    <div style="width:100%; margin:0 0 15px; border:3px solid #ccc; border-radius:8px; padding:10px 12px;" >
        <input type="hidden" id="thisItemID" />
        <div style="width: 100%; display: block">
            <select class="form-control" id="languageCode" name="languageCode"></select>
        </div>

        <div style="width: 100%; display: block; margin-top: 15px;">
            <label style="display:block; width: 100%">
                Add Chat Intro
                <small style="float: right">max:<?=$chatIntro_voiceIntro_maxlength;?></small>
            </label>
            <input placeholder="Chat Intro" class="form-control" id="chatIntro" name="chatIntro" 
                   maxlength="<?=$chatIntro_voiceIntro_maxlength;?>"
            />
        </div>

        <div style="width: 100%; display: block; margin-top: 15px;">
            <label style="display:block; width: 100%">
                Add Voice Intro (if empty, Chat Intro will be used)
                <small style="float: right">max:<?=$chatIntro_voiceIntro_maxlength;?></small>
            </label>
            <input placeholder="Voice Intro" class="form-control" id="voiceIntro" name="voiceIntro" 
                   maxlength="<?=$chatIntro_voiceIntro_maxlength;?>"
            />
            <small id="defaultTXT"></small>
        </div>

    </div>
</div>

<div id="extendedlink"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script type="text/javascript">
    var apiURL = "<?=env('API_URL');?>";
    var orgID  = "<?=$orgID;?>";
    var userID = "<?=session()->get('userID');?>";
    var requestTableName  = "<?=$requestTableName;?>";
    var requestParentId  = "<?=$requestParentId;?>";
    var table;
    var termPerPage = 100;
    var chatIntro_voiceIntro_maxlength = <?=$chatIntro_voiceIntro_maxlength?>;
</script>

<link  href="/public/layui/css/layui.css" rel="stylesheet">
<!--采用模块化方式-->
<script  src="/public/layui/layui.js"></script>
<script  src="/public/js/jquery.js"></script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
  $(function(){
    //--------------------------------------------------------
    $("select#parentId, select#parentTable, select#entityId").on('change', function(){
      var tempVar =
        $("select#parentTable option:selected").text()+' '+
        $("select#parentId option:selected").text()+' '+
        $("select#entityId option:selected").text();
      $("input#parentIdTEMP" ).val($("select#parentId option:selected" ).text());
      $("input#entityIdTEMP").val($("select#entityId option:selected").text());
      $("input#sampleChatDisplay").val(tempVar);
    });

    $("select#parentName, select#entityName").on('change', function(){

      $("input#parentNameTEMP" ).val($("select#parentName option:selected" ).text());
      $("input#entityNameTEMP").val($("select#entityName option:selected").text());
    });

    //--------------------------------------------------------
    $("#searchItemText").on('change', function(){
        //table.searchTermByName($("#objID").val().trim(), $(this).val().trim(), $("#modal_ownerId").val().trim());
    });
	$("#goBTN").on("click", function(){
        table.searchTermByName($("#objID").val().trim(), $("#searchItemText").val().trim(), $("#modal_ownerId").val().trim());
	});
    //--------------------------------------------------------
    $("#searchBox").on('shown.bs.modal', function(){ $("#searchItemText").focus();} );
    //--------------------------------------------------------
    $("#modal_ownerId").on('change select',function () {
      table.searchTermByName($("#objID").val().trim(),  $("#searchItemText").val().trim(), $("#modal_ownerId").val().trim());
    })
    //--------------------------------------------------------
    $("#modal_ownerId").on('change #showGlobalExtData',function () {
      table.searchTermByName($("#objID").val().trim(),  $("#searchItemText").val().trim(), $("#modal_ownerId").val().trim());
    })
    //--------------------------------------------------------
  });
  //------------------------------------------------------------
  function cancelSearchBox(){
    //table.searchTermByName($("#objID").val().trim(),  $("#searchItemText").val().trim(), $("#modal_ownerId").val().trim());
    let objID = $("#objID").val();
	$("#"+objID).val(0).change();
    if(objID=='parentName'){ $("#"+objID+"TEMP").val('Parents All'); }
    else if(objID=='entityName'){ $("#"+objID+"TEMP").val('Ext. Data All'); }
    else if(objID=='parentId'){ $("#"+objID+"TEMP").val('Search ...'); }
    else if(objID=='entityId'){ $("#"+objID+"TEMP").val('Search ...'); }
    else $("#"+objID+"TEMP").val('me['+objID+"]");
    $("#searchBox").modal('hide');
  }
  //------------------------------------------------------------
  function showSearchBox(objID, label){
    $("#modal_ownerId").val(-1);
    if($("#showGlobalExtData").prop('checked')==false){ $("#showGlobalExtData").prop('checked', true).change(); }
    $("#showGlobalExtData").bootstrapToggle("disable");
    //--------------------------------------------------------
    $("#objID").val(objID);
    $("#searchBox .modal-header").text('enter '+label);
    let serchVal = '';
    if($("#"+objID).val().trim()!='0'){ serchVal = $("#"+objID+" option:selected").text(); }
    $("#searchItemText").val(serchVal);
	if(serchVal!=''){
		table.searchTermByName($("#objID").val().trim(),  serchVal, $("#modal_ownerId").val().trim());
	}else{
		$("#termsList option").remove();
	}
    
    /*
    $("#searchItemText").val('');
    $("#objID").val(objID);
    $("#searchBox .modal-header").text('enter '+label);
    //--------------------------------------------------------
    $("#termsList option").remove();
    $("#"+objID+" option").each(function(){
        var value = $(this).attr('value');
        var text  = $(this).text();
        if(value==0){ return; }
        $("#termsList").append("<option onDblClick='selectTermItem()' value='"+value+"' >"+text+"</option>");
    });
    */
    $('#searchBox .btn-yes').prop('disabled', true);
    $('#searchBox .btn-no').prop('disabled', true);
    //--------------------------------------------------------
    $("#searchBox").modal({backdrop:'static', keyboard:false});
    //--------------------------------------------------------
    $("#termsList").on('change', function(){
    //      $("#"+$("#objID").val().trim()).val( $(this).val() ).change();
        $('#searchBox .btn-yes').prop('disabled', false);
        $("#searchItemText").val( $(this).find('option:selected').text() );
    });
  }
  //------------------------------------------------------------
  function selectTermItem(){
    $("#"+$("#objID").val().trim()).val( $('#termsList').val() ).change();
    $("#searchBox").modal('hide');
  }
  //------------------------------------------------------------
  document.onkeydown=function(e){
   /* if(e.keyCode == 13 && e.ctrlKey){
      // 这里实现换行
      //document.getElementById("a").value += "\n";
    }else if(e.keyCode == 13){
      // 避免回车键换行
      e.preventDefault();
      // 下面写你的发送消息的代码
    }*/
    if(e.keyCode == 13){
		// 避免回车键换行
		e.preventDefault();
		// 下面写你的发送消息的代码
		//$('#searchItemText').change()
		$('#goBTN').click();
    }
  }
</script>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="cloneItem" aria-hidden="true" id="searchBox">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h3 style="margin:2px 10px"></h3></div>
            <div class="modal-body">
                <div>
                    <select id="modal_ownerId" name="modal_ownerId" value="" class="where-control" style="display:inline-block;width:80%;height: 2.5rem;margin-bottom: 1rem;">
                    </select>
                </div>
                <div style="margin-bottom:5px;">
                    <textarea class='form-control' id='searchItemText' value="" rows="2"></textarea>
                    <button class="btn btn-info" id="goBTN">go</button>
                    <input type='hidden' id='objID' value="" />
                </div>
                <div>
                  <label>
                    Show Global: <input type="checkbox" data-toggle="toggle" id="showGlobalExtData" checked data-on="Yes" data-off="No">
                  </label>
                </div>
                <div style="margin-top:20px;">
                    <select size="10" class="form-control" id="termsList"></select>
                </div>
            </div>
            <div class="modal-fotter">
                <div style="width:100%;border-top:1px dotted #ccc;padding:12px 20px 16px;" align="right">
                    <button type="button" class="btn btn-success btn-yes" style="width:100%;margin-bottom:5px;" onclick="selectTermItem()" >Select</button>
                    <button type="button" class="btn btn-danger btn-no" style="width:100%;" onClick="cancelSearchBox()">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
