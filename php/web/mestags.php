<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2010-2012 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */

require_once('folksoDBconnect.php');
require_once('folksoDBinteract.php');
require_once('folksoFabula.php');
require_once('folksoClient.php');
require_once('folksoUserServ.php');
require_once('folksoPage.php');
require_once('folksoAuth.php');

if ((! $fp) || (! $fp instanceof folksoPage)) {
  $fp = new folksoPage();
} 


require("/var/www/dom/fabula/commun3/head_libs.php");
require("/var/www/dom/fabula/commun3/head_folkso2.php");
$page_titre = 'Fabula - Espace tags et gestion de compte';
$loggedIn = false;
$redacRight = false;
$adminRight = false; // this was "true" before. why?

if ($sessionValid) {
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
else { // not logged in
  header("Location: /tags/login.php?retour=/tags/mestags.php");
  exit();
}

try {
  $fa = new folksoAuth();
  $currentProviders = $fa->getConnectedProviders();
}
catch (configurationException $confE) {
  $error = 
    "Problème interne : nous n'avos pas pu "
    ."contacter votre service d'identification";
}
catch (userException $uE) {
  // this really should be impossible
  print "user exception";
  print $uE->getMessage();
  //  header('Location: /tags/login.php');
  exit();
}




require("/var/www/dom/fabula/commun3/head_dtd.php");

print "<html>\n<head>";

require("/var/www/dom/fabula/commun3/head_meta.php");
?>

<link rel="stylesheet" href="css/blueprint/screen.css" type="text/css" media="screen, projection"/>
<link rel="stylesheet" href="css/blueprint/print.css" type="text/css" media="print"/>	
<!--[if lt IE 8]><link rel="stylesheet" href="css/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->
<?php

require("/var/www/dom/fabula/commun3/head_css.php");

?>

<style>
* {
position: static;
 }

ul, li {
  margin-left: 0px;
}

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

div#title-and-docs {
    min-height: 300px;
    padding-left: 220px;
    background: url('/tags/images/taglogo.png') 0% 40%;
    background-repeat: no-repeat;
}
#title-and-docs h1 {
    font-size: x-large;
    font-weight: bold;
    padding-top: 1em;
}

emph {
    font-style: italic;
}

div#docGenerale {
    border: 1px dotted gray;
    padding-top: 5px;
    padding-left: 8px;
    padding-right: 8px;
    padding-bottom: 5px;
}

div#docContainer {
    padding-left: 1.5em;
    padding-top: 0.5em;
    margin-top: 1em;
}
div#docGenerale p {
    font-size: 9pt;
}

span.moretext { 
    display:none;
}

div#test { background-color: red;
}
ul#userNav li {
    text-align: left;
    display: inline; 
    list-style-type: none; 
  padding-right: 1.4em;
    font-size: 8pt;
    color: #d45500;
}
ul#userNav a, ul#userNav a:link, ul#userNav a:visited, ul#userNav a:active {
    color: #d45500;
    border-bottom: none;
  text-align: left;
}

div#user-intro {
    padding-top: 1em; 
    padding-bottom: 1em;
    border-top: 1px dotted #d45500;
    border-bottom: 1px dotted #d45500;
    margin-bottom: 0.3em;

}

a#addsub, a#userdata-send {
    color: white;
    background-color: #d45500;
    font-weight: bold;
    padding: 2px;
    border-bottom: none;
}

div#newsubscriptions {
    padding-top: 1em;
}

a#userdata-send {
    margin-top: 8px; // ne marche pas, espace mangé par tinymce
}

div#currentTags {
    padding-top: 1.5em
}

#greeting {
    font-weight: bold;
}


div#userdata {
    border-top: 1px dotted #d45500;
    margin-top: 2em;
    padding-top: 1.5em;
}

div#userdata p {
    padding-left: 2em;
    padding-right: 2em;
}

span.inputlabel {
    font-weight: bold;
}

div#recently h2 {
    margin-top: 36px;
}

#resList a, #resList a:visited, #resList a:active, #resList a:link, #favtags a, #favtags a:visited, #favtags a:active, #favtags a:link {
    border-bottom: none;
}
#resList li {
    margin-top: 3px;
}

#loggedVia {
   color: gray;
   font-style: italic;
}

div#cv-view {
    border: 1px dotted gray;
    padding: 1em;
    margin: 0.5em;
}

div#cv-view p {
    padding-left: 0;
}

div#cv-view li  {
    list-style: disc;
    margin-left: 1em;
}

div#lastNav {
    border-bottom: 1px dotted #d45500;
}

div#lastNav p, #logout {
    color: #d45500;
    font-size: small;
    border-bottom: none;
}

div.user-cv, div.user-cv p {
position: static;
}

div.udatafield {
position: static;
}

div#tabs-1 a, div#tabs-1 a:link, div#tabs-1 a:active, div#tabs-1 a:hover, div#tabs-1 a:visited {
  border-bottom: none;
}

div#tabs-1 li {
  list-style: none;
}

.not-logged, #login-tabs {
   display: none;
}
.login-only, #logout, #logout2 {
   display: inline;
}

#login-tabs { display: none; } 

#gestionIdLink {
  font-size: 10pt;
  text-decoration: none;
  color: #d45500;
  border-bottom: none;
}

#gestionIdBox {
display: none;
font-size: 9pt;
padding-left: 2em;
padding-right: 2em;
border: dotted grey 1px;
}
#gestionIdClose{
  float: right;
}
</style>

<link rel="stylesheet" type="text/css" href="/tags/css/jquery-ui-1.8.9.custom.css" media="screen"/>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js"></script> 

<script type="text/javascript"
  src="/tags/js/tinymce/jscripts/tiny_mce/jquery.tinymce.js">
</script>

<script type="text/javascript" src="/tags/js/jquery-ui-1.8.9.custom.min.js"></script>
<script type="text/javascript" src="/tags/js/jquery.folksodeps.js"></script>
<script type="text/javascript" src="/tags/js/folksonomie.min.js"></script> 

<script type="text/javascript" 
  src="/tags/js/folkso-user.js">
</script>
<script type="text/javascript"
  src="/tags/js/pageinit.js">
</script>


<?php
    print $fp->jsHolder($fp->fKjsLoginState('fK.loginStatus')
                        . $fp->fbJsVars());
?>

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

          $("a.morelink").click(
                                function(ev) {
                                    ev.preventDefault();
                                    var $link = $(this);
                                    var $more = $($link.siblings("span.moretext")[0]);
                                    $more.toggle();
                                });

          var 
          $gestionIdBox   = $("#gestionIdBox"),
          $gestionIdLink  = $("#gestionIdLink"),
          $gestionIdClose = $("#gestionIdClose");

          $gestionIdLink.click(
              function(ev) {
                  ev.preventDefault();
                  $gestionIdBox.toggle(300);
              });

          $gestionIdClose.click(
              function(ev) {
                  ev.preventDefault();
                  $gestionIdBox.hide(300);
              });
          



      });



</script>
<?php

  //require ('/var/www/dom/fabula/commun3/browser_detect.php');

echo ("</head>\n<body>");

require("/var/www/dom/fabula/commun3/html_start.php");

?> 
<div id="colonnes_nouvelles">
<div id="colonnes-un" class="span-15">

<div id="title-and-docs">


  <!-- Documentation block, javascript enabled -->
<h1>Espace tags</h1>
<div id="docContainer">
<div id="docGenerale" class="quiet">

<p><strong>Qu'est-ce qu'un <emph>tag</emph> ?</strong> Le terme anglais <emph>tag</emph> 
désigne une étiquette ou <strong>mot clé</strong>. <a href="#" class="morelink">(suite)</a><span class="moretext">(Voir l'<a 
 href="http://fr.wikipedia.org/wiki/Tag_%28m%C3%A9tadonn%C3%A9e%29">article Wikipédia</a>.)
  Sur Fabula, les <emph>tags</emph> sont appliqués par les lecteurs afin de faciliter l'accès
aux informations sur le site, de façon plus pertinente que les moteurs de recherche. Vous êtes 
invité(e) à <emph>«&#160;tagger&#160;</emph> autant de pages que vous souhaitez. Ce faisant, 
vous enrichissez le site pour tous les utilisateurs.</span></p>

<p>
 <strong>A quoi sert votre «&#160;Espace Tags&#160;» ?</strong> Cette page vous permet de choisir des 
  <emph>tags</emph> qui vous intéressent, et de les <emph>suivre</emph>.  <a href="#" class="morelink">(suite)</a><span class="moretext">Chaque fois
  que quelqu'un applique l'un  de vos <emph>tags</emph>  à une nouvelle ressource sur Fabula 
(une annonce dans les nouvelles, un texte de l'Atelier, un compte-rendu dans <emph>Acta Fabula</emph>, etc.), un lien
vers la ressource apparaîtra dans la liste <emph>Vos
ressources</emph>, à droite.</span> </p> <p> La page "Espace tags"
vous donne également la possibilité de vous présenter et de publier un
mini-CV.  </p>

</div>
</div>

</div>


<div id="user-intro" class="login-only span-15">
<div class="span-6" id="greeting">
<p>Bonjour <span class="userhello"></span> !</p>
</div>
            <?php 
            /**
             * $currentProviders = les services utilisés en ce moment
             * avec hybridauth. Il peut n'y en avoir aucun, si la
             * session folksonomie (folksoSession) est valable en
             * dehors des sessions hybridauth. (This is a feature.)
             */
            if (count($currentProviders) > 0) { 
              ?>
 <p id="loggedVia" class="span-15">Loggé(e) via <span id="loginSource">
              <?php
              echo implode(', ', $currentProviders);
              ?>
              </span></p>
              <?php
            } // end if $currentProviders
          ?>
 <p><a href="#" id="gestionIdLink">Gérer ses services d'indentification</a></p>
<div class="hidden span-13" id="gestionIdBox">
   <p><strong>Comptes actuels</strong>  Vous avez déjà associé votre 
compte Fabula avec le(s) service(s) suivant(s) :
</p>
   <ul>
   <?php //'
   foreach ($u->getLoginServices() as $prov) {
            print "<li>" . $prov['fullname'] . "</li>";
          }
   ?>
</ul>

  <p>Vous pouvez sélectionner un autre service à associer à votre compte
                                                                                                               Fabula parmi les suivants :
</p>
  <ul>
    <li><a href="/tags/login.php?provider=Google&ajouterservice=true">Google</a></li> 
    <li><a href="/tags/login.php?provider=Yahoo&ajouterservice=true">Yahoo</a></li> 
    <li><a href="/tags/login.php?provider=Facebook&ajouterservice=true">Facebook</a></li>
    <li><a href="/tags/login.php?provider=Twitter&ajouterservice=true">Twitter</a></li>
    <li><a href="/tags/login.php?provider=LinkedIn&ajouterservice=true">LinkedIn</a></li> 
  </ul>
<a href="#" id="gestionIdClose"><img src="/tags/images/close_x_20px.png"/></a>
</div>



<ul id="userNav" class="span-9 login-only">
  <?php // Admin, redac link
  if ($redacRight || $adminRight) {
    ?>
    <li><a id="adminLink" href="/tags/adminlogin.php">Gestion tags</a></li>
    <?php
  }

 if (($u instanceof folksoUser) && $u->nameUrl()) {
       $userUrl =  'http://www.fabula.org/' . $u->nameUrl();
       echo sprintf('<li><a id="publicLink" href="%s">Page publique</a></li>',
                    $userUrl);
 }
?>

<li><a href="/tags/logout.php" id="logout2" class="login-only">Quitter</a></li>
</ul>
</div>

<p id="tag-brag" class="login-only quiet">Vous avez appliqué <span id="tagcount"></span> tags.</p>

<div id="currentTags" class="span-15 login-only">
<div id="subscriptions" class="login-only  span-7">
<h2>Vos abonnements</h2>
  <p>Les tags auxquels vous êtes actuellement abonné(e) : </p>
<ul>
</ul>
</div>


<div id="favtags" class="login-only span-6 prepend-1 colsep last">
<h2>Vos tags préférés</h2>
  <p>Les tags que vous appliquez le plus souvent : </p> 
<ul class="fav-list">
</ul>
</div>
</div>

<div id="newsubscriptions" class="login-only span-15">
<h2>Vous abonner à de nouveaux tags</h2> 
<p>Choisir un nouveau tag:</p>
<input type="text" class="fKTaginput login-only" id="newsubbox" size="60">
</input>
<a id="addsub" href="#" class="actionButton">OK</a>
</div>



<div id="userdata" class="login-only span-15">
<h2>Vos données</h2>
  <p>Lorsque vous renseignez les champs "Prénom" et "Nom de famille", 
  une page personnelle sera créée où vous pourrez afficher vos coordonnées et un mini-CV, 
accessible à l'adresse:  <code>www.fabula.org/<strong>prenom.nomdefamille</strong>.</code> (Notez que les accents sont supprimés.)</p> <!-- ' -->
<div id="publique">
<?php

if ($userUrl) {
  echo sprintf('<p>Votre page publique : <a class="pagepublique" 
                                                   href="%s">%s</a></p>',
               $userUrl,  $userUrl);
}

?>
</div>
<p>
Si vous ne souhaitez pas publier ces informations, il suffit de vous assurer que ces champs sont vides.
</p>

<div class="udatafield span-15 firstname">
<div class="span-4 firstname">
  <span class="inputlabel">
  Prénom : 
  </span>
</div>
<div class="span-4">
<span class="firstname">&#160;</span>
</div>
<div class="span-5 prepend-1">
  <input type="text" class="firstnamebox">
  </input>
</div>
</div>

<div class="udatafield span-15 lastname">
<div class="span-4 lastname">
  <span class="inputlabel">
  Nom de famille :
  </span>
</div>
<div class="span-4">
<span class="lastname">&#160;</span>
</div>
<div class="span-5 prepend-1">
  <input type="text" class="lastnamebox">
  </input>
</div>
</div>

<div class="udatafield span-15 email">
<div class="span-4 email">
  <span class="inputlabel">
  Courrier électronique :
  </span>
</div>
<div class="span-4">
<span class="email">&#160;</span>

</div>
<div class="span-5 prepend-1">
  <input type="text" class="emailbox">
  </input>
</div>
</div>

<div class="udatafield span-15 institution">
<div class="span-4 institution">
  <span class="inputlabel">
  Institution :
  </span>
</div>
<div class="span-4">
<span class="institution">&#160;</span>
</div>
<div class="span-5 prepend-1">
  <input type="text" class="institutionbox">
  </input>
</div>
</div>

<div class="udatafield span-15 pays">
<div class="span-4 pays">
  <span class="inputlabel">
  Pays :
  </span>
</div>
<div class="span-4">
<span class="pays">&#160;</span>
</div>
<div class="span-5 prepend-1">
  <input type="text" class="paysbox">
  </input>
</div>
</div>

<div class="udatafield span-15 fonction">
<div class="span-4 fonction">
  <span class="inputlabel">
  Fonction : 
  </span>
</div>
<div class="span-4">
<span class="fonction">&#160;</span>
</div>

<div class="span-5 prepend-1">
  <input type="text" class="fonctionbox">
  </input>
</div>
</div>

<div class="user-cv">
  <p><span class="inputlabel">CV:  </p>
<div id="cv-view"></div>
  <textarea cols="80" rows="20" class="cv-write" id="cveditor" name="cveditor"></textarea>
  </input>
</div>


<p class="login-only"><a href="#" id="userdata-send">Valider</a> <span id="validerMessage"></span></p>



</div>



<div class="login-only span-15" id="lastNav">
<div class="span-3 last prepend-11">
<a href="/tags/logout.php" class="login-only" id="logout">Quitter</a>
</div>
</div>

</div>


<div id="colonnes-deux">
<div id="recently" class="login-only">
<h2>Vos ressources</h2>
  <p class="quiet">Les ressources récemment taggées avec les 
tags auxquels vous êtes abonné(e).
   Si une ressource comporte plus d'un de vos tags, il apparaîtra
plus haut dans la liste.</p>
<ul id="resList"></ul>
</div>
</div>
</div>
</div>
<?php

include("/var/www/dom/fabula/commun3/foot.php");

