<?php

include('folksoClient.php');


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


$fc = new folksoClient('localhost', '/commun3/folksonomie/resource.php', 'POST');
$fc->set_postfields(array('folksovisituri' => curPageURL(),
                          'folksourititle' => $page_titre ? $page_titre : ''));
//print $fc->build_req();

$fc->execute();
//print $fc->query_resultcode();


?>