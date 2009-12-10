<?php
  /**
   * 
   *
   * @package Folkso
   * @subpackage
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   */
require_once 'folksoUser.php';
  /**
   * @package Folkso
   */
class folksoOIuser extends folksoUser {

  public $loginId;

  /**
   * @param folksoDBconnect $dbc
   */
  public function __construct (folksoDBconnect $dbc) {
    $this->dbc = $dbc;
  }


  /**
   * @param $url
   */
  public function validateLoginId ($loginId = null) {
    $url = $loginId ? $loginId : $this->loginId;
    if ((strlen($url) > 12) &&
        preg_match('/^https?:\/\/[a-z]+/',
                   $url)){
      return true;
    }
    return false;   
  }

  

  /**
   * @param $id The id that we want to check
   * @return Boolean  true if a user with that id exists, false otherwise
   */
  public function exists ($id) {
     if ($this->validateLoginId($id) === false) {
       return false; // should we warn?
     }

     $i = new folksoDBinteract($this->dbc);
     if ($i->db_error()) {
       trigger_error("Database connection error: " .  $i->error_info(), 
                     E_USER_ERROR);
       return false;
     }

     $i->query("select userid from oi_users "
               . " where oid_url = '" . $i->dbescape($id) . "'");

     if ($i->result_status == 'OK') {
       return true;
     }
     return false;
   }

  
/**
 * @param $id
 */
 public function userFromLogin ($id) {
   $z = $this->userFromLogin_base($id, 'oi_users', 'oid_url');
   return $z;
 }


/**
 * @param 
 */
 public function writeNewUser () {
   if (! $this->Writeable()){
     throw new Exception('User object is not writeable, cannot write to DB');
   }

   if ($this->exists($this->loginId)) {
     throw new Exception('User already exists, cannot be created');
   }

   $i = new folksoDBinteract($this->dbc);
   if ($i->db_error()) {
     throw new Exception('DB connect error: ' . $i->error_info());
   }

   $i->sp_query(
                sprintf("call create_user('%s', '%s', '%s', '%s', '%s', 0, '%s', '%s', '%s')",
                        $i->dbescape($this->nick),
                        $i->dbescape($this->firstName),
                        $i->dbescape($this->lastName),
                        $i->dbescape($this->email),
                        $i->dbescape($this->loginId),
                        $i->dbescape($this->institution),
                        $i->dbescape($this->pays),
                        $i->dbescape($this->fonction)));
   if ($i->result_status == 'DBERR') {
     throw new Exception('DB query error on create user: ' . $i->error_info());
   }
 }
}
?>