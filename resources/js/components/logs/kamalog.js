import "../css/bootstrap-datetimepicker.min.css"
import '../extend/bootstrap-datetimepicker.min.js'

import { DataTable, showError, showSuccess } from '../DataTable'
import Columns from '../Columns'
//import Slider from 'jquery-ui-bundle'
//import 'jquery-ui-bundle/jquery-ui.min.css'

class Kamalog extends DataTable {
	constructor(data){
		super(data);
		this.showRefresh = true;
		let tmpThis = this;
		this.showGlobal = true;
		this.showGlobal = false;
		
		this.pageSort = 'timestamp';
		this.pageOrder = 'desc';
		this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;
		this.s_time = data.startDate;
		this.e_time = data.endDate;
		this.user_id= 0;
		this.org_id = (typeof(orgID ) != 'undefined')? orgID :0;
		this.searc_email='';
		this.archive=1;
		this.sele_s_e_time();

		this.selectedLog = {id:0, username:null, startTime:null, orgId:0 };

		var icon1 = $('<a></a>').attr({ href: '#', 'data-desc': 'View', class: 'menu_view', 'data-onlyowner': 1 });
		var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'Email', class: 'menu_email', 'data-onlyowner': 1 });
		var archiveIcon = $('<a></a>').attr({
			href: '#',
			style: "color:#2196f3;",
			class: 'archive-item',
			'data-desc': 'Archive',
			'data-onlyowner': 1
		});
		var deleteIcon = $("<a></a>").attr({
			href: '#',
			style: "color:#f3ae4e;",
			class: 'delete-item',
			'data-desc': 'Delete',
			'data-onlyowner': 1
		});
		
		this.actionIcons = [icon1,icon2,archiveIcon]; 
		if(orgID==0){ this.actionIcons.push(deleteIcon); }
		
		$('body').on('click', '.link-item', (e) => { this.showLinkDialogHandler(e) });
		tmpThis.showGlobalStatus=1;
	    $(this.container).append(this.tableToolbar());
		this.getorg_all();

		$('body').on('change select', '#searc_org_id', (e) => { this.changeSearcOrgID(e); });
		$('body').on('change select', "#showArchived", (e) => { this.changeArchive(e) });
		$('body').on('changeDate', '#start_time', (e) => { this.changeStartTime(e); });
		$('body').on('changeDate', '#end_time', (e) => { this.changeEndTime(e); });
		$('body').on('change input', '#searc_email', (e) => { this.changeSearcEmail(e); });

		$('body').on('click', '.closemode', (e) => { this.modal_default(e) });
		$('body').on('click', '.menu_view', (e) => { this.showMenu_viewDialogHandler(e) });

		$('body').on('click', '.menu_email', (e) => { 
			this.selectedLog.id = $(e.currentTarget).attr('data-itemid');
			$.each(this.rows, (i, item) => {
				if(item[this.columns.primaryColumn] == this.selectedLog.id){
					this.selectedLog.username = item.nickname;
					this.selectedLog.startTime = item.timestamp;
					this.selectedLog.orgId = item.org_id;
					this.editItem = item;
					return false;
				}
			});
			this.showMenu_emailDialogHandler(e) 
		});
		$('body').on('click', '#sendEmail', (e) => { this.showMenu_emailDialogHandler(e) });
		
		$('body').on('click', '.archive-item', (e) => { this.showArchiveDialogHandler(e) });
		$('body').on('click', '#archive-cancel', (e) => { this.cancelArchive(e) });
		$('body').on('click', "#archive-confirm", (e) => { this.archiveConfirmHandler() });
		
		$('body').on('click', "#closeSendEmail", (e) => { $("#kamaLogEmailModal").modal("hide"); });

		if(this.orgID!=0){ $("#searc_org_id").hide(); }
		
		let that = this;
		$("#end_time").ready(function(){
			that.refreshOptions(1);
		})

		$(".datetimepicker .datetimepicker-days th.prev>i").ready(function(){
			$(".datetimepicker .datetimepicker-days th.prev>i").attr("class", "fa fa-angle-double-left");
			$(".datetimepicker .datetimepicker-days th.next>i").attr("class", "fa fa-angle-double-right");
		});
		$(".datetimepicker .datetimepicker-months th.prev>i").ready(function(){
			$(".datetimepicker .datetimepicker-months th.prev>i").attr("class", "fa fa-angle-double-left");
			$(".datetimepicker .datetimepicker-months th.next>i").attr("class", "fa fa-angle-double-right");
		});
		$(".datetimepicker .datetimepicker-years th.prev>i").ready(function(){
			$(".datetimepicker .datetimepicker-years th.prev>i").attr("class", "fa fa-angle-double-left");
			$(".datetimepicker .datetimepicker-years th.next>i").attr("class", "fa fa-angle-double-right");
		});
	}
	//------------------------------------------------------------
	showDeleteDialogHandler(e) {
		super.showDeleteDialogHandler(e);
		let getData = $(this.table).bootstrapTable('getData');
		for( let i in getData){ if(getData[i].signin_id==this.deleteId){ $(this.table).bootstrapTable('check', i); } }

		getData = $(this.table).bootstrapTable('getSelections');
		if(getData.length>1){
			$("#deleteDialog>div>div:nth-child(1)")
				.html("<b>"+getData.length+"</b> logs will be deleted<br/><br/>Are you sure you want to delete this items?");
			$("#deleteDialog>div").css("height", "130px");
		}else{
			$("#deleteDialog>div").css("height", "90px");
			$("#deleteDialog>div>div:nth-child(1)").html("Are you sure you want to delete this item?");
		}
	}
	deleteConfirmHandler(){
		let getData = $(this.table).bootstrapTable('getSelections');
		for(let i in getData){
			this.deleteId = getData[i].signin_id;
			super.deleteConfirmHandler();
		}
	}
	//------------------------------------------------------------
	rowActions(value, row, index, field){
		var icons = this.actionIcons;
		$("[data-menu-toggle='#actions-menu-"+index+"']").remove();
	    if(icons.length==0){ return ''; }
	    var rowAction = '<div class="row-actions"></div>';
    	if(row.archive!=undefined){ rowAction = '<div class="row-actions" archivevalue="'+row.archive+'"></div>'; }
		var others = '<ul class="menu-actions" data-menu data-menu-toggle="#actions-menu-'+index+'" style="font-size:12px;"></ul>';
		for (var i in icons){
			if(icons[i].attr('class')=='archive-item'){
				icons[i].attr('data-archive',0);
				if(row.archive==1){ 
					icons[i].attr('data-desc','Restore');
					icons[i].attr('data-archive',1)
				}else{ icons[i].attr('data-desc','Archive') }
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
		$(document).ready(function(e){$("[data-menu]").menu(); });
		return $(rowAction)[0].outerHTML;
	}
	//------------------------------------------------------------
	showArchiveDialogHandler(e) {
		e.preventDefault();
		this.archiveId = $(e.currentTarget).data('itemid');

		if($(e.currentTarget).data('archive')=='1'){ $(this.table).bootstrapTable('uncheckAll'); }
		let getData = $(this.table).bootstrapTable('getData');
		for( let i in getData){
			if(getData[i].archive=='1'){ $(this.table).bootstrapTable('uncheck', i); }
			if(getData[i].signin_id==this.archiveId){ $(this.table).bootstrapTable('check', i); }
		}

		getData = $(this.table).bootstrapTable('getSelections');
		if(getData.length>1){
			$("#archiveDialog_msg")
				.html("<b>"+getData.length+"</b> logs will be archived<br/><br/>Are you sure you want to archive this items?");
			$("#archiveDialog>div").css("height", "130px");
		}else{
			$("#archiveDialog>div").css("height", "90px");
			if($(e.currentTarget).data('archive')=='1'){ $("#archiveDialog_msg").html("Are you sure you want to restore this item?"); }
			else{ $("#archiveDialog_msg").html("Are you sure you want to archive this item?"); }
			
		}
		$('#archiveDialog').fadeIn();
	}
	archiveConfirmHandler(){
		let getData = $(this.table).bootstrapTable('getSelections');
		for(let i in getData){
			this.archiveId = getData[i].signin_id;
			this.archiveConfirmHandler_();
		}
	}
	get archiveURL() { return this.apiURL + "/upArchive/" + ((this.orgID) ? (this.orgID + '/') : '') + this.archiveId; }
	cancelArchive(e){ $('#archiveDialog').fadeOut(); }
	archiveConfirmHandler_(){
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
				if(res.result == 1){ showError(res.showMsg); }
				else{
					if(res.archive=='1'){ showSuccess('Archived successfully'); }
					else{ showSuccess('Restored successfully'); }
					$(table).bootstrapTable('refresh');
				}
			},
			error: function(e){ showError('IN USE. DELETE RELATED RECORDS FIRST'); }
		});
	}
	//------------------------------------------------------------
	modal_default(e){
		$('#kamaLogModal').removeClass('in');
		$('#kamaLogEmailModal').removeClass('in');
		
		$('#kamaLogModal').css('display','none');
		$('#kamaLogEmailModal').css('display','none');
		
		this.selectedLog = {id:0, username:null, startTime:null, orgId:0 };
	}
	//------------------------------------------------------------
	changeSearcEmail(e){
		var target = e.currentTarget;
		var value = target.value;
		this.searc_email=value;
		this.pageNumber=1;
		try{ this.refreshOptions(true); }catch(e){}
		$('#searc_email').focus();
	}
	//------------------------------------------------------------
	changeArchive(e){
		var target = e.currentTarget;
		var value = target.value;
		if(value=='NULL'){ value=1; }
		this.archive=value;
		
		this.refreshOptions(true)
	}
	//------------------------------------------------------------
	changeStartTime(e){
		$('.datetimepicker-dropdown-bottom-right').css('display','none');
		this.s_time=e.date.getTime()/1000+3600*13;
		this.pageNumber=1;
		this.refreshOptions(true)
	}
	changeEndTime(e){
		$('.datetimepicker-dropdown-bottom-right').css('display','none');
		this.e_time=(e.date.getTime()/1000);//+3600;//*13;
		this.pageNumber=1;
		this.refreshOptions(true)
	}
	//------------------------------------------------------------
	changeSearcOrgID(e){
		var target = e.currentTarget;
		var value = target.value;
		if(value=='NULL'){value=0;}
		this.org_id=value;
		this.pageNumber=1;
		this.refreshOptions(true);
	}
	//------------------------------------------------------------
	sele_s_e_time(){
		var cDate = new Date();
		var sDate = new Date(cDate.setDate(cDate.getDate()-7));
		var eDate = new Date();
		sDate.setMinutes(0);
		sDate.setHours(0);
		sDate.setSeconds(0);
		sDate.setSeconds(0);
		eDate.setMilliseconds(0);
		this.s_time=( sDate.getTime()/1000);//+3600*13;
		this.e_time=( eDate.getTime()/1000);//+3600*13;
	}
	//------------------------------------------------------------
	refreshOptions(pageNumber){
		var url = (this.search == '')? this.getURL: this.searchURL;
		if(pageNumber){ $(this.table).bootstrapTable('refreshOptions', {url: url,pageNumber:1}); }
		else{ $(this.table).bootstrapTable('refreshOptions', {url: url}); }
	}
	//------------------------------------------------------------
	get org_allURL() { return this.apiURL + '/org_all' }
	getorg_all(){
		$.get(this.org_allURL, (res) => {
			res.data.unshift({org_id:null, org_name:'Owner All'});
			this.org_all = this.createSelectOptions(res.data, 'org_id', 'org_name');
			$("#searc_org_id").append(this.org_all);
		});
	}
	//------------------------------------------------------------
	tableToolbar(){
		var toolbar = $("<div>").attr({
			id: 'tableToolbar'
		});
		toolbar.append(
			$("<div>")
				.attr({ class: "searc_org_id where-group" })
				.append($("<select>")
					.attr({
						id: 'searc_org_id',
						name: 'searc_org_id',
						value: "",
						class: "where-control"
					})
				)
		);

		toolbar.append(
			$('<input  id="searc_email" maxlength="50" style="height: 34px;\n' +
			'    padding: 6px 10px;\n' +
			'    font-size: 12px;\n' +
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
			$('<input id="start_time" size="16" type="text"  readonly="" class="form_datetime" style="' +
			'    cursor: not-allowed;\n' +
			'    background-color: #eee;\n' +
			'    display: inline-block;\n' +
			'    height: 34px;\n' +
			'    padding: 4px 6px;\n' +
			'    font-size: 12px;\n' +
			'    line-height: 34px;\n' +
			'    color: #555;\n' +
			'    vertical-align: middle;\n' +
			'    border-radius: 4px;margin-left: 0.5rem;\n' +
			'    margin-right: .5rem;' +
			'">')
		);
		
		toolbar.append(
			$('<input id="end_time" size="16" type="text"  readonly="" class="form_datetime" style="' +
			'    cursor: not-allowed;\n' +
			'    background-color: #eee;\n' +
			'    display: inline-block;\n' +
			'    height: 34px;\n' +
			'    padding: 4px 6px;\n' +
			'    font-size: 12px;\n' +
			'    line-height: 34px;\n' +
			'    color: #555;\n' +
			'    vertical-align: middle;\n' +
			'    border-radius: 4px; margin-left: 0.5rem;\n' +
			'    margin-right: 1rem;' +
			'">')
		);

		toolbar.append(
			$("<div>")
				.attr({ class: "showArchived where-group" })
				.append($("<select>")
					.attr({
						id: 'showArchived',
						name: 'showArchived',
						value: "1",
						class: "where-control",
						style: "width:140px"
					})
					.append("<option value='1'>Show UnArchived</option>")
					.append("<option value='0'>Show All</option>")
					.append("<option value='2'>Show Archived</option>")
				)
		);

		return toolbar;
	}
	//------------------------------------------------------------
	showLinkDialogHandler(e){
		e.preventDefault();
		window.location.href=this.apiURLBase + '/panel/extend/extendedlink/0/'+$(e.currentTarget).data('itemid');
	}
	//------------------------------------------------------------
	get getURL(){
		let myStartTime = $("#start_time").val().trim().replace(" ", "_").replace(/-/g, "_").replace(/:/g, "_");
		let myEndTime   = $("#end_time"  ).val().trim().replace(" ", "_").replace(/-/g, "_").replace(/:/g, "_");
		if(this.searc_email==''){ this.searc_email='0'; }
		if(this.search==''){ this.search='0'; }
		return ""+
			this.apiURL+'/page/'+
			this.archive+ '/'+
//			parseInt(this.s_time)+'/'+
//			parseInt(this.e_time)+'/'+
			myStartTime+'/'+
			myEndTime+'/'+
			this.user_id + '/'+
			this.org_id	+ '/'+
			this.pageSort + '/' + 
			this.pageOrder + '/' + 
			this.pageSize +'/' + 
			this.pageNumber	+ '/'+
			this.searc_email+ '/'+
			this.search+ '/';
	}
	get searchURL() {
		let myStartTime = $("#start_time").val().trim().replace(" ", "_").replace(/-/g, "_").replace(/:/g, "_");
		let myEndTime   = $("#end_time"  ).val().trim().replace(" ", "_").replace(/-/g, "_").replace(/:/g, "_");
		if(this.searc_email==''){ this.searc_email='0'; }
		if(this.search==''){ this.search='0'; }
		return ""+
			this.apiURL+'/page/'+
			this.archive+ '/'+
//			parseInt(this.s_time)+'/'+
//			parseInt(this.e_time)+'/'+
			myStartTime+'/'+
			myEndTime+'/'+
			this.user_id + '/'+
			this.org_id + '/'+
			this.pageSort + '/' + 
			this.pageOrder + '/' + 
			this.pageSize +'/' + 
			this.pageNumber + '/'+
			this.searc_email+ '/'+
			this.search+ '/';
	}
	//------------------------------------------------------------

	//------------------------------------------------------------
	showMenu_viewDialogHandler(e){
		var that = this;
		//--------------------------------------------------------
		e.preventDefault();
		var tabindex = $(e.currentTarget).attr('data-itemid');
		that.selectedLog.id = tabindex;

		$('#kamaLogModal').addClass('in');
		$('#kamaLogModal').css('display','block')
		$('#kamaLogModal').css('overflow','auto')
		//--------------------------------------------------------
		var getheadtalbe=function(data){
			that.selectedLog.username = data.nickname;
			that.selectedLog.startTime = data.timestamp;
			that.selectedLog.orgId = data.org_id;
			let table_str= ''+
				'<table width="100%" cellspacing="0" cellpadding="5" border="1">\n' +
				'<tbody>\n' +
				'	<tr><td colspan="2" class=""><div >'+data.memo+'</div></td></tr>\n' +
				'	<tr><td><b>IP</b></td><td>'+data.ip+'</td></tr>\n' +
				'	<tr><td><b>Chat start time</b></td><td>'+data.timestamp+'</td></tr>\n' +
				'	<tr><td><b>User ID</b></td><td>'+data.user_id+'</td></tr>\n' +
				'	<tr><td><b>User Name</b></td><td>'+data.nickname+'</td></tr>\n' +
				'	<tr><td><b>Org ID</b></td><td>&nbsp;'+data.org_id+'</td></tr>\n' +
				'	<tr><td><b>Org Name</b></td><td>&nbsp;'+data.org_name+'</td></tr>\n' +
				'	<tr>'+
					'<td style="width:10%;min-width:100px;"><b>Email</b></td>'+
					'<td style="max-width:85%;word-break:break-all;">&nbsp;'+data.showEmail+'</td>'+
				'</tr>\n' +
				'</tbody>\n' +
				'</table>';
			return table_str;
		}
		//--------------------------------------------------------
		var getneirongtable=function(data,ainame,username){
			let tmp_table1='<div style="width:100%; height:350px; overflow:auto;">';
			let tmp_table=''+
				'<table width="100%" cellspacing="0" cellpadding="5" border="1">\n' +
				'<tbody>\n' +
				'	<tr><td><div>Chat  Transcript</div></td></tr>\n' +
				'	<tr><td>' ;
	
			let temp_end= ''+
				'</td></tr>\n' +
				'</tbody>\n' +
				'</table>';
	
			let temp_body='';
			if(data){
				var tempdata={};
				for(let i=0;i<data.length;i++){
					if(data[i].sender=='AI'){
						var newM = JSON.parse(data[i].showRawMsg);
						if (newM.response.type == 'text'){
							if(newM.response.err){
								tmp_table1+=''+
									'<div class="chat_v bot">' +
										'<div class="chat_b" style="color: #ff0000;">' +
											'<p style="">'+ainame+'<br/>' + newM.response.message+'<br/>' +data[i].timestamp+ '</p>'+
										'</div>'+
									'</div>';
							}else{
								let msgVal = newM.response.message;
								try{ 
									if(msgVal==''){ 
										for(let iinnddxx in newM.response.messages){
											msgVal += newM.response.messages[iinnddxx].value+" ";
										}
									}
								}catch(ex){}
								tmp_table1+=''+
									'<div class="chat_v bot">' +
										'<div class="chat_b" style="color: #6f6f6f;">' +
											'<p>'+ainame+'<br/>' + msgVal +'<br/>' +data[i].timestamp+ '</p>'+
										'</div>'+
									'</div>';
							}
						}
						else if (newM.response.type == 'yesno'){
							tmp_table1+=''+
								'<div class="chat_v bot">' +
									'<div class="chat_b" style="color: #6f6f6f;">' +
										'<p>'+ainame+'<br/>' + newM.response.message + '<br/>' +data[i].timestamp + '</p>' +
										'<div class="rd">' +
											'<span class="myYes">Yes</span>' +
											'<span class="myNo">No</span>' +
										'</div>' +
									'</div>';
						}
						else if (newM.response.type == 'radiobutton'){
							var rd = '';
							//----------------------------------------------------------------------------------------------
							if(typeof newM.response.answers!=="undefined"){
								for(var ii = 0; ii < newM.response.answers.length; ii++){
									//--------------------------------------------------------------------------------------
									if(typeof newM.response.answers[ii].text !=="undefined"){
										rd+='<span class="my_radiobutton" type='+newM.response.answers[ii].value+'>'+
												newM.response.answers[ii].text+
											'</span>';
										tempdata[newM.response.answers[ii].value]=newM.response.answers[ii].text;
									}
									//--------------------------------------------------------------------------------------
									else{
										if(typeof newM.response.answers[ii].url !=="undefined"){
											rd+='<span class="my_radiobutton" type='+newM.response.answers[ii].value+'>'+
													newM.response.answers[ii].url+
												'</span>';
											tempdata[newM.response.answers[ii].value]=newM.response.answers[ii].url;
										}else{
											tempdata[newM.response.answers[ii].value]=newM.response.answers[ii].value;
											continue;
										}
									}
									//--------------------------------------------------------------------------------------
								}
							}
							//----------------------------------------------------------------------------------------------
							if(typeof newM.response.slidebar!=="undefined"){
								for(var ii = 0; ii < newM.response.slidebar.length; ii++){
									//--------------------------------------------------------------------------------------
									rd+='<span class="my_radiobutton" style="width:24.5%;" type='+newM.response.slidebar[ii].name+'>'+
											newM.response.slidebar[ii].name+
											' [ '+newM.response.slidebar[ii].value2+' , '+newM.response.slidebar[ii].value3+' ] : '+
											newM.response.slidebar[ii].value1+
										'</span>';
									//--------------------------------------------------------------------------------------
								}
							}
							//----------------------------------------------------------------------------------------------
							if(typeof newM.response.buttons!=="undefined"){
								for(var ii = 0; ii < newM.response.buttons.length; ii++){
									//--------------------------------------------------------------------------------------
									rd+='<span class="my_radiobutton" type='+newM.response.buttons[ii].value+'>'+
											newM.response.buttons[ii].text+
										'</span>';
									tempdata[newM.response.buttons[ii].value]=newM.response.buttons[ii].text;
									//--------------------------------------------------------------------------------------
								}
							}
							//----------------------------------------------------------------------------------------------
							let msgVal = newM.response.message;
							try{ 
								if(msgVal==''){ 
									for(let iinnddxx in newM.response.messages){
										msgVal += newM.response.messages[iinnddxx].value+" ";
									}
								}
							}catch(ex){}
							tmp_table1+=''+
								'<div class="chat_v bot">' +
									'<div class="chat_b" style="color: #6f6f6f;">' +
										'<p>'+ainame+'<br>' + msgVal +'<br/>' +data[i].timestamp + '</p>' +
										'<div class="rd">' + rd + '</div>'+
									'</div>' +
								'</div>';
						}else if (newM.response.type == 'valueslider'){
							var rd = '';
							for(var ii = 0; ii < newM.response.answers.length; ii++){
								rd += ''+
									'<div class="sd_g" type=' + newM.response.answers[ii].value + '>' +
										'<p>' + newM.response.answers[ii].text + ':' +
											'<span class="tn">' + '? (1 to 10), (' + newM.response.answers[ii].value + ')?' + ' </span>'+
										'</p>' +
										'<div class="sd_l"></div>' +
										'<div class="sd_s">' +
											'<div class="sd_b" style="left:' + newM.response.answers[ii].value * 10 + '%' + '"></div>'+
										'</div>'+
									'</div>';
							}
							tmp_table1+=''+
								'<div class="chat_v bot">' +
									'<div class="chat_b" style="color: #6f6f6f;">' +
										'<p>'+ainame+'<br/>' + newM.response.message + '</p>' +
										'<div class="sd">' + rd + 
											'<div class="sd_sb">click when done</div>'+
										'</div>'+
									'</div>' +
									'<br/>'+
									'<p>' +data[i].timestamp+ '</p>'+
								'</div>';
						}else{
							tmp_table1+=''+
								'<div class="chat_v bot">' +
									'<div class="chat_b" style="color: #6f6f6f;">' +
										'<p>'+ainame+'<br/>' + newM.response.message+'<br/>'+data[i].timestamp + '</p>'+
									'</div>'+
								'</div>';
						}
						if(typeof data[i].feedback!=='undefined'){
							tmp_table1+=''+
								'<div class="chat_v bot">' +
									'<div class="chat_b" style="color: #6f6f6f;">' +
										'<p>feedback: '+data[i].feedback+'</p>'+
									'</div>'+
								'</div>';
						}
					}
					else{
						if(data[i].sender=='System'){
							var temp_mmsg=data[i].showRawMsg
								if(tempdata[data[i].showRawMsg]){ temp_mmsg=tempdata[data[i].showRawMsg]; }
								tmp_table1+=''+
									'<div class="chat_v mine" style="justify-content:flex-start">' +
										'<div class="chat_b" style="color:#6f6f6f; background:#efefef;">' +
											'<p>Kamazooie Development<br>' +temp_mmsg +'<br>'+data[i].timestamp+ '</p>'+
										'</div>'+
									'</div>';
						}
						else{
							var temp_mmsg="";
							try{
								var tmpM = JSON.parse(data[i].showRawMsg);
								if(tmpM.answers.length!=0){
									for(let jj in tmpM.answers){
										temp_mmsg+= ('<span>'+tmpM.answers[jj].text+' : [ '+tmpM.answers[jj].value+' ]</span><br/>');
									}
								}
								if(tempdata[tmpM.utterance]){ temp_mmsg+=tempdata[tmpM.utterance]; }
								else{ temp_mmsg+=tmpM.utterance; }

							}catch(ex){
								temp_mmsg=data[i].showRawMsg;
								if(tempdata[temp_mmsg]){ temp_mmsg=tempdata[temp_mmsg]; }
							}

							tmp_table1+=''+
								'<div class="chat_v mine">' +
									'<div class="chat_b" style="color: #fff;">' +
										'<p>' +username+':<br/>' +temp_mmsg +'<br/>'+data[i].timestamp+ '</p>'+
									'</div>'+
								'</div>';
						}
						if(typeof data[i].feedback!=='undefined'){
							tmp_table1+=''+
								'<div class="chat_v mine" style="margin-top:-2px;">' +
									'<div class="chat_b" style="color: #fff;">' +
										'<p>feedback: '+data[i].feedback+'</p>'+
									'</div>'+
								'</div>';
						}
					}
				}
			}
			return tmp_table1+'</div>';
		}
		//--------------------------------------------------------
		$.each(this.rows, (i, item) => {
			if(item[this.columns.primaryColumn] == tabindex){
				this.editItem = item;
				$('#kamaLogModal .modal-body').html(getheadtalbe(item));
				return false;
			}
		});
		//--------------------------------------------------------
		$.get(this.apiURL+'/chatlog/'+tabindex, (res) => {
			if(res.result == 1){ showError(res.showMsg); }
			else{
				$('#kamaLogModal .modal-body').html($('#kamaLogModal .modal-body').html()+getneirongtable(res.data,this.editItem.org_name,this.editItem.nickname));
			}
		});
		//--------------------------------------------------------
	}
	//------------------------------------------------------------
	showMenu_emailDialogHandler(e){
		var that = this;
		e.preventDefault();
		$('#kamaLogEmailModal')
			.modal({backdrop:"static"})
			.on('shown.bs.modal', function(){ 
				$("#emailTo").focus(); 
				$('#emailTo').val("");
				$('#emailSubject').val("Kama-DEI chat log, "+that.selectedLog.username+", "+that.selectedLog.startTime);
				$('#emailInstructions').val("Hi"+"\n"+"This is a log from Kama-DEI.");
			});
		
		$('#sendEmaiBTN').off('click');
		
		$('#sendEmaiBTN').on('click',
			function() {
				var emailTo           = $('#emailTo').val().trim();
				var emailSubject      = $('#emailSubject').val().trim();
				var emailInstructions = $('#emailInstructions').val().trim();
				if(!/\S+@\S+\.\S+/.test(emailTo)){ showError('Please enter valid the mail address.'); return }
				if(emailSubject==''){ showError('Please enter subject.'); return; }
				if(emailTo){
					$.ajax({
						url: that.apiURL+'/email',
						data: {
							log_id:that.selectedLog.id,
							email:emailTo,
							subject:emailSubject,
							orgId:that.selectedLog.orgId,
							instructions:emailInstructions,
						},
						type: 'POST',
						async: true,
						beforeSend:function(){
							$("#closeSendEmail, #sendEmaiBTN, #emailTo, #emailSubject").prop('disabled', true);
							showSuccess('Sending...'); 
						},
						complete: function(){
							$("#closeSendEmail, #sendEmaiBTN, #emailTo, #emailSubject").prop('disabled', false);
						},
						success: (data) => {
							if(data.result==0){
								showSuccess('Successful mail delivery.');
								$('#kamaLogEmailModal').modal('hide');
							}else{ showError(data.msg); }
						}
					});
				}else{ showError('Please enter the mail address.'); }
		});
	}
	//------------------------------------------------------------
	get getColumns() {
		var columns = [];
		for(var x in this.columns.data){
			var column = this.columns.data[x];
			if(column.hidden === true && column.primary !== true) continue;
			if(column.name=='checkBox'){
				var obj = {
					//        editable: true,
					field: column.name,
					title: column.display,
					sortable: (column.sortable)? true:false,
					visible: (column.hidden === true)? false:true,
					formatter: (value, row, index, field) => this.checkBoxRender(value, row, index, field),
					checkbox: true
				}
			}else{
				var obj = {
					//        editable: true,
					field: column.name,
					title: column.display,
					sortable: (column.sortable)? true:false,
					visible: (column.hidden === true)? false:true,
					formatter: (value, row, index, field) => this.cellRenderer(value, row, index, field),
					checkbox: false
				}
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
	//------------------------------------------------------------
	checkBoxRender(value, row, index, field){
		if(this.ownerId!=0){
			if(row.ownerId!=this.ownerId){ return { disabled: true }; }
		}
		return value;
	}
	//------------------------------------------------------------
}

var columns = [
	{ name: 'checkBox', display: '', sortable: false, editable: false },
//	{name: 'state', checkbox: true},
	{ name: 'signin_id', display: 'ID'         ,field: 'selectItem',primary: true, sortable: false, search: false, hidden: true },
//	{ name: 'email'  , display: 'User(email)',sortable: true, search: true },
	{ name: 'showEmail'  , display: 'User(email)',sortable: true, search: true },
	
//	{ name: 'user_name', display: 'Nickname',sortable: true},
	{ name: 'nickname', display: 'Nickname',sortable: false},
	{ name: 'timestamp', display: 'Start Date/Time', sortable: true},
	
	{ name: 'org_id', display: 'Owner', hidden: true, search: true},
	{ name: 'user_id', display: 'Nick Name', hidden: true },
	
	
	{ name: 'org_name', display: 'Owner', sortable: true},
	
	{ name: 'log_s', display: 'Duration'},
	{ name: 'logcount', display: 'Log Count'},
	
	{ name: 'ip', display: 'IP',sortable: true },
	{ name: 'memo', display: 'Memo',hidden: true},

	{ name: 'archive', display: 'Archive',sortable: false,hidden: false,editable: false,search: false, reserved: true },
];
var termColumns = new Columns(columns);

var data = {
	columns: termColumns,
	apiURL: apiURL + '/api/dashboard/logs'
}

if($("#kamalog").length != 0){
	//-------------------------------------------------------------------
	var dataFormat = function(thisdata,fmt){ 
		var o = {
			"M+" : thisdata.getMonth()+1,                 //month
			"d+" : thisdata.getDate(),                    //day
			"h+" : thisdata.getHours(),                   //hour
			"m+" : thisdata.getMinutes(),                 //Minute
			"s+" : thisdata.getSeconds(),                 //second
			"q+" : Math.floor((thisdata.getMonth()+3)/3), //Quarter
			"S"  : thisdata.getMilliseconds()             //millisecond
		};
		
		if(/(y+)/.test(fmt)) fmt=fmt.replace(RegExp.$1, (thisdata.getFullYear()+"").substr(4 - RegExp.$1.length));
		for(var k in o) if(new RegExp("("+ k +")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
		return fmt;
	}
	//-------------------------------------------------------------------
	var cDate   = new Date();
	var eDate   = dataFormat(cDate,"yyyy-MM-dd hh:mm:ss");
	var sDate = new Date(cDate.setDate(cDate.getDate()-7));
	sDate.setMinutes(0);
	sDate.setHours(0);
	sDate.setSeconds(0);
	sDate.setMilliseconds(0);
	sDate = dataFormat(sDate,"yyyy-MM-dd hh:mm:ss");
	
	data.curDate   = cDate;
	data.endDate   = eDate;
	data.startDate = sDate;
	//-------------------------------------------------------------------
	var table = new Kamalog(data);
	table.createTable('kamalog');
	//-------------------------------------------------------------------
	$("#start_time").val(sDate).change();
	$("#end_time"  ).val(eDate).change();
	$(".form_datetime").datetimepicker({format: 'yyyy-mm-dd hh:ii:ss'});
	//-------------------------------------------------------------------
	var kamalog_viewForm=function(){
		var htmlviewstr='\n' +
			'<div class="modal fade" id="kamaLogModal" tabindex="-1" role="dialog" aria-labelledby="kamaLogModalLabel" aria-hidden="true">\n' +
			'\t<div class="modal-dialog modal-lg">\n' +
			'\t\t<div class="modal-content">\n' +
			'\t\t\t<div class="modal-header">\n' +
			'\t\t\t\t<button type="button" class="close closemode" data-dismiss="modal" aria-hidden="true">\n' +
			'\t\t\t\t</button>\n' +
			'\t\t\t\t<h4 class="modal-title" id="kamaLogModalLabel">Kama Log\n' +
			'\t\t\t\t</h4>\n' +
			'\t\t\t</div>\n' +
			'\t\t\t<div class="modal-body">\n' +
			'\t\t\t</div>\n' +
			'\t\t\t<div class="modal-footer">\n' +
			'\t\t\t\t<button type="button" class="btn btn-info" style="float:left" id="sendEmail" >Send to an email</button>\n' +
			'\t\t\t\t<button type="button" class="btn btn-danger closemode"  data-dismiss="modal">Close</button>\n' +
			'\t\t\t</div>\n' +
			'\t\t</div><!-- /.modal-content -->\n' +
			'\t</div><!-- /.modal -->\n' +
			'</div>';
			
	    if($('#kamalog')){ $('#kamalog').append(htmlviewstr); }
	};
	kamalog_viewForm();
	//-------------------------------------------------------------------
	var  kamaLogEmailForm=function(){
		var htmlviewstr='\n' +
		'<div class="modal fade" id="kamaLogEmailModal" tabindex="-1" role="dialog" aria-labelledby="kamaLogEmailModalLabel" aria-hidden="true">\n' +
		'\t<div class="modal-dialog">\n' +
		'\t\t<div class="modal-content">\n' +
		'\t\t\t<div class="modal-header">\n' +
		'\t\t\t\t<button type="button" class="close closemode"  data-dismiss="modal" aria-hidden="true">\n' +
		'\t\t\t\t</button>\n' +
		'\t\t\t\t<h4 class="modal-title" id="kamaLogEmailModalLabel">Send email \n' +
		'\t\t\t\t</h4>\n' +
		'\t\t\t</div>\n' +
		'\t\t\t<div class="modal-body">\n' +
			'<div class="input-group input-group-lg">\n' +
			'	<span class="input-group-addon">To</span>\n' +
			'	<input type="text" class="form-control" placeholder="Email Address(es)" id="emailTo">\n' +
			'</div>'+
			'<div class="input-group input-group-lg" style="margin-top:10px;">\n' +
			'	<span class="input-group-addon">Subject</span>\n' +
			'	<input type="text" class="form-control" placeholder="Subject" id="emailSubject">\n' +
			'</div>'+
			'<div class="input-group input-group-lg" style="margin-top:10px;">\n' +
			'	<span class="input-group-addon" style="padding:15px;">Introduction</span>\n' +
			'	<textarea class="form-control" placeholder="Introduction" id="emailInstructions"'+
					'style="max-height:100px;min-height:100px;min-width:99%;max-width:100%;"'+
				'>'+
					'Hi\n'+
					'This is a log from Kama-DEI'+
				'</textarea>\n' +
			'</div>'+
		'\t\t\t</div>\n' +
		'\t\t\t<div class="modal-footer">\n' +
		'\t\t\t\t<button type="button" class="btn btn-danger" id="closeSendEmail" style="float:left;width:80px;">Cancel\n' +
		'\t\t\t\t</button>\n' +
		'\t\t\t\t<button type="button" class="btn btn-primary" id="sendEmaiBTN" style="width:80px;" >Send</button>\n' +
		'\t\t\t</div>\n' +
		'\t\t</div><!-- /.modal-content -->\n' +
		'\t</div><!-- /.modal -->\n' +
		'</div>';
		if($('#kamalog')){ $('#kamalog').append(htmlviewstr); }
	}
	kamaLogEmailForm();
	//-------------------------------------------------------------------
	var  kamaLogArchive=function(){
		var htmlviewstr='\n' +
			"<div id='archiveDialog' style='display:none;z-index:10'>"+
				"<div>"+
					"<div id='archiveDialog_msg' >Do you really want to archive record?</div>"+
					"<div class='archiveActions'>"+
						"<button id='archive-cancel'  class='btn btn-danger' >No</button>"+
						"<button id='archive-confirm' class='btn btn-primary' style='float:right' >Yes</button>"+
					"</div>"+
				"</div>"+
			"</div>";
		if($('#kamalog')){ $('#kamalog').append(htmlviewstr); }
	}
	kamaLogArchive();
}
