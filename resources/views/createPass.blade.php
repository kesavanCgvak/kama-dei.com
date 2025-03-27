<head>
	<title><?=env('BASE_ORGANIZATION');?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script type="text/javascript" src="{{ asset('public/js/jquery.js') }}?new"></script>

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
			height: fit-content;
			margin: auto;
			top: 0;
			bottom: 0;
			right: 0;
			left: 0;
			padding: 20px;
			border-radius: 2px;
		}

		.loginForm form {
			margin: 0;
		}

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

		#submitLogin {
			margin-top: 30px;
			width: 100%;
			height: auto;
			font-family: arial, sans-serif;
		}

		.logo img {
			width: 50%;
		}

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
	</style>
</head>
<body>
	<div class="loginForm white">
		<div class="logo"><img src="{{ asset('public/dist/images/logo2.jpeg') }}" /></div>
		<br/>
		<br/>
		<div>
		<form>
			<div>
				<div class="input-field">
					<input type="password" id="passLogin1" class="validate" autocomplete="new-password" />
					<label for="userLogin">Password</label>
				</div>
				<div class="input-field">
					<input type="password" id="passLogin2" class="validate" autocomplete="new-password" />
					<label for="passLogin">Retype password</label>
				</div>
				<div style="text-align:right">
					<button type="button" id="submitLogin" class="waves-effect waves-light btn blue" onClick="callSetPass()"><h5>Set Pass</h5></button>
				</div>
			</div>
		</form>
		</div>
	</div>
</body>

<script type="text/javascript">
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
$(function(){
	$("#passLogin1").on('keypress', function(e){ if(e.keyCode==13){ $("#passLogin2").focus(); } });
	$("#passLogin2").on('keypress', function(e){ if(e.keyCode==13){ callSetPass(); } });
});
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
function callSetPass() {
	$("body > .login-error").remove();

	var data = {};
	data.passLogin1 = $("#passLogin1").val().trim();
	data.passLogin2 = $("#passLogin2").val().trim();
	data.passKey    = '<?=$passKey;?>';
	data._token     = '{{ csrf_token() }}';

	if(data.passLogin1==''){ Materialize.toast("Password is empty!", 5000, 'red'); $("#passLogin1").focus(); return; }
	if(data.passLogin2==''){ Materialize.toast("Retype password is empty!", 5000, 'red'); $("#passLogin2").focus(); return; }
	if(data.passLogin1!=data.passLogin2){ Materialize.toast("Wrong check Password or Retype password!", 5000, 'red'); $("#passLogin1").focus(); return; }

	$.post('/login/setPass',data,
		function(retVal){
			if(retVal.trim()==1){ window.location.reload(); }
			else{
				Materialize.toast(retVal, 5000, 'red');
				$("#passLogin1").focus();
			}
		}
	).fail(
		function(){ Materialize.toast("Error", 5000, 'red'); }
	);
}
//---------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------
</script>
