<?php
  /**
   * This class provides a unified interface for all of the data
   * pertaining to the HTTP request, including GET and POST
   * parameters, and possible PUT and DELETE parameters should we end
   * up using those methods.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */

class folksoQuery {
  private $method;
  private $content_type;
  private $fk_params = array(); //will contain only folkso related parameters

   function __construct ($server, $get, $post) {
    $this->method = $server['REQUEST_METHOD'];
    $this->content_type = $server['CONTENT_TYPE'];
    if (count($get) > 0) {
      $this->fk_params = array_merge($this->param_get($get), $this->fk_params);;
    }

    if (count($post) > 0) {
      $this->fk_params = array_merge($this->param_get($post), $this->fk_params);
    }
    /** Will add put and delete support here later **/

  }

  private function param_get ($array) { // $array is either
                                               // _POST, _GET etc.,
                                               // $meth is the
                                               // corresponding name
                                               // 'post', 'get', etc.
    $accum = array();
    foreach ($array as $param_key => $param_val) {
      if (substr($param_key, 0, 6) == 'folkso') {
        $accum[$param_key] = $param_val;
      }
    }
    return $accum;
  }

  public function params () {
    return $this->fk_params;
  }

  public function method () {
    return strtolower($this->method);
  }

  }// end class



?>