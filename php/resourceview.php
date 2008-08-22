<html>
<head>
<title>Resources par tag</title>
<link 
   rel="stylesheet" type="text/css" 
   href="http://www.fabula.org/commun3/template.css" 
   media="screen">
<link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/print.css" media="print">
<link rel="shortcut icon" type="image/x-icon" href="http://www.fabula.org/favicon.ico">
<style type="text/css">
.tagname { font-weight: bold; font-size: 14pt}
#container { background-color: white;}
ul.resourcelist li a, ul.resourcelist li a:link, ul.resourcelist li a:visited { font-size: 14pt; 
font-color: orange;}

ul.resourcelist li { margin-bottom: 1em}
ul.resourcelist li a.tocloud, ul.resourcelist li a:visited.tocloud, ul.resourcelist li a:link.tocloud {font-size:10pt;}
</style>

</head>
<body>
<div id="container">
<form action="/resourceview.php" method="get">
            <p>Entrer un uri ou un identifiant de resource déjà  présente dans la base</p>
             <input type="text" name="tagthing" maxlength="3" size="3"></input></p>
             <input type="submit" value="Submit"/>
           </form>




<?php

require_once('folksoClient.php');
require_once('folksoFabula.php');

$loc = new folksoFabula();

   if ($_GET['tagthing']) {
     $tagthing = substr($_GET['tagthing'], 0, 255);

     $fc = new folksoClient('localhost', 
                            $loc->server_web_path . 'tag.php', 
                            'get');

     $fc->set_getfields(array('folksotag' => $tagthing, 'folksofancy' => '1'));
     $reslist = $fc->execute();

     //     print $reslist;

     if ($fc->query_resultcode() == 200) {

           $resources = new DOMDocument();
           $resources->loadXML($reslist);
           
           $xsl = new DOMDocument();
           $xsl->load($loc->xsl_dir . "resourcelist.xsl");

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

</div>
</body>
</html>