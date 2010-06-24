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
$srv->addResponseObj(new folksoResponse('get',
                                        array('required_single' => array('q')),
                                        'metacomplete'));


$srv->Respond();

function getAllMetas (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
  }

  $sql = 
    'SELECT id, tagdisplay FROM metatag WHERE id <> 1';
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

function metacomplete (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {
  $i = new folksoDBinteract($dbc);
  if ($i->db_error()) {
    header('HTTP/1.1 501 Database error');
    die($i->error_info());
  }

  $sql = 
    "select tagdisplay "
    ." from metatag "
    ." where "
    ." tagnorm like '"
    . $i->dbescape(strtolower($q->get_param('q')))
    . "%'";

  $i->query($sql);
  switch($i->result_status) {
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

      /** For entirely numeric tags, we enclose them in quotes so that
          they can be treated as text instead of as ids. **/
      if (is_numeric($row->tagdisplay)) {
        print '"' . $row->tagdisplay . '"' . "\n";
      }
      else {
        print $row->tagdisplay . "\n";
      }
    }
    break;
  }
}