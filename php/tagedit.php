<html>
<head>
<title>Editons les tags</title>
<link 
   rel="stylesheet" type="text/css" 
   href="http://www.fabula.org/commun3/template.css" 
   media="screen">
<link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/print.css" media="print">
<link rel="shortcut icon" type="image/x-icon" href="http://www.fabula.org/favicon.ico">
   <link 
        rel="stylesheet" type="text/css"
        href="jquery.autocomplete.css"
        media="screen">
    </link>


<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js">
  </script>
<script type="text/javascript" src="js/folkso.js"></script>
<script type="text/javascript" src="js/tagedit.js"></script>

<style type="text/css">
.tagname { font-weight: bold; font-size: 12pt}
   .tagcommands { display: none; 
 border: 2px solid grey; width: 400px; padding: 0.5em }
#container { background-color: white;}
</style>

</head>
<body>
<div id="container">
<h1>Gestion des tags</h1>

<form action="tagedit.php" method="get">
   <p>Taper une, deux ou trois lettres pour  sélectionner les tags à éditer: 
        <input type="text" name="letters" maxlength="3" size="3"></input>
    </p>
   <p>
        <input type="submit" value="Submit"/>
   </p>
</form>

<form action="tag.php" method="post">
   <p>Créer un nouveau tag : 
      <input type="text" name="folksonewtag" maxlength="255" size="50"/>
      <input type="submit" value="Créer"/>
   </p>
</form> 

<ul class="pagecommands">
  <li><a href="#" class="seealltags">Afficher tous les tags</a></li>
  <li><a href="#" class="restags">Afficher seulement les tags <em>déjà utilisés</em></a> (Associés à des ressources)</li>
  <li><a href="#" class="norestags">Afficher seulement les tags <em>non utilisés</em></a> (Associés à aucune ressource)</li>
</ul>

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
</ul>

</div>
</body>
</html>
