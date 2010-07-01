<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2010 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */

require_once('folksoDBconnect.php');
require_once('folksoDBinteract.php');
require_once('folksoFabula.php');
require_once('folksoClient.php');
require_once('folksoUserServ.php');
require_once('folksoPage.php');

if ((! $fp) || (! $fp instanceof folksoPage)) {
  $fp = new folksoPage();
} 


require("/var/www/dom/fabula/commun3/head_libs.php");
require("/var/www/dom/fabula/commun3/head_folkso.php");

$loggedIn = false;
$authorized = false; //ie. logged in and has required privileges
if ($fks->status()) {
    $loggedIn = true;
    if (($u instanceof folksoUser) &&
        $u->checkUserRight('folkso', 'admin')) {
      $authorized = true;
    }
}

require("/var/www/dom/fabula/commun3/head_dtd.php");

print "<html>\n<head>";

require("/var/www/dom/fabula/commun3/head_meta.php");
require("/var/www/dom/fabula/commun3/head_css.php");

?>

<style>
a.taglink {
font-size: 12pt;
 }
a.unsub {
font-size: 8pt;
}

</style>

<?php
require("/var/www/dom/fabula/commun3/head_javascript_folkso.php");

?> 

<script type="text/javascript" 
  src="/tags/js/folkso-admin.js">
</script>
<?php

require ('/var/www/dom/fabula/commun3/browser_detect.php');
if (stristr($_SERVER['HTTP_USER_AGENT'], 'iPhone')) {
echo ("</head>\n<body>");
echo ("<h1 class=\"titre_iphone\">Visitez notre site optimis<C3><A9> <br><a href=\"http://iphone.fabula.org\">iphone.fabula.org</a></h1>");
} else {
if ( (browser_detection( 'os' )== "mac" ) && (browser_detection( 'browser' ) =="moz") ) {
echo "<style>\n#tabs-menu {\nheight: 17px;\n}\n</style>";
}
echo ("</head>\n<body>");
}


require("/var/www/dom/fabula/commun3/html_start.php");

?> 
<div id="colonnes_nouvelles">
<div id="colonnes-un">

  <h1>Folksonomie : Administration des utilisateurs</h1>

<?php
  if (!$loggedIn) {
    // display login buttons

?><h1 class="not-logged">Il faut vous identifier d'abord</h1> 

<div id="fbstuff">
<?php //'
print $fp->facebookConnectButton(); 

?>
</div>
<?php 

  }
elseif (! $authorized) {
?>
<h1>Accés refusé</h1>

<p>Vous ne disposez pas des privilèges nécessaires pour accéder à l'interface 
d'administration. Veuillez contacter les administrateurs du site si vous pensez 
qu'il s'agit d'une erreur. <!-- ' --></p>

<?php
}
else {
?>

<div id="usersearch">
<h2>Recherche d'utilisateur</h2> <!-- ' -->

<p>
    Saisir un ou plusieurs noms ou prénoms. Pour affiner la recherche, vous pouvez utiliser des mots clés suivis d'un deux-points, comme <strong>fname:</strong> pour les prénoms, ou <strong>lname:</strong> pour les noms de famille. 
</p><!-- ' -->

<input id="searchbox" type="text"></input>
<a href="#" id="searchok">OK</a>
</div>

<div id="userlist">
<ul id="masterlist">

</ul>
</div>


</div>
</div>
<?php
    }
include("/var/www/dom/fabula/commun3/foot.php");

?>