<?php


  /**
   * Web service for accessing users tags.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */


require_once('folksoTags.php');
require_once('folksoIndexCache.php');
require_once('folksoUrl.php');
require_once('folksoSession.php');
require_once('folksoUser.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponder('get',
                                         array('required' => array('uid')),
                                         'getMyTags'));
                                               

$srv->Respond();

/**
 * Just a list of tags
 */
function getMyTags (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks){
  $r = new folksoResponse();
  $u = $fks->userSession(null);
  if (! $u instanceof folksoUser){
    return $r->unAuthorized($u); // add message about logging in?
  }

  try {
    $i = new folksoDBinteract($dbc);
    $sql = 
      sprintf(
              '  select t.tagnorm, t.id, t.tagdisplay, count(te.tag_id) as cnt'
              .' from tag t '
              .' join tagevent te on t.id = te.tag_id '
              ." where te.userid = '%s' "
              .' group by t.tagnorm ',
              $i->dbescape($u->userid));
    $i->query($sql);
  }
  catch(dbException $e) {
    return $r->handleDBexception($e);
  }

  if ($i->rowCount == 0) {
    return $r->setOk(204, 'No tags found');
  }
    
  $r->setOk(200, 'Tags found');

  $df = new folksoDisplayFactory();
  $disp = $df->simpleTagList('xml');
  $r->t($disp->startform());
  while ($i->result->fetch_object()) {
    $link = new folksoTagLink($row->tagnorm);
    $r->t($disp->line(htmlspecialchars($row->id),
                      htmlspecialchars($row->tagnorm),
                      htmlspecialchars($link->getLink()),
                      htmlspecialchars($row->tagdisplay),
                      htmlspecialchars($row->cnt)));
  }
  $r->t($disp->endform());
  return $r;
}