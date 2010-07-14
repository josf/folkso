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
require_once 'folksoUserServ.php';
require_once 'folksoInteract.php';

if ((! $fp) || (! $fp instanceof folksoPage)) {
  $fp = new folksoPage();
} 

if ((! $loc) || (! $loc instanceof folksoFabula)) {
  $loc = new folksoFabula();
}
$dbc = $loc->locDBC();
$u = new folksoUser($dbc);

$rawUser = strip_tags($_GET['user']);
if (strlen($rawUser) > 255) {
  $rawUser = null;
}

if ((! $rawUser) || 
    (! $u->validateUid($rawUser)) ||
    (! $u->userFromUserId($rawUser))) {
  // userFromUserId returns false when user is not found
  header('HTTP/1.1 404 User not found');
  print "The user id you supplied is either invalid or does not correspond to a real user";
  exit();
}

#ifdef DEBUG
$debug = '';
#endif

$fkint = new folksoOnServer($dbc);
$userDataReq = $fkint->userDataReq($u);
$userFaveReq = $fkint->userFavoriteTags($u);

if (! ($userDataReq->status  == 200)) {
  $noUserData = true;

#ifdef DEBUG
  $debug .= '<p>bad status on userDataReq: ' . $userDataReq . '</p>';
#endif
}
else {
  $userData_DOM = new DOMDocument();
  if (! $userData_DOM->loadXML($userDataReq->body())) {
    $noUserData = true;
#ifdef DEBUG
    $debug .= '<p>userData failed to load as xml</p>';
#endif
  }
  else {  // process and build $userDataHtml which will be used in the page
    $udataXsl = new DOMDocument();
    $udataXsl->load($loc->xsl_dir . "userdata_display.xsl");
    $udataProc = new XsltProcessor();
    $udataXsl = $udataProc->importStylesheet($udataXsl);
    $udataTrans = $udataProc->transformToDoc($userData_DOM);
    $userDataHtml = $udataTrans->saveXML();

  }    

  if (($userFaveReq->status == 200) &&
      (strlen($userFaveReq->body()) > 0)) {
    $userFave_DOM = new DOMDocument();
    $userFave_DOM->loadXML($userFaveReq->body());

    $ufaveXsl = new DOMDocument();
    $ufaveXsl->load($loc->xsl_dir . "userfaves_display.xsl");
    $ufaveProc = new XsltProcessor();
    $udataXsl = $ufaveProc->importStylesheet($ufaveXsl);
    $ufaveTrans = $ufaveProc->transformToDoc($userFave_DOM);
    $userFaveHtml = $ufaveTrans->saveXML();
  }
#ifdef DEBUG
  else {
    $debug .= '<p>No favorites found: userFaveReq->status: ' . $userFaveReq->status 
      . ' bodylength: ' . strlen($userFaveReq->body()) . '</p>';
  }
#endif 
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

<?php
#ifdef DEBUG
  print $debug;
#endif 

  if ($noUserData ||
      (strlen($userDataHtml) == 0)) {
    print "<p>Les informations concernant cet utilisateur ne sont pas disponibles actuellement.</p>";
  }
  else {
    print $userDataHtml;
    
    if (strlen($userFaveHtml) > 0) {
      print $userFaveHtml;
    }
  }
?>
</div></div>
<?php

include("/var/www/dom/fabula/commun3/foot.php");
