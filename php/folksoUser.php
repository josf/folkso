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
  public $oidUrl;
  public $institution;
  public $pays;
  public $fonction;
  

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
   public function validEmail($arg){
     if ((strpos($arg, '@') === false) ||
         (strlen($arg) < 8)) {
       return false;
     }
   }

  public $dbc;
  private $required_fields = array('nick', 'email', 'firstname', 'lastname', 'oid_url');
  private $allowed_fields = array('nick', 'email', 'firstname', 'lastname', 'oid_url',
                                  'institution', 'pays', 'fonction');

  public function __construct (folksoDBconnect $dbc) {
    $this->dbc = $dbc;
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
          empty($this->nick)) {
       return false;
     }

     if ($this->validNick() === false) {
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
   public function createUser ($params) {
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
     $this->setOidUrl($params['oid_url']);
     $this->setInstitution($params['institution']);
     $this->setPays($params['pays']);
     $this->setFonction($params['fonction']);
     $this->setEmail($params['email']);

     $this->Writeable();
     return array(true);
   }
}  
?>