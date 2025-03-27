<head>
	<title>Login</title>
	<meta http-equiv="refresh" content="5400">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script type="text/javascript" src="{{ asset('public/js/jquery.js') }}?new"></script>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>

	<style>
		body {
			background: #06182c ;
		}

		.loginForm {
			position: absolute;
			width: 90%;
			max-width: 400px;
			height: auto;
			min-height: 420px;
			max-height: 470px;
			margin: auto;
			top: 0;
			bottom: 0;
			right: 0;
			left: 0;
			padding: 20px;
			border-radius: 2px;
		}
		.loginForm form{ margin: 0; }
		.login-error {
			position: absolute;
			left: 0;
			right: 0;
			width: 400px;
			max-width: 90%;
			margin: auto;
			top: -365px;
			bottom: 0;
			height: fit-content;
		}

		.submit {
			margin-top: 30px;
			width: 49%;
			height: auto;
			font-family: arial, sans-serif;
		}
		.submitWrapper .submit{ width: 100%; }
		.logo img { width: 50%; }

		.forgotWrapper{ display: none; }

		.submitWrapper {
			position: absolute;
			left: 20px;
			right: 20px;
			bottom: 20px;
		}

		.forgotLink{ float: right; }

		.forgotLink a{ color: #9e9e9e; }
		.forgotLink a:hover{ color: red; }

		.backToLogin { margin-bottom: 20px; margin-left: -4px; font-size: 22px; }
		.backToLogin a{ color: #9e9e9e; }
		.backToLogin a:hover{ color:red; }

		.resetWrapper{ display: none; }

		@media (max-height: 600px) {
			.login-error {
				top: 0;
				margin-top: 0;
				width: 100%;
				max-width: 100%;
				border-radius: 0;
				z-index: 10000;
			}
		}
		p.rememberMe{ position: absolute; bottom: 70px; left: 20px; right: 20px; }
		div.input-field{ margin-top: 0; }
		p.rememberMe>label:hover{ color: red; }
		#orgNames
			{ max-height:110px;overflow:auto; }
		#orgList ul
			{ max-height:70px;overflow:auto; margin-top:0; }
		#orgNames li:hover,
		#orgList li.active:hover
			{ color: red; cursor: pointer; }
		#orgNames i.material-icons,
		#orgList i.material-icons
			{ margin-right: 5px;vertical-align: bottom; }
		.fotter{
			color: burlywood;
			position: fixed;
			bottom: 10px;
			right: 10px;
			font-size: 12px;			
		}
	</style>
</head>
<body>
	<div class="loginForm white">
		<div class="logo"><img src="/public/dist/images/logo_mfa.jpg" /></div>
		<br/>
		<div class="loginWrapper">
			<form onSubmit="return false;">
				<div>
					<div class="input-field part1Login">
						<input id="emailLogin" type="text" class="validate"  autofocus placeholder="Email" value="" />
			        </div>

					<div class="input-field part2Login" style="display: none">
						<label style="position: static;">Select</label>
						<ul id="orgNames"></ul>
			        </div>

					<div class="input-field part3Login" style="display: none">
						<input id="orgName" type="text" class="validate" disabled/>
						<input id="orgId" type="hidden" />
						<input type="password" id="passLogin" class="validate" autocomplete="new-password" placeholder="Password" />
			        </div>

					<p class="rememberMe">
						<input type="checkbox" id="remember" style="display: none" />
						<label for="remember" style="display: none">Remember Me</label>
						<span class="forgotLink"><i><a href="#">Forgot password?</a></i></span>
					</p>
					<div class="submitWrapper">
						<input type="hidden" id="loginStep" value="1"/>
						<button type="button" id="backLogin"   class="submit waves-effect waves-light btn red">back</button>
						<button type="button" id="submitLogin" class="submit waves-effect waves-light btn blue">Next</button>
					</div>
				</div>
			</form>
		</div>
		<div class="forgotWrapper">
			<div class="backToLogin"><a href="#"><i class="material-icons">arrow_back</i></a></div>
			<div>Please enter your email address below. We will send a message containing your <b>Username</b> and a link to reset your <b>Password</b>.</div>
			<br>
			<form id="forgotPasswordForm">
				<div class="input-field">
					<input id="userEmail" type="email" class="validate">
					<label for="userEmail">Email</label>
				</div>
				<div id="orgList">
					<i class="fa fa-spin fa-spiner"></i>
				</div>
				<div class="submitWrapper">
					<button type="submit" id="submitForgot" class="submit waves-effect waves-light btn blue">Send</button>
				</div>
			</form>
		</div>
		<div class="resetWrapper">
			<form id="resetForm">
				<div class="input-field">
					<input id="newPass" type="password" class="validate">
					<label for="newPass">New Password</label>
				</div>
				<div class="input-field">
					<input id="newPassConfirm" type="password" class="validate">
					<label for="newPassConfirm">Confirm Password</label>
				</div>
				<div class="submitWrapper">
					<button type="submit" id="submitReset" class="submit waves-effect waves-light btn blue"><h5>Save</h5></button>
				</div>
			</form>
		</div>
	</div>
	<div class="fotter" style="display: none">Version 0.0</div>
</body>

<script type="text/javascript">
if(<?=($hasResetError)? 'true': 'false';?>){
	Materialize.toast("<?=$msg;?>", 5000, 'red');
}
else if(<?=($isPassReset)? 'true':'false';?>){
	$(".loginWrapper").hide();
	$(".resetWrapper").show();
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
var forgotPasswordFormOrg = -1;
$(function(){
	$("#submitLogin").on('click', function(e){ callLogin(); });
	$("#backLogin"  ).on('click', function(e){ callBackLogin(); });
	
	$("#emailLogin" ).on('keypress', function(e){ if(e.keyCode==13){ callLogin_1(); } });
	$("#passLogin"  ).on('keypress', function(e){ if(e.keyCode==13){ callLogin_3(); } });
	setStep_1();
	
//	$("#passLogin").on('keypress', function(e){ if(e.keyCode==13){  } });
//	$("#orgLogin").material_select();
//	$(".select-dropdown").off("focus");

	$("#forgotPasswordForm").submit(function(e){
		e.preventDefault();
		var email = $("#userEmail").val().trim();
		if(email.length == 0) {
			Materialize.toast("Please enter your email", 5000, 'red');
			return;
		}
		$.ajax({
			url: "<?=env('API_URL');?>/api/forgot_pass",
			type: 'post',
			data:{
				email: email,
				orgId: forgotPasswordFormOrg
			},
			beforeSend: function(){
				$("#userEmail, #submitForgot, #orgList li").prop('disabled', true );
				$("#submitForgot").text('Wait');
				$("#orgList li").removeClass('active');
			},
			complete  : function(){
				$("#submitForgot, #orgList li").prop('disabled', false);
				$("#submitForgot").text('Send');
				forgotPasswordFormOrg = -1;
				$("#orgList li").addClass('active');
			},
			success: function(res){
				$("#userEmail").prop('disabled', false);
				if(res.result == 0){
					if(res.orgList==null){ Materialize.toast(res.msg, 5000, 'green'); }
					else{
						$("#userEmail").prop('disabled', true );
						$("#orgList").html
							("<h6>You are a user in more than one organization, please select the organization that applies.</h6>");
						$("#orgList").append("<ul>");
						for(let i in res.orgList){ 
							let id   = res.orgList[i].orgId;
							let name = res.orgList[i].orgName;
							$("#orgList ul")
								.append("<li data-id='"+id+"' class='active'><i class='material-icons'>check_box_outline_blank</i>"+name+"</li>");
						}
						$("#orgList li").on('click', function(){
							if($(this).attr('class')!='active'){ return; }
							$("#orgList li>i").remove();
							forgotPasswordFormOrg = $(this).data('id');
							$("#orgList li").prepend("<i class='material-icons'>check_box_outline_blank</i>");
							$(this).find("i").html('done');
						});
					}
				}else{
					Materialize.toast(res.msg, 5000, 'red');
				}
			},
			error: function(e){
				$("#userEmail").prop('disabled', false);
				Materialize.toast("Error", 5000, 'red');
			}
		})
	})

	$("#resetForm").submit(function(e){
		e.preventDefault();

		if($("#newPass").val() != $("#newPassConfirm").val()){
			Materialize.toast("Passwords do not match", 5000, 'red');
			return;
		}

		if($("#newPass").val().length < 4){
			Materialize.toast("Password must be at least 4 characters", 5000, 'red');
			return;
		}

		var data = {
			userID: "<?=isset($userID)? $userID:'';?>",
			oldPass: "<?=isset($userPass)? $userPass:'';?>",
			newPass: $("#newPass").val()
		}

		$.ajax({
			url: "<?=env('API_URL');?>/api/pass_change",
			type: 'post',
			data: data,
			success: function(res){
				if(res.result == 0){
					Materialize.toast("Password successfully changed", 5000, 'green');
					$(".resetWrapper").hide();
					$(".loginWrapper").fadeIn();
				}else{
					Materialize.toast(res.msg, 5000, 'red');
				}
			},
			error: function(e){
				Materialize.toast("Error", 5000, 'red');
			}
		})
	})

	$(".forgotLink a").click(function(e){
		e.preventDefault();
		$(".loginWrapper").hide();
		$(".forgotWrapper").fadeIn();
		$("#userEmail").focus();
	})

	$(".backToLogin a").click(function(e){
		e.preventDefault();
		$(".loginWrapper").fadeIn();
		$(".forgotWrapper").hide();
		setStep_1();
		$("#userEmail").prop('disabled', false);
		$("#userEmail").val('');
		$("#orgList").html("");
		forgotPasswordFormOrg = -1;
	})
});
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
function callLogin(){
	switch($("#loginStep").val().trim()){
		case '1': callLogin_1(); break;
		case '2': setStep_3(3);  break;
		case '3': 
		case '4': callLogin_3(); break;
	}
}
function callLogin_1() {
	$("div.part2Login").hide();
	$("div.part3Login").hide();
	$("#orgNames li").remove();
	$("#orgId").val('');
	$("body > .login-error").remove();

	var data = {};
	data.email = $("#emailLogin").val().trim();
	data._token    = '{{ csrf_token() }}';

	$("#submitLogin").prop('disabled', true);
	$.post('/login/emailisvalid',data,
		function(retVal){
			$("#submitLogin").prop('disabled', false);
			if(retVal.result==0){
				if(retVal.data.length==1){
					$("#orgId").val(retVal.data[0].id);
					$("#orgName").val(retVal.data[0].name);
					setStep_3(4);
				}else{
					setStep_2();
					for(let i in retVal.data){ 
						let id   = retVal.data[i].id;
						let name = retVal.data[i].name;
						$("#orgNames")
							.append("<li data-id='"+id+"'><i class='material-icons'>check_box_outline_blank</i>"+name+"</li>");
					}
					$("#orgNames li").on('click', function(){
						$("#orgNames li>i").remove();
						$("#orgId").val($(this).data('id'));
						$("#orgName").val($(this).text());
						$("#orgNames li").prepend("<i class='material-icons'>check_box_outline_blank</i>");
						$(this).find("i").html('done');
					});
				}
			}else{
				Materialize.toast(retVal.msg, 5000, 'red');
				$("#emailLogin").focus();
			}
		}
	).fail(
		function(xhr){
			$("#submitLogin").prop('disabled', false);
			if(xhr.status==404){ Materialize.toast("Error: Invalid request", 5000, 'red'); }
			else{ 
				if(xhr.status==419){ Materialize.toast("Error: Your browser couldn’t create a secure cookie or couldn’t access that cookie to authorize your login.", 5000, 'red'); }
				else{ Materialize.toast("Error: " +xhr.responseJSON.message, 5000, 'red'); }
			}
		}
	);
}
function callLogin_3() {
	$("body > .login-error").remove();

	var data = {};
	data.userLogin = $("#emailLogin").val().trim();
	data.passLogin = $("#passLogin" ).val().trim();
	data.orgLogin  = $("#orgId"      ).val().trim();
	data._token    = '{{ csrf_token() }}';

	$("#submitLogin").prepend('<i style="margin-right:10px" class="fa fa-refresh fa-spin"></i>');
	$("#submitLogin, #backLogin").prop('disabled', true);
	$.post('/login/isvalid',data,
		function(retVal){
			$("#submitLogin, #backLogin").prop('disabled', false);
			$("#submitLogin>i").remove();
			if(retVal.trim()==1){ window.location='/mfa_code'; }
			else{
				if(retVal.trim()==2){ window.location='/'; }
				else{ Materialize.toast("password is incorrect on this organization!", 5000, 'red'); }
			}
		}
	).fail(
		function(xhr){
			$("#submitLogin, #backLogin").prop('disabled', false);
			$("#submitLogin>i").remove();
			if(xhr.status==404){ Materialize.toast("Error: Invalid request", 5000, 'red'); }
			else{ Materialize.toast("Error: " +xhr.responseJSON.message, 5000, 'red'); }
		}
	);
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
function setStep_1(){
	$("#loginStep").val(1);
	$("#backLogin").hide();
	$("#submitLogin").css('width','100%');
	$(".part2Login, .part3Login").hide();
	$(".part1Login").show();
	$("#emailLogin").prop('disabled', false);
	$("#emailLogin").val('');
	$("#emailLogin").focus();
	$("#submitLogin").text("next");
}
function setStep_2(){
	$("#loginStep").val(2);
	$("#emailLogin").prop('disabled', true);

	$("#submitLogin, #backLogin").css('width','49%');
	$("#submitLogin").text("next");
	$("#backLogin").show();
	$(".part3Login").hide();

	$("div.part2Login").show();
}
function setStep_3(flag){
	if($("#orgId").val().trim()==''){ return; }
	$("#emailLogin").prop('disabled', true);
	$("div.part2Login").hide();
	$("div.part3Login").show();
	$("#passLogin").focus();
	$("#submitLogin, #backLogin").css('width','49%');
	$("#backLogin").show();
	$("#submitLogin").text("Login");
	$("#loginStep").val(flag);
}
function callBackLogin(){
	switch($("#loginStep").val().trim()){
		case '1': 
		case '2': 
		case '4': setStep_1(); break;
		case '3': setStep_2(); break;
	}
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
</script>
