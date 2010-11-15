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
$srv->Respond();

function autocomplete (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $i = new folksoDBinteract($dbc);
  $r = new folksoResponse();
  $r->setType('text');

  if ($i->db_error()) {
    $r->dbConnectionError($i->error_info());
    return $r;
  }

  $sql = "SELECT tagdisplay ".
            "FROM tag ".
            "WHERE tagnorm like '". 
    $i->dbescape(strtolower($q->get_param('q'))) . "%'";

  $i->query($sql);
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
    $r->setOk(200, 'OK I guess');
    while ($row = $i->result->fetch_object()) {

      /** For entirely numeric tags, we enclose them in quotes so that
          they can be treated as text instead of as ids. **/
      if (is_numeric($row->tagdisplay)) {
        $r->t('"' . $row->tagdisplay . '"' . "\n");
      }
      else {
        $r->t( $row->tagdisplay . "\n");
      }
    }
    return $r;
    break;
  }
}

?>