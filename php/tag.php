<?php

include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');



$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponse('getTagTest', 'getTagDo'));
$srv->addResponseObj(new folksoResponse('getTagResourcesTest', 'getTagResourcesDo'));
$srv->addResponseObj(new folksoResponse('singlePostTagTest', 'singlePostTagDo'));
$srv->addResponseObj(new folksoResponse('autoCompleteTagsTest', 'autoCompleteTagsDo'));
$srv->addResponseObj(new folksoResponse('headCheckTagTest', 'headCheckTagDo'));

$srv->Respond();

// HEAD

/**
 * checkTag (Test and Do) : given a string, checks if that tag is
 * already present in the database.
 *
 *
 *
 */

function headCheckTagTest (folksoQuery $q, folksoWsseCreds $cred) {
  if (($q->method() == 'head') &&
      ($q->is_param('tag'))) {
    return true;
  }
  else {
    return false;
  }
}

function headCheckTagDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $db = $dbc->db_obj();
  if ($dbc->dberr){
    header('HTTP/1.1 501');
    print "Database connection error.  ";
    print $dbc->dberr;
    die("Something is wrong.");
  }

  $result = $db->query("select id from tag where tagnorm = normalize_tag('" .
                       $db->real_escape_string($q->get_param('tag')) .
                       "') " . 
                       "limit 1");

  if ($db->errno <> 0) {
    header('HTTP/1.1 501');
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
    die("DB error");
  }
  
  if ($result->num_rows == 0) {
    header('HTTP/1.1 404');
  }
  else {
    header('HTTP/1.1 200');
    $id = 0;
    while ($row = $result->fetch_object()) {
      $id = $row->id;
    }
    header("X-Folkso-Tagid: " . $id);
  }
}


// GET

/**
 * getTag (Test and Do) : with tag id, return the display version of
 * the tag. Don't know what it should do if the tag does not
 * exist. 404?
 */

function getTagTest (folksoQuery $q, folksoWsseCreds $cred) {
  if (($q->method() == 'get') &&
      ($q->is_param('tagid'))) {
    return true;
  }
  else {
    return false;
  }
}

function getTagDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $db = $dbc->db_obj();
  if ($dbc->dberr){
    header('HTTP/1.1 501');
    print "Database connection error.  ";
    print $dbc->dberr;
    die("Something is wrong.");
  }

  $result = $db->query("select tagdisplay from tag where id ='". 
                       $db->real_escape_string($q->get_param('folksotagid')) ."'");
  if ($db->errno <> 0) {
    header('HTTP/1.1 501');
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
  }
  else {
    if ($result->num_rows == 0) {
      header('HTTP/1.1 404');
      print "Tag not found: ". $q->get_param('tagid');
      return;
    }
    else {
      header('HTTP/1.1 200');
      $df = new folksoDisplayFactory();
      $disp = $df->singleElementList();
      $disp->activate_style('xml');
      print $disp->startform();
      while($row = $result->fetch_object()) {
        print $disp->line($row->tagdisplay);
      }
      print $disp->endform();
  }
}
}

/**
 * Retrieves a list of the resources associated with the given tag.
 */
function getTagResourcesTest (folksoQuery $q, folksoWsseCreds $cred) {

  if (($q->method() == 'get') &&
      ($q->is_single_param('folksotagresources'))) {
    return true;
  }
  else {
    return false;
  }
}

function getTagResourcesDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $db = $dbc->db_obj();
    if ( mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());
    }
    $querybase = "select distinct
                 uri_raw as href, uri_normal, title, 
                 case when title is null then uri_normal else title end as display
              from resource
                 join tagevent on resource.id = tagevent.resource_id
                 join tag on tagevent.tag_id = tag.id ";

    if (preg_match( '/^\d+$/', $q->get_param('tagresources'))) {
      $querybase .= 
        "where tag.id = " . 
        $db->real_escape_string($q->get_param('tagresources'));
    }
    else {
      $querybase .= 
        "where tag.tagnorm = normalize_tag('" .
        $db->real_escape_string($q->get_param('tagresources')) . "')";
    }
    $result = $db->query($querybase);
    if ($db->errno <> 0) {
      printf("Statement failed %d: (%s) %s\n", 
             $db->errno, $db->sqlstate, $db->error);
    }
    // We have results
    elseif ($result->num_rows > 0) {
      header('HTTP/1.1 200');
      $df = new folksoDisplayFactory();
      $dd = $df->basicLinkList();

      if ($q->content_type() == 'text/html') {
        $dd->activate_style('xhtml');
      }
      else {
        $dd->activate_style('xhtml');
      }
      print $dd->startform();
      while ($row = $result->fetch_object()) {
        print $dd->line( $row->href, $row->display);
      }
      print $dd->endform();
    }
    else { // No results : is it the tag or the resources' fault?
      $eres = $db->query("select id from tag where tag.id = '" .
                         $db->real_escape_string($q->get_param('tagresources')) . "'".
                         "or tag.tagnorm = normalize_tag('" .
                         $db->real_escape_string($q->get_param('tagresources')) ."')");
      if ($db->errno <> 0) {
        header('HTTP/1.1 501');
        printf("Statement failed %d: (%s) %s\n",
               $db->errno, $db->sqlstate, $db->error);
      }
      // No TAG!
      elseif ($eres->num_rows == 0) {
        header('HTTP/1.1 404');
        print "Tag '" . $q->get_param('tagresources') . "' does not seem to exist";
      }
      else { // No resources
        header('HTTP/1.1 204');
        print "No resources associated with this tag";
      }
    }
}

/**
 * Add a new tag.
 *
 */
function singlePostTagTest (folksoQuery $q, folksoWsseCreds $cred) {
  if (($q->method() == 'post') &&
      ($q->is_single_param('folksonewtag'))) {
    return true;
  }
  else {
    return false;
  }
}

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


function autoCompleteTagsTest (folksoQuery $q, folksoWsseCreds $cred) {
  if (($q->is_param('autotag')) &&
      ($q->method() == 'get')) {
    return true;
  }
  else {
    return false;
  }
}

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
