<?php


include_once('folksoClient.php');
include_once('folksoFabula.php');
include_once('folksoPage.php');

class folksoPageData {

  /**
   *  String HTML version of whatever is asked for.
   */
  public $html;

  /**
   * Result status 
   */
  public $status;

  /**
   * Title 
   */
  public $title;

  public function _construct() {
    


  }


  /**
   * Returns what should be the real URL of the current page.
   */
  public function curPageURL() {
    if (strlen($this->url) > 8) {
      return $this->url;
    }

    $pageURL = 'http';
    $server_name = trim($_SERVER["SERVER_NAME"]);

    /* for some reason, we have been getting urls like :
      
      http://xn--   example.com. 

      Until I find out why, we are going to stupidly check for this. */

    if (strpos($server_name, ' ') > 0) {
      $server_name = substr($server_name, 
                            (strpos($server_name, ' ')));
    }

    if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
      $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
      $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    $this->url = $pageURL;
    return $pageURL;
  }


  /**
   * Backend function to the cloud() function. This gets the information
   * which is already formatted (html). Returns an assoc. array with the
   * result code (404 in case the resource is not found, 204 if no tags)
   * and the tag cloud itself.
   *
   * @returns array array('status' => 204, 'result' => CLOUD)
   */
  private function get_cloud($url) {

    $fc = new folksoClient('localhost', 
                           $this->loc->server_web_path . 'resource.php',
                           'GET');
    $fc->set_getfields(array('folksoclouduri' => 1,
                             'folksores' => $url,
                             'folksodatatype' => 'xml'));

    $result = $fc->execute();

    return array('status' => $fc->query_resultcode(),
                 'result' => $result);
  }

  /**
   * Retreives and formats a tag cloud for the current page. 
   *
   * @param $a_url string (Optional) The url for which a cloud should be
   * made. Default is to use the current page.
   *
   * @returns string An html tag cloud, ready to be outputted. 
   */
  public function format_cloud($a_url = NULL) {
    $url = '';
    if ($a_url) {
      $url = $a_url;
    } 
    else {
      $url = $this->curPageURL(); 
    }

    $r = $this->get_cloud($url);

    if ($r['status'] == 200) {
      $cloud_xml = new DOMDocument();
      $cloud_xml->loadXML($r['result']);

      $xsl = new DOMDocument();
      $xsl->load($this->loc->xsl_dir . "publiccloud.xsl");

      $proc = new XsltProcessor();
      $xsl = $proc->importStylesheet($xsl);
      $proc->setParameter('', 
                          'tagviewbase', 
                          $this->loc->server_web_path . 'tagview.php?tag=');
      $cloud = $proc->transformToDoc($cloud_xml);

      return array('html' =>  $cloud->saveXML(),
                   'status' => $r['status']);

    }
    else {
      return array('html' => '',
                   'status' => $r['status']);
    }
  }


  /**
   * Backend to public_tag_resource_list()
   */
  private function resource_list($tag) {

    $fc = new folksoClient('localhost',
                           $this->loc->server_web_path . 'tag.php',
                           'GET');
    $fc->set_getfields(array('folksotag' => $tag,
                             'folksofancy' => 1,
                             'folksodatatype' => 'xml'));

    $result = $fc->execute();
    return array('status' => $fc->query_resultcode(),
                 'result' => $result);

  }

  /**
   * Given xml resource list as DOM document, returns the "tag" element
   * as a string.
   *
   * @param $dom DOMDocument
   * @returns string or empty string if no title is found.
   */
  private function getTitle($dom) {
    $elems = $dom->getElementsByTagName("tagtitle");
    if ($elems->length > 0) {
      return $elems->item(0)->textContent;
    }
    else {
      return '';
    }
  }


  /**
   * Returns an assoc array containing ['html'] :an html list of
   * resources associated with a given tag, and ['status'] the http
   * status. Printing is left to the calling page.
   *
   * @param $tag Either a tag name or a tag id.
   */
  public function public_tag_resource_list($tag) {

    $r = $this->resource_list($tag);
  
    if (($r['status'] == 200) ||
        ($r['status'] == 304)){
    
      $taglist_xml = new DOMDocument();
      $taglist_xml->loadXML($r['result']);

      $title = $this->getTitle($taglist_xml);

      $xsl = new DOMDocument();
      $xsl->load($this->loc->xsl_dir . "public_resourcelist.xsl");
    
      $proc = new XsltProcessor();
      $xsl = $proc->importStylesheet($xsl);
      $taglist = $proc->transformToDoc($taglist_xml);

      return array('html' => $taglist->saveXML(),
                   'status' => $r['status'],
                   'title' => $title);

    }
    elseif ($r['status'] == 204) {
      return array('html' => '<p>Aucune ressource n\'est  associée à ce tag.</p>',
                   'status' => $r['status'],
                   'title' => "");

    }
    elseif ($r['status'] == 404) {
      return array('html' => '<p>Tag non trouvé.</p>',
                   'status' => $r['status'],
                   'title' => "Tag non trouvé.");

    }
    else {
      return array('html' => '<p>Erreur. Excusez-nous.</p>',
                   'status' => $r['status'],
                   'title' => 'Erreur de tag');
    }
  }


}