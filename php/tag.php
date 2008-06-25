<?php

include('/usr/local/www/apache22/lib/jf/fk/folksoTags.php');

/** 
 * When the tag's name or id is known, the field name "tag"
 * ("folksotag" if we maintain that system) will always be used. It
 * can be a multiple field (that is: "tag001, tag002, tag003...").
 *
 * The "tag" field should be able to accept either a numerical id or a
 * tag name. In this case the tag name is not necessarily normalized.
 *
 */



$srv = new folksoServer(array( 'methods' => 
                               array('POST', 'GET', 'HEAD', 'DELETE'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponse('get', 
                                        array('required' => array('tag')),
                                        'getTagDo'));

$srv->addResponseObj(new folksoResponse('get', 
                                        array('required_single' => 
                                              array('tag', 
                                                    'resources')),
                                        'getTagResourcesDo'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('newtag')),
                                        'singlePostTagDo'));

$srv->addResponseObj(new folksoResponse('get', 
                                        array('required' => array('autotag')),
                                        'autoCompleteTagsDo'));

$srv->addResponseObj(new folksoResponse('get',
                                        array('required' => array('byalpha')),
                                        'byalpha'));

$srv->addResponseObj(new folksoResponse('get',
                                        array('required' => array('fancy'),
                                              'required_single' => array('tag')),
                                        'fancyResource'));

$srv->addResponseObj(new folksoResponse('head',
                                        array('required' => array('tag')),
                                        'headCheckTagDo'));

/**
 * Note that the "tag" field here refers to the resource that will be
 * deleted during the merge.
 */
$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('tag', 'target')),
                                        'tagMerge'));

$srv->addResponseObj(new folksoResponse('delete',
                                        array('required' => array('delete'),
                                              'required_single' => array('tag')),
                                        'deleteTag'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('delete'),
                                              'required_single' => array('tag')),
                                        'deleteTag'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('tag', 
                                                                         'newname')),
                                        'renameTag'));

$srv->Respond();

/** 
 *  HEAD
 */

/**
 * checkTag (Test and Do) : given a string, checks if that tag is
 * already present in the database.
 * 
 * If the tag exists, returns 200 and sets an 'X-Folkso-Tagid' header
 * with the numeric id.
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
 * getTag : with tag id (or string, but that would be pointless),
 * return the display version of the tag.
 *
 */
function getTagDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);

  if ($i->db_error()) {
    header('HTTP/1.1 501 Database problem');
    die( $i->error_info());
  }

  $query = 'SELECT tagdisplay FROM tag WHERE ';

  if (is_numeric($q->tag)) {
    $query .= 'id = ' . $q->get_param('tag');
  }
  else {
    $query .= "tagnorm = normalize_tag('" . $q->tag . "')";
  }
  $i->query($query);

  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database error');
    die( $i->error_info());
    break;
  case 'NOROWS':
    header('HTTP/1.1 404 Tag not found');
    die('The tag ' . $q->tag . ' was not found');
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
    
  $querybase = "SELECT
                 r.uri_raw AS href, r.id AS id, r.title AS title, 
                 CASE 
                   WHEN title IS NULL THEN uri_normal 
                   ELSE title 
                 END AS display
              FROM resource r
                 JOIN tagevent ON r.id = tagevent.resource_id
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

  $querybase .= " ORDER BY r.visited DESC ";  

  //pagination
  if  ((!$q->is_param('page')) ||
       ($q->get_param('page') == 1))  {
      $querybase .= "  LIMIT 20 ";
  }
  else {
      $querybase .= "  LIMIT ". 
        $q->get_param('page') * 20 . ",20 ";
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
    print "No resources are currently associated with " . 
      $q->get_param('resources');
    return;
    break;
  case 'OK':
    header('HTTP/1.1 200');
    break; // now we do the rest, assuming all is well.
  }

  $df = new folksoDisplayFactory();
  $dd = $df->ResourceList();
  
  if ($q->content_type() == 'text/html') {
    $dd->activate_style('xhtml');
  }
  else {
    $dd->activate_style('xml');
  }
  print $dd->startform();
  while ($row = $i->result->fetch_object()) {
    print $dd->line( $row->id, 
                     $row->href, 
                     array('xml' => 
                           'Placeholder',
                           'default' => $row->display));
  }
  print $dd->endform();
}
/*                           html_entity_decode(strip_tags($row->display), 
                                              ENT_NOQUOTES, 
                                              'UTF-8'),*/
/**
 * Add a new tag.
 *
 * POST, newtag
 *
 */
function singlePostTagDo (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $db = $dbc->db_obj();
  $db->set_charset('UTF8');
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


function fancyResource (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ( $i->db_error() ) {
    header('HTTP/1.1 501 Database problem');
    die( $i->error_info());
  }

$querystart = 
  'select 
  r.title as title, 
  r.id,
  r.uri_raw as href,
  CASE 
    WHEN title IS NULL THEN uri_normal 
    ELSE title
  END AS display,
  (select group_concat(distinct tagdisplay separator \' - \')
               from tag t2
               join tagevent te2 on t2.id = te2.tag_id
               join resource r2 on r2.id = te2.resource_id
               where r2.id = r.id
               ) as tags
  from resource r
  join tagevent te on r.id = te.resource_id
  join tag t on te.tag_id = t.id';

  $queryend = " LIMIT 20";
  $querywhere = '';
  if (is_numeric($q->get_param('fancy'))) {
    $querywhere = 'where t.id = ' . $q->get_param('fancy') . ' ';
  }
  else {
    $querywhere = "where t.tagnorm = normalize_tag('" . 
      $i->dbescape($q->get_param('fancy')) . "') ";
  }

  $i->query($querystart . ' '  . $querywhere . ' ' . $queryend);
  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database query error');
    die($i->error_info());
    break;
  case 'NOROWS':
    header('HTTP/1.1 204 No resources associated with  tag');
    print "No resources are currently associated with " . 
      $q->get_param('fancy');
    return;
    break;
  case 'OK':
    header('HTTP/1.1 200');
    break;
  default:
      header('HTTP/1.1 501 Inexplicable error');
    die('This does not make sense');
  }
  // so we are 'OK'

  $df = new folksoDisplayFactory();
  $dd = $df->FancyResourceList();
  
  $dd->activate_style('xml');
  print $dd->startform();
  while ($row = $i->result->fetch_object()) {
    print $dd->line( $row->id,
                     $row->href,
                     html_entity_decode(strip_tags($row->display), ENT_NOQUOTES, 'UTF-8'),
                     $row->tags);
  }
  print $dd->endform();
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

/**
 * POST, target source
 * 
 * Retags all the tagevents tagged by "source" as "target", then
 * deletes "source".
 *
 * Returns 204 on success, 404 on missing tags.
 * 
 */

function tagMerge (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ( $i->db_error() ) {
    header('HTTP/1.1 501 Database connection problem');
    die($i->error_info());
  }

  // build query
  $source_part = '';
  if (is_numeric($q->get_param('source'))){
    $source_part = $q->get_param('source'). ", ''";
  }
  else {
    $source_part = "'', '". $i->dbquote($q->get_param('source')) . "'";
  }

  $target_part = '';
  if (is_numeric($q->get_param('target'))) {
    $target_part = $q->get_param('target') . ", ''";
  }
  else {
    $target_part = "'', '" . $i->dbquote($q->get_param('target')) . "'";
  }
  
  // execute
  $i->query("call tagmerge($source_part, $target_part)");
  

  if ($i->result_status == 'DBERR') {
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
  }
  else {
    switch ($i->first_val('status')) {
    case 'OK':
      header('HTTP/1.1 204 Merge successful');
      print $i->first_val('status');
      return;
      break;
    case 'NOTARGET':
      header('HTTP/1.1 404 Invalid target tag');
      print $i->first_val('status');
      die($q->get_param('target') . 
          " is not present in the database. Merge not accomplished.");
      break;
    case 'NOSOURCE':
      header('HTTP/1.1 404 Invalid source tag');
      die($q->get_param('source') . 
          " is not present in the database. Merge not accomplished.");
      break;
    }
    header('HTTP/1.1 501 Strange server error');
    print "fv: ".$i->first_val('status');
    die('This should not have happened.');
  }
}

/**
 * DELETE, folksodelete (can be tag id or tag name)
 *
 * Obviously, this badly needs authentication!
 *
 * 404 on tag not found. 204 on success.
 */
function deleteTag  (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);

  if ( $i->db_error() ) {
    header('HTTP/1.1 501 Database problem');
    print $i->error_info() . "\n";
    return;
  }

  /* Check to see if tag is there. This might not be necessary: if the
   * tag does not exist, we just continue */
  if (!$i->tagp($q->get_param('delete'))) {
    if ($i->db_error()) {
      header('HTTP/1.1 501 Database problem');
      die($i->error_info());
    }
    else {
      header('HTTP/1.1 404 Could not delete - tag not found');
      print $q->get_param('resource') . " does not exist in the database";
      return;
    }
  }

  // delete 1 : remove tagevents
  // delete 2 : remove the tag itself
  $delete1 = '';
  $delete2 = "delete from tag  ";

  if ($q->is_number('delete')) {
    $delete1 .= ' delete from tagevent where tag_id='. $q->get_param('delete');
    $delete2 .= ' where id='. $q->get_param('delete');
  }
  else {
    $delete1 .= " DELETE tagevent FROM tagevent JOIN ON tag WHERE tagevent.tag_id = tag.id
                  WHERE tag.tagnorm = normalize_tag('" . $q->get_param('delete') . "')";    
    $delete2 .= " WHERE tagnorm = normalize_tag('" . $q->get_param('delete') . "')";
  }

  $i->query($delete1); // delete tagevent
  if ($i->result_status ==  'DBERR') {
    header('HTTP/1.1 501 Database error');
    die("Initial tagevent delete failed " . $i->error_info());
  }

  $i->query($delete2); // delete tag
  if ($i->result_status ==  'DBERR') {
    header('HTTP/1.1 501 Database error');
    die("Secondary delete failed ". $i->error_info());
  }

  header('HTTP/1.1 204 Tag deleted');
}

/**
 * byalpha
 *
 * GET, folksoalpha
 * 
 * List of tags by letter of the alphabet. Up to three letters are allowed.
 *
 */
function byalpha (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.1 501 Database connection problem');
    die($i->error_info());
  }
  
  $alpha = substr($q->get_param('byalpha'), 0, 3);


  $query = "select id, tagdisplay, tagnorm, popularity
            from tag
            where tagnorm like '" . $i->dbescape($alpha) . "%'";

  $i->query($query);
  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
    break;
  case 'NOROWS':
    header('HTTP/1.1 204 No matching tags');
    return;
    break;
  case 'OK':
    header('HTTP/1.1 200 Ok');
    break;
  }

  // assuming everything is ok (200)
  $df = new folksoDisplayFactory();
  $dd = $df->TagList();
  $dd->activate_style('xml');
  print $dd->startform();
  while ($row = $i->result->fetch_object()) {
    print $dd->line($row->id, 
                    $row->tagnorm,
                    $row->tagdisplay,
                    $row->popularity) . "\n";
  }
  print $dd->endform();
}

/**
 * rename tag
 *
 * rename, newname
 * 
 */
function renameTag (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.1 501 Database connection problem');
    die($i->error_info());
  }

  if (!$i->tagp($q->get_param('rename'))) {
    header('HTTP/1.1 404 Tag not found');
    die('Nothing to rename. No such tag: ' . $q->get_param('rename'));
  }

  $query = "update tag
            set tagdisplay = '" . 
    $i->dbescape($q->get_param('newname')) . "', " .
    "tagnorm = normalize_tag('" . $i->dbescape($q->get_param('newname')) . "') ".
    "where ";

  if (is_numeric($q->get_param('rename'))) {
    $query .= " id = " . $q->get_param('rename');
  }
  else {
    $query .= " tagnorm = normalize_tag('" . 
      $i->dbescape($q->get_param('rename')) . "')";
  }
  $i->query($query);
  if ($i->result_status == 'DBERR') {
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
  }
  else {
    header('HTTP/1.1 204 Tag renamed');
    return;
  }
}


?>
