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
        preg_match('/^http:\/\/[a-z]+/',
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
   if($this->userFromLogin_base($id, 'oi_users', 'oid_url')) {
     return $this;
   }
   else {
     return false;
   }
 }


}
?>