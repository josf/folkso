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

  /**
   * Stores away the results of a query. If the query is not valid
   * (200 or 304), does not store the XML content, which might be the
   * explanation of an error returned by the server.
   *
   * @param $xml string XML data from the server
   * @param $status integer HTTP status from the request.
   */
  public function store_new_xml ($xml, $status) {
    $this->status = $status;
    if ($this->is_valid()) {
      $this->xml = $xml;
    }
  }

  /**
   * Retrieve raw data from the server.
   */
  abstract public function getData($max_tags = 0, $meta_only = false);

  /*  abstract public function formatData(); */


  /**
   * Get the DOM document object from the current xml data.
   *
   * If $this->xml_dom is not yet a DOMDocument object, generates one
   * from the current xml data (retreived from server). If a
   * DOMDocument already exists, we just return that one.
   *
   * There does not appear to be a way to get anything but a php
   * warning if there are problems building the DOM object.
   *
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