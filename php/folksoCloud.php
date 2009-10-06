<?php 
  /**
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */

require_once('folksoTagdata.php');
require_once('folksoClient.php');
  /**
   *
   * Cloud objects for use through folksoPage and more specifically
   * folksoPageData.
   * 
   *
   * @package Folkso
   */
class folksoCloud extends folksoTagdata {
  public $xml;
  public $html;
  public $status;
  public $xml_dom;

public $loc;

  /**
   * Get the raw cloud information from the server. This data is
   * stored in $this->pdata->cloud.
   *
   * @returns folksoCloud object.
   */
  public function getData($max_tags = 0, 
                          $meta_only = false,
                          $cloudtype = '') {


    $fc = new folksoClient('localhost', 
                           $this->loc->server_web_path . 'resource.php',
                           'GET');

    $fc->set_getfields(array('folksoclouduri' => 1,
                             'folksores' => $this->url,
                             'folksodatatype' => 'xml', 
                             'folksolimit' => $max_tags)); 


    if ($cloudtype == 'bypop') {
      $fc->add_getfield('bypop', 1);
    }
    elseif ($cloudtype == 'bydate') {
      $fc->add_getfield('bydate', 1);
    }

    $result = $fc->execute();
    $status = $fc->query_resultcode();
    if (! $status) {
      trigger_error('no valid status here.', E_USER_ERROR);
    }

    $this->store_new_xml($result, $status);
    return $this;
  }
  

  /**
   * Retreives and formats a tag cloud for the current page.  The html
   * remains available in $this->html. If html data is already
   * present, simply returns that.
   *
   * @param $url string (Optional) The url (or id) for which a cloud should be
   * made. Default is to use the current page.
   * @param $max_tags integer  (Optional). Defaults to 0, meaning get all
   * the tags.
   *
   * @returns string The html cloud that is built. 
   */
  public function buildCloud($url = '', 
                             $max_tags = 0, 
                             $cloudtype = '') {
    if ($this->html) {
      return $this->html;
    }

    if (! $this->xml ){
      $this->getData($url ? $url : $this->url,
                     $max_tags,
                     $cloudtype);
    }

    if ($this->is_valid()) {
      $xsl = new DOMDocument();
      $xsl->load($this->loc->xsl_dir . "publiccloud.xsl");

      $proc = new XsltProcessor();
      $xsl = $proc->importStylesheet($xsl);

      // setting url format so that it is not hard coded in the xsl.
      $proc->setParameter('', 
                          'tagviewbase', 
                          $this->loc->server_web_path . 'tagview.php?tag=');

      //using cloud->xml_DOM() because this data might have been cached already.
      $cloud = $proc->transformToDoc($this->xml_DOM());
      $this->html = $cloud->saveXML();
    }
    return $this->html;
  }
  


  }
?>