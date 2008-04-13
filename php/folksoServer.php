<?php

class TagServer {

  // Access stuff
  public $clientUrlRestrict = 'LOC'; //'LOC', 'LIS' or 'ALL'
  public $clientUrlRestrictList = array('127.0.0.1'); //localhost always allowed
  public $allowedClientMethods = 'GET', 'POST';

  public function initialChecks () {
    if ((in_array($_SERVER['REQUEST_METHOD'], $this->allowedClientMethods)) &&
        ($this->validClientAddress($_SERVER['REMOTE_HOST'], $_SERVER['REMOTE_ADDR']))) {
      // deal with request
    }
    else {
      // send error message
    }
  }
  
  private function validClientAddress ($host, $ip) {
    if ( $this->clientUrlRestrict == 'ALL') {
      return true;
    }
    if (( $this->clientUrlRestrict == 'LOC') &&
        ( $ip == '127.0.0.1' )) {
      return true;
    }
    if (( $this->clientUrlRestrict == 'LIS' ) &&
        (( in_array($ip, $this->clientUrlRestrictList)) ||
         ( in_array($host, $this->clientUrlRestrictList)))) {
      return true;
    }
    return false;
  }


}


?>