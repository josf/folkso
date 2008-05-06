<?php

class folksoUserCreds {
  public $userid;
  public $digest;

  function __construct ($digest) {
    $this->digest = $digest;
  }

  /**
   * The checking is not done in the __construct because we may not
   * need to check sometimes (for GETs, essentially) and since that
   * involves a database connection, it isn't worth it.
   */
  function check_digest () {   // completely bogus for now...
    if ($this->digest == 'xyz') {
      $this->userid = 99999;
    }
    else {
      return false;
    }
  }

  function tag_create_access () {
    return true;
  }

  function admin_access () {
    return true;
  }
  function userid () {
    return $this->userid;
  }



  


// function to parse the http auth header
function http_digest_parse($txt) {

    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();

    $raw = explode(',', $txt);
    $auth = array();
    foreach ($raw as $rr) {
      $key = '';
      $val = '';

      $rr = trim($rr);
      if(strpos($rr,'=') !== false) {
        $lhs = substr($rr,0,strpos($rr,'='));
        $rhs = substr($rr,strpos($rr,'=')+1);
        $lhs = trim($lhs);
        $rhs = trim($rhs);

        if ((substr($rhs, 0, 1) == substr($rhs, -1, 1)) &&
            ((substr($rhs, 0, 1) == '"') ||
             (substr($rhs, 0, 1) == "'"))) {
          $val = substr($rhs, 1, (strlen($rhs) - 2));
        }
        else {
          $val = $rhs;
        }

        // avoiding the 'Digest firstparam="' part
        if (strstr($lhs, ' ') == false) {
          $key = $lhs;
        }
        else {
          $key = substr($lhs, (strpos($lhs, ' ') + 1));
        }
        $auth[$key] = $val;
      }
    }
    return $auth;
}
      
  } //end class


?>