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

  /**
   * @package Folkso
   */
class folksoSession {

  public $sessionId;
  public $dbc;
  public $loc;

  /**
   * @param $dbc folksoDBconnect 
   */
  function __construct(folksoDBconnect $dbc){
    $this->dbc = $dbc;
    $this->loc = new folksoFabula();
  }


/**
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
   if (strlen($session_id) == 64){
     return true;
   }
   return false;
 }


  /**
   * @param $uid String
   */
  public function startSession ($uid) {
    if ($this->validateUid($uid) === false) {
      return false; // error
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
      print $i->error_info();
      return false; // exception, errror ?
    }
    setcookie('folksosess', $this->sessionId, 
              time() + 1800, '/', 
              $this->loc->web_domain);
    return $this->sessionId;
  }
      
  /**
   * Erases any current session id.
   */
  public function newSessionId () {
    $this->sessionId = hash('sha256', time() . 'OldSalts');
    return $this->sessionId;
  }

/**
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
  * Load user data from session id (cookie). Retuns folksoUser obj
  *
  * @param $sid
  */
  public function userSession ($sid) {
    $sid = $sid ? $sid : $this->sessionId;
    if ($this->validateSid($sid) === false) {
      return false;  // excepmtion
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
     print "---" . $res->nick . "---";
     $u->createUser(array(
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
 
       
       
}
?>