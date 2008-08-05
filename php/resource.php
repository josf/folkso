<?php


  /**
   * Web interface providing information about resources.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */

  //include('/var/www/dom/fabula/commun3/folksonomie/folksoTags.php');

require_once('folksoTags.php');
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
                                        array('required' => array('res')),
                                        'getTagsIds'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('res', 'tag'),
                                              'required' => array('delete')),
                                        'unTag'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('res', 'tag')),
                                        'tagResource'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required_single' => array('res'),
                                              'required' => array('visit')),
                                        'visitPage'));
$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('res', 'newresource')),
                                        'addResource'));
$srv->addResponseObj(new folksoResponse('delete',
                                        array('required' => array('res', 'tag')),
                                        'unTag'));

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
 *
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
             JOIN tagevent ON t.id = tagevent.tag_id
             JOIN metatag meta ON tagevent.meta_id = meta.id
             JOIN resource ON resource.id = tagevent.resource_id ";

  if (is_numeric($q->res)) {
    $select .= 
      " WHERE resource.id = " . 
      $q->res;
  }
  else {
    $select .= 
      " WHERE uri_normal = url_whack('". 
      $i->dbescape($q->res) ."') ";
  }
   
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
  case 'text/text':
    header('Content-Type: text/text');
    $dd->activate_style('text');
    break;
  case 'text/html':
    $dd->activate_style('xhtml');
    header('Content-Type: text/xhtml');
    break;
  case 'text/xml':
    $xf->activate_style('xml');
    break;
  default:
      $dd->activate_style('xhtml');
      break;
  }

  if ($q->content_type() == 'text/xml') {
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
  }
  else {
    $row = $i->result->fetch_object(); //???
    print $dd->title($row->uri_normal);
    print $dd->startform();
    while ( $row = $i->result->fetch_object() ) {
      print $dd->line($row->tagdisplay);
    }
    print $dd->endform();
  }
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
  
  $i->query("CALL cloudy('" . $i->dbescape($q->res) . "', 5, 5)");

  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
    break;
  case 'NOROWS':
    header('HTTP/1.1 204 No tags associated with resource');
    return;
    break;
  case 'OK':
    header('HTTP/1.1 200');
    break;
  }

  $df = new folksoDisplayFactory();
  $dd = $df->cloud();
  $dd->activate_style('xhtml'); // only style available right now.
  
  print $dd->startform();
  while ($row = $i->result->fetch_object()) {
    print $dd->line($row->cloudweight, 
                    "/resourceview.php?tagthing=".
                    $row->tagid, $row->tagdisplay)."\n";
  }
  print $dd->endform();
}


/**
 * VisitPage : add a resource (uri) to the resource index
 * 
 * Web parameters: POST + folksovisituri
 * This uses a cache in /tmp to reduce (drastically) the number of database connections.
 *
 * Optional parameters: urititle,
 * 
 */
function visitPage (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $ic = new folksoIndexCache('/tmp/cachetest', 500);  

  $page = new folksoUrl($q->res, 
                        $q->is_single_param('urititle') ? $q->get_param('urititle') : '' );

  if (!($ic->data_to_cache( serialize($page)))) {
    trigger_error("Cannot store data in cache", E_USER_ERROR);
  }

  if ($ic->cache_check() ) {
    $pages_to_parse = $ic->retreive_cache();

    $i = new folksoDBinteract($dbc);
    if ($i->db_error()) {
      header('HTTP/1.0 501 Database connection problem');
      die( $i->error_info());
    }

    foreach ($pages_to_parse as $raw) {
      $item = unserialize($raw);

      $query = "CALL url_visit('". 
        $db->real_escape_string($url_obj->get_url()). "', '" . 
        $i->dbescape($url_obj->get_title()) ."', 1)";

      $i->query($query);
      if ($i->result_status == 'DBERR') {
        header('HTTP/1.1 501 Database error');
        print $i->error_info() . "\n";
      }
    }
    $i->done();
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
 */
function tagResource (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.0 501 Database connection error');
    die($i->error_info());
  }

  $firstpart = '';
  $secondpart = '';

  if (is_numeric($q->res)) {
    $firstpart = "'', ". $q->res;
  }
  else {
    $firstpart = "'".$i->dbescape($q->res). "', ''";
  }

  if (is_numeric($q->tag)) {
    $secondpart = "'', ". $q->tag;
  }
  else {
    $secondpart = "'". $q->tag. "', ''";
  }

  $query = "CALL tag_resource($firstpart, $secondpart)";
  $i->query($query);
  if ($i->result_status == 'DBERR') {

    if (($i->db->errno == 1048) &&
        (strpos($i->db->error, 'resource_id'))) {
      header('HTTP/1.1 404');
      print "Resource ". $q->res . " has not been indexed yet.";
      $i->done();
      return;
    }
    elseif (($i->db->errno == 1048) &&
            (strpos($i->db->error, 'tag_id'))) {
      header('HTTP/1.1 404');
      print "Tag ". $q->tag . " does not exist.";
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
      'delete from tagevent '.
      'where (tag_id = ' . $q->tag .') '.
      'and '.
      '(resource_id = ' . $q->res . ') ';
  }
  else {
    $query = 
      'delete from tagevent '.
      'using tagevent join resource r on tagevent.resource_id = r.id '.
      'join tag t on tagevent.tag_id = t.id ';

    $where = 'where';
    
    if (is_numeric($q->tag)) {
      $where .= ' (tagevent.tag_id = ' . $q->tag . ') ';
    }
    else {
      $where .= 
        " (t.tagnorm = normalize_tag('". $i->dbescape($q->tag) ."')) ";
    }

    if (is_numeric($q->res)) {
      $where .= ' and '.
        ' (tagevent.resource_id = ' . $q->res . ') ';
    }
    else {
      $where .=  ' and '.
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

?>