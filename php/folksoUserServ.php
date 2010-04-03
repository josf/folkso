<?php
  /**
   * Web service for accessing users tags and information.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009-2010 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */


require_once('folksoTags.php');
require_once('folksoUrl.php');
require_once('folksoSession.php');
require_once('folksoUser.php');
require_once('folksoUserQuery.php');
require_once('folksoFabula.php');

/** facebook related stuff **/
require_once('folksoFBuser.php');
require_once('facebook.php');



/**
 * Just a list of tags. Need to add offset parameters.
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
        (! $q->is_param('uid'))){
      return $r->setError(404, 'No user');
    }
    elseif ($q->is_param('uid')) { 
      $u = new folksoUser($dbc); // we create a user object anyway
      $u->setUid($q->get_param('uid'));
      if (! $u->exists($q->get_param('uid'))) {
        return $r->setError(404, 'Missing or invalid user');
      }
    } 

    $i = new folksoDBinteract($dbc);
    $uq = new folksoUserQuery();
    $sql = $uq->resourcesByTag($q->tag, $u->userid);
    $i->query($sql);

    /* these are inside the try block because exists() hits the DB */
    if ($i->rowCount == 0) {
      if (isset($u->nick) ||
          ($u->exists())) {
        return $r->setOk(204, 'User has no resources with this tag');
      }
      else { // no longer necessary
        return $r->setError(404, 'Unknown user');
      }
    }
  }
  catch (dbException $e){
    return $r->handleDBexception($e);
  }
  catch (badUseridException $e) {
    return $r->handleDBexception($e); // TODO: update this with new class
  }

  $r->setOk(200, 'Found');
  $df = new folksoDisplayFactory();
  if ($q->content_type() == 'json') {
    $dd = new folksoDataJson('resid', 'url', 'title');
  }
  else {
    $dd = $df->ResourceList('xml');
  }

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

/**
 * Mostly returns its own status depending on whether the user exists or not.
 */
function checkFBuserId (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  
  /* check for well formed FB uid */
  $fbu = new folksoFBuser($dbc);
  if (! $fbu->validateLoginId($q->get_param('fbuid'))) {
    return $r->setError(406, "Malformed or impossible Facebook uid",
                        htmlspecialchars($q->get_param('fbuid')) 
                        . " is not a valid Facebook user id.");
  }

  if ($fbu->exists($q->get_param('fbuid'))) {
      $r->setOk(200, "User found");
      $r->t('The Facebook id that you supplied corresponds to a valid user');
      return $r;
  }
  else {
    return $r->setError(404, "User not found",
                        'The Facebook id that you supplied does not correspond '
                        .' to a valid account');

  }
}

/**
 * Logs in an existing Facebook user or creates a new account. 
 */
function loginFBuser (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();

  /* already logged in? go about your business */
  if ($fks->status()) {
    $r->setOk(200, "Session already valid");
    $r->body("You are already logged in");
    return $r;
  }


  $loc = new folksoFabula();
  $fb = new Facebook($loc->snippets['facebookApiKey'],
                     $loc->snippets['facebookSecret']);
  $fbu = new folksoFBuser($dbc);

  $fb_uid = $fb->get_loggedin_user();

  if (! $fb_uid) {
    return $r->setError(400, "Insufficient information",
                 'Unable to obtain necessary login information');

  }
  else {

    /** user already known **/
    if ($fbu->exists($fb_uid)) {
      $fks = new folksoSession($dbc);
      $u = $fbu->userFromLogin($fb_uid);
      try {
        $fks->startSession($u->userid);
        $r->setOk(200, "User found, session started");
        $r->t('You are in.');
        return $r;
      }
      catch (userException $e) {
        return $r->setError(500, 'Internal user or session related error',
                            'An error occurred. Sorry. We will get right on it.');
      }
    }
    else { /* Create new user */
      
      /* get name from Facebook */
      $name = $fb->api_client->users_getInfo($fb_uid, 'last_name, first_name');
      //      $fbu->useFBname($name, true); // true = make sure we overwrite
      $fbu->setFirstName($name[0]['first_name']);
      $fbu->setLastName($name[0]['last_name']);
      $fbu->urlbaseFromFBname();

      try {
        $fbu->setLoginId($fb_uid);
        $fbu->writeNewUser();
      }
      catch (userException $e) {
        return $r->setError(500, 'Error creating new user');
      }
      catch (dbException $e) {
        return $r->handleDBexception($e);
      }

      $fbu2 = new folksoFBuser($dbc);
      $u2 = $fbu2->userFromLogin($fb_uid);
      
      if (! $u2 instanceof folksoFBuser) {
        return $r->setError(500, 'Strange error creating new account',
                            'New user not found.');
      }
      // TODO: correct format for user url
      $xml = sprintf('<?xml version="1.0"?>'
                     .'<user>'
                     .'<facebookid>%d</facebookid>'
                     .'<firstname>%s</firstname>'
                     .'<lastname>%s</lastname>'
                     .'<url>%s</url>'
                     .'</user>',
                     $u2->loginId, $u2->firstName, $u2->lastName, $u2->urlBase);
      $r->setOk(201, 'User successfully created');
      $r->t($xml);
      $fks->startSession($u2->userid);
      return $r;
    }
  }
}


function userSubscriptions (folksoQuery $q, 
                            folksoDBconnect $dbc, 
                            folksoSession $fks) {  

  $r = new folksoResponse();
  $u = $fks->userSession();

  if (! $u instanceof folksoUser) {
    return $r->unAuthorized($u);
  }

  try {
    $i = new folksoDBinteract($dbc);
    $sql = sprintf(
                   'select t.tagnorm, t.id, t.tagdisplay '
                   .' from tag t '
                   .' join user_subscription us on us.tag_id = t.id '
                   ." where us.userid = '%s'",
                   $i->dbescape($u->userid));
    $i->query($sql);
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }

  if ($i->result_status == 'NOROWS') {
    return $r->setOk(204, 'No subscribed tags');
  }

  $df = new folksoDisplayFactory();
  $dd = $df->simpleTagList();
  $dd->activate_style('xml');
  $r->setType('xml');
  $r->t($dd->startform());

  while ($row = $i->result->fetch_object()) {
    $link = new folksoTagLink($row->tagnorm);
    $r->t($dd->line($row->id,
                    $row->tagnorm,
                    htmlspecialchars($link->getLink()),
                    htmlspecialchars($row->tagdisplay),
                    '')
          );
  }
  $r->t($dd->endform());
  $r->setOk(200, 'Subscribed tags found');
  return $r;

}

function addSubscription (folksoQuery $q, 
                            folksoDBconnect $dbc, 
                            folksoSession $fks) {  

  $r = new folksoResponse();
  $u = $fks->userSession();

  if (! $u instanceof folksoUser) {
    return $r->unAuthorized($u);
  }

  try {
    $i = new folksoDBinteract($dbc);
    $uq = new folksoUserQuery();
    $i->query($uq->addSubscriptionSQL($q->tag, $u->userid));
  }
  catch (dbException $e) {
    if ($e instanceof dbQueryException) {
      if ($e->sqlcode == 1048) {
        return $r->setError(404, "Tag not found", "Tag '" 
                            . $q->tag 
                            . "' does not exist yet");
      }
      elseif ($e->sqlcode == 1062) {
        return $r->setOk(204, "Already subscribed");
      }
      else {
        return $r->handleDBexception($e);
      }
    }
  }



  $r->setOk(200, "Subscribed");
  try {
    $i->query($uq->singleTagRepresentation($q->tag));
  }
  catch( dbException $e) {
    return $r->handleDBexception($e);
  }

  $row = $i->result->fetch_object();
  $r->t('<?xml version="1.0"?>');
  $link = new folksoTagLink($row->tagnorm);
  $r->t(sprintf("<tag>\n\t<numid>%s</numid>\n\t<tagnorm>%s</tagnorm>\n\t"
                ."<link>%s</link>\n\t<display>%s</display></tag>",
                $row->id,
                htmlspecialchars($row->tagnorm),
                htmlspecialchars($link->getLink()),
                htmlspecialchars($row->display, ENT_NOQUOTES, 'UTF-8')
                )
        );
  return $r;
}


function removeSubscription (folksoQuery $q, 
                            folksoDBconnect $dbc, 
                            folksoSession $fks) {  

  $r = new folksoResponse();
  $u = $fks->userSession();

  if (! $u instanceof folksoUser) {
    return $r->unAuthorized($u);
  }

  /* First we get data about the tag so that we can send the tag data
     back to the client. */
  try {
    $i = new folksoDBinteract($dbc);
    $uq = new folksoUserQuery();
    $i->query($uq->singleTagRepresentation($i->dbescape($q->tag)));
  }
  catch( dbException $e) {
    return $r->handleDBexception($e);
  }
  if ($i->result_status == 'NOROWS') {
    return $r->setError(404, "Tag not found", 
                        "The tag your subscribed to may not exist anymore");
  }

  $row = $i->result->fetch_object();
  $link = new folksoTagLink($row->tagnorm);
  $taginfo = sprintf('<?xml version="1.0"?>'
                     ."\n<deletedSubscription><tag>\n<numid>%s</numid>"
                     ."\n<tagnorm>%s</tagnorm>\n"
                     ."<link>%s</link>\n\t<display>%s</display></tag>"
                     ."</deletedSubscription>",
                     $row->id,
                     htmlspecialchars($row->tagnorm),
                     htmlspecialchars($link->getLink()),
                     htmlspecialchars($row->display, ENT_NOQUOTES, 'UTF-8')
                     );


  $tag_id = $row->id;
 
  try {
    $i->query(sprintf("delete from user_subscription "
                      ." where userid = '%s'"
                      .' and '
                      ." tag_id = %d",
                      $u->userid,
                      $tag_id));
  }
  catch( dbException $e) {
    return $r->handleDBexception($e);
  }

  $r->setOk(200, 'Unsubscribed');
  $r->setType('xml');
  $r->t($taginfo);
  return $r;
}
            
/**
 * Returns a list of resources corresponding to the most recent tag events
 * using tags that the user has subscribed to.
 */

function recentlyTagged  (folksoQuery $q, 
                          folksoDBconnect $dbc, 
                          folksoSession $fks) {  

  $r = new folksoResponse();
  $u = $fks->userSession();

  if (! $u instanceof folksoUser) {
    return $r->unAuthorized($u);
  }

  /* First we get data about the tag so that we can send the tag data
     back to the client. */
  try {
    $i = new folksoDBinteract($dbc);
    $i->query(sprintf(
                      "select res.id, res.uri_raw, res.title, count(res.id) as weight
from resource res
join 
(select resource_id as red
from tagevent te
where te.tag_id in
(select tag_id
from user_subscription us
where us.userid = '%s')) as inside 
on inside.red = res.id
join tagevent tev on tev.resource_id = res.id
group by res.id
order by tev.tagtime, weight desc
limit 50",

                      $i->dbescape($u->userid)));
  }
  catch(dbException $e) {
    return $r->handleDBexception($e);
  }

  if ($i->result_status == 'NOROWS'){
    return $r->setOk(204, 'No resources tagged yet');
  }
  $r->setOk(200, 'Resources found');
  $r->setType('xml');

  $df = new folksoDisplayFactory();
  $rl = $df->ResourceList('xml');
  $r->t($rl->startform());
  
  while ($row = $i->result->fetch_object()) {
    $r->t($rl->line(
                    $row->id,
                    htmlspecialchars($row->url),
                    htmlspecialchars($row->title)
                    )
          );
  }
  $r->t($rl->endform());
  return $r;
}


function storeUserData (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {  

  $r = new folksoResponse();
  $u = $fks->userSession();

  if (! $u instanceof folksoUser) {
    return $r->unAuthorized($u);
  }


  $fields = array('firstname' => true, 
                  'lastname' => true, 
                  'nick' => false, 
                  'email' => false, 
                  'institution' => false, 
                  'pays' => false, 
                  'fonction' => false);

  /* First we get data about the tag so that we can send the tag data
     back to the client. */

    $sql = '';

    $reqFields = array();

    try {
      $i = new folksoDBinteract($dbc);
    }
    catch(dbException $e) {
      return $r->handleDBexception($e);
    }


    foreach ($fields as $fieldName => $isRequired) {
      if ($q->is_param('set' . $fieldName)) {
        $reqFields[$fieldName] = $i->dbescape($q->get_param('set' . $fieldName));
      }
      elseif ($isRequired) {
        return $r->setError(400, "Insufficient data", 
                            "Firstname and lastname are required");
      }
      else {
        $reqFields[$fieldName] = 'NULL';
      }
    }


    // check if user already has entry in user_data. Probably does,
    // but we still might need to insert rather than update.
    try {
      $i->query("select userid from user_data where userid = '"
                . $i->dbescape($u->userid) . "'");
    }
    catch(dbException $e) {
      return $r->handleDBexception($e);
    }

    if ($i->result_status == 'NOROWS') {

      // add userid field only for inserts
      $reqFields['userid'] = $u->userid;
      $sql .=
        ' insert into user_data ('
        . implode(', ', array_keys($reqFields))
        .") values ('"
        . implode("', '", array_values($values))
        ."')";
    }
    else {
      $sql .= ' update user_data set ';

      $parts = array();
      foreach ($reqFields as $k => $v) {
        $parts[] = sprintf("%s = '%s'",
                           $k, $v);
      }
      $sql .= implode(', ', $parts);
      $sql .= " where userid = '" . $u->userid . "'";
    }

    try {
      $i->query($sql);
    }
    catch(dbException $e) {
      return $r->handleDBexception($e);
    }
    
    try {
      $i->query('select userid, firstname, lastname, nick, email, '
                .' institution, pays, fonction '
                .' from user_data '
                ." where userid = '" . $u->userid . "'");
    }
    catch(dbException $e) {
      return $r->handleDBexception($e);
    }

    $r->setOk(200, 'User data stored');

    // return xml representation of userdata
    $df = new folksoDisplayFactory();
    $ud = $df->userData();
    $r->t($ud->startform());
    
    $row = $i->result->fetch_object();
    $r->t($ud->line(
                    $r->userid,
                    htmlspecialchars($row->firstname),
                    htmlspecialchars($row->lastname),
                    htmlspecialchars($row->nick),
                    htmlspecialchars($row->email),
                    htmlspecialchars($row->institution),
                    htmlspecialchars($row->pays),
                    htmlspecialchars($row->fonction)
                    ));
    $r->t($ud->endform());
    $r->setType('xml');
    return $r;
}

