<?php
  /**
   * 
   *
   * @package Folkso
   * @subpackage
   * @author Joseph Fahey
   * @copyright 2009-2011 Gnu Public Licence (GPL)
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
    if (is_numeric($id_to_check) &&
        ($id_to_check > 100000)) {
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
    if($this->userFromLogin_base($id, 'fb_users', 'fb_uid', $service, $right)) {
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
   if (! $this->validUrlbase($this->urlBase)){
     if ($this->firstName && $this->lastName) {
       /* Currently, this condition should never be true since we are
        * using the FB id instead of the name. Leaving it here in case
        * we switch back to getting the FB name on account creation.
        */
       $this->urlbaseFromFBname();
     }
     if (! $this->validUrlbase($this->urlBase)) {
       $this->urlBaseFromLoginId($this->loginId);
     }
   }
   

    if (! $this->Writeable()){
      /** This probably means that we did not get a valid urlbase **/
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

   if ($i->result_status == 'DBERR')
       {
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

    $first = ''; $last = '';
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
 

 /**
  * By default, overwrites $this->urlBase, so make sure that is what
  * you want.
  *
  * NB: We have hardcoded the locale here, via setlocale(). If there
  * are problems with accented characters, this may need to be
  * changed.
  * 
  * @uses firstName
  * @uses lastName
  * @uses urlBase
  * @param $name String Optional Facebook name string
  * @param $overwrite Optional default true. False if you just want a string returned
  */
 public function urlbaseFromFBname ($name = null, $overwrite = true) {
    if ($name) {
      $this->useFBname($name, true);
    }
    elseif (! ($this->firstName || $this->lastName)) {
      throw new userException('No information to construct base url');
    }

    if ((strlen($this->firstName) > 1) && (strlen($this->lastName) > 1)) {
      $base = $this->firstName . '.' . $this->lastName;
    }
    else {
      $base = $this->firstName . $this->lastName;  // one of these is empty
    }
    $base = strtolower($base);
    $base = str_replace(' ', '', $base);
    setlocale(LC_CTYPE, 'en_US.utf8');

    //    $base = mb_convert_encoding($base, 'UTF-8', "auto");
    $base = iconv('UTF-8', 'ASCII//TRANSLIT', $base);
    if (strlen($base) > 4) {
      if ($overwrite) {
        $this->urlBase = $base; 
      }
      return $base;
    }
    else {
      throw userException('urlbase is too short');
    }
  }

/**
 * Simply checks that the login id looks valid (ie. is all numbers and
 * at least 5 characters long) and sets it, otherwise throws
 * exception. This will overwrite the current value of $u->urlBase so
 * make sure that is what you want.
 *
 * @param $fbId
 * @uses $urlBase
 * @uses $loginId
 */
 public function urlBaseFromLoginId ($fbId = null) {
   $id = $fbId ? $fbId : $this->loginId;
   if ((! preg_match('/^[0-9]+$/', $id)) &&
       (strlen($id) > 5)) {
     throw new userException('Invalid FB id');
   }
   $this->setUrlbase($id);
   return $id;
 }

 

}
