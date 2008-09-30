<?php

  /**
   * This class should work with PHP4 or PHP5.
   * 
   * It requires the php libcurl functions but could be rewritten,
   * with the same interface, to use another http client.
   */

class folksoClient {
  var $host;
  var $uri; // not including hostname ie. /thispage.php
  var $method;
  var $postfields = '';
  var $getfields = array();
  var $datastyle;
  var $ch;

  /**
   * Arguments: HOST, URI, METHOD. URI means the local part of the
   * URI. Right now we assume that it starts with a leading slash,
   * though that should probably be fixed.
   *
   */
  function folksoClient ($host, $path, $method) {
    $this->host = $host;
    $this->path = $path;
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

  /**
   * Datastyle indicators (html, xml, etc.) should be very short,
   * probably one character (h = html, x = xml, etc.)
   *
   */
  function set_datastyle ($str) {
    if (strlen($str) > 3) { 
      $str = substr($str, 0, 3);
    }
    $this->datastyle = $str;
  }

  function execute () {
    $this->ch = curl_init($this->build_req());

    curl_setopt($this->ch, CURLOPT_USERAGENT, 'folksoClient');
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_USERPWD, 'taggeur:quelbeautag');
    if (strtolower($this->method) == 'post'){
      $headers = array( "Content-Type: application/x-www-form-urlencoded",
                        "Content-length: " . $this->content_length());
      curl_setopt($this->ch, CURLOPT_HTTPHEADERS, $headers);
      curl_setopt($this->ch, CURLOPT_POST, true);
      curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postfields);
    }
    return curl_exec($this->ch);
  }

  /**
   * Returns the appropriate request string depending on method and
   * the different fields involved. Mostly this is a problem for GETs
   * since all the fields are part of the URI.
   *
   */
  function build_req () {
    $uri = $this->host . $this->path;
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


  /**
   * Calculates content length for POSTs. 
   *
   */
  function content_length () {
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

  /**
   * Access to information about the query.
   *
   */

  function query_resultcode () {
    return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
  }

  function parse_arg_array ($arr) {
    $arg_a = array();
    foreach ($arr as $pkey => $pval) {
      if (substr($pkey, 0, 6) == 'folkso') {
        array_push($arg_a, $pkey . "=" . urlencode(trim($pval)));
      }
      else {
        // TODO : error of some kind
      }
    }
    return implode($arg_a, '&');
  }
  } //end class


?>

