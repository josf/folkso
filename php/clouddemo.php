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

</style>

  </head>
  <body>
<div id="containter">
    <h1>Un nuage de tags</h1>

    <form action="clouddemo.php" method="get">
      <p>Saisir l'URI d'une page du site: 
      <input type="text" name="demouri" maxlength="255" size="80"></input>
      </p>
      <p>
        <input type="submit"/>
      </p>
    </form>

<?php

require_once('/var/www/dom/fabula/commun3/folksonomie/folksoClient.php');

if ($_GET['demouri']) {

$cl = new folksoClient('localhost', 
                       '/commun3/folksonomie/resource.php',
                       'GET');

$cl->set_getfields( array('folksoclouduri' => $_GET['demouri']));


$result =  $cl->execute();
print "<p>HTTP code: " . $cl->query_resultcode() . "</p>";

}
?>
  </div>
  </body>
</html>