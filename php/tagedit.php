<html>
<head>
<title>Editons les tags</title>
</head>
<body>

<?php
require_once('/usr/local/www/apache22/lib/jf/fk/folksoClient.php');

if (!isset($_GET['letters'])) {
    print '<form action="/tagedit.php" method="get">
            <p>Il faut inscrire une deux ou trois lettres pour sélectionner les tags à éditer: 
             <input type="text" name="letters" maxlength="3" size="3"></input></p>
             <input type="submit" value="Submit"/>
           </form>';
  }
  else {
    $alpha = substr($_GET['letters'], 0, 3);
    $fc = new folksoClient('localhost', '/tag.php', 'get');
    $fc->set_getfields(array('folksobyalpha' => $alpha));
    $taglist = $fc->execute();

    if (strlen($taglist) == 0) {
      trigger_error("No taglist data", E_USER_ERROR);
    }

    $tags = new DomDocument();
    $tags->loadXML($taglist);
    $xsl = new DomDocument();
    $xsl->load("tagform.xsl");

    $proc = new XsltProcessor();
    $xsl = $proc->importStylesheet($xsl);
    $form = $proc->transformToDoc($tags);
    print $form->saveXML();


  }
?>
</body>
</html>
