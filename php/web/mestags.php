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
if ($fks->status()) {
    $loggedIn = true;
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

ul.fav-list li {
display: inline;
  right-margin: 0.2em;
}

ul.fav-list a {
  font-size: 10pt;
}

</style>

<?php
require("/var/www/dom/fabula/commun3/head_javascript_folkso.php");

?> 

<script type="text/javascript" 
  src="/tags/js/folkso-user.js">
</script>
<script type="text/javascript"
  src="/tags/js/jquery.tinymce.js">
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

<h1>Espace tags</h1>

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

?>

<div id="user-intro" class="login-only">
<p>Bonjour <span class="userhello"></span> !</p>
<p>Vous avez appliqué <span id="tagcount"></span> tags.</p>
</div>

<div id="subscriptions" class="login-only">
<h2>Vos abonnements</h2>
  <p>Les tags auxquels vous êtes actuellement abonné(e) : </p>
<ul>
</ul>
</div>

<div id="favtags" class="login-only">
<h2>Vos tags préférés</h2>
  <p>Les tags que vous appliquez le plus souvent : </p> 
<ul class="fav-list">
</ul>
</div>

<div id="newsubscriptions" class="login-only">
<h2>Vous abonner à de nouveaux tags</h2> 
<p>Choisir un nouveau tag:</p>
<input type="text" id="newsubbox" size="60">
</input>
<a id="addsub" href="#">OK</a>
</div>



<div id="userdata" class="login-only">
<h2>Vos données</h2>
    <p>En renseignant les champs "courrier électronique", 
      "institution", "pays" ou "fonction", 
      vous acceptez de figurer dans l'annuaire Fabula</p> <!-- ' -->


<p class="firstname">
  Prénom : 
  <span class="firstname">
  </span>
  <input type="text" class="firstnamebox">
  </input>
</p>

<p class="lastname">
  Nom de famille :
  <span class="lastname">
  </span>
  <input type="text" class="lastnamebox">
  </input>
</p>

<p class="email">
  Courrier électronique :
  <span class="email">
  </span>
  <input type="text" class="emailbox">
  </input>
</p>

<p class="institution">
  Institution :
  <span class="institution">
  </span>
  <input type="text" class="institutionbox">
  </input>
</p>

<p class="pays">
  Pays :
  <span class="pays">
  </span>
  <input type="text" class="paysbox">
  </input>
</p>

<p class="fonction">
  Fonction : 
  <span class="fonction">
  </span>
  <input type="text" class="fonctionbox">
  </input>
</p>

<div class="user-cv">
  <p>CV : </p>
  <p class="cv">
  </p>
  <textarea cols="80" rows="20" class="cv-write"></textarea>
  </input>
</div>

</div>
<p><a href="#" id="userdata-send">Valider</a></p>
</div>


<div id="colonnes-deux">
<div id="recently" class="login-only">
<h2>Mes ressources</h2>
<p>Les ressources récemment taggées avec mes tags.
   Si une ressource comporte plus d'un de vos tags, il apparaîtra
plus haut dans la liste.</p> <!-- ' -->
<ul></ul>
</div>
</div>
</div>
</div>
<?php

include("/var/www/dom/fabula/commun3/foot.php");

?>