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
      . ' ud.pays, ud.fonction ' 
      . ' from users u '
      . ' join user_data ud on ud.userid = u.userid '
      . ' where ';

    $column_equivs 
      = array('default:' => array('ud.lastname','ud.firstname'),
              'lname:' => 'ud.lastname',
              'fname:' => 'ud.firstname',
              'uid:' => 'u.userid');

    $sql .= $sq->whereClause($req, $column_equivs, $i);
    $r->deb($sql);
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
                    $newU->rights->xmlRights()  // careful here, no containing xml element in template                
                    ));
  //
  }
  $r->t($ud->endform());
  return $r;
}