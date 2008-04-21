<?php

include('/usr/local/www/apache22/lib/jf/folksoIndexCache.php');
include('folksoUrl.php');
include('folksoServer.php');
include('folksoResponse.php');
include('folksoQuery.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponse('singlePostTagTest', 'singlePostTagDo'));
$srv->addResponseObj(new folksoResponse('elster', 'elsterDo'));
$srv->Respond($q);

function singlePostTagTest ($q) {
  $params = $q->params();

  if (($q->method() == 'post') &&
      (is_string($params['folksonewtag']))) {
    return true;
  }
  else {
    return false;
  }
}

function singlePostTagDo ($q) {
  //  header('Content-Type: text/html');
  $server = 'localhost'; $user ='root'; 
  $pwd = 'hellyes';

  $params = $q->params();

  print "We are doing";
  $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
  if ( mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
  }
  $result = $db->query("call new_tag('" . $params['folksonewtag'] . "')");
  if ($db->errno <> 0) {
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
  }
  else {
    while($row = $result->fetch_object()) {
      print "<p>A row:".$row->id . "</p>";
    }
  }
}

function elster ($q) {
  return true;
}

function elsterDo ($q) {
  //  header('Content-Type: text/html');
  print "<h2>Hey!</h2>";
}

?>
