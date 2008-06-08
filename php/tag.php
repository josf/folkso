<?php

include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');



$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponse('get', 
                                        array('required' => array('tagid')),
                                        'getTagDo'));

$srv->addResponseObj(new folksoResponse('get', 
                                        array('required_single' => array('resources')),
                                        'getTagResourcesDo'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('newtag')),
                                        'singlePostTagDo'));

$srv->addResponseObj(new folksoResponse('get', 
                                        array('required' => array('autotag')),
                                        'autoCompleteTagsDo'))
;
$srv->addResponseObj(new folksoResponse('head',
                                        array('required' => array('tag')),
                                        'headCheckTagDo'));

$srv->Respond();

/** 
 *  HEAD
 */

/**
 * checkTag (Test and Do) : given a string, checks if that tag is
 * already present in the database.
 *
 * HEAD, tag
 *
 */
function headCheckTagDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
  }

  $i->query("select id from tag where tagnorm = normalize_tag('" .
            $i->dbquote($q->get_param('tag')) .
            "') " . 
            " limit 1");
  
  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
    break;
  case 'NOROWS':
    header('HTTP/1.1 404 Tag does not exist');
    die('The tag '. $q->get_param('tag') . ' is not present in our database.');
    break;
  case 'OK':
    header('HTTP/1.1 200 Tag exists');
    $id = 0;
    while ($row = $i->result->fetch_object()) {
      $id = $row->id;
    }
    header("X-Folkso-Tagid: " . $id);
  }
}

/** 
 *  GET
 */

/**
 * getTag : with tag id, return the display version of
 * the tag. 
 *
 */
function getTagDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);

  if ($i->db_error()) {
    header('HTTP/1.1 501 Database problem');
    die( $i->error_info());
  }

  $i->query("select tagdisplay from tag where id ='". 
            $i->dbquote($q->get_param('folksotagid')) . "'");

  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database error');
    die( $i->error_info());
    break;
  case 'NOROWS':
    header('HTTP/1.1 404 Tag not found');
    die('The tag ' . $q->get_param('tagid') . ' was not found');
    break;
  case 'OK':
    header('HTTP/1.1 200');
    break;
  }

  $df = new folksoDisplayFactory();
  $disp = $df->singleElementList();
  $disp->activate_style('xml');
  print $disp->startform();
  while($row = $i->result->fetch_object()) {
    print $disp->line($row->tagdisplay);
  }
  print $disp->endform();
}

/**
 * Retrieves a list of the resources associated with the given tag.
 *
 * Parameters: GET, resources (single param)
 *
 * We might have to think about adding a way to announce whether there
 * is a "next page" or not.
 * 
 */
function getTagResourcesDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);

  if ( $i->db_error() ) {
    header('HTTP/1.1 501 Database problem');
    print $i->error_info() . "\n";
    return;
  }

  // check to see if tag exists
  if (!$i->tagp($q->get_param('resources'))) {
    if ($i->db_error()) {
      header('HTTP/1.0 501 Database problem');
      print $i->error_info() . "\n";
      return;
    }
    else {
      header('HTTP/1.1 404 Tag not found');
      print $q->get_param('resource') . " does not exist in the database";
      return;
    }
  }
    
  $querybase = "SELECT DISTINCT
                 uri_raw AS href, uri_normal, title, 
                 CASE 
                   WHEN title IS NULL THEN uri_normal 
                   ELSE title 
                 END AS display
              FROM resource
                 JOIN tagevent ON resource.id = tagevent.resource_id
                 JOIN tag ON tagevent.tag_id = tag.id ";

  // tag by ID
  if (preg_match( '/^\d+$/', $q->get_param('resources'))) {
    $querybase .= 
      "WHERE tag.id = " . 
      $i->dbquote($q->get_param('resources'));
  } //tag by string
  else {
    $querybase .= 
      "WHERE tag.tagnorm = normalize_tag('" .
      $i->dbquote($q->get_param('resources')) . "')";
  }

  //pagination
  if  ((!$q->is_param('page')) ||
       ($q->get_param('page') == 1))  {
      $querybase .= "  LIMIT 20";
  }
  else {
      $querybase .= "  LIMIT ". $q->get_param('page') * 20 . ",20";
  }
  
  $i->query($querybase);
  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database error');
    print $i->error_info() . "\n";
    return;
    break;
  case 'NOROWS':
    header('HTTP/1.1 204 No resources associated with  tag');
    print "No resources are currently associated with " . $q->get_param('resources');
    return;
    break;
  case 'OK':
    header('HTTP/1.1 200');
    break; // now we do the rest, assuming all is well.
  }

  $df = new folksoDisplayFactory();
  $dd = $df->basicLinkList();
  
  if ($q->content_type() == 'text/html') {
    $dd->activate_style('xhtml');
  }
  else {
    $dd->activate_style('xhtml');
  }
  print $dd->startform();
  while ($row = $i->result->fetch_object()) {
    print $dd->line( $row->href, $row->display);
  }
  print $dd->endform();
}

/**
 * Add a new tag.
 *
 * POST, newtag
 *
 */
function singlePostTagDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $db = $dbc->db_obj();
  if ( mysqli_connect_errno()) {
    header('HTTP/1.1 501');
    printf("Connect failed: %s\n", mysqli_connect_error());
    die("Database problem. Something is wrong.");
  }
  $result = $db->query("call new_tag('" . 
                       $db->real_escape_string($q->get_param('folksonewtag')) . "')");
  if ($db->errno <> 0) {
    header('HTTP/1.1 501');
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
  }
  else {
    header('HTTP/1.1 201'); // should add a "location" header
    while($row = $result->fetch_object()) {
      print "Tag created (or already existed), id is ".$row->id  ;
    }
  }
}

/** 
 * GET, autotag
 */
function autoCompleteTagsDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  
  $req = substr($q->get_param('autotag'), 0, 3);
  
  $db = $dbc->db_obj();
  $result = $db->query("select tagdisplay
                        from tag
                        where tagdisplay like '" .
                       $db->real_escape_string($req) .
                       "%'");

  if ($db->errno <> 0) {
    header('HTTP/1.1 501');
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
    return;
  }
  elseif ($result->num_rows > 0) {
    header('HTTP/1.1 200');

    $df = new folksoDisplayFactory();
    $dd = $df->singleElementList();
    $dd->activate_style('xhtml');

    print $dd->startform();
    while($row = $result->fetch_object()) {
      print $dd->line($row->tagdisplay) . "\n";
    }
    print $dd->endform();
    return;
  }
  else {
    header('HTTP/1.1 204 No rows');
    return;
  }
}

?>
