<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009-2010 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */
require_once('folksoTags.php');
require_once('folksoRights.php');
require_once('HTMLPurifier.auto.php');

/**
 * @package Folkso
 */

class folksoUser {

  /**
   * String to identify user. Format should be: nick-1234-000
   */
  public $userid;

  /**
   * String provided by identity service (OAuth or OpenId). A user can
   * have multiple accounts and thus multiple identifiers, but this
   * identifier will be considered the active one.
   */
  public $identifer;

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
  public $institution;
  public $pays;
  public $fonction;
  public $uid;
  public $loginId;
  private $cv;
  public $dbc;
  public $eventCount;
  public $firstName_norm;
  public $lastName_norm;
  public $ordinality; // used for keeping track of people with the same name (see nameUrl())
  /** 
   * A folksoRightStore object
   */
  public $rights;

  private $required_fields = array('email', 'firstname', 'lastname', 'loginid');
  private $allowed_fields = array('email', 'firstname', 'lastname', 'userid', 'loginid', 'institution', 'pays', 'fonction');


  public function __construct (folksoDBconnect $dbc) {
    $this->dbc = $dbc;
    $this->rights = new folksoRightStore();
  }

  public function setFirstName($first){
    $this->firstName = trim($first);
  }

  public function setLastName($arg) {
    $this->lastName = trim($arg);
  }

  private function setFirstName_norm($fNorm) {
    $this->firstName_norm = trim($fNorm);
  }

  private function setLastName_norm($lNorm) {
    $this->lastName_norm = trim($lNorm);
  }

  public function setOidUrl($arg) {
    $this->oidUrl = trim($arg);
  }
   

  public function setNick($arg) {
    $this->nick = trim($arg);
  }
    
  public function setInstitution($arg) {
    $this->institution = trim($arg);
  }
  public function setPays($arg) {
    $this->pays = trim($arg);
  }
  public function setFonction($arg) {
    $this->fonction = trim($arg);
  }
  public function setEmail($arg) {
    $this->email = trim($arg);
  }
  public function validEmail($arg = null){
    $em = $arg ? $arg : $this->email;
    if ((strpos($em, '@') === false) ||
        (strlen($em) < 8)) {
      return false;
    }
    return true;
  }
  public function setCv($html) {
    $this->cv = trim($html);
  }

  public function setEventCount($number) {
    if ($number && 
        (! is_numeric($number))) {
      throw new userException("Bad data for event count");
    } 
    $this->eventCount = $number;
  }

  /**
   * @param $id
   */
   public function setLoginId ($id) {
     if ((strlen($id) == 0) || (strlen($id) > 255)) {
       throw new malformedIdentifierException('Internal error, bad data for setLoginId');
     }
     $this->loginId = trim($id);
   }
   /**
    * This is private because it should only be called when loading
    * from the DB. We would never want to set it ourselves or try to write it 
    * to the DB.
    */
   private function setOrdinality($ord) {
     $this->ordinality = $ord;
   }

   /**
    * By default, returns a filtered version of the CV for HTML consumption.
    *
    * Privileged users can benefit from less restrictive filtering.
    *
    * @param $userLevel String Default 'user' (other values: 'admin', 'redac')
    * @param $unfiltered Boolean TRUE for raw content, default is false.
    *
    */
   public function getCv($userLevel = 'user', $unfiltered = false) {

     if ($unfiltered) {
       return $this->cv;
     }
     else {
       $config = HTMLPurifier_Config::createDefault();
       if (($userLevel == 'admin') ||
           ($userLevel == 'redac')) {
         $config->set('HTML.Allowed', 'a[href],img[src],p,span[style],ul,ol,li,div,strong,b,i,em,sup,h1,h2,h3,h4');
         $purist = new HTMLPurifier($config);
         return $purist->purify($this->cv);
       }
       $config->set('HTML.Allowed', 'p,span[style],ul,ol,li,div,strong,b,i,em,sup,h1,h2,h3,h4');
       $pure = new HTMLPurifier($config);
       return $pure->purify($this->cv);
     }
   }

   /**
    * @return String User's personal URL (firstname.lastname) if possible
    */

   public function nameUrl() {

     // base case: we already have the data because the user has been
     // retrieved from the DB and has a name and lastname in user_data
     $ord = ($this->ordinality > 0) ? $this->ordinality : '';
     $first = $this->firstName_norm . '.' . $this->lastName_norm . $ord;
     if (strlen($first) > 3) {
       return $first;
     }

     // we have the names, but no normalized names
     // we return false because we don't want to give out data that might be wrong.
     if (strlen($this->firstName . $this->lastName) > 0) {
       return false;
     }


     // we (re)load the user, now we really know
     if (($this->userid) &&
         ($this->userFromUserId($this->userid))) {
       $ord = ($this->ordinality > 0) ? $this->ordinality : '';
       $second = $this->firstName_norm  . '.' . $this->lastName_norm . $ord;
       if (strlen($second) > 3) {
         return $second;
       }
       else {
         return false;
       }
     }


   }


/**
 * Bogus function right now to preserve functionality when not
 * initialized as a subclass.
 *
 *  @param $id 
 */
 public function validateLoginId ($id) {
   return true;
 }

  /**
   * this function exists in folksoSession too. They should be identical.
   *
   * @param $uid
   */
  public function validateUid ($uid) {
    if ((strlen($uid) > 11) &&
        (strlen($uid) < 100) &&
        preg_match('/^[a-z0-9]+-\d+-\d+/',  $uid)){
      return true;
    }
    return false;
  }

  /**
   * @param $uid String
   */
  public function setUid ($uid) {
    $uid = trim($uid);
    if (is_string($uid) &&
        $this->validateUid($uid)){
      $this->userid = $uid;
      return $this->userid;
    }   
    else {
      throw new badUseridException('Could not set userid with the bad data we got');
    }
  }

  /**
   * Tests to see if User object contains enough data to be
   * used. Requires valid nick, first and last names and (possibly) valid
   * email address.
   */  
  public function Writeable() {
    /** check presence of vars **/
    if ( empty($this->loginId)) {
      return false;
    }
    return true;
  }

/**
 * If given a 4-character argument, assumes that it is a
 * service_id. Otherwise tries to find the service_id in the database.
 *
 * @param $service Either the 4-char service_id or the provider name
 */
 public function writeNewUser ($service) {
   if ($this->exists($this->loginId)) {
     throw new userException('User already exists, cannot be created');
   }
   if (! is_string($service)) {
     throw new unknownServiceException();
   } 
   if (strlen($service) !== 4) {
     $service = $this->getServiceIdFromName($service);
   }

   $i = new folksoDBinteract($this->dbc);
   $i->sp_query(
                sprintf("call create_user('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                        $i->dbescape($this->loginId),
                        $i->dbescape($service),
                        $i->dbescape($this->firstName),
                        $i->dbescape($this->lastName),
                        $i->dbescape($this->email),
                        $i->dbescape($this->institution),
                        $i->dbescape($this->pays),
                        $i->dbescape($this->fonction)));

 }

 /**
  * @return String the four-character string for the given identity service
  */
 public function getServiceIdFromName ($name) {
   /* Ideally, this could be done in the stored procedure, but might 
    be handy to have on the application level. */

   $i = new folksoDBinteract($this->dbc);
   $i->query('select service_id '
             .' from id_services '
             .' where '
             ." provider_name = '" . $i->dbescape($name) . "' "
             .' or '
             ." fullname = '" . $i->dbescape($name) . "'");

   if ($i->result_status == 'OK') {
     $res = $i->result->fetch_object();
     return $res->service_id;
   }

   if ($i->result_status == 'NOROWS') {
     throw new unknownServiceException();
   }
 }


  /**
   * @param $params
   *
   * @return Array with one or two values: first is either a user
   * object or false in case of error, the second is a message
   * (typically an error message).final
   */
  public function loadUser ($params) {
    /** urlBase is necessary for user to be writeable, but we may need
        to generate it after the user object is loaded, for instance
        when we are not loading from the db but actually creating the
        user for the first time.**/

    if (isset($params['userid'])) {
      $this->setUid($params['userid']);
    }
    if (isset($params['loginid'])) {
      $this->setLoginId($params['loginid']);
    }

    if (isset($params['firstname'])) {
      $this->setFirstName($params['firstname']);
    }

    if (isset($params['lastname'])) {
      $this->setLastName($params['lastname']);
    }

    if (array_key_exists('institution', $params)) {
      $this->setInstitution($params['institution']);
    }

    if (array_key_exists('pays', $params)) {
      $this->setPays($params['pays']);
    } 

    if (array_key_exists('fonction', $params)) {
      $this->setFonction($params['fonction']);
    }

    if (array_key_exists('email', $params)) {
      $this->setEmail($params['email']);
    }

    if (array_key_exists('cv', $params)) {
      $this->setCv($params['cv']);
    }
    if (array_key_exists('eventCount', $params)) {
      $this->setEventCount($params['eventCount']);
    }

    if (array_key_exists('firstname_norm', $params)) {
      $this->setFirstName_norm($params['firstname_norm']);
    }

    if (array_key_exists('lastname_norm', $params)) {
      $this->setLastName_norm($params['lastname_norm']);
    }

    if (array_key_exists('ordinality', $params)) {
      $this->setOrdinality($params['ordinality']);
    }
    $this->Writeable();
    return array($this);
  }


  public function userFromUserId ($uid) {
    if ($this->validateUid($uid) === false) {
      throw new userException("Invalid user id");
    }

    $i = new folksoDBinteract($this->dbc);
    $sql = 
      "select u.userid as userid, ud.lastname, ud.firstname, "
      . " ud.email, ud.institution, ud.pays, ud.fonction, ud.cv, "
      . " ud.firstname_norm, ud.lastname_norm, ud.ordinal,"
      .' count(te.resource_id) as eventCount '
      . ' from users u '
      . ' left join user_data ud on ud.userid = u.userid '
      . ' left join tagevent te on te.userid = u.userid '
      . " where u.userid = '" . $i->dbescape($uid) . "'"
      .' group by u.userid ';
    
    $i->query($sql);
    switch ($i->result_status) {
    case 'NOROWS': // this may be worthless because of left join, see below.
      return false;
      break;
    case 'OK':
      $res = $i->result->fetch_object();
      if (! $res->userid) { 
        // because of left join, one row of nulls may be returned
        // so we test for userid anyway
        return false;
      }

      $this->loadUser(array(
                            'userid' => $res->userid,
                            'firstname' => $res->firstname,
                            'lastname' => $res->lastname,
                            'email' => $res->email,
                            'userid' => $res->userid,
                            'institution' => $res->institution,
                            'pays' => $res->pays,
                            'fonction' => $res->fonction,
                            'cv' => $res->cv,
                            'eventCount' => $res->eventCount,
                            'firstname_norm' => $res->firstname_norm,
                            'lastname_norm' => $res->lastname_norm,
                            'ordinality' => $res->ordinal));
    }
    return $this;
  }

/**
 * Load user object using user's first and last names. Calls userFromUserId().
 *
 * @param $first String First name
 * @param $last String Last name
 * @param $ord Integer ordinal, in case of homonyms
 * @throws DB errors
 * @return Self
 */
  public function userFromName ($first, $last, $ord = 0) {
    if (! is_numeric($ord)) {
      throw new userException('Bad ordinality argument in userFromName: ' . $ord);
    }

    $i = new folksoDBinteract($this->dbc);
    $sql = 
      sprintf('select u.userid from users u '
              .' join user_data ud on u.userid = ud.userid '
              .' where '
              ." ud.firstname = '%s' and ud.lastname = '%s'"
              ." and ud.ordinal = %d "
              .' limit 1',
              $i->dbescape($first),
              $i->dbescape($last),
              $i->dbescape($ord));

    $i->query($sql);
    if ($i->result_status == 'OK') {
      $row = $i->result->fetch_object();
      $uid = $row->userid;
      return $this->userFromUserId($uid);
    }
    return false;
 }

  /**
   * Given a users identity string (Facebook, Google etc.), load the
   * available user data. If no user is found, throws unknownUserException.
   *
   * @param $loginId Identification string from an identity provider (FB etc.).
   *
   */

  public function userFromLogin($loginId = null) {
    if ((! $loginId) ||
        (strlen($loginId) < 4)) {
      throw new  malformedIdentifierException();
    }

    $i = new folksoDBinteract($this->dbc);
    $sql = "select"
      ." u.userid as userid, u.last_visit, u.created,  "
      ." ud.lastname as lastname, ud.firstname as firstname, "
      ." ud.email as email, ud.institution as institution, ud.pays as pays, "
      ." ud.fonction as fonction, ud.cv as cv, ud.firstname_norm as firstname_norm, "
      ." ud.lastname_norm as lastname_norm, ud.ordinal  as ordinal "
      ." from "
      ." users u "
      ." left outer join user_data ud on u.userid = ud.userid "
      ." join user_services us on us.userid = u.userid "
      ." where us.identifier = '" . $i->dbescape($loginId) . "'";


      $i->query($sql);

      if ($i->result_status === 'NOROWS') {
        throw new unknownUserException();
      }

      $res = $i->result->fetch_object();


      $this->loadUser(array('loginid' => $loginId,
                            'userid' => $res->userid,
                            'firstname' => $res->firstname,
                            'lastname' => $res->lastname,
                            'email' => $res->email,
                            'userid' => $res->userid,
                            'institution' => $res->institution,
                            'pays' => $res->pays,
                            'fonction' => $res->fonction,
                            'cv' => $res->cv,
                            'firstname_norm' => $res->firstname_norm,
                            'lastname_norm' => $res->lastname_norm,
                            'ordinality' => $res->ordinal));
      return $this;
  }




  /**
   * Slightly different from the subclass versions of exists(). Takes
   * userid as argument rather than login id.
   *
   * @param $id userid
   */
   public function exists ($id = null) {
     $id = $id ? $id : $this->userid;
     if ($this->validateUid($id) === false) {
       return false; // should we warn?
     }

     $i = new folksoDBinteract($this->dbc);
     $i->query("select userid  "
               . " from users"
               . " where userid = '" . $i->dbescape($id) . "' " 
               ." limit 1 ");
     if ($i->rowCount == 1) {
       return true;
     }
     return false;
   }

   /**
    * Returns true if current user object has firstName and lastName
    * information.
    * 
    * @return Boolean 
    */
   public function hasData () {
     if ($this->firstName && $this->lastName) {
       return true;
     }
     return false;
   }


  
   /**
    * Delete user and all associated tags. This is permanent.
    */
   public function deleteUserWithTags () {
     $i = new folksoDBinteract($this->dbc);
     $i->query("call delete_user_with_tags('" . $i->dbescape($this->userid) . "')");
   }

   /**
    * Stores current user to DB. User must already exist in
    * database and should be created through one of the subclasses.
    */
   public function storeUserData () {
     $i = new folksoDBinteract($this->dbc);
     if (! $this->exists($this->userid)) {
         throw new userException("User does not already exist, must be created first.");
     }

     // check if user already has entry in user_data. Probably does,
     // but we still might need to insert rather than update.
     $i->query("select userid from user_data where userid = '"
               . $i->dbescape($this->userid) . "'");

     if ($i->result_status == 'NOROWS') {
       // add userid field only for inserts
       $reqFields['userid'] = $this->userid;
       $sql =
         sprintf(
                 ' insert into user_data ('
                 .' userid, firstname, lastname, nick, email, '
                 .' institution, pays, fonction, cv, firstname_norm, '
                 .' lastname_norm '
                 .") values "
                 ."('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', "
                 ."'%s', normalize_tag('%s'), normalize_tag('%s'))",
                 $i->dbescape($this->userid), 
                 $i->dbescape($this->firstName),
                 $i->dbescape($this->lastName),
                 $i->dbescape($this->nick),
                 $i->dbescape($this->email),
                 $i->dbescape($this->institution),
                 $i->dbescape($this->pays),
                 $i->dbescape($this->fonction),
                 $i->dbescape($this->cv),
                 $i->dbescape($this->firstName),
                 $i->dbescape($this->lastName)
                 );
     }
     else {
       $sql = ' update user_data set ';
       $sql .=
         sprintf("firstname = '%s', lastname = '%s', nick = '%s', "
                 . " email = '%s', institution = '%s', pays = '%s', fonction = '%s', "
                 . " cv = '%s', "
                 . " firstname_norm = normalize_tag('%s'), "
                 . " lastname_norm = normalize_tag('%s')",
                 $i->dbescape($this->firstName),
                 $i->dbescape($this->lastName),
                 $i->dbescape($this->nick),
                 $i->dbescape($this->email),
                 $i->dbescape($this->institution),
                 $i->dbescape($this->pays),
                 $i->dbescape($this->fonction),
                 $i->dbescape($this->cv),
                 $i->dbescape($this->firstName),
                 $i->dbescape($this->lastName)
                 );

       $sql .= " where userid = '" . $i->dbescape($this->userid) . "'";
     }
     $i->query($sql);
   }



   /**
    * @param string $identifier
    * @param string $service 4 character service identifier ("face", "goog", etc.)
    */
   public function associateUserWithIdentifier ($identifier, $service) {
     $idLen = strlen($identifier);
     if (($idLen < 4) || ($idLen > 255)) {
       throw new malformedIdentifierException();
     }

     try {
       $i = new folksoDBinteract($this->dbc);
       $sql = sprintf('insert into user_services '
                      .' (userid, service_id, identifier) '
                      .' values '
                      ." (%s, %s, %s)",
                      $this->userId,
                      $i->dbescape($identifier),
                      $i->dbescape(strtolower($service)));

       $i->query($sql);
     }
     catch (dbQueryException $qe) {
       if (($qe->sqlcode == 1452) &&
           (strpos($qe->message, 'service_id'))) {
         throw new unknownServiceException();
       }
       elseif (($qe->sqlcode == 1452) &&
               (strpos($qe->message, 'userid'))) {
         throw new badUseridException("userid is not known or is missing");
       }
     }
   }



/**
 * @param 
 */
 public function loadAllRights () {
   $i = new folksoDBinteract($this->dbc);
   $i->query('select ur.rightid, r.service '
             .' from users_rights ur '
             .' join rights r on r.rightid = ur.rightid '
             ." where userid = '" . $i->dbescape($this->userid) . "' ");

   if ($i->result_status == 'OK') {
     if ((! $this->rights) ||
         (! $this->rights instanceof folksoRightStore)) {
       $this->rights = new folksoRightStore();
     }

     while ($row = $i->result->fetch_object()){
       if (! $this->rights->checkRight($row->service, $row->rightid)) {
         $this->rights->addRight(new folksoRight($row->service,
                                                 $row->rightid));
       }
     }
   }
 }


  /**
   * @param $service String
   * @param $right String
   */
  public function checkUserRight ($service, $right) {
    if (($this->rights instanceof folksoRightStore) &&
        ($this->rights->checkRight($service, $right))){
      return true;
    }
    else {
      $this->loadAllRights();
      if ($this->rights->checkRight($service, $right)) {
        return true;
      }
    }
    return false;
  }
}

