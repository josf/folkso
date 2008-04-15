<?php
 
class folksoResponse {
  private $httpMethod = 'GET';
  private $test_func;
  private $action_func;


  function __construct ($test_func, $action_func) {
  }


  

  function getHttpMethod () {
    return $this->httpMethod;
  }

  function setHttpMethod ($meth) {
    if ((is_string($meth)) &&
        (in_array(array('get', 'put', 'post', 'delete')), strtolower($meth))) {
      $this->httpMethod = $meth;
    }
    else {
      //Error
    }
  }

  }// end of class



?>