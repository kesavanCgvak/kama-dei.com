import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class PersonalityValue extends DataTable {
	//--------------------------------------------------------------
	constructor(data){
		super(data);
		this.showGlobal = false;
		this.orgOrgID = this.orgID;
		this.orgID = this.orgOrgID + '/' + '0'+ '/' + '0';
		this.pageSort = 'personTermName';
		this.terms = [];
		this.personality = [];
		this.values = [];
		this.getOwnerList();
		this.getAllPersonality();
		this.isCreatedSlider = 0;
		this.showRefresh = true;
		$('body').on('change', '#showGlobal', (e) => { 
			if($(this).prop('checked')==true){ 
				$(this).prop('checked', false);
				$("#ownerList").prop('disabled', false); 
				$("#ownerList").val(-1).change(); 
			}else{ 
				$(this).prop('checked', true);
				$("#ownerList").prop('disabled', true); 
				$("#ownerList").val(orgID).change(); 
			}
		});
		var deleteIcon = $("<a></a>").attr({
			href: '#',
			style: "color:#f3ae4e;",
			class: 'delete-item',
			'data-desc': 'Reset',
			'data-onlyowner': 1
		});
		this.actionIcons = [deleteIcon];
		$('body').on('blur', '#personalityListSrch', (e) => { 
			setPersonality();
		});
		$('body').on('keypress', '#personalityListSrch', (e) => { 
			if(e.keyCode==13){ setPersonality(); }
		});
	}
	//--------------------------------------------------------------
	refreshOptions(){
		this.orgID = this.orgOrgID + '/' + $('#personalityList').val()+ '/' + $('#ownerList').val();
		var url = (this.search == '')? this.getURL: this.searchURL;
		$(this.table).bootstrapTable('refreshOptions', {url: url});
	}
	//--------------------------------------------------------------
	createPersonalityList(obj){
		$(obj)
			.prepend(
				'<span class="all">'+
					'<label>Owner</label>'+
					'<select id="ownerList" namd="ownerList" class="form-control" onchange="setPersonality()"/>'+
				'</span>'+
				'<hr style="margin:10px 50px; border:1px dotted #eee"/>'+
				'<div>'+
					'<div class="left" style="display:inline-block; width:30%; vertical-align:top;">'+
						'<ul class="personalityFilter">'+
/*
							'<li class="personalityFilter0">'+
								'<input type="radio" id="personalityFilter0" name="personalityFilter" onchange="personalityCheck(0)" checked="checked" />'+
								'&nbsp;<label for="personalityFilter0">All</label>'+
							'</li>'+
*/
							'<li class="personalityFilter0">'+
								'<input type="radio" id="personalityFilter1" name="personalityFilter" onchange="personalityCheck(1)" checked="checked" />'+
								'&nbsp;<label for="personalityFilter1">Persona</label>'+
							'</li>'+
							'<li class="personalityFilter0">'+
								'<input type="radio" id="personalityFilter2" name="personalityFilter" onchange="personalityCheck(2)" />'+
								'&nbsp;<label for="personalityFilter2">Personality</label>'+
							'</li>'+
						'</ul>'+
					'</div>'+
					'<div class="" style="display:inline-block; width:70%;">'+
						'<div class="" style="height:34px; margin-bottom:5px" >'+
							'<div class="" style="float:right;width:calc(300px - 90px)">'+
								'<input id="personalityListSrch" class="form-control" placeholder="Search Persona" />'+
							'</div>'+
						'</div>'+
						'<div class="" style="height:34px; margin-bottom:5px" >'+
							'<div class="" style="width:300px; float:right">'+
	//							'<label for="personalityList">Personality or Persona</label>'+
								'<label for="personalityList" style="width: 90px;">Persona</label>'+
								'<select id="personalityList" class="form-control" onchange="selectPersonality()"/>'+
							'</div>'+
						'</div>'+
						'<div class="" style="height:34px; margin-bottom:5px" >'+
							'<div class="" style="float:right;width:calc(300px - 90px)">'+
								'<span id="personalityListTotal" style="display:block; font-size:small">'+
									'<i class="fa fa-refresh fa-spin"></i>'+
								'</span>'+
								'<span id="personalityListMsg" style="display:block; font-size:small"></span>'+
							'</div>'+
						'</div>'+
					'</div>'+
				'</div>'+
				'<hr style="margin:5px 50px; border:1px dotted #eee"/>'+
				'<div id="valuesFor">Values for <span></span></div>'+
				'<hr style="margin:10px 50px; border:1px dotted #eee"/>'
			);
	}
	//--------------------------------------------------------------
	getOwnerList() {
		$.get(apiURL+'/api/dashboard/organization/all/'+(-1*orgID), (obj) => {
			var tempOptions = [];
			for(var i=0; i< obj.data.length; i++){ 
				if(obj.data[i].organizationId==0){ 
					tempOptions.unshift("<option value='"+obj.data[i].organizationId+"'>"+obj.data[i].organizationShortName+"</option>"); 
				}else{ tempOptions.push("<option value='"+obj.data[i].organizationId+"'>"+obj.data[i].organizationShortName+"</option>"); }
			}
		
			this.terms = tempOptions;
			$("select#ownerList").append(this.terms);
			$("select#ownerList").prepend("<option value='-1' selected='selected'>All</option>");

			$("select#ownerList").val(orgID);
		});
	}
	//--------------------------------------------------------------
	getAllValues(){
		var prsID = $("#personalityList").val();
		return apiURL+'/api/dashboard/term/values/'+orgID+'/'+prsID+'/termName/asc';
	}
	//--------------------------------------------------------------
	getAllPersonality(){
		$("span#personalityListTotal").html("<i class='fa fa-refresh fa-spin'></i>");
		$("span#personalityListMsg").html("");
//		$.get(apiURL+'/api/dashboard/personality/allPersonality/'+ orgID +'/-1/personalityName/asc', (obj) => {
		$.get(apiURL+'/api/dashboard/personality/zeroPersonality/'+ orgID +'/-1/personalityName/asc/', (obj) => {
			var tempOptions = [];
			for(var i=0; i< obj.data.length; i++){ 
				let tmpOwner = (obj.data[i].ownerId==null) ?0 :obj.data[i].ownerId;
				let fullText = "";
				let showText = "";
				if(obj.data[i].parentPersonaId!=0){
					var email = "";
					if(obj.data[i].get_consumer_user!=null ){
						if(obj.data[i].get_consumer_user.email==null){ email=''; }
						else{ email = " | " + obj.data[i].get_consumer_user.email; }
					}
					fullText = obj.data[i].personalityName + ' | '+ obj.data[i].parent_persona.personalityName + email;
					showText = fullText;
					if(showText.length>50){ showText = showText.substr(0, 47)+"..."; }
				}
				else{ 
					fullText = obj.data[i].personalityName;
					showText = fullText;
					if(showText.length>50){ showText = showText.substr(0, 47)+"..."; }
				}
				tempOptions.push(
					"<option "+
							"data-owner='"+tmpOwner+"' "+
							"data-parent='"+obj.data[i].parentPersonaId+"' "+
							"value='"+obj.data[i].personalityId+"' "+
							"data-full='"+ fullText +"'"+
					">"+
						showText+
					"</option>"
				);  
			}
		
			this.personality = tempOptions;
			$("select#personalityId").append(this.personality);
			$("select#personalityList").append(this.personality);
//			$("select#personalityList").prepend($("<option value='0' selected='selected'>Select Personality</option>"));
			$("select#personalityList").prepend($("<option value='0' selected='selected'>Select Persona</option>"));
			$("span#personalityListTotal").html("Records: "+obj.total);
			if(obj.total>obj.limit){
				$("span#personalityListMsg").html("Displaying the first "+obj.limit+" records.");
			}
		});
	}
	//--------------------------------------------------------------
	showEditDialogHandler(e){
		return;
	}
	showAddDialogHandler(){
	    super.showAddDialogHandler();
		$("#personTermId").prop('disabled', true);
		$("#personTermId_TXT").val("");
		$("#personTermId").val("").change();
		$("#personTermIdTable").bootstrapTable("refresh", {url:this.getAllValues()});
		$("#personTermIdTable").bootstrapTable("selectPage", 1);
		$("#personTermIdTable").bootstrapTable("resetSearch", "");
		$("#personTermId_WRP").hide();
		$(".col-personTermId .fixed-table-container").css("height", "220px")
		if( this.isCreatedSlider==0){
			$("#slider-scalarValue").slider({
				max  : 10,
				value:  0,
				min  : 0,
				create: function() { $("#custom-handle").text( $(this).slider( "value" ) ); $("#scalarValue").val($(this).slider( "value" )).change(); },
				slide: function( event, ui ){ $("#custom-handle").text( ui.value ); $("#scalarValue").val(ui.value).change(); }
			});
			this.isCreatedSlider=1;
		}else{
			$( "#slider-scalarValue" ).slider( "option", "value", 0 );
			$("#custom-handle").text( 0 );
			$("#scalarValue").val( 0 ).change();
		}
		$(".col-personalityId #personalityId").val( $("#personalityList").val() ).change();
		let personaCAP = "Personality";
		if( $("#personalityList option:selected").data("parent")==0){ personaCAP = "Persona"; }
		let personaTXT = $("#personalityList option:selected").text();
		$("#header")
			.html("<p style='color:#000'>Current "+personaCAP+":</p><span style='color:yellow;margin-left:20px'>"+personaTXT+"</span>");
		if(orgID==0){ $("#ownership0").click(); }
		else{ $("#ownership2").click(); }
		$(".col-ownership").hide();
		$(".col-ownerId>label").text("Owner");
		$(".col-ownerId").css("width", "100%");
		$("select#ownerId").val($("select#personalityList option:selected").data("owner")).change();
		$("select#ownerId").prop('disabled', true);
	}
	getActionFormInput(col, label){
		var input = '';
		switch (col){
			case 'personTermId':{
				input = $("<div>")
					.attr({	class: "col-" + col + " form-group"})
					.append( $("<input>").attr({ 
						id: col+"_TXT", 
						class: 'form-control', 
						disabled:"", 
						style:"max-width:92%; display:inline-block;"
					}) )
					.append( 
							$("<button>").attr({
											id:col+"_btn", 
											class:'btn btn-default', 
											type:"button",
											onclick:"$('#"+col+"_WRP').slideToggle('slow')",
											style:"height:34px; vertical-align:top; float:right;padding:5px 8px"
								})
								.append("<i class='fa fa-list' style='font-size:20px;'></i>")
						   )
					.append( $("<input>").attr({ id: col, name: col, type: 'hidden' }) )
					.append(
						$('<div>')
							.attr({ id:col+"_WRP", style:"margin:10px 0;display:none" })
							.append('<div id="'+col+'Table"></div>')
					);
				break;
			}
			case 'personalityId':{
				input = $("<div>")
					.attr({	class: "col-" + col + " form-group"})
					.append( $("<label>"+label+"</label>") )
					.append( $("<div>")
							   .append( $("<select>").attr({ id: col, name: col, class: 'form-control' }) )
					);
				break;
			}
			case 'scalarValue':{
				input = super.getActionFormInput(col, label);
				$(input)
					.append("<div id='slider-"+ col +"'><div id='custom-handle' class='ui-slider-handle'></div></div>")
					.append("<div class='sliderTopValue'><span id='min'>0</span><span id='center'>5</span><span id='max'>10</span></div>");
				
				break;
			}
			default:
				input = super.getActionFormInput(col, label);
		}
		return input;
	}
	//--------------------------------------------------------------
	cellRenderer(value, row, index, field){
		var column = null;
		
		for(var x in this.columns.names){
			if(this.columns.names[x] == field){
				column = this.columns.data[x];
				break;
			}
		}
		
		if(column.reserved === true) return this.checkCell(value, row, column);
		if(column.ownership === true) return this.ownershipCell(value, row, column);
		if(column.date === true) return this.dateCell(value, row, column);
		
		if(column.showScaler === true){
			var retVal = 
				"<div class='sliderHolder'>" +
					"<div class='sliderTopValueOnTable'>"+
						"<span id='min'>0</span><span id='center'>5</span><span id='max'>10</span>"+
					"</div>" +
					"<div class='slider-onTable' data='"+value+"' data-id='"+row.personalityValueId+"' data-add='"+row.isParent+"'>"+
						"<div class='ui-slider-handle'>"+value + "</div>"+
					"</div>" +
				"</div>" ;
			return retVal;
		}
		if(field=='curScalarValue'){
			return row.scalarValue;
		}
		return value;
	}
	//--------------------------------------------------------------
	rowActions(value, row, index, field) {
		//------------------------------------------------------------------------
		var icons = this.actionIcons;
		var tmpICN = [];
		//------------------------------------------------------------------------
		if(row.isParent==0){
			for (var i in icons){
				if( icons[i].attr('class')!='edit-item' ){ tmpICN.push(icons[i]); }
			}
		}
		this.actionIcons = tmpICN;
		//------------------------------------------------------------------------
		let tmpRowAction = super.rowActions(value, row, index, field);
		this.actionIcons = icons;
		return tmpRowAction;
		//------------------------------------------------------------------------
	}
	//--------------------------------------------------------------
	actionForm() {
		var submitLabel = 'Save Item';
		var submitId = 'saveItem';

		var formChildren = [];
		var columns = this.columns;
		var data = columns.data;
		for(var x in data) {
			var column = data[x];
			if(column.editable === false || column.primary === true || column.reserved === true) continue;
			var col = column.name;
			var label = column.display;
			if(column.onlyFor == null || column.onlyFor == orgID){
				formChildren.push(this.getActionFormInput(col, label));
			}
		}

		if(columns.reservedColumn !== null) {
			formChildren.push(this.getActionFormInput(columns.reservedColumn, 'Reserved'));
		}

		var wrapper = $("<div>").attr({ id: 'editItem' });
		var form = $("<form>").attr({ class: 'action-form', onSubmit:"return false;" });
		var header = $("<div style='background:#00a6b4;height:70px;margin:-15px -15px 15px;padding:15px;'>").attr({ id: 'header' });
		$(form).append(header);
		
		$(formChildren).each(function(i, el){
			form = $(form).append(el);
		});

		var hint = $("<div><b style='color:red;font-size:18px;vertical-align:bottom'>*</b>: Editing this field<small>(s)</small> requires special password.</div>")
						.attr({style:"font-size:12px;padding:8px 0 15px;"});
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
										class: 'btn btn-danger',
										onClick: "$('#editItem').fadeOut()"
									})
						);

		form = $(form).append([submit, cancel]);
		wrapper = $(wrapper).append([form]);

		return wrapper;
	}
	//--------------------------------------------------------------
	tmpFormatter(value, row, index, field){
		if(field=='ownership'){ return table.ownershipCell(value, row, field); }
		if(field=='organizationShortName'){
			if(row.ownerId!=null && row.ownerId!=0){ return row.organization.organizationShortName; }
			else{ return BASE_ORGANIZATION; }
		}
	}
	//--------------------------------------------------------------
	checkCell(cell, row, column) {
		if(cell == '1'){
			return "<span class='glyphicon glyphicon-ok' style='color:green'></span>";
		}else{
			return "<span></span>";
		}
	}
	//--------------------------------------------------------------
}

var columns = [
  { name: 'personalityValueId', display: 'ID', primary: true, sortable: true, width: '8%', hidden: true },

  { name: 'personalityId'  , display: 'Personality', hidden:true },

  { name: 'personTermId'  , display: 'Value', hidden:true },
  { name: 'personTermName', display: 'Value', sortable: true,editable: false, search:false },

  { name: 'scalarValue'   , display: 'Scalar Value', sortable: false, search: false, editable: true, showScaler: true, width: '200px' },
  { name: 'curScalarValue', display: 'Value'       , sortable: false, search: false, editable: false, hidden: true  },

  { name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true, width: '100px' },
  { name: 'ownerId', display: '', onlyFor: 0, hidden: true},
  { name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false, hidden:true },
  { name: 'personalized', display: 'Personalized', sortable: true, editable: false, reserved:true },
  { name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
  { name: 'lastUserId', hidden: true, editable: false, default: '1' },

  { name: 'isParent', display: 'isParent', sortable: false, editable: false, hidden:true },
];
var personalityValuesColumns = new Columns(columns);

var data = {
  columns: personalityValuesColumns,
  apiURL: apiURL + '/api/dashboard/personality_value'
}

if($("#personalityValues").length != 0){
	var tblDIV = $('<div id="personalityValuesTable"/>');
	var lstDIV = $('<div id="personalityValuesList"/>');
	table = new PersonalityValue(data);

	table.createPersonalityList(lstDIV);
	$('#personalityValues').html(tblDIV);
	table.createTable('personalityValuesTable');

	$('#personalityValues').prepend(lstDIV);
	
	$("#personTermIdTable").bootstrapTable({
		columns: [
			{sortable:true , searchable:true , title:'Value', field:'termName' , width:'50%', align:'left !important' },
			{sortable:false, searchable:false, title:'Ownership', field:'ownership'            , width:'15%', align:'center !important', formatter:table.tmpFormatter },
			{sortable:false, searchable:false, title:'Owner'    , field:'organizationShortName', width:'34%', align:'left   !important', formatter:table.tmpFormatter },
			{sortable:false, searchable:false , title:'' , field:'termId', visible:false },
		],
		url         : '',
		showRefresh : true,
		search      : true,
		pagination  : true,
		pageSize:5,
		pageList:[5],
		sortName: 'termName',
		sortOrder: 'asc',
		rowStyle: function(row, index){ 
			if(row.termId==$("#personTermId").val()){ 
				return { css:{ color:'red' } };
			}
			return { css:{ color:'#000' } };
		},
	})
	.on('click-row.bs.table', function(e, row, a, b){
		$("#personTermId_TXT").val(row.termName);
		$("#personTermId").val(row.termId).change();
		$('#personTermIdTable td').css('color', '#000');
		$(a[0]).find('td').css('color', 'red');
		$("#personTermId_WRP").slideUp("slow");
	});
}
