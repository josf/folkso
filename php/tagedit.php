<html>
<head>
<title>Editons les tags</title>
<link 
   rel="stylesheet" type="text/css" 
   href="http://www.fabula.org/commun3/template.css" 
   media="screen">
<link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/print.css" media="print">
<link rel="shortcut icon" type="image/x-icon" href="http://www.fabula.org/favicon.ico">
<style type="text/css">
.tagname { font-weight: bold; font-size: 14pt}
#container { background-color: white;}
</style>

</head>
<body>
<div id="container">
<h1>Gestion des tags</h1>

<form action="/commun3/folksonomie/tagedit.php" method="get">
   <p>Taper une, deux ou trois lettres pour sélectionner les tags à éditer: 
        <input type="text" name="letters" maxlength="3" size="3"></input>
    </p>
   <p>
        <input type="submit" value="Submit"/>
   </p>
</form>

<form action="/commun3/folksonomie/tag.php" method="post">
   <p>Créer un nouveau tag : 
      <input type="text" name="folksonewtag" maxlength="255" size="50"/>
      <input type="submit" value="Créer"/>
   </p>
</form> 

<?php
require_once('/var/www/dom/fabula/commun3/folksonomie/folksoClient.php');

if (isset($_GET['letters'])) {
    $alpha = substr($_GET['letters'], 0, 3);
    $fc = new folksoClient('localhost', '/commun3/folksonomie/tag.php', 'get');
    $fc->set_getfields(array('folksobyalpha' => $alpha));
    $taglist = $fc->execute();

    if ($fc->query_resultcode() == 200) {
    if (strlen($taglist) == 0) {
      trigger_error("No taglist data", E_USER_ERROR);
    }

    $tags = new DomDocument();
    $tags->loadXML($taglist);
    $xsl = new DomDocument();
    $xsl->load("/var/www/dom/fabula/commun3/folksonomie/xsl/tagform.xsl");

    $proc = new XsltProcessor();
    $xsl = $proc->importStylesheet($xsl);
    $form = $proc->transformToDoc($tags);
    print $form->saveXML();
    }
    else {
      print $fc->query_resultcode();
    }

  }

?>
</div>
</body>
</html>
