<?php

require_once "/var/www/dom/fabula/commun3/head_folkso.php"; 

$page_titre = 'Folksonomie - édition de CV des rédacteurs et administrateurs';
$loggedIn = false;
$redacRight = false;
$adminRight = false; 

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
<link rel="stylesheet" type="text/css"  href="/tags/css/fk-admin.css"></link>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js"></script> 



<script type="text/javascript"
  src="/tags/js/tinymce/jscripts/tiny_mce/jquery.tinymce.js">
</script>
<script type="text/javascript" src="/tags/js/jquery-ui-1.8.9.custom.min.js"></script>
<script src="http://connect.facebook.net/en_US/all.js"></script>
<script type="text/javascript" src="/tags/js/jquery.folksodeps.js"></script>
<script type="text/javascript" src="/tags/js/folksonomie.min.js"></script> 

<script type="text/javascript" src="/tags/js/folkso-admincv.js"></script>

</head>
<body class="fk-admin">
<?php
  require("/var/www/dom/fabula/commun3/html_start.php");
?>

<div id="colonnes_nouvelles">
<div id="colonnes-un" class="span-15">

<?php


  if ($loggedIn === false) {
?>
<div id="nolog_accueil">
<h1>Vous n'êtes pas logué</h1>
<p>Veuillez vous identifier sur votre <a href="/tags/mestags.php">Espace Tags</a> 
pour accéder à l'interface de gestion.</p>
</div>
<?php //'
  }

elseif ($redacRight || $adminRight) {

?>
<div id="logged_accueil">
    <h1>Edition de CV, administrateurs et rédacteurs</h1>
<p>Cette interface d'éditer votre CV en ajoutant des liens et des images, qui ne sont pas
disponibles aux autres utilisateurs pour des raisons de sécurité.</p> <!-- ' -->

<p>Pour ajouter une image, elle doit être déjà en ligne, sur Fabula ou
ailleurs.</p> 

<p>La modification des autres champs dans vos données
personnelles peut se faire sur votre <a
href="/tags/mestags.php">Espace Tags</a> habituel.</p>

</div>

<div id="adminEditorContainer">
<p id="messageBox"></p>
<textarea id="adminCvEditor" name="adminCvEditor" cols="80" rows="20"></textarea>
<a href="#" class="actionButton" id="cv-send">Valider</a>

</div>

</div>
</div>

<?php
    }
include("/var/www/dom/fabula/commun3/foot.php");

