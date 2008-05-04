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
  } //end class


?>