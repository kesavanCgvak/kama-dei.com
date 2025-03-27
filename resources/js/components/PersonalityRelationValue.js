import { DataTable, showError, showSuccess, showConfirm } from './DataTable'
import Columns from './Columns'
import Slider from 'jquery-ui-bundle'
import 'jquery-ui-bundle/jquery-ui.min.css'

class PersonalityRelationValue extends DataTable {
	//--------------------------------------------------------------
	constructor(data){
		super(data);
//		this.showGlobal = true;
		this.orgOrgID = this.orgID;
		this.orgID = this.orgOrgID + '/' + '0'+ '/' + '0';
		this.pageSort = 'personTermName';
		this.terms = [];
		this.personality = [];
//		this.getAllTerms();
		this.getOwnerList();
		this.getAllPersonality();
		this.isCreatedSlider = 0;
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
		$('body').on('blur', '#personalityListSrch', (e) => { 
			setPersonality();
		});
		$('body').on('keypress', '#personalityListSrch', (e) => { 
			if(e.keyCode==13){ setPersonality(); }
		});
		
/*
		var resetIcon = $("<a></a>").attr({
			href: '#',
			style: "color:#f3ae4e;",
			class: 'delete-item',
			'data-desc': 'Reset',
			'data-onlyowner': 1
		});

		var deleteIcon = $("<a></a>").attr({
			href: '#',
			style: "color:#f3ae4e;",
			class: 'delete-item',
			'data-desc': 'Delete',
			'data-onlyowner': 1
		});
		this.actionIcons = [resetIcon, deleteIcon];
*/
		if( levelID==1 && orgID==0){
			var icon1 = $('<a></a>').attr({ href: '#', 'data-desc': 'Copy', class: 'copy-item', 'data-onlyowner': 1 });
			this.actionIcons = this.actionIcons.concat([icon1]);
		}
	}
	//--------------------------------------------------------------
	showError(msg){ showError(msg); }
	//--------------------------------------------------------------
	showSuccess(msg){ showSuccess(msg); }
	//--------------------------------------------------------------
	showConfirm(callback, msg, yes='Yes', no='No'){ showConfirm(callback, msg, yes, no); }
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
				'<div class="all" style="text-align:left;">'+
					'<label for="ownerList">Owner</label>'+
					'<select id="ownerList" namd="ownerList" class="form-control" onchange="setPersonality()"/>'+
				'</div>'+
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
							'<div class="" style="float:right;width:calc(500px - 90px)">'+
								'<input id="personalityListSrch" class="form-control" placeholder="Search Persona" />'+
							'</div>'+
						'</div>'+
						'<div class="" style="height:34px; margin-bottom:5px" >'+
							'<div class="" style="width:500px; float:right">'+
	//							'<label for="personalityList">Personality or Persona</label>'+
								'<label for="personalityList" style="width: 90px;">Persona</label>'+
								'<select id="personalityList" class="form-control" onchange="selectPersonality()"/>'+
							'</div>'+
						'</div>'+
						'<div class="" style="height:34px; margin-bottom:5px" >'+
							'<div class="" style="float:right;width:calc(500px - 90px)">'+
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
				tempOptions.push("<option value='"+obj.data[i].organizationId+"'>"+obj.data[i].organizationShortName+"</option>");
			}
		
			this.terms = tempOptions;
			$("select#ownerList").append(this.terms);
			$("select#ownerList").prepend("<option value='-1' selected='selected'>All</option>");

			$("select#ownerList").val(orgID);
		});
	}
	//--------------------------------------------------------------
	getAllTerms(){
		var prsID = $("#personalityList").val();
		$("select#personTermId>option").remove();
		$.get(apiURL+'/api/dashboard/term/values/'+orgID+'/'+prsID+'/termName/asc', (obj) => {
			var tempOptions = [];
			for(var i=0; i< obj.data.length; i++){ tempOptions.push("<option value='"+obj.data[i].termId+"'>"+obj.data[i].termName+"</option>"); }
		
			this.terms = tempOptions;
			$("select#personTermId").append(this.terms);
		});
	}
	//--------------------------------------------------------------
	getAllPersonality(){
		$("span#personalityListTotal").html("<i class='fa fa-refresh fa-spin'></i>");
		$("span#personalityListMsg").html("");
//		$.get(apiURL+'/api/dashboard/personality/allPersonality/'+ orgID +'/-1/personalityName/asc', (obj) => {

//		let tmpOrgID=((orgID!=0) ?orgID :-1);
		let tmpOrgID=((orgID!=0) ?orgID :0);
		
		$.get(apiURL+'/api/dashboard/personality/zeroPersonality/'+orgID+'/'+tmpOrgID+'/personalityName/asc/', (obj) => {
			
			var tempOptions = [];
//			for(var i=0; i< obj.data.length; i++){ tempOptions.push("<option value='"+obj.data[i].personalityId+"'>"+obj.data[i].personalityName+"</option>"); }
			for(var i=0; i< obj.data.length; i++){
				if(obj.data[i].parentPersonaId!=0){
					var email = "";
					if(obj.data[i].get_consumer_user!=null ){
						if(obj.data[i].get_consumer_user.email==null){ email=''; }
						else{ email = " | " + obj.data[i].get_consumer_user.email; }
					}
					let fullText = obj.data[i].personalityName + ' | '+ obj.data[i].parent_persona.personalityName + email;
					let showText = fullText;
					if(showText.length>50){ showText = showText.substr(0, 47)+"..."; }
					tempOptions.push(
								"<option "+
									"data-parentID='"+obj.data[i].parentPersonaId+"' "+
									"data-owner='"+obj.data[i].ownerId+"' "+
									"value='"+obj.data[i].personalityId+"' "+
									"data-full='"+ fullText +"' "+
									"data-parentname='"+obj.data[i].parent_persona.personalityName+"' "+
									"data-personaname='"+obj.data[i].personalityName+"' "+
									"data-portalname='"+obj.data[i].portalname+"' "+
								">"+
									showText +
								"</option>"
					);
				}
				else{ 
					let fullText = obj.data[i].personalityName;
					let showText = fullText;
					if(showText.length>50){ showText = showText.substr(0, 47)+"..."; }
					tempOptions.push(
						"<option data-parentID='"+obj.data[i].parentPersonaId+"' data-owner='"+obj.data[i].ownerId+"' value='"+
							obj.data[i].personalityId+"' data-full='"+ fullText +"'>"+
							showText +
						"</option>"
					);  
				}
			}
		
			this.personality = tempOptions;
			$("select#personalityId").append(this.personality);
			$("select#personalityList").append(this.personality);
			$("select#personalityList").prepend($("<option value='0' selected='selected'>Select Persona</option>"));
//			$("select#personalityList").prepend($("<option value='0' selected='selected'>Select Personality or Persona</option>"));
			$("span#personalityListTotal").html("Records: "+obj.total);
			if(obj.total>obj.limit){
				$("span#personalityListMsg").html("Displaying the first "+obj.limit+" records.");
			}
		});
	}
	//--------------------------------------------------------------
	showEditDialogHandler(e){
		return;
	    super.showEditDialogHandler(e);
		if( this.isCreatedSlider==0){
			$("#slider-scalarValue").slider({
				max   : 10,
				value : $("#scalarValue").val(),
				min   : 0,
				create: function() { $("#custom-handle").text( $(this).slider( "value" ) ); $("#scalarValue").val($(this).slider( "value" )).change(); },
				slide: function( event, ui ){ $("#custom-handle").text( ui.value ); $("#scalarValue").val(ui.value).change(); }
			});
			this.isCreatedSlider=1;
		}else{
			$( "#slider-scalarValue" ).slider( "option", "value", $("#scalarValue").val() );
			$("#custom-handle").text( $("#scalarValue").val() );
			$("#scalarValue").val( $("#scalarValue").val() ).change();
		}
	}
	showAddDialogHandler(){
	    super.showAddDialogHandler();
		$("#personTermId").prop('disabled', true);
		this.getAllTerms();
		$("#personTermId").prop('disabled', false);
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
	}
	getActionFormInput(col, label){
		var input = '';
		switch (col){
			case 'personTermId':
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
					.append("<div class='sliderTopValue'><span id='min'>0</span><span id='center'>5</span><span id='max'>10</span></div>")
					.append("<div id='slider-"+ col +"'><div id='custom-handle' class='ui-slider-handle'></div></div>");
				
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
				"<div class='sliderTopValueOnTable'><span id='min'>0</span><span id='center'>5</span><span id='max'>10</span></div>" +
				"<div class='slider-onTable' data='" + value + "' data-id='"+row.personalityValueId+"'><div class='ui-slider-handle'>" + value + "</div></div>" +
				"</div>" ;
			return retVal;
//				"<div>"+ 12 + "</div>";
		}
		if(field=='curScalarValue'){
			return row.scalarValue;
		}
		return value;
	}
	//--------------------------------------------------------------
	//--------------------------------------------------------------
	rowActions(value, row, index, field) {
		//----------------------------------------------------------
		var icons = this.actionIcons;
		var tmpICN = [];
		//----------------------------------------------------------
		for (var i in icons){
			if( icons[i].attr('class')=='delete-item' ){ icons[i].attr('data-desc', 'Reset') }
			if( icons[i].attr('class')!='edit-item' ){ tmpICN.push(icons[i]); }
		}
		this.actionIcons = tmpICN;
		//----------------------------------------------------------
		return super.rowActions(value, row, index, field);
		//----------------------------------------------------------
	}
	//--------------------------------------------------------------
	rowActions1(value, row, index, field) {
		//------------------------------------------------------------------------
		var icons = this.actionIcons;
		var tmpICN = [];
		//------------------------------------------------------------------------
		for (var i in icons){
			if( icons[i].attr('class')=='delete-item' ){ icons[i].attr('data-desc', 'Reset') }
			if( icons[i].attr('class')!='edit-item' ){ tmpICN.push(icons[i]); }
		}
		this.actionIcons = tmpICN;
		//------------------------------------------------------------------------
		return super.rowActions(value, row, index, field);
		//------------------------------------------------------------------------
		if(row.ownerId == 0 && this.orgID != 0) return;
		var rowAction = '<div class="row-actions"></div>';
		var deleteIcon = $("<a><small class='glyphicon glyphicon-trash'></small></a>").attr({
			href: '#',
			style: "color:#f3ae4e",
			class: 'delete-item',
			'data-itemid': row[this.columns.primaryColumn]
		});
		/*
		var editIcon = $('<a><small class="glyphicon glyphicon-pencil"></small></a>').attr({
			href: '#',
			class: 'edit-item',
			'data-itemid': row[this.columns.primaryColumn]
		});
		return $(rowAction).append(deleteIcon).append(editIcon)[0].outerHTML;
		*/
		return $(rowAction).append(deleteIcon)[0].outerHTML;
	}
	//--------------------------------------------------------------
}

var columns = [
  { name: 'personalityValueId', display: 'ID', primary: true, sortable: true, width: '8%', hidden: true },

  { name: 'personalityId'  , display: 'Personality', hidden:true },
//  { name: 'personalityName', display: 'Personality', sortable: true,editable: false, search:false },

  { name: 'personTermId'  , display: 'Value', hidden:true },
  { name: 'personTermName', display: 'Value', sortable: true,editable: false, search:false },

  { name: 'scalarValue'   , display: 'Scalar Value', sortable: false, search: false, editable: true, showScaler: true, width: '200px' },
  { name: 'curScalarValue', display: 'Value'       , sortable: false, search: false, editable: false, hidden: true  },

  { name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true, width: '100px' },
  { name: 'ownerId', display: '', onlyFor: 0, hidden: true},
  { name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },
  { name: 'dateCreated', display: 'Created', sortable: true, editable: false, date: true },
  { name: 'lastUserId', hidden: true, editable: false, default: '1' },
];
var personalityValuesColumns = new Columns(columns);

var data = {
  columns: personalityValuesColumns,
  apiURL: apiURL + '/api/dashboard/personality_value'
}

if($("#personalityRelationValue").length != 0){
	var tblDIV = $('<div id="personalityValuesTable"/>');
	var lstDIV = $('<div id="personalityValuesList"/>');
	myClass = new PersonalityRelationValue(data);

	myClass.createPersonalityList(lstDIV);
	$('#personalityRelationValue').html(tblDIV);
	myClass.createTable('personalityRelationValueTable');

	$('#personalityRelationValue').prepend(lstDIV);
}
