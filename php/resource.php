<?php

include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');

/*include('/var/www/dom/fabula/commun3/folksonomie/folksoIndexCache.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoUrl.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoServer.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoResponse.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoQuery.php');
*/

$server = 'localhost'; $user ='root'; 
$pwd = 'hellyes'; $database = 'folksonomie';

$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponse('isHeadTest', 'isHeadDo'));
$srv->addResponseObj(new folksoResponse('getTagsIdsTest', 'getTagsIdsDo'));
$srv->addResponseObj(new folksoResponse('visitPageTest', 'visitPageDo'));
$srv->Respond();



function isHeadTest (folksoQuery $q, folksoUserCreds $cred) {
  if (($q->method() == 'head') &&
      ($q->is_param('uri'))) {
    return true;
  }
  else {
    return false;
  }
}

function isHeadDo (folksoQuery $q, folksoUserCreds $cred) {
  $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
  if ( mysqli_connect_errno()) {
    header('HTTP/1.0 501');
    printf("Connect failed: %s\n", mysqli_connect_error());
  }

  $result = $db->query("select id from resource where id = '" .
                       $db->real_escape_string($q->get_param('uri')) .
                       "'");

  if ($db->errno <> 0) {
    header('HTTP/1.0 501 Database problem');
  }
  elseif ($result->num_rows == 0) {
    header('HTTP/1.0 404 Resource not found');
  }
  else {
    header('HTTP/1.0 200 Resource exists');
  }
}



function getTagsIdsTest ($q) {
  $params = $q->params();
  if (($q->method() == 'get') &&
      (is_string($params['folksoresourceuri']))) {
    return true;
  }
  else {
    return false;
  }
}

function getTagsIdsDo ($q) {
  $params = $q->params();
  
  $db = new mysqli('localhost', 'root', 'LucienLeuwen', 'folksonomie');
  if ( mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
  }

  $result = $db->query("select tagdisplay 
                        from tag 
                        join tagevent on tag.id = tagevent.tag_id
                        join resource on resource.id = tagevent.resource_id
                        where uri_normal = url_whack('". $params['folksoresourceuri']."')");
  if ($db->errno <> 0) {
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
  }
  else {
    while ( $row = $result->fetch_object() ) {
      print $row->tagdisplay . "\n";
    }
  }

}


function visitPageTest (folksoQuery $q, folksoUserCreds $cred) {
  if (($q->method() == 'post') &&
      ($q->is_single_param('folksovisituri'))) {
    return true;
  }
  else {
    return false;
  }
}

function visitPageDo (folksoQuery $q, folksoUserCreds $cred) {
  $ic = new folksoIndexCache('/tmp/cachetest', 500);  

  $page = new folksoUrl($q->get_param('visituri'), 
                        $q->is_single_param('urititle') ? $q->get_param('folksourititle') : '' );


  if (!($ic->data_to_cache( serialize($page)))) {
    trigger_error("Cannot store data in cache", E_USER_ERROR);
  }


  if ($ic->cache_check() ) {
    $pages_to_parse = $ic->retreive_cache();
    //    print "count ". count($pages_to_parse);

    $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
    if ( mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());
    }

    foreach ($pages_to_parse as $raw) {
      $item = unserialize($raw);
      //      print $item->get_url();
      db_store_data($item, $db);
    }
  }


  /*  $db = new mysqli('localhost', 'root', 'LucienLeuwen', 'folksonomie');
  if ( mysqli_connect_errno()) {
    header('HTTP/1.0 501');
    printf("Connect failed: %s\n", mysqli_connect_error());
  }

  $result = $db->query("call url_visit('" .
                       $db->real_escape_string($q->get_param('folksovisituri')) .
                       "')");
  if ($db->errno <> 0) {
    header('HTTP/1.0 501 Database problem');
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
  }
  else {
    header('HTTP/1.0. 200');
    print "Page considered visited\n";
    } */
}

function db_store_data ($url_obj, $db) {
  
  $qq = "call url_visit('". $url_obj->get_url(). "')";
  //  print "<p>$qq</p>";
  $result = $db->query($qq);
  if ($mysqli->errno) {
    die("execution failed : " . $mysqli->errno.": ". $mysqli->error);
  }
  /*  while ($row = $result->fetch_object()) {
    print("<p><b>". $row->uri_normal . "</b>". $row->visited . "</p>");
    }*/
  unset($qq);
  unset($row);
  unset($result);
}


?>