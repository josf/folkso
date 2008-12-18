<?php 
  /**
   *
   * @package Folkso
   * @subpackage webinterface
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */
  /**
   * Methods for displaying lists of resources associated with a given
   * tag.
   *
   * @package Folkso
   */

class folksoTagRes extends folksoTagdata {
  
  /**
   * Technically, this is no longer a url but a tag id or a tag
   * name. This name difference is really the only thing that
   * separates this class from the other folksoTagdata classes (Cloud
   * and Pagetags).
   */
  public $url;
  public $xml;
  public $xml_dom;
  public $html;
  public $status;
  public $loc;

  /**
   * Beyond the interface. The display text of the tag.
   */
  public $title;

  /**
   * Ok, so $meta_only doesn't mean anything in this class. So sue
   * me. You can use it, but nothing will happen.
   */
  public function getData($max_tags = 0, $meta_only = false) {
    $fc = new folksoClient('localhost',
                           $this->loc->server_web_path . 'tag.php',
                           'GET');
    $fc->set_getfields('folksotag' => $url,
                       'folksodatatype' => 'xml');
    $result = $fc->execute();
    $status = $fc->query_resultcode();
    $this->store_new_xml($result, $status);
  }

  /**
   * @return string html for a list of resources referenced by the
   * given tag.
   */
  public function resList() {
       


  }
  }