<?php


/**
 * Interface for responding to autocomplete tag requests from the
 * jquery.autocomplete plugin.
 *
 * @package Folkso
 * @author Joseph Fahey
 * @copyright 2008 Gnu Public Licence (GPL)
 */

require_once('folksoTags.php');

$srv = new folksoServer(array( 'methods' => array('GET'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponse('get',
                                        array('required' => array('q')),
                                        'autocomplete'));
$srv->Respond();

function autocomplete (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
  }

  $sql = "select tagdisplay ".
            "from tag ".
            "where tagnorm like '". 
    $i->dbescape(strtolower($q->get_param('q'))) . "%'";

  $i->query($sql);
  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database query error');
    die($i->error_info());
    break;
  case 'NOROWS':
    header('HTTP/1.1 204 No matching tags');
    return;
    break;
  case 'OK':
    header('HTTP/1.1 200 OK I guess');
    while ($row = $i->result->fetch_object()) {
      print $row->tagdisplay . "\n";
    }
    break;
  }
}

?>