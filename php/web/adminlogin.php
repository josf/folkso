<?php

  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008-2010 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */


  /**
   * Simple login page for the web interface.
   */

require_once "/var/www/dom/fabula/commun3/head_folkso.php"; 

$page_titre = 'Folksonomie - Administration';
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
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js"></script> 



<script type="text/javascript"
  src="/tags/js/tinymce/jscripts/tiny_mce/jquery.tinymce.js">
</script>
<script type="text/javascript" src="/tags/js/jquery-ui-1.8.9.custom.min.js"></script>
<script src="http://connect.facebook.net/en_US/all.js"></script>
<script type="text/javascript" src="/tags/js/jquery.folksodeps.js"></script>
<script type="text/javascript" src="/tags/js/folksonomie.min.js"></script> 

<script type="text/javascript"
  src="/tags/js/pageinit.js">
</script>
<?php
 if ($fp instanceof folksoPage) {
	print $fp->jsHolder($fp->fKjsLoginState('fK.loginStatus')
				. $fp->fbJsVars());

} 

?>
<script type="text/javascript">
  $(fK.events).bind('userLogout',
                    function() {
                      window.location.reload();
                    });

</script>

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
  <h1>Administration folksonomie</h1>
<a href="#" id="logout">Quitter</a>
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
    <?php
    if ($adminRight) {
      ?>
    <li>
    <a href="http://fabula.org/tags/uadmin.php">
    Gestion des utilisateurs
    </a> (niveau "admin" nécessaire)
    </li>
      <?php } ?>
</ul>

</div>    
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

?>

</div></div>
<?php

include("/var/www/dom/fabula/commun3/foot.php");
