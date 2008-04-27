<?php

include('/usr/local/www/apache22/lib/jf/folksoIndexCache.php');
include('folksoUrl.php');
include('folksoServer.php');
include('folksoResponse.php');
include('folksoQuery.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponse('visitPageTest', 'visitPageDo'));
$srv->Respond();

$server = 'localhost'; $user ='root'; 
$pwd = 'hellyes'; $database = 'folksonomie';


function visitPageTest ($q) {
  $params = $q->params();

  if (($q->method() == 'post') &&
      ($q->is_single_param('folksovisituri'))) {
    return true;
  }
  else {
    return false;
  }
}

function visitPageDo ($q) {
  $params = $q->params();

  $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
  if ( mysqli_connect_errno()) {
    header('HTTP/1.0 501');
    printf("Connect failed: %s\n", mysqli_connect_error());
  }

  $result = $db->query("call url_visit('" .
                       $db->real_escape_string($params['folksovisituri']) .
                       "')");
  if ($db->errno <> 0) {
    header('HTTP/1.0 501');
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
  }
  else {
    header('HTTP/1.0. 200');
    print "Page considered visited\n";
  }
}


?>