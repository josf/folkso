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
   * Boolean. Whether or not we have enough information to write user
   * data to the DB.
   */
  public $writeable;

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
     $this->nick = $nick;
     if (strlen($this->nick) < 5) {
       $this->notWriteable();
     }
   }

   public function setFirstName($first){
     $this->firstName = $first;
     if (! $this->firstName) {
       $this->notWriteable();
     }
   }

   public function setLastName($arg) {
          $this->lastName = $arg;
          if (strlen($this->lastName) < 2){
            $this->notWriteable();
          }
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
     if ((strpos($arg, '@') === false) ||
         (strlen($arg) < 8)) {
       $this->notWriteable();
       return;
     }
     $this->email = $arg;
   }

  public $dbc;
  private $required_fields = array('nick', 'email', 'firstname', 'lastname', 'oid_url');
  private $allowed_fields = array('nick', 'email', 'firstname', 'lastname', 'oid_url',
                                  'institution', 'pays', 'fonction');

  public function __construct (folksoDBconnect $dbc) {
    $this->dbc = $dbc;
  }


  /**
   * Set writable state to false
   */
   public function notWriteable () {
     $this->writeable = false;
   }
  
   public function Writeable() {
     $this->writeable = true;
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