<?php


  /**
   * Provide link formatting tools to build URLs for  resources
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */
require_once('folksoFabula.php');
require_once('folksoLink.php');
  /**
   * @package Folkso
   */
class folksoTagLink extends folksoLink {
  public $loc;
  public $identifier;
  public $getlink;

  public function getLink ($tag = null, $type = 'html') {
    if ($this->getlink) {
      return $this->getlink;
    }
    
    $t = $tag ? $tag : $this->identifier;
    $this->getlink = 
      $this->loc->web_url
      . '/tag/'
      . $t;
    return $this->getlink;
  }

}