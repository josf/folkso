<?php

<?php
include('/usr/local/www/apache22/lib/jf/folksoIndexCache.php');
include('folksoUrl.php');
include('folksoServer.php');
include('folksoResponse.php');
include('folksoQuery.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponse('ourTestFunc', 'actionVisitURI'));
$srv->Respond();




?>
