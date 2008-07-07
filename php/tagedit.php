<html>
<head>
<title>Editons les tags</title>
<link 
   rel="stylesheet" type="text/css" 
   href="http://www.fabula.org/commun3/template.css" 
   media="screen">
<link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/print.css" media="print">
<link rel="shortcut icon" type="image/x-icon" href="http://www.fabula.org/favicon.ico">

<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="folkso.js"></script>

<style type="text/css">
.tagname { font-weight: bold; font-size: 14pt}
#container { background-color: white;}
</style>

</head>
<body>
<div id="container">
<h1>Gestion des tags</h1>

<form action="/tagedit.php" method="get">
   <p>Taper une, deux ou trois lettres pour sélectionner les tags à éditer: 
        <input type="text" name="letters" maxlength="3" size="3"></input>
    </p>
   <p>
        <input type="submit" value="Submit"/>
   </p>
</form>

<form action="/tag.php" method="post">
   <p>Créer un nouveau tag : 
      <input type="text" name="folksonewtag" maxlength="255" size="50"/>
      <input type="submit" value="Créer"/>
   </p>
</form> 

<?php
require_once('folksoClient.php');

if (isset($_GET['letters'])) {
    $alpha = substr($_GET['letters'], 0, 3);
    $fc = new folksoClient('localhost', '/tag.php', 'get');
    $fc->set_getfields(array('folksobyalpha' => $alpha));
    $taglist = $fc->execute();

    if ($fc->query_resultcode() == 200) {
      if (strlen($taglist) == 0) {
        trigger_error("No taglist data", E_USER_ERROR);
      }

      $tags = new DomDocument();
      $tags->loadXML($taglist);
      $xsl = new DomDocument();
      $xsl->load("/usr/local/www/apache22/data/tagform.xsl");

      $proc = new XsltProcessor();
      $xsl = $proc->importStylesheet($xsl);
      $form = $proc->transformToDoc($tags);
      print $form->saveXML();
    }
    else {
      print $fc->query_resultcode();
      print $taglist;
    }

  }

?>
</div>
</body>
</html>
