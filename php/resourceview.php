<html>
<head>
<title>Resources par tag</title>
</head>
<body>

<form action="/resourceview.php" method="get">
            <p>Entrer un uri ou un identifiant de resource déjà présente dans la base</p>
             <input type="text" name="tagthing" maxlength="3" size="3"></input></p>
             <input type="submit" value="Submit"/>
           </form>




<?php

require_once('/usr/local/www/apache22/lib/jf/fk/folksoClient.php');

   if ($_GET['tagthing']) {
     $tagthing = substr($_GET['tagthing'], 0, 255);

     $fc = new folksoClient('localhost', '/tag.php', 'get');
     $fc->set_getfields(array('folksofancy' => $tagthing));
     $reslist = $fc->execute();

     if ($fc->query_resultcode() == 200) {

       print $reslist;

           $resources = new DomDocument();
           $resources->loadXML($reslist);
           
           $xsl = new DomDocument();
           $xsl->load("resourcelist.xsl");

           $proc = new XsltProcessor();
           $xsl = $proc->importStylesheet($xsl);
           $form = $proc->transformToDoc($resources);
           print $form->saveXML();
     }
     else {
       print $fc->query_resultcode();
     }
   }
?>