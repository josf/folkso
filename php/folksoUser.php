<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */
require_once('folksoTags.php');
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

  private $required_fields = array('nick', 'email', 'firstname', 'lastname', 'loginid');
  private $allowed_fields = array('nick', 'email', 'firstname', 'lastname', 'userid', 'loginid', 'institution', 'pays', 'fonction');


  public function __construct (folksoDBconnect $dbc) {
    $this->dbc = $dbc;
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
    $this->nick = strtolower($nick);
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
    $this->firstName = $first;
  }

  public function setLastName($arg) {
    $this->lastName = $arg;
  }
  public function setOidUrl($arg) {
    $this->oidUrl = $arg;
  }
   

  /**
   * @param $id Facebook id
   */
  public function setFbId ($id) {
    $this->fbId = $id;
  }
    
  public function setInstitution($arg) {
    $this->institution = $arg;
  }
  public function setPays($arg) {
    $this->pays = $arg;
  }
  public function setFonction($arg) {
    $this->fonction = $arg;
  }
  public function setEmail($arg) {
    $this->email = $arg;
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
     if ($this->validateLoginId($id)) {
       $this->loginId = $id;
     }
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
    if ($this->validateUid($uid)){
      $this->uid = $uid;
    }   
    return $uid;
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
    $missing = array();
    foreach ($this->required_fields as $field){
      if ((! isset($params[$field])) ||
          (empty($params[$field]))) {
        $missing[] = $field;
      }
    }
    if (count($missing) > 0) {
      return array(false, "Missing fields: " . implode(' ', $missing));
    }
     
    $this->setNick($params['nick']);
    $this->setFirstName($params['firstname']);
    $this->setLastName($params['lastname']);
    $this->setLoginId($params['loginid']);
    $this->setInstitution($params['institution']);
    $this->setPays($params['pays']);
    $this->setFonction($params['fonction']);
    $this->setEmail($params['email']);
    $this->setUid($params['userid']);

    $this->Writeable();
    return array(true);
  }




  public function userFromLogin_base ($id, $view, $login_column) {
   if ($this->validateLoginId($id) === false) {
     return false; // exception ? warning ?
   }
   
   $i = new folksoDBinteract($this->dbc);
   if ($i->db_error()) {
     trigger_error("Database connection error: " .  $i->error_info(), 
                   E_USER_ERROR);
   }

   $i->query("select "
             ." userid, last_visit, lastname, firstname, nick, email, institution, pays, fonction "
             ." from "
             ." $view "
             ." where $login_column = '" . $i->dbescape($id) . "'");

   switch ($i->result_status) {
   case 'DBERR':
     trigger_error('database query error ' . $i->error_info(),
                   E_USER_ERROR);
     return false;
     break;
   case 'NOROWS':
     print "User not found";
     return false;
     break;
   case 'OK':
     $res = $i->result->fetch_object();
     $this->loadUser(array('nick' => $res->nick,
                           'firstname' => $res->firstname,
                           'lastname' => $res->lastname,
                           'email' => $res->email,
                           'userid' => $res->userid,
                           'institution' => $res->institution,
                           'pays' => $res->pays,
                           'fonction' => $res->fonction));
   }
   return $this;
  }


  /**
   * @param $right
   */
  public function checkUserRight ($right) {
    $i = new folksoDBinteract($this->dbc);
    if ($i->db_error()) {
      trigger_error("Database connection error: " .  $i->error_info(), 
                    E_USER_ERROR);
    }    
    if ($this->validateRight($right) === false) {
      return false;
    }

    $i->query('select rightid '
              .' from users_rights '
              ." where userid = '" . $this->uid . "' "
              ." and "
              ." rightid = '" . $right . "'");

    if ($i->result_status == 'OK') {
      return true;
    }
    elseif ($i->result_status == 'DBERR') {
      trigger_error("DB error on right check: " . $i->error_info(),
                    E_USER_WARNING);
      return false;
    }
    return false;
  }

  /**
   * @param $right String
   */
  public function validateRight ($right) {
    if (is_string($right) && 
        (strlen($right) > 2) &&
        preg_match('/^[a-z_]+$/', $right)) {
      return true;
    }
    return false;
  }


}  
?>