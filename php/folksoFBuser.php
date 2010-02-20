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
       $row = $i->result->fetch_object();
       $this->setUid($row->userid);
       return true;
     }
     return false;
   }

/**
 * @param $id
 */
  public function userFromLogin ($id, $service = null, $right = null) {
    if($this->userFromLogin_base($id, 'fb_users', 'fb_uid', $service, $null)) {
     return $this;
   }
   else {
     return false;
   }
 }



/**
 * writes new user to DB. Should only be used for new users. Do not
 * use for existing users, which will throw exceptions.
 */
 public function writeNewUser () {
    if (! $this->Writeable()){
     throw new userException('User object is not writeable, cannot write to DB');
   }

   if ($this->exists($this->loginId)) {
     throw new userException('User already exists, cannot be created');
   }

   $i = new folksoDBinteract($this->dbc);
   if ($i->db_error()) {
     throw new dbConnectionException($i->error_info());
   }

   $i->sp_query(
                sprintf("call create_user("
                        ."'%s', '', %d, '%s', '%s', '', %d, '%s', '%s')",
                        $i->dbescape($this->urlBase),
                        $i->dbescape($this->loginId), //that is: facebook id, here
                        $i->dbescape($this->firstName),
                        $i->dbescape($this->lastName),
                        $i->dbescape($this->email),
                        $i->dbescape($this->institution),
                        $i->dbescape($this->pays),
                        $i->dbescape($this->fonction)));

   if ($i->result_status == 'DBERR') {
     throw new dbQueryException($i->db->errno,
                                $i->latest_query 
                                . ' DB query error on create FB user: ',
                                $i->db->error);
   }
 }



 /**
  * Fill in firstname and lastname using name provided by FB.
  * Separates on last space in string.
  *
  * @param $name String FB name as returned by API call
  * @param $overwrite Bool If not null, replace current values
  */
 public function useFBname ($name, $overwrite = null) {
    if (strlen($name) < 5){
      throw new Exception('Bad or insufficient FB name given as input to userFBname()');
    }
    if (strpos($name, ' ')) {
      $last = substr($name, strrpos($name, ' ') + 1);
      $first = substr($name, 0, strrpos($name, ' '));
    }
    else {
      $last = $name;
    }

    if ($overwrite) {
      $this->setFirstName($first);
      $this->setLastName($last);
    }
    else {
      if (! $this->firstName) {
        $this->setFirstName($first);
      }
      if (! $this->lastName){
        $this->setLastName($last);
      }
    }
  }
 

}
?>