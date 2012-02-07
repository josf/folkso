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
require_once('folksoOIuser.php');
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
  $userid = '';

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
 * Logs in an existing Facebook user. Sends 404 if user does not exist.
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
  $faceHelp = new folksoFacebookHelper($loc);
  try {
    $faceHelp->init();
  }
  catch (FacebookApiException $e) {
    return $r->setError(500, "Facbook API error", 
                        "Somemthing strange happened. This might be somebody else's fault.");
  }



  $fbu = new folksoFBuser($dbc);

  if (! $faceHelp->uid()) {
    return $r->setError(400, "Insufficient information",
                 'Unable to obtain necessary login information');
  }
  else {

    /** user already known **/
    if ($fbu->exists($faceHelp->uid())) {
      $fks = new folksoSession($dbc);
      $u = $fbu->userFromLogin($faceHelp->uid());
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
    else { /* user does not exist */
      return $r->setError(404, 'User unknown', 'We need a user. '
#ifdef DEBUG
                          . ' FB uid = ' . $fb_uid
#endif

                          );
    }
  }
}


/*
 * Creates new user account and logs the new user in. There is no
 * access control on this method but the only hack possible would be
 * to create an account for somebody else, which would only be
 * accessible to that person.
 */
function createUser (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {

  $r = new folksoResponse();
  $loc = new folksoFabula();
  $u = new folksoUser($dbc);

  $u->setLoginId($q->get_param('setloginid'));

  // load available fields from parameters
  $fields = array('loginid'     => 'setLoginId',
                  'firstname'   => 'setFirstName',
                  'lastname'    => 'setLastName',
                  'nick'        => 'setNick',
                  'email'       => 'setEmail',
                  'institution' => 'setInstitution',
                  'pays'        => 'setPays',
                  'fonction'    => 'setFonction',
                  'cv'          => 'setCv');

  foreach ($fields as $param => $meth) {
    if ($q->is_param('set' . $param)) {
      call_user_func(array($u, $meth), $q->get_param('set' . $param));
    }
  }
    
  try {
    $u->writeNewUser($q->get_param('service'));
  }
  catch (unknownServiceException $usE) {
    return $r->setError(400,
                        'Unknown or missing identification provider',
                        'Bizarre...');
  }
  catch (userException $ue) {
    return $r->setError(500, 
                        'Error creating new user', 
                        'Error: ' . $ue->getMessage());
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }


  try {
    /**
     * By reloading the user, not only we check that the user was
     * properly created, but we get the new userid which we cannot
     * know before the user is created in the database.
     */
    $u2 = new folksoUser($dbc);
    $u2->userFromLogin($u->loginId);
  }
  catch (unknownUserException $uuE) {
    return $r->setError(500,
                        "Echec de la création de l'utilisateur",
                        "Erreur : l'utilisateur n'a pas pu être créé.");
  }

      
  // TODO: correct format for user url
  $xml = sprintf('<?xml version="1.0"?>'
                 .'<user>'
                 .'<identifier>%s</identifier>'
                 .'<firstname>%s</firstname>'
                 .'<lastname>%s</lastname>'
                 .'<userid>%s</userid>'
                 .'</user>',
                 htmlspecialchars($u->loginId), // note that this is in the original $u
                 htmlspecialchars($u2->firstName), 
                 htmlspecialchars($u2->lastName), 
                 htmlspecialchars($u2->userid)
                 );
  $r->setOk(201, 'User successfully created');
  $r->t($xml);
  $fks->startSession($u2->userid);
  return $r;
}


/* Function not currently necessary. Not exposed in API. */
function loginOidUser (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $oid = $q->get_param('oid');
  $o = new folksoOIUser($dbc);

  try {
    if (! $o->userFromLogin($oid)) {
      return $r->setError(404, "Unknown user",
                          "This user does not exist in our database. You must first ".
                          " create an account");
    }

    $fks->startSession($o, $q->is_param('debug') ? true : false);
    $r->setOk(200, "User found, session started");
    $r->t('Welcome back');
    return $r;
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }
}

/* Function not currently necessary. Not exposed in API */
function createOidUser (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $oid = $q->get_param('oid');
  $o = new folksoOIuser($dbc);
  
  try {
    if ($o->userFromLogin($oid)) {
      $r->setOk(200, 'User already exists');
      $r->t('A user account is already associated with this OpenId');
      return $r;
    }
  
    $o->setLoginId($oid);
    $o->writeNewUser();
    if ($o->userFromLogin($oid)) {
      $r->setOk(201, 'User created');
      $r->t('The user account was created for ' . $o->loginId . '.');
      $fks->startSession($o, $q->is_param('debug') ? true : false);
      return $r;
    }
    else {
      return $r->setError(500, "Strange error", 
                          "User was created and then disappeared");
    }
  }
  catch(dbException $e) {
    return $r->handleDBexception($e);
  }
  catch(userException $e) {
    return $r->setError(400, "Cannot complete request",
                        "The user information provided is incomplete or invalid. "
                        . " The user account could not be created." 
                        . $o->loginId);
  }
}

function userSubscriptions (folksoQuery $q, 
                            folksoDBconnect $dbc, 
                            folksoSession $fks) {  

  $r = new folksoResponse();
  $u = $fks->userSession();

  /*  A Zuckerberg moment: I decide that subscriptions are public data 
      if (! $u instanceof folksoUser) {
    return $r->unAuthorized($u);
    }*/

  if ($q->is_param('uid')) {
    $userId = $q->get_param('uid');
  }
  elseif ($u instanceof folksoUser) {
    $userId = $u->userid;
  }
  else {
    return $r->setError(404, "User not identified", 
                        "No information was supplied to identify the user. "
                        . " Try logging in first.");
  }

  try {
    $i = new folksoDBinteract($dbc);
    $sql = sprintf(
                   'select t.tagnorm, t.id, t.tagdisplay '
                   .' from tag t '
                   .' join user_subscription us on us.tag_id = t.id '
                   ." where us.userid = '%s'",
                   $i->dbescape($userId));
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
                htmlspecialchars($row->tagdisplay, ENT_NOQUOTES, 'UTF-8')
                )
        );
  $r->setType("xml");
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
                     htmlspecialchars($row->tagdisplay, ENT_NOQUOTES, 'UTF-8')
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
                    htmlspecialchars($row->uri_raw),
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
  $i = new folksoDBinteract($dbc);

  if (! $u instanceof folksoUser) {
    return $r->unAuthorized($u);
  }

  $u->loadAllRights();
  $fields = array('firstname' => 'setFirstName',
                  'lastname' => 'setLastName',
                  'nick' => 'setNick',
                  'email' => 'setEmail',
                  'institution' => 'setInstitution',
                  'pays' => 'setPays',
                  'fonction' => 'setFonction',
                  'cv' => 'setCv');

  foreach ($fields as $param => $meth) {
    if ($q->is_param('set' . $param)) {
      call_user_func(array($u, $meth), $q->get_param('set' . $param));
    }
  }

  try {
    $u->storeUserData();

    // retreive stored data for return representation
    $u2 = new folksoUser($dbc);
    $u2->userFromUserId($u->userid);
  }
  catch(dbException $e) {
    return $r->handleDBexception($e);
  }

  $r->setOk(200, 'User data stored');

  $u2->loadAllRights();

  // return xml representation of userdata
  $df = new folksoDisplayFactory();
  $ud = $df->userData();
  $r->t($ud->startform());

  require_once('HTMLPurifier.auto.php');
  $pure = new HTMLPurifier();
    
  $r->t($ud->line(
                  $u2->userid,
                  htmlspecialchars(excludeSQLnullKeyWord($u2->firstName)),
                  htmlspecialchars(excludeSQLnullKeyWord($u2->lastName)),
                  htmlspecialchars(excludeSQLnullKeyWord($u2->nick)),
                  htmlspecialchars(excludeSQLnullKeyWord($u2->email)),
                  htmlspecialchars(excludeSQLnullKeyWord($u2->institution)),
                  htmlspecialchars(excludeSQLnullKeyWord($u2->pays)),
                  htmlspecialchars(excludeSQLnullKeyWord($u2->fonction)),
                  $pure->purify(excludeSQLnullKeyWord($u2->getCv($u2->rights->maxRight()))),
                  $u2->eventCount,
                  excludeSQLnullKeyWord($u2->nameUrl()),
                  ''
                  ));
  $r->t($ud->endform());
  $r->setType('xml');
  return $r;
}


/**
 * Helper function for userData functions
 */

function excludeSQLnullKeyWord ($txt) {
  if ($txt == 'NULL') {
    return '';
  }
  return $txt;
}


function getUserData (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $u = $fks->userSession(); //actual user
  $user = new folksoUser($dbc); // the user we are getting information for

  // use uid parameter for $user
  if ($q->is_param('uid')) {
    try {
      if (! $user->validateUid($q->get_param('uid'))) {
        return $r->setError(400, 'Malformed user id',
                            'Check your data, something is wrong.');
      }
      elseif (! $user->userFromUserId($q->get_param('uid'))) {
        return $r->setError(404, 'User not found',
                              'This user does not appear to exist');
      }
    }
    catch(dbException $e) {
      return $r->handleDBexception($e);
    }
  }
    // otherwise use session user
  elseif  ($u instanceof folksoUser) {
    $user->userFromUserId($u->userid);
  }
  else {
    return $r->setError(400, 'No valid user specified', 
                        'Cannot complete request without a user');
  }

  if (! $user->hasData()) {
    return $r->setOk(204, 'No data for this user');
  }

  $r->setOk(200, 'User data found');




  // get the rights of the user whose data we are getting. (For CV filtering
  // and possibly to be included in the response.)
  $user->loadAllRights();

  // if this is called in a session, we might want to know the user's rights but
  // the general public does not need to know. $rightString is the string that 
  // will be sent back in the request, if necessary. We need to know $user's rights
  // anyway to know how to filter his CV (links and images or not).
  $rightString = '';
  if ($u instanceof folksoUser) {
    $u->loadAllRights();
    if (($u->userid == $user->userid) ||
        $u->checkRight('folkso', 'admin')) {
      $rightString = $user->rights->maxRight();
    }
  }

  // return xml representation of userdata
  $df = new folksoDisplayFactory();
  $ud = $df->userData();
  $r->t($ud->startform());

  // leaving this here to get meaningful 
  // error in case of problem.
  require_once('HTMLPurifier.auto.php');
  $pure = new HTMLPurifier();

  $r->t($ud->line(
                  $user->userid,
                  htmlspecialchars(excludeSQLnullKeyWord($user->firstName)),
                  htmlspecialchars(excludeSQLnullKeyWord($user->lastName)),
                  htmlspecialchars(excludeSQLnullKeyWord($user->nick)),
                  htmlspecialchars(excludeSQLnullKeyWord($user->email)),
                  htmlspecialchars(excludeSQLnullKeyWord($user->institution)),
                  htmlspecialchars(excludeSQLnullKeyWord($user->pays)),
                  htmlspecialchars(excludeSQLnullKeyWord($user->fonction)),
                  $pure->purify(excludeSQLnullKeyWord($user->getCv($user->rights->maxRight()))),
                  $user->eventCount,
                  excludeSQLnullKeyWord($user->nameUrl()),
                  $rightString
                  ));

  

  $r->t($ud->endform());
  $r->setType('xml');
  return $r;
}

function getFavoriteTags (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $emptyU = new folksoUser($dbc);

  // Will contain the uid of the user whose tags we are getting.
  $userid = '';

  // $userid is either a parameter...
  if ($q->is_param('uid')) {
    if ($emptyU->validateUid($q->get_param('uid'))) {
      $userid = $q->get_param('uid');
    }
    else {
      return $r->setError(400, "Malformed user id", "This userid cannot be read");
    }
  }
  // ... or can be obtained through the calling user.
  else {
    $u = $fks->userSession();
    if ($u instanceof folksoUser) {
      $userid = $u->userid;
    }
  }
  
  if ((! $userid) ||
      (strlen($userid) < 5)) {
    return $r->setError(400, "No user specified", 
                        "You must either log in or set the uid parameter");
  }
  elseif (! $emptyU->validateUid($userid)) {
    return $r->setError(400, "Malformed user id",
                        "The userid supplied cannot be read");
  }
  
  try {
    if (! $emptyU->exists($userid)) {
      return $r->setError(404, "Unknown user",
                          "Check your data, this user does not appear to exist");
    }

    $i = new folksoDBinteract($dbc);
    $sql = 
      sprintf( ' select '
               .' count(te.resource_id) as eventCount, '
               .' te.tag_id as tag_id, t.tagdisplay as tagdisplay, t.tagnorm as tagnorm '
               .' from tagevent te '
               .' join tag t on t.id = te.tag_id '
               ." where te.userid = '%s' "
               .' group by tag_id '
               .' order by eventCount desc '
               .' limit 20 ',
               $i->dbescape($userid));
#ifdef DEBUG
    $r->deb($sql);
#endif
    $i->query($sql);
    
    if ($i->rowCount == 0) {
      return $r->setOk(204, "No tags found");
    }
      
    $r->setOk(200, "Tags found");

    $df = new folksoDisplayFactory();
    if ($q->content_type() == 'json') {
      $disp = $df->json(array('tag_id', 'tagnorm', 'link', 'tagdisplay', 'count'));
      $r->setType('json');
    }
    else {
      $disp = $df->simpleTagList('xml');
      $r->setType('xml');
    }

    $r->t($disp->startform());
    while ($row = $i->result->fetch_object()) {
      $link = new folksoTagLink($row->tagnorm);
      $r->t($disp->line(htmlspecialchars($row->tag_id),
                        htmlspecialchars($row->tagnorm),
                        htmlspecialchars($link->getLink()),
                        htmlspecialchars($row->tagdisplay),
                        htmlspecialchars($row->eventCount)));
    }
    $r->t($disp->endform());
    return $r;


  }
  catch(dbException $e) {
    return $r->handleDBexception($e);
  }
}

class folksoFacebookHelper {
  public $fb;
  private $uid;
  private $session;

  function __construct (folksoFabula $loc) {
    $this->fb = new Facebook(
                             array('appId' => $loc->snippets['facebookApiKey'], 
                                   'secret' => $loc->snippets['facebookSecret'],
                                   'cookie' => true));
  }

  /**
   * should be called inside a try block, catching FacebookApiException
   */ 
  function init () {
    if (! $this->session) {
      $this->session = $this->fb->getSession();
    }
    $this->uid = $this->fb->getUser();
  }

  function uid () {
    if (! $this->session) {
      throw new folksoFacebookException("folksoFacebookHelper does not have a valid session. "
                                        ." init() method should have been called first");
    }
    return $this->uid;
  }

  /**
   * A wrapper for init. To be used when we might not have a session.
   */
  function sessionCheck () {
    try {
      $this->init();
      if ($this->session) {
        return true;
      }
      else {
        return false;
      }
    }
    catch (FacebookApiException $e) {
      return false;
    }
  }
}