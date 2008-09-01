<?php

require_once('folksoTags.php');

/**
 *
 * @package Folkso
 * @author Joseph Fahey
 * @copyright 2008 Gnu Public Licence (GPL)
 */

$srv = new folksoServer(array( 'methods' => 
                               array('GET', 'POST'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponse('get',
                                         array('required_single' => array('all')),
                                         'getAllMetas'));



$srv->Respond();

function getAllMetas (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
  }

  $sql = 
    'SELECT id, tagdisplay FROM metatag';
  $i->query($sql);

  switch ($i->result_status) {
  case 'DBERR':
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
    break;
  case 'NOROWS':
    header('HTTP/1.1 204 There is a problem with the metatag list');
    return;
    break;
  case 'OK':
    header('HTTP/1.1 200 Metataglist sent');
    header('Content-Type: text/xml');
    break;
  }
  
  $df = new folksoDisplayFactory();
  $dd = $df->MetatagList();
  $dd->activate_style('xml');

  print $dd->startform();
  while ($row = $i->result->fetch_object()) {
    print $dd->line($row->id, $row->tagdisplay);
  }
  print $dd->endform();
}

