<?php

require_once('folksoClient.php');


$cl = new folksoClient('localhost', 
                       '/commun3/folksonomie/resource.php',
                       'GET');

$cl->set_getfields( array('folksouri' => curPageURL()));


$result =  $cl->execute();
print $cl->query_resultcode();




function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

?>