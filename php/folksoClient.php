<?php


class folksoClient {
  public $host;
  public $uri; // not including hostname ie. /thispage.php
  public $method;
  public $postfields = '';
  public $getfields = array();
  public $datastyle;
  public $ch;

  /**
   * Arguments: HOST, URI, METHOD. URI means the local part of the
   * URI. Right now we assume that it starts with a leading slash,
   * though that should probably be fixed.
   *
   */
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
    $this->postfields = $this->parse_arg_array($arr);
    return $this->postfields;
  }

  /**
   * Takes as argument an associative array of GET fieldnames and
   * values. Keys must begin with 'folkso'. datastyle is added later,
   * do not put it here.
   */
  function set_getfields ($arr) {
    $this->getfields = $this->parse_arg_array($arr);
    return $this->getfields;
  }



  function execute () {
    $this->ch = curl_init($this->build_req());
    $headers = array( "Content-Type: application/x-www-form-urlencoded",
                      "Content-length: " . $this->content_length());
    curl_setopt($this->ch, CURLOPT_HTTPHEADERS, $headers);
    if (strtolower($this->method) == 'post'){
      curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postfields);
    }
    curl_exec($this->ch);
    

  }

  // curl_setopt($this->ch, CURLOPT_POSTFIELDS....)


  public function build_req () {
    $uri = $this->host . $this->uri;
    if (strtolower($this->method) == 'get') {
      /* add '?' */
      if ($this->datastyle || $this->getfields) {
        $get = '?';
      }
      if (strlen($this->getfields) > 0) {
        $get .= $this->getfields;
      }
      if ((strlen($get) > 1) &&
          (strlen($this->datastyle) > 0)) {
        $get .= '&';
      }
      if (strlen($this->datastyle) > 0) {
        $get .= 'folksodatastyle=' . $this->datastyle;
      }
      return $uri . $get;
    }
    else {
      return $uri;
    }
  }

  public function content_length () {
    if (strtolower($this->method) == 'get') {
      return 0;
    }
    elseif (strtolower($this->method) == 'post') {
      $length = strlen($this->postfields);
      if ($this->datastyle) {
        $length = $length + strlen('&folksodatastyle='.$this->datastyle);
      }
      return $length;
    }
    else {
      // error : unsupported method
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
  } //end class


?>