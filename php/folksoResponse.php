<?php

  /**
   * folksoResponse provides objects to be used as args to the
   * addResponseObj method in folksoServer.php. Each object must
   * contain a test function and an "action" function. The test
   * function is called to determine if the action function should be
   * called or not.
   *
   * Both test and action functions receive folksoQuery objects as
   * mandatory arguments. Test functions must return either true or
   * false. Action functions should provide a document of some sort to
   * return and probably should provide a status code.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */

 
class folksoResponse {
  public $httpMethod = 'GET';
  public $test_func;
  public $action_func;


  function __construct ($test_func, $action_func) {
    $this->test_func = $test_func; // must return true or false
    $this->action_func = $action_func;
  }


  function activatep (folksoQuery $query) {
    $aa =  $this->test_func;
    return $aa($query);
  }
  
  function Respond (folksoQuery $query) {
    $aa = $this->action_func;
    return $aa($query); //action (on DB for example) + return document + status
  }

  function getHttpMethod () {
    return $this->httpMethod;
  }

  function setHttpMethod ($meth) {
    if ((is_string($meth)) &&
        in_array(strtolower($meth), array('get', 'put', 'post', 'delete'))) {
      $this->httpMethod = $meth;
      return $this->httpMethod;
    }
    else {
      //Error
    }
  }

  }// end of class



?>