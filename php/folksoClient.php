<?php

class folksoClient {
  public $host;
  public $uri; // not including hostname ie. /thispage.php
  public $method;
  public $postfields = '';
  public $getfields = array();
  public $datastyle;
  private $ch;

  function __construct ($host, $uri, $method) {
    $this->host = $host;
    $this->uri = $uri;
    $this->method = $method;
  }

  /**
   * Takes as argument an associative array of POST fieldnames and
   * values. Keys must begin with 'folkso'.
   */
  function set_postfields ($arr) {
    $this->postfields = parse_arg_array($arr);
    return $this->postfields;
  }

  /**
   * Takes as argument an associative array of GET fieldnames and
   * values. Keys must begin with 'folkso'. datastyle is added later,
   * do not put it here.
   */
  function set_getfields ($arr) {
    $this->getfields = parse_arg_array($arr);
    return $this->getfields;
  }



  function execute () {
    $this->ch = curl_init($this->host . $this->uri);



    curl_setopt($this->ch, CURLOPT_POSTFIELDS....)
      }

  private function build_req () {
    $uri = $this->host . $this->uri;
    if (strtolower($this->method) == 'get') {
      $uri =. '?';
      if (is_string($this->datastyle)) {
        $uri =. 'folksodatastyl=' . $this->datastyle;
      }


  }


  private function parse_arg_array ($arr) {
    $arg_a = array();
    foreach ($arr as $pkey => $pval) {
      if (substr($pkey, 0, 6) == 'folkso') {
        array_push($arg_a, $pkey . "=" . trim($pval));
      }
      else {
        // TODO : error of some kind
      }
    }
    return implode($arg_a, '&');
  }
      


  }


?>