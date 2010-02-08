<?php

require_once "folksoPage.php";
require_once "fdent/fabelements.inc";
require_once "fdent/common.php";
require_once "fdent/fab_info.inc";
require_once "facebook.php";
require_once "folksoSession.php";
require_once "folksoFabula.php";
require_once "folksoFBuser.php";

$fp = new folksoPage();
$el = new fabelements();
$loc = new folksoFabula();

/*$dbc = new folksoDBconnect('localhost', 'tester_dude', 
  'testy', 'testostonomie');*/
$dbc = $loc->locDBC();
$fks = new folksoSession($dbc);

if ($_COOKIE['folksosess']) {
  $fks->setSid($_COOKIE['folksosess']);
}

if (! $fks->sessionId
   || (! $fks->checkSession($fks->sessionId))) {
  // debug only
  //    $fks->startSession('gustav-2010-001');

  // FB
  $fbsec = fdent_fbSecret();
  $fb = new Facebook($fbsec['api_key'], $fbsec['secret']);


  $fb_uid = $fb->get_loggedin_user();
  $fbu = new folksoFBuser($dbc);

  $name = $fb->api_client->users_getInfo($fb_uid, 'name');

  if ($fb_uid && $fbu->exists($fb_uid)) {
      $fbu->userFromLogin($fb_uid);
      $fks->startSession($fbu->userid);
      /* we are good to go */
  }
}



?>
<html>
<head>

<title>Une page au hasard</title>

<script type="text/javascript"
    src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.js"></script> 
<script type="text/javascript" 
  src="<?php print $fp->javascript_path() ?>jquery.autocomplete.js"> </script>
<script type="text/javascript" 
  src="<?php print $fp->javascript_path() ?>jquery.jqote.js"></script>
<script type="text/javascript" 
  src="<?php print $fp->javascript_path() ?>folksonomie.js"></script>
<script type="text/javascript" 
  src="<?php print $fp->javascript_path() ?>faboid.js"></script>

<?php // outputs a <script> elememnt
  print $fp->jsHolder($fp->fKjsLoginState('fK.loginState'));
?>

<script type="text/javascript">

  function setupLogin () {
  return function (ev) {
    ev.preventDefault();
    $(this).parent().append( fK.oid.providerList() );
  }
}

  $(document).ready(function()
                    {
                      fK.oid.logopath = "/logos/";
                      // setup according to login state
                      fK.cf.container = $("#folksocontrol");
                      if (fK.loginStatus) {
                        $("#fbkillbox", fK.cf.container).hide();
                      }
                      else {
                        $(".fKTagbutton", fK.cf.container).hide();
                        $("input.fKTaginput", fK.cf.container).hide();
                        $(".fKLoginButton", fK.cf.container).click(setupLogin());

                        fK.onLoggedIn.push(function() {
                            $("#fbkillbox", fK.cf.container).hide();
                            alert("Hey, you logged in!");
                            $(".fbconnect_login_button").hide();
                          });
                        fK.fn.pollFolksoCookie();
                      }

                    });



</script>

</head>
<body>
<h1>Test des fonctions</h1>
<?php

print $fp->tagbox();

?>
<div id="fbkillbox">
<?php
    print $fp->facebookLoginCode();
?>
</div>

</body>
</html>