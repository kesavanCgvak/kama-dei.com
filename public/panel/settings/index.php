<?php
use \App\User;
use \App\Http\Controllers\Api\Dashboard\Organization\OrganizationController;
use \App\Http\Controllers\Api\Dashboard\Level\LevelController;
use \App\ConsumerUserPersonality;
use \App\Personality;

$user = (new User())->where('id', '=', session()->get('userID'))->first();
$organizations = (new OrganizationController())->listOrganization($orgID)['data'];
$org = null;
/*
foreach($organizations as $organization){if($organization->organizationId == $user->orgID) $org = $organization->organizationShortName;}
*/
foreach($organizations as $organization){
	if($organization['organizationId'] == $user->orgID){ $org = $organization['organizationShortName'];	}
}
//$levels = (new LevelController())->listLevel($orgID)['data'];
$level = \App\Level::find($user->levelID)->levelName;
if($user->levelID == 4){
  $personality = (new ConsumerUserPersonality())->where('consumerUserId', $user->id)->first();
  $personality = (new Personality())->where('personalityId', $personality->personalityId)->first()->personalityName;
}else{
  $personality = '-';
}

?>

<link rel="stylesheet" type="text/css" href="<?=env('API_URL');?>/public/dist/css/settings.css">
<div class="settingsContent row" style="min-height:400px">
  <div class="col-sm-7 col-xs-12">
    <form id="settingsForm">
     <div class="form-group">
       <label>Organization:</label>
       <p class="form-control-static"><?=$org;?></p>
     </div>
     <div class="form-group">
       <label>Role:</label>
       <p class="form-control-static"><?=$level;?></p>
     </div>
     <div class="form-group">
       <label>Last Login:</label>
       <p class="form-control-static"><?=$user->lastLogin;?></p>
     </div>
     <div class="form-group">
       <label>Created at:</label>
       <p class="form-control-static"><?=$user->createAt;?></p>
     </div>
     <div class="form-group">
       <label>Last edited:</label>
       <p class="form-control-static"><?=$user->lastLogin;?></p>
     </div>
   </form>
 </div>

 <div class="userInfoWrapper col-sm-5 col-xs-12">
   <div class="userInfo">
     <div class="userLogo"><i class="fa fa-user" aria-hidden="true"></i></div>
     <div class="userName"><?=$user->userName;?></div>
     <div class="userEmail"><?=$user->email;?></div>
     <div class="changePass"><a href="#">Change Password</a></div>
   </div>
   <?php if($user->isAdmin): ?>
     <span class="isAdmin">Admin</span>
   <?php endif; ?>
 </div>
</div>


<script>
$(function(){
  $(".changePass a").click(function(e){
    e.preventDefault();
    var popup = window.open("<?=env('API_URL');?>/pass/change", 'window', 'height=400, width=300, resizable=no, titlebar=no, toolbar=no, status=no');
  })
  /*
  var error = true;
  var success = false;

  $("#settingsForm").submit(function(e){
    e.preventDefault();
    hideMessage();
    if($("#password").val() != $("#confirmPassword").val()){
      message('Passwords do not match', error);
      return;
    }

    if($("#password").val().length != 0 && $("#password").val().length < 4) {
      message('Password must be at least 4 characters', error);
      return;
    }

    var data = {
      userName: "<?=$user->userName;?>",
      email: "<?=$user->email;?>",
      userPass: $("#password").val(),
      orgID: $("#organization").val(),
      levelID: $("#level").val()
    }

    $.ajax({
      url: "<?=env('API_URL');?>/api/panel/settings/edit/<?=$orgID;?>/<?=$user->id;?>",
      type: 'POST',
      data: data,
      success: function(res){
        if(res.result == 1) {
          message(res.msg, error);
        }else{
          message('Saved', success);
          setTimeout(function(){location.reload()}, 1000);
        }
      },
      error: function(e){
        message(e.responseText, error);
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
  */
})
</script>
