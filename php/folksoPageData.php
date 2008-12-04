<?php


require_once('folksoClient.php');
require_once('folksoFabula.php');
require_once('folksoPage.php');
require_once('folksoCloud.php');
require_once('folksoPagetags.php');

class folksoPageData {
  
  /**
   * A folksoCloud object
   */
  public $cloud;
  
  /**
   * A folksoPagetags object
   */
  public $ptags;
  
  /**
   *  String HTML version of whatever is asked for.
   */
  public $html;

  /**
   * Title 
   */
  public $title;

  /**
   * Raw xml data.
   */
  public $xml; 

  /**
   * The URL of the page in question. That is, this page, the one we
   * are talking about.
   */
  public $url;

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
   * Get the raw cloud information from the server. This data is
   * stored in $this->pdata->cloud.
   *
   * @returns folksoCloud object.
   */
  public function get_cloud($max_tags = 0) {
    // We assume that there will never be a need to do more than one query.
    if ($this->cloud instanceof folksoCloud) {
      return $this->cloud;
    }

    $fc = new folksoClient('localhost', 
                           $this->loc->server_web_path . 'resource.php',
                           'GET');
    $fc->set_getfields(array('folksoclouduri' => 1,
                             'folksores' => $this->url,
                             'folksodatatype' => 'xml', 
                             'folksolimit' => $max_tags)); 
    $result = $fc->execute();
    $status = $fc->query_resultcode();
    if (! $status) {
      trigger_error('no valid status here.', E_USER_ERROR);
    }

    $this->store_new_cloud($result, $status);
    return $this->cloud;
  }

  /**
   * Integrates new cloud data.
   */
  private function store_new_cloud($xml, $status) {
    $this->cloud = new folksoCloud();
    $this->cloud->store_new_xml($xml, $status);
  }


  /**
   * Retreives and formats a tag cloud for the current page. 
   *
   * @param $url string (Optional) The url for which a cloud should be
   * made. Default is to use the current page.
   * @param $max_tags integer  (Optional). Defaults to 0, meaning get all
   * the tags.
   *
   * @returns folksoCloud
   */
  public function format_cloud($url = '', $max_tags = 0) {

    if (! $this->cloud instanceof folksoCloud) {
      $this->get_cloud($url ? $url : $this->url,
                       $max_tags);
    }

    if ($this->cloud->is_valid()) {
      $cloud_xml = new DOMDocument();
      $cloud_xml->loadXML($this->cloud->xml);

      $xsl = new DOMDocument();
      $xsl->load($this->loc->xsl_dir . "publiccloud.xsl");

      $proc = new XsltProcessor();
      $xsl = $proc->importStylesheet($xsl);

      // setting url format so that it is not hard coded in the xsl.
      $proc->setParameter('', 
                          'tagviewbase', 
                          $this->loc->server_web_path . 'tagview.php?tag=');
      $cloud = $proc->transformToDoc($cloud_xml);

      $this->cloud->html = $cloud->saveXML();
    }
    return $this->cloud;
  }


  /**
   * Sets the $this->ptags object.
   * 
   * @param A resource, either an id or a URL.
   * @returns folksoPagetags with only the xml part ($rm->xml).
   */
  public function getTaglist($max = 0) {
    if ($this->ptags instanceof folksoPagetags) {
      return $this->ptags;
    }


    $fc = new folksoClient($this->loc->db_server,
                           $this->loc->get_path . 'resource.php',
                           'GET');
    $fc->set_getfields(array('folksores' => $this->url,
                             'folksodatatype' => 'xml'));

    if (is_numeric($max) &&
        ($max > 0)) {
      $fc->add_getfield('limit', $max);
    }
    $this->ptags = new folksoPagetags();

    $result = $fc->execute();
    $status = $fc->query_resultcode();
    $this->ptags->store_new_xml($result, $status);
    return $this->ptags;
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
   * @returns folksoPageData
   */
  public function resourceMetas($url = NULL, $non_principal_fallback = NULL) {
    if (! $this->ptags instanceof folksoPagetags) {
      $this->getTaglist(); // could set a maximum here
    }
    if (! $this->mt instanceof folksoPageDataMeta) {
      $this->mt = new folksoPageDataMeta();
    }
    
    if ($this->ptags->is_valid()) {
      $metas_xml = new DOMDocument();
      $metas_xml->loadXML($this->ptags->xml);
      $xpath = new DOMXpath($metas_xml);

      //$tag_princ is a DOMNodelist object
      $tag_princ = $xpath->query('//taglist/tag[metatag="Sujet principal"]');

      // 'Sujet principal' only
      if ($tag_princ->length > 0) {
        foreach ($tag_princ as $element) {
          $tagname = $element->getElementsByTagName('display');
          $this->mt->add_keyword($tagname->item(0)->textContent);
        }
      }
      // All tags, when no 'sujet principal' is found.
      elseif ($non_principal_fallback) {
        $all_tags = $xpath->query('//taglist/tag');
        
        if ($all_tags->length > 0) {
          foreach ($all_tags as $element) {
            $tagname = $element->getElementsByTagName('display');
            $this->mt->add_keyword($tagname->item(0)->textContent);
          }
        }
      }
    }
    return $this->mt;
  }


} /* end of class */