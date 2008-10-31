<?php


  /**
   * Web service for accessing, creating and modifying tag information
   * about resources (URLs).
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */

  //include('/var/www/dom/fabula/commun3/folksonomie/folksoTags.php');

require_once('folksoTags.php');
require_once('folksoIndexCache.php');
require_once('folksoUrl.php');

/*include('/var/www/dom/fabula/commun3/folksonomie/folksoIndexCache.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoUrl.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoServer.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoResponse.php');
include('/var/www/dom/fabula/commun3/folksonomie/folksoQuery.php');
*/

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
                                              'exclude' => array('clouduri', 'visit', 'note')),
                                        'getTagsIds'));


$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('res', 'tag'),
                                              'required' => array('delete'),
                                              'exclude' => array('meta', 'newresource')),
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
      "select id from resource where id = " .
      $i->dbescape($q->res);
  }
  else {
    $query = "select id
           from resource
           where uri_normal = url_whack('" .
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
 * id. 
 * 
 * Web parameters : GET + folksores 
 * Optional : metas only
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

  $select = "SELECT DISTINCT
                t.id as id, t.tagdisplay as tagdisplay, 
                t.tagnorm as tagnorm, t.popularity as popularity, 
                meta.tagdisplay as meta
             FROM tag t
             JOIN tagevent te ON t.id = te.tag_id
             JOIN metatag meta ON te.meta_id = meta.id
             JOIN resource r ON r.id = te.resource_id ";

  if (is_numeric($q->res)) {
    $select .= 
      " WHERE (r.id = " 
      . $q->res
      . ")";
  }
  else {
    $select .= 
      " WHERE (r.uri_normal = url_whack('"
      . $i->dbescape($q->res) ."')) ";
  }
  
  /** optional exclusion of tags with "normal" relation metatag **/
  if ($q->is_param('metaonly')) {
    $select .= ' AND (te.meta_id <> 1) ';
  }
  
  $select .= ' ORDER BY t.popularity DESC ';

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
 * Tag cloud local.
 *
 * Parameters: GET, folksoclouduri
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
  $sql = "CALL cloudy(";

  if (is_numeric($q->res)) {
    $sql .= $q->res . ", '', 1, 5)";
  }
  else {
    $sql .= "'', '" .$i->dbescape($q->res) . "', 5, 5)";
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
      print $sql;
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




?>