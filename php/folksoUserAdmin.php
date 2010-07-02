<?php


/**
 *
 * @package Folkso
 * @author Joseph Fahey
 * @copyright 2010 Gnu Public Licence (GPL)
 * @subpackage Tagserv
 */


require_once('folksoTags.php');
require_once('folksoSession.php');
require_once('folksoSearch.php');


/**
 * @param query
 */
function getUsersByQuery (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  $u = $fks->userSession(null, 'folkso', 'admin');
  if (! $u instanceof folksoUser) {
    return $r->insufficientPrivileges();
  }

  /* parse search query */
  $kw = new folksoSearchKeyWordSetUserAdmin();
  $sq = new folksoSearchQueryParser($kw);

  $req = $sq->parseString($q->get_param('search'));
  if (! $sq->isValidTree()) {
    return $r->setError(400, "Invalid search query", 
                        "The user information search query that you tried was not valid.");
  }


  try {
    $i = new folksoDBinteract($dbc);

    $sql = ' select u.userid, u.urlbase, '
      . ' ud.firstname, ud.lastname, ud.email, ud.nick, ud.institution, '
      . ' ud.pays, ud.fonction, count(te.tag_id) as tagacts ' 
      . ' from users u '
      . ' left join user_data ud on ud.userid = u.userid '
      . ' left join tagevent te on te.userid = u.userid '
      . ' where ';

    $column_equivs 
      = array('default:' => array('ud.lastname','ud.firstname'),
              'lname:' => 'ud.lastname',
              'fname:' => 'ud.firstname',
              'uid:' => 'u.userid');

    $sql .= $sq->whereClause($req, $column_equivs, $i);
    $sql .= " group by u.userid ";

#ifdef DEBUG
    $r->deb($sql);
#endif

    $i->query($sql);
  }
  catch(dbException $e) {
    return $r->handleDBexception($e);
  }

  if ($i->result_status == 'NOROWS') {
    return $r->setOk(204, "No corresponding users found");
  }

  $r->setOk(200, "Query executed");
  $r->setType('xml');

  $df = new folksoDisplayFactory();
  $ud = $df->AdminUserView();
  
  $r->t($ud->startform());
  while ($row = $i->result->fetch_object()) {
    $uid = $row->userid;
    $newU = new folksoUser($dbc);
    $newU->loadUser(array('userid' => $row->userid,
                          'firstname' => $row->firstname,
                          'lastname' => $row->lastname,
                          'institution' => $row->institution,
                          'nick' => $row->nick,
                          'pays' => $row->pays,
                          'fonction' => $row->fonction,
                          'email' => $row->email));


    /** OK, we are still doing a select per row here, but this is just
        the admin interface, so we assume that it is low
        traffic. Other solution would be to load right data via a left
        join, concatenate it and unpack it here. That seems like a lot
        to do when we already have the methods built. (Just learned
        that Drupal installs can have hundreds of DB queries per page.)
    **/
    $newU->loadAllRights();
    $r->t($ud->line(
                    $newU->userid,
                    htmlspecialchars($newU->firstName),
                    htmlspecialchars($newU->lastName),
                    htmlspecialchars($newU->nick),
                    htmlspecialchars($newU->email),
                    htmlspecialchars($newU->institution),
                    htmlspecialchars($newU->pays),
                    htmlspecialchars($newU->fonction),
                    htmlspecialchars($row->tagacts),
                    $newU->rights->xmlRights()  // careful here, no containing xml element in template                
                    ));
  //
  }
  $r->t($ud->endform());
  return $r;
}


function newMaxRight (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  try {
    $u = $fks->userSession(null, 'folkso', 'admin');
    if (! $u instanceof folksoUser) {
      return $r->insufficientPrivileges();
    }
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }

  $user = new folksoUser($dbc); // the user we work on, not the user we are

  $right = new folksoRight('bogus', 'right');

  try {
    if (! $user->validateUid($q->get_param('user'))) {
      return $r->setError(400, "Invalid user id",
                          "The user id received is formally invalid.");
    }

    if (! $user->userFromUserId($q->get_param('user'))) {
      return $r->setError(404, "User not found",
                          "This user does not seem to exist currently");

    }

    if (! $right->validateRight($q->get_param('newright'))) {
      return $r->setError(400, "Invalid right",
                          "This right is invalid or malformed.");
    }
    $user->loadAllRights();
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }

  if ($user->rights->checkRight('folkso', $q->get_param('newright'))) {

    $rightMessage = '';

    // remove possible offending old rights...
    $user->rights->removeRightsAbove($q->get_param('newright'));
    // ...then add a the new right in case it is not present yet.
    try {
      $user->rights->addRight(new folksoRight('folkso',
                                              $q->get_param('newright')));
      $rightMessage = "New right assigned to user";
    }
    catch (rightAlreadyPresentException $e) {
      // do nothing, we are good
      $rightMessage = "Rights removed";
    }
    catch (rightInvalidException $e) {
      return $r->setError('400', 'Invalid right', 'This right was not recognized.');
    }

    try {
      $user->rights->synchDB($user);
    }
    catch (dbException $e) {
      return $r->handleDBexception($e);
    }
    $r->setOk(200, 'OK, rights removed');
    $r->t($user->rights->xmlRights(true));   // we want xml doctype
    $r->setType('xml');
    return $r;
  }
  else {  // we are upgrading the user to better rights
    $rightMessage = '';

    try {
      $user->rights->addRight(new folksoRight('folkso', $q->get_param('newright')));
      $rightMessage = "User promoted";
    }
    catch (rightException $e) {
      // do nothing (the exception means right is already there, so we are fine)
      $rightMessage = "User already had this right";
    }
    try {
      $user->rights->synchDB($user);
      $r->setOk(200, 'OK. ' . $rightMessage);
      $r->t($user->rights->xmlRights(true)); // true means we want xml doctype
      $r->setType('xml');
      return $r;
    }
    catch (dbException $e) {
      return $r->handleDBexception($e);
    }
  }
}


function deleteUserAndTags (folksoQuery $q, folksoDBconnect $dbc, folksoSession $fks) {
  $r = new folksoResponse();
  try {
    $u = $fks->userSession(null, 'folkso', 'admin');
    if (! $u instanceof folksoUser) {
      return $r->insufficientPrivileges();
    }

    $user = new folksoUser($dbc); // the user we work on, not the user we are
    if ($user->validateUid($q->get_param('user'))) {
      $victim = $q->get_param('user');
    }
    else {
      return $r->setError(400, "Malformed user id", 
                          "Something is wrong. The user parameter must be a valid "
                          . " userid.");
    }

    if (! $user->userFromId($victim)) { // returns false when user does not exist
      return $r->setError(404, "User not found", 
                          "The user you tried to delete was not found.");
    }
    $user->deleteUserWithTags();
    $r->t("User successfully deleted.");
    return $r->setOK("Deleted");
  }
  catch (dbException $e) {
    return $r->handleDBexception($e);
  }
}