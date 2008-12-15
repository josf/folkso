<?php 
  /**
   *
   * @package Folkso
   * @subpackage webinterface
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */
  /**
   * Abstract class for data objects for use through folksoPage and
   * more specifically folksoPageData.
   * 
   *
   * @package Folkso
   */
abstract class folksoTagdata {

  /**
   * The uri of the resource being dealt with. Should be passed in on
   * object construction.
   */
  public $url;

  /**
   * The actual xml document returned by the server.
   */
  public $xml;
  /**
   * If a DOM object is made, we save it here for future use.
   */
  public $xml_dom;
  public $html;

  /**
   * Result status from the http request.
   */
  public $status;

  /**
   * Local data (subclass of folksoLocal)
   * @param folksoLocal object
   */
public $loc;

  public function __construct (folksoFabula $loc, $url) {
    $this->loc = $loc;
    $this->url = $url;
  }

  public function store_new_xml ($xml, $status) {
    $this->xml = $xml;
    $this->status = $status;
  }

  /**
   * Retrieve raw data from the server.
   */
  abstract public function getData($max_tags = 0, $meta_only = false);

  /*  abstract public function formatData(); */


  /**
   * @return DOMDocument containing the XML in $this->xml. If there is
   * no data, returns an empty DOMDocument.
   */
  public function xml_DOM () {
    if (! $this->xml_dom instanceof DOMDocument) {
      $this->xml_dom = new DOMDocument();
      if (strlen($this->xml) > 0) {
        $this->xml_dom->loadXML($this->xml);
      }
    }
    return $this->xml_dom;
  }

  /**
   * Test whether the request was successful (result status 200 or
   * 304). If $this->status is NULL issues a warning but returns false
   * all the same.
   *
   * @returns boolean
   */
  public function is_valid() {
    if (($this->status) && 
        (($this->status == 200) ||
         ($this->status == 304))) {
      return true;
    }
    else {
      if (! is_numeric($this->status)) {
        trigger_error('is_valid: no result status yet.', E_USER_WARNING);
      }
      return false;
    }
  }
  
  }

?>