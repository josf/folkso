<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2010 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */

  /*
   * User's public Fabula page.
   */

require_once 'folksoUser.php';
require_once 'folksoDBinteract.php';
require_once 'folksoFabula.php';
require_once 'folksoUserServ.php';
require_once 'folksoPage.php';

if ((! $fp) || (! $fp instanceof folksoPage)) {
  $fp = new folksoPage();
} 




require("/var/www/dom/fabula/commun3/head_libs.php");
require("/var/www/dom/fabula/commun3/head_folkso.php");
require("/var/www/dom/fabula/commun3/head_dtd.php");

print "<html>\n<head>";

require("/var/www/dom/fabula/commun3/head_meta.php");
require("/var/www/dom/fabula/commun3/head_css.php");

require("/var/www/dom/fabula/commun3/head_javascript_folkso.php");

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
