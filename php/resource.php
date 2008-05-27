<?php


  /**
   * Web interface providing information about tags.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */




  //include('/var/www/dom/fabula/commun3/folksonomie/folksoTags.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');


$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponse('head', 
                                        array('oneof' => array('uri', 'id')),
                                        'isHeadDo'));
$srv->addResponseObj(new folksoResponse('get',
                                        array('oneof' => array('uri', 'id')),
                                        'getTagsIdsDo'));
$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('resource', 'tag')),
                                        'tagResourceDo'));
$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('visituri')),
                                        'visitPageDo'));
$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('newuri')),
                                        'addResourceDo'));
$srv->Respond();

/**
 * Check to see if a resource is present in the database.
 */
function isHeadTest (folksoQuery $q, folksoWsseCreds $cred) {
  if (($q->method() == 'head') &&
      (($q->is_param('uri')) ||
       ($q->is_param('id')))) {
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

  if ($q->is_param('id')) {

    $result = $db->query("select id from resource where id = '" .
                         $db->real_escape_string($q->get_param('id')) .
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
  $i = new folksoDBinteract($dbc);

  if ($i->db_error()){
    header('HTTP/1.0 501 Database problem');
    print $i->error_info() . "\n";
    return;
  }
  
  // check to see if resource is in db.
  $ress = $q->is_param('id') ? $q->get_param('id') : $q->get_param('uri');
  if  (!$i->resourcep($ress))  {
    if ($i->db_error()) {
      header('HTTP/1.0 501 Database problem');
      print $i->error_info() . "\n";
      return;
    }
    else {
      header('HTTP/1.0 404 Resource not found');      
      print "Resource not present in database";
      return;
    }
  }

  $select = "select distinct tagdisplay 
                        from tag 
                        join tagevent on tag.id = tagevent.tag_id
                        join resource on resource.id = tagevent.resource_id ";
  if ($q->is_param('id')) {
    $select .= "where resource.id = " . $i->dbquote($q->get_param('id'));
  }
  else {
    $select .= "where uri_normal = url_whack('". $i->dbquote($q->get_param('uri')) ."')";
  }
   
  $i->query($select);

  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database error');
    print $i->error_info() . "\n";
    return;
    break;
  case 'NOROWS':
    header('HTTP/1.1 204 No tags associated with resource');
    return;
    break;
  case 'OK':
    header('HTTP/1.1 200');
    break;
  }
  // here everything should be ok

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

    $row = $i->result->fetch_object();
    print $dd->title($row->uri_normal);
    print $dd->startform();
    while ( $row = $i->result->fetch_object() ) {
      print $dd->line($row->tagdisplay);
    }
    print $dd->endform();
}




/**
 * Tag cloud local.
 */

function tagCloudLocalPop (folksoQuery $q, folksoWsseCreds $cred, folksoDBconect $dbc) {
  $db = $dbc->db_obj();
  if ( mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
  }

  // check to see if resource is in db.
  if ($q->is_param('id')) {
    $pageres = $db->query("select uri_normal, uri_raw
                           from resource
                           where id = " . 
                          $db->real_escape_string($q->get_param('id')));
  }
  elseif ($q->is_param('uri')) {
    $pageres = $db->query("select uri_normal, uri_raw
                         from resource
                         where uri_normal = url_whack('" . $q->get_param('uri') . "')");
  }

  if ($db->errno <> 0) {
    header('HTTP/1.0 501 Database problem');
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
    return;
  }
  elseif ($pageres->num_rows == 0) {
    header('HTTP/1.0 404 Resource not indexed.');
    print "uri was ". $q->get_param('resourceuri');
    return;
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