<?php


  /**
   * Web service for accessing, creating and modifying tag information
   * about resources (URLs).
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */


require_once('folksoTags.php');
require_once('folksoIndexCache.php');
require_once('folksoUrl.php');
require_once('folksoResQuery.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponse('head', 
                                        array('required' => array('res')),
                                        'isHead'));

$srv->addResponseObj(new folksoResponse('get',
                                        array('required' => array('clouduri', 'res')),
                                        'tagCloudLocalPop'));

$srv->addResponseObj(new folksoResponse('get',
                                        array('required' => array('res'),
                                              'exclude' => 
                                              array('clouduri', 'visit', 'note', 'ean13list')),
                                        'getTagsIds'));


$srv->addResponseObj(new folksoResponse('get',
                                        array('required' => array('res', 'ean13list'),
                                              'exclude' => array('delete', 'clouduri', 'note')),
                                        'resEans'));
                                              
$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('res', 'tag'),
                                              'required' => array('delete'),
                                              'exclude' => array('meta', 'newresource', 'ean13')),
                                        'unTag'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('res', 'tag'),
                                              'exclude' => array('delete', 'newresource')),
                                        'tagResource'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('res'),
                                              'required' => array('visit'),
                                              'exclude' => array('tag', 'note')),
                                        'visitPage'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('res', 'newresource'),
                                              'exclude' => array('note', 'delete')),
                                        'addResource'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('res', 'ean13'),
                                              'exclude' => array('newean13', 'oldean13', 'note', 'meta', 'tag', 'delete')),
                                        'assocEan13'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('res', 'newean13', 'oldean13'),
                                              'exclude' => array('ean13', 'tag', 'delete')),
                                        'modifyEan13'));
$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('delete', 'res', 'ean13'),
                                              'exclude' => array('tag', 'note')),
                                        'deleteEan13'));


$srv->addResponseObj(new folksoResponse('delete',
                                        array('required' => array('res', 'tag')),
                                        'unTag'));

$srv->addResponseObj(new folksoResponse('delete',
                                        array('required_single' => array('res'),
                                              'exclude' => array('tag')),
                                        'rmRes'));
$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('res', 'delete'),
                                              'exclude' => array('tag')),
                                        'rmRes'));
$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('res', 'note'),
                                              'exclude' => array('tag', 'delete')),
                                        'addNote'));

$srv->addResponseObj(new folksoResponse('get',
                                       array('required_single' => array('res', 'note'),
                                             'exclude' => array('tag', 'delete')),
                                       'getNotes'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('note', 'delete'),
                                              'exclude' => array('res', 'tag')),
                                        'rmNote'));

$srv->Respond();

/**
 * Check to see if a resource is present in the database.
 * 
 * Web parameters: HEAD + folksouri or folksoid
 */
function isHead (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  $query = '';
  if (is_numeric($q->res)) {
    $query = 
      "SELECT id FROM resource WHERE id = " .
      $i->dbescape($q->res);
  }
  else {
    $query = "SELECT id
           FROM resource
           WHERE uri_normal = url_whack('" .
      $i->dbescape($q->res) . "')";
  }

  $i->query($query);

  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.0 501 Database problem');
    die($i->error_info());
    break;
  case 'NOROWS':
    header('HTTP/1.0 404 Resource not found');
    die('Resource '. $q->res . ' not present in database');
    break;
  case 'OK':
    header('HTTP/1.0 200 Resource exists');
    break;
  }
  $i->done();
}


/**
 * Retrieve the tags associated with a given resource. Accepts uri or
 * id. Also includes EAN13 information if available, tagged as 'EAN13'. 
 * 
 * Web parameters : GET + folksores 
 * Optional: metas only
 * Optional: limit
 */
function getTagsIds (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);

  if ($i->db_error()){
    header('HTTP/1.0 501 Database connection problem');
    die($i->error_info());
  }

  // check to see if resource is in db.
  if  (!$i->resourcep($q->res))  {
    if ($i->db_error()) {
      $i->done();
      header('HTTP/1.0 501 Database problem');
      die($i->error_info());
    }
    else {
      header('HTTP/1.0 404 Resource not found');      
      print "Resource not present in database";
      $i->done();
      return;
    }
  }
  
  $limit = 0;
  if ($q->is_param('limit') &&
      is_numeric($q->get_param('limit'))) {
    $limit = $q->get_param('limit');
  }
  $metaonly = false;
  if ($q->is_param('metaonly')) {
    $metaonly = true;
  }

  $rq = new folksoResQuery();  
  $select = $rq->getTags($i->dbescape($q->res), 
                         $limit, 
                         $metaonly);
  $i->query($select);

  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database error');
    die( $i->error_info());
    break;
  case 'NOROWS':
    header('HTTP/1.1 204 No tags associated with resource');
    return;
    break;
  case 'OK':
    header('HTTP/1.1 200');
    break;
  }
  // here everything should be ok (200)
  $df = new folksoDisplayFactory();
  $dd = $df->singleElementList();
  $xf = $df->Taglist();

  switch ($q->content_type()) {
  case 'text':
    header('Content-Type: text/text');
    $dd->activate_style('text');
    break;
  case 'html':
    $dd->activate_style('xhtml');
    header('Content-Type: text/xhtml');
    break;
  case 'xml':
    $xf->activate_style('xml');
    header('Content-Type: text/xml');
    print $xf->startform();
    while ($row = $i->result->fetch_object()) {
      print $xf->line($row->id,
                      $row->tagnorm,
                      $row->tagdisplay,
                      $row->popularity,
                      $row->meta);
    }
    print $xf->endform();
    return;
    break;
  default:
      $dd->activate_style('xhtml');
      break;
  }

  //    $row = $i->result->fetch_object(); //???
    print $dd->title($row->uri_normal);
    print $dd->startform();
    while ( $row = $i->result->fetch_object() ) {
      print $dd->line($row->tagdisplay);
    }
    print $dd->endform();
}


/**
 * Tag cloud.
 *
 * Parameters: GET, folksoclouduri, res.
 *
 * Optional: folksolimit (limit number of tags returned).
 * Optional: folksobypop (pure popularity based cloud).
 * Optional: folksobydate (date based cloud).
 */
function tagCloudLocalPop (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);

  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection problem');
    die( $i->error_info());
  }

  // check to see if resource is in db.
  if  (!$i->resourcep($q->res ))  {
    if ($i->db_error()) {
      header('HTTP/1.0 501 Database problem');
      die( $i->error_info());
    }
    else {
      header('HTTP/1.0 404 Resource not found');      
      print "Resource not present in database";
      return;
    }
  }

  // using the "limit" parameter
  $taglimit = 0;
  if ($q->is_param('limit') &&
      (is_numeric($q->get_param('limit')))) {
    $taglimit = $q->get_param('limit');
  }

  $rq = new folksoResQuery();
  if ($q->is_param('bypop')) {
    $sql = $rq->cloud_by_popularity($i->dbescape($q->res), 
                                    $taglimit);
  }
  elseif ($q->is_param('bydate')) {
    $sql = $rq->cloud_by_date($i->dbescape($q->res),
                              $taglimit);
  }
  else {
    $sql = "CALL cloudy(";

    if (is_numeric($q->res)) {
      $sql .= $q->res . ", '', 1, 5, $taglimit)";
    }
    else {
      $sql .= "'', '" .$i->dbescape($q->res) . "', 1, 5, $taglimit)";
    }
  }
  $i->query($sql);

  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
    break;
  case 'NOROWS': // probably impossible now
    header('HTTP/1.1 204 No tags associated with resource');
    return;
    break;
  case 'OK':
    if ($i->result->num_rows == 1) {
      header('HTTP/1.1 204 No tags associated with resource');
    } 
    else {
      header('HTTP/1.1 200 Cloud OK');
    }
    break;
  }

  $df = new folksoDisplayFactory();
  $dd = $df->cloud();
  $row1 = $i->result->fetch_object(); // popping the title line
  
  /** in CLOUDY
   * tagdisplay = r.title
   * tagnorm = r.uri_raw
   * tagid = r.id
   */

  switch ($q->content_type()) {
  case 'html':
    $dd->activate_style('xhtml'); 
    print $dd->title($row1->tagdisplay);
    print $dd->startform();  // <ul>
    while ($row = $i->result->fetch_object()) {
      print $dd->line($row->cloudweight, 
                      "resourceview.php?tagthing=".
                      $row->tagid, $row->tagdisplay)."\n";
    }
    print $dd->endform();
    break;
  case 'xml':
    $dd->activate_style('xml');
    header('Content-Type: text/xml');
    print $dd->startform();
    print $dd->title($q->res);
    while ($row = $i->result->fetch_object()) {
      print $dd->line($row->tagid,
                      $row->tagdisplay,
                      $row->cloudweight) . "\n";
    }
    print $dd->endform();
    break;
  default:
    header("501 We need a datatype");
    print 
      "Sorry, but you did not give a datatype and we are too lazy "
      ." right now to supply a default.";
    return;
  }
}


/**
 * VisitPage : add a resource (uri) to the resource index
 * 
 * Web parameters: POST + folksores + folksovisit
 * Optional: folksourititle
 *
 * This uses a cache in /tmp to reduce (drastically) the number of database connections.
 *
 * Optional parameters: urititle,
 * 
 */
function visitPage (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $ic = new folksoIndexCache('/tmp/cachetest', 5);  

  $page = new folksoUrl($q->res, 
                        $q->is_single_param('urititle') ? $q->get_param('urititle') : '' );

  if (!($ic->data_to_cache( serialize($page)))) {
    trigger_error("Cannot store data in cache", E_USER_ERROR);
  }

  if ($ic->cache_check()) {
    $pages_to_parse = $ic->retreive_cache();

    $i = new folksoDBinteract($dbc);
    if ($i->db_error()) {
      header('HTTP/1.0 501 Database connection problem');
      die( $i->error_info());
    }

    $urls = array();
    $title = array();
    foreach ($pages_to_parse as $raw) {
      $item = unserialize($raw);
      $urls[] = $i->dbescape($item->get_url());
      $titles[] = $item->get_title();
    }

    $sql = 
      "CALL bulk_visit('".
      implode('&&&&&', $urls) . "', '".
      implode('&&&&&', $titles) . "', 1)";

    if (!($lfh = fopen('/tmp/folksologfile', 'a'))){
      trigger_error("logfile failed to open", E_USER_ERROR);
    }
    fwrite($lfh, implode("\n", $urls) . "\n");
    fclose($lfh);

      $i->query($sql);
      if ($i->result_status == 'DBERR') {
        header('HTTP/1.1 501 Database error');
        print $i->error_info() . "\n";
      }
      header('HTTP/1.1 200 Read cache');
      print "updated db";
      $i->done();
    } 
  else {
    header("HTTP/1.1 202 Caching visit");
    print "caching visit";
  }
}

/**
 * Web parameters : POST + folksonewuri
 * Optional : newtitle
 *
 */
function addResource (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  $query = 
    "CALL url_visit('" .
    $i->dbescape($q->res) .     "', '" .
    $i->dbescape($q->get_param('newtitle')) . "', 500)";
      
  if ($i->result_status == 'DBERR') {
    header('HTTP/1.1 501 DB error');
    die($i->error_info());
  }
  else {
    header('HTTP/1.1 201');
    print "Resource added";
  }
  $i->done();
}


/**
 * Web parameters: POST + folksores + folksotag
 * Optional : folksometa (defaults to 'normal' (1)). 
 */
function tagResource (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  $tag_args = argSort($q->res, $q->tag, $q->get_param('meta'), $i);

  $query = "CALL tag_resource($tag_args)";
  $i->query($query);

  if ($i->result_status == 'DBERR') {
    if (($i->db->errno == 1048) &&
        (strpos($i->db->error, 'resource_id'))) {
      header('HTTP/1.1 404 Missing resource');
      print "Resource ". $q->res . " has not been indexed yet.";
      $i->done();
      return;
    }
    elseif (($i->db->errno == 1048) &&
            (strpos($i->db->error, 'tag_id'))) {
      header('HTTP/1.1 404 Tag does not exist');
      print "Tag ". $q->tag . " does not exist.";
      print $i->error_info();
      $i->done;
      return;
    }
    else {
      header('HTTP/1.1 501 Database query error.');
      $i->done();
      print "obscure database problem";
      die($i->error_info());
    }
  }
  else {
    header('HTTP/1.1 200 Tagged');
    print "Resource has been tagged";
    print $query;
    print "  DB says: ". $i->db->error;
  }
  $i->done();
}


function unTag (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  $sql = '';

  if ((is_numeric($q->tag)) &&
      (is_numeric($q->res))) {
    $sql = 
      'DELETE FROM tagevent '.
      'WHERE (tag_id = ' . $q->tag .') '.
      'AND '.
      '(resource_id = ' . $q->res . ') ';
  }
  else {
    $query = 
      'DELETE FROM tagevent '.
      'USING tagevent JOIN resource r ON tagevent.resource_id = r.id '.
      'JOIN tag t ON tagevent.tag_id = t.id ';

    $where = 'WHERE';
    
    if (is_numeric($q->tag)) {
      $where .= ' (tagevent.tag_id = ' . $q->tag . ') ';
    }
    else {
      $where .= 
        " (t.tagnorm = normalize_tag('". $i->dbescape($q->tag) ."')) ";
    }

    if (is_numeric($q->res)) {
      $where .= ' AND '.
        ' (tagevent.resource_id = ' . $q->res . ') ';
    }
    else {
      $where .=  ' AND '.
        " (r.uri_normal = url_whack('". $i->dbescape($q->res) . "')) ";
    }
    $sql = $query . $where;
  }
  $i->query($sql);

  if ($i->result_status == 'DBERR') {
    header('HTTP/1.1 501 Database query error');
    die($i->error_info());
  }
  else {
    header('HTTP/1.1 200 Deleted');
  }
}

/**
 * Delete a resource and add its url to the list of excluded URL.
 */
function rmRes (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  // call rmres('url', id);
  $sql = "CALL rmres('";
  if (is_numeric($q->res)) {
    $sql .= "', ". $q->res . ")";
  }
  else {
    $sql .= $i->dbescape($q->res) . "', '')";
  }
  $i->query($sql);

 if ($i->result_status == 'DBERR') {
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
 }
 else {
   header('HTTP/1.1 200 Resource deleted');
   print "Resource " . $q->res . " permanently deleted\n";
   print "This resource will not be indexed in the future.";
   //   print $sql;
 }
}

/**
 * Associate a resource and an EAN-13 (ISBN-13) code. 
 *
 * These codes are treated differently from ordinary tags, first of
 * all because treating them as tags would expand the list of tags to
 * equal potentially the list of resources, also because the
 * relationships between EAN-13 and resources are fundamentally
 * different from those between resources and tags.
 *
 * Since we are allowing multiple EAN13 per resource (article about 2
 * or more books...), re-posting a 2nd ean13 to the same resource will
 * just add a new ean13 and will not affect previous data.
 *
 * We check the ean13 data and return a 406 if it is non-numeric or
 * too long.
 *
 * POST, res, ean13
 */
function assocEan13 (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {

  /** check **/
  if (! ean13dataCheck($q->get_param('ean13'))) {
    header('HTTP/1.1 406 Bad EAN13 data');
    print 
      "The folksoean13 field should consist of exactly 13 digits. "
      . $q->get_param('ean13') . " is " . strlen($q->get_param('ean13')) . " long "
      . is_numeric($q->get_param('ean13')) ? " and it is numeric " : " but it is not numeric " 
      ."\n\nPlease check your "
      ."data before trying again.";
    return;
  }

  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  if (is_numeric($q->res)) {
    $sql = 
      "INSERT INTO ean13 SET resource_id = " . $q->res
      . ", ean13 = " . $q->get_param('ean13'); //not escaping because we know this is just numbers
  }
  else {
    $sql =
      "INSERT INTO ean13 (ean13, resource_id) "
      ." VALUES (" . $q->get_param('ean13') . ", "
      ." (SELECT id FROM resource "
      ."WHERE uri_normal = url_whack('". $i->dbescape($q->res) . "'))) ";
  }

  $i->query($sql);
  if ($i->result_status == 'DBERR') {

    if (($i->db->errno == 1048) || // resource_id cannot be null
        ($i->db->errno == 1452)) { // cannot add or update a child row
      header('HTTP/1.1 404 Resource not found');
      print 
        "The resource you tried to associate with a EAN13 was not" 
        ." found in the database. \n\nPerhaps it has not yet been indexed,".
        "  or your reference is incorrect.";
    }
    elseif ($i->db->errno == 1062) {
      header('HTTP/1.1 409 Duplicate EAN13');
      print 
        "This resource/EAN13 combination is already present in the database.\n\n"
        ."Duplicate EAN13 entries for the same resource are not allowed. This is "
        ."slightly different from how tags work.";
      return;
    }
    else {
      header('HTTP/1.1 501 Database error');
      die($i->error_info());
    }
  }
  else {
    header('HTTP/1.1 200 OK');
    print "The EAN13 information was added to a resource.";
    return;
  }
}

/**
 * Web params: POST, res, oldean13, newean13
 */
function modifyEan13 (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {

  if (! ean13dataCheck($q->get_param('newean13'))) {
    header('HTTP/1.1 406 Bad EAN13 data');
    print 
      "The folksoean13 fields (old and new) should consist of up to 13 "
      ."digits. "
      . $q->get_param('oldean13') . " is " . strlen($get_param('oldean13')) . " long "
      . "and " . $q->get_param('newean13') . " is " . strlen($get_param('newean13')) . " long "
      ." \n\nPlease check your "
      ."data before trying again.";
    return;
  }
  
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.1 501 Database connection error');
    die($i->error_info());
  }

  $sql = 
    "UPDATE ean13 " 
    ."SET ean13 = " . $i->dbescape($q->get_param('newean13'));
  if (is_numeric($q->res)) {
    $sql .=
      " WHERE (resource_id = " . $i->dbescape($q->res) . ") ";
  }
  else {
    $sql .= 
      " WHERE resource_id = "
      ."(SELECT id FROM resource WHERE "
      ." uri_normal = url_whack('". $i->dbescape($q->res) . "'))";
  }
  $sql .= "AND (ean13 = " . $q->get_param('oldean13') . ")";

  $i->query($sql);
  if ($i->result_status == 'DBERR') {
    header('HTTP/1.1 501 Database query error');
    die($i->error_info());
  }
  elseif ($i->affected_rows == 0) {
    header('HTTP/1.1 404 Resource/EAN13  not found');
    print "The combination resource + ean13 was not found.";
    return;
  }
  else {
    header('HTTP/1.1 200 Modified');
    print "The EAN13 information was successfully modified.\n\nHave a nice day.";
  }
}

/**
 * Delete EAN13 information from a resource.
 */
function deleteEan13 (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  if (! ean13dataCheck($q->get_param('ean13'))) {
    header('HTTP/1.1 406 Bad EAN13 data');
    print 
      "The folksoean13 field should consist of exactly 13 digits. "
      ."\n\nPlease check your "
      ."data before trying again.";
    return;
  }

  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  $sql = 
    "DELETE FROM ean13  where (ean13 = " .  $q->get_param('ean13') . " "
    . "AND resource_id = ";
  if (is_numeric($q->res)) {
    $sql .=  $q->res;
  }
  else {
    $sql .= 
      "(select id from resource where uri_normal = url_whack('"
      . $q->res 
      . "'))";
  }
  $sql .= ")";
  $i->query($sql);

  if ($i->result_status == 'DBERR') {
    header('HTTP/1.1 501 Database insert error');
    die($i->error_info());
  }
  else {
    if ($i->affected_rows == 0) {
      header('HTTP/1.1 404 Resource/EAN13 not found');
      print 
        "The combination resource + EAN13 could not be found. "
        ."Nothing was deleted.";
      return;
    }
    else {
      header('HTTP/1.1 200 Deleted');
      print "The EAN13 information was deleted";
      return;
    }
  }
}


/**
 * Add a note to a resource
 *
 * Web params: POST, note, res
 */
function addNote (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  $sql = 
    "INSERT INTO note ".
    "SET note = '". $i->dbescape($q->get_param("note")) . "', ".
    "user_id = 9999, " .
    "resource_id = ";

  if (is_numeric($q->res)) {
    $sql .= $q->res;
  }
  else {
    $sql .= 
      "(SELECT id FROM resource  ".
      " WHERE uri_normal = url_whack('" . $q->res . "'))";
  }

  $i->query($sql);
  if ($i->result_status == 'DBERR') {
    header('HTTP/1.1 501 Database insert error');
    die($i->error_info());
  }
  else {
    header('HTTP/1.1 202 Note accepted');
    print "This note will be added to the resource: " . $q->res;
    print "\n\nText of the submitted note:\n". $q->get_param('note');
  }
}

function getNotes (folksoquery $q, folksoWsseCreds $cred, folksoDBconnect $dbc){
 $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  $sql = 
    "SELECT note, user_id, id \n\t".
    "FROM note n \n\t".
    "WHERE n.resource_id = ";

  if (is_numeric($q->res)){
    $sql .= $q->res;
  }
  else {
    $sql .= 
      "(SELECT id FROM resource r \n\t".
      " WHERE r.uri_normal = url_whack('" .$q->res . "'))";
  }
  $i->query($sql);
  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database query error');
    die($i->error_info());
    break;
  case 'NOROWS': 
    if ($i->resourcep($q->res)) {
      header('HTTP/1.1 404 No notes associated with this resource');
      //      print $sql;
      print "No notes have been written yet. Write one if you want.";
    }
    else {
      header('HTTP/1.1 404 Resource not found');
      print "Sorry. The resource for which you requested an annotation".
        " is not present in the database";
    }
    return;
    break;
  case 'OK':
    header('HTTP/1.1 200 Notes found');
    break;
}
  // assuming 200 from here on

  $df = new folksoDisplayFactory();
  $dd = $df->NoteList();
  $dd->activate_style('xml');

  print $dd->startform();
  print $dd->title($q->res);
  while ($row = $i->result->fetch_object()) {
    print $dd->line($row->user_id,
                    $row->id, 
                    $row->note);
  }
  print $dd->endform();
}

/**
 * Web params: POST + note + delete
 *
 * "note" must be a numerical note id.
 */
function rmNote (folksoquery $q, folksoWsseCreds $cred, folksoDBconnect $dbc){
 $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }
  if (! is_numeric($q->get_param('note'))){
    header('HTTP/1.1 400 Bad note argument');
    die($q->get_param('note') . ' is not a number');
  }

  $sql = "DELETE FROM note WHERE id = " . $q->get_param('note');
  $i->query($sql);

  if ($i->result_status == 'DBERR'){
    header('HTTP/1.1 Database delete errors');
    die($i->error_info());
  }
  else {
    header('HTTP/1.1 200 Deleted');
    print "The note " . $q->get_param('note'). " was deleted.";
  }
}

/**
 * Returns an xml list of resources associated with the same ean-13 as
 * the select resource
 *
 * Web params: GET, folksores, folksoean13list
 */
function resEans (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  $rq = new folksoResQuery();
  $sql = $rq->resEans($i->dbescape($q->res));

  $i->query($sql);

  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database query error');
    die($i->error_info());
    break;
  case 'NOROWS':
      header('HTTP/1.1 404 Resource not found');
      print "The requested resource is not present in the database.\n"
        ." Maybe it  has not been indexed yet, or an erroneous identifier "
        ." was used. ";
      return;
      break;
  case 'OK':
    if ($i->result->num_rows == 1) {
      header('HTTP/1.1 404 No EAN-13 data associated with this resource');
      print "There is no EAN-13 data yet for the resource " . $q->res . ".";
      print "<br/>" . $sql;
      return;
    }
    else {
      header('HTTP/1.1 200 EAN-13 data found');
    }
  }

  $title_line = $i->result->fetch_object(); /**popping the title that
                                               we are not using, but
                                               we could if we needed
                                               too (see note in ResQuery) 
                                            **/

  $df = new folksoDisplayFactory();
  $dd = $df->associatedEan13resources();
  $dd->activate_style('xml');

  print $dd->startform();
  while($row = $i->result->fetch_object()) {
    print $dd->line($row->id, 
                    $row->url,
                    $row->title);
  }
  print $dd->endform();                 

  }




/**
 * @param $res string (url) or integer 
 * @param $tag string (tagname) or integer
 * @param $meta string (metatagname) or integer
 * @param $i folksoDBinteract object for quoting
 *
 * @returns Argument string ready to be sent to stored procedure
 *
 * Return format (example, not including the double quotes): 
 *
 *  "'http://example.com', '', '', 55, 'author', ''"
 */
function argSort ($res, $tag, $meta, folksoDBinteract $i) {
  $result = array();
  foreach (array($res, $tag, $meta) as $arg) {
    $result[] = argParse($arg, $i);
  }
  return implode(', ', $result);
}


 function argParse ($aa, $i) {
  if (is_numeric($aa)) {
    return  "'', " . $aa;
  }
  else {
    return "'" .
      $i->dbescape($aa) .
      "', ''";
  }
}

/**
 * Length must be 13 characters, all numbers.
 * 
 * @param $ean string or integer. A candidate for ean13 status.
 * @return boolean
 */
function ean13dataCheck ($ean) {
  if ((is_numeric($ean)) &&
      (strlen($ean) == 13) &&
      (substr($ean, 0,1) != '0')){ /** we could add more powerful
                                 constraints here. if we were using
                                 postgres, they could go in the DB **/
    return true;
  }
  else {
    return false;
  }
}


?>