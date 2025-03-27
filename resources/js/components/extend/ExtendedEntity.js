import { DataTable, showError, showSuccess } from '../extend/DataTable'
import Columns from '../extend/Columns'


class ExtendedEntity extends DataTable {
	//-----------------------------------------------------------
	constructor(data){
		super(data);
		this.showGlobal = true;
//		this.pageSort = 'extendedEntityId';
		this.pageSort = 'review_by';
		this.attributetypeID=0;
		this.subtypeID=0;
		//this.orgID=0;
		this.orgID  = (typeof(orgID ) != 'undefined')? orgID :null;
		this.getExtendedTypes();
		
		this.lang = 'en';

		var icon1 = $('<a></a>').attr({ href: '#', 'data-desc': 'Value', class: 'eav-item', 'data-onlyowner': 1 },);
		// var icon2 = $('<a></a>').attr({ href: '#', 'data-desc': 'Promote', class: 'promote', 'data-onlyowner': 1 },);
		// var icon3 = $('<a></a>').attr({ href: '#', 'data-desc': 'Demote', class: 'demote', 'data-onlyowner': 1 },);
		this.actionIcons = this.actionIcons.concat([icon1]);


		this.saveOnlyClick = false;
		
		//$('body').on('change', '#certify', (e) => { this.changeCertify(e) });
		$('body').on('change', '#result', (e) => { this.changeResult(e) });
		$('body').on('click', '#saveDraft', (e) => { this.saveDraft(e) });
		$('body').on('click', '.eav-item', (e) => { this.showEavDialogHandler(e) });
		$('body').on('click', '.notes-item', (e) => { this.showNotesDialogHandler(e) });
		
		$('body').on('click', "#saveOnly", (e) => { this.saveOnlyClick = true ; this.editItemConfirmHandler(e) });
		$('body').on('click', "#saveAttr", (e) => { this.saveOnlyClick = false; this.editItemConfirmHandler(e) });
		$('body').on('click', ".delete-item", (e) => { this.showOrgDeleteBox(e) });
		$('body').on('click', '#Delete_Attrbutes_extendedData', (e) => { this.showDelete_Attrbutes_extendedData(e) });
		$('body').on('click', '#draft', (e) => { this.showDraftModal(e) });
		$('body').on('click', '#searchBTN', (e) => { this.draftSearch(e) });
		
		//$('body').on('change', '#prevEdit', (e) => { this.showPrevEditResult(e) });
		$('body').on('click', '#showEditBtn', (e) => { this.showPrevEditResult(0) });
		$('body').on('click', '#showPrevBtn', (e) => { this.showPrevEditResult(1) });

		// $('body').on('click', '.promote', (e) => { this.showorderup(e) });
		// $('body').on('click', '.demote', (e) => { this.showorderdown(e) });
		$('body').on('change', '#ownerId', (e)=>{
			if($("#ownerId").val()==0){
				$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"0").click();
				$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
			}else{
				$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
				$("#"+this.columns.ownershipColumn+"2").click();
				$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
			}
		});

		let that = this;
/*
		$('body').on('input', '#editEavItem textarea', (e)=>{
			let len = 1000 - $("#editEavItem textarea").val().length;
			len = (len<0) ?0 :len;

			$("#editEavItem .charactersLeft>span").html(len);
		});
*/
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
	}
	//-----------------------------------------------------------
	showNotesDialogHandler(e){
		this.extendedEntityId = $(e.currentTarget).data('itemid');
		
		$("#notesRecords").bootstrapTable('refresh',{url:this.getAllNotes});
		$("#notesModal").modal({backdrop:"static"});
	}
	//-----------------------------------------------------------
	showEavDialogHandler(e){
		$("#editEavItem>form").prepend('<i id="waitspin" class="fa fa-spin fa-spinner" style="float:right;margin-left:-20px"></i>');
		this.lang = 'en';
		e.preventDefault();
		this.extendedEntityId=$(e.currentTarget).data('itemid');
		//-------------------------------------------------------
		$("#editEavItem").fadeIn();
		$("#extendedEntityId option[value$='"+this.extendedEntityId+"']").attr("selected",true);
		let lang = this.lang;
		$.get(this.ExtendedDataViewsURL+"/"+lang, (res) => {
			this.editItem=res.data;
			//---------------------------------------------------
/*
			attributeTypeName
			text
			text_url
*/
			//---------------------------------------------------
			$.each(this.rows, (i, item) => {
				if(item[this.columns.primaryColumn] == this.extendedEntityId){
					this.editItem_entity = item;
					return false;
				}
			});
			//---------------------------------------------------
			this.createAttrs(res.data, res.responsiblity, res.RAG);
			$("#ownership_entity0").parent().attr('disabled', true);
			$("#ownership_entity1").parent().attr('disabled', true);
			$("#ownership_entity2").parent().attr('disabled', true);
			$("#ownership_entity"+this.editItem_entity.ownership).click();
			$("#ownership_entity"+this.editItem_entity.ownership).parent().attr('disabled', false);
			//---------------------------------------------------
			$("#draft").hide();
			$("#certifyA").parent().hide();
//			if(this.editItem[0].has_draft==1){ $("#draft").show(); }
//			if(this.editItem[0].storageType.toLowerCase()=='text' && res.RAG==1){
			if((this.editItem[0].storageType.toLowerCase()=='text' || this.editItem[0].storageType.toLowerCase()=='text-url') && res.RAG==1){
				$("#draft").show();
				$("#certifyA").parent().show();
			}
			//---------------------------------------------------
			$("#ownerId_entity").prop('disabled', true);
//			$.get(this.organizationURL, (res) => {
			$.get(apiURL+"/api/dashboard/organization/all/"+this.editItem_entity.ownerId, (res) => {
				
				this.organizations = this.createSelectOptions(res.data, 'organizationId', 'organizationShortName');
				$("#ownerId_entity").append(this.organizations);

				for(var tt=0 ;tt< $("#ownerId_entity").children().length;tt++){
					if($("#ownerId_entity").children()[tt].value==this.editItem_entity.ownerId){
						$($("#ownerId_entity").children()[tt]).attr("selected",true);
					}
				}
				//$("#ownerId_entity").value =this.editItem_entity.organizationShortName;
			});

			$("#lang_entity_div .lngBTN").remove();
			$("#lang_entity option").remove();
			$.get("/api/dashboard/organization/get/language/"+this.editItem_entity.ownerId, (res) => {
				if(res.result==0){
					for(let i in res.data){
						if(res.data[i].isActive==1 ||res.data[i].code=='en'){
							let op = res.data[i];
							$("#lang_entity").append('<option value="'+op.code+'">'+op.name+'</option>');
							$("#lang_entity_div")
								.append(
									$("<button>"+op.name+"</button>")
										.attr({
											type   : 'button',
											class  : 'btn btn-default lngBTN '+op.code,
											value  : op.code,
											style  : ((i==0) ?'border-radius: 4px 0 0 4px;' :'')
											//onclick: this.clickLngBtn(op.code)
										})
								);
						}
						$(".lngBTN").on('click', function(){
							let lng = $(this).val();
//							$(".lngBTN").removeClass('btn-info');
							$(".lngBTN").removeClass('active');
//							$(".lngBTN").addClass('btn-primary');

//							$(".lngBTN."+lng).removeClass('btn-info');
							$(".lngBTN."+lng).addClass('active');
							$("#lang_entity").val(lng).change();
						});
					}
					$(".lngBTN."+lang).click();
					//$("#lang_entity").val(lang).change();
				}
			});

			review_by = res.data[0].review_by;
			/*
			$("#editEavItem [name]").each((i, el) => {
				var val = '';
				switch(el.name){
					case 'ownerId':
						el.value = (this.editItem_entity[el.name] == null)? '0':this.editItem_entity[el.name];
						break;

					case this.columns.ownershipColumn:
						$("#"+this.columns.ownershipColumn+this.editItem_entity[el.name]).click();
						break;

					case this.columns.reservedColumn:
						var checked = (this.editItem_entity[this.columns.reservedColumn] == 1)? true:false;
						$("#"+this.columns.reservedColumn).attr('checked', checked);
						break;

					case this.columns.reservedColumn2:
						var checked = (this.editItem_entity[this.columns.reservedColumn2] == 1)? true:false;
						$("#"+this.columns.reservedColumn2).attr('checked', checked);
						break;

					case 'storageType':
						el.value = (this.editItem_entity[el.name] == null)? '0':this.editItem_entity[el.name];
						break;

					default:
						el.value = this.editItem_entity[el.name];
				}
			});*/
			
			$("#waitspin").hide();
			
			if($('body .outputCode').length!=0){
				let item = $('body .outputCode');
				if($('#outputCodeV').length==0){
					let divP = $(item).parent();
					let divD = $("<div>")
						.attr({
							style:"width:98%; overflow:auto;margin:5px auto 15px;border:1px solid #ddd;padding:10px 15px;height:150px",
							id:'outputCodeV'
						});
					let divB = $("<div>")
						.attr({class:"btn-group",role:"group",'aria-label':"Basic example",style:"width:99%;text-align:right;margin-top:-25px"});
					let btnO = $("<button>Preview</button>")
						.attr({style:"float:none;padding:3px 6px;font-size:80%;", class:"btn btn-info"   , type:"button", id:"btnShowOutput"});
					let btnC = $("<button>Code</button>")
						.attr({style:"float:none;padding:3px 6px;font-size:80%;", class:"btn btn-default", type:"button", id:"btnShowCode"});
					$(divB).append($(btnC)).append($(btnO));
/*					
					$(divB).append(
						"<div style='width:100%; text-align:left; font-size:80%'>"+
							"<small style='color:red;opacity:0' id='showCodeMsg'>"+
								"Save to see the updated previow"+
							"</small>"+
						"</div>"
					);
*/					
					$(divP).find("div:first-child").after($(divB));
					$(item).before($(divD));
					
					
					
/*
					let divN = $("<div>").attr({class: $(divP).attr('class'), id:'outputCodeO'});
					let divD = $("<div>").attr({style: "width:98%; overflow:auto;margin:auto;", id:'outputCodeV'});

					//$(divP).attr('id', 'outputCodeC');
					$(divN).append($(divD));
					$(divP).before($(divB));
					$(item).prepend($(divN));
*/
					$("#btnShowOutput").on("click", function(){
						$("#btnShowOutput").removeClass("btn-default").addClass("btn-info");
						$("#btnShowCode"  ).removeClass("btn-info").addClass("btn-default");
						$("body .outputCode").hide().change();
						$("#outputCodeV").html($("body .outputCode").val().trim());
						$("#outputCodeV").show();
						//$("#showCodeMsg").css('opacity', 0);
					});
					$("#btnShowCode").on("click", function(){
						$("#btnShowOutput").removeClass("btn-info").addClass("btn-default");
						$("#btnShowCode"  ).removeClass("btn-default").addClass("btn-info");
						$("#outputCodeV").hide();
						$("body .outputCode").show();
						//$("#showCodeMsg").css('opacity', 1);
					});
				}
				$("#outputCodeV").html($(item).val());
				$("body .outputCode").hide();
				$("#outputCodeV").show();
				
				/*
				if($("#draft").is(":visible")){
					$("#btnShowOutput").click();
					if($(item).val().trim().length==0){ $("#btnShowCode").click(); }
				}
				else{ $("#btnShowCode").click(); }
				*/
				$("#btnShowOutput").click();
				if($(item).val().trim().length==0){ $("#btnShowCode").click(); }
			}
		});
	}
	//-----------------------------------------------------------
	createAttrs(data, responsiblity, RAG){
		var that= this;
		$("#extendedEntityNameDIV").remove();
		$("#attributes")
			.parent()
			.prepend('<div id="extendedEntityNameDIV"><label>Name: <span id="extendedEntityName">-</span></label></div>');
		$("#attributes").html('');
		var for_function = function (i,oldata) {
			if(i==0){ $("#extendedEntityNameDIV").html("<label>Name: </label><span>"+oldata[0].extendedEntityName+"</span>"); }
			if(i<oldata.length){
				var row_attributes = $("<div>").attr({
					class: "col-attribute_"+i+"  attributes-group",
					data_itemid:i,
					'data-attrid':oldata[i].attributeId
				});
				that.defaultAttrsFormInput(i,oldata[i],row_attributes,oldata,function (i,oldata) {
					i++;
					for_function(i,oldata)
				});
			}else{
				layui.use('upload', function(){
					var $ = layui.jquery, 
						upload = layui.upload;
					upload.render({
						elem: '.myuploadfile',
						url: '/api/upload_action',
						accept: 'file',
						data:{ 'orgID':that.editItem_entity.ownerId },
						done: function(res){
							var tmp_attr = this.item.attr('uploadattr');
							$('#'+tmp_attr).val(res.Src);
						}
					});
				});
				/*
				var tmp_attr=$('.col-attributes div[class$="attributes-group"]');
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
		$("#attributes").prepend(
			'<div class="col-lang_entity form-group" style="margin:10px 0">'+
				'<label>Language</label>'+
				'<div id="lang_entity_div" style="display:inline-flex; margin-left:5px;" class="btn-group">'+
					'<select name="lang_entity" id="lang_entity" style="display:none"></select>'+
				'</div>'+
			'</div>'
		);
/*
		if{
			fdom.append(
				$("<div>")
					.attr({class: "reviewed_by "+data.extendedEAVID+'_'+i, style: "margin-top:"+marginTop})
					.append($("<input>").attr({ type: "checkbox", id:"chk_review_by_"+data.extendedEAVID, class:"chk_review_by" }))
					.append($('<label for="chk_review_by_'+data.extendedEAVID+'"> Reviewed</label>'))
				);
		}
*/

		let chk1 = '0%';
		let chk2 = '100%';
		let chk3 = "";
		if(this.editItem[0].storageType.toLowerCase()=='text'){
			chk1 = '30%';
			chk2 = '70%';
			chk3 = ""+
				"<div class='reviewed_by' style='text-align:right !important; margin:6px 0 0'>"+
					"<input type='checkbox' id='chk_review_by_00' class='chk_review_by'> "+
					'<label for="chk_review_by_00"> Reviewed</label>'+
				"</div>";
		}
		if(this.editItem[0].storageType.toLowerCase()=='text'){
			let notesA = ((responsiblity!=null) ?responsiblity.note :'');
			$(".charactersLeft").append(
				'<input style="width:60px;padding:2px;float:right;margin-right:5px" type="button" value="Draft" class="btn btn-primary" id="draft">'
			);
			$("#attributes").append(
				'<div class="col-review  form-group" style="margin:10px 0 0; display:block;">'+
					'<input type="checkbox" style="margin:0 2px;" id="certifyA">'+
					'<b><small><label for="certifyA" style="display: initial;font-size:96%">'+
						'I am responsible to ensure the authorized use of this information. (Add necessary notes below)'+
					'</label></small></b>'+
					"<div>"+
						'<label for="notesA">Notes</label>'+
//						'<textarea id="notesA" class="form-control">'+notesA+'</textarea>'+
						'<textarea id="notesA" class="form-control"></textarea>'+
					'</div>'+
				""
			);
			$(".attributes-group").css('background', 'rgba(204, 196, 196, 0.15)').css("padding", "1px 0");
		}
		$("#attributes").append(
			'<div class="col-review  form-group" style="margin:10px 0 5px; display:flex;">'+
				"<div style='width:"+chk2+";'>"+
					'<label>Review by</label>'+
					'<div style="display:inline-flex; margin-left:5px;" class="btn-group">'+
						'<input type="text" id="review_by" name="review_by" class="form-control" readonly />'+
						'<button type="button" id="clear_review_by" class="btn btn-danger">Clear</button>'+
					'</div>'+
				"</div>"+
				"<div style='width:"+chk1+"; text-align:right !important; display:none' class='reviewed_by chk1'>"+//bhr123
					"<div class='reviewed_by chk2' style='text-align:right !important'>"+
						chk3+
					"</div>"+
				"</div>"+
			'</div>'
		);
		$(function(){
			$( "#review_by" ).datepicker({minDate: new Date() });
			$( "#review_by" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
			
			
//			$( "#review_by" ).css('width', 'calc(100% - 80px)');
			$( "#review_by" ).css('display', 'inline-block');
			$( "#review_by" ).css('margin-right', '10px');
			$("body").on('click', '#clear_review_by', function(){
				$( "#review_by" ).val('').change();
			})
			$("#review_by").on('change', function(){
				that.editItem[0].review_by = $("#review_by").val();
				$(".chk_review_by").prop("checked", true);
			});
			$( "#review_by" ).datepicker( "setDate", review_by );
			$( "#review_by" ).change();
			$(".chk_review_by").prop("checked", false);
			
			$('body').on('keyup', 'textarea.iput_stringvalue', function(){
				let len = maxIputStringValue - $(this).val().length;
				len = (len<0) ?0 :len;

				$(this).parent().find("span").html(len);
			});
			$('body').on('change', 'textarea.iput_stringvalue', function(){
				let len = maxIputStringValue - $(this).val().length;
				len = (len<0) ?0 :len;

				$(this).parent().find("span").html(len);
			});
			
			if($("#review_by").val().trim()!=''){
				//$(".chk_review_by").prop('checked', true);
			}
			
			if($("#notesA").val()!=''){ $("#certifyA").prop("checked", true); }
			
		});
		
		$("#lang_entity").on('change', function(){
			that.editItem[0].lang = $("#lang_entity").val();
			if(that.lang!=$("#lang_entity").val()){
				that.lang = $("#lang_entity").val();
				$("#attributes .attributes-group .iput_stringvalue").each(function(){
					$(this).val('').change();
					//$(this).parent().find("span").html(1000);
				})
				$(".chk_review_by").each(function(){
					$(this).prop('checked', false).change();
				})
				
				$("#waitspin").show();
				if(that.lang=='en'){ $(".translationBTN").hide(); }else{ $(".translationBTN").show(); }
				$(".machineTranslation").click();
				//
				//machineTranslation
				$.get(that.ExtendedDataViewsURL+"/"+that.lang, (res) => {
					if(res.result==0){
						if(res.data.length>0){
							$("#attributes .attributes-group .iput_stringvalue").each(function(){
								let itemid =  $(this).attr('itemid');
								
								$(this).val(res.data[itemid].valueString).change();
								if(that.lang=='en' && res.data[itemid].valueString==null)
									{ $(this).val(res.data[itemid].defaultValue).change(); }
								//$(this).parent().find('label.tmp_label').html(res.data[itemid].attributeName);
								if(that.lang!='en' && res.data[itemid].valueString!=null)
									{ $("#customTranslation_"+itemid).click(); }
								if(that.lang=='en')
									{ $("#customTranslation_"+itemid).click(); }
							})
						}
					}
					$("#waitspin").hide();
				});
			}
		});
		
		$("#attributes").append(this.getActionFormInput('ownership_entity', 'Ownership'));
		$("#attributes").append(this.getActionFormInput('ownerId_entity', 'Owner'));
		
	}
	//-----------------------------------------------------------
	defaultAttrsFormInput(i,data,fdom,oldata,cb) {
		//-------------------------------------------------------
		var tmp_name =data.displayName?data.displayName:data.attributeName;
		var tmp_value =data.valueString?data.valueString:data.defaultValue;
		var tmp_placeholder =data.attributeMemo?data.attributeMemo:data.attributeName;
		var temp_memo= data.memo?data.memo:'';
		var ownerId= data.ownerId?data.ownerId:0;
		var tmp_ownership=data.ownership;
		let marginTop = "5px";
		//-------------------------------------------------------
		//fdom.append($("<label class='tmp_label'>"+tmp_name+"</label>"));
//console.log(data.extendedEAVID)
		fdom.append(
			$("<div>")
				.attr({style: 'display:flex; flex-wrap:wrap; margin-top:15px'})
				.append(
					$("<label >"+tmp_name+"</label>")
						.attr({class: 'tmp_label', style:"flex-grow:1; width:30%;"})
				)
				.append(
					$("<small>")
						.attr({style: 'font-size:80%; flex-grow:1; width:25%;z-index:9999;'})
						.append(
							$("<label >Use Machine Translation</label>")
								.attr({ 
									id         : 'machineTranslation_'+i,
									'data-item':data.extendedEAVID+'_'+i,
									'data-i'   : i,
									style      :'cursor:pointer;font-size:80%;margin-top:-6px;',
									class      :'btn btn-default translationBTN machineTranslation '+((tmp_value.lenght==0) ?'active' :'' )
								})
						)
				)
				.append(
					$("<small>")
						.attr({style: 'font-size:80%; flex-grow:1; width:25%;z-index:9999;'})
						.append(
							$("<label >Enter Custom Translation</label>")
								.attr({ 
									id         : 'customTranslation_'+i,
									'data-item':data.extendedEAVID+'_'+i,
									'data-i'   : i,
									style      :'cursor:pointer;font-size:80%;margin-top:-6px;',
									class      :'btn btn-default translationBTN customTranslation '+((tmp_value.lenght!=0) ?'active' :'' )
								})
						)
				)
				.append(
					$("<small>")
						.attr({style: 'font-size:80%; flex-grow:1; width:20%;'})
						.append(
							$("<label></label>")
						)
				)
		);
		//-------------------------------------------------------
		if(data.storageType.indexOf('BLOB')>-1){
			fdom.append(
				$("<input>")
					.attr({
						name: tmp_name+'_'+i,
						id: tmp_name+'_'+i,
						placeholder: tmp_placeholder,
						value:tmp_value,
						itemid:i,
						class: 'iput_stringvalue form-control '+data.extendedEAVID+'_'+i,
						readonly:"readonly"
					})
			);
			fdom.append(
				$('<button type="button" class="layui-btn myuploadfile '+data.extendedEAVID+'_'+i+'" uploadattr="'+tmp_name+'_'+i+'" style="margin-left: 3px;" id="uploadfile_'+i+'"><i class="layui-icon"></i>Upload File</button>')
			);
		}else{
			if(data.storageType.indexOf('TEXT')>-1){
				marginTop = "-25px";
				fdom.append(
					$("<textarea>")
						.attr({
							name: tmp_name+'_'+i,
							id: tmp_name+'_'+i,
							placeholder: tmp_placeholder,
							//					  value:tmp_value,
							itemid:i,
							maxlength:maxIputStringValue,
							class: 'outputCode iput_stringvalue form-control '+data.extendedEAVID+'_'+i
						})
						.val(tmp_value)
				);
				let charactersLeft = maxIputStringValue - tmp_value.length;
				charactersLeft = (charactersLeft<0) ?0 :charactersLeft;
				fdom.append(
					$("<div><span>"+charactersLeft+"</span> characters left</div>")
						.attr({
							class: "charactersLeft "+data.extendedEAVID+'_'+i,
							style: "margin:-10px 0px 10px 10px; font-size: smaller;"
						})
				);
			}else{
				fdom.append(
					$("<input>")
						.attr({
							name: tmp_name+'_'+i,
							id: tmp_name+'_'+i,
							placeholder: tmp_placeholder,
							value:tmp_value,
							itemid:i,
							maxlength:maxIputStringValue,
							class: 'iput_stringvalue form-control '+data.extendedEAVID+'_'+i
						})
				);
			}
		}
		//-------------------------------------------------------
/**/
		if(data.review_by!=null){
			if(this.editItem[0].storageType.toLowerCase()!='text'){
				fdom.append(
					$("<div>")
						.attr({class: "reviewed_by "+data.extendedEAVID+'_'+i, style: "margin-top:"+marginTop})
						.append($("<input>").attr({ type: "checkbox", id:"chk_review_by_"+data.extendedEAVID, class:"chk_review_by" }))
						.append($('<label for="chk_review_by_'+data.extendedEAVID+'" style="margin-left:3px;">Reviewed</label>'))
					);
			}
		}
/**/
		//-------------------------------------------------------
		$("#attributes").append(fdom);
		$(".translationBTN").hide();
		$(".translationBTN").on('click', function(){
			if($(this).hasClass('machineTranslation')){
				$("."+$(this).data('item')).hide();
				$("#customTranslation_"+$(this).data('i')).removeClass('active');
				$("#machineTranslation_"+$(this).data('i')).addClass('active');
				
				let itemClass = $(this).data('item');
				if( $(".iput_stringvalue."+itemClass).val().trim()!='' ){
				showError("If you choose \"Machine Translation\" your current custom translation will be removed. You may enter a new custom translation later.");
				 	$(".iput_stringvalue."+itemClass).val('').change();
				}
				
			}
			else{
				$("."+$(this).data('item')).show();
				$("#customTranslation_"+$(this).data('i')).addClass('active');
				$("#machineTranslation_"+$(this).data('i')).removeClass('active');
			}
		})
		if(cb){ cb(i,oldata); }
		//-------------------------------------------------------
	}
	//-----------------------------------------------------------
  /*showorderup(e){
    e.preventDefault();
    let temp_itemid=$(e.currentTarget).data('itemid');
    console.dir(temp_itemid);
    console.dir(this.rows);
    let newData=[''];
    var this_rows = JSON.parse(JSON.stringify(this.rows));
    for(let i=0;i<this_rows.length;i++){
      if(this_rows[i].extendedEntityId==temp_itemid){
        newData[0]=this_rows[i];
      }else{
        newData.push(this_rows[i]);
      }
    }

    let temp_upolderl=[];
    for(let temp_j = 0;temp_j<this.rows.length;temp_j++){
      newData[temp_j].orderid=this.rows[temp_j].orderid;
      temp_upolderl.push({extendedEntityId:newData[temp_j].extendedEntityId,orderid:newData[temp_j].orderid})
    }

    console.dir(temp_upolderl);
    console.dir(this.upolderURL);
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
        }else{
          showError(res.msg);
        }
      },
      error: function(e){
        showError('Server error');
      }
    });

  }
  showorderdown(e){
    e.preventDefault();
    let temp_itemid=$(e.currentTarget).data('itemid');
    console.dir(temp_itemid);
    console.dir(this.rows);
    let newData=[];
    newData[this.rows.length-1]='';
    var this_rows = JSON.parse(JSON.stringify(this.rows));
    for(let i=0;i<this_rows.length;i++){
      if(this_rows[i].extendedEntityId==temp_itemid){
        newData[this_rows.length-1]=this_rows[i];
      }else{
        for(let t=0;t<newData.length;t++){
          if(!newData[t]){
            newData[t]=this_rows[i];
            break;
          }
        }

      }
    }

    let temp_upolderl=[];
    for(let temp_j = 0;temp_j<this.rows.length;temp_j++){
      newData[temp_j].orderid=this.rows[temp_j].orderid;
      temp_upolderl.push({extendedEntityId:newData[temp_j].extendedEntityId,orderid:newData[temp_j].orderid})
    }
    console.dir(temp_upolderl);
    console.dir(this.upolderURL);
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
        }else{
          showError(res.msg);
        }
      },
      error: function(e){
        showError('Server error');
      }
    });

  }*/
	showOrgDeleteBox(e){
		e.preventDefault();
/*
		$('#editEavItem').fadeOut();
		$('#deleteDialog').fadeIn();

//		this.deleteId = this.extendedEntityId;
		$("#deleteDialog_msg").parent().css('width' , '300px');
		$("#deleteDialog_msg").parent().css('height', '100px');
		$("#deleteDialog_msg").html('Are you sure you want to delete this item?');
		this.lang = "";
*/
		this.extendedEntityId = $(e.target).data('itemid');
	    $.get(this.apiURL + "/" + this.extendedEntityId+'/langs', (res) => {
			if(res.result==0){
				$('#editEavItem').fadeOut();
				$('#deleteDialog').fadeIn();
				if(res.count==0){
					$("#deleteDialog_msg").parent().css('width' , '300px');
					$("#deleteDialog_msg").parent().css('height', '100px');
					$("#deleteDialog_msg").html('Are you sure you want to delete this item?');
				}else{
					$("#deleteDialog_msg").parent().css('width' , '340px');
					$("#deleteDialog_msg").parent().css('height', '120px');
					$("#deleteDialog_msg")
						.html('Custom language translation(s) exist on this record.<br/><br/>Are you sure you want to delete this item?');
				}
				this.deleteId = this.extendedEntityId;
				this.lang = "";
			}
    	});
	}
	showDelete_Attrbutes_extendedData(e) {
		e.preventDefault();
	    $.get(this.apiURL + "/" + this.extendedEntityId+'/langs', (res) => {
			if(res.result==0){
				$('#editEavItem').fadeOut();
				$('#deleteDialog').fadeIn();
				if(res.count==0){
					$("#deleteDialog_msg").parent().css('width' , '300px');
					$("#deleteDialog_msg").parent().css('height', '100px');
					$("#deleteDialog_msg").html('Are you sure you want to delete this item?');
				}else{
					$("#deleteDialog_msg").parent().css('width' , '340px');
					$("#deleteDialog_msg").parent().css('height', '120px');
					$("#deleteDialog_msg")
						.html('Custom language translation(s) exist on this record.<br/><br/>Are you sure you want to delete this item?');
				}
				this.deleteId = this.extendedEntityId;
//				this.lang = "all";
				this.lang = "";
			}
    	});
		return;
  }
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
          $("#"+this.columns.ownershipColumn+"2").click();
          this.editItem[this.columns.ownershipColumn] = '2';
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
    $("#editItem [name]").each((i, el) => {
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
          $("#"+this.columns.ownershipColumn+"2").click();
          this.editItem[this.columns.ownershipColumn] = '2';
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
    //----------------------------------------------------------
	if($("#ownerId").val()==0){
		$("#"+this.columns.ownershipColumn+"0").parent().removeClass('disabled');
		$("#"+this.columns.ownershipColumn+"0").click();
		$("#"+this.columns.ownershipColumn+"1").parent().addClass('disabled');
		$("#"+this.columns.ownershipColumn+"2").parent().addClass('disabled');
	}else{
		$("#"+this.columns.ownershipColumn+"1").parent().removeClass('disabled');
		$("#"+this.columns.ownershipColumn+"2").parent().removeClass('disabled');
		$("#"+this.columns.ownershipColumn+"2").click();
		$("#"+this.columns.ownershipColumn+"0").parent().addClass('disabled');
	}
    //----------------------------------------------------------
  }
//--------------------------------------------------------------
  /*changeSelectextendedEntityId(e){
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
    /!*if(value=='NULL'){value=0;}
    this.extendedAttributeId=value;
    this.refreshOptions()*!/
  }*/
	editItemConfirmHandler(e){
		e.preventDefault();
		let continued = true;
		if(this.lang!='en'){
			$(".customTranslation").each(function(){
				if($(this).hasClass('active')){
					let itemClass = $(this).data('item');
					if($(".iput_stringvalue."+itemClass).val().trim()==''){ continued=false; }
				}
			});
		}
		if(continued==false){
			showError("Custom Translation cannot be blank");
			return false;
		}

		continued = true;
		if($("#review_by").val().trim()!=''){
			if($(".chk_review_by").length>1){
				$(".chk_review_by").each(function(){
					if($(this).prop('checked')==false){
						if($(this).parent().parent().find(".customTranslation").hasClass('active'))
							{ continued=false; }
					}
				});
			}else{
				if($(".chk_review_by").prop('checked')==false){
	//				if($(".customTranslation").hasClass('active') && $(".customTranslation").is(":visible"))
					if($(".customTranslation").hasClass('active'))
						{ continued=false; }
				}
			}
		}
		if(continued==false){
			showError("You have not reviewed all the values");
			return false;
		}
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

		//----------------------------------------------------------
		if($("#certifyA").is(":visible")){
			if($("#certifyA").prop('checked')!=true){
				showError("Responsibility is required");
				return;
			}
		}
		//----------------------------------------------------------
		var that = this;
		//----------------------------------------------------------
		let myData = this.editItem;
		if(myData[0].storageType.toLowerCase()=='text'){
			myData[0].certify = (($("#certifyA").prop('checked')) ?1 :0);
			myData[0].notes   = $("#notesA").val().trim();
			myData[0].userid  = userID;
		}
		//----------------------------------------------------------
		$.ajax({
		  url: that.editEavURL,
		  type: 'put',
		  headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		  },
		  data: JSON.stringify(myData),
		  /*data: JSON.stringify({editItem:this.editItem,
			ownership_entity:$("input[name='ownership_entity']:checked").val(),
			ownerId_entity:$("#ownerId_entity").find("option:selected")[0].value
		  }),*/
		  beforeSend: function(){ $("#editItem #saveItem #editEavItem").prop('disabled', true); },
		  success: function(res){
			if(res.result == 0){
				if(that.saveOnlyClick==false){
					$("#editEavItem").fadeOut(function(){ $("#editItem #saveItem #editEavItem").prop('disabled', false); });
				}else{
					$("#outputCodeV").html($("textarea.iput_stringvalue").val());					
					$("#btnShowOutput").click();
				}
				showSuccess('Item saved.');
				that.refreshOptions();
			}else{
				showError(res.msg);
				$("#editItem #saveItem #editEavItem").prop('disabled', false);
			}
		  },
		  error: function(e){
			showError('Server error');
			$("#editItem #saveItem #editEavItem").prop('disabled', false);
		  }
		});
	}
	//--------------------------------------------------------------
	actionEavForm() {

		var submitLabel = 'Save';// & Close';
		var submitId    = 'saveAttr';

		var formChildren = [];
		var columns = this.columns;
		var data = columns.data;

		var row_attributes = $("<div>").attr({ class: "col-attributes form-group" })
			.append("<label>Attributes</label>")
			.append($("<div>").attr({ id: 'attributes' }));
		formChildren.push(row_attributes);

		var wrapper = $("<div>").attr({ id: 'editEavItem' });
		var form = $("<form>").attr({ class: 'action-form' });

		$(formChildren).each(function(i, el){
			form = $(form).append(el);
		});

		let actionDIV = $("<div>")
			.attr({style:"display:flex; flex-wrap:wrap"});
		let submit = $("<div>")
			.attr({style:"width:33%; text-align:right; min-width:101px"})
			.append($("<input>")
				.attr({
					id   : submitId,
					style: 'width:105px;padding:7px 0',
					type : 'submit',
					value: submitLabel,
					class: 'btn btn-primary'
				})
			);

		let Delete_Attrbutes_extendedData = $("<div>")
			.attr({style:"width:34%; text-align:center; min-width:101px"})
			.append($("<input>")
				.attr({
					id: 'Delete_Attrbutes_extendedData',
					style: 'width:105px;padding:7px 0',
					type : 'button',
					value: 'Delete',
					class: 'btn btn-danger'
				})
			);

		let cancel = $("<div>")
			.attr({style:"width:33%; text-align:left; min-width:101px"})
			.append($("<input>")
				.attr({
					style: 'width:105px;padding:7px 0',
					type : 'button',
					value: 'Cancel',
					class: 'btn btn-danger',//btn btn-default
					onClick: "$('#editEavItem').fadeOut()"
				})
			);
/*
		let draft = $("<div>")
			.attr({style:"width:25%; text-align:center; min-width:101px"})
			.append($("<input>")
				.attr({
					id   : 'saveOnly',
					style: 'width:105px;padding:7px 0',
					type : 'button',
					value: 'Save',
					class: 'btn btn-success',//btn btn-default
				})
			);
*/
//		$(actionDIV).append([cancel, draft, Delete_Attrbutes_extendedData, submit]);
		$(actionDIV).append([cancel, Delete_Attrbutes_extendedData, submit]);
		form = $(form).append([actionDIV]);
		wrapper = $(wrapper).append(form);

		return wrapper;
	}
	//--------------------------------------------------------------
	showDraftModal(e){
		enterpriseOrgID = this.editItem_entity.ownerId;
		$("#inquiry, #urlText, #result, #notes").val('');
		$("#charMax").val('1000');
//		$("#internet, #enterprise, #kamaDEI, #sharepoint, #url, #certify").prop('checked', false);
		$("#internet, #enterprise, #kamaDEI, #url, #certify").prop('checked', false);
		$("#saveDraft, #copyDraft, #charMax").prop('disabled', true);
		$(".enterprise.radioItemsElemans").html("").hide();
		$(".url.radioItemsElemans").hide();

		//$("#prevEdit").prop('checked', false).change();
		this.showPrevEditResult(0);

		$("#draftModal")
			.modal({backdrop:"static"})
			.on('shown.bs.modal', function(e){ $("#inquiry").focus(); });
		
		$("#inquiry").on('change', function(){
			if($("#inquiry").val().length>maxInquiryLen){ $("#inquiry").val($("#inquiry").val().substr(0,maxInquiryLen)); }
		});
		$("#inquiry").on('keypress', function(){
			if($("#inquiry").val().length>=maxInquiryLen){ $("#inquiry").val($("#inquiry").val().substr(0,maxInquiryLen-1)); }
		});
		$("#inquiry").on('keyup', function(){
			if($("#inquiry").val().length>maxInquiryLen){ $("#inquiry").val($("#inquiry").val().substr(0,maxInquiryLen)); }
		});
		//extendedsubtype
	}
	//--------------------------------------------------------------
/*
	changeCertify(e){
		$("#saveDraft").prop('disabled', true);
		if($("#certify").prop('checked')){ $("#saveDraft").prop('disabled', false); }
	}
*/
	changeResult(){
		$("#saveDraft").prop('disabled', true);
		if($("#result").val().trim()!=''){ $("#saveDraft").prop('disabled', false); }
	}
	//--------------------------------------------------------------
	draftSearch(e){
		let searchType = "";
		let urlSearch = draftSearchURL;
		let data = {
			question: $("#inquiry").val().trim()
		};
		
		if(data.question==''){
			showError("Inquiry is empty.");
			$("#inquiry").focus();
			return;
		}

		let radioItems = 0;
		if($("#internet").prop('checked')){
			radioItems = 1;
			searchType = "internet";
		}
		if($("#url").prop('checked')){
			data.site = $("#urlText").val().trim();
			if(data.site==''){
				showError("URL is empty");
				$("#urlText").focus();
				return;
			}
			searchType = "url";
			radioItems = 1;
		}
		if($("#enterprise").prop('checked')){
			let collection_name = ""
			$(".enterpriseCllctns").each(function(){
				if($(this).prop('checked')){ collection_name = $(this).attr('id'); }
			});
			urlSearch = draftSearchURL_enterprise;
			data.org             = enterpriseOrgID;
			data.collection_name = collection_name;
			searchType = "enterprise";
			//data.filetype = "pdf";
			radioItems = 1;
		}
		if(radioItems==0){
//			showError("Internet? / Enterprise? / Kama DEI? / Sharepoint? / URL?");
			showError("Internet? / Enterprise? / Kama DEI? / URL?");
			return;
		}
		let that = this;
		$.ajax({
			url   : urlSearch,
			method: "POST",
            headers: {
				'apikey': '123'
            },
			data: data,
			beforeSend: function(){
				$("#inquiry, #searchBTN, #certify").prop('disabled', true);
				$("#searchBTN").html('<i class="fa fa-refresh fa-spin"></i>');
				$("#certify").prop('checked', false).change();
				$("#resultResult").show();
				$("#result").val('');
				//$("#prevEdit").prop('checked', false).change();				
				that.showPrevEditResult(0);
			},
			complete: function(){ $("#inquiry, #searchBTN").prop('disabled', false); $("#searchBTN").html('Search'); },
			success: function(res){
				$("#certify").prop('disabled', false);
				$("#result").val(res.res).change();
				resultDraftRES = res.res.trim();
				resultDraftURL = res.urls_dict;
			},
			error: function(e){
				if(e.status==422){
					if(searchType == "enterprise"){ showError(e.responseJSON.detail.message); }
					else{ showError(e.statusText); }
				}
				else{ showError('Server error'); }
			}
		});
	}
	//--------------------------------------------------------------
	showPrevEditResult(e){
//		if($("#prevEdit").prop('checked')){
		if(e==1){
			$("#resultResult").hide();
			$("#resultPreview").show();
			let tmp = $("#result").val().trim();
			let indxs = [];
			for(let i in resultDraftURL){
				indxs.push({index:i, pos:tmp.indexOf("["+i+"]")});
			}
			let indx = 0;
			let pos  = tmp.length;
			for(let i in indxs){
				if(indxs[i].pos<pos){
					indx = indxs[i].index;
					pos  = indxs[i].pos;
				}
			}
			for(let i in resultDraftURL){
				if(i==indx){
					tmp = tmp.replace(
						"["+i+"]",
						'<br/><a target="_blank" href="'+resultDraftURL[i]+'" data-title="'+resultDraftURL[i]+'">['+i+']</a>'
					);
				}else{
					tmp = tmp.replace(
						"["+i+"]",
						'<a target="_blank" href="'+resultDraftURL[i]+'" data-title="'+resultDraftURL[i]+'">['+i+']</a>'
					);
				}
			}
	/*
			tmp += '<a  target="_blank" href="https://kama.ai/resources/kama-dei-platform/" data-title="https://kama.ai/resources/kama-dei-platform/">[1]</a><a  target="_blank" href="https://slashdot.org/software/p/kama-DEI/" data-title="https://slashdot.org/software/p/kama-DEI/">[2]</a>';
	*/
			$("#resultPreview p").html(tmp);
			$("#showEditBtn").removeClass('btn-info').addClass('btn-default');
			$("#showPrevBtn").removeClass('btn-default').addClass('btn-info');
		}else{
			$("#resultResult").show();
			$("#resultPreview").hide();
			$("#showPrevBtn").removeClass('btn-info').addClass('btn-default');
			$("#showEditBtn").removeClass('btn-default').addClass('btn-info');
		}
	}
	//--------------------------------------------------------------
	showPreViewResult(e){
	}
	//--------------------------------------------------------------
	saveDraft(e){
//console.log(this.editItem)

		//if($("#certify").prop('checked')==false){ return; }
		
		let style = "<style>"
			+"[data-title]:hover:after{opacity:1;transition:all 0.1s ease 0.5s;visibility:visible;}"
			+"[data-title]:after{content:attr(data-title);color:#111;background:#fff;position:absolute;padding:1px 5px 2px 5px;bottom:0.6em;"+
			   "left:100%;white-space:nowrap;box-shadow:1px 1px 3px #222222;opacity:0;border:1px solid #111111;z-index:99999;visibility:hidden;}"
			+"[data-title]{position:relative;}"
			+"</style>";
		
		let tmp = $("#result").val().trim();
		
		let indxs = [];
		for(let i in resultDraftURL){
			indxs.push({index:i, pos:tmp.indexOf("["+i+"]")});
		}
		let indx = 0;
		let pos  = tmp.length;
		for(let i in indxs){
			if(indxs[i].pos<pos){
				indx = indxs[i].index;
				pos  = indxs[i].pos;
			}
		}
		for(let i in resultDraftURL){
			if(i==indx){
				tmp = tmp.replace(
					"["+i+"]",
					'<br/><a target="_blank" href="'+resultDraftURL[i]+'" data-title="'+resultDraftURL[i]+'">['+i+']</a>'
				);
			}else{
				tmp = tmp.replace(
					"["+i+"]",
					'<a target="_blank" href="'+resultDraftURL[i]+'" data-title="'+resultDraftURL[i]+'">['+i+']</a>'
				);
			}
		}
		
		
/*		
		for(let i in resultDraftURL){
			tmp = tmp.replace("["+i+"]", '<a target="_blank" href="'+resultDraftURL[i]+'" data-title="'+resultDraftURL[i]+'">['+i+']</a>');
		}
*/		
		tmp = style+tmp;
//
		let itemID = this.editItem[0].displayName+"_0";
		$("textarea").each(function(){
			if($(this).attr('id')==itemID){
				$(this).val(tmp).change();
				$("#outputCodeV").html(tmp);
			}
		});
		
		let selections = 0;
		if($("#internet"  ).prop('checked')){ selections = 0; }
		if($("#enterprise").prop('checked')){ selections = 1; }
		if($("#kamaDEI"   ).prop('checked')){ selections = 2; }
		//if($("#sharepoint").prop('checked')){ selections = 3; }
		if($("#url"       ).prop('checked')){ selections = 4; }
		
		let urlText = "";
		if(selections==4){ urlText = $("#urlText").val().trim(); }
		let data = {
			extendedEntityId: this.editItem[0].extendedEntityId,
			lang            : this.lang,
			inquiry         : $("#inquiry").val().trim(),
			selections      : selections,
			urlText         : urlText,
			result          : tmp,
			notes           : $("#notes").val().trim()
		};
		
		let that = this;
		$.ajax({
			url: that.saveDraftURL,
			method: 'put',
			data: data,
			beforeSend: function(){},
			complete: function(){},
			error: function(xhr){ showError(xhr.statusText); },
			success: function(res){
				if(res.result==1){ showError(res.msg); }
				else{
					showSuccess("Draft saved.");
					$("#draftModal").modal('hide');
					$("#btnShowOutput").click();
				}
			}
		});
//showError(this.editItem[0].extendedEAVID);
	}
	//--------------------------------------------------------------
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
			this.subtypeID + '/' +
			this.orgID + '/' +
			this.pageSort + '/' +
			this.pageOrder + '/' +
			this.pageSize +'/' +
			this.pageNumber + '/' +
			'showglobal/'+this.showGlobalStatus;
	}
	get searchURL(){
		return this.apiURL + '/' +
			this.subtypeID + '/' +
			this.orgID + '/' +
			this.pageSort + '/' +
			this.pageOrder + '/' +
			this.pageSize + '/' +
			this.pageNumber + '/' +
			this.columns.searchColumn + '/' +
			this.search + '/' +
			'showglobal/'+this.showGlobalStatus;
	}
	get addURL   () { return this.apiURL+'/new/' +  this.orgID+'/'; }
	get editURL  () { return this.apiURL+'/edit/' + this.orgID+'/' + this.editItem[this.columns.primaryColumn]; }
	get deleteURL() { return this.apiURL + "/delete/" + this.orgID+'/' + this.deleteId + '/' + this.lang; }
	get ExtendedDataViewsURL() { return this.apiURLBase + '/api/extend/extendeddataview/all/'+ this.extendedEntityId }
	get editEavURL  () { return this.apiURL+'/edit'; }
	get saveDraftURL() { return this.apiURL+'/draft'; }
	get getAllNotes()  { return this.apiURLBase + '/api/extend/extendeddataview/notes/'+ this.extendedEntityId; }
  //--------------------------------------------------------------
  getExtendedTypes() {
    $.get(this.extendedSubTypesURL, (res) => {
      this.extendedSubTypes = this.createSelectOptions(res.data, 'extendedSubTypeId', 'extendedSubTypeName');
      $("#extendedSubTypeId").append(this.extendedSubTypes);
      $("#searc_extendedSubTypeName").append(this.extendedSubTypes);
      //"<option value='"+value+"'>"+label+"</option>"
    });
  }
	//----------------------------------------------------------
	//----------------------------------------------------------
	rowActions(value, row, index, field) {
		//------------------------------------------------------
		var icons = this.actionIcons;
		$("[data-menu-toggle='#actions-menu-"+index+"']").remove();
		//------------------------------------------------------
		if( orgID!=0 ){
			if( row.ownerId==null || orgID!=row.ownerId ){
				var tmpICN = [];
				var icons = this.actionIcons;
				for (var i in icons){ if(icons[i].data('onlyowner')!=1){ tmpICN.push(icons[i]); } }
				icons = tmpICN;
			}
		}
		if(icons.length==0){ return ''; }
		//------------------------------------------------------
		var rowAction = '<div class="row-actions"></div>';
		//------------------------------------------------------
		var others = '<ul class="menu-actions" data-menu data-menu-toggle="#actions-menu-'+index+'" style="font-size:12px;"></ul>';
		for (var i in icons){
			icons[i].attr('data-itemid', row[this.columns.primaryColumn]);
			var $icon = icons[i].clone();
			$icon = $icon.append('&nbsp;&nbsp;'+$icon.data('desc'));
			others = $(others).append('<li>'+$icon[0].outerHTML+'</li>');
		}
		var toggle = ""+
			'<a href="#" class="toggle" id="actions-menu-'+index+'" style="color:dimgray">'+
				'<small class="glyphicon glyphicon-chevron-down"></small>'+
			'</a>';
		var othersIcon = '<span>'+toggle+'</span>';
		rowAction = $(rowAction).append(othersIcon);

		//if(row['notes']!=0 || true){
		let rag=0;
		if(row['organization']!=null){
			rag = row['organization']['RAG'];
		}else{ rag=1; }

		if((row['attributeType']!=null && (row['attributeType'].toLowerCase()=='text' || row['attributeType'].toLowerCase()=='text-url')) && rag)
		{
			var notes = ""+
				'<a href="#" class="notes-item" style="" data-itemid="'+row[this.columns.primaryColumn]+'">'+
					'&nbsp;&nbsp;'+'Notes'+
				'</a>';
			others = $(others).append('<li>'+notes+'</li>');
		}
		$("body").append(others);
		//----------------------------------------------------------
		$(document).ready(function(e){
			$("[data-menu]").menu(); 
		});
		//----------------------------------------------------------
		return $(rowAction)[0].outerHTML;
  }
  //--------------------------------------------------------------
}

var columns = [
	{ name: 'extendedEntityId', display: 'ID', primary: true, sortable: true },
	{ name: 'extendedEntityName', display: 'Name', sortable: true, search: true },

	{ name: 'extendedSubTypeId', display: 'Extended Data Type', hidden: true ,search: false},
	{ name: 'memo', display: 'Memo', hidden: true},

	{ name: 'ownerId', display: 'Owner', onlyFor: 0, hidden: true , searchWhere: true},
	{ name: 'ownership', display: 'Ownership', sortable: true, default: '2', ownership: true },
	{ name: 'organizationShortName', display: 'Owner', sortable: true, onlyFor: 0, editable: false },

	{ name: 'extendedSubTypeName', display: 'SubType Name', sortable: true ,editable: false  },

	{ name: 'attributeType', display:'Attribute Type', sortable: true , editable: false },
	
	{ name: 'dateCreated', display: 'Created'   , sortable: true , editable: false, date: true ,hidden: true },
	{ name: 'dateUpdated', display: 'Updated on', sortable: true , editable: false, date: true },
	{ name: 'review_by'  , display: 'Review by' , sortable: true , editable: false, date: false },
	{ name: 'review_by_p', display: ''          , sortable: false, editable: false, date: false,hidden: true },
	{ name: 'reserved', display: 'Reserved',sortable: true, reserved: true},
	{ name: 'lastUserId', hidden: true, editable: false, default: '1'},
  //{ name: 'orderid', hidden: true, editable: false, default: '0'},
];
var termColumns = new Columns(columns);

var data = {
	columns: termColumns,
	apiURL: apiURL + '/api/extend/extended_entity'
}

if($("#extendedentity").length != 0){
	var table = new ExtendedEntity(data);
	table.createTable('extendedentity');
	
	$(function(){
		$(table.table).on('load-success.bs.table',function(xhr,data){
			for(let i in data.data){
				if(data.data[i].review_by_p==1){
					$(table.table).find("tr[data-index="+i+"]>td:nth-child(7)").css('color', 'red');
				}
			}
		});
	});
}
