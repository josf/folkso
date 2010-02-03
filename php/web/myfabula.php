<?php

  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */

require_once('folksoDBconnect.php');
require_once('folksoDBinteract.php');
require_once('folksoFabula.php');
require_once('folksoAdmin.php');
require_once('folksoUser.php');
require_once('folksoSession.php');
require_once('folksoClient.php');
require_once("fdent/fab_info.inc");
require_once("folksoFBuser.php");
require_once("fdent/common.php");
require_once "facebook.php";
require_once "fdent/fabelements.inc";

$loc = new folksoFabula();
//$dbc = $loc->locDBC();
$dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                'testy', 'testostonomie');
$fks = new folksoSession($dbc);
$el = new FabElements();

if ($_COOKIE['folksosess']) {
  $fks->setSid($_COOKIE['folksosess']);
}

if (! $fks->sessionId
   || (! $fks->checkSession($fks->sessionId))) {
  // debug only
  $fks->startSession('gustav-2010-001');

  // FB
  $fbsec = fdent_fbSecret();
  $fb = new Facebook($fbsec['api_key'], $fbsec['secret']);


  $fb_uid = $fb->get_loggedin_user();
  $fkS = new folksoSession($dbc); // session not started yet! 
  $fbu = new folksoFBuser($dbc);

  $name = $fb->api_client->users_getInfo($fb_uid, 'name');

  if ($fb_uid && $fbu->exists($fb_uid)) {
      $fbu->userFromLogin($fb_uid);
      $fkS->startSession($fbu->userid);
      /* we are good to go */
  }
}

if ($fbu instanceof folksoUser) {
  $u = $fbu;
}
$u = $fks->userSession();
if (! $u instanceof folksoUser) {
  print "Error not a logged user";
  //  header('Location: ' . $loc->loginPage());
  exit();
}

$cl = new folksoClient('localhost', 
                       $loc->server_web_path . 'user.php',
                       'GET');
print $cl->method;

$cl->set_getfields(array('folksouid' => $u->userid,
                         'folksomytags' => 1)
                   );

$cl->set_datastyle('json');
print_r( $cl->build_req());
$result = $cl->execute();

if ($cl->query_resultcode() == 200) {
  $message = 'w00t ';
}
elseif ($cl->query_resultcode() == 204) {
  $message = "L'utilisateur n'a pas encore de tags";
}
else {
  $message = "Erreur rescode" . $cl->query_resultcode() . ':: ' . $result . 'how bout that';
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Language" content="fr-FR"/>
  <title>Mon (ma) Fabula</title>

<script type="text/javascript" src="jquery-1.3.2.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js"> </script>
<script type="text/javascript" src="jquery.jqote.js"></script>
<script type="text/javascript" src="folksonomie.js"></script>
<script type="text/javascript" src="faboid.js"></script>
<script type="text/javascript" src="myfabula.js"></script>

<script type="text/javascript">
<?php
  print 'var fK = fK || {};
         fK.data = fK.data || {};
         fK.data.myfab = ' . $result . ';';
  
  ?>//
 <?php $message ?>
</script>

<!-- templates -->

<!-- TAG template -->
<script type="text/bogus" id="tagitem">
<![CDATA[

         <a href="tag.php?folksotag=<%= this.tagnorm %>"><%= this.display %></a>
         <a class="expandtag" href="#">voir</a> <a href="#" class="hidereslist">cacher</a>
         <a class="droptag" href="tag.php?folksodelete=1&folksotag=<%= this.tagnorm %>">
          x</a>
         <ul class="tag_resources"></ul>
]]>
</script>

<!-- RESOURCE template -->
<script type="text/bogus" id="resitem">
<![CDATA[
         <a class="restitle" href="<%= this.url %>"><%= this.title %></a>
         <a class="resurl" href="<%= this.url %>"><%= this.url %></a>
         <a class="untag" href="#">x</a>
]]>
</script>


</head>
<body>
  <h1>Bienvenue ! </h1>
  <?php //print 'Voici le message : ' . $message ?>           
<div id="#oidlist"></div>                         
<div id="tagholder"> <h2>Vos tags</h2></div>

<div id="loginbox">
<h2>Log in</h2>

<?php 

  print $el->fbInit(); 
  print $el->fbLogButton();  
  if (! $u instanceof folksoUser) {
    print $el->OIform('', 'try.php');
  }





?>
</div>

</body>
</html>




