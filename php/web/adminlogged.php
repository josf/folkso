<?php

  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008-2010 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */

require_once "/var/www/dom/fabula/commun3/head_folkso.php"; 

$page_titre = 'Folksonomie - accès à l\'interface de gestion';

if ($fks->status()) {
  $loggedIn = true;
  $user = $fks->userSession();
  if ($user->checkUserRight('folkso', 'redac')) {
    $hasRights = true;
  }
}


  /**
   * Simple login page for the web interface.
   */

$fab = new FabElements();

require("/var/www/dom/fabula/commun3/head_dtd.php");

print "<html>\n<head>";

require("/var/www/dom/fabula/commun3/head_meta.php");
require("/var/www/dom/fabula/commun3/head_css.php");
require("/var/www/dom/fabula/commun3/html_start.php");
?>

<div id="colonnes_nouvelles">
<div id="colonnes-un">


<?php
if (! $loggedIn ) {
?>

<h1>Utilisateur non logué</h1>
<p>Veuillez vous loguer sur <a href="adminlogin.php">
la page de login de la gestion de tags</a>.
</p>

<?php 

}

elseif ( $hasRights ) {
?>
  <h1>Vous êtes loggué(e).</h1>

<p>Vous pouvez vous rendre sur les pages de gestion de tags.</p>
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
    <a href="http://fabula.org/tags/uadmin.php">
    Gestion des utilisateurs
    </a>
    </li>
</ul>
    
<?php
}

else { // logged in but insufficient rights

?>
<h1>Droits insuffisants</h1>
    <p>Nous sommes désolés, mais vous ne disposez pas des droits nécessaires
    pour accéder à l'interface de gestion des tags. Veuillez nous en excuser si 
    vous pensez qu'il s'agit d'une erreur.</p>
<?php
}

print $fab->footHtml();






