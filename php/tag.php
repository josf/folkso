<?php

include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');
$srv = new folksoServer(array( 'methods' => array('POST', 'GET'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponse('getTagTest', 'getTagDo'));
$srv->addResponseObj(new folksoResponse('singlePostTagTest', 'singlePostTagDo'));

$srv->Respond();

$server = 'localhost'; $user ='root'; 
$pwd = 'hellyes';



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

  $result = $db->query("select tagdisplay from tag where id ='". $params['folksotagid']."'");
  if ($db->errno <> 0) {
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
  }
  else {
    if ($result->num_rows == 0) {
      header('HTTP/1.0 404');
      print "Tag not found: ".$params['folksotagid'];
    }
    while($row = $result->fetch_object()) {
      print "<p>A row:".$row->tagdisplay . "</p>";
    }
  }

}


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
  $params = $q->params();

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

?>
