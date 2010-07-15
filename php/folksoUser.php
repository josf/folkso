<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009-2010 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */
require_once('folksoTags.php');
require_once('folksoRights.php');
/**
 * @package Folkso
 */

class folksoUser {

  /**
   * String to identify user. Format should be: nick-1234-000
   */
  public $userid;

  /**
   * User's screen name.
   */
  public $nick;

  /**
   * Email address.
   */
  public $email;
  public $lastName;
  public $firstName;
  public $institution;
  public $pays;
  public $fonction;
  public $uid;
  public $loginId;
  public $cv;
  public $dbc;
  public $eventCount;

  /** 
   * A folksoRightStore object
   */
  public $rights;

  private $required_fields = array('email', 'firstname', 'lastname', 'loginid');
  private $allowed_fields = array('email', 'firstname', 'lastname', 'userid', 'loginid', 'institution', 'pays', 'fonction');


  public function __construct (folksoDBconnect $dbc) {
    $this->dbc = $dbc;
    $this->rights = new folksoRightStore();
  }

  /**
   * Will throw an exception on malformed urlbases
   */
  public function setUrlbase($ubase) {
    $ubase = strtolower($ubase);
    if ($this->validUrlbase($ubase)) {
      $this->urlBase = $ubase;
    }
    else {
      throw new badUrlbaseException("Bad urlbase: " . $ubase);
    }
  }

  /**
   * urlbase must be lowercase, all letters and numbers, and at least 5
   * characters long. Periods are allowed.
   */
  public function validUrlbase($ubase = null){
    $ubase = $ubase ? $ubase : $this->urlBase;
    if (preg_match('/^[.a-z0-9]{5,}$/',
                   $ubase) === 0){
      return false;
    }
    else {
      return true;
    }
  }

  public function setFirstName($first){
    $this->firstName = trim($first);
  }

  public function setLastName($arg) {
    $this->lastName = trim($arg);
  }
  public function setOidUrl($arg) {
    $this->oidUrl = trim($arg);
  }
   

  /**
   * @param $id Facebook id
   */
  public function setFbId ($id) {
    $this->fbId = $id;
  }

  public function setNick($arg) {
    $this->nick = trim($arg);
  }
    
  public function setInstitution($arg) {
    $this->institution = trim($arg);
  }
  public function setPays($arg) {
    $this->pays = trim($arg);
  }
  public function setFonction($arg) {
    $this->fonction = trim($arg);
  }
  public function setEmail($arg) {
    $this->email = trim($arg);
  }
  public function validEmail($arg = null){
    $em = $arg ? $arg : $this->email;
    if ((strpos($em, '@') === false) ||
        (strlen($em) < 8)) {
      return false;
    }
    return true;
  }
  public function setCv($html) {
    $this->cv = trim($html);
  }

  public function setEventCount($number) {
    if ($number && 
        (! is_numeric($number))) {
      throw new userException("Bad data for event count");
    } 
    $this->eventCount = $number;
  }

  /**
   * @param $id
   */
   public function setLoginId ($id) {
     if ((! is_string($id)) && (! is_numeric($id))) {
       throw new userException('Internal error, bad data for setLoginId');
     }

     if ($this->validateLoginId(trim($id))) {
       $this->loginId = trim($id);
     }
     else {
       throw new userException('Invalid login id, user creation will fail');
     }
   }

/**
 * Bogus function right now to preserve functionality when not
 * initialized as a subclass.
 *
 *  @param $id 
 */
 public function validateLoginId ($id) {
   return true;
 }

  /**
   * this function exists in folksoSession too. They should be identical.
   *
   * @param $uid
   */
  public function validateUid ($uid) {
    if ((strlen($uid) > 11) &&
        (strlen($uid) < 100) &&
        preg_match('/^[a-z]+-\d+-\d+/',  $uid)){
      return true;
    }
    return false;
  }

  /**
   * @param $uid String
   */
  public function setUid ($uid) {
    $uid = trim($uid);
    if (is_string($uid) &&
        $this->validateUid($uid)){
      $this->userid = $uid;
      return $this->userid;
    }   
    else {
      throw new badUseridException('Could not set userid with the bad data we got');
    }
  }


  /**
   * @param $params
   *
   * @return Array with one or two values: first is either a user
   * object or false in case of error, the second is a message
   * (typically an error message).final
   */
  public function loadUser ($params) {
    /** urlBase is necessary for user to be writeable, but we may need
        to generate it after the user object is loaded, for instance
        when we are not loading from the db but actually creating the
        user for the first time.**/
    if (isset($params['urlbase'])) {
      $this->setUrlbase($params['urlbase']);
    }
    if (isset($params['userid'])) {
      $this->setUid($params['userid']);
    }

    if (isset($params['loginid'])) {
      $this->setLoginId($params['loginid']);
    }

    $this->setFirstName($params['firstname']);
    $this->setLastName($params['lastname']);

    $this->setInstitution($params['institution']);
    $this->setPays($params['pays']);
    $this->setFonction($params['fonction']);
    $this->setEmail($params['email']);
    $this->setCv($params['cv']);
    $this->setEventCount($params['eventCount']);

    return array($this);
  }


  public function userFromUserId ($uid) {
    if ($this->validateUid($uid) === false) {
      throw new userException("Invalid user id");
    }

    $i = new folksoDBinteract($this->dbc);
    $sql = 
      "select u.userid as userid, u.urlbase as urlbase, ud.lastname, ud.firstname, "
      . " ud.email, ud.institution, ud.pays, ud.fonction, ud.cv, "
      .' count(te.resource_id) as eventCount '
      . ' from users u '
      . ' join user_data ud on ud.userid = u.userid '
      . ' left join tagevent te on te.userid = u.userid '
      . " where u.userid = '" . $i->dbescape($uid) . "'";
    
    $i->query($sql);
    switch ($i->result_status) {
    case 'NOROWS': // this may be worthless because of left join, see below.
      print "User not found";
      return false;
      break;
    case 'OK':
      $res = $i->result->fetch_object();
      if (! $res->userid) { 
        // because of left join, one row of nulls may be returned
        // so we test for userid anyway
        return false;
      }

      $this->loadUser(array(
                            'userid' => $res->userid,
                            'urlbase' => $res->urlbase,
                            'firstname' => $res->firstname,
                            'lastname' => $res->lastname,
                            'email' => $res->email,
                            'userid' => $res->userid,
                            'institution' => $res->institution,
                            'pays' => $res->pays,
                            'fonction' => $res->fonction,
                            'cv' => $res->cv,
                            'eventCount' => $res->eventCount));
    }
    return $this;
  }

  public function userFromLogin_base ($id, $view, $login_column, 
                                      $service = null, $right = null) {
   if ($this->validateLoginId($id) === false) {
     return false; // exception ? warning ?
   }

   $i = new folksoDBinteract($this->dbc);
   $sql = "select "
     ." userid, urlbase, last_visit, lastname, firstname, email, "
     ." institution, pays, fonction, cv "
     ." from "
     ." $view "
     ." where $login_column = '" . $i->dbescape($id) . "'";

   if ($service && $right){
     $sql = "select "
       ." v.userid, urlbase, last_visit, lastname, firstname, email, institution, "
       ." pays, fonction, cv, ur.rightid "
       ." from "
       ." $view v "
       ." left join users_rights ur on ur.userid = v.userid "
       ." left join rights rs on rs.rightid = ur.rightid "
       ." where v.$login_column = '" . $i->dbescape($id) . "' "
       ." and "
       ." service = '" . $i->dbescape($service) . "'"
       ." and "
       ." rs.rightid = '" . $i->dbescape($right) . "'";
   }

   $i->query($sql);

   switch ($i->result_status) {
   case 'NOROWS':
     print "User not found";
     return false;
     break;
   case 'OK':
     $res = $i->result->fetch_object();
     $this->loadUser(array('loginid' => $id,
                           'userid' => $res->userid,
                           'urlbase' => $res->urlbase,
                           'firstname' => $res->firstname,
                           'lastname' => $res->lastname,
                           'email' => $res->email,
                           'userid' => $res->userid,
                           'institution' => $res->institution,
                           'pays' => $res->pays,
                           'fonction' => $res->fonction,
                           'cv' => $res->cv));
     if ($service &&
         $right && 
         ($res->rightid == $right)) {
       $this->rights->addRight(new folksoRight($service, $res->rightid));
     }
   }
   return $this;
  }




  /**
   * Slight different from the subclass versions of exists(). Takes
   * userid as argument rather than login id.
   *
   * @param $id userid
   */
   public function exists ($id = null) {
     $id = $id ? $id : $this->userid;
     if ($this->validateUid($id) === false) {
       return false; // should we warn?
     }

     $i = new folksoDBinteract($this->dbc);
     $i->query("select userid  "
               . " from users"
               . " where userid = '" . $i->dbescape($id) . "' " 
               ." limit 1 ");
     if ($i->rowCount == 1) {
       return true;
     }
     return false;
   }
  
   /**
    * Delete user and all associated tags. This is permanent.
    */
   public function deleteUserWithTags () {
     $i = new folksoDBinteract($this->dbc);
     $i->query("call delete_user_with_tags('" . $i->dbescape($this->userid) . "')");
   }

   /**
    * Stores current user to DB. User must already exist in
    * database and should be created through one of the subclasses.
    */
   public function storeUserData () {
     $i = new folksoDBinteract($this->dbc);
     if (! $this->exists($this->userid)) {
         throw new userException("User does not already exist, must be created first.");
     }

     // check if user already has entry in user_data. Probably does,
     // but we still might need to insert rather than update.
     $i->query("select userid from user_data where userid = '"
               . $i->dbescape($this->userid) . "'");

     if ($i->result_status == 'NOROWS') {
       // add userid field only for inserts
       $reqFields['userid'] = $this->userid;
       $sql =
         sprintf(
                 ' insert into user_data ('
                 .' userid, firstname, lastname, nick, email, '
                 .' institution, pays, fonction, cv '
                 .") values "
                 ."('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                 $i->dbescape($this->userid), 
                 $i->dbescape($this->firstName),
                 $i->dbescape($this->lastName),
                 $i->dbescape($this->nick),
                 $i->dbescape($this->email),
                 $i->dbescape($this->institution),
                 $i->dbescape($this->pays),
                 $i->dbescape($this->fonction),
                 $i->dbescape($this->cv)
                 );
     }
     else {
       $sql = ' update user_data set ';
       $sql .=
         sprintf("firstname = '%s', lastname = '%s', nick = '%s', "
                 . " email = '%s', institution = '%s', pays = '%s', fonction = '%s', "
                 . " cv = '%s' ",
                 $i->dbescape($this->firstName),
                 $i->dbescape($this->lastName),
                 $i->dbescape($this->nick),
                 $i->dbescape($this->email),
                 $i->dbescape($this->institution),
                 $i->dbescape($this->pays),
                 $i->dbescape($this->fonction),
                 $i->dbescape($this->cv));

       $sql .= " where userid = '" . $i->dbescape($this->userid) . "'";
     }
     $i->query($sql);
   }

/**
 * @param 
 */
 public function loadAllRights () {
   $i = new folksoDBinteract($this->dbc);
   $i->query('select ur.rightid, r.service '
             .' from users_rights ur '
             .' join rights r on r.rightid = ur.rightid '
             ." where userid = '" . $i->dbescape($this->userid) . "' ");

    while ($row = $i->result->fetch_object()){
      if (! $this->rights->checkRight($row->service, $row->rightid)) {
        $this->rights->addRight(new folksoRight($row->service,
                                                $row->rightid));
      }
    }
 }


  /**
   * @param $service String
   * @param $right String
   */
  public function checkUserRight ($service, $right) {
    if ($this->rights->checkRight($service, $right)){
      return true;
    }
    else {
      $this->loadAllRights();
      if ($this->rights->checkRight($service, $right)) {
        return true;
      }
    }
    return false;
  }
}

