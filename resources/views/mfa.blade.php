<head>
	<title>Multi-Factor Authentication</title>
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
		#resendVerify{ float:right; font-size:80%; padding:0; width:65%; margin-top:5px; }
		#resendVerify:hover{ color:yellow; }
		
		#timeLeft{ display:block; width:100%; text-align:right; }
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
						<input id="mfaCode" type="text" class="validate"  autofocus placeholder="verification code" value="" maxlength="6" />
						 <span id="timeLeft">
							 <b>{{date("i:s", 0)}}</b>
							 &nbsp;
							 left (min:sec) 
						</span>
<?php
$user = \App\User::find(\Session('userID'));
$maxTime = 0;
if($user!=null){
	$maxTime = $user->mfa_valid_until-time();
}
if($maxTime<=0){ $maxTime=0; }
$maxTime = date("i:s", $maxTime);
?>
						<button type="button" id="resendVerify" class="submit waves-effect waves-light btn blue" onClick="resendVerifyCode()">
							RESEND VERIFICATION CODE
						</button>
			        </div>

					<div class="submitWrapper">
						<button type="button" id="verifyCode" class="submit waves-effect waves-light btn blue">Verify</button>
						<button type="button" id="backToLogin" class="submit waves-effect waves-light btn dark">Back to login</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="fotter" style="display: none">Version 0.0</div>
</body>

<script type="text/javascript">
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
var forgotPasswordFormOrg = -1;
$(function(){
	$("#backToLogin").on('click', function(e){ window.location="/login"; });
	
	$("#verifyCode").on('click'   , function(e){ callVerify(); });
	$("#mfaCode"   ).on('keypress', function(e){ if(e.keyCode==13){ callVerify(); } });
	
	countDown();
});
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
let intervalID = null;
let startS = "2000-01-01T00:{{$maxTime}}";
let endS   = "2000-01-01T00:00:00";
function countDown(){
	let start = Date.parse(startS);
	let end   = Date.parse(endS  );
	$("#resendVerify").prop('disabled', true);
	$("#timeLeft").show();
	intervalID = setInterval(function(){
//console.log(dateString);
		start-=1000;
		if(start<=end){
			clearInterval(intervalID);
			$("#timeLeft>b").html('00:00');
			$("#resendVerify").prop('disabled', false);
			$("#timeLeft").hide();
			return;
		}
		let a = new Date(start);
		let min = a.getMinutes();
		if(min<10){ min="0"+min; }
		let sec = a.getSeconds();
		if(sec<10){ sec="0"+sec; }
		$("#timeLeft>b").html(min + ':' + sec);
	}, 1000); 
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
function callVerify() {
	var data = {};
	data.mfaCode = $("#mfaCode").val().trim();
	data._token  = '{{ csrf_token() }}';

	if(data.mfaCode.length<6){
		Materialize.toast("invalid verification code", 5000, 'red');
		$("#mfaCode").focus();
		return;
	}
	
	$("#verifyCode").prepend('<i style="margin-right:10px" class="fa fa-refresh fa-spin"></i>');
	$("#resendVerify, #verifyCode, #backToLogin").prop('disabled', true);
	$.post('/login/multi_factor_authentication',data,
		function(retVal){
			$("#resendVerify, #verifyCode, #backToLogin").prop('disabled', false);
			$("#verifyCode i").remove();
			if(retVal.result==0){ window.location="/"; }
			else{
				Materialize.toast(retVal.msg, 5000, 'red');
				$("#mfaCode").focus();
			}
		}
	).fail(
		function(xhr){
			$("#resendVerify, #verifyCode, #backToLogin").prop('disabled', false);
			$("#verifyCode i").remove();
			if(xhr.status==404){ Materialize.toast("Error: Invalid request", 5000, 'red'); }
			else{ 
				if(xhr.status==419){ Materialize.toast("Error: Your browser couldn’t create a secure cookie or couldn’t access that cookie to authorize your login.", 5000, 'red'); }
				else{ Materialize.toast("Error: " +xhr.responseJSON.message, 5000, 'red'); }
			}
		}
	);
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
function resendVerifyCode(){
	$("#mfaCode").val('');
	var data = {};
	data._token  = '{{ csrf_token() }}';
	$("#resendVerify").prepend('<i style="margin-right:5px" class="fa fa-refresh fa-spin"></i>');
	$("#resendVerify, #verifyCode, #backToLogin").prop('disabled', true);
	$.post('/login/resend_verify_code',data,
		function(retVal){
			$("#resendVerify, #verifyCode, #backToLogin").prop('disabled', false);
			$("#resendVerify i").remove();
			if(retVal.result==0){
				$("#mfaCode").val('').focus();
				Materialize.toast("Verify code was sent, check your mailbox.", 5000, 'green');
				startS = "2000-01-01T00:"+retVal.remind;
				endS   = "2000-01-01T00:00:00";
				countDown();
				
			}
			else{
				Materialize.toast(retVal.msg, 5000, 'red');
				$("#mfaCode").focus();
			}
		}
	).fail(
		function(xhr){
			$("#resendVerify, #verifyCode, #backToLogin").prop('disabled', false);
			$("#resendVerify i").remove();
			if(xhr.status==404){ Materialize.toast("Error: Invalid request", 5000, 'red'); }
			else{ 
				if(xhr.status==419){ Materialize.toast("Error: Your browser couldn’t create a secure cookie or couldn’t access that cookie to authorize your login.", 5000, 'red'); }
				else{ Materialize.toast("Error: " +xhr.responseJSON.message, 5000, 'red'); }
			}
		}
	);
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
</script>
