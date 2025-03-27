<style>
	.myLabel{ display:inline-block; width:200px; vertical-align:middle; }
	.mySelect{ width:calc(100% - 210px); display:inline-block; }
	.card-body{ max-width: 600px; }
	.btn-group{ width:calc(100% - 210px); }
	.btn-group button{ width:50%; }
	.myTextArea{
		display:inline-block;
		
		height: 300px;
		max-height: 200px;
		min-height: 200px;
	}
</style>
<div class="card" style="border: none">
	<div class="card-body">
		<label class="myLabel">Organization:</label>
		<select class="form-control mySelect" id="orgid">
			<?php if($orgID==0): ?>
			<option value="" selected>Select Organization</option>
			<?php endif; ?>
			<?php
			if($orgID!=0){
				$orgs = \App\Organization::where('organizationId', $orgID)->orderBy('organizationShortName', 'asc')->get();
			}else{
				$orgs = \App\Organization::orderBy('organizationShortName', 'asc')->get();
			}
			foreach($orgs as $org){
				?>
				<option <?=(($org['organizationId']==$orgID) ?"selected" :"");?>  value="<?=$org['organizationId'];?>">
					<?=$org['organizationShortName'];?>
				</option>
				<?php
			}
			?>
		</select>
	</div>

	<br/>
	<div class="card-body">
		<label class="myLabel">User:</label>
		<select class="form-control mySelect" id="user"></select>
	</div>

	<br/>
	<div class="card-body">
		<label class="myLabel">Select Portal Code:</label>
		<select class="form-control mySelect" id="portals"></select>
	</div>

	<br/>
	<div class="card-body">
		<label class="myLabel">Select Portal Type:</label>
		<div class="btn-group" role="group" aria-label="Basic example">
			<button type="button" class="btn btn-default" id="btnOriginal" onClick="setPortalType(this,'originalMode')">
				 Production Mode
			</button>
			<button type="button" class="btn btn-default" id="btnTest" onClick="setPortalType(this,'testMode')">
				 Debug Mode
			</button>
		</div>
		<input id="originalMode" type="hidden" />
		<input id="testMode" value="3" type="hidden" />
	</div>

	<br/>
	<div class="card-body">
		<label class="myLabel">Portal Code to Use:</label>
		<input
			   class="form-control"
			   id="portalcode" 
			   value="" 
			   style="max-width:120px;display:inline-block;text-align:center" 
			   disabled
		/>
	</div>

	<br/>
	<div class="card-body">
		<label class="myLabel" style="margin-bottom:-1px;">
			Notification email(s):
			<small style="font-size: 80%; display: block">comma separated</small>
		</label>
		<input class="form-control mySelect" id="email" value="" maxlength="500" />
	</div>

	<br/>
	<div class="card-body">
		<label class="myLabel">Run every:</label>
		<input class="form-control mySelect" id="frequency" value="" maxlength="4" style="max-width:120px;" placeholder="5" />
		<small>Min [1 - 1440]</small>
	</div>

	<br/>
	<div class="card-body">
		<label class="myLabel">Acceptable response time:</label>
		<input class="form-control mySelect" id="art" value="" maxlength="5" style="max-width:120px;" placeholder="1" /> <small>Sec</small>
	</div>

	<br/>
	<div class="card-body">
		<label class="myLabel">Test Utterance:</label>
		<input class="form-control mySelect" id="uu_text" value="" maxlength="200" placeholder="i am hungry" />
	</div>

	<br/>
	<div class="card-footer">
		<div class="myLabel">&nbsp;</div>
		<button class="btn btn-success myLabel" onClick="updateData()">Update</button>
		<br/><br/><br/>
		<div style="width:100%;" id="msgBox">&nbsp;</div>
	</div>

	<br/>
	<div class="card-body">
		<label class="myLabel" style="vertical-align: top">Response:</label>
		<button class="btn btn-danger" style="margin:-2px 0 2px 0; float: right" onClick="clearLog()">Clear log</button>
		<iframe src="/monitoring/log" class="form-control myTextArea" frameborder="0" id="monitorLog"></iframe>
	</div>
</div>

<!-- ---------------------------------------------- -->
<!-- ---------------------------------------------- -->

<script type="application/javascript">
	//-------------------------------------------------
	var users = [];
	<?php
	$users = \App\User::where('levelID', 4)->orderBy('orgID', 'asc')->orderBy('email', 'asc')->get(); 
	foreach($users as $user):
	if(strpos($user['email'],"@")):
	?>
	users.push( {email:'<?=$user['email'];?>', userid:'<?=$user['id'];?>', orgid:'<?=$user['orgID'];?>' } );
	<?php
	endif;
	endforeach;
	?>
	//-------------------------------------------------
	var portals = [];
	<?php
	$portals = \App\Portal::orderBy('organization_id', 'asc')->orderBy('name', 'asc')->get(); 
	foreach($portals as $prtl):
	?>
	portals.push( {name:'<?=$prtl['name'];?>',code:'<?=$prtl['portal_number'].$prtl['code'];?>',orgid:'<?=$prtl['organization_id'];?>'} );
	<?php
	endforeach;
	?>
	//-------------------------------------------------
	
	$(function(){
		callChangeOrg();
		$("#orgid").on('change', callChangeOrg);
		$("#portals").on('change', callChangePortals);
		$("#user").on('change', function(){ $("#portals").focus(); });
		$("#btnOriginal, #btnTest").removeClass('btn-active').addClass('btn-default').prop('disabled', true);
		setUp();
		$("#art, #frequency").on("keydown", function(key){
			switch(key.keyCode){
				case 110:
					if($(this).attr('id')=='frequency'){ return false; }
					if($(this).val().trim().includes(".")){ return false; }
				case   8:
				case   9:
				case  35:
				case  36:
				case  37:
				case  39:
				case  46:
					
				case  48:
				case  49:
				case  50:
				case  51:
				case  52:
				case  53:
				case  54:
				case  55:
				case  56:
				case  57:
					
				case  96:
				case  97:
				case  98:
				case  99:
				case 100:
				case 101:
				case 102:
				case 103:
				case 104:
				case 105:
					
				case 116:
					return;
				default:
					return false;
			}
	   });
	});
	
	//-------------------------------------------------
	function setUp(){
		<?php
		if(file_exists("../storage/logs/monitoring.ini")):
		$ini = parse_ini_file("../storage/logs/monitoring.ini");
		foreach($ini as $key=>$val):
		?>
		$("#<?=$key;?>").val("<?=$val;?>");
		<?php
		endforeach;
		?>
		$("#orgid").val("<?=$ini['orgid'];?>").change();
		$("#user").val("<?=$ini['user'];?>").change();
		$("#portals").val("<?=$ini['portals'];?>").change();
		if(("<?=$ini['portalcode'];?>").substr(0,1)!=3){ setPortalType($("#btnOriginal"),'originalMode'); }
		<?php
		endif;
		?>
	}
	//-------------------------------------------------
	function callChangeOrg(){
		let orgid = $("#orgid").val();
		
		$("#user, #portals").prop('disabled', true);
		$("#user option, #portals option").remove();
		$("#user, #portals").append('<option value="">Select ...</option>');

		if(orgid==''){ return; }
		//---------------------------------------------
		for(let i in users){
			if(users[i].orgid==orgid){ $("#user").append('<option value="'+users[i].email+'">'+users[i].email+'</option>'); }
		}
		$("#user").prop('disabled', false).focus();
		//---------------------------------------------
		for(let i in portals){
			if(portals[i].orgid==orgid){
				$("#portals").append('<option value="'+portals[i].code+'">('+portals[i].code+') '+portals[i].name+'</option>'); 
			}
		}
		$("#portals").prop('disabled', false);
	}
	//-------------------------------------------------
	function callChangePortals(){
		$("#btnOriginal, #btnTest").removeClass('btn-info').addClass('btn-default').prop('disabled', false);
		$("#originalMode").val($("#portals").val().trim().substr(0,1));
		setPortalType($("#btnTest"),'testMode')
	}
	//-------------------------------------------------
	function setPortalType(obj, id){
		if($("#portals").val().trim()==''){ $("#portalcode").val(''); return; }
		let portalCode = $("#portals").val().trim().substr(1,5);
		$("#portalcode").val($("#"+id).val()+portalCode);
		$("#btnOriginal, #btnTest" ).attr('class', 'btn btn-default');
		$(obj).removeClass('btn-default').addClass('btn-info');
	}
	//-------------------------------------------------
	function emailIsValid(email){
		let pattern = /^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/; 
		//let pattern = /^[0-9A-Za-z\s\-\_\.]+@[a-zA-Z0-9]+?\.[a-zA-Z]{2,3}$/; 
		return pattern.test(email);
	}
	//-------------------------------------------------
	function updateData(){
		$("#msgBox").html("");
		let data = {};

		data.email = $("#email").val().trim();
		if( data.email=="" ){
			$("#msgBox").html("<b style='color:red;'>Invalid Notification email(s).</b>");
			$("#email").focus();
			return;
		}
		let emails = data.email.split(',');
		for(let i in emails){
			emails[i] = emails[i].trim();
			if(!emailIsValid(emails[i])){
				$("#msgBox").html("<b style='color:red;'>Invalid Notification email("+emails[i]+").</b>");
				$("#email").focus();
				return;
			}
		}
		data.email = emails.join(',');
		$("#email").val(data.email);

		data.orgid = $("#orgid").val();
		if( data.orgid=='' ){
			$("#msgBox").html("<b style='color:red;'>Select organization.</b>");
			$("#orgid").focus();
			return;
		}

		data.user = $("#user").val();
		if( data.user=='' ){
			$("#msgBox").html("<b style='color:red;'>Select user.</b>");
			$("#user").focus();
			return;
		}

		data.portals    = $("#portals").val();
		data.portalcode = $("#portalcode").val();
		if( data.portals=='' ){
			$("#msgBox").html("<b style='color:red;'>Select portal.</b>");
			$("#portals").focus();
			return;
		}
		
		data.frequency =$("#frequency").val().trim();
		if( data.frequency=='' ){ data.frequency=5; }
		data.frequency = parseInt( data.frequency );
		if( data.frequency<1 || data.frequency >1440 ){
			$("#msgBox").html("<b style='color:red;'>Inavlid value.</b>");
			$("#frequency").focus();
			return;
		}
		$("#frequency").val(data.frequency);

		data.art =$("#art").val().trim();
		if(data.art=='' || isNaN(data.art)){
			data.art=1;
			$("#art").val(data.art);
		}
		data.uu_text =$("#uu_text").val().trim();
		if(data.uu_text==''){
			$("#msgBox").html("<b style='color:red;'>Invalid user utterance.</b>");
			$("#uu_text").focus();
			return;
		}
		
		$("#msgBox").html("<i class='fa fa-refresh fa-spin' style='margin:0 50%'></i>");
		$.ajax({
			method  :'put',
			url     : "<?=env('API_URL');?>/api/sysamin/monitorig/save",
			data    : data,
			dataType: 'json',
			success: 
				function( response ){
					if(response.result==0){ $("#msgBox").html("<b style='color:green;margin:10px 5px'>"+response.msg+"</b>"); }
					else{ $("#msgBox").html("<b style='color:red;'>"+response.msg+"</b>"); }
					 $('#monitorLog').attr("src", $('#monitorLog').attr("src"));
				},
			error:
				function(xhr, textStatus, errorThrown ){
					let msg = "";
					if(xhr.status==400){ msg = xhr.responseJSON.message; }
					else{ msg = errorThrown; }
					$("#msgBox").html("<b style='color:red;'>"+msg+"</b>");
				}
		});
	}
	//-------------------------------------------------
	function clearLog(){
		$("#msgBox").html("<i class='fa fa-refresh fa-spin' style='margin:0 50%'></i>");
		$.ajax({
			method  :'put',
			url     : "<?=env('API_URL');?>/api/sysamin/monitorig/clear",
			data    : {},
			dataType: 'json',
			success: 
				function( response ){
					if(response.result==0){ $("#msgBox").html(""); }
					else{ $("#msgBox").html("<b style='color:red;'>"+response.msg+"</b>"); }
					 $('#monitorLog').attr("src", $('#monitorLog').attr("src"));
				},
			error:
				function(xhr, textStatus, errorThrown ){
					let msg = "";
					if(xhr.status==400){ msg = xhr.responseJSON.message; }
					else{ msg = errorThrown; }
					$("#msgBox").html("<b style='color:red;'>"+msg+"</b>");
				}
		});
	}
	//-------------------------------------------------
</script>
