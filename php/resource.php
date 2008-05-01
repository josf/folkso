<?php

include('/usr/local/www/apache22/lib/jf/folksoIndexCache.php');
include('folksoUrl.php');
include('folksoServer.php');
include('folksoResponse.php');
include('folksoQuery.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'LOCAL'));

$srv->addResponseObj(new folksoResponse('visitPageTest', 'visitPageDo'));
$srv->Respond();

$server = 'localhost'; $user ='root'; 
$pwd = 'hellyes'; $database = 'folksonomie';


function visitPageTest ($q) {
  if (($q->method() == 'post') &&
      ($q->is_single_param('folksovisituri'))) {
    return true;
  }
  else {
    return false;
  }
}

function visitPageDo ($q) {
  $ic = new folksoIndexCache('/tmp/cachetest', 2);  

  $page = new folksoUrl($q->get_param('visituri'), 
                        $q->is_single_param('urititle') ? $q->get_param('urititle') : '' );


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


  $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
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
  }
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