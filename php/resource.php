<?php

  //include('/var/www/dom/fabula/commun3/folksonomie/folksoTags.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');
/*include('/var/www/dom/fabula/commun3/folksonomie/folksoIndexCache.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoUrl.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoServer.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoResponse.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoQuery.php');
*/


$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponse('isHeadTest', 'isHeadDo'));
$srv->addResponseObj(new folksoResponse('getTagsIdsTest', 'getTagsIdsDo'));
$srv->addResponseObj(new folksoResponse('tagResourceTest', 'tagResourceDo'));
$srv->addResponseObj(new folksoResponse('visitPageTest', 'visitPageDo'));
$srv->addResponseObj(new folksoResponse('addResourceTest', 'addResourceDo'));
$srv->Respond();

/**
 * Check to see if a resource is present in the database.
 */
function isHeadTest (folksoQuery $q, folksoWsseCreds $cred) {
  if (($q->method() == 'head') &&
      (($q->is_param('uri')) ||
       ($q->is_param('resourceid')))) {
    return true;
  }
  else {
    return false;
  }
}

function isHeadDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $db = $dbc->db_obj();
  if ( mysqli_connect_errno()) {
    header('HTTP/1.0 501');
    printf("Connect failed: %s\n", mysqli_connect_error());
  }

  if ($q->is_param('resourceid')) {

    $result = $db->query("select id from resource where id = '" .
                         $db->real_escape_string($q->get_param('resourceid')) .
                         "'");
  }
  elseif ($q->is_param('uri')) {
    $result = $db->query("select id
                          from resource
                          where uri_normal = url_whack('" .
                         $db->real_escape_string($q->get_param('uri')) . "')");
  }
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


/**
 * Retrieve the tags associated with a given resource. Accepts uri or
 * id.
 */
function getTagsIdsTest (folksoQuery $q, folksoWsseCreds $cred) {
  if (($q->method() == 'get') &&
      (($q->is_param('resourceuri')) ||
       ($q->is_param('resourceid')))) {
    return true;
  }
  else {
    return false;
  }
}

function getTagsIdsDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $db = $dbc->db_obj();
  if ( mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
  }

  // check to see if resource is in db.
  if ($q->is_param('resourceid')) {
    $pageres = $db->query("select uri_normal, uri_raw
                           from resource
                           where id = " . 
                          $db->real_escape_string($q->get_param('resource_id')));
  }
  elseif ($q->is_param('resourceuri')) {
    $pageres = $db->query("select uri_normal, uri_raw
                         from resource
                         where uri_normal = url_whack('" . $q->get_param('resourceuri') . "')");
  }

  if ($db->errno <> 0) {
    header('HTTP/1.0 501 Database problem');
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
    return;
  }
  elseif ($pageres->num_rows == 0) {
    header('HTTP/1.0 404 Resource not found');
    print "uri was ". $q->get_param('resourceuri');
    return;
  }

  $result = $db->query("select distinct tagdisplay 
                        from tag 
                        join tagevent on tag.id = tagevent.tag_id
                        join resource on resource.id = tagevent.resource_id
                        where uri_normal = url_whack('". $q->get_param('folksoresourceuri') ."')");
  if ($db->errno <> 0) {
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
  }
  elseif ($result->num_rows == 0) {
    header('HTTP/1.1 204 No tags');
    return;
  }
  else {
    header('HTTP/1.1 200');
    $dd = new folksoDataDisplay( array('type' => 'text',
                                 'start' => "\n",
                                 'end' => "\n",
                                 'lineformat' => " XXX\n",
                                 'titleformat' => " XXX \n-----",
                                 'argsperline' => 1),
                           array('type' => 'xhtml',
                                 'start' => '<ul>',
                                 'end' => '</ul>',
                                 'titleformat' => '<h1>XXX</h1>',
                                 'lineformat' => '<li>XXX</li>',
                                 'argsperline' => 1));

    
    if ($q->content_type() == 'text/text') {
      $dd->activate_style('text');
    }
    elseif ($q->content_type() == 'text/html') {
      $dd->activate_style('xhtml');
    }
    else {
      $dd->activate_style('xhtml');
    }


    $row = $pageres->fetch_object();
    print $dd->title($row->uri_normal);
    print $dd->startform();
    while ( $row = $result->fetch_object() ) {
      print $dd->line($row->tagdisplay);
    }
    print $dd->endform();
  }

}



/**
 * VisitPage : add a resource (uri) to the resource index
 */
function visitPageTest (folksoQuery $q, folksoWsseCreds $cred) {
  if (($q->method() == 'post') &&
      ($q->is_single_param('folksovisituri'))) {
    return true;
  }
  else {
    return false;
  }
}

function visitPageDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $ic = new folksoIndexCache('/tmp/cachetest', 500);  

  $page = new folksoUrl($q->get_param('visituri'), 
                        $q->is_single_param('urititle') ? $q->get_param('urititle') : '' );


  if (!($ic->data_to_cache( serialize($page)))) {
    trigger_error("Cannot store data in cache", E_USER_ERROR);
  }


  if ($ic->cache_check() ) {
    $pages_to_parse = $ic->retreive_cache();
    //    print "count ". count($pages_to_parse);

    $db = $dbc->db_obj();
    if ( mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());
    }

    foreach ($pages_to_parse as $raw) {
      $item = unserialize($raw);
      //      print $item->get_url();
      db_store_data($item, $db);
    }
  }
}

function addResourceTest (folksoQuery $q, folksoWsseCreds $cred) {
  if (($q->method() == 'post') &&
      ($q->is_param('newuri'))) {
    return true;
  }
  else {
    return false;
  }
}

function addResourceDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $db = $dbc->db_obj();
  $action = $db->query("call url_visit('" .
                       $db->real_escape_string($q->get_param('newuri')) . 
                       "', '" .
                       $db->real_escape_string($q->get_param('newtitle')) . "')");
      
  if ($db->errno <> 0) {
    header('HTTP/1.1 501 DB error');
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
    die("execution failed : " . $db->errno.": ". $db->error);
  }
  else {
    header('HTTP/1.1 201');
    print "Resource added";
  }
}


function tagResourceTest (folksoQuery $q, folksoWsseCreds $cred) {
  if (($q->method() == 'post') &&
      ($q->is_param('folksoresource')) &&
      ($q->is_param('folksotag'))) {
    return true;
  }
  else {
    return false;
  }
}

function tagResourceDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $db = $dbc->db_obj();
  $db->query("call tag_resource('" .
             $db->real_escape_string($q->get_param('resource')) . "', '" .
             $db->real_escape_string($q->get_param('tag')) . "')");

  if ($db->errno <> 0) {

    if (($db->errno == 1048) &&
        (strpos($db->error, 'resource_id'))) {
      header('HTTP/1.1 404');
      print "Resource ". $q->get_param('resource') . " has not been indexed yet.";
    }
    elseif (($db->errno == 1048) &&
            (strpos($db->error, 'tag_id'))) {
      header('HTTP/1.1 404');
      print "Tag ". $q->get_param('tag') . " does not exist.";
    }
    else {
      header('HTTP/1.1 501');
      print "obscure database problem";
      printf("Statement failed error number %d: (%s) %s\n", 
          $db->errno, $db->sqlstate, $db->error); 
    }
  }
  else {
    header('HTTP/1.1 200');
    print "Resource has been tagged";
  }
}


function db_store_data ($url_obj, $db) {
  
  $qq = "call url_visit('". $url_obj->get_url(). "', '" . $url_obj->get_title() ."')";

  $result = $db->query($qq);
  if ($mysqli->errno) {
    die("execution failed : " . $mysqli->errno.": ". $mysqli->error);
  }
}


?>