<?php


/**
 *
 * @package Folkso
 * @author Joseph Fahey
 * @copyright 2008 Gnu Public Licence (GPL)
 * @subpackage Tagserv
 */


require_once('folksoTags.php');
require_once('folksoAlpha.php');
require_once('folksoTagQuery.php');
require_once('folksoSession.php');

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

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('fancy'),
                                              'required_single' => array('tag')),
                                        'fancyResource'));

$srv->addResponseObj(new folksoResponder('get', 
                                        array('required_single' => 
                                              array('tag', 
                                                    'resources')),
                                        'getTagResources'));

$srv->addResponseObj(new folksoResponder('get', 
                                        array('required' => array('tag'),
                                              'exclude' => array('related')),
                                        'getTag'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('newtag')),
                                        'singlePostTag'));

$srv->addResponseObj(new folksoResponder('get', 
                                        array('required' => array('autotag')),
                                        'autoCompleteTags'));

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('related')),
                                        'relatedTags'));

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('byalpha')),
                                        'byalpha'));

$srv->addResponseObj(new folksoResponder('head',
                                        array('required' => array('tag')),
                                        'headCheckTag'));
$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('alltags')),
                                        'allTags'));

/**
 * Note that the "tag" field here refers to the resource that will be
 * deleted during the merge.
 */
$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('tag', 'target')),
                                        'tagMerge'));

$srv->addResponseObj(new folksoResponder('delete',
                                        array('required_single' => array('tag')),
                                        'deleteTag'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('delete'),
                                              'required_single' => array('tag')),
                                        'deleteTag'));

$srv->addResponseObj(new folksoResponder('post',
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
function headCheckTag (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  $i->query("select id from tag where tagnorm = normalize_tag('" .
            $i->dbquote($q->get_param('tag')) .
            "') " . 
            " limit 1");
  
  switch ($i->result_status) {
  case 'DBERR':
    $r->dbQueryError($i->error_info());
    return $r;
    break;
  case 'NOROWS':
    $r->setError(404, 'Tag does not exist',
                 'The tag '. $q->get_param('tag') 
                 . ' is not present in our database.');
    return $r;
    break;
  case 'OK':
    $r->setOk(200, 'Tag exists');
    $id = 0;
    while ($row = $i->result->fetch_object()) {
      $id = $row->id;
    }
    $r->addHeader("X-Folkso-Tagid: " . $id);
    return $r;
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
function getTag (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
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
    $r->dbQueryError($i->error_info());
    return $r;
    break;
  case 'NOROWS':
    $r->setError(404, 'Tag not found',
                 'The tag ' . $q->tag . ' was not found');
    return $r;
    break;
  case 'OK':
    $r->setOk(200, 'Found');
    break;
  }

  $df = new folksoDisplayFactory();
  $disp = $df->singleElementList();
  $disp->activate_style('xml');
  $r->t($disp->startform());
  while($row = $i->result->fetch_object()) {
    $r->t($disp->line($row->tagdisplay));
  }
  $r->t( $disp->endform());
  return $r;
}

/**
 * Retrieves a list of the resources associated with the given tag.
 *
 * Parameters: GET, resources (single param)
 *
 * We might have to think about adding a way to announce whether there
 * is a "next page" or not.
 * 
 * Currently supports xhtml and xml dataformats.
 */
function getTagResources (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  // check to see if tag exists -- can this be done with the main query instead?
  if (!$i->tagp($q->tag)) {
    if ($i->db_error()) {
      $r->dbQueryError($i->error_info());
      return $r;
    }
    else {
      $r->setError(404, 'Tag not found',
                   $q->tag . " does not exist in the database");
      return $r;
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
  if (is_numeric($q->tag)) {
    $querybase .= 
      "WHERE tag.id = " . 
      $i->dbquote($q->tag);
  } //tag by string
  else {
    $querybase .= 
      "WHERE tag.tagnorm = normalize_tag('" .
      $i->dbquote($q->tag) . "')";
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
    $r->dbQueryError($i->error_info());
    return $r;
    break;
  case 'NOROWS':
    $r->setOk(204, 'No resources associated with  tag');
    $r->t( "No resources are currently associated with " .
           $q->tag);
    return $r;
    break;
  case 'OK':
    $r->setOk(200, 'Tag found');
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
  $r->t($dd->startform());
  while ($row = $i->result->fetch_object()) {
    $r->t($dd->line( $row->id, 
                     $row->href, 
                     array('xml' => $row->display,
                           'default' => $row->display)));
  }
  $r->t($dd->endform());
  return $r;
}
/*                           html_entity_decode(strip_tags($row->display), 
                                              ENT_NOQUOTES, 
                                              'UTF-8'),*/


function relatedTags (folksoQuery $q, folksoWsseCreds $cred, folksoDBConnect $dbc) {
  $i = new folksoDBinteract($dbc);
  //$r = new folksoResponse();

  if ($i->db_error()){
    //$r->dbConnectionError();
    //return $r;
    header('HTTP/1.1 501 Database connection error');
    die($i->error_info());
  }
  
  $tq = new folksoTagQuery();
  $i->query($tq->related_tags($q->tag));
  switch($i->result_status){
  case 'DBERR':
    //$r->dbQueryError($i->error_info);
    //return $r;
    header('HTTP/1.1 501 Database query error');
    die($i->error_info());
    break;
  case 'NOROWS':
    //$r->setOk(204, 'No related tags yet');
    //return $r;
    header('HTTP/1.1 204 No related tags yet');
    return;
  case 'OK':
    //$r->setOk(200, 'Related tags found');
    header('HTTP/1.1 200 Related tags found');
  }
  
  $df = new folksoDisplayFactory();
  $dd = $df->TagList();
  $dd->activate_style('xml');

  $accum .= $dd->startform();
  //pop title row
  $title_row = $i->result->fetch_object();
  
  //  $accum = $dd->title($title_row->display);
  while ($row = $i->result->fetch_object()) {
    //$r->t(
    $accum .= $dd->line($row->tagid,
                       $row->tagnorm,
                       $row->display,
                       $row->popularity,
                       '');
  }

  $accum .= $dd->endform();

  /** Default html output via xslt transformation. Content-type
      negotiation should be handled here. **/
  $accum_XML = new DOMDocument();
  $accum_XML->loadXML($accum);
  
  $loc = new folksoFabula();
  $xsl = new DOMDocument();
  $xsl->load($loc->xsl_dir . "reltags.xsl");

  $proc = new XsltProcessor();
  $proc->importStylesheet($xsl);
  $proc->setParameter('', 
                    'tagviewbase',
                    $loc->server_web_path . 'tagview.php?tag=');
  // by using transformToXML instead of transformToDoc, we avoid
  // putting an xml type declaration into the output doc.
  $reltags = $proc->transformToXML($accum_XML);
  //  $xml = $reltags->saveXML();
  print $reltags;

}


/**
 * Add a new tag.
 *
 * POST, newtag
 *
 */
function singlePostTag (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  $sql = 
    "CALL new_tag('" . 
    $i->dbescape(stripslashes($q->get_param('folksonewtag'))) . "')";
  $i->query($sql);

  switch ($i->result_status) {
  case 'DBERR':
    $r->dbQueryError($i->error_info());
    return $r;
    break;
  case 'OK':
    $r->setOk(201, 'Tag created');
    $row = $i->result->fetch_object();
    $r->addHeader('X-Folksonomie-Newtag: ' . $row->tagnorm);
    $r->t("Tag created (or already existed), id is " 
          . $row->id . ' : ' . $q->get_param('newtag'));
  return $r;
  }
}


function fancyResource (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  /*
   * This is a bit of a hack. We are using a UNION to put the tag name
   * in the first row of the result set, to avoid two separate
   * queries. If columns are to be added to the main query, equivalent
   * dummy columns should be added to the first part of the UNION.
   */
$querytagtitle = 
  "SELECT tagdisplay AS title, \n\t" .
  "id AS id, \n\t" .
  "'dummy' AS href, \n\t" .
  "'dummy' AS display, \n\t" .
  "'dummy' AS tags \n".
  "FROM tag \n\t";
  if (is_numeric($q->tag)) {
    $querytagtitle .= ' WHERE id = ' . $q->tag . ' ';
  }
  else {
    $querytagtitle .= " WHERE tagnorm = normalize_tag('" . 
      $i->dbescape($q->tag) . "') ";
  }

  $querytagtitle .= ' LIMIT 1 '; // just to be sure

$querystart = 
'  SELECT 
  r.title AS title, 
  r.id AS id,
  r.uri_raw AS href,
  CASE 
    WHEN title IS NULL THEN uri_normal 
    ELSE title
  END AS display,
  (SELECT 
       GROUP_CONCAT(DISTINCT concat(t2.tagdisplay, \'::\', t2.tagnorm) SEPARATOR \' - \')
       FROM tag t2
       JOIN tagevent te2 ON t2.id = te2.tag_id
       JOIN resource r2 ON r2.id = te2.resource_id
       WHERE r2.id = r.id
       ) AS tags
  FROM resource r
  JOIN tagevent te ON r.id = te.resource_id
  JOIN tag t ON te.tag_id = t.id';

//  $queryend = " LIMIT 100";
  $querywhere = '';
  if (is_numeric($q->tag)) {
    $querywhere = 'WHERE t.id = ' . $q->tag . ' ';
  }
  else {
    $querywhere = "WHERE t.tagnorm = normalize_tag('" . 
      $i->dbescape($q->tag) . "') ";
  }
  $total_query = $querytagtitle . " UNION \n" .  $querystart . ' '  . $querywhere . ' ' . $queryend;
  $i->query($total_query);

  switch ($i->result_status) {
  case 'DBERR':
    $r->dbQueryError($i->error_info());
    return $r;
    break;
  case 'NOROWS':
    $r->setOk(200, 'No resources associated with  tag');
    return $r;
    break;
  case 'OK':
    $r->setOk(200, 'Found');
    break;
  default:
    $r->setError(500, 'Inexplicable error',    
                 'This does not make sense');
    return $r;
  }
  // so we are 'OK'

  $df = new folksoDisplayFactory();
  $dd = $df->FancyResourceList();
  
  $dd->activate_style('xml');

  //pop the first line of the results containing the tagtitle
  $row1 = $i->result->fetch_object();  
  $r->t($dd->startform());
  $r->t($dd->title($row1->title));

  while ($row = $i->result->fetch_object()) {
    $r->t( $dd->line( $row->id,
                     htmlspecialchars($row->href, ENT_COMPAT, 'UTF-8'),
                     html_entity_decode(strip_tags($row->display), ENT_NOQUOTES, 'UTF-8'),
                     htmlspecialchars($row->tags, ENT_COMPAT, 'UTF-8')
                      )); // inner quotes supplied by sql
  }
  $r->t( $dd->endform());
  return $r;
}


/** 
 * GET, autotag
 */
function autoCompleteTags (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  $req = substr($q->get_param('autotag'), 0, 3);
  
  $i->query("select tagdisplay
                        from tag
                        where tagdisplay like '" .
            $i->dbescape($req) .
            "%'");

  switch ($i->result_status){
  case 'DBERR':
    $r->dbQueryError($i->error_info());
    return $r;
    break;
  case 'OK':
    $r->setOk(200, 'Tags found');
    $df = new folksoDisplayFactory();
    $dd = $df->singleElementList();
    $dd->activate_style('xhtml');

    $r->t($dd->startform());
    while($row = $i->result->fetch_object()) {
      $r->t($dd->line($row->tagdisplay) . "\n");
    }
    $r->t($dd->endform());
    return $r;
    break;
  case 'NOROWS':
    $r->setOk(204, 'No tags');
    return $r;
  }
}

/**
 * POST, res, target 
 * 
 * Retags all the tagevents tagged by "source" as "target", then
 * deletes "source".
 *
 * Returns 204 on success, 404 on missing tags.
 * 
 */

function tagMerge (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  /* build query. all the funny quote marks are there because the
     stored procedure 'tagmerge' needs 4 arguments to distinguish
     between numeric args and non numeric args*/
  $source_part = '';
  if (is_numeric($q->tag)){
    $source_part = $q->tag . ", ''";
  }
  else {
    $source_part = "'', '". $i->dbquote($q->tag) . "'";
  }

  $target_part = '';
  if (is_numeric($q->get_param('target'))) {
    $target_part = $q->get_param('target') . ", ''";
  }
  else {
    $target_part = "'', '" . $i->dbquote($q->get_param('target')) . "'";
  }
  
  $i->query("CALL TAGMERGE($source_part, $target_part)");
  
  if ($i->result_status == 'DBERR') {
    $r->dbQueryError($i->error_info());
  }
  else {
    $row = $i->result->fetch_object();
    $newid = $row->newid;
    switch ($row->status) {
    case 'OK':
      $r->setOk(204, 'Merge successful');
      $r->t( $i->first_val('status'));
      $r->addHeader('X-Folksonomie-TargetId: ' . $newid);
      break;
    case 'NOTARGET':
      $r->setError(404, 'Invalid target tag', 
                   $status .
                   $q->get_param('target') . 
                   " is not present in the database. Merge not accomplished.");
      break;
    case 'NOSOURCE':
      $r->setError(404, 'Invalid source tag',
                   $q->res . 
                   " is not present in the database. Merge not accomplished.");
      break;
    default:
      $r->setError(500, 'Strange server error',
                   "fv: ". $status .
                   'This should not have happened.' . $row->status);
    }
  }
  return $r;
}

/**
 * DELETE, folksodelete (can be tag id or tag name)
 *
 * Obviously, this badly needs authentication!
 *
 * 404 on tag not found. 204 on success.
 */
function deleteTag  (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  /* Check to see if tag is there. This might not be necessary: if the
   * tag does not exist, we just continue */
  if (!$i->tagp($q->tag)) {
    if ($i->db_error()) {
      $r->dbQueryError($i->error_info());
    }
    else {
      $r->setError(404, 'Could not delete - tag not found',
                   $q->tag . " does not exist in the database");
    }
    return $r;
  }

  // delete 1 : remove tagevents
  // delete 2 : remove the tag itself
  $delete1 = '';
  $delete2 = "DELETE FROM tag  ";

  if (is_numeric($q->tag)) {
    $delete1 .= ' DELETE FROM tagevent WHERE tag_id='. $q->tag;
    $delete2 .= ' WHERE id='. $q->tag;
  }
  else {
    $delete1 .= " DELETE  "
      ." FROM tagevent USING tag, tagevent "
      ." where tag.id = tagevent.tag_id and "
      ." tag.tagnorm = normalize_tag('" . $i->dbescape($q->tag) . "')";    

    $delete2 .= " WHERE tagnorm = normalize_tag('" . $i->dbescape($q->tag) . "')";
  }

  $i->query($delete1); // delete tagevent
  if ($i->result_status ==  'DBERR') {
    $r->dbQueryError($i->error_info());
    return $r;
  }
  $i->query($delete2); // delete tag
  if ($i->result_status ==  'DBERR') {
    $r->setError(500, 'Database error',
                 "Secondary delete failed ". $i->error_info());
    return $r;
  }
  $r->setOk(204, 'Tag deleted');
  return $r;
}

/**
 * byalpha
 *
 * GET, folksoalpha
 * 
 * List of tags by letter of the alphabet. Up to three letters are allowed.
 *
 */
function byalpha (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }
  
  $alpha = substr($q->get_param('byalpha'), 0, 1);

  $al = new folksoAlpha();
  // we are not going to escape anything because only one-character
  // strings are allowed.
  $ors = $al->SQLgroup($al->lettergroup($alpha), "tagnorm"); 

  $query = 
    "SELECT id, tagdisplay, tagnorm, \n".
    "(SELECT COUNT(*) \n".
    "FROM tagevent te  \n".
    "WHERE te.tag_id = tag.id) AS popularity
            FROM tag
            WHERE " . $ors;

  $i->query($query);
  switch ($i->result_status) {
  case 'DBERR':
    $r->dbQueryError($i->error_info());
    break;
  case 'NOROWS':
    $r->setOk(204, 'No matching tags');
    break;
  case 'OK':
    $r->setOk(200, 'OK');

    // assuming everything is ok (200)
    $df = new folksoDisplayFactory();
    $dd = $df->TagList();
    $dd->activate_style('xml');
    $r->setType('xml');
    $r->t($dd->startform());
    while ($row = $i->result->fetch_object()) {
      $r->t($dd->line($row->id, 
                      $row->tagnorm,
                      $row->tagdisplay,
                      $row->popularity,
                      '') . "\n"); // empty field because there are no metatags here
    }
    $r->t($dd->endform());
    break;
  }
  return $r;
}

/**
 * rename tag
 *
 * rename, newname
 * 
 */
function renameTag (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  if (!$i->tagp($q->tag)) {
    $r->setError(404, 'Tag not found',
                 'Nothing to rename. No such tag: ' . $q->tag);
    return $r;
  }

  $query = "UPDATE tag
            SET tagdisplay = '" . 
    $i->dbescape($q->get_param('newname')) . "', " .
    "tagnorm = normalize_tag('" . $i->dbescape($q->get_param('newname')) . "') ".
    "where ";

  if (is_numeric($q->tag)) {
    $query .= " id = " . $q->tag;
  }
  else {
    $query .= " tagnorm = normalize_tag('" . 
      $i->dbescape($q->tag) . "')";
  }
  $i->query($query);
  if ($i->result_status == 'DBERR') {
    $r->dbQueryError($i->error_info());
  }
  else {
    $r->setOk(204, 'Tag renamed');
    return $r;
  }
}


/**
 * List of all the tags.
 */
function allTags (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  $query = 
    "SELECT t.tagdisplay AS display, t.id AS tagid, \n\t" .
    "t.tagnorm AS tagnorm, \n\t".
    "(SELECT COUNT(*) FROM tagevent te WHERE te.tag_id = t.id) AS popularity \n".
    "FROM tag t \n".
  " ORDER BY display ";
    
  $i->query($query);
  if ($i->result_status != 'OK') {
    $r->dbQueryError($i->error_info());
    return $r;
  }
  $r->setOk(200, 'There they are');
  $df = new folksoDisplayFactory();
  $dd = $df->TagList();
  $dd->activate_style('xml');

  $r->t($dd->startform());
  while ($row = $i->result->fetch_object()) {
    $r->t($dd->line($row->tagid,
                    $row->tagnorm,
                    $row->display,
                    $row->popularity,
                    ''));
  }
  $r->t($dd->endform());
  return $r;
}

?>
