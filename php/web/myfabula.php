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
print_r( $cl->build_req());
$cl->set_datastyle('json');
$result = $cl->execute();

if ($cl->query_resultcode == 200) {
  $message = 'w00t ' . $result;;
}
elseif ($cl->query_resultcode == 204) {
  $message = "L'utilisateur n'a pas encore de tags";
}
else {
  $message = "Erreur " . $cl->query_resultcode . ' ' . $result . 'how bout that';
}



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Language" content="fr-FR"/>
  <title>Mon (ma) Fabula</title>

    <script type="text/javascript" src="js/jquery.js">
    </script>
    <script type="text/javascript" src="js/jquery.autocomplete.js">
    </script>
  
   <script type="text/javascript">
<?php
  print 'var fk = fk || {};
         var fk.myfab = fk.myfab || {};
         fk.myfab = ' . $result . ';'
?>
</script>
</head>
<body>
  
       <?php print 'Voici le message : ' . $message ?>           
                         

</body>
</html>




