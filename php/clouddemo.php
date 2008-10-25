<html>
  <head>
    <title>Démo Nuage</title>
<link 
   rel="stylesheet" type="text/css" 
   href="http://www.fabula.org/commun3/template.css" 
   media="screen"/>
<link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/print.css" media="print"/>
<link rel="shortcut icon" type="image/x-icon" href="http://www.fabula.org/favicon.ico"/>
<style type="text/css">
.tagname { font-weight: bold; font-size: 14pt}
#container { background-color: white;}
ul.resourcelist li a, ul.resourcelist li a:link, ul.resourcelist li a:visited { font-size: 14pt; 
font-color: orange;}

ul.resourcelist li { margin-bottom: 1em}


      div.cloud ul {
      list-style-type: none;
      }
div.cloud ul li {       display: inline; padding-right: 1em;}
div.cloud .cloud1 {font-size: 10pt}
div.cloud .cloud2 { font-size: 12pt}
div.cloud .cloud3 { font-size: 14pt}
div.cloud .cloud4 { font-size: 16pt}
div.cloud .cloud5 { font-size: 18pt}

</style>

  </head>
  <body>
<div id="container">
    <h1>Un nuage de tags</h1>

<?php

require_once('folksoClient.php');
require_once('folksoFabula.php');

$loc = new folksoFabula();

if ($_GET['demouri']) {

$cl = new folksoClient('localhost', 
                       $loc->server_web_path . 'resource.php',
                       'GET');

$cl->set_getfields( array('folksores' => $_GET['demouri'],
                          'folksoclouduri' => '1'));

print '<div class="cloud">';
print $result =  $cl->execute();
print "</div>";
print "<p>HTTP code: " . $cl->query_resultcode()."<br/>" ;
switch ($cl->query_resultcode()) {
case '200':
  print "Ressource trouvée";
  break;
case '204':
  print "Aucun tag n'est associé à cette resource";
  break;
case '404':
  print "Cette ressource n'existe pas dans la base de données";
  break;
case '501':
  print "Erreur base de données - c'est inadmissible";
  break;
}
print "</p>"; 

}
?>
  </div>
  </body>
</html>