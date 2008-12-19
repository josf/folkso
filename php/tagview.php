
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>

   <link 
        rel="stylesheet" type="text/css" 
        href="http://www.fabula.org/commun3/template.css" 
        media="screen">
    </link>

<style type="text/css">
#container { background-color: white; padding: 2em; }

</style>
<?php

require_once('folksoPage.php');
require_once('folksoPageData.php');



$tagreq = $_GET['tag'];

if ((strlen($tagreq) == 0) ||
    (strlen($tagreq) > 300)) {
  die("Requête malformée, tag impossible");
}

$page = new folksoPage($tagreq);
$taglist = $page->TagResources();

print "<title>Fabula - Tags - tag \"";
if ($page->tr->is_valid()) {
  print $page->tr->title();
}
else {
  print " Erreur ";
}

print "\"</title>\n</head>\n<body>\n<div id='container'>";

if ($page->tr->is_valid()) {
  print "<h1>Ressources associées au tag : </h1>\n";
  print $taglist; 
}

print "</div>\n</body>\n";

?>