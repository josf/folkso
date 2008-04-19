<?php
 
class folksoResponse {
  public $httpMethod = 'GET';
  public $test_func;
  public $action_func;


  function __construct ($test_func, $action_func) {
    $this->test_func = $test_func; // must return true or false
    $this->action_func = $action_func;
  }


  function activatep () {
    $aa =  $this->test_func;
    return $aa();
  }
  
  function Respond () {
    $aa = $this->action_func;
    return $aa();
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