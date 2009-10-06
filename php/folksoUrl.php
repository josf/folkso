<?php

  /**
   * A class for describing URLs. (Primarily so that they can be
   * stored conveniently.)
   * 
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */
  /**
   * @package Folkso
   */
class folksoUrl {
  /* very bare bones. just holds title and url */
  var $url;
  var $title;
  
  function __construct ($url, $title) {
    $this->set_url( $this->validate_url($url));
    $this->set_title( $title );
  }

  function get_url() {  
    return $this->url; 
  }
  function set_url ($new_url) {
    $this->url = $this->validate_url($new_url);
    return $this->url;
  }
  function get_title () { 
    return $this->title;
  }
  function set_title ($new_title) {
    $this->title = $new_title;
    return $this->title;
  }

  function validate_url ($string) {
    if (strpos($string, '#')) {
      $string  = substr($string, 0, strpos($string, '#'));
    }

    if (strlen( $string) > 262) {
      $string = substr($string, 1, 262);
    }
    $this->url = $string;
    return $string;
  }

}

?>
