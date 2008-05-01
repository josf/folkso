<?php

include('/usr/local/www/apache22/lib/jf/folksoIndexCache.php');
include('folksoUrl.php');
include('folksoServer.php');
include('folksoResponse.php');
include('folksoQuery.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponse('isHeadTest', 'isHeadDo'));
$srv->addResponseObj(new folksoResponse('getTagTest', 'getTagDo'));
$srv->addResponseObj(new folksoResponse('singlePostTagTest', 'singlePostTagDo'));
$srv->addResponseObj(new folksoResponse('elster', 'elsterDo'));
$srv->Respond();

$server = 'localhost'; $user ='root'; 
$pwd = 'hellyes';


function isHeadTest ($q) {
  if (($q->method() == 'head') &&
      ($q->is_param('tagid'))) {
    return true;
  }
  else {
    return false;
  }
}

function isHeadDo ($q) {
    $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
  if ( mysqli_connect_errno()) {
    header('HTTP/1.0 501');
    return;
  }

  $result = $db->query("select id from tag where id ='" . 
                       $db->real_escape_string($q->get_param('tagid')) .
                       "'");
  if ($db->errno <> 0) {
      header('HTTP/1.0 501');
  }
  else {
    if ($result->num_rows == 0) {
      header('HTTP/1.0 404');
    }
    else {
      header('HTTP/1.0 200');
    }
  }
}

/**
 * getTag (Test and Do) : with tag id, return the display version of
 * the tag. Don't know what it should do if the tag does not
 * exist. 404?
 */

function getTagTest ($q) {
  $params = $q->params();

  if (($q->method() == 'get') &&
      (is_string($params['folksotagid']))) {
    return true;
  }
  else {
    return false;
  }
}

function getTagDo ($q) {
  $params = $q->params();
  $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
  if ( mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
  }

  $result = $db->query("select tagdisplay from tag where id ='" . 
                       $db->real_escape_string($params['folksotagid']) .
                       "'");
  if ($db->errno <> 0) {
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
  }
  else {
    if ($result->num_rows == 0) {
      header('HTTP/1.0 404');
      print "Tag not found: " .
        $db->real_escape_string($params['folksotagid']);
    }
    while($row = $result->fetch_object()) {
      print "<p>A row:".$row->tagdisplay . "</p>";
    }
  }

}


function singlePostTagTest ($q) {
  if (($q->method() == 'post') &&
      ($q->is_param('folksonewtag'))) {
    return true;
  }
  else {
    return false;
  }
}

function singlePostTagDo ($q) {
  //  header('Content-Type: text/html');
  $params = $q->params();

  $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
  if ( mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
  }
  $result = $db->query("call new_tag('" . 
                       $db->real_escape_string($params['folksonewtag']) . 
                       "')");
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
  header('HTTP/1.0. 400');
  print "Incorrect request.";
}

?>
