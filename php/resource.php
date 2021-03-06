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
require_once('folksoSession.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponder('head', 
                                        array('required' => array('res')),
                                        'isHead'));

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('clouduri', 'res')),
                                        'tagCloudLocalPop'));

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('res'),
                                              'exclude' => 
                                              array('clouduri', 'visit', 'note', 'ean13list')),
                                        'getTagsIds'));


$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('res', 'ean13list'),
                                              'exclude' => array('delete', 'clouduri', 'note')),
                                        'resEans'));
                                              
$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('res', 'tag'),
                                              'required' => array('delete'),
                                              'exclude' => array('meta', 'newresource', 'ean13')),
                                        'unTag'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('res', 'tag'),
                                              'exclude' => array('delete', 'newresource')),
                                        'tagResource'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('res'),
                                              'required' => array('visit'),
                                              'exclude' => array('tag', 'note')),
                                        'visitPage'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('res', 'newtitle'),
                                              'exclude' => array('note', 'delete')),
                                        'addResource'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('res', 'ean13'),
                                              'exclude' => array('newean13', 'oldean13', 'note', 'meta', 'tag', 'delete')),
                                        'assocEan13'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('res', 'newean13', 'oldean13'),
                                              'exclude' => array('ean13', 'tag', 'delete')),
                                        'modifyEan13'));
$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('delete', 'res', 'ean13'),
                                              'exclude' => array('tag', 'note')),
                                        'deleteEan13'));


$srv->addResponseObj(new folksoResponder('delete',
                                        array('required' => array('res', 'tag')),
                                        'unTag'));

$srv->addResponseObj(new folksoResponder('delete',
                                        array('required_single' => array('res'),
                                              'exclude' => array('tag')),
                                        'rmRes'));
$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('res', 'delete'),
                                              'exclude' => array('tag')),
                                        'rmRes'));
$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('res', 'note'),
                                              'exclude' => array('tag', 'delete')),
                                        'addNote'));

$srv->addResponseObj(new folksoResponder('get',
                                       array('required_single' => array('res', 'note'),
                                             'exclude' => array('tag', 'delete')),
                                       'getNotes'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('note', 'delete'),
                                              'exclude' => array('res', 'tag')),
                                        'rmNote'));

$srv->Respond();

/**
 * Check to see if a resource is present in the database.
 * 
 * Web parameters: HEAD + folksouri or folksoid
 */
function isHead (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  try {
    $i = new folksoDBinteract($dbc);

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
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }

  if ($i->result_status == 'NOROWS') {
    $r->setError(404, 
                 'Resource not found',
                 'Resource '. $q->res . ' not present in database');
  }
  else {
    $r->setOk(200, 'Resource exists');
  }
  return $r;
}


/**
 * Retrieve the tags associated with a given resource. Accepts uri or
 * id. 
 * 
 * Web parameters : GET + folksores 
 * Optional: metas only
 * Optional: limit
 * Optional: ean13 Includes EAN13 information if available, tagged as 'EAN13'. 
 */
function getTagsIds (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  try {
    $i = new folksoDBinteract($dbc);

    // check to see if resource is in db.
    if  (!$i->resourcep($q->res))  {
      $r->setError(404, 'Resource not found');
      $r->errorBody("Resource not present in database");
      return $r;
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

    $include_eans = false;
    if ($q->is_param('ean13')) {
      $include_eans = true;
    }

    $rq = new folksoResQuery();  
    $select = $rq->getTags($i->dbescape($q->res), 
                           $limit, 
                           $metaonly, 
                           $include_eans);
    $i->query($select);
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }

  switch ($i->result_status) {
  case 'NOROWS':
    $r->setOk(204, 'No tags associated with resource');
    return $r;
    break;
  case 'OK':
    $r->setOk(200, 'Resource found');
    break;
  }
  // here everything should be ok (200)
  $df = new folksoDisplayFactory();
  $dd = $df->singleElementList();
  $xf = $df->Taglist();

  switch ($q->content_type()) {
  case 'text':
    $r->setType('text');
    $dd->activate_style('text');
    break;
  case 'html':
    $r->setType('html');
    $dd->activate_style('xhtml');
    break;
  case 'xml':
    $r->setType('xml');
    $xf->activate_style('xml');
    $r->t($xf->startform());
    while ($row = $i->result->fetch_object()) {
      $r->t($xf->line($row->id,
                      $row->tagnorm,
                      $row->tagdisplay,
                      $row->popularity,
                      $row->meta)
            );
    }
    $r->t($xf->endform());
    return $r;
    break;
  default:
      $dd->activate_style('xhtml');
      break;
  }

  //    $row = $i->result->fetch_object(); //???
  $r->t($dd->title($row->uri_normal));
  $r->t($dd->startform());
  while ( $row = $i->result->fetch_object() ) {
    $r->t($dd->line($row->tagdisplay));
  }
  $r->t($dd->endform());
  return $r;
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
function tagCloudLocalPop (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();

  try {
    $i = new folksoDBinteract($dbc);

    // check to see if resource is in db.
    if  (!$i->resourcep($q->res ))  {
      $r->setError(404, 'Resource not found');
      $r->errorBody("Resource not present in database");
      return $r;
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
      $sql = $rq->basic_cloud($i->dbescape($q->res),
                              $taglimit);
    }
    $i->query($sql);

    switch ($i->result_status) {
    case 'NOROWS': // probably impossible now
      $r->setOK(204, 'No tags associated with resource');
      return $r;
      break;
    case 'OK':  
      if ($i->result->num_rows == 0) { // should this be == 1 instead?
        $r->setOK(204, 'No tags associated with resource');
        return $r;
      } 
      else {
        $r->setOK(200, 'Cloud OK');
      }
      break;
    }
  }
  catch (dbException $e) {
    $r->handleDBexception($e);
  }


  $df = new folksoDisplayFactory();
  $dd = $df->cloud();
  //  $row1 = $i->result->fetch_object(); // popping the title line
  
  /** in CLOUDY
   * tagdisplay = r.title
   * tagnorm = r.uri_raw
   * tagid = r.id
   */

  switch ($q->content_type()) {
  case 'html':
    $dd->activate_style('xhtml'); 
    $r->setType('html');
    $r->t($dd->title($row1->tagdisplay));
    $r->t($dd->startform());  // <ul>
    while ($row = $i->result->fetch_object()) {
      $r->t($dd->line($row->cloudweight, 
                      "resourceview.php?tagthing=".
                      $row->tagid, $row->tagdisplay)."\n");
    }
    $r->t($dd->endform());
    return $r;
    break;
  case 'xml':
    $r->setType('xml');
    $dd->activate_style('xml');
    $r->t($dd->startform());
    $r->t($dd->title($q->res));
    while ($row = $i->result->fetch_object()) {
      $link = new folksoTagLink($row->tagnorm);
      $r->t($dd->line($row->tagid,
                      $row->tagdisplay,
                      htmlspecialchars($link->getLink()),
                      $row->cloudweight,
                      $row->tagnorm) . "\n");
    }
    $r->t($dd->endform());
    return $r;
    break;
  default:
    $r->setError(406, "Invalid or missing specified");
    $r->errorBody(
                  "Sorry, but you did not give a datatype and we are too lazy "
                  ." right now to supply a default."
                  );
    return $r;
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
function visitPage (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $ic = new folksoIndexCache('/tmp/cachetest', 5);  
  $r = new folksoResponse();
  $page = new folksoUrl($q->res, 
                        $q->is_single_param('urititle') ? $q->get_param('urititle') : '' );

  if (!($ic->data_to_cache( serialize($page)))) {
    $r->setError(500, 'Internal server error: could not use cache');
    return $r;
  }

  try {
    if ($ic->cache_check()) {
      $pages_to_parse = $ic->retreive_cache();
      $i = new folksoDBinteract($dbc);

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
        $r->setError(500, 'Internal server error: could not open logfile');
      }
      fwrite($lfh, implode("\n", $urls) . "\n");
      fclose($lfh);

      $i->query($sql);
      if ($i->result_status == 'DBERR') {
        $r->dbQueryError($i->error_info());
        return $r;
      }
      $r->setOk(200, "200 Read cache'");
      $r->t("updated db");
    }
    else {
      $r->setOk(202, "Caching visit");
      $r->t('Caching visit. Results will be incorporated shortly.');
    }
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }
  return $r;
}

/**
 * Web parameters : POST + folksonewuri
 * Optional : newtitle
 *
 */
function addResource (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  try {
    $i = new folksoDBinteract($dbc);
    $query = 
      "CALL url_visit('" .
      $i->dbescape($q->res) .     "', '" .
      $i->dbescape($q->get_param('newtitle')) . "', 500)";
    $i->query($query);
  }
  catch(dbConnectionException $e) {
    $r->dbConnectionError($e->getMessage());
    return $r;
  }
  catch(dbQueryException $e) {
    $r->dbQueryError($e->getMessage() . $e->sqlquery);
    return $r;
  }

  $r->setOk(201, "Resource added");
  $r->t('Resource added to database'); // TODO Return representation here (id, url)
  return $r; 
}


/**
 * Web parameters: POST + folksores + folksotag
 * Optional : folksometa (defaults to 'normal' (1)). 
 */
function tagResource (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $u = $fks->userSession(null, 'folkso', 'tag');
  if ((! $u instanceof folksoUser) ||
      (! $u->checkUserRight('folkso', 'tag'))) {
    return $r->unAuthorized($u);
  }

  try {
    $i = new folksoDBinteract($dbc);
    $tag_args = argSort($q->res, $q->tag, $q->get_param('meta'), $i);

    $query = sprintf("CALL tag_resource('%s', %s)",
                     $u->userid, $tag_args);
    $i->query($query);
  }
  catch (dbConnectionException $e) {
    $r->dbConnectionError($e->getMessage());
    return $r;
  }
  catch (dbQueryException $e) {
    if ($e->sqlcode == 1048) {
      if (strpos($e->getMessage(), 'resource_id')){
        $r->setError(404, 
                     "Missing resource",
                     "Resource " . $q->res . " has not been indexed yet.");
      }
      elseif (strpos($e->getMessage(), 'tag_id')) {
        $r->setError(404, 'Tag does not exist',
                     "Tag ". $q->tag . " does not exist. "
                     . $i->error_info());
      }
    }
    else {
      $r->dbQueryError($i->error_info());
    }
    return $r;
  }

  $r->setOk(200, "Tagged");
  $r->t("Resource has been tagged");

  return $r;
}

function unTag (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $u = $fks->userSession(null, 'folkso', 'tag');
  if ((! $u instanceof folksoUser) ||
      (! $u->checkUserRight('folkso', 'tag'))){
    return $r->unAuthorized($u);
  }

  try {
    $i = new folksoDBinteract($dbc);
    $sql = '';

    if ((is_numeric($q->tag)) &&
        (is_numeric($q->res))) {
      $sql = 
        'DELETE FROM tagevent '
        .'WHERE (tag_id = ' . $q->tag .') '
        .'AND '
        .'(resource_id = ' . $q->res . ') '
        . ' and '
        . "(userid = '" . $u->userid . "')";
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
      $where .= " and (tagevent.userid = '" . $u->userid . "')";

      $sql = $query . $where;
    }
    $i->query($sql);
  }
  catch (dbException $e){
    return $r->handleDBexception($e);
  }
  $r->setOK(200, 'Deleted');
  return $r;
}

/**
 * Delete a resource and add its url to the list of excluded URL.
 */
function rmRes (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $u = $fks->userSession(null, 'folkso', 'admin');
  if ((! $u instanceof folksoUser) ||
      (! $u->checkUserRight('folkso', 'admin'))) {
    return $r->unAuthorized($u);
  }

  try {
    $i = new folksoDBinteract($dbc);

    // call rmres('url', id);
    $sql = "CALL rmres('";
    if (is_numeric($q->res)) {
      $sql .= "', ". $q->res . ")";
    }
    else {
      $sql .= $i->dbescape($q->res) . "', '')";
    }
    $i->query($sql);
  }
  catch (dbException $e){
    return $r->handleDBexception($e);
  }

  $r->setOk(200, "Resource deleted");
  $r->t("Resource " . $q->res . " permanently deleted");
  $r->t("This resource will not be indexed in the future.");
  return $r;
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
function assocEan13 (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $u = $fks->userSession(null, 'folkso', 'redac');
  if ((! $u instanceof folksoUser) ||
      (! $u->checkUserRight('folkso', 'redac'))){
    return $r->unAuthorized($u);
  }

  try {
    $i = new folksoDBinteract($dbc);
    /** check **/
    if (! ean13dataCheck($q->get_param('ean13'))) {
      $r->setError(406, "Bad EAN13 data",
                   "The folksoean13 field should consist of exactly 13 digits. "
                   . $q->get_param('ean13') . " is " . strlen($q->get_param('ean13')) . " long "
                   . is_numeric($q->get_param('ean13')) ? " and it is numeric " : " but it is not numeric " 
                   ."\n\nPlease check your "
                   ."data before trying again.");
      return $r;
    }

    if (is_numeric($q->res)) {
      $sql = 
        "INSERT INTO ean13 SET resource_id = " . $q->res
        . ", ean13 = " . $q->get_param('ean13'); 
    }
    else {
      $sql =
        "INSERT INTO ean13 (ean13, resource_id) "
        ." VALUES (" . $q->get_param('ean13') . ", "
        ." (SELECT id FROM resource "
        ."WHERE uri_normal = url_whack('". $i->dbescape($q->res) . "'))) ";
    }

    $i->query($sql);
  }
  catch (dbConnectionException $e){
    $r->dbConnectionException($e->getMessage());
    return $r;
  }
  catch (dbQueryException $e) {
    if (($e->sqlcode == 1048) ||
        ($e->sqlcode == 1452)) {
      $r->setError(404,
                   "Resource not found",
                   "The resource you tried to associate with a EAN13 was not" 
                   ." found in the database. \n\nPerhaps it has not yet been indexed,".
                   "  or your reference is incorrect.");
    }
    elseif ($e->sqlcode == 1062) {
      $r->setError(409,
                   'Duplicate EAN13',
                   "This resource/EAN13 combination is already present in the database.\n\n"
                   ."Duplicate EAN13 entries for the same resource are not allowed. This is "
                   ."slightly different from how tags work.");
    }
    else {
      $r->dbQueryError($i->error_info());
    }
    return $r;
  }

  $r->setOk(200, 'Added');
  $r->t("The EAN13 information was added to a resource.");
  return $r;
}

/**
 * Web params: POST, res, oldean13, newean13
 */
function modifyEan13 (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $u = $fks->userSession(null, 'folkso', 'redac');
  if ((! $u instanceof folksoUser) ||
      (! $u->checkUserRight('folkso', 'redac'))) {
    return $r->unAuthorized($u);
  }

  try {
    $i = new folksoDBinteract($dbc);
  
    if (! ean13dataCheck($q->get_param('newean13'))) {
      $r->setError(406, 
                   'Bad EAN13 data',
                   "The folksoean13 fields (old and new) should consist of up to 13 "
                   ."digits. "
                   . $q->get_param('oldean13') . " is " . strlen($get_param('oldean13')) . " long "
                   . "and " . $q->get_param('newean13') . " is " . strlen($get_param('newean13')) . " long "
                   ." \n\nPlease check your data before trying again.");
      return $r;
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
  }
  catch(dbException $e) {
    return $r->handleDBexception($e);
  }

  if ($i->affected_rows === 0) {
    $r->setError(404, 
                 'Resource/EAN13  not found',
                 "The combination resource + ean13 was not found.");
  }
  else {
    $r->setOk(200, 'Modified');
    $r->t("The EAN13 information was successfully modified.\n\nHave a nice day.");
  }
  return $r;
}

/**
 * Delete EAN13 information from a resource.
 */
function deleteEan13 (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $u = $fks->userSession(null, 'folkso', 'redac');
  if ((! $u instanceof folksoUser) ||
      (! $u->checkUserRight('folkso', 'redac'))) {
    return $r->unAuthorized($u);
  }

  try {
    $i = new folksoDBinteract($dbc);
    if (! ean13dataCheck($q->get_param('ean13'))) {
      $r->setError(406, 'Bad EAN13 data',
                   "The folksoean13 field should consist of exactly 13 digits. "
                   ."\n\nPlease check your "
                   ."data before trying again.");
      return $r;
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
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }

  if ($i->affected_rows == 0) {
    return $r->setError(404, 'Resource/EAN13 not found',
                        "The combination resource + EAN13 could not be found. "
                        ."Nothing was deleted.");
  }
  else {
    $r->setOk(200, 'Deleted');
    $r->t( "The EAN13 information was deleted");
    return $r;
  }
}


/**
 * Add a note to a resource
 *
 * Web params: POST, note, res
 */
function addNote (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $u = $fks->userSession(null, 'folkso', 'tag');
  if ((! $u instanceof folksoUser) ||
      (! $u->checkUserRight('folkso', 'tag'))) {
    return $r->unAuthorized($u);
  }

  try {
    $i = new folksoDBinteract($dbc);
    $sql = 
      "INSERT INTO note "
      . "SET note = '". $i->dbescape($q->get_param("note")) . "', "
      . "userid = '" . $u->userid . "', "
      . "resource_id = ";

    if (is_numeric($q->res)) {
      $sql .= $q->res;
    }
    else {
      $sql .= 
        "(SELECT id FROM resource  ".
        " WHERE uri_normal = url_whack('" . $q->res . "'))";
    }

    $i->query($sql);
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }

  $r->setOk(202, 'Note accepted');
  $r->t( "This note will be added to the resource: " . $q->res);
  $r->t( "\n\nText of the submitted note:\n". $q->get_param('note'));
  return $r;
}

function getNotes (folksoquery $q, folksoDBconnect $dbc, folksoSession $fks){
  $r = new folksoResponse();
  $u = $fks->userSession(null, 'folkso', 'redac');
  if ((! $u instanceof folksoUser) ||
      (! $u->checkUserRight('folkso', 'redac'))){
    return $r->unAuthorized($u);
  }

  try {
    $i = new folksoDBinteract($dbc);

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
  }
  catch (dbException $e){
    return $r->handleDBexception($e);
  }

  if  ($i->result_status ==  'NOROWS'){
    if ($i->resourcep($q->res)) {
      $r->setError(404, 'No notes associated with this resource',
                   "No notes have been written yet. Write one if you want.");
    }
    else {
      $r->setError(404, 'Resource not found',
                   "Sorry. The resource for which you requested an annotation".
                   " is not present in the database");
    }
    return $r;
  }

  $r->setOk(200, 'Notes found');

  // assuming 200 from here on
  $df = new folksoDisplayFactory();
  $dd = $df->NoteList();
  $dd->activate_style('xml');

  $r->t( $dd->startform());
  $r->t( $dd->title($q->res));
  while ($row = $i->result->fetch_object()) {
    $r->t( $dd->line($row->user_id,
                    $row->id, 
                     $row->note));
  }
  $r->t( $dd->endform());
  return $r;
}

/**
 * Web params: POST + note + delete
 *
 * "note" must be a numerical note id.
 */
function rmNote (folksoquery $q, folksoDBconnect $dbc, folksoSession $fks){
  $r = new folksoResponse();
  $u = $fks->userSession(null, 'folkso', 'redac');
  if ((! $u instanceof folksoUser) ||
      (! $u->checkUserRight('folkso', 'redac'))){
    return $r->unAuthorized($u);
  }
  try {
    $i = new folksoDBinteract($dbc);
    if (! is_numeric($q->get_param('note'))){
      $r->setError(400, 'Bad note argument',
                   $q->get_param('note') . ' is not a number');
      return $r;
    }

    $sql = "DELETE FROM note WHERE id = " . $q->get_param('note');
    $i->query($sql);
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }

  $r->setOk(200, 'Deleted');
  $r->t( "The note " . $q->get_param('note'). " was deleted.");
  return $r;
}

/**
 * Returns an xml list of resources associated with the same ean-13 as
 * the selected resource
 *
 * Web params: GET, folksores, folksoean13list
 */
function resEans (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  try {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  $rq = new folksoResQuery();
  $sql = $rq->resEans($i->dbescape($q->res));

  $i->query($sql);
  }
  catch (dbConnectionException $e) {
    $r->dbConnectionError($e->getMessage());
    return $r;
  }
  catch (dbQueryException $e) {
    $r->dbQueryError($e->getMessage() . $e->sqlquery);
    return $r;
  }

  switch ($i->result_status) {
  case 'NOROWS':
    $r->setError(404, 'Resource not found',
                 "The requested resource is not present in the database.\n"
                 ." Maybe it  has not been indexed yet, or an erroneous identifier "
                 ." was used. ");
    return $r;
    break;
  case 'OK':
    if ($i->result->num_rows == 1) {
      $r->setError(404, 'No EAN-13 data associated with this resource',
                   "There is no EAN-13 data yet for the resource " . $q->res . ".");
      return $r;
    }
    else {
      $r->setOk(200, 'EAN-13 data found');
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

  $r->t($dd->startform());
  while($row = $i->result->fetch_object()) {
    $r->t( $dd->line($row->id, 
                     $row->url,
                     $row->title));
  }
  $r->t( $dd->endform());
  return $r;
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