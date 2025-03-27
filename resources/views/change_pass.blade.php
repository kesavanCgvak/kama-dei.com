<html>
<head>
  <title>Change Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="{{ asset('public/dist/images/kama-favicon.jpg') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/css/bootstrap.min.css') }}">
  <script type="text/javascript" src="{{ asset('public/js/jquery.js') }}"></script>
  <script src="https://use.fontawesome.com/3d00a0f028.js"></script>
</head>
<body>
  <form role="form" style="padding:15px;">
    <div class="form-group">
      <label for="oldPass">Old Password</label>
      <input type="password" class="form-control" id="oldPass" placeholder="Enter your old password">
    </div>
    <div class="form-group">
      <label for="newPass">New Password</label>
      <input type="password" class="form-control" id="newPass" placeholder="Enter your new password">
    </div>
    <div class="form-group">
      <label for="confirmPass">Confirm Password</label>
      <input type="password" class="form-control" id="confirmPass" placeholder="Enter your new password again">
    </div>
    <div class="messages"><div class="alert alert-danger" style="display:none">Error</div></div>
    <div style="position: absolute;right: 15px;bottom: 15px;"><button type="submit" class="btn btn-default">Save</button></div>
  </form>
</body>

<script>
$(function(){
  $("form").submit(function(e){
    e.preventDefault();

    hideMessage();

    if($("#oldPass").val().length == 0) {
      message('Please enter your old password.', true);
      return;
    }

    if($("#newPass").val() != $("#confirmPass").val()){
      message('Passwords do not match', true);
      return;
    }

    if($("#newPass").val().length < 4) {
      message('Password must be at least 4 characters', true);
      return;
    }

    var data = {
      oldPass: $("#oldPass").val(),
      newPass: $("#newPass").val()
    }

    $.ajax({
      url: "<?=env('API_URL');?>/api/pass_change",
      type: "post",
      data: data,
      success: function(res){
        if(res.result == 0){
          message("Password successfully changed.", false);
          setTimeout(function(){window.close()}, 1000);
        }else{
          message(res.msg, true);
        }
      },
      error: function(e){
        message("Error", true);
      }
    })
  })

  function message(str, isError){
    if(isError) $(".messages .alert").removeClass('alert-success').addClass('alert-danger').html(str).fadeTo(200, 1);
    else $(".messages .alert").removeClass('alert-danger').addClass('alert-success').html(str).fadeTo(200, 1);
  }

  function hideMessage(){
    $(".messages .alert").fadeTo(200, 0);
  }
})
</script>

</html>
