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
   * @package Folkso
   *
   * Provides EAN-13 related information about a resource.
   * Ean-13 based lists of resources refering to the same EAN-13 as
   * the current page.
   * 
   *
   * @package Folkso
   */
class folksoEanList extends folksoTagdata {
  public $xml;
  public $html;
  public $status;
  public $xml_dom;
  public $loc;

  /**
   * None of the parameters are used. Everything is based on the
   * current url instead.
   */
  public function getData($max_tags = 0,
                          $meta_only = false,
                          $cloudtype = '') {

    $fc = new folksoClient('localhost', 
                           $this->loc->server_web_path . 'resource.php',
                           'GET');

    /** we do not set the datatype because right now this request
        automatically returns an XML doc **/
    $fc->set_getfields(array('folksores' => $this->url,
                             'folksoean13list' => 1));

    $result = $fc->execute();
    $status = $fc->query_resultcode();
    if (! $status) {
      trigger_error('no valid status here.', E_USER_ERROR);
    }

    $this->store_new_xml($result, $status);
    return $this;
  }

  /**
   * Returns a string containing Dublin Core Identifier <meta> tags.
   *
   * <meta name="DC.Identifier" scheme="URI" content="..."/>
   *
   * @param $url string optional defaults to '', ie. current object's url.
   * @param $max_tags integer ignored.
   * @param $cloudtype string ignored.
   */
  public function ean13_dc_metalist($url = '',
                            $max_tags = 0,
                            $cloudtype = '') {
    if ($this->html) {
      return $this->html;
    }

    if (! $this->xml) {
      $this->getData($url ? $url : $this->url);
    }

    if ($this->is_valid()) {
      $meta_string = '';
      $exml = $this->xml_DOM();
      $elems = $exml->getElementsByTagName('url');
      if ($elems->length > 0) {
        foreach ($elems as $unode) {
        $meta_string .= 
          '<meta name="DC.Identifier" scheme="URI" content="'
          . $unode->textContent
          . '"/>' . "\n";
        }
        $this->html = $meta_string;
        return $meta_string;
      }
    }
  }
}