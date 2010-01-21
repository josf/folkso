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

$loc = new folksoFabula();
//$dbc = $loc->locDBC();
$test_dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                'testy', 'testostonomie');
$fks = new folksoSession($test_dbc);

if ($_COOKIE['folksosess']) {
  $fks->setSid($_COOKIE['folksosess']);
}
else { // warning, dev only!!!!!
  $fks->startSession('gustav-2009-001');
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

    <script type="text/javascript" src="jquery-1.3.2.js">
    </script>
    <script type="text/javascript" src="js/jquery.autocomplete.js">
    </script>
<script type="text/javascript" src="jquery.jqote.js"></script>
  <script type="text/javascript" src="folksonomie.js"></script>
  <script type="text/javascript" src="myfabula.js"></script>
   <script type="text/javascript">
<?php
  print 'var fK = fK || {};
         fK.data = fK.data || {};
         fK.data.myfab = ' . $result . ';'
?>
</script>

<!-- templates -->

<script type="text/bogus" id="tagitem">
<![CDATA[
         <p class="simpletag">Tag: 
         <a href="tag.php?folksotag=<%= this.tagnorm %>"><%= this.display %></a>
         <a class="droptag" href="tag.php?folksodelete=1&folksotag=<%= this.tagnorm %>">
          x</a></p>
]]>
</script>

</head>
<body>
  <h1>Bienvenue ! </h1>
  <?php //print 'Voici le message : ' . $message ?>           
                         
<div id="tagholder"> <h2>Vos tags</h2></div>
</body>
</html>




