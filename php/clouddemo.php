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
ul.cloudlist li {       display: inline; padding-right: 1em;}
.cloudclass1 {font-size: 10pt}
.cloudclass2 { font-size: 12pt}
.cloudclass3 { font-size: 14pt}
.cloudclass4 { font-size: 16pt}
.cloudclass5 { font-size: 18pt}

</style>

  </head>
  <body>
<div id="container">
    <h1>Un nuage de tags</h1>

<?php

require_once('folksoPage.php');

if ($_GET['demouri']) {

  $p = new folksoPage();
  $pg = $p->format_cloud(0, $_GET['demouri']);
  if ($pg->is_valid()) {
    print $pg->html;
  }
  else {
    print $pg->status;
  }
}
?>
  </div>
  </body>
</html>