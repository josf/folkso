<?php

  /**
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */

require_once('folksoClient.php');
require_once('folksoFabula.php');
require_once('folksoPageData.php');

/**
 * Presentation of tagclouds of "related tags".
 *
 *
 */

class folksoRelatedTags extends folksoTagdata {
  public $url;
  public $xml;
  public $xml_dom;
  public $html;
  public $status;
  public $loc;

  /**
   * @param folksoLocal $loc
   * @param $url
   */
  public function __construct (folksoLocal $loc, $tag) {
    $this->loc = $loc;
    $this->url = $url; // why do we need this again?
    $this->tag = $tag;
  }
  
  /**
   * Note: $this->tag must be set for getData to work.
   *
   * @param $max_tags
   * @param $meta_only Here just to preserve the interface. Does nothing.
   * @param $cloud_type Unused, though maybe could be used some day for something.
   */
  public function getData ($max_tags = 0,$meta_only = false, $cloud_type = '') {
    $fc = new folksoClient($this->loc->web_url,
                           $this->loc->server_web_path . 'tag.php',
                           'GET');

    $fc->set_getfields(array('folksorelated' => 1,
                             'folksotag' => $this->tag));
    /** will eventually need a datatype here **/

    $result = $fc->execute();
    $status = $fc->query_resultcode();
    if (! $status) {
      trigger_error('Internal request failed.', E_USER_ERROR);
    }
    $this->store_new_xml($result, $status);
    return $this;
   }
  
  
/**
 * @param 
 */
  public function buildCloud ($tagarg = null, $max_tags = 0, $cloudtype = '') {
    if ($this->html) {
      return $this->html;
    }
    
    $tag = $tagarg ? $tagarg : $this->tag;

    if (! $this->xml) {
      $this->getData();
    }
    /** this part is simpler because we are now doing the xslt
        processing on the server side **/
    $this->html = $this->xml;
    return $this->html;
 }


}