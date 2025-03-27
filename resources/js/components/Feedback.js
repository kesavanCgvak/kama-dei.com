import "./css/bootstrap-datetimepicker.min.css"
import './extend/bootstrap-datetimepicker.min.js'

import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'
//import Slider from 'jquery-ui-bundle'
//import 'jquery-ui-bundle/jquery-ui.min.css'

class Feedback extends DataTable {
	//-----------------------------------------
	constructor(data){
		super(data);
		this.pageSort = 'chat_date';
		this.pageOrder = 'desc';
		this.viewData   = null;
		this.archive=1;
		
		let icon1 = $('<a></a>').attr({ href:'#', 'data-desc':'View', 'data-msgid':0, class:'menu_view', 'data-onlyowner':1 });
		var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'Send to email', class: 'menu_email', 'data-onlyowner':0 });
		var icon3 = $('<a></a>').attr({ href: '#', 'data-desc': 'Archive', class: 'menu_archive', 'data-onlyowner':1 });
		this.actionIcons = [icon1, icon2, icon3];

		this.s_time = data.startDate;
		this.e_time = data.endDate;

		$('body').on('click', '.menu_view', (e) => { this.showMenu_viewDialogHandler(e) });
		
	    $(".fixed-table-toolbar").append(this.tableToolbar());
		
		this.getorg_all();
		let that = this;

		$('body').on('changeDate', '#start_time', (e) => { 
			$('.datetimepicker-dropdown-bottom-right').css('display','none');
			that.s_time=e.date.getTime()/1000+3600*13;
			$(that.table).bootstrapTable('refreshOptions', {pageNumber:1});
		});
		
		$('body').on('changeDate', '#end_time', (e) => {
			$('.datetimepicker-dropdown-bottom-right').css('display','none');
			that.e_time=(e.date.getTime()/1000);//+3600;//*13;
			$(that.table).bootstrapTable('refreshOptions', {pageNumber:1});
		});

		$('body').on('change select', '#searc_org_id', (e) => {
			$(that.table).bootstrapTable('refreshOptions', {pageNumber:1});
		});
		$('body').on('change', '#searc_email', (e) => {
			$(that.table).bootstrapTable('refreshOptions', {pageNumber:1});
		});
		$('body').on('keypress', '#searc_email', (e) => {
			$(that.table).bootstrapTable('refreshOptions', {pageNumber:1});
			$("#searc_email").focus();
		});
		$('body').on('keyup', '#searc_email', (e) => {
			$(that.table).bootstrapTable('refreshOptions', {pageNumber:1});
			$("#searc_email").focus();
		});

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
		
		$('body').on('click', '.menu_archive', (e) => { 
			let id        = $(e.currentTarget).attr('data-itemid');
			let that = this;
			$.ajax({
				url: that.apiURL+'/archive',
				data: { feedback_id:id },
				type: 'POST',
				async: true,
				beforeSend:function(){},
				complete  : function(){},
				success   : (data) => {
					if(data.result==0){ $(that.table).bootstrapTable('refreshOptions', {pageNumber:1}); }
					else{ showError(data.msg); }
				},
				error: function(xhr){ showError("<b>Error</b>: [<i><small>"+xhr.status+"</small></i>] "+xhr.statusText); }
			});
			
		});
		
		$('body').on('click', "#closeSendEmail", (e) => { $("#kamaLogEmailModal").modal("hide"); });
		$('body').on('click', '#sendEmaiSHOW', (e) => { this.showMenu_emailDialogHandler() });
		$('body').on('change select', "#showArchived", (e) => { this.changeArchive(e) });
		
		$('body').on('click', '.menu_email', (e) => { this.showMenuEmailDialog(e) });
	}
	//-----------------------------------------
	rowActions(value, row, index, field){
		let icons = this.actionIcons;
		//------------------------------------------------
		for(let i in icons){
			if( icons[i].attr('class')=='menu_archive' ){
				if(row.archived==1){ icons[i][0].dataset.desc="Unarchive"; }
				else{ icons[i][0].dataset.desc="Archive"; }
			}
		}
		this.actionIcons = icons;
		
		return super.rowActions(value, row, index, field);
	}
	//-----------------------------------------
	changeArchive(e){
		var target = e.currentTarget;
		var value = target.value;
		if(value=='NULL'){ value=1; }
		this.archive=value;
		
		$(this.table).bootstrapTable('refreshOptions', {pageNumber:1});
	}
	//-----------------------------------------
	showMenuEmailDialog(e){
		e.preventDefault();
		var tabindex = $(e.currentTarget).attr('data-itemid');
		let that = this;

		$.get(this.apiURL+'/chatlog/'+tabindex, (res) => {
			if(res.result == 1){ showError(res.msg); }
			else{
				that.viewData = res.usage;
				that.showMenu_emailDialogHandler();
			}
		});
	}
	//-----------------------------------------
	showMenu_emailDialogHandler(){
		let that = this;
		if(that.viewData.signin_id==null || that.viewData.signin_id==0){ showError('Message not found.'); return; }
		$('#kamaLogEmailModal')
			.modal({backdrop:"static"})
			.on('shown.bs.modal', function(){ 
				$("#emailTo").focus(); 
				$('#emailTo').val("");
				$('#emailSubject').val("Kama-DEI chat log, "+that.viewData.nickname+", "+that.viewData.timestamp);
				$('#emailInstructions').val("Hi"+"\n"+"This is a log from Kama-DEI.");

				$('#sendEmaiBTN').off('click');
				$('#sendEmaiBTN').on('click',
					function() {
						let emailTo           = $('#emailTo').val().trim();
						let emailSubject      = $('#emailSubject').val().trim();
						let emailInstructions = $('#emailInstructions').val().trim();
						if(!/\S+@\S+\.\S+/.test(emailTo)){ showError('Please enter valid the mail address.'); return }
						if(emailSubject==''){ showError('Please enter subject.'); return; }
						if(emailTo){
							$.ajax({
								url: that.apiURL+'/email',
								data: {
									log_id:that.viewData.signin_id,
									email:emailTo,
									subject:emailSubject,
									orgId:that.viewData.org_id,
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
		
			});
	}
	//-----------------------------------------
	showMenu_viewDialogHandler(e){
		var that = this;

		//--------------------------------------------------------
		e.preventDefault();
		var tabindex = $(e.currentTarget).attr('data-itemid');

		//that.selectedLog.id = tabindex;

		//$('#kamaLogModal').modal('show');
//		$('#kamaLogModal').css('display','block')
		//$('#kamaLogModal').css('overflow','auto')
		
		//--------------------------------------------------------
		var getheadtalbe=function(data){
			that.viewData = data;
			//that.selectedLog.username = data.nickname;
			//that.selectedLog.startTime = data.timestamp;
			//that.selectedLog.orgId = data.org_id;
			let table_str= ''+
				'<table width="100%" cellspacing="0" cellpadding="5" border="1" style="font-size:85%">\n' +
				'<tbody>\n' +
				'	<tr><td colspan="2" class=""><div >'+data.memo+'</div></td></tr>\n' +
//				'	<tr><td><b>IP</b></td><td>'+data.ip+'</td></tr>\n' +
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
										'<p>feedback: ' +
											(
												(data[i].feedback==1)
												? '<span class="fa fa-thumbs-o-up" style="color:green"></span>'
												: '<span class="fa fa-thumbs-o-down" style="color:black"></span>'
											)+
										'</p>'+
										(
											(data[i].is_general==1)
											?'<p>comment: '+data[i].comment+'</p>'
											:'<p>comment: '+data[i].comment+'</p>'
										)+
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
										'<p>feedback: ' +
											(
												(data[i].feedback==1)
												? '<span class="fa fa-thumbs-o-up" style="color:green"></span>'
												: '<span class="fa fa-thumbs-o-down" style="color:black"></span>'
											)+
										'</p>'+
									'</div>'+
								'</div>';
						}
					}
				}
			}
			return tmp_table1+'</div>';
		}
		//--------------------------------------------------------
/*
		$.each(this.rows, (i, item) => {
			if(item[this.columns.primaryColumn] == tabindex){
				this.editItem = item;
				$('#kamaLogModal .modal-body').html(getheadtalbe(item));
				return false;
			}
		});
*/
		//--------------------------------------------------------
		$.get(this.apiURL+'/chatlog/'+tabindex, (res) => {
			if(res.result == 1){ showError(res.msg); }
			else{
				$('#kamaLogModal').modal('show');
				$('#kamaLogModal .modal-body').html(getheadtalbe(res.usage));
				$('#kamaLogModal .modal-body')
					.html(
						$('#kamaLogModal .modal-body').html()+getneirongtable(res.data,res.usage.org_name,res.usage.memo)
					);
			}
		});
		//--------------------------------------------------------
	}
	//------------------------------------------------------------
	//-----------------------------------------
	get getURL() { 
		let cDate       = new Date();
		let myStartTime = $("#start_time").val().trim();
		if(myStartTime=="" || myStartTime==null){
			let sDate = new Date(cDate.setDate(cDate.getDate()-7));
			sDate.setMinutes(0);
			sDate.setHours(0);
			sDate.setSeconds(0);
			sDate.setMilliseconds(0);
			sDate = dataFormat(sDate,"yyyy-MM-dd hh:mm:ss");
			
			myStartTime = sDate;
		}
		myStartTime = myStartTime.replace(" ", "_").replace(/-/g, "_").replace(/:/g, "_");

		let myEndTime = $("#end_time"  ).val().trim().replace(" ", "_").replace(/-/g, "_").replace(/:/g, "_");
		if(myEndTime=="" || myEndTime==null){
			let eDate = dataFormat(cDate,"yyyy-MM-dd hh:mm:ss");
			myEndTime = eDate;
		}
		myEndTime = myEndTime.replace(" ", "_").replace(/-/g, "_").replace(/:/g, "_");
		
		let searc_email = $("#searc_email").val().trim();
		if(searc_email=='' || searc_email==null){ searc_email='0'; }
		
		let searc_org_id = $("#searc_org_id").val();
		if(searc_org_id==null){ searc_org_id=0; }

		return this.apiURL + 
			'/page/' 
			+ ((orgID) ?(orgID+'/') :'') 
			+ this.pageSort + '/' 
			+ this.pageOrder + '/' 
			+ this.pageSize 
			+ '/' 
			+ this.pageNumber + '/' 
			+ searc_email + '/'
			+ myStartTime + '/'
			+ myEndTime + '/'
			+ 'ownerId' + '/' 
			+ searc_org_id + '/' 
			+ 'showglobal' + '/' 
			+ 0 + '/'
			+ this.archive;
	}
	get searchURL() {
		let cDate       = new Date();
		let myStartTime = $("#start_time").val().trim();
		if(myStartTime=="" || myStartTime==null){
			let sDate = new Date(cDate.setDate(cDate.getDate()-7));
			sDate.setMinutes(0);
			sDate.setHours(0);
			sDate.setSeconds(0);
			sDate.setMilliseconds(0);
			sDate = dataFormat(sDate,"yyyy-MM-dd hh:mm:ss");
			
			myStartTime = sDate;
		}
		myStartTime = myStartTime.replace(" ", "_").replace(/-/g, "_").replace(/:/g, "_");

		let myEndTime = $("#end_time"  ).val().trim().replace(" ", "_").replace(/-/g, "_").replace(/:/g, "_");
		if(myEndTime=="" || myEndTime==null){
			let eDate = dataFormat(cDate,"yyyy-MM-dd hh:mm:ss");
			myEndTime = eDate;
		}
		myEndTime = myEndTime.replace(" ", "_").replace(/-/g, "_").replace(/:/g, "_");
		
		let searc_email = $("#searc_email").val().trim();
		if(searc_email=='' || searc_email==null){ searc_email='0'; }

		let searc_org_id = $("#searc_org_id").val();
		if(searc_org_id==null){ searc_org_id=0; }

		return this.apiURL + '/' 
			+ ((orgID) ? (orgID + '/') : '') 
			+ this.pageSort + '/'
			+ this.pageOrder + '/' 
			+ this.pageSize +'/' 
			+ this.pageNumber + '/' 
			+ searc_email + '/' 
			+ myStartTime + '/'
			+ myEndTime + '/'
			+ this.search + '/' 
			+ 'ownerId' + '/' 
			+ searc_org_id + '/' 
			+ 'showglobal' + '/'
			+ 0 + '/'
			+ this.archive;

	}
	//-----------------------------------------
	cellRenderer(value, row, index, field){
		let retVal = super.cellRenderer(value, row, index, field);
		if( field=='thumbs'){
			if(value>0){ return "<span class='fa fa-thumbs-o-up' style='color:green'></span>"; }
			else{ return "<span class='fa fa-thumbs-down' style='color: #adadad'></span>"; }
		}
		if( field=='archived'){
			if(value==0){ return '<span class="fa fa-minus"></span>'; }
			else{ return "<span class='fa fa-check'></span>"; }
		}
		return retVal;
	}
	//-----------------------------------------
	tableToolbar(){
		var cDate   = new Date();
		var eDate   = dataFormat(cDate,"yyyy-MM-dd hh:mm:ss");
		var sDate = new Date(cDate.setDate(cDate.getDate()-7));
		sDate.setMinutes(0);
		sDate.setHours(0);
		sDate.setSeconds(0);
		sDate.setMilliseconds(0);
		sDate = dataFormat(sDate,"yyyy-MM-dd hh:mm:ss");

		var toolbar = $("<div>").attr({
			id: 'tableToolbar'
		});
		let style = "";
		if(this.orgID!=0){ style="display:none;"}
		toolbar.append(
			$("<div>")
				.attr({ class: "searc_org_id where-group", style:style })
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
			'    "' +
			'    value="'+sDate+'">'
			)
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
			'    "' +
			'    value="'+eDate+'">'
			)
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
	//-----------------------------------------
	get org_allURL() { return this.apiURL + '/org_all' }
	getorg_all(){
		$.get(this.org_allURL, (res) => {
			res.data.unshift({id:0, name:'Owner All'});
			this.org_all = this.createSelectOptions(res.data, 'id', 'name');
			$("#searc_org_id").append(this.org_all);
		});
	}
	//-----------------------------------------
}
//---------------------------------------------

//---------------------------------------------
var columns = [
	{ name: 'feedback_id', display:'ID', primary: true, sortable:false,  editable:false, hidden:true },
	
	{ name: 'user_name' , display:'Consumer User', sortable:false, editable:false, search: true },
	{ name: 'org_name'  , display:'Organization' , sortable:false, editable:false, search: true },
	{ name: 'chat_date' , display:'Chat Date'    , sortable:true , editable:false, search: true },
	{ name: 'portalName', display:'Portal'       , sortable:true , editable:false, search: true },
	{ name: 'thumbs'    , display:'Thumbs'       , sortable:true , editable:false, search: false, reserved: true },
	{ name: 'comment'   , display:'Comment'      , sortable:true , editable:false, search: true },
	{ name: 'q_a_pair'  , display:'Q & A pair'   , sortable:false, editable:false, search: true , hidden:true },
	{ name: 'archived'  , display:'Archive'      , sortable:false, editable:false, search: false, reserved: true },
];

//---------------------------------------------
var data = {
	columns: new Columns(columns),
	apiURL: apiURL + '/api/feedback'
}
//---------------------------------------------
if($("#feedbackDIV").length != 0){
	//-----------------------------------------
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
	//-----------------------------------------
	var cDate   = new Date();
	var eDate   = cDate;
	eDate.setSeconds(59);
	eDate = dataFormat(eDate,"yyyy-MM-dd hh:mm:ss");

	var sDate = new Date(cDate.setDate(cDate.getDate()-7));
	
	sDate.setMinutes(0);
	sDate.setHours(0);
	sDate.setSeconds(0);
	sDate.setMilliseconds(0);
	sDate = dataFormat(sDate,"yyyy-MM-dd hh:mm:ss");

	data.curDate   = cDate;
	data.endDate   = eDate;
	data.startDate = sDate;
	//-----------------------------------------
	table = new Feedback(data);
	table.createTable('feedbackDIV');
	//-----------------------------------------
	$("#start_time").val(sDate).change();
	$("#end_time"  ).val(eDate).change();
	$(".form_datetime").datetimepicker({format: 'yyyy-mm-dd hh:ii:ss'});
	//-----------------------------------------
	//-----------------------------------------
}
//---------------------------------------------
