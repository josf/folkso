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
if ($dbc instanceof folksoDBconnect) {
  print "YO YO";
}
$ddd = $dbc->db_obj();

print var_dump($ddd);

// GET

/**
 * getTag (Test and Do) : with tag id, return the display version of
 * the tag. Don't know what it should do if the tag does not
 * exist. 404?
 */

function getTagTest (folksoQuery $q, folksoUserCreds $cred) {
  $params = $q->params();

  if (($q->method() == 'get') &&
      (is_string($params['folksotagid']))) {
    return true;
  }
  else {
    return false;
  }
}

function getTagDo (folksoQuery $q, folksoUserCreds $cred, folksoDBconnect $dbc) {
  $params = $q->params();
    $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
  global $dbc;
  print "in the funk:  ";
  print var_dump($dbc);
  // $db = $dbc->db_obj();
  if ( mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
  }

  $result = $db->query("select tagdisplay from tag where id ='". 
                       $db->real_escape_string($q->get_param('folksotagid')) ."'");
  if ($db instanceof mysqli) {
    print "yo";
  }
  if ($db->errno <> 0) {
    printf("Statement failed %d: (%s) %s\n", 
           $db->errno, $db->sqlstate, $db->error);
  }
  else {
    if ($result->num_rows == 0) {
      header('HTTP/1.1 404');
      print "Tag not found: ".$params['folksotagid'];
    }
    while($row = $result->fetch_object()) {
      print "<p>A row:".$row->tagdisplay . "</p>";
    }
  }
}

/**
 * Retrieves a list of the resources associated with the given tag.
 */
function getTagResourcesTest (folksoQuery $q, folksoUserCreds $cred) {

  if (($q->method() == 'get') &&
      ($q->is_single_param('folksotagresources'))) {
    return true;
  }
  else {
    return false;
  }
}

function getTagResourcesDo (folksoQuery $q, folksoUserCreds $cred, folksoDBconnect $dbc) {
    $db = new mysqli('localhost', 'root', 'hellyes', 'folksonomie');
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
      print "<ul>";
      while ($row = $result->fetch_object()) {
        print "<li><a href='" . $row->href . "'>" . $row->display . "</a></li>";
      }
      print "</ul>";
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

function singlePostTagTest (folksoQuery $q, folksoUserCreds $cred) {
  if (($q->method() == 'post') &&
      ($q->is_single_param('folksonewtag'))) {
    return true;
  }
  else {
    return false;
  }
}

function singlePostTagDo (folksoQuery $q, folksoUserCreds $cred, folksoDBconnect $dbc) {
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
