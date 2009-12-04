<?php
  /**
   * 
   *
   * @package Folkso
   * @subpackage
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   */
require_once('folksoUser.php');
  /**
   * @package Folkso
   */
class folksoFBuser extends folksoUser{
  public $loginId;

/**
 * @param folksoDBconnect $dbc
 */
 public function __construct (folksoDBconnect $dbc) {
   $this->dbc = $dbc;
 }


  /**
   * @param $id FB id to verify
   */
  public function validateLoginId ($loginId = null) {
    $id_to_check = $loginId ? $loginId : $this->loginId;
    if (strlen($id_to_check) > 5){
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

     $i->query("select userid  "
               . " from fb_users"
               . " where fb_uid = " . $i->dbescape($id) );

     if ($i->result_status == 'OK') {
       return true;
     }
     elseif ($i->result_status == 'DBERR'){
       trigger_error('query error ' . $i->error_info(),
                     E_USER_ERROR);
     }
     return false;
   }

/**
 * @param $id
 */
 public function userFromLogin ($id) {
   if($this->userFromLogin_base($id, 'fb_users', 'fb_uid')) {
     return $this;
   }
   else {
     return false;
   }
 }
  

}
?>