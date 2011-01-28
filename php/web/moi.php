<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2010-2011 Gnu Public Licence (GPL)
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

require_once 'facebook.php';

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

$fb = new Facebook($loc->snippets['facebookApiKey'],
                   $loc->snippets['facebookSecret']);
$fb_uid = $fb->get_loggedin_user();

// check the userid param first. If someone asks for this, then they know what they want
if ($rawUser) {
  if (! $u->validateUid($rawUser)) {
    header('HTTP/1.1 400 Malformed userid');
    print "The user id should look something like this: characters-2000-001";
    exit();
  }
  else if (! $u->userFromUserId($rawUser)) {
    // userFromUserId returns false when user is not found
    header('HTTP/1.1 404 User not found');
    react404($loc,
             "The user id you supplied does not correspond to a real user");
    exit();
  }
}
// otherwise we might be getting the name as parameters
elseif ($_GET['first'] && $_GET['last']) {
  // letters only in names
  $first = preg_replace('/[^a-zA-Z-]/', '', $_GET['first']);
  $last = preg_replace('/[^a-zA-Z-]/', '', $_GET['last']);

  if ((strlen($first) > 1) &&
      (strlen($last) > 1)) {
    if (! $u->userFromName($first, $last)){
      header('HTTP/1.1 404 User not found');
      react404($loc,
               "The user name you have supplied does not correspond to a current user");
      exit();
    }
  }
  else {
    header('HTTP/1.1 400 Invalid user name');
    react404($loc, "There is a problem with the user name you have supplied");
    exit();
  }
}

// and now we have run out of chances for finding a user to get data about
if (! $u->userid) {
  header('HTTP/1.1 404 No user');
  react404($loc,
           "A user must be identified in the request, either by name or by userid");
  exit();
}


#ifdef DEBUG
$debug = '';
#endif

$fkint = new folksoOnServer($dbc);
$userDataReq = $fkint->userDataReq($u);
$userFaveReq = $fkint->userFavoriteTags($u);
$userSubsReq = $fkint->getUserSubs($u);

if (! ($userDataReq->status  == 200)) {
  $noUserData = true;

#ifdef DEBUG
//  $debug .= '<p>uid: ' . $u->userid . '</p><p>bad status on userDataReq: ' . $userDataReq->status . '</p>'
//    . '<p>' . $userDataReq->error_body . '</p>';
#endif
}
else {
  $userData_DOM = new DOMDocument();
  if (! $userData_DOM->loadXML($userDataReq->body())) {
    $noUserData = true;
#ifdef DEBUG
//    $debug .= '<p>userData failed to load as xml</p>';
#endif
  }
  else {  
    // extract user name
    $firstnameList = $userData_DOM->getElementsByTagName("firstname");
    $lastnameList =  $userData_DOM->getElementsByTagName("lastname");
    $firstname = $firstnameList->item(0)->textContent;
    $lastname = $lastnameList->item(0)->textContent;
    $fullname = $firstname . ' ' . $lastname;

    // process and build $userDataHtml which will be used in the page
    $udataXsl = new DOMDocument();
    $udataXsl->load($loc->xsl_dir . "userdata_display.xsl");
    $udataProc = new XsltProcessor();
    $udataXsl = $udataProc->importStylesheet($udataXsl);
    $udataTrans = $udataProc->transformToDoc($userData_DOM);
    $userDataHtml = $udataTrans->saveXML();
  }    

  /* Favorite tag list */
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
    //    $debug .= '<p>No favorites found: userFaveReq->status: ' . $userFaveReq->status 
    //      . ' bodylength: ' . strlen($userFaveReq->body()) . '</p>';
  }
#endif 
}
$page_titre = 'Fabula - Page personnelle de ' . $fullname;

if ($userSubsReq->status !== 200) {
  $noUserSubs = true;
}
else {
  $userSubs_DOM = new DOMDocument();
  $userSubs_DOM->loadXML($userSubsReq->body());
  
  $userSubsXsl = new DOMDocument();
  $userSubsXsl->load($loc->xsl_dir . 'usersubs_display.xsl');
  $userSubsProc = new XsltProcessor();
  $userSubsXsl = $userSubsProc->importStylesheet($userSubsXsl);
  $userSubsTrans = $userSubsProc->transformToDoc($userSubs_DOM);
  $userSubsHtml = $userSubsTrans->saveXML();
}


require("/var/www/dom/fabula/commun3/head_libs.php");
require("/var/www/dom/fabula/commun3/head_folkso.php");
require("/var/www/dom/fabula/commun3/head_dtd.php");

print "<html>\n<head>";

require("/var/www/dom/fabula/commun3/head_meta.php");

?>
<link rel="stylesheet" href="/tags/css/blueprint/screen.css" type="text/css" media="screen, projection"/>
<link rel="stylesheet" href="/tags/css/blueprint/print.css" type="text/css" media="print"/>	
<!--[if lt IE 8]><link rel="stylesheet" href="/tags/css/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->

<?php 

require("/var/www/dom/fabula/commun3/head_css.php");

?>

<style>

dl.user-data-list {
    padding-top: 0.7em;
}

dl.user-data-list dt {
    font-size: smaller;
}

dl.user-data-list dd {
    padding-top: 0.5em;
    padding-left: 2em;
    margin-bottom: 0.7em;
}

ul.favorite-tags li, div.user-subs li {
    list-style: disc;
    display: inline;
    margin-right: 1em;
}

div.cv-text {
    padding: 1em;
    border: 1px dotted gray;
    margin-bottom: 0.5em;
}

div.user-subs, div.favorite-tags {
    margin-top: 1em;
}

div.user-subs h4, div.favorite-tags h4 {
    font-weight: bold;
    padding-bottom: 0.3em;
}

div.user-subs a, ul.favorite-tags a, div.user-subs a:visited, ul.favorite-tags a:visited, div.user-subs a:link, ul.favorite-tags a:link {
    border-bottom: none;
}

#decoration-holder {
    padding-top: 100px;
    background-image: url('/tags/images/taggroup.png');
    background-repeat: no-repeat;
    background-position: 50% 0%;
}

div.cv-text ul li {
    list-style: disc;
    margin-left: 1em;
}
</style>
<?php

require("/var/www/dom/fabula/commun3/head_javascript_folkso.php");

/*require ('/var/www/dom/fabula/commun3/browser_detect.php');*/
if (stristr($_SERVER['HTTP_USER_AGENT'], 'iPhone')) {
echo ("</head>\n<body>");
echo ("<h1 class=\"titre_iphone\">Visitez notre site optimis<C3><A9> <br><a href=\"http://iphone.fabula.org\">iphone.fabula.org</a></h1>");
} else {
  /*if ( (browser_detection( 'os' )== "mac" ) && (browser_detection( 'browser' ) =="moz") ) {
echo "<style>\n#tabs-menu {\nheight: 17px;\n}\n</style>";
}*/
echo ("</head>\n<body>");
}

require("/var/www/dom/fabula/commun3/html_start.php");
?> 
<div id="colonnes_nouvelles">
<div id="colonnes-un">

<?php
#ifdef DEBUG
//  print $debug;
#endif 

  if ($noUserData ||
      (strlen($userDataHtml) == 0)) {
    print "<p>Les informations concernant cet utilisateur ne sont pas disponibles actuellement.</p>";
  }
  else {
    ?>
    <div  id="basicPresentation">
    <?php print $userDataHtml; ?>


    </div>
<?php
  }
?>
<a href="/tags/mestags.php">Éditer mes propres informations (Espace Tags)</a>
</div>
<div id="colonnes-deux">
  <div id="decoration-holder">&#160;</div>
      <?php 
  if (strlen($userSubsHtml) > 0) {
    print $userSubsHtml;
  }
  else {
    print $userSubsReq->status;

  }


      if (strlen($userFaveHtml) > 0) {
        print $userFaveHtml;
      }
      ?>

</div>
</div>
<?php

include("/var/www/dom/fabula/commun3/foot.php");

function react404 (folksoLocal $loc, $message) {
  if ($loc->urlRedirect404) {
    header('Location: ' . $loc->urlRedirect404);
  }
  else {
    print $message;
  }
}