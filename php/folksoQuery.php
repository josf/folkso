<?php
  /**
   * This class provides a unified interface for all of the data
   * pertaining to the HTTP request, including GET and POST
   * parameters, and possible PUT and DELETE parameters should we end
   * up using those methods.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright Gnu Public Licence (GPL)
   */

class folksoQuery {
  public $method;
  public $content_type;
  public $fk_params = array(); //will contain only folkso related parameters

  function __constructor () {
    $method = $_SERVER['REQUEST_METHOD'];
    $content_type = $_SERVER['CONTENT_TYPE'];
    
    if (count($_GET) > 0) {
      $this->param_get($_GET);
    }

    if (count($_POST) > 0) {
      $this->param_get($_POST);
    }
    /** Will add put and delete support here later **/

  }

  private function param_get ($array) { // $array is either
                                               // _POST, _GET etc.,
                                               // $meth is the
                                               // corresponding name
                                               // 'post', 'get', etc.
    foreach ($array as $param_key => $param_val) {
      if (substr($param_key, 0, 6) == 'folkso') {
        $this->fk_params['$param_key'] = $param_val;
      }
    }

  }

  public function params () {
    return $this->fk_params;
  }

  public function method () {
    return $this->method;
  }

  }// end class



?>