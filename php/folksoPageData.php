<?php
/**
 * @package Folkso
 * @author Joseph Fahey
 * @subpackage webinterface
 * @copyright Joseph Fahey 2008
 */

require_once('folksoFabula.php');
require_once('folksoCloud.php');
require_once('folksoPagetags.php');
require_once('folksoPageDataMeta.php');

/**
 * @package Folkso
 *
 * Class allowing access to the various kinds of objects pertaining to
 * a given page:  tagclouds, metadata, tag lists.
 * 
 * The primary function of this page is to build these objects that
 * can then be used in folksoPage. This class could probably be
 * absorbed by folksoPage, but it does allow folksoPage, which is
 * intended to be the primary, if not the sole, interface for
 * webpages, to be a little bit cleaner.
 */
class folksoPageData {
  
  /**
   * A folksoCloud object
   */
  public $cloud;
  
  /**
   * A folksoPagetags object, used for building metadata.
   */
  public $ptags;

  /**
   * Title 
   */
  public $title;

  /**
   * The URL of the page in question. That is, this page, the one we
   * are talking about.
   */
  public $url;

  /**
   * A folksoPageDataMeta object for building meta data. Right now the
   * mt object must be created by calling a method in folksoPagetags.
   */
  public $mt;


  private $loc;

  /**
   * @param $url string The url of the current page, usually generated
   * by folksoPage.curPageURL(). This usually can also be the
   * resource's numeric ID (not technically a url).
   */
  public function __construct($url) {
    $this->url = $url;
    $this->loc = new folksoFabula();
  }


  /**
   * Initiates $this->mt, a folksoPageDataMeta object which can then
   * be used to display meta data. If $this->mt is already an object,
   * then we simply return it and do not build a new one.
   *
   * @return folksoPageMetaData object
   */
  public function prepareMetaData() {
    if ($this->mt instanceof folksoPageDataMeta) {
      return $this->mt;
    }

    if (! $this->ptags instanceof folksoPagetags) {
      $this->ptags = new folksoPagetags($this->loc, $this->url);
    }

    $this->mt = $this->ptags->buildMeta();
    return $this->mt;
  }

  /**
   * Build a folksoCloud object and populate it with data from the
   * tagserver.
   *
   * @return folksoCloud object.
   */
  public function prepareCloud($url = '', $max_tags = 0, $cloudtype = '') {
    if (! $this->cloud instanceof folksoCloud) {
      $this->cloud = new folksoCloud($this->loc, $this->url);
    }

    $this->cloud->buildCloud($url,
                             $max_tags, 
                             $cloudtype);
    return $this->cloud;
  }

} /* end of class */