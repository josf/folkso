<?php
require_once('/var/www/dom/fabula/commun3/folksonomie/folksoClient.php');

$cl = new folksoClient('localhost', 
                       '/commun3/folksonomie/resource.php',
                       'GET');

$cl->set_getfields( array('folksoclouduri' => 'http://www.fabula.org'));


$result =  $cl->execute();
print $cl->query_resultcode();



/*$fc->set_postfields(array('folksovisituri' => curPageURL(),
                          'folksourititle' => $page_titre ? $page_titre : ''));*/
#print $fc->build_req();


#print $fc->query_resultcode();


?>