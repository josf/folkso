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
$page_titre = 'Fabula - Espace tags et gestion de compte';
$loggedIn = false;
$redacRight = false;
$adminRight = true;

if ($fks->status()) {
    $loggedIn = true;

    if ($u &&
        $u instanceof folksoUser) {
      if ($u->checkUserRight('folkso', 'redac')) {
        $redacRight = true;
      }
      if ($u->checkUserRight('folkso', 'admin')) {
        $adminRight = true;
      }
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

ul.fav-list li {
display: inline;
  right-margin: 0.2em;
}

ul.fav-list a {
  font-size: 10pt;
}
.add-user-data {
  display: none;
  font-weight: bold;
  border: solid 2px red;
 }

.login-only {
display: none;
 }

div.doc .closetext, div.doc .doc-content {
  display: none;
}

div.doc .opentext, div.doc .closetext {
  font-size: 0.8em;
  font-style: italic;
}

div.doc .doc-content {
  background-color: #f6e346;
  border: 2px solid black;

}

#login-tabs { display: none; } 
</style>

<link rel="stylesheet" type="text/css" href="/tags/css/jquery-ui-1.8.4.custom.css" media="screen"/>

<?php
require("/var/www/dom/fabula/commun3/head_javascript_folkso.php");

?> 

<script type="text/javascript"
  src="/tags/js/jquery.tinymce.js">
</script>

<script type="text/javascript" 
  src="/tags/js/folkso-user.js">
</script>

<script type="text/javascript">
  $(document).ready(
      function() {
          $("div.doc").each(
              function(){
                  var $doc = $(this);
                  $("a.opentext", $doc).click(
                      function(ev) {
                          ev.preventDefault();
                          $(this).hide();
                          $("a.closetext", $doc).show();
                          $("div.doc-content", $doc).show();
                      });                       
                  $("a.closetext", $doc).click(
                      function(ev) {
                          ev.preventDefault();
                          $(this).hide();
                          $("a.opentext", $doc).show();
                          $("div.doc-content", $doc).hide();
                  });
              });
     });

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

<div id="title-and-docs">

  <!-- Documentation block, javascript enabled -->
<h1>Espace tags</h1>
<div class="doc">
  <a class="opentext" href="#">Qu'est-ce que c'est&#160;?</a>
  <a class="closetext" href="#">Fermer</a>
  <div class="doc-content">
  <p>
  Cette page vous permet de choisir et suivre les <em>tags</em> 
  qui vous intéressent, et de gérer les informations que vous souhaitez 
  rendre publiques.
   </p>
  <p>Pour ajouter un nouveau tag, il suffit d'en 
  saisir les premières lettres dans le champ <strong>Vous abonner 
  à de nouveaux tags</strong>. 
  </p>
  </div>
</div>
</div> <!-- 'end title-and-docs -->
<?php 
    if (!$loggedIn) {
    // display login buttons

?><h1 class="not-logged">Il faut vous identifier d'abord</h1> 

<div id="login-tabs">

<!-- tab headers --'>
<ul>
<li><a href="#tabs-1">Open ID</a></li>
<li><a href="#tabs-2">Facebook Connect</a></li>
</ul>

<div id="tabs-1">
OpenId
</div>

<div id="tabs-2">
<?php

print $fp->facebookConnectButton(); 

?>
</div>
</div> <!-- end of #login-tabs div -->     

<?php 
  } // end of if block
?>

<div id="user-intro" class="login-only">
<p>Bonjour <span class="userhello"></span> !</p>
<p id="tag-brag">Vous avez appliqué <span id="tagcount"></span> tags.</p>
</div>

  <?php // Admin, redac link
  if ($redacRight || $adminRight) {
    ?>
    <p>
    <a href="/tags/adminlogin.php">Accès aux pages de gestion des tags.</a>
    </p>
    <?php
  }
?>


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
<input type="text" class="fKTaginput" id="newsubbox" size="60">
</input>
<a id="addsub" href="#">OK</a>
</div>



<div id="userdata" class="login-only">
<h2>Vos données</h2>
  <p>Lorsque vous renseignez les champs "Prénom" et "Nom de famille", 
  une page personelle sera créée où vous pourriez afficher vos coordonnées et un mini-CV, 
accessible à l'adresse <code>www.fabula.org/<strong>prénom.nomdefamille</strong></code></p>

<p>
Si vous ne souhaitez pas publier ces informations, il suffit de vous assurer que ces champs sont vides.
</p>

 <!-- ' -->


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
<p class="login-only"><a href="#" id="userdata-send">Valider</a></p>
</div>


<div id="colonnes-deux">
<div id="recently" class="login-only">
<h2>Vos ressources</h2>
  <p>Les ressources récemment taggées avec les tags auxquels vous êtes abonné(e).
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