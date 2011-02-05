<?php
   /**
    *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */
require_once "/var/www/dom/fabula/commun3/head_folkso.php"; 
require_once('folksoClient.php');
require_once('folksoFabula.php');
require_once('folksoAdmin.php');

$loc = new folksoFabula();
$fk = new folksoAdmin();


$login_page = 'http://www.fabula.org/tags/admin/adminlogin.php';
$sorry = 'http://www.fabula.org/tags/admin/sorry.php';

if (! $fks->status()) {
  header('Location: ' . $login_page);
  exit();
}

$user = $fks->userSession($sid);
if (! $user->checkUserRight('folkso', 'redac')) {
  header('Location: ' . $sorry);
  exit;
}


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Language" content="fr-FR"/>
<title>Fabula -> Folksonomie : édition des tags</title>
<link 
   rel="stylesheet" type="text/css" 
   href="http://www.fabula.org/commun3/template.css" 
   media="screen">
</link>
<link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/print.css" media="print">
</link>
<!-- <link rel="shortcut icon" type="image/x-icon" href="http://www.fabula.org/favicon.ico"></link> -->
   <link 
        rel="stylesheet" type="text/css"
        href="/tags/css/jquery.autocomplete.css"
        media="screen">
    </link>
<link rel="stylesheet" type="text/css" href="tagedit.css" media="screen">
</link>
  

<link rel="stylesheet" 
  type="text/css" 
  href="/tags/css/jquery-ui-1.8.4.custom.css" media="screen">
</link>
<link 
  rel="stylesheet"
  type="text/css"
  href="/tags/css/fk-admin.css"
  media="screen"></link>

<script type="text/javascript" 
        src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js"></script>

<script 
  type="text/javascript"
  src="/tags/js/jquery-ui-1.8.custom.min.js">
</script>

<script type="text/javascript" src="/tags/js/jquery.autocomplete.js">
  </script>

<?php

/** if basic authentication info is present, we send it back to the
    browser for use in ajax requests **/
print $fk->BasicAuthJSScript();

print "<script type='text/javascript'>\n\n";
print $loc->WebPathJS();
print "</script>\n";
?>
<!-- <script type="text/javascript" src="js/folkso.js"></script> -->
<script type="text/javascript" src="/tags/js/tagedit.js"></script>

</head>
<body>
<div id="container">
<div id="pagehead">
<h1>Gestion des tags : création, suppression, modification, fusion</h1>

<ul id="adminNav">
<li><a href="/tags/editresources.php">Éditer ressources</a></li>
<li><a href="/tags/uadmin.php">Gestion utilisateurs</a></li>
<li><a href="/tags/mestags.php">Espace tags</a></li>
<li><a href="#" id="logout">Quitter</a></li>
</ul> 
<p>
  La <em>suppression</em> d'un tag détruit en même temps et
  définitivement toutes les références (c'est-à-dire les actes de taggage 
faits par les utilisateurs)  vers ce tag. En revanche, la
  <em>fusion</em> d'un tag avec un autre préserve les références en
  les dirigeant vers le tag "cible" de la fusion.
</p>



<form action="tag.php" method="post">
   <h3>Créer un nouveau tag :</h3>
   <p>
      <input type="text" name="folksonewtag" id="tagcreatebox" maxlength="255" size="50"/>
      <input type="submit" id="tagcreatebutton" value="Créer"/>
   </p>
</form> 

<h3>Sélection des tags&#160;:</h3>
<ul class="pagecommands">
  <li><a href="#" class="seealltags">Tous les tags</a></li>
  <li><a href="#" class="restags">Tags <em>déjà utilisés</em></a></li>
  <li><a href="#" class="norestags">Tags <em>non utilisés</em></a></li>
</ul>

<ul id="letterlist">
  <?php
  foreach (array('a', 'b', 'c', 'd', 'e', 'f', 
                'g', 'h', 'i', 'j', 'k', 'l', 
                'm', 'n', 'o', 'p', 'q', 'r', 
                 's', 't', 'u', 'v', 'w', 'x', 'y', 'z') as $letter) {

  print 
  "<li><a href=\"#\" id=\"letter$letter\" class=\"selectletter\">". strtoupper($letter)
  . "</a></li>\n";

}

  ?>
</ul>
</div>
<?php



?>
<ul class="pagecommands">
  <li><a href="#" class="seealltags">Tous les tags</a></li>
  <li><a href="#" class="restags">Tags <em>déjà utilisés</em></a></li>
  <li><a href="#" class="norestags">Tags <em>non utilisés</em></a></li>
</ul>


</div>
</body>
</html>
