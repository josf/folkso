<html xmlns="http://www.w3.org/1999/xhtml">
<head>
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
        href="jquery.autocomplete.css"
        media="screen">
    </link>
<link rel="stylesheet" type="text/css" href="tagedit.css" media="screen">
</link>
  

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js">
  </script>

<?php 
   /**
    *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */

require_once('folksoClient.php');
require_once('folksoFabula.php');
require_once('folksoAdmin.php');

$loc = new folksoFabula();
$fk = new folksoAdmin();

/** if basic authentication info is present, we send it back to the
    browser for use in ajax requests **/
print $fk->BasicAuthJSScript();

print "<script type='text/javascript'>\n\n";
print $loc->WebPathJS();
print "</script>\n";
?>
<!-- <script type="text/javascript" src="js/folkso.js"></script> -->
<script type="text/javascript" src="js/tagedit.js"></script>

</head>
<body>
<div id="container">
<div id="pagehead">
<h1>Gestion des tags : création, suppression, modification, fusion</h1>

<p>
  La <em>suppression</em> d'un tag détruit en même temps et
  définitivement toutes les références vers ce tag. En revanche, la
  <em>fusion</em> d'un tag avec un autre préserve les références en
  les dirigeant vers le tag "cible" de la fusion.
</p>

<h3>Sélection des tags</h3>

<form action="tag.php" method="post">
   <h3>Créer un nouveau tag :</h3>
   <p>
      <input type="text" name="folksonewtag" id="tagcreatebox" maxlength="255" size="50"/>
      <input type="submit" id="tagcreatebutton" value="Créer"/>
   </p>
</form> 

<ul class="pagecommands">
  <li><a href="#" class="seealltags">Afficher tous les tags</a></li>
  <li><a href="#" class="restags">Afficher seulement les tags <em>déjà utilisés</em></a> (Associés à des ressources)</li>
  <li><a href="#" class="norestags">Afficher seulement les tags <em>non utilisés</em></a> (Associés à aucune ressource)</li>
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
  <li><a href="#" class="seealltags">Afficher tous les tags</a></li>
  <li><a href="#" class="restags">Afficher seulement les tags <em>déjà utilisés</em></a> (Associés à des ressources)</li>
  <li><a href="#" class="norestags">Afficher seulement les tags <em>non utilisés</em></a> (Associés à aucune ressource)</li>
  <li><a href="#">Retour au début de la page</a></li>
</ul>

</div>
</body>
</html>
