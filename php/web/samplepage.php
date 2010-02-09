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
  print $fp->jsHolder($fp->fKjsLoginState('fK.loginStatus'));
?>

<script type="text/javascript">



  $(document).ready(function()
                    {
                      var hostAndPath = 'http://www.fabula.org/tags/';
                      fK.init({autocompleteUrl: hostAndPath + 'tagcomplete.php'});

                      fK.oid.logopath = "/tags/logos/";
                      fK.oid.oidpath = "/tags/fdent/";

                      function setupLogin () {
                        return function (ev) {
                          ev.preventDefault();
                          $(this).parent().append( fK.oid.providerList() );
                        }
                      }

                      window.handleOpenIDResponse = function (openid_args){
                        $("#bucket").html("Verifying OpenID response");
                        $.ajax({type: "get",
                              url: fK.oid.oidpath + "oid_popup_end.php",
                              data: openid_args,
                              success: function(msg) {
                              $("#bucket").html(msg);
                            }});
                      };

                      // setup according to login state
                      fK.cf.container = $("#folksocontrol");

                        $('body').bind('loggedIn',
                                       function() {
                                         $("#fbkillbox", fK.cf.container).hide();
                                         $(".fKTagbutton").show();
                                         $(".fKTaginput", fK.cf.container).show();
                                         $(".fKLoginButton", fK.cf.container).hide();
                                         $("fb:login-button").hide();
                                         $("ul.provider_list").hide();
                                       });

                          
                      if (fK.loginStatus) {
                        $("#fbkillbox", fK.cf.container).hide();
                        $(".fKLoginButton", fK.cf.container).hide();
                      }
                      else {
                        $(".fKTagbutton", fK.cf.container).hide();
                        $("input.fKTaginput").hide();
                        $(".fKLoginButton").click(setupLogin());

                        /* Sets up event handler: $("body").bind("loggedIn") */
                        fK.fn.pollFolksoCookie();

                      }
                      $("input.fKTaginput", fK.cf.container).autocomplete(fK.cf.autocompleteUrl);
                    });



</script>
<link rel="stylesheet" href="/tags/js/jquery.autocomplete.css"></link>
</head>
<body>
<h1>Test des fonctions  </h1>
<div id="bloc_orange" style="padding-top: 0pt; padding-bottom: 0pt;">
  <b class="niftycorners"><h3>Mots clés: </h3></b>
  <div class="tagcloud">
  <?php print $fp->basic_cloud(); ?>
  </b>
  </div>
  </div>
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