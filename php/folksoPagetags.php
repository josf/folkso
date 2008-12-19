<?php

  /**
   * Tag lists for use through folksoPage and more specifically
   * folksoPageData.
   * 
   *
   * @package Folkso
   * @subpackage webinterface
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */
require_once('folksoPageDataMeta.php');

  /**
   * @package Folkso
   */
class folksoPagetags extends folksoTagdata {
  public $url;
  public $xml;
  public $xml_dom;
  public $html;
  public $status;
  public $loc;


  /**
   * Retrieves an xml list of  tags and sets the $this->ptags object.
   * 
   * @param A resource, either an id or a URL. Default is to use $this->url.
   * @returns folksoPagetags with only the xml part ($rm->xml).
   */
  public function getData($url = '', $max = 0) {
    $fc = new folksoClient($this->loc->db_server,
                           $this->loc->get_path . 'resource.php',
                           'GET');
    $fc->set_getfields(array('folksores' => $url ? $url : $this->url,
                             'folksodatatype' => 'xml'));

    if (is_numeric($max) &&
        ($max > 0)) {
      $fc->add_getfield('limit', $max);
    }

    $result = $fc->execute();
    $status = $fc->query_resultcode();
    $this->store_new_xml($result, $status);
    return $this;
  }


  /**
   * Retreives a list of tags for a given resource that are marked
   * "Sujet principal". 
   * 
   * With the "non_principal_fallback" option set to TRUE, in the
   * event that there are no "sujet principal" tags, all the tags will
   * be used instead.
   *
   * (There should probably be a parameter for the string "Sujet
   * principal".)
   *
   * @param $url Optional. Defaults to current page.
   * @param $non_principal_fallback boolean Get all tags if there are NO "sujet principal".
   * @returns folksoPageDataMeta object
   */
  public function buildMeta($url = NULL, 
                            $max_tags = 0, 
                            $non_principal_fallback = NULL) {

    $mt = new folksoPageDataMeta();
    $this->getData($url); /** could set a maximum here, but instead we can
                              set that at display time, since we might be 
                              reusing data. 

                              NB: if $url is NULL, getData() will use
                              $this->url anyway.
                          **/
    
    if ($this->is_valid()) {
      $xpath = new DOMXpath($this->xml_DOM()); // reusing existing DOM, maybe

      //$tag_princ is a DOMNodelist object
      $tag_princ = $xpath->query('//taglist/tag[metatag="Sujet principal"]');

      // 'Sujet principal' only
      if ($tag_princ->length > 0) {
        foreach ($tag_princ as $element) {
          $tagname = $element->getElementsByTagName('display');
          $mt->add_principal_keyword($tagname->item(0)->textContent);
        }
      }
      // All tags, when no 'sujet principal' is found.
      $all_tags = $xpath->query('//taglist/tag');
      if ($all_tags->length > 0) {
        foreach ($all_tags as $element) {
          $tagname = $element->getElementsByTagName('display');
          $mt->add_all_tags($tagname->item(0)->textContent);
        }
      }
    }
    return $mt;
  }



  }
?>