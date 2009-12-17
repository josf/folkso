<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
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
  public $dbc;

  /** 
   * A folksoRightStore object
   */
  public $rights;

  private $required_fields = array('nick', 'email', 'firstname', 'lastname', 'loginid');
  private $allowed_fields = array('nick', 'email', 'firstname', 'lastname', 'userid', 'loginid', 'institution', 'pays', 'fonction');


  public function __construct (folksoDBconnect $dbc) {
    $this->dbc = $dbc;
    $this->rights = new folksoRightStore();
  }

/**
 * @param $id
 */
 public function initializeUser ($id) {
   if ($this->Writeable === false) {
     return false;
   }
   $id = $id ? $id : $this->loginId;
   
   if (substr($id, 0, 7) == 'http://') {
     
   }
 }

  public function setNick($nick) {
    $this->nick = trim(strtolower($nick));
  }

  /**
   * Nick must be lowercase, all letters and numbers, and at least 5
   * characters long.
   */
  public function validNick($nick = null){
    $nick = $nick ? $nick : $this->nick;
    if (preg_match('/^[a-z0-9]{5,}/',
                   $nick) === 0){
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


  /**
   * @param $id
   */
   public function setLoginId ($id) {
     if ($this->validateLoginId(trim($id))) {
       $this->loginId = trim($id);
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
    if ($this->validateUid($uid)){
      $this->userid = $uid;
      return $this->userid;
    }   
    else {
      throw new badUseridException('Could not set userid with the bad data we got');
    }
  }

  /**
   * Tests to see if User object contains enough data to be
   * used. Requires valid nick, first and last names and (possibly) valid
   * email address.
   */  
  public function Writeable() {
    /** check presence of vars **/
    if ( empty($this->firstName) ||
         empty($this->lastName) ||
         empty($this->email) ||
         empty($this->nick)  ||
         empty($this->loginId)){
      return false;
    }

    if ($this->validNick() === false) {
      return false;
    }
    if ($this->validEmail() === false){
      return false;
    }
    return true;
  }

  /**
   * @param $params
   *
   * @return Array with one or two values: first is either a user
   * object or false in case of error, the second is a message
   * (typically an error message).final
   */
  public function loadUser ($params) {
    $this->setNick($params['nick']);
    $this->setFirstName($params['firstname']);
    $this->setLastName($params['lastname']);
    $this->setLoginId($params['loginid']);
    $this->setInstitution($params['institution']);
    $this->setPays($params['pays']);
    $this->setFonction($params['fonction']);
    $this->setEmail($params['email']);
    if ($params['userid']) {
      $this->setUid($params['userid']);
    }

    $this->Writeable();
    return array(true);
  }




  public function userFromLogin_base ($id, $view, $login_column, 
                                      $service = null, $right = null) {
   if ($this->validateLoginId($id) === false) {
     return false; // exception ? warning ?
   }

   $i = new folksoDBinteract($this->dbc);
   $sql = "select "
     ." userid, last_visit, lastname, firstname, nick, email, institution, pays, fonction "
     ." from "
     ." $view "
     ." where $login_column = '" . $i->dbescape($id) . "'";

   if ($service && $right){
     $sql = "select "
       ." v.userid, last_visit, lastname, firstname, nick, email, institution, pays, fonction, ur.rightid "
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
     $this->loadUser(array('nick' => $res->nick,
                           'loginid' => $id,
                           'userid' => $res->userid,
                           'firstname' => $res->firstname,
                           'lastname' => $res->lastname,
                           'email' => $res->email,
                           'userid' => $res->userid,
                           'institution' => $res->institution,
                           'pays' => $res->pays,
                           'fonction' => $res->fonction));
     if ($service &&
         $right && 
         ($res->rightid == $right)) {
       $this->rights->addRight(new folksoRight($service, $res->rightid));
     }
   }
   return $this;
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
   * @param $right
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
  }
}

