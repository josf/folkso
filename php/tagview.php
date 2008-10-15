
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

$page = new folksoPage();

$tagreq = $_GET['tag'];

if ((strlen($tagreq) == 0) ||
    (strlen($tagreq) > 300)) {
  die("Requête malformée, tag impossible");
}

$taglist = $page->public_tag_resource_list($tagreq);

print "<title>Fabula - Tags - tag \"";
if (($taglist['status'] == 200) ||
    ($taglist['status'] == 304)) {
  print $taglist['title'];
}
else {
  print " Erreur ";
}

print "\"</title>\n</head>\n<body>\n<div id='container'>";

if (($taglist['status'] == 200) ||
    ($taglist['status'] == 304)) {

  print "<h1> \"". $taglist['title'] . "\" </h1>\n";

  print 
    "<p>Voici la liste des ressources qui ont été associées au tag \"" 
    . $taglist['title']
    . "\".</p>\n";

  print $taglist['html'];
}

print "</div>\n</body>\n</html>\n";

?>
