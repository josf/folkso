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

require_once('folksoTagdata.php');
require_once('folksoClient.php');

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
   * Beyond the interface. The display text of the tag. Use
   * $tr->title() to access this.
   */
  private $title;


  /**
   * Ok, so $meta_only doesn't mean anything in this class. So sue
   * me. You can use it, but nothing will happen.
   */
  public function getData($max_tags = 0, $meta_only = false) {
    $fc = new folksoClient('localhost',
                           $this->loc->server_web_path . 'tag.php',
                           'GET');
    $fc->set_getfields(array('folksotag' => $this->url,
                             'folksofancy' => 1,
                             'folksodatatype' => 'xml'));
    $result = $fc->execute();
    $status = $fc->query_resultcode();
    $this->store_new_xml($result, $status);
  }

  /**
   * Return a formatted html list of resources associated with a given
   * tag.
   *
   * The caller should check the $tr->status variable to see if the
   * query was successful. On anything but a 200 or 304, this method
   * returns nothing, but it would generally be useful to have a
   * warning for 204 (no resources yet for this tag) or 404 (tag not
   * found).
   * 
   * @return string html for a list of resources referenced by the
   * given tag.
   *
   * 
   */
  public function resList() {
    if (strlen($this->xml) == 0) {
      $this->getData();  // args ?
    }

    if ($this->is_valid()) {
      $xsl = new DOMDocument();
      $xsl->load($this->loc->xsl_dir . "public_resourcelist.xsl");

      $proc = new XsltProcessor();
      $xsl = $proc->importStylesheet($xsl);

      // possibly cached DOM
      $taglist = $proc->transformToDoc($this->xml_DOM()); 
      $this->html = $taglist->saveXML();
      return $this->html;
    }
  }

  /**
   * Stores the current tag title in $tr->title.
   *
   * @returns string The title of the current tag.
   */
  public function title() {
    if (! $this->status) {
      $this->getData();
    }
    $dom = $this->xml_DOM();
    $elems = $dom->getElementsByTagName("tagtitle");
    if ($elems->length > 0) {
      $this->title = $elems->item(0)->textContent;
      return $this->title;
    }
    else {
      return '';
    }
  }


  } /* end of class */