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
<form action="tagedit.php" method="get">
   <p>Pour mieux cibler les tags à éditer, saisir une, deux ou trois
   lettres du début des tags recherchés: <input type="text"
   name="letters" maxlength="3" size="3"></input>
    </p>
   <p>
        <input type="submit" value="Submit"/>
   </p>
</form>

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
</div>
<?php
require_once('folksoClient.php');
require_once('folksoFabula.php');

$loc = new folksoFabula();

$fc = new folksoClient('localhost', 
                       $loc->get_server_web_path(). 'tag.php', 
                       'get');

if (isset($_GET['letters'])) {
    $alpha = substr($_GET['letters'], 0, 3);
    $fc->set_getfields(array('folksobyalpha' => $alpha));
}
else { //if no letter specified, get all tags 
  $fc->set_getfields(array('folksoalltags' => 1));
}

    $taglist = $fc->execute();

//    print $taglist;

    if ($fc->query_resultcode() == 200) {
      if (strlen($taglist) == 0) {
        trigger_error("No taglist data", E_USER_ERROR);
      }

      $tags = new DomDocument();

      $tags->loadXML($taglist);
      $xsl = new DomDocument();
      $xsl->load($loc->xsl_dir . "tagform.xsl");

      $proc = new XsltProcessor();
      $xsl = $proc->importStylesheet($xsl);
      $form = $proc->transformToDoc($tags);
      print $form->saveXML();
    }
    else {
      print $fc->query_resultcode();
      print $taglist;
    }
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
