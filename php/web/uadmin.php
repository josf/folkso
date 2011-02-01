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
$titre = "Folksonomies : gérer les utilisateurs";

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

input.rightsel {
display: inline; margin-left: 0.5em;
}

form.rightsel {
display: inline; 
}

label.rightsel {
display: inline;
  margin-left: 0.2em;
}

#masterlist li {
margin-top: 1em;
margin-bottom: 0.3em;
border: 1px grey solid;
padding: 0.3em;
}

#masterlist .details li {
margin-top: 0.2em; margin-bottom: 0.1em;
border: none;
padding: 0.1em;
}

span.realname {
  font-weight: bold;
size: 14pt;
}

a.rightModButton {
  margin-left: 0.7em;
}

a.delete-user, a.rightModButton {
color: #e86a24;
  font-weight: bold;
}

span.rightmod-done {
size: 8pt;
color: #e86a24;
  margin-left: 1em;
}

div.doc-expert {
display: none;
}
</style>
<script type="text/javascript" src="/tags/js/jquery.folksodeps.js"></script>
<script type="text/javascript" src="/tags/js/folksonomie.min.js"></script>
<?php
require("/var/www/dom/fabula/commun3/head_javascript.php");
require("/var/www/dom/fabula/commun3/head_javascript_folkso.php");

?> 

<script type="text/javascript" 
  src="/tags/js/folkso-admin.js">
</script>

<script type="text/javascript">
  $(fK.events).bind("loggedOut",
                    function () {
                        window.location = "/tags/mestags.php";                        
                    });

</script>

<?php

if (stristr($_SERVER['HTTP_USER_AGENT'], 'iPhone')) {
echo ("</head>\n<body>");
echo ("<h1 class=\"titre_iphone\">Visitez notre site optimis<C3><A9> <br><a href=\"http://iphone.fabula.org\">iphone.fabula.org</a></h1>");
} else {
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

?><h1 class="not-logged">Il faut vous identifier</h1> <!-- -->
<p>Veuillez <a href="http://www.fabula.org/tags/admin/adminlogin.php">vous loguer.</a></p>

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

<ul>
    <li>
    <a href="http://www.fabula.org/tags/editresources.php">
    Interface de taggage 
    </a> : tagger les pages du site.
    </li>
    <li>
    <a href="http://www.fabula.org/tags/tagedit.php">
    Edition des tags
    </a> : modifier, fusionner, supprimer les tags du site.
    </li>
    <li>
</ul>
<?php
}
else {
?>

<a href="#" id="logout">Quitter</a>
<div id="usersearch">
<h2>Recherche d'utilisateur</h2> <!-- ' -->

<input id="searchbox" type="text"></input>
<a href="#" id="searchok">OK</a>
</div>

<a href="#" class="show-expert">Avancé</a>

<div class="doc-expert">
<p>
    Saisir un ou plusieurs noms ou prénoms. Pour affiner la recherche, vous pouvez utiliser des mots clés suivis d'un deux-points, comme <strong>fname:</strong> pour les prénoms, ou <strong>lname:</strong> pour les noms de famille. 
</p><!--  -->
<p>
    Le mot clé <strong>and:</strong> peut s'utiliser avec <strong>fname:</strong>
    et <strong>lname:</strong> pour limiter les recherches : "and: fname: Marcel lname: Proust" exclut tous les Proust et tous les Marcel sauf "Marcel Proust".
</p>
<p>
    Le mot clé <strong>recent:</strong> retourne une liste des utilisateurs les plus récemment inscrits.
</p>
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

