<style>
.modal-backdrop {
    visibility: hidden !important;
}
.modal.in {
    background-color: rgba(0,0,0,0.5);
}
	
	#showError{
		position: fixed;
		top: 10px;
		left: 10px;
		color: #fff;
		background: #d25c5c;
		z-index: 9999;
		min-width: 150px;
		width: auto;
		padding: 8px 15px;
		border: 1px dotted #fff;
		border-radius: 8px;
		box-shadow: 0 0 0 3px #d25c5c;
		display:none;
	}

	#editOrganization, #addOrganization {
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

	#editOrganization > form, #addOrganization > form {
		position: absolute;
		margin: auto;
		top: 0;
		bottom: 0;
		right: 0;
		left: 0;
		width: 280px;
		height: 700px;
		width: fit-content;
		height: fit-content;
		background: white;
		padding: 15px;
	}

	#editOrganization.show, #addOrganization.show{ display: block; }
	#editOrganization > form input, #addOrganization > form input{ width: 250px; }

	.react-bs-table-bordered, .react-bs-container-body{ height: auto !important; }
	.row-actions{ text-align: center; }
	.row-actions > a:first-child{ padding-right: 10px; }

	#organization th, #organization td{ font-size:13px; }

	#organization td:nth-child(1){ text-align:right; }

	#organization th:nth-child(2),
	#organization td:nth-child(2){ width:30% !important; max-width:30px;  }

	#organization td:nth-child(5){ text-align:center; }
	#organization td:nth-child(6){ text-align:center; }
	
	#organization th:last-child,
	#organization td:last-child{ width:40px !important;font-size:11px; }
	
	#organization .form-group.half{ width:49%; min-width:200px; display:inline-block; }
	#organization .form-group.half.left{ margin-right:1%; }
	#organization .form-group.half.right{ margin-left:1%; }

	#organization .form-group.third{ width:32%; min-width:115px; display:inline-block; }
	#organization .form-group.third.left{ margin-right:1%; }
	#organization .form-group.third.center{ margin-right:1%; margin-left:1%; }
	#organization .form-group.third.right{ margin-left:1%; }
	
	#organization textarea.form-control.fixed{
		width:100%; min-width:100%; max-width:100%;
		height:80px; min-height:80px; max-height:80px;  
	}
/*	.action-form input[type='submit']{ margin-top:5px; }*/
	textarea.fit{
		max-height: 120px;
		min-height: 120px;
		height: 120px;
		width: 100%;
		max-width: 100%;
		min-width: 100%;
	}
	.clickable{ color: blue; }
	.clickable:hover{ color: red; cursor: pointer; }
	.form-control.email{
		width: calc( 100% - 30px );
		display: inline-block;
		margin-right: 15px;
	}
	.col-send_chat_format .btn-primary,
	.col-chat_logs_sent .btn-primary{
		color: #44596c;
		background: #e6e6e6;
	}
	.col-send_chat_format .btn-primary.active,
	.col-chat_logs_sent .btn-primary.active{
		color: #f6fafd;
		background: #2a94d6;
	}
	.emailSenderAddress{
		width: 100%;
		display: block;
		background: #ed3f46c4;
		padding: 10px;
		margin: 10px 0px -10px;
		color: #ffffff;
		border-radius: 5px;
	}
	.emailSenderAddress>b:hover{
		box-shadow: 0px 0px 15px #000;
		cursor: pointer;
		padding: 10px;
		background: red;
	}
	
	#orgFlags{
		width: 100%;
		border: 1px solid #ddd;
	}
	#orgFlags th{ text-align: center; padding: 10px 0; font-size:13px !important; background: #eee; border-bottom: 1px solid #ddd; }
	#orgFlags th:nth-child(1){ width:30% !important; border-right: 1px solid #bbb; }
	#orgFlags th:nth-child(2){ width:10% !important; max-width:40px !important; border-right:1px solid #bbb; }
	#orgFlags th:nth-child(3){ width:60% !important; }
	
	#orgFlags td{ padding: 8px 2px; text-align:left !important; border-bottom: none; }
	#orgFlags td:nth-child(1){ border:1px solid #eee; border-left:none; }
	#orgFlags td:nth-child(2){ border:1px solid #eee; border-left:none; text-align:center !important; vertical-align:middle; }
	#orgFlags td:nth-child(3){ border:1px solid #eee; border-left:none; }
	.orgFlags.col-MultiLanguage{ height: 51px; }
	
	#editItem > form, #addItem > form{max-height:650px; overflow:auto; }
</style>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<script type="text/javascript">
	//-----------------------------------------------------------
	var apiURL  = "<?=env('API_URL');?>";
	var orgID   = "<?=$orgID;?>";
	var levelID = "<?=$levelID;?>";
	var defaultPersonaId = 0;
	var table;
	var MAIL_FROM_ADDRESS = '<?=env('MAIL_FROM_ADDRESS', '');?>';
	//-----------------------------------------------------------
	function copyEmail2Clipboard(obj){
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val($(obj).text()).select();
		document.execCommand("copy");
		$temp.remove();
		table.showSuccess("copied to clipboard")
	}
	//-----------------------------------------------------------
</script>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->

<?php if($orgID==0): ?>
<div id="organization"><i class="fa fa-gear fa-spin fa-5x" style="margin:auto"></i></div>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
		table.createTable('organization');
</script>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
<?php else: ?>
<div id="organization" style="min-height:75vh">
	<?php $orgDB = new \App\Organization(); ?>
	<h2><?=$orgDB->getName($orgID);?></h2>
	<div style="display:inline-block;vertical-align:top;max-width:550px;">
		<div class="input-group m-b-1" style="">
			<span class="input-group-addon">Default Persona</span>
			<?php
			$defPersTBL = new \App\OrganizationPersonality();
			$orgPers    = $defPersTBL->where("organizationId", $orgID)->where("is_default", 1)->first();
			$orgPerId  = "";
			if($orgPers!=null){ $orgPerId = $orgPers->personalityId; }
			/*<input class="form-control" id="defaultPersona" value="<?=$orgPerId;?>" disabled  />*/
			?>
			<select class="form-control" id="defaultPersona" onChange="setDefaultPersona($(this).val())"></select>
		</div>
		<br/>
		<?php
		$usersTBL = new \App\User();
		$orgAdmins = $usersTBL->where("isAdmin", "=", 1)->where("orgID", "=", $orgID)->get();
		if(!$orgAdmins->isEmpty()){ 
			?>
			<div class="form-group">
				<label>Admin(s)</label>
				<ul>
				<?php foreach($orgAdmins as $orgAdmin){ ?>
					<li class="form-control" disabled style="margin-bottom:5px;"><?=$orgAdmin->userName;?> <?=$orgAdmin->email;?></li>
				<?php } ?>
				</ul>
			</div>
			<?php
		}
		?>
		<div style="margin-top:20px;">
			<?php $orgData = $orgDB->find($orgID); ?>
			
			<div class="form-group">
				<label>Organization Description</label>
				<p class="form-control" style="height:auto;" disabled><?=$orgData->Descripiton;?></p>
			</div>
			<div style="width:100%; height:360px;">
				<label>Value Added Services</label>
				<table id="orgFlags">
					<thead>
						<th>Service</th>
						<th>Status</th>
						<th>Details</th>
					</thead>
					<tbody>
						<tr>
							<td>Billable</td>
							<td><?=(($orgData->Billable==1) ?'<b>On</b>' :'Off');?></td>
							<td></td>
						</tr>
						<tr>
							<td>RPA</td>
							<td><?=(($orgData->RPA==1) ?'<b>On</b>' :'Off');?></td>
							<td></td>
						</tr>
						<tr>
							<td>KaaS 3PB</td>
							<td><?=(($orgData->KaaS3PB==1) ?'<b>On</b>' :'Off');?></td>
							<td></td>
						</tr>
						<tr>
							<td>Has Live Agent</td>
							<td><?=(($orgData->hasLiveAgent==1) ?'<b>On</b>' :'Off');?></td>
							<td></td>
						</tr>
						<tr>
							<td>Message of the Day</td>
							<td><?=(($orgData->MessageOfTheDay==1) ?'<b>On</b>' :'Off');?></td>
							<td></td>
						</tr>
						<tr>
							<td>RAG</td>
							<td><?=(($orgData->RAG==1) ?'<b>On</b>' :'Off');?></td>
							<td></td>
						</tr>
						<tr>
							<td>Multi Language</td>
							<td><?=(($orgData->MultiLanguage==1) ?'<b>On</b>' :'Off');?></td>
							<td></td>
						</tr>
						<tr>
							<td>Multi-Factor Authentication</td>
							<td><?=(($orgData->mfa==1) ?'<b>On</b>' :'Off');?></td>
							<td></td>
						</tr>
						<tr>
							<td>Feedback</td>
							<td><?=(($orgData->feedback==1) ?'<b>On</b>' :'Off');?></td>
							<td></td>
						</tr>
					</tbody>
				</table>
			</div>
			
<?php /*BHR*/ ?>
			<div class="panel panel-default autoEmail">
				<div class="panel-heading" style="background:#edeff0; font-weight:normal; font-size:15px;">
					Auto Email
					<span style="float:right;">
						<label>
							Email Chats: <span class="clickable" id="AutoOnOff"><?=(($orgData->AutoOnOff==1) ?'On' :'Off');?></span>
						</label>
					</span>
				</div>
				<div class="panel-body" style="border: 1px solid #64768740;">
					<div class="form-group">
						<label>Email Subject</label>
						<input class="form-control" value="<?=$orgData->EmailTheme;?>" id="emailSubject" />
					</div>

					<div class="form-group">
						<label>Email Body</label>
						<textarea class="form-control fit" id="emailBody"><?=$orgData->EmailBody;?></textarea>
					</div>
					
					<div class="form-group">
						<label>Footer</label>
						<input class="form-control" value="<?=$orgData->Footer;?>" id="footer"/>
					</div>
					
					<div class="form-group">
						<label>Auto Email Address(es)</label>
						<i class="fa fa-plus clickable" style="margin-left: 5px;" onClick="addEmail()"></i>
						<?php
						$tmpEmails = explode(";", $orgData->AutoEmail);
						?>
						<ul id="autoEmailAddress">
						<?php foreach($tmpEmails as $tmpEmail){ if(trim($tmpEmail)=='') continue; ?>
							<li style="margin-bottom:5px; list-style:none;">
								<input class="form-control email" value="<?=$tmpEmail;?>"/>
								<i class="fa fa-trash clickable" onClick="eraseThis(this)"></i>
							</li>
						<?php } ?>
						</ul>
					</div>
					
					<div class="panel panel-default" style="border-color:#eee; margin-top:30px;padding:10px 0;" id="autoEmailOptions">
						<div class="panel-body">
							<div>
								<input type="hidden" id="send_chat_format" value="<?=$orgData->send_chat_format;?>" />
								<div class="btn-group col-send_chat_format" role="group" >
									<button type="button" class="btn btn-primary send_chat_format" value="csv">CSV</button>
									<button type="button" class="btn btn-primary send_chat_format" value="pdf">PDF</button>
									<!--
									<button type="button" class="btn btn-primary send_chat_format" value="json">JSON</button>
									<button type="button" class="btn btn-primary send_chat_format" value="xml">XML</button>
									-->
								</div>
							</div>

							<div style="margin-top:10px;">
								<input type="hidden" id="chat_logs_sent" value="<?=$orgData->chat_logs_sent;?>" />
								<div class="btn-group col-chat_logs_sent" role="group">
									<button type="button" class="btn btn-primary chat_logs_sent" value="1">
										As individual emails
									</button>
									<button type="button" class="btn btn-primary chat_logs_sent" value="2">
										As several attachments to one single email
									</button>
								</div>
							</div>
						  
							<div class="emailSenderAddress">
								Please configure your email system to not block Kama-DEI emails for spam.&nbsp;
								The chat-log emails will be sent to you from this email address:&nbsp;
								<b onclick="copyEmail2Clipboard(this)"><?=env('MAIL_FROM_ADDRESS', '');?></b>
							</div>

						</div>
					</div>

					<div class="form-group" align="right">
						<button class="btn btn-success" onClick="saveAutoEmail()">Save</button>
					</div>

				</div>
			</div>
			
		</div>
	</div>
	<img src="/public/dist/images/logo/logo.png" width="200" style="display:inline-block;margin-left:20px;border:1px solid #999;" />
</div>
<script type="text/javascript">defaultPersonaId='<?=$orgPerId;?>';</script>
<script src="/public/js/app.js"></script>
<script type="text/javascript">
	//----------------------------------------------
	function setDefaultPersona(defPerId){
		if(defPerId==''){ return; }
		var data = {};
		data.orgId = orgID;
		data.defaultPersona = defPerId;
		$.ajax({
			url: apiURL+'/api/dashboard/organization/setdefaultpersona/',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(data),
			beforeSend: function(){},
			success: function(res){
				if(res.result == 1){
					$(this).val('<?=$orgPerId;?>');
					table.showError(res.msg); 
				}
			},
			error: function(e){
				if(e.status==404){ table.showError(e.status+": "+e.statusText); }
				else{ table.showError(e.responseJSON.message); }
			}
		});
	}
	//----------------------------------------------
	var AutoOnOff = <?=(($orgData->AutoOnOff==1) ?1 :0);?>;
	
	$(function(){
		$("#AutoOnOff").on('click', function(){
			if(AutoOnOff==1){ AutoOnOff=0; }
			else{ AutoOnOff=1; }
			setAutoEmail();
		});
		setAutoEmail();
	});
	
	function setAutoEmail(){
		if(AutoOnOff==0){
			$("#AutoOnOff"         ).text("Off");
			$(".autoEmail i"       ).hide();
			$(".autoEmail input"   ).prop('disabled', true);
			$(".autoEmail textarea").prop('disabled', true);
			$("#autoEmailOptions"  ).hide();
		}else{
			$("#AutoOnOff"         ).text("On");
			$(".autoEmail i"       ).show();
			$(".autoEmail input"   ).prop('disabled', false);
			$(".autoEmail textarea").prop('disabled', false);
			$("#autoEmailOptions"  ).show();
		}
		
		let tmp = $("#send_chat_format").val();
		$(".send_chat_format").removeClass('active');
		$(".send_chat_format[value='"+tmp+"']").addClass('active');

		tmp = $("#chat_logs_sent").val();
		$(".chat_logs_sent").removeClass('active');
		$(".chat_logs_sent[value='"+tmp+"']").addClass('active');
	}
	
	function eraseThis(obj){
		$(obj).parent().remove();
	}
	
	function addEmail(){
		$("#autoEmailAddress")
			.append(
				'<li style="margin-bottom:5px; list-style:none;">'+
					'<input class="form-control email" value=""/>'+
					'<i class="fa fa-trash clickable" onClick="eraseThis(this)"></i>'+
				'</li>'
			);
	}
	
	function saveAutoEmail(){
		let emailRegExp = /\S+@\S+\.\S+/;
		let sendData = {};
		sendData.orgID      = "<?=$orgID;?>";
		sendData.AutoOnOff  = AutoOnOff;
		sendData.EmailTheme = $("#emailSubject").val().trim();
		sendData.EmailBody  = $("#emailBody").val().trim();
		sendData.Footer     = $("#footer").val().trim();
		sendData.AutoEmail  = "";
		sendData.send_chat_format = $("#send_chat_format").val().trim();
		sendData.chat_logs_sent   = $("#chat_logs_sent"  ).val().trim();
		
		$("#autoEmailAddress li input").each(function(){
			let tmp = $(this).val().trim();
			if(tmp!='' && emailRegExp.test(tmp)){ sendData.AutoEmail+= tmp+";"; }
			else{ $(this).val(""); }
		})
		
		$.ajax({
			url: apiURL+'/api/dashboard/organization/edit/<?=$orgID;?>/<?=$orgID;?>',
			type: 'put',
			headers: {
				'Accept': 'application/json',
				'Content-Type': 'application/json'
			},
			data: JSON.stringify(sendData),
			beforeSend: function(){},
			success: function(res){
				if(res.result == 1){
					table.showError(res.msg); 
				}else{
					table.showSuccess("Data saves");
				}
			},
			error: function(e){
				if(e.status==404){ table.showError(e.status+": "+e.statusText); }
				else{ table.showError(e.responseJSON.message); }
			}
		});
	
	}
	//----------------------------------------------
</script>
<?php endif; ?>
<!-- ---------------------------------------------------------------------------------------- -->
<!-- ---------------------------------------------------------------------------------------- -->
