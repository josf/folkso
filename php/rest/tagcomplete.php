<?php


/**
 * Interface for responding to autocomplete tag requests from the
 * jquery.autocomplete plugin.
 *
 * @package Folkso
 * @author Joseph Fahey
 * @copyright 2008 Gnu Public Licence (GPL)
 * @subpackage Tagserv
 */

require_once('folksoTags.php');
require_once('folksoResponse.php');
require_once('folksoResponder.php');

$srv = new folksoServer(array( 'methods' => array('GET'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('q')),
                                        'autocomplete'));

$srv->addResponseObj(new folksoResponder('get',
                                         array('required' => array('term')),
                                         'autocomplete'));

$srv->Respond();

function autocomplete (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $i = new folksoDBinteract($dbc);
  $r = new folksoResponse();

  /*
   * term is for the jquery.ui autocomplete, q is for the older
   * jquery.autocomplete.js lib. Eventually, we would like to use only
   * the jquery.ui, but by distinguishing based on request type, text
   * for the old autocomplete and json for the new, the transition
   * should be fairly smooth.
   */

  $typed = '';
  $json = false;
  if ($q->is_param('term')) {
    $r->setType('json');
    $typed = $q->get_param('term');
    $json = true;
  }
  else {
    $r->setType('text');
    $typed = $q->get_param('q');
  }

  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  $sql = "SELECT tagdisplay ".
            "FROM tag ".
            "WHERE tagnorm like '". 
    $i->dbescape(strtolower($typed)) . "%'";

  $i->query($sql);

  /**
   * The php array that will be returned as a json array, if that is
   * the result type.
   */
  $preJsonArray = array();

  switch ($i->result_status) {
  case 'DBERR':
    $r->dbQueryError($i->error_info());
    return $r;
    break;
  case 'NOROWS':
    $r->setOk(204, 'No matching tags');
    return $r;
    break;
  case 'OK':
    $r->setOk(200, 'OK, tags found');
    while ($row = $i->result->fetch_object()) {

      /** For entirely numeric tags, we enclose them in quotes so that
          they can be treated as text instead of as ids. **/
      if (is_numeric($row->tagdisplay)) {
          $tag = htmlspecialchars('"' . $row->tagdisplay . '"');
      }
      else {
        $tag = htmlspecialchars($row->tagdisplay);
      }
        
      if ($json) {
        $preJsonArray[] = $tag;
      }
      else {
        $r->t($tag . "\n");
      }
    }
    if (! $json) {
      return $r;
    }
    else {
      $r->t(json_encode($preJsonArray));
      return $r;
    }
    break;
  }
}
