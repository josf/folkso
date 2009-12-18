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
require_once('folksoUserQuery.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponder('get',
                                         array('required' => array('getmytags')),
                                         'getMyTags'));
                                               
$srv->Respond();

/**
 * Just a list of tags
 */
function getMyTags (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks){
  $r = new folksoResponse();
  $u = $fks->userSession();
  if (! $u instanceof folksoUser) {
    if (! $q->is_param('uid')){
      return $r->unAuthorized($u); // add message about logging in?
    }
    else {
      $userid = $q->get_param('uid');
    }
  }
  $userid = $userid ? $userid : $u->userid;

  try {
    $i = new folksoDBinteract($dbc);
    $sql = 
      sprintf(
              '  select t.tagnorm, t.id, t.tagdisplay, count(te.tag_id) as cnt, tagtime'
              .' from tag t '
              .' join tagevent te on t.id = te.tag_id '
              ." where te.userid = '%s' "
              .' group by t.tagnorm '
              .' order by tagtime '
              .' limit 50',
              $i->dbescape($userid));
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
  if ($q->content_type() == 'json') {
    $disp = $df->json(array('resid', 'tagnorm', 'link', 'tagdisplay', 'count'));
  }
  else {
    $disp = $df->simpleTagList('xml');
  }

  $r->t($disp->startform());
  while ($row = $i->result->fetch_object()) {
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

/**
 *
 * 
 */
function getUserResByTag (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();

  try {
    $u = $fks->userSession(null);
    if ((! $u instanceof folksoUser) &&
        (! $q->is_param('user'))){
      return $r->setError(404, 'No user');
    }
    elseif ($q->is_param('user')) { 
      $u = new folksoUser($dbc); // we create a user object anyway
      $u->setUid($q->get_param('user'));
    } 

  /* if the uid is bad, the error will be caught. if the user does not
     exist, results will just be empty. Note that we consider this
     information to be public: we are not checking the identity of the
     user */

    $i = new folksoDBinteract($dbc);
    $uq = new folksoUserQuery();
    $sql = $uq->resourcesByTag($q->tag, $u->userid);
    $i->query($sql);

    /* these are inside the try block because exists() hits the DB */
    if ($i->rowCount == 0) {
      if (isset($u->nick) ||
          ($u->exists)) {
        return $r->setOk(204, 'User has no resources with this tag');
      }
      else {
        return $r->setError(404, 'Unknown user');
      }
    }
  }
  catch (dbException $e){
    return $r->handleDBexception($e);
  }
  catch (badUseridException $e) {
    return $r->handleDBexception($e);
  }

  $r->setOk(200, 'Found');
  $df = new folksoDisplayFactory();
  $dd = $df->ResourceList('xml');
  $r->t($dd->startform());
  while ($row = $i->result->fetch_object()) {
    $r->t($dd->line($row->id,
                    htmlspecialchars($row->uri_raw),
                    htmlspecialchars($row->title)
                    ));
  }
  $r->t($dd->endform());
  return $r;
}




