<?php

require_once('folksoUserValid.php');

class folksoWsseCreds extends folksoUserValid {

  public $header;
  public $received_values;

  function __construct ($header)  {
    $this->header = $header;
  }

  public function getUserPasswd ($user) {
    if ($user == 'folksy') {
      return "folksong";
    }
    elseif ($user == 'bob') {
      return 'slob';
    }
  }

  public function getUsername () {
    return $this->username;
  }

  function tag_create_access () {
    return true;
  }

  function tag_admin_access () {
    return true;
  }
  function userid () {
    return $this->userid;
  }


  /**
   * Probably should not be using errors like this here. Return false
   * instead.
   */

  function parse_auth_header ($head = '') {
    if (empty($head)) {
      $head = $this->header;
    }
    if (empty($head)){
      return false;
    }

    if (preg_match('/username="([^"]+)"/i', $head, $username_match)) {
      $this->username = $username_match[1];
    }
    elseif (preg_match("/username='([^']+)'/i", $head, $username_match)) {
      $this->username = $username_match[1];
    }
    else {
      trigger_error("Could not find username in $head", E_USER_ERROR);
    }

    if (preg_match('/passworddigest="([^"]+)"/i', $head, $digest_match)) {
      $this->password_digest = $digest_match[1];
    }
    elseif (preg_match("/passworddigest='([^']+)'/i", $head, $digest_match)) {
      $this->password_digest = $digest_match[1];
    }
    else {
      trigger_error("Could not find passwordDigest in $head", E_USER_ERROR);    
    }
    if (preg_match('/nonce="([^"]+)"/i', $head, $nonce_match)) {
      $this->nonce = $nonce_match[1];
    }
    elseif (preg_match('/nonce="([^"]+)"/i', $head, $nonce_match)) {
      $this->nonce = $nonce_match[1];
    }
    else {
      trigger_error("Could not find nonce in $head", E_USER_ERROR);
    }

    if (preg_match('/created="([^"]+)/i', $head, $created_match)) {
      $this->created = $created_match[1];
    }
    elseif (preg_match("/created='([^']+)/i", $head, $created_match)) {
      $this->created = $created_match[1];
    }
    else {
      trigger_error("Could not find created in $head", E_USER_ERROR);
    }
    return true;
  }

  function buildDigestResponse () {
    $first = sha1( $this->nonce . 
                   $this->created . 
                   $this->getUserPasswd($this->username));
  
    return base64_encode($first);
  }

  public function Validate () {
    if ( $this->buildDigestResponse() == $this->password_digest)  {
      return true;
    }
    else {
      return false;
    }
  }

  public function validateAuth ($header = '') {
    // first check if info is already there
    if ((isset($this->username)) &&
        (isset($this->nonce)) &&
        (isset($this->password_digest))) {
      return true;
    }

    // if not : parse header and check again
    if (empty($header)) {
      $header = $this->header;
    }

    $this->parse_auth_header($header);
    if ((isset($this->username)) &&
        (isset($this->nonce)) &&
        (isset($this->password_digest))) {
      return true;
    }
    else {
      return false;
    }
  }
} // end of class

?>