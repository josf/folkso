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

require("/var/www/dom/fabula/commun3/head_libs.php");
require("/var/www/dom/fabula/commun3/head_folkso.php");

$loggedIn = false;
if ($fbu && ($fbu instanceof folksoUser) && ($fbu->exists()) {
    $loggedIn = true;
}

require("/var/www/dom/fabula/commun3/head_dtd.php");

print "<html>\n<head>";

require("/var/www/dom/fabula/commun3/head_meta.php");
require("/var/www/dom/fabula/commun3/head_css.php");
require("/var/www/dom/fabula/commun3/head_javascript_folkso.php");

require ('browser_detect.php');
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

/** your stuff here **/


  if (!$loggedIn) {

    // display login buttons
  }
  else { 
?>

<h1>Les tags de <?php echo $fbu->firstName . ' ' . $fbu->lastName ?></h1>

<div id="subscriptions">
<ul>
</ul>
</div>


<?php

      }

include("/var/www/dom/fabula/commun3/foot.php");