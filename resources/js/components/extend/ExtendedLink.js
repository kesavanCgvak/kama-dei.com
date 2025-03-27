import { DataTable, showError, showSuccess } from '../extend/DataTable'
import Columns from '../extend/Columns'

import { DataTable_modal } from '../extend/DataTable_modal'
//----------------------------------------------------------------
class ExtendedLink extends DataTable {
    //------------------------------------------------------------
    constructor(data){
        super(data);
        this.showGlobal = true;
        this.pageSort   = 'orderid';
        this.entityId=0;

        this.display_val={
            lang          : 'en',
            langCaption   : 'en',
            eng_chatIntro : '',
            eng_voiceIntro: '',
            
            chatIntro :'',
            voiceIntro:'',
            entityName:'',
            subTypechatIntro:'',
            atributes:'',
            atributes_en:''
        };

        this.parentTable=requestTableName?requestTableName:2;
        this.parentId=requestParentId?requestParentId:0;
        this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;

		if(this.parentId!=0){
			var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'Promote', class: 'promote', 'data-onlyowner': 1 },);
			var icon3 = $('<a></a>').attr({ href: '#', 'data-desc': 'Demote', class: 'demote', 'data-onlyowner': 1 },);
			this.actionIcons = this.actionIcons.concat([icon2,icon3]);
        
			$('body').on('click', '.promote', (e) => { this.showorderup(e) });
			$('body').on('click', '.demote', (e) => { this.showorderdown(e) });
		}

        $('body').on('click', '#insertBtn', (e) => { this.showAddDialogHandler() });

        $('body').on('change select', '#parentTable', (e) => { this.select_parentTable() });

        $('body').on('change select', '#parentId', (e) => { this.sampleChatDisplayChange() });
        $('body').on('change select', '#entityId', (e) => { this.sampleChatDisplayChange() });


        $('body').on('change select', '#searc_parentTableName', (e) => { this.searc_parentTableName(e); });
        $('body').on('change', '#chatIntro', (e) => { this.chatIntroChange() });
        $('body').on('change', '#includedExtDataName', (e) => { this.chatIntroChange() });
        $('body').on('change', '#includedExtDataChatIntro', (e) => { this.chatIntroChange() });
        $('body').on('change', '#voiceIntro', (e) => { this.chatIntroChange() });

        var that =this
        setTimeout(function (){
            $('body').off('change select', '#parentName', (e) => { that.select_parentName() });
            $('body').off('change select', '#entityName', (e) => { that.select_entityName() });
            that.searc_show();
        },2000);

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
//              $("#"+this.columns.ownershipColumn+"2").click();
                if(this.editItem.ownership==0){ $("#"+this.columns.ownershipColumn+"2").click(); }
                else{ $("#"+this.columns.ownershipColumn+this.editItem.ownership).click(); }
            }
            $("#languageCode option").remove();
            $.get(apiURL+'/api/dashboard/organization/get/language/'+$('#ownerId').val(), function(res){
                if(res.result==0){
                    for(let i in res.data){
                        if(res.data[i].isActive==1 || res.data[i].code=='en'){
                            let code = res.data[i].code;
                            let caption = res.data[i].name;
                            $("#languageCode").append('<option value="'+code+'">'+caption+'</option>');
                        }
                    }
                    $("#languageCode").val('en').change();
                }
            });
        });
        
        $('body').on('change', '#languageCode', (e)=>{
            
            let url = this.apiURL+'/translation/' +
                $('#thisItemID'  ).val() + "/" +
                $('#ownerId'     ).val() + "/" +
                $('#languageCode').val();

            $.get(url, function(res){
                if(res.result==0){
                    if(res.data!=null){
                        that.display_val.chatIntro  = res.data.chatIntro;
                        that.display_val.voiceIntro = res.data.voiceIntro;
                        
                        $("#chatIntro" ).val(res.data.chatIntro );
                        $("#voiceIntro").val(res.data.voiceIntro);
                    }else{
                        that.display_val.chatIntro  = '';
                        that.display_val.voiceIntro = '';

                        $("#chatIntro" ).val('');
                        $("#voiceIntro").val('');
                    }
                    if(res.data_en!=null){
                        that.display_val.eng_chatIntro  = res.data_en.chatIntro;
                        that.display_val.eng_voiceIntro = res.data_en.voiceIntro;
                    }else{
                        that.display_val.eng_chatIntro  = '';
                        that.display_val.eng_voiceIntro = '';
                    }
                    that.display_val.lang = $('#languageCode').val();
                    that.display_val.langCaption = $('#languageCode option:selected').text();

                    $("#chatIntro" ).change();
                }else{ showError(res.msg); }
            });
        });
        
        $('body').on('click', '#showChatDisplay', (e) => { 
            $("#showVoiceText").removeClass("active");
            $("div.col-sampleVoiceText").hide();
            $("#showChatDisplay").addClass("active");
            $("div.col-sampleChatDisplay").show();
        });
        $('body').on('click', '#showVoiceText', (e) => { 
            $("#showChatDisplay").removeClass("active");
            $("div.col-sampleChatDisplay").hide();
            $("#showVoiceText").addClass("active");
            $("div.col-sampleVoiceText").show();
        });

        this.showGlobalStatus=1;
        $("#showGlobal").prop('checked', true);

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

        this.showGlobalExtData=1;
        $("#showGlobalExtData").bootstrapToggle('disable');
        $('body').on('change', '#showGlobalExtData', (e) =>{
            if($("#modal_ownerId").val()==-1){
//              that.showGlobalExtData=1;
//              $("#showGlobalExtData").bootstrapToggle("on");
                return;
            }
            if($(this).prop('checked')==true){
                that.showGlobalExtData=1;
                $(this).prop('checked', false);
            }else{
                that.showGlobalExtData=0;
                $(this).prop('checked', true);
            }
            $('#searchItemText').change();
        });
        $('body').on('change', '#modal_ownerId', (e) =>{
            if($('#modal_ownerId').val()==-1){
                that.showGlobalExtData=1;
                $("#showGlobalExtData").prop('checked', true).change();
                $("#showGlobalExtData").bootstrapToggle('disable');
            }else{ $("#showGlobalExtData").bootstrapToggle('enable'); }
        });
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    showorderup(e){
        e.preventDefault();
        let temp_itemid   = $(e.currentTarget).data('itemid');
        let this_rows     = JSON.parse(JSON.stringify(this.rows));
		let indx          = -1;
        let temp_upolderl = [];

		for(let i=0; i<this_rows.length; i++){ if(this_rows[i].extendedLinkId==temp_itemid){ indx=i; } }

		if(indx==0){ indx=-1; }
        for(let i=0; i<this_rows.length; i++){
			if(i==indx)
				{ temp_upolderl.push({extendedLinkId:this_rows[i].extendedLinkId,orderid:this_rows[i-1].orderid}) }
			else{
				if(i==(indx-1))
					{ temp_upolderl.push({extendedLinkId:this_rows[i].extendedLinkId,orderid:this_rows[indx].orderid}) }
				else
					{ temp_upolderl.push({extendedLinkId:this_rows[i].extendedLinkId,orderid:this_rows[i].orderid}) }
			}
            
        }
/*
		let newData=[''];
		for(let i=0;i<this_rows.length;i++){
            if(this_rows[i].extendedLinkId==temp_itemid){ newData[0]=this_rows[i]; }
            else{ newData.push(this_rows[i]); }
        }

		let temp_upolderl=[];
        for(let temp_j = 0;temp_j<this.rows.length;temp_j++){
            newData[temp_j].orderid=this.rows[temp_j].orderid;
            temp_upolderl.push({extendedLinkId:newData[temp_j].extendedLinkId,orderid:newData[temp_j].orderid})
        }
*/

		let that = this;
        $.ajax({
            url: this.upolderURL,
            type: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({jsonstr:temp_upolderl}),
			beforeSend: function(){
				$('body')
					.append(
						$("<div>")
							.attr({
								id: "extendedLinkWaitting",
								style: "position:fixed;left:0;top:0;bottom:0;right:0;background:#0000000f;"
							})
							.append("<i class='fa fa-refresh fa-spin fa-5x' style='margin:20% 50%;font-size:150px'></i>")
					);
			},
			complete: function(){ $('body #extendedLinkWaitting').remove(); },
            success: function(res){
                if(res.result == 0){
                    showSuccess('Sort success.');
                    $(that.table).bootstrapTable('refresh');
                }else{ showError(res.msg); }
            },
            error: function(e){ showError('Server error'); }
        });

    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    showorderdown(e){
/*
        e.preventDefault();
        let temp_itemid=$(e.currentTarget).data('itemid');
        let newData=[];
        newData[this.rows.length-1]='';
        var this_rows = JSON.parse(JSON.stringify(this.rows));
        for(let i=0;i<this_rows.length;i++){
            if(this_rows[i].extendedLinkId==temp_itemid){ newData[this_rows.length-1]=this_rows[i]; }
            else{ 
                for(let t=0;t<newData.length;t++){
                    if(!newData[t]){ newData[t]=this_rows[i]; break; }
                }
            }
        }

        let temp_upolderl=[];
        for(let temp_j = 0;temp_j<this.rows.length;temp_j++){
            newData[temp_j].orderid=this.rows[temp_j].orderid;
            temp_upolderl.push({extendedLinkId:newData[temp_j].extendedLinkId,orderid:newData[temp_j].orderid})
        }
        let that = this;
        $.ajax({
            url: this.upolderURL,
            type: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({jsonstr:temp_upolderl}),
            success: function(res){
                if(res.result == 0){
                    showSuccess('Sort success.');
                    $(that.table).bootstrapTable('refresh');
                }else{ showError(res.msg); }
            },
            error: function(e){ showError('Server error'); }
        });
*/
        e.preventDefault();
        let temp_itemid   = $(e.currentTarget).data('itemid');
        let this_rows     = JSON.parse(JSON.stringify(this.rows));
		let indx          = -1;
        let temp_upolderl = [];

		for(let i=0; i<this_rows.length; i++){ if(this_rows[i].extendedLinkId==temp_itemid){ indx=i; } }
		if(indx==this_rows.length-1){ indx=-1*this_rows.length; }
        for(let i=0; i<this_rows.length; i++){
			if(i==indx)
				{ temp_upolderl.push({extendedLinkId:this_rows[i].extendedLinkId,orderid:this_rows[i+1].orderid}) }
			else{
				if(i==(indx+1))
					{ temp_upolderl.push({extendedLinkId:this_rows[i].extendedLinkId,orderid:this_rows[indx].orderid}) }
				else
					{ temp_upolderl.push({extendedLinkId:this_rows[i].extendedLinkId,orderid:this_rows[i].orderid}) }
			}
            
        }

		let that = this;
        $.ajax({
            url: this.upolderURL,
            type: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({jsonstr:temp_upolderl}),
			beforeSend: function(){
				$('body')
					.append(
						$("<div>")
							.attr({
								id: "extendedLinkWaitting",
								style: "position:fixed;left:0;top:0;bottom:0;right:0;background:#0000000f;"
							})
							.append("<i class='fa fa-refresh fa-spin fa-5x' style='margin:20% 50%;font-size:150px'></i>")
					);
			},
			complete: function(){ $('body #extendedLinkWaitting').remove(); },
            success: function(res){
                if(res.result == 0){
                    showSuccess('Sort success.');
                    $(that.table).bootstrapTable('refresh');
                }else{ showError(res.msg); }
            },
            error: function(e){ showError('Server error'); }
        });
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    getOrganizations(){
        $.get(this.organizationURL, (res) => {
            this.organizations = this.createSelectOptions(res.data, 'organizationId', 'organizationShortName');
            this.ownerId = this.orgID;
            $("#orgID, #ownerId,#modal_ownerId").append(this.organizations);
            $("#searc_ownerId").append(this.organizations);

            $("#modal_ownerId").prepend("<option value='-1'>Owner All</option>");
            this.getStorageTypes();
        });
    }
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
        return this.apiURL + '/page/' +
            this.entityId + '/' + 
            this.parentTable + '/' + 
            this.parentId + '/' +
            this.orgID + '/' +
            this.pageSort + '/' +
            this.pageOrder + '/' +
            this.pageSize +'/' +
            this.pageNumber + '/' + 
            'showglobal/'+this.showGlobalStatus;
    }
    get searchURL(){
        return this.apiURL + '/page/' +
            this.entityId + '/' +
            this.parentTable + '/' +
            this.parentId + '/' +
            this.orgID + '/' +
            this.pageSort + '/' +
            this.pageOrder + '/' +
            this.pageSize +'/' +
            this.pageNumber + '/' +
            this.search + '/' +
            'showglobal/'+this.showGlobalStatus;
    }
    get addURL   () { return this.apiURL+'/new/' +  this.orgID+'/'; }
    get editURL  () { return this.apiURL+'/edit/' + this.orgID+'/' + this.editItem[this.columns.primaryColumn]; }
    get deleteURL() { return this.apiURL + "/delete/" + this.orgID+'/' + this.deleteId; }
    //------------------------------------------------------------

    //------------------------------------------------------------
    searchTermByName(objID, val,thisorgID){
		val = val.trim();
		let tmpVal = $("#searchItemText").val();
		if(tmpVal.substr(-1)==' '){ val+=" "; }

		if(!thisorgID){thisorgID=this.orgID}
        if(val==''){ val=='n' }
        var obj = $("select#"+objID);
        $("select#"+objID+" option").remove();
        $("#termsList option").remove();
        var temp_url=apiURL+'/api/dashboard/term/page/'+thisorgID+'/termName/asc/'+termPerPage+'/1';
        if(objID=='parentId'){
            var select_parentTable= $("select#parentTable option:selected").text();
            if(select_parentTable=='Term'){
                temp_url=apiURL+'/api/dashboard/term/page/'+thisorgID+'/termName/asc/'+termPerPage+'/1';
                if(val!=''&&val!=='n'){
                    temp_url=apiURL+'/api/dashboard/term/'+thisorgID+'/termName/asc/'+termPerPage+'/1/termName/'+val;
                }
                temp_url+="/ownerId/"+thisorgID+"/showglobal/"+(($("#showGlobalExtData").prop('checked') || thisorgID==-1) ?'1' :"0");
                $("#goBTN").html("<i class='fa fa-spin fa-hourglass-half'></i>").prop('disabled', true);
                $('#searchBox .btn-no').prop('disabled', true);
                $.get(temp_url, (dataIn) => {
                    $(obj).html('');
                    $(obj).append("<option data-prev='0' data-next='0' value='0' >Parents All</option>");
                    $("#termsList option").remove();

					if(dataIn.total>termPerPage && (val!=''&&val!='n')){
						var tmp = ""+
							"<option "+
								"data-prev='0' "+
								"data-next='0' "+
								"disabled "+
								"value='0' >"+
									"Too many results, Please refine the search."+
							"</option>";
						$(obj).append(tmp);
						$("#termsList").append(tmp);
					}else{
						if(dataIn.total==0){
							var tmp = ""+
								"<option "+
									"data-prev='0' "+
									"data-next='0' "+
									"disabled "+
									"value='0' >"+
										"No match found."+
								"</option>";
							$(obj).append(tmp);
							$("#termsList").append(tmp);
						}else{
							for(var i=0; i<dataIn.data.length; i++){
								var tmp = ""+
									"<option data-prev='0' data-next='0' value='"+dataIn.data[i].termId+"' >"+
										dataIn.data[i].termName+
									"</option>";
								$(obj).append(tmp);
								$("#termsList").append(tmp);
							}
							$("#termsList option").each(function(){ $(this).attr('onDblClick' ,'selectTermItem()'); });
						}
					}
                    $("#goBTN").text("go").prop('disabled', false);
                    $('#searchBox .btn-no').prop('disabled',false);
                });
            }else if(select_parentTable=='Knowledge Link'){
                temp_url=apiURL+'/api/dashboard/relation_link/page/'+thisorgID+'/-1/relationLinkId/asc/'+termPerPage+'/1';
                if(val!=''&&val!=='n'){
                    temp_url=apiURL+'/api/dashboard/relation_link/'+thisorgID+'/-1/relationLinkId/asc/'+termPerPage+'/1/allFields/'+val;
                }
                temp_url+="/ownerId/"+thisorgID+"/showglobal/"+(($("#showGlobalExtData").prop('checked') || thisorgID==-1) ?'1' :"0");
                $("#goBTN").html("<i class='fa fa-spin fa-hourglass-half'></i>").prop('disabled', true);
                $('#searchBox .btn-no').prop('disabled', true);
                $.get(temp_url, (dataIn) => {
                    $(obj).html('');
                    $(obj).append("<option data-prev='0' data-next='0' value='0' >Parents All</option>");
                    $("#termsList option").remove();

					if(dataIn.total>termPerPage && (val!=''&&val!='n')){
						var tmp = ""+
							"<option "+
								"data-prev='0' "+
								"data-next='0' "+
								"disabled "+
								"value='0' >"+
									"Too many results, Please refine the search."+
							"</option>";
						$(obj).append(tmp);
						$("#termsList").append(tmp);
					}else{
						if(dataIn.total==0){
							var tmp = ""+
								"<option "+
									"data-prev='0' "+
									"data-next='0' "+
									"disabled "+
									"value='0' >"+
										"No match found."+
								"</option>";
							$(obj).append(tmp);
							$("#termsList").append(tmp);
						}else{
							for(var i=0; i<dataIn.data.length; i++){
								var tmp = ""+
									"<option data-prev='0' data-next='0' value='"+dataIn.data[i].relationLinkId+"' >"+
										dataIn.data[i].leftKRName+'-'+dataIn.data[i].termName+'-'+dataIn.data[i].rightKRName+
									"</option>";
								$(obj).append(tmp);
								$("#termsList").append(tmp);
							}
							$("#termsList option").each(function(){ $(this).attr('onDblClick' ,'selectTermItem()'); });
						}
					}
                    $("#goBTN").text("go").prop('disabled', false);
                    $('#searchBox .btn-no').prop('disabled',false);
                });
            }else if(select_parentTable=='Knowledge Record'){
                temp_url=apiURL+'/api/dashboard/relation/page/'+thisorgID+'/knowledgeRecordName/asc/'+termPerPage+'/1';
                if(val!=''&&val!='n'){
                    temp_url=apiURL+'/api/dashboard/relation/'+thisorgID+'/knowledgeRecordName/asc/'+termPerPage+'/1/allFields/'+val;
                }
                temp_url+="/ownerId/"+thisorgID+"/showglobal/"+(($("#showGlobalExtData").prop('checked') || thisorgID==-1) ?'1' :"0");
                $("#goBTN").html("<i class='fa fa-spin fa-hourglass-half'></i>").prop('disabled', true);
                $('#searchBox .btn-no').prop('disabled', true);
                $.get(temp_url, (dataIn) => {
                    $(obj).html('');
                    $(obj).append("<option data-prev='0' data-next='0' value='0' >Parents All</option>");
                    $("#termsList option").remove();

					if(dataIn.total>termPerPage && (val!=''&&val!='n')){
						var tmp = ""+
							"<option "+
								"data-prev='0' "+
								"data-next='0' "+
								"disabled "+
								"value='0' >"+
									"Too many results, Please refine the search."+
							"</option>";
						$(obj).append(tmp);
						$("#termsList").append(tmp);
					}else{
						if(dataIn.total==0){
							var tmp = ""+
								"<option "+
									"data-prev='0' "+
									"data-next='0' "+
									"disabled "+
									"value='0' >"+
										"No match found."+
								"</option>";
							$(obj).append(tmp);
							$("#termsList").append(tmp);
						}else{
							for(var i=0; i<dataIn.data.length; i++){
								var tmp = ""+
									"<option data-prev='0' data-next='0' value='"+dataIn.data[i].relationId+"' >"+
										dataIn.data[i].knowledgeRecordName+
									"</option>";
								$(obj).append(tmp);
								$("#termsList").append(tmp);
							}
							$("#termsList option").each(function(){ $(this).attr('onDblClick' ,'selectTermItem()'); });
						}
					}
                    $("#goBTN").text("go").prop('disabled', false);
                    $('#searchBox .btn-no').prop('disabled',false);
                });
            }
        }else if(objID=='entityId'||objID=='entityName'){
            temp_url=apiURL+'/api/extend/extended_entity/page/0/'+thisorgID+'/extendedEntityName/asc/'+termPerPage+'/1';
            if(val!=''&&val!='n'){
                temp_url=apiURL+'/api/extend/extended_entity/0/'+thisorgID+'/extendedEntityName/asc/'+termPerPage+
                    '/1/extendedEntityName/'+val;
            }
            temp_url+="/showglobal/"+(($("#showGlobalExtData").prop('checked') || thisorgID==-1) ?'1' :"0");
            $("#goBTN").html("<i class='fa fa-spin fa-hourglass-half'></i>").prop('disabled', true);
            $('#searchBox .btn-no').prop('disabled', true);
            $.get(temp_url, (dataIn) => {
                $(obj).html('');
                $(obj).append("<option data-prev='0' data-next='0' value='0' >Ext. Data All</option>");
                $("#termsList option").remove();
				
				if(dataIn.total>termPerPage && (val!=''&&val!='n')){
                    var tmp = ""+
                        "<option "+
                            "data-prev='0' "+
                            "data-next='0' "+
                            "subtypechatintro='' "+
							"disabled "+
                            "value='0' >"+
                                "Too many results, Please refine the search."+
                        "</option>";
                    $(obj).append(tmp);
                    $("#termsList").append(tmp);
				}else{
					if(dataIn.total==0){
						var tmp = ""+
							"<option "+
								"data-prev='0' "+
								"data-next='0' "+
								"disabled "+
								"value='0' >"+
									"No match found."+
							"</option>";
						$(obj).append(tmp);
						$("#termsList").append(tmp);
					}else{
						for(var i=0; i<dataIn.data.length; i++){
							let chatIntro = "";
							try{
								chatIntro = dataIn.data[i].extendedsubtype.chatIntro;
							}catch(e){}
							var tmp = ""+
								"<option "+
									"data-prev='0' "+
									"data-next='0' "+
									"subtypechatintro='"+chatIntro+"' "+
									"value='"+dataIn.data[i].extendedEntityId+"' >"+
										dataIn.data[i].extendedEntityName+
								"</option>";
							$(obj).append(tmp);
							$("#termsList").append(tmp);
						}
						$("#termsList option").each(function(){ $(this).attr('onDblClick' ,'selectTermItem()'); });
					}
				}
                
                $("#goBTN").text("go").prop('disabled', false);
                $('#searchBox .btn-no').prop('disabled',false);
            });
        }else  if(objID=='parentName'){
            var select_parentTable= $("select#searc_parentTableName option:selected").text();
            if(select_parentTable=='Term'){
                temp_url=apiURL+'/api/dashboard/term/page/'+thisorgID+'/termName/asc/'+termPerPage+'/1';
                if(val!=''&&val!=='n'){
                    temp_url=apiURL+'/api/dashboard/term/'+thisorgID+'/termName/asc/'+termPerPage+'/1/termName/'+val;
                }
                temp_url+="/ownerId/"+thisorgID+"/showglobal/"+(($("#showGlobalExtData").prop('checked') || thisorgID==-1) ?'1' :"0");
                $(obj).html('');
                $(obj).append("<option data-prev='0' data-next='0' value='0' >Parents All</option>");
                $("#goBTN").html("<i class='fa fa-spin fa-hourglass-half'></i>").prop('disabled', true);
                $('#searchBox .btn-no').prop('disabled', true);
                $.get(temp_url, (dataIn) => {
                    $("#termsList option").remove();

					if(dataIn.total>termPerPage && (val!=''&&val!='n')){
						var tmp = ""+
							"<option "+
								"data-prev='0' "+
								"data-next='0' "+
								"disabled "+
								"value='0' >"+
									"Too many results, Please refine the search."+
							"</option>";
						$(obj).append(tmp);
						$("#termsList").append(tmp);
					}else{
						if(dataIn.total==0){
							var tmp = ""+
								"<option "+
									"data-prev='0' "+
									"data-next='0' "+
									"disabled "+
									"value='0' >"+
										"No match found."+
								"</option>";
							$(obj).append(tmp);
							$("#termsList").append(tmp);
						}else{
							for(var i=0; i<dataIn.data.length; i++){
								var tmp = ""+
									"<option data-prev='0' data-next='0' value='"+dataIn.data[i].termId+"' >"+
										dataIn.data[i].termName+
									"</option>";
								$(obj).append(tmp);
								$("#termsList").append(tmp);
							}
							$("#termsList option").each(function(){ $(this).attr('onDblClick' ,'selectTermItem()'); });
						}
					}
                    $("#goBTN").text("go").prop('disabled', false);
                    $('#searchBox .btn-no').prop('disabled',false);
                });
            }else if(select_parentTable=='Knowledge Link'){
                temp_url=apiURL+'/api/dashboard/relation_link/page/'+thisorgID+'/-1/relationLinkId/asc/'+termPerPage+'/1';
                if(val!=''&&val!=='n'){
                    temp_url=apiURL+'/api/dashboard/relation_link/'+thisorgID+'/-1/relationLinkId/asc/'+termPerPage+
                        '/1/allFields/'+val;
                }
                temp_url+="/ownerId/"+thisorgID+"/showglobal/"+(($("#showGlobalExtData").prop('checked') || thisorgID==-1) ?'1' :"0");
                $("#goBTN").html("<i class='fa fa-spin fa-hourglass-half'></i>").prop('disabled', true);
                $('#searchBox .btn-no').prop('disabled', true);
                $.get(temp_url, (dataIn) => {
                    $(obj).html('');
                    $(obj).append("<option data-prev='0' data-next='0' value='0' >Parents All</option>");
                    $("#termsList option").remove();

					if(dataIn.total>termPerPage && (val!=''&&val!='n')){
						var tmp = ""+
							"<option "+
								"data-prev='0' "+
								"data-next='0' "+
								"disabled "+
								"value='0' >"+
									"Too many results, Please refine the search."+
							"</option>";
						$(obj).append(tmp);
						$("#termsList").append(tmp);
					}else{
						if(dataIn.total==0){
							var tmp = ""+
								"<option "+
									"data-prev='0' "+
									"data-next='0' "+
									"disabled "+
									"value='0' >"+
										"No match found."+
								"</option>";
							$(obj).append(tmp);
							$("#termsList").append(tmp);
						}else{
							for(var i=0; i<dataIn.data.length; i++){
								var tmp = ""+
									"<option data-prev='0' data-next='0' value='"+dataIn.data[i].relationLinkId+"' >"+
										dataIn.data[i].leftKRName+'-'+dataIn.data[i].termName+'-'+dataIn.data[i].rightKRName+
									"</option>";

								$(obj).append(tmp);
								$("#termsList").append(tmp);
							}
							$("#termsList option").each(function(){ $(this).attr('onDblClick' ,'selectTermItem()'); });
						}
					}
                    $("#goBTN").text("go").prop('disabled', false);
                    $('#searchBox .btn-no').prop('disabled',false);
                });
            }else if(select_parentTable=='Knowledge Record'){
                temp_url=apiURL+'/api/dashboard/relation/page/'+thisorgID+'/knowledgeRecordName/asc/'+termPerPage+'/1';
                if(val!=''&&val!='n'){
                    temp_url=apiURL+'/api/dashboard/relation/'+thisorgID+'/knowledgeRecordName/asc/'+termPerPage+
                        '/1/allFields/'+val;
                }
                temp_url+="/ownerId/"+thisorgID+"/showglobal/"+(($("#showGlobalExtData").prop('checked') || thisorgID==-1) ?'1' :"0");
                $("#goBTN").html("<i class='fa fa-spin fa-hourglass-half'></i>").prop('disabled', true);
                $('#searchBox .btn-no').prop('disabled', true);
                $.get(temp_url, (dataIn) => {
                    $(obj).html('');
                    $(obj).append("<option data-prev='0' data-next='0' value='0' >Parents All</option>");
                    $("#termsList option").remove();

					if(dataIn.total>termPerPage && (val!=''&&val!='n')){
						var tmp = ""+
							"<option "+
								"data-prev='0' "+
								"data-next='0' "+
								"disabled "+
								"value='0' >"+
									"Too many results, Please refine the search."+
							"</option>";
						$(obj).append(tmp);
						$("#termsList").append(tmp);
					}else{
						if(dataIn.total==0){
							var tmp = ""+
								"<option "+
									"data-prev='0' "+
									"data-next='0' "+
									"disabled "+
									"value='0' >"+
										"No match found."+
								"</option>";
							$(obj).append(tmp);
							$("#termsList").append(tmp);
						}else{
							for(var i=0; i<dataIn.data.length; i++){
								var tmp = ""+
									"<option data-prev='0' data-next='0' value='"+dataIn.data[i].relationId+"' >"+
										dataIn.data[i].knowledgeRecordName+
									"</option>";
								$(obj).append(tmp);
								$("#termsList").append(tmp);
							}
							$("#termsList option").each(function(){ $(this).attr('onDblClick' ,'selectTermItem()'); });
						}
					}
                    $("#goBTN").text("go").prop('disabled', false);
                    $('#searchBox .btn-no').prop('disabled',false);
                });
            }
        }
        
        $("#termsList").focus();
        $(obj).focus();
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    searchWhereItem(col,lable){
        var value = '';
        var tmpitems=[]
        if(this.orgID==0){ tmpitems=["<option value='NULL'>"+lable+"  All</option>"]; }
        if(col=='parentTableName'){
            if(parseInt(this.parentTable)==0){
                tmpitems=[
                    "<option value='0'>Term</option>",
                    "<option value='2'>Knowledge Record</option>",
                    "<option value='1'>Knowledge Link</option>"
                ];
            }else if(parseInt(this.parentTable)==1){
                tmpitems=[
                    "<option value='1'>Knowledge Link</option>",
                    "<option value='0'>Term</option>",
                    "<option value='2'>Knowledge Record</option>"
                ];
            }else if(parseInt(this.parentTable)==2){
                tmpitems=[
                    "<option value='2'>Knowledge Record</option>",
                    "<option value='1'>Knowledge Link</option>",
                    "<option value='0'>Term</option>"
                ];
            }else{
                tmpitems=[
                    "<option value='0'>Term</option>",
                    "<option value='2'>Knowledge Record</option>",
                    "<option value='1'>Knowledge Link</option>"
                ];
            }
            var row = $("<div>").attr({ class: "wol-"+lable+" where-group" })
                        .append("<label>"+lable+"</label>")
                        .append($("<div>")
                            .append($("<select>")
                                .attr({
                                    id: 'searc_'+col, 
                                    name: 'searc_'+col, 
                                    value: value, 
                                    class: "where-control",
                                    style:"width:100%;display:inline-block"
                                })
                                .append(tmpitems)
                        ));
            return row;
        }else if(col=='parentName'){
            var row = $("<div>").attr({ class: "wol-"+lable+" where-group" })
                        .append("<label>"+lable+"</label>")
                        .append($("<div>")
                            .append($("<select>").attr({ id: col, name: col, class: 'form-control', style: 'display:none' }) )
                            .append( $("<input>")
                                        .attr({ 
                                            id: col+'TEMP', 
                                            class: 'form-control', 
                                            disabled: true,
                                            style: "width:100% !important;display:inline-block;padding-right:24px;"
                                        })
                            )
                            .append( $("<i>")
                                        .attr({ 
                                            id: col+'-btn-search', 
                                            class: 'fa fa-search', 
                                            style: 'margin-left:-20px;cursor:pointer;', 
                                            onclick: "showSearchBox('"+col+"', '"+lable+"')"
                                        })
                            )
                        );
            return row;
        }
        else if(col=='entityName'){
            var row = $("<div>").attr({ class: "wol-"+lable+" where-group" })
                        .append("<label>"+lable+"</label>")
                        .append($("<div>")
                            .append($("<select>").attr({ id: col, name: col, class: 'form-control', style: 'display:none' }))
                            .append( $("<input>")
                                        .attr({
                                            id: col+'TEMP',
                                            class: 'form-control',
                                            disabled: true,
                                            style: "width:100% !important;display:inline-block;padding-right:24px;"
                                        })
                            )
                            .append( $("<i>")
                                        .attr({ 
                                            id: col+'-btn-search', 
                                            class: 'fa fa-search', 
                                            style: 'margin-left:-20px;cursor:pointer;', 
                                            onclick: "showSearchBox('"+col+"', '"+lable+"')"
                                        })
                            )
                        );
            return row;
        }else{
            var row = $("<div>").attr({ class: "wol-"+lable+" where-group" })
                        .append("<label>"+lable+"</label>")
                        .append($("<div>")
                                    .append($("<select>")
                                            .attr({
                                                id: 'searc_'+col,
                                                name: 'searc_'+col,
                                                value: value,
                                                class: "where-control",
                                                style: "width:100% !important;display:inline-block;"
                                            })
                                            .append(tmpitems)
                        ));
            return row;
        }
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    searc_show(){
        switch (parseInt(this.parentTable)) {
            case 0:
                this.getTerms(parseInt(this.parentId), $("select#parentName" ), 'n','Parents All');
                break;
            case 1:
                this.getKnowledgeLinks(parseInt(this.parentId) , $("select#parentName" ), 'n','Parents All');
                break;
            case 2:
                this.getKnowledgeRecord(parseInt(this.parentId), $("select#parentName" ), 'n','Parents All');
                break;
        }
        this.getEntity(parseInt(this.entityId), $("select#entityName"), 'n','Ext. Data All');
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    chatIntroChange(){
        this.display_val.chatIntro  = $("#chatIntro").val();
        this.display_val.vchatIntro = $("#voiceIntro").val();
//      this.sampleChatDisplay_val();
        this.sampleChatDisplayChange();
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    sampleChatDisplayChange(){
        if(typeof $("select#entityId option:selected").val()=='undefined'){ return; }
        var tempVar = ""+
            $("select#parentTable option:selected").text()+' '+
            $("select#parentId option:selected").text()+' '+
            $("select#entityId option:selected").text();
        this.display_val.entityName=$("select#entityId option:selected").text();
        this.display_val.subTypechatIntro=$("select#entityId option:selected").attr('subtypechatintro');
        let lang = $("#languageCode").val();
        lang = ((lang==null) ?'en' :lang);
        var temp_url=apiURL+'/api/extend/extendeddataview/all/'+$("select#entityId option:selected").val()+"/"+lang;

        $.get(temp_url, (dataIn) => {
            this.display_val.atributes='';
            for(var i=0; i<dataIn.data.length; i++){
                if(this.display_val.atributes!=''){ 
                    this.display_val.atributes=this.display_val.atributes+' '+dataIn.data[i].valueString;
                }else{ this.display_val.atributes=dataIn.data[i].valueString; }
            }
            
            if(lang=='en'){ this.display_val.atributes_en = this.display_val.atributes; }
            
            this.sampleChatDisplay_val();
        });
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    showAddDialogHandler(){
        this.display_val={
            lang          : 'en',
            eng_chatIntro : '',
            eng_voiceIntro: '',
            
            chatIntro :'',
            voiceIntro:'',
            entityName:'',
            subTypechatIntro:'',
            atributes:[]
        };
        super.showAddDialogHandler();
        $("#parentTable").val(this.parentTable);
        
        if(parseInt(this.parentTable)==0){ this.getTerms(parseInt(this.parentId), $("select#parentId" ), 'n', 'Search ...'); }
        else if(parseInt(this.parentTable)==1){ this.getKnowledgeLinks(parseInt(this.parentId), $("select#parentId" ), 'n', 'Search ...'); }
        else if(parseInt(this.parentTable)==2){ this.getKnowledgeRecord(parseInt(this.parentId), $("select#parentId" ), 'n', 'Search ...'); }
        else{ this.getTerms(this.parentId, $("select#parentId" ), 'n', 'Search ...'); }

        this.getEntity(0, $("select#entityId"), 'n', 'Search ...');
        $("#insertItem").prop('disabled',false);
        $("#saveItem"  ).prop('disabled',false);

        $("input#parentIdTEMP").val($("select#parentId option:selected").text());
        $("input#entityIdTEMP").val($("select#entityId option:selected").text());
/*
        if(this.ownerId==0){
            $("#"+this.columns.ownershipColumn+"0").click();
            $("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
            $("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
        }else{
            $("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
            $("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
        }
*/
        $('#showChatDisplay').click();
        if($("#searc_ownerId").val()==null || $("#searc_ownerId").val()=='NULL'){ $("#ownerId").val(0); }
        else{ $("#ownerId").val($("#searc_ownerId").val()); }
        
        $("#languageCode").prop('disabled', true);
        $("#ownerId"     ).change();
        $("#thisItemID"  ).val(0);
        
        $(".action-form").scrollTop(0);
    }
    //--------------------------------------------------------------

    //--------------------------------------------------------------
    editConfirmHandler(e){
        if(this.baseItem!=null){ for(let i in this.editItem){ this.baseItem[i]=null; } }
        super.editConfirmHandler(e);
    }
    //--------------------------------------------------------------
    showEditDialogHandler(e){
        super.showEditDialogHandler(e);
        
        if(this.baseItem==null){ this.baseItem={}; for(let i in this.editItem){ this.baseItem[i]=this.editItem[i]; } }

        if(this.baseItem.extendedLinkId!=this.editItem.extendedLinkId)
            { for(let i in this.editItem){ this.baseItem[i]=this.editItem[i]; } }
        this.baseItem.ownerId = (this.baseItem.ownerId==null) ?0 :this.baseItem.ownerId;
        
        $("#parentTable").val(this.baseItem.parentTable);
        switch (parseInt(this.baseItem.parentTable)) {
            case 0:
                this.getTerms(parseInt(this.baseItem.parentId) , $("select#parentId" ), 'n');
                break;
            case 1:
                this.getKnowledgeLinks(parseInt(this.baseItem.parentId) , $("select#parentId" ), 'n');
                break;
            case 2:
                this.getKnowledgeRecord(parseInt(this.baseItem.parentId) , $("select#parentId" ), 'n');
                break;
        }
        this.getEntity(parseInt(this.baseItem.entityId), $("select#entityId"), 'n');

        if(this.baseItem.ownerId==0 || this.baseItem.ownerId==null){
            $("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
            $("#"+this.columns.ownershipColumn+"0").click();
            $("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
            $("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
            
        }else{
            $("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
            $("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
            $("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
            $("#"+this.columns.ownershipColumn+this.baseItem.ownership).click();
        }

        $('#showChatDisplay').click();
        $("#languageCode"   ).prop('disabled', false);
        $("#ownerId"        ).change();
        $("#thisItemID"     ).val(this.baseItem.extendedLinkId);
        $("#memo"           ).val(this.baseItem.memo);

        if(orgID!=0){
            $("#languageCode option").remove();
            $.get(apiURL+'/api/dashboard/organization/get/language/'+orgID, function(res){
                if(res.result==0){
                    for(let i in res.data){
                        if(res.data[i].isActive==1 || res.data[i].code=='en'){
                            let code = res.data[i].code;
                            let caption = res.data[i].name;
                            $("#languageCode").append('<option value="'+code+'">'+caption+'</option>');
                        }
                    }
                    $("#languageCode").val('en').change();
                }
            });
        }

        $(".action-form").scrollTop(0);
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    searc_parentTableName(e){
        var target = e.currentTarget;
        var value = target.value;
        if(value=='NULL'){ value=0; }
        this.parentTable=value;
        this.parentId=0;
        this.entityId=0;
        this.searc_show();
    };
    //------------------------------------------------------------

    //------------------------------------------------------------
    getTerms(id, obj, direction,isall,thisorgID){
        if(!thisorgID){ thisorgID=this.orgID; }
        $(obj).find("option").remove();
        if(isall){ $(obj).append("<option data-prev='0' data-next='0' value='0' >"+isall+"</option>"); }

        var temp_url=apiURL+'/api/dashboard/term/page/'+thisorgID+'/termName/asc/'+termPerPage+'/1';
        if(direction!=''&&direction!=='n'){
            temp_url=apiURL+'/api/dashboard/term/'+thisorgID+'/termName/asc/'+termPerPage+'/1/termName/'+direction;
        }
        if(id && id!=0){ temp_url=apiURL+'/api/dashboard/term/get/'+thisorgID+'/'+id; }
        $.get(temp_url, (dataIn) => {
            for(var i=0; i<dataIn.data.length; i++){
                var tmp = "<option data-prev='0' data-next='0' value='" + dataIn.data[i].termId+"' >" +
                    dataIn.data[i].termName +
                    "</option>";
                if(direction=='n'){ $(obj).append(tmp); }
                else{ $(obj).prepend(tmp); }
            }
            if( id!=0||isall ){ $(obj).val(id).change(); }
            else{ $(obj).change(); }
        });
        if(isall){
            $('body').off('change select', '#parentName', (e) => { this.select_parentName() });
            $('body').on('change select', '#parentName', (e) => { this.select_parentName() });
        }
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    getKnowledgeLinks(id, obj, direction,isall,thisorgID){
        if(!thisorgID){ thisorgID=this.orgID; }
        $(obj).find("option").remove();
        var temp_url=apiURL+'/api/dashboard/relation_link/page/'+thisorgID+'/-1/relationLinkId/asc/'+termPerPage+'/1';
        if(direction!=''&&direction!=='n'){
            temp_url=apiURL+'/api/dashboard/relation_link/'+thisorgID+'/-1/relationLinkId/asc/'+termPerPage+'/1/allFields/'+direction;
        }
        if(id){ temp_url=apiURL+'/api/dashboard/relation_link/get/0/'+id; }
        if(isall){ $(obj).append("<option data-prev='0' data-next='0' value='0' >"+isall+"</option>"); }
        $.get(temp_url, (dataIn) => {
            for(var i=0; i<dataIn.data.length; i++){
                var tmp = ""+
                    "<option data-prev='0' data-next='0' value='"+dataIn.data[i].relationLinkId+"' >"+
                        dataIn.data[i].leftKRName+'-'+dataIn.data[i].termName+'-'+dataIn.data[i].rightKRName+
                    "</option>";
                if(direction=='n'){ $(obj).append(tmp); }
                else{ $(obj).prepend(tmp); }
            }
            if( id!=0||isall ){ $(obj).val(id).change(); }
            else{ $(obj).change(); }
            if(isall){
                $('body').off('change select', '#parentName', (e) => { this.select_parentName() });
                $('body').on('change select', '#parentName', (e) => { this.select_parentName() });
            }
        });
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    getKnowledgeRecord(id, obj, direction,isall,thisorgID){
        if(!thisorgID){ thisorgID=this.orgID; }
        var temp_url=apiURL+'/api/dashboard/relation/page/'+thisorgID+'/knowledgeRecordName/asc/'+termPerPage+'/1/';
        if(direction!=''&&direction!='n'){
            temp_url=apiURL+'/api/dashboard/relation/'+thisorgID+'/knowledgeRecordName/asc/'+termPerPage+'/1/allFields/'+direction;
        }
        if(id){ temp_url=apiURL+'/api/dashboard/relation/get/0/'+id; }
        $(obj).find("option").remove();
        if(isall){ $(obj).append("<option data-prev='0' data-next='0' value='0' >"+isall+"</option>"); }
        $.get(temp_url, (dataIn) => {
//          $(obj).find("option").remove();
            for(var i=0; i<dataIn.data.length; i++){
                var tmp = ""+
                    "<option data-prev='0' data-next='0' value='"+dataIn.data[i].relationId+"' >"+
                        dataIn.data[i].knowledgeRecordName+
                    "</option>";
                if(direction=='n'){ $(obj).append(tmp); }
                else{ $(obj).prepend(tmp); }
            }
            if( id!=0||isall ){ $(obj).val(id).change(); }
            else{ $(obj).change(); }
            if(isall){
                $('body').off('change select', '#parentName', (e) => { this.select_parentName() });
                $('body').on('change select', '#parentName', (e) => { this.select_parentName() });
            }
        });
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
    getEntity(id, obj, direction,isall,thisorgID){
        if(!thisorgID){ thisorgID=this.orgID; }
        $(obj).find("option").remove();
        var temp_url=apiURL+'/api/extend/extended_entity/page/0/'+thisorgID+'/extendedEntityName/asc/'+termPerPage+'/1/';
        if(direction!=''&&direction!='n'){
            emp_url=apiURL+'/api/extend/extended_entity/0/'+
                thisorgID+'/extendedEntityName/asc/'+
                termPerPage+'/1/extendedEntityName/'+
                direction;
        }
        if(id){ temp_url=apiURL+'/api/extend/extended_entity/get/0/0/'+id; }
        if(isall){ $(obj).append("<option data-prev='0' data-next='0' value='0' >"+isall+"</option>"); }
        $.get(temp_url, (dataIn) =>{
            $(obj).find("option").remove();
            if(isall){ $(obj).append("<option data-prev='0' data-next='0' value='0' >"+isall+"</option>"); }
            for(let i=0; i<dataIn.data.length; i++){
                let tmpChatIntro = "";
                if(dataIn.data[i].extendedsubtype!=null){ tmpChatIntro=dataIn.data[i].extendedsubtype.chatIntro; }
                var tmp = "<option data-prev='0' data-next='0' "+
                            "subtypechatintro='"+tmpChatIntro+"' "+
                            "value='"+dataIn.data[i].extendedEntityId+"' "+
                            ">"+
                            dataIn.data[i].extendedEntityName+
                            "</option>";
                if(direction=='n'){ $(obj).append(tmp); }
                else{ $(obj).prepend(tmp); }
            }
            if( id!=0||isall ){ $(obj).val(id).change(); }
            else{ $(obj).change(); }
            if(isall){
                $('body').off('change select', '#entityName', (e) => { this.select_entityName() });
                $('body').on('change select', '#entityName', (e) => { this.select_entityName() });
            }
        });
    }
    //------------------------------------------------------------

    //------------------------------------------------------------
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
    //------------------------------------------------------------

    //------------------------------------------------------------
    defaultFormInput(col, label) {
        if(col=='sampleChatDisplay'){
            var row = $("<div>")
                        .attr({ class: 'col-' + col + ' form-group' ,style:'width: 100%;'})
                        .append("<label>"+label+"</label>")
                        .append(
                            $("<div>")
                                .append(
                                    $("<textarea>")
                                        .attr({
                                            name: col,
                                            id: col,
                                            placeholder: label,
                                            class: 'form-control',
                                            disabled:'disabled',
                                            rows:"2",
                                            style: "min-height:120px;max-height:120px;max-width:100%;min-width:100%;"
                                        })
                                ));
            return row;
        }else if(col=='memo'){
            var row = $("<div>")
                        .attr({ class: 'col-' + col + ' form-group' ,style:'width: 100%;'})
                        .append("<label>"+label+"</label>")
                        .append(
                            $("<div>")
                                .append(
                                    $("<textarea>")
                                        .attr({
                                            name: col,
                                            id: col,
                                            placeholder: label,
                                            class: 'form-control',
                                            rows:"2",
                                            style: "min-height:75px;max-height:75px;max-width:100%;min-width:100%;"
                                        })
                                ));
            return row;
        }else{
            var row = '';
            if(col=='entityId'){
                row = $("<div>")
                        .attr({ class: 'col-' + col + ' form-group' })
                        .append("<label>"+label+"</label>")
                        .append(
                            $("<div>")
                                .append(
                                    $("<input>")
                                        .attr({
                                            name: col,
                                            id: col,
                                            placeholder: label,
                                            class: 'form-control',
                                            style:'width: 75%;'
                                        })
                                )
                                .append(
                                    $('<button id="select_entityId"  data-toggle="modal" data-target="#form_select_entityId"  type="button" class="btn btn-primary" style="float: right;"><span class="glyphicon glyphicon-zoom-in"></span></button>'))
                        );
            }else if(col=='parentTable'){
                row = $("<div>")
                        .attr({ class: "col-"+col+" form-group" })
                        .append("<label>"+label+"</label>")
                        .append(
                            $("<div>")
                                .append(
                                    $("<select>")
                                        .attr({
                                            id: col,
                                            name: col,
                                            //value: value,
                                            class: "form-control"
                                        })
                                    .append([
                                        "<option value='0'>Term</option>",
                                        "<option value='2'>Knowledge Record</option>",
                                        "<option value='1'>Knowledge Link</option>"
                                    ])
                                ));
            }else if(col=='parentId'){
                row = $("<div>")
                        .attr({ class: 'col-' + col + ' form-group' })
                        .append("<label>"+label+"</label>")
                        .append(
                            $("<div>")
                                .append(
                                    $("<input  style='width: 75%;'>")
                                        .attr({
                                            name: col,
                                            id: col,
                                            placeholder: label,
                                            class: 'form-control',
                                            style:'width: 75%;'
                                        })
                                )
                                .append($('<button id="select_parentId"  data-toggle="modal" data-target="#form_select_parentId"  type="button" class="btn btn-primary"  style="float: right;"><span class="glyphicon glyphicon-zoom-in"></span></button>'))
                        );
            }else{
                if(col=='chatIntro'){ row = $("#languageItems").html(); $("#languageItems").remove(); }
                else{ row = ""; }
/*
                row = $("<div>")
                        .attr({ class: 'col-' + col + ' form-group' })
                        .append("<label>"+label+"</label>")
                        .append(
                            $("<div></div>")
                                .append(
                                    $("<input>")
                                        .attr({
                                            name: col,
                                            id: col,
                                            maxlength: chatIntro_voiceIntro_maxlength,
                                            placeholder: label,
                                            class: 'form-control '
                                        })

                                )
                        );
*/
            }
            return row;
        }
    }
    //------------------------------------------------------------

    //------------------------------------------------------------


  //--------------------------------------------------------------
  //--------------------------------------------------------------
  select_parentName(){
    let that = this;
    this.parentId=parseInt($("#parentName  option:selected").val());
    //this.searc_show();
    this.refreshOptions();
  };
  select_entityName(){
    let that = this;
    this.entityId=parseInt($("#entityName  option:selected").val());
    //this.searc_show();
    this.refreshOptions();
  };

//--------------------------------------------------------------
  select_parentTable(){
    let that = this;
    var selectedtablename=$("#parentTable  option:selected").text();
    this.parentTable=$("#parentTable  option:selected").val();
    switch (selectedtablename) {
      case 'Term':
        this.getTerms(0, $("select#parentId" ), 'n', "Search ...");
        break;
      case 'Knowledge Link':
        this.getKnowledgeLinks(0, $("select#parentId" ), 'n', "Search ...");
        break;
      case 'Knowledge Record':
        this.getKnowledgeRecord(0, $("select#parentId" ), 'n', "Search ...");
        break;
    }
  };
    
    //--------------------------------------------------------------
    sampleChatDisplay_val(){
        //----------------------------------------------------------
        var _sampleChatDisplay_val='';
        this.display_val.chatIntro=$("#chatIntro").val().trim();
        //----------------------------------------------------------
        if(this.display_val.chatIntro==''){
            if(this.display_val.lang!='en'){
                _sampleChatDisplay_val=this.display_val.langCaption +
                    " translation of:[ " +
                    this.display_val.eng_chatIntro + " " + this.display_val.atributes_en
                    " ]";
            }else{
                _sampleChatDisplay_val = this.display_val.atributes_en;
            }
        }else{
            _sampleChatDisplay_val=this.display_val.chatIntro;
            if ($('#includedExtDataName').prop("checked")){
                if(this.display_val.entityName!=''){ _sampleChatDisplay_val += this.display_val.entityName; }
            }
            if ($('#includedExtDataChatIntro').prop("checked")){
                if(this.display_val.subTypechatIntro!=''){ _sampleChatDisplay_val += this.display_val.subTypechatIntro; }
                if(this.display_val.atributes!=''){ _sampleChatDisplay_val += (" " + this.display_val.atributes); }
            }else{
                if(this.display_val.atributes!=''){ _sampleChatDisplay_val += (" " + this.display_val.atributes); }
            }
        }
        //----------------------------------------------------------
        $("#sampleChatDisplay").val(_sampleChatDisplay_val);
        //----------------------------------------------------------

        //----------------------------------------------------------
        var _sampleVoiceText_val='';
        this.display_val.voiceIntro = $("#voiceIntro").val().trim();
        if(this.display_val.voiceIntro==''){
            if(this.display_val.lang!='en'){
                _sampleChatDisplay_val=this.display_val.langCaption +
                    " translation of:[ " +
                    ((this.display_val.eng_voiceIntro=='') ?this.display_val.eng_chatIntro: this.display_val.eng_voiceIntro)
                    " ]";
            }
        }else{
            _sampleVoiceText_val = this.display_val.voiceIntro; 
            if( $('#includedExtDataChatIntro').prop("checked") ){
                if(this.display_val.subTypechatIntro!=''){ _sampleVoiceText_val += (' '+this.display_val.subTypechatIntro); }
                if(this.display_val.atributes       !=''){ _sampleVoiceText_val += (' '+this.display_val.atributes); }
            }else{
                if(this.display_val.atributes!=''){ _sampleVoiceText_val += (' '+this.display_val.atributes); }
            }
        }
        //----------------------------------------------------------
        $("#sampleVoiceText").val(_sampleVoiceText_val);
        //----------------------------------------------------------

        //----------------------------------------------------------
        $(".sampleChatDisplayHint").remove();
        $(".sampleVoiceTextHint"  ).remove();
        if(this.display_val.lang!='en'){
            if(this.display_val.chatIntro==''){
                    $("#sampleChatDisplay").parent()
                        .append('<small class="sampleChatDisplayHint hint">'+
                                    'The ' +
                                    "<b>" + this.display_val.langCaption + "</b>" +
                                    ' translation will be done by our Natural Language Layer at runtime' +
                                '</small>');
            }

            if(this.display_val.vchatIntro==''){
                    $("#sampleVoiceText").parent()
                        .append('<small class="sampleVoiceTextHint hint">'+
                                    'The ' +
                                    "<b>" + this.display_val.langCaption + "</b>" +
                                    ' translation will be done by our Natural Language Layer at runtime' +
                                '</small>');
            }
        }
        //----------------------------------------------------------
    }
    //--------------------------------------------------------------
    
  createTable(id,isrowReorder) {
    var temp_this=this;
    this.container = "#" + id;
    $(this.container).html('<table></table>');
    $(this.container).append(this.deleteDialog());
    $(this.container).append(this.actionForm());
    if(this.actionForm_copy){
      $(this.container).append(this.actionForm_copy());
    }
    $(this.container).append(this.actionEavForm());
    $(this.container).append(this.tableToolbar());
    $(this.container).append(
      $('<div class="modal fade" id="form_select_entityId" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">\n' +
      '</div>')
    );
    $(this.container).append(
      $('<div class="modal fade" id="form_select_parentId" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">\n' +
        '</div>')
    );


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
//    showExport: true,
//    exportDataType: 'all',
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
//    showExport: true,
//    exportDataType: 'all',
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
  //--------------------------------------------------------------
  //--------------------------------------------------------------
  getActionFormInput(col, label) {
    var row='';
    switch (col) {
        case 'sampleChatDisplay':
        case 'sampleVoiceText':{ return ""; }
        case 'sampleDisplay':{
            row = $("<div>")
                    .attr({ class:"col-"+col+" form-group", style: "vertical-align:top" })
                    .append(
                        $("<ul>").attr({ class:"nav nav-tabs" })
                        .append( $("<li><a href='#'>Preview Chat Display</a></li>").attr({ class:"active", id:"showChatDisplay" }) )
                        .append( $("<li><a href='#'>Preview Voice Text</a></li>").attr({ class:"", id:"showVoiceText" }) )
                    )
                    .append(
                        $("<div>")
                            .attr({ class:"col-sampleChatDisplay form-group", style: "width: 100%;" })
                            .append("<label>&nbsp;</label>")
                            .append(
                                $("<div>")
                                    .append(
                                        $("<textarea>")
                                            .attr({ 
                                                name:"sampleChatDisplay", 
                                                id:"sampleChatDisplay", 
                                                placeholder: "Preview Chat Display",
                                                class:"form-control", 
                                                disabled: "disabled",
                                                style: "min-height:75px;max-height:75px;max-width:100%;min-width:100%;" 
                                            })
                                    )
                            )
                    )
                    .append(
                        $("<div>")
                            .attr({ class:"col-sampleVoiceText form-group", style: "width: 100%;display:none" })
                            .append("<label>The following text will be converted to voice.</label>")
                            .append(
                                $("<div>")
                                    .append(
                                        $("<textarea>")
                                            .attr({ 
                                                name:"sampleVoiceText", 
                                                id:"sampleVoiceText", 
                                                placeholder: "Preview Voice Text",
                                                class:"form-control", 
                                                disabled: "disabled",
                                                style: "min-height:75px;max-height:75px;max-width:100%;min-width:100%;" 
                                            })
                                    )
                            )
                    );
            break;
        }
            
      case 'parentId':
      case 'entityId':
        row = $("<div>")
          .attr({
            class: "col-" + col + " form-group",
            style: "vertical-align:top",
          })
          .append( $("<label>"+label+"</label>") )
          .append(
            $("<div>")
              .append(
                $("<select>").attr({
                  id: col,
                  name: col,
                  class: 'form-control',
                  style: 'display:none'
                })
              )
              .append(
                $("<input>").attr({
                  id: col+'TEMP',
                  class: 'form-control',
                  disabled: true,
                  style: "width:calc( 100% - 25px ) !important;display:inline-block;margin-right:5px;"
                })
              )
              .append(
                $("<i>").attr({
                  id: col+'-btn-search',
                  class: 'fa fa-search',
                  style: '',
                  onclick: "showSearchBox('"+col+"', '"+label+"')"
                })
              )
          );
        break;
      /*case this.columns.reservedColumn:
        row = this.reservedFormInput(col, label);
        break;*/
      /*case this.columns.reservedColumn2:
        row = this.reservedFormInput(col, label);
        break;*/

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
    //------------------------------------------------------------
    actionForm(){
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

        var wrapper = $("<div>").attr({ id: 'editItem' });
        var form = $("<form>").attr({ class: 'action-form' });

        $(formChildren).each(function(i, el){
            form = $(form).append(el);
        });

        let divUnderButtons = $("<div>").attr({class:"divUnderButtons"});
        var submit = $(divUnderButtons)
            .append($("<input>")
                .attr({
                    id: submitId,
                    type: 'submit',
                    value: submitLabel,
                    class: 'btn btn-primary'
                })
            );

        var cancel = $(divUnderButtons)
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
    //------------------------------------------------------------
}
//----------------------------------------------------------------
var columns = [
    { name: 'extendedLinkId', display: 'ID', primary: true, sortable: true ,hidden: true},

    { name: 'parentTable', display: 'Parent Type', sortable: true, search: true ,hidden: true },
    { name: 'parentTableName', display: 'Parent Type', sortable: true, onlyFor: 0, editable: false ,searchWhere: true},

    { name: 'parentId', display: 'Parent', hidden: true },
    { name: 'parentName', display: 'Parent', sortable: true, onlyFor: 0, editable: false ,searchWhere: true},

    { name: 'entityId', display: 'Ext. Data', sortable: true, search: true ,hidden: true },
    { name: 'entityName', display: 'Ext. Data', sortable: true, onlyFor: 0, editable: false ,searchWhere: true},


    { name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
    { name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true , searchWhere: true},
    { name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },


    { name: 'chatIntro',  display: 'Add Chat Intro',                                      sortable:false, search:false , hidden:true},
    { name: 'voiceIntro', display: 'Add Voice Intro (if empty, Chat Intro will be used)', sortable:false, search:false , hidden:true},

    { name: 'sampleChatDisplay', display: 'Preview Chat Display', hidden: true },
    { name: 'sampleVoiceText'  , display: 'Preview Voice Text'  , hidden: true, editable:false },
    { name: 'sampleDisplay'    , display: '', hidden: true },

    { name: 'includedExtDataName', display: 'Include Ext Data Name',reserved: true, hidden: true, editable:false },
    { name: 'includedExtDataChatIntro', display:'Include Ext Data Chat Intro',reserved:true, hidden:true, editable:false},
    { name: 'memo', display: 'Memo', hidden: true},
    /*{ name: 'created_at', display: 'Created', sortable: true, editable: false, date: true },
    { name: 'updated_at', display: 'Updated', sortable: true, editable: false, date: true },*/
    { name: 'reserved', display: 'Reserved',sortable: true, reserved: true},
    { name: 'lastUserId', hidden: true, editable: false, default: '1'},
    { name: 'orderid', hidden: true, editable: false, default: '0'},
    { name: 'languageCode', hidden: true, editable: false, default: '1'},
];
var termColumns = new Columns(columns);
//----------------------------------------------------------------
var data = {
    columns: termColumns,
    apiURL: apiURL + '/api/extend/extended_link'
}
//----------------------------------------------------------------
if($("#extendedlink").length != 0){
    table = new ExtendedLink(data);
    table.createTable('extendedlink',true);
}
//----------------------------------------------------------------
//----------------------------------------------------------------

