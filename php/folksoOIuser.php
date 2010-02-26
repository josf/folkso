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
require_once 'folksoRights.php';
  /**
   * @package Folkso
   */
class folksoOIuser extends folksoUser {

  public $loginId;
  public $rights;

  /**
   * @param folksoDBconnect $dbc
   */
  public function __construct (folksoDBconnect $dbc) {
    $this->dbc = $dbc;
    $this->rights = new folksoRightStore();
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
  public function userFromLogin ($id, $service = null, $right = null) {
    $z = $this->userFromLogin_base($id, 'oi_users', 'oid_url', $service, $right);
   return $z;
 }


/**
 * @param 
 */
 public function writeNewUser () {
   if (! $this->urlBase) {
     $this->urlBase = $this->autoUrlbase($this->loginId);
   }

   if (! $this->Writeable()){
     throw new userException('User object is not writeable, cannot write to DB');
   }

   if ($this->exists($this->loginId)) {
     throw new userException('User already exists, cannot be created');
   }

   $i = new folksoDBinteract($this->dbc);
   if ($i->db_error()) {
     throw new dbConnectionException($i->db->errno,
                                     $i->latest_query,
                                     $i->db->error);
   }

   $i->sp_query(
                sprintf("call create_user('%s', '%s', 0, '%s', '%s', '%s', '%s', '%s', '%s')",
                        $i->dbescape($this->urlBase),
                        $i->dbescape($this->loginId),
                        $i->dbescape($this->firstName),
                        $i->dbescape($this->lastName),
                        $i->dbescape($this->email),
                        $i->dbescape($this->institution),
                        $i->dbescape($this->pays),
                        $i->dbescape($this->fonction)));

   if ($i->result_status == 'DBERR') {
     throw new dbQueryException($i->db->errno,
                                $i->latest_query,
                                'create_user problem: ' . $i->db->error);
   }
 }

 /**
  * Extract a user id from an openid url. Returned string will be no
  * more than 50 characters long and consist of all alphanumerics.
  *
  * @param $oid_url String (Optional) The url
  * @return String
  */
 public function autoUrlbase ($oid_url = null) {
   $oid_url = $oid_url ? $oid_url : $this->loginId;
   
   /* case 1: yahoo style, personal part is after slash,
    * http://yahoo.com/blah/342Z3sd.cqufd */
   if (preg_match("{^http://[^/]+/([^/]{5,})$}", $oid_url, $matches)) {
     $base = $matches[1];
   }
   /* case 2: myopenid style, personal part as subdomain
    * http://yourself.myopenid.com
    */
   elseif (preg_match("{http://([^.]+)}",
                      $oid_url, $matching)) {
     $base = $matching[1];
   }
   else {
     /*
      * Unknown pattern, we take twenty letters and call it a night
      */
     preg_match("{^https?://(.*)$}", $oid_url, $matcheesmo);
     $base = $matcheesmo[1];
   }
   $base = preg_replace("/[^a-z0-9]/", '', $base);
   if (strlen($base) > 50) {
     $base = substr($base, -50);
   }
   return $base;
 }

}
?>