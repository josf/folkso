<?php
  /**
   * 
   *
   * @package Folkso
   * @subpackage tagserv
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   */
require_once 'folksoUser.php';
require_once 'folksoDBconnect.php';
require_once 'folksoDBinteract.php';
require_once 'folksoFabula.php';
require_once 'folksoException.php';

  /**
   * @package Folkso
   */
class folksoSession {

  public $sessionId;
  public $dbc;
  private $loc;

  /**
   * When a user object is retreived, it is cached here. Subsequent
   * calls to userSession will simply return the cached version.
   */
  public $user;

  /**
   * @param $dbc folksoDBconnect 
   */
  function __construct(folksoDBconnect $dbc){
    $this->dbc = $dbc;
    $this->loc = new folksoFabula();
  }


/**
 *
 * Makes sure a uid respects the basic format, which is:
 *
 * At least 5 letters, a hyphen, a group of numbers a hyphen, a second
 * group of numbers.
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
 *
 * @param $session_id String (actually should be a hash...)
 */
 public function validateSid ($session_id) {
   if ((strlen($session_id) == 64) &&
       preg_match('/^[a-z0-9]+$/', $session_id)) {
     return true;
   }
   return false;
 }

/**
 * This does not initialize anything, since we might want to wait
 * until the data is actually needed before starting a DB connection
 * etc.
 *
 * @param $sid Session ID 
 */
 public function setSid ($sid) {
   if (! $this->validateSid($sid)) {
     throw new badSidException(htmlspecialchars($sid) . ' is not a valid session id');
   }
   else {
     $this->sessionId = $sid;
   }
 }



  /**
   * Starts session and sets cookie.
   * 
   * @param $uid String
   */
  public function startSession ($uid) {
    if ($this->validateUid($uid) === false) {
      throw new Exception('Bad userid');
    }

    $i = new folksoDBinteract($this->dbc);
    if ($i->db_error()) {
      trigger_error("Database connection error: " .  $i->error_info(), 
                    E_USER_ERROR);
    }    
    $sess = $this->newSessionId();
    $i->query(
              'insert into sessions '
              .' (token, userid) '
              ." values ('"
              . $i->dbescape($sess) . "', '"
              . $i->dbescape($uid) . "')"
              );
    if ($i->result_status == 'DBERR'){
      //      print $i->error_info();
      return false; // exception, errror ?
    }
    setcookie('folksosess', $this->sessionId, 
              time() + 1800, '/', 
              $this->loc->web_domain);
    return $this->sessionId;
  }
      
  /**
   * Erases any current session id from current object (but not from DB).
   */
  public function newSessionId () {
    $this->sessionId = hash('sha256', time() . 'OldSalts');
    return $this->sessionId;
  }

/**
 * Verifies that session is present.
 *
 * @param $session_id
 */
  public function checkSession ($session_id) {
    $i = new folksoDBinteract($this->dbc);
    if ($i->db_error()) {
      trigger_error("Database connection error: " .  $i->error_info(), 
                   E_USER_ERROR);
   }    
   if ($this->validateSid($session_id) === false){
     trigger_error("Bad session id", E_USER_WARNING);
   }

   $i->query("select userid, started from sessions where token = '"
             . $i->dbescape($session_id) . "'  and started > now() - 1209600");
   if( $i->result_status == 'OK') {
     return true;
   }
   elseif ($i->result_status == 'NOROWS'){
     return false;
   }
   else {
     trigger_error("DB query error: " . $i->error_info(),
                   E_USER_ERROR);
   }
 }

/**
 * @param $session_id (optional)
 */
 public function killSession ($session_id = null) {
   $sid = $session_id ? $session_id : $this->sessionId;
   if ($this->validateSid($sid)){
   $i = new folksoDBinteract($this->dbc);
   if ($i->db_error()){
     trigger_error("Database connection error: " . $i->error_info(),
                   E_USER_ERROR);
   }
   $i->query( "delete from sessions where token = '" . $i->dbescape($sid) . "'");
   }
   else {
     trigger_error('invalid session id', E_USER_WARNING);
   }
 }

 /**
  * Load user data from session id (cookie). Retuns folksoUser
  * obj. Caches the fkUser object. We might consider a "force reload"
  * option if there were a reason for it. This also means that if the
  * arguments (sid) change, the data returned will not. This should
  * not be a problem though.
  *
  * @param $sid Session ID.
  */
  public function userSession ($sid) {
    if ($this->user instanceof folksoUser) {
      return $this->user;
    }

    $sid = $sid ? $sid : $this->sessionId;
    if ($this->validateSid($sid) === false) {
      return false;  // exception?
    }
    
   $i = new folksoDBinteract($this->dbc);
   if ($i->db_error()){
     trigger_error("Database connection error: " . $i->error_info(),
                   E_USER_ERROR);
   }
   
   $i->query('select u.nick as nick, u.firstname as firstname, '
             .'  u.lastname as lastname, u.email as email, u.userid  as userid'
             .' from sessions s '
             .' join users u on u.userid = s.userid '
             ." where s.token = '" . $sid . "'"
             ." and s.started > now() - 1209600 ");
   if ($i->result_status == 'OK') {
     $u = new folksoUser($this->dbc);
     $res = $i->result->fetch_object();
     $u->loadUser(array(
                          'nick' => $res->nick,
                          'firstname' => $res->firstname,
                          'lastname' => $res->lastname,
                          'email' => $res->email,
                          'userid' => $res->userid
                          ));
     return $u;
   }
   else {
     return false;
   }
  }
 
  /**
   * Simple wrapper around userSession, when all we actually need is the userid
   */
  public function getUserId ($sid = null) {
    $u = $this->userSession($sid);
    return $u->userid;
  }
       
       
}
?>