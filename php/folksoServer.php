<?php

class folksoServer {

  // Access stuff
  public $clientAccessRestrict = 'LOCAL'; //'LOCAL', 'LIST' or 'ALL'
  public $clientAccessRestrictList = array('127.0.0.1'); //localhost always allowed
  public $allowedClientMethods;
  
  private $possibleMethods = array('GET', 'get', 'POST', 'post', 'PUT', 'put', 'DELETE', 'delete');
  private $possibleAccessModes = array('LOCAL', 'LIST', 'ALL');
  private $responseObjects = array();


  function __construct ($config) {
     $conf_keys = array('methods', 'access_mode','access_list' );
    
    // methods
    if ((array_key_exists('methods', $config)) &&
        (is_array($config['methods']))) {
      $this->allowedClientMethods = array();
      foreach ($config['methods'] as $meth) {
        if (in_array($meth, $this->possibleMethods)) {
          array_push($this->allowedClientMethods, $meth);
        }
      }
    }
    else {
      $this->allowedClientMethods = array('GET');
    }

    // access mode
    if ((array_key_exists('access_mode', $config)) &&
        (in_array($config['access_mode'], $this->possibleAccessModes))) {
      $this->clientAccessRestrict = $config['access_mode'];
    }
    else {
      $this->clientAccessRestrict = 'LOCAL';
    }
        
    // client restrictions
    if (( $this->clientAccessRestrict == 'LIST') &&
        ( array_key_exists('access_list', $config)) &&
        ( is_array($config['access_list']))) { // this should be an erreur instead!
      $this->clientAllowedHost = $config['access_list'];
    }
  }

  
  public function addResponseObj (folksoResponse $resp) { //one arg here to indicate
                                           //that at least one is
                                           //necessary
    $objs = func_get_args();
    foreach ($objs as $ob) {
      if ( $ob instanceof folksoResponse ) {
        push($this->responseObjects, $ob);
      }
      else {
        //error
      }
    }
  }


  public function Respond ($request) {
    /*
     * Based on the request received, checks each response object is
     * checked to see if it is equiped to handle the request.
     */
    $q = new folksoQuery(); //automatically gets all current request info

    /* check each response object and run the response if activatep
     returns true*/
    foreach ($this->responseObjects as $resp) {
      if ( $rep->activatep($q)) {
        $rep->Respond($q);
        break;
      }
    }
  }
  
  public function initialChecks () {
    if ((in_array($_SERVER['REQUEST_METHOD'], $this->allowedClientMethods)) &&
        ($this->validClientAddress($_SERVER['REMOTE_HOST'], $_SERVER['REMOTE_ADDR']))) {
      // deal with request
    }
    else {
      // send error message
    }
  }
  
  function validClientAddress ($host, $ip) {
    if ( $this->clientAccessRestrict == 'ALL') {
      return true;
    }
    if (( $this->clientAccessRestrict == 'LOCAL') &&
        (( $ip == '127.0.0.1' ) or
         ( $ip == '::1'))) {
      return TRUE;
    }
    if (( $this->clientAccessRestrict == 'LIST' ) &&
        (( in_array($ip, $this->clientAccessRestrictList)) ||
         ( in_array($host, $this->clientAccessRestrictList)))) {
      return true;
    }
    return false;
  }


  } //end of class


?>