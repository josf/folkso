<?php

include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponse('getTagTest', 'getTagDo'));
$srv->addResponseObj(new folksoResponse('getTagResourcesTest', 'getTagResourcesDo'));
$srv->addResponseObj(new folksoResponse('singlePostTagTest', 'singlePostTagDo'));

$srv->Respond();

$server = 'localhost'; $user ='root'; 
$pwd = 'hellyes'; $database = 'folksonomie';

$dbc = new folksoDBconnect($server, $user, $pwd, $database);

// GET

/**
 * getTag (Test and Do) : with tag id, return the display version of
 * the tag. Don't know what it should do if the tag does not
 * exist. 404?
 */

function getTagTest (folksoQuery $q, folksoWsseCreds $cred) {
  $params = $q->params();

  if (($q->method() == 'get') &&
      (is_string($params['folksotagid']))) {
    return true;
  }
  else {
    return false;
  }
}

function getTagDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $params = $q->params();
  //$db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
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
    }
    while($row = $result->fetch_object()) {
      print "<p>A row:".$row->tagdisplay . "</p>";
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
    $querybase = "select 
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
      $dd = new folksoDataDisplay(array('type' => 'xhtml',
                                        'start' => '<ul>',
                                        'end' => '</ul>',
                                        'lineformat' => '<li><a href"XXX">XXX</a></li>',
                                        'argsperline' => 2),
                                  array('type' => 'text',
                                        'start' => '',
                                        'end' => '',
                                        'lineformat' => " XXX XXX\n", 
                                        'argsperline' => 2));
      print "content type" . $q->content_type();
      if ($q->content_type() == 'text/html') {
        $dd->activate_style('xhtml');
      }
      //      elseif ($q->content_type() == 'text/text') {
      else {
        $dd->activate_style('text');
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
  $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
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

?>
