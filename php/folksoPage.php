<?php

  /**
   * A class that regroups the functions that might be called from a
   * page: presentation, certain database interaction, tag clouds etc.
   * Ideally, a page should be able to include this library alone in
   * order to access all the tag and resource functionalities.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */

include_once('folksoClient.php');
include_once('folksoFabula.php');


class folksoPage {

  /**
   * Site specific information. Leaving this private since there can
   * be sensitive stuff here.
   */
  private $loc;

  /**
   * The url of the current page. Set whenever $this->curPageURL() is
   * called.
   */
  public $url;

  public function __construct() {
    $this->loc = new folksoFabula();
  }


  /**
   * Report this page to the tag system. A new resource is created if
   * the page has not already been reported, otherwise the page view
   * statistic is incremented.
   *
   * This information is cached in order to reduce the number of
   * database connections.
   */
  public function visit_resource ($page_title = '') {
    $title = stripslashes(stripslashes(stripslashes($page_titre)));
    $our_current_url = $this->curPageURL(); //ridiculous var name to avoid namespace problems

    if ($this->ua_ignore($_SERVER['HTTP_USER_AGENT'], 
                         $this->loc->visit_ignore_useragent)) {
      return;
    }

    if ($this->ignore_check($our_current_url, 
                            $this->loc->visit_ignore_url)) {
      return;
    }

    if ($page_title &&
        ($this->ignore_check($page_title, 
                             $this->loc->visit_ignore_title))) {
      return;
    }

    $fc = new folksoClient('localhost', 
                           $this->loc->server_web_path . 'admin/resource.php',
                           'POST');
    $fc->set_postfields(array('folksovisit' => 1,
                              'folksores' => $our_current_url,
                              'folksourititle' => $title ? $title : ''));
    //print $fc->build_req();

    $fc->execute();
    // print $fc->query_resultcode();
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
   * Check user agents against list of strings. Standard list is used
   * first, then optional site specific list.
   *
   * Note: returns false if the ua is valid and should _not_ be ignored,
   * and true if the ua _should_ be ignored.
   *
   * @return Boolean
   */
  public function ua_ignore($ua) {
    $ua_list =
      array('Mozilla', 'MSIE', 'Opera', 
            'w3m', 'Safari','Links','Lynx');

    if ((is_array($this->loc->visit_valid_useragents)) &&
        (count($this->loc->visit_valid_useragents) > 0)) {
      $ua_list = array_merge($ua_list, $this->loc->visit_valid_useragents);
    }

    foreach ($ua_list as $valid) {
      if ((strpos(strtolower($ua), strtolower($valid))) > -1)  {
        return false; // false = do not ignore
      }
    }
    return true; //no matching ua found
  }

  /**
   * Utility function.
   *
   * @param $str string A string to check against the list of strings to ignore
   * @param $ignore array The list of strings to ignore.
   * @return Boolean
   */
  private function ignore_check($str, $ignore) {
    if (!is_array($ignore)) {
      return false;
    }
    foreach ($ignore as $pattern) {
      if ((strpos(strtolower($str), strtolower($pattern))) > -1) {
        return true;
      }
    }
    return false;
  }

  /**
   * Backend function to the cloud() function. This gets the information
   * which is already formatted (html). Returns an assoc. array with the
   * result code (404 in case the resource is not found, 204 if no tags)
   * and the tag cloud itself.
   *
   * @returns array array('status' => 204, 'result' => CLOUD)
   */
  private function get_cloud($url, $max_tags = 0) {

    $fc = new folksoClient('localhost', 
                           $this->loc->server_web_path . 'resource.php',
                           'GET');
    $fc->set_getfields(array('folksoclouduri' => 1,
                             'folksores' => $url,
                             'folksodatatype' => 'xml')); 
    $fc->add_getfield('folksolimit', $max_tags));

    $result = $fc->execute();

    $p = new folksoPageData($fc->query_resultcode(),
                            $result);
    return $p;
  }

  /**
   * Retreives and formats a tag cloud for the current page. 
   *
   * @param $a_url string (Optional) The url for which a cloud should be
   * made. Default is to use the current page.
   *
   * @returns folksoPageData 
   */
  public function format_cloud($max_tags, $a_url = NULL) {
    $url = '';
    if ($a_url) {
      $url = $a_url;
    } 
    else {
      $url = $this->curPageURL(); 
    }

    // $r is a folksoPageData object
    $r = $this->get_cloud($url, $max_tags);

    if ($r->status == 200) {
      $cloud_xml = new DOMDocument();
      $cloud_xml->loadXML($r->xml);

      $xsl = new DOMDocument();
      $xsl->load($this->loc->xsl_dir . "publiccloud.xsl");

      $proc = new XsltProcessor();
      $xsl = $proc->importStylesheet($xsl);
      $proc->setParameter('', 
                          'tagviewbase', 
                          $this->loc->server_web_path . 'tagview.php?tag=');
      $cloud = $proc->transformToDoc($cloud_xml);

      $r->html = $cloud->saveXML();
    }
    return $r;
  }

  /**
   * Print the current page's tag cloud.
   * 
   * Wrapper function for $p->format_cloud(). If no data is found, or
   * there is an error of some kind cloud() silently does nothing.
   */
  public function basic_cloud($max_tags = 0) {
    $cloud = $this->format_cloud($max_tags);
    if (($cloud->status == 200) ||
        ($cloud->status  == 304)) {
      return $cloud->html;
    }
  }

  /**
   * Returns an assoc array containing ['html'] :an html list of
   * resources associated with a given tag, and ['status'] the http
   * status. Printing is left to the calling page.
   *
   * @param $tag Either a tag name or a tag id.
   * @returns folksPageData
   */
  public function public_tag_resource_list($tag) {

    /* $r is a folksoPageData object*/
    $r = $this->resource_list($tag);
  
    if ($r->is_valid()) {
    
      $taglist_xml = new DOMDocument();
      $taglist_xml->loadXML($r['result']);

      $r->title = $this->getTitle($taglist_xml);

      $xsl = new DOMDocument();
      $xsl->load($this->loc->xsl_dir . "public_resourcelist.xsl");
    
      $proc = new XsltProcessor();
      $xsl = $proc->importStylesheet($xsl);
      $taglist = $proc->transformToDoc($taglist_xml);

      $r->html = $taglist->saveXML();
    }
    elseif ($r->status == 204) {
      $r->html = '<p>Aucune ressource n\'est  associée à ce tag.</p>';
    }
    elseif ($r->status == 404) {
      $r->html = '<p>Tag non trouvé.</p>';
      $r->title = "Tag non trouvé.";
    }
    else {
      $r->html = '<p>Erreur. Excusez-nous.</p>';
      $r->title = 'Erreur de tag';
    }
    return $r;
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
    $r = new folksoPageData($fc->query_resultcode(),
                            $result);
    return $r;
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
   * 
   * @param A resource, either an id or a URL.
   * @returns folksoPageDataMeta with only the xml part ($rm->xml).
   */
  public function getTaglist($res) {
    $rm = new folksoPageDataMeta();
    $fc = new folksoClient('localhost',
                           'resource.php',
                           'GET');
    $fc->set_getfields(array('folksores' => $res,
                             'folksodatatype' => 'xml'));

    $rm->xml = $fc->execute();
    $rm->status = $fc->query_resultcode();
    return $rm;
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
   * @returns folksoPageDataMeta
   */
  public function resourceMetas($url = NULL, $non_principal_fallback = NULL) {
    $rm = $this->getTaglist($url ? $url : $this->curPageUrl());

    if ($rm->is_valid()) {
      $metas_xml = new DOMDocument();
      $metas_xml->loadXML($r->xml);
      $xpath = new DOMXpath($metas_xml);

      //$tag_princ is a DOMNodelist object
      $tag_princ = $xpath->query('//taglist/tag[metatag="Sujet principal"]');

      if ($tag_princ->length > 0) {
        foreach ($tag_princ as $element) {
          $tagname = $element->getElementsByTagName('display');
          $rm->array[] = $tagname->item(0)->textContent;
        }
      }
      elseif ($non_principal_fallback) {
        $all_tags = $xpath->query('//taglist/tag');
        
        if ($all_tags->length > 0) {
          foreach ($all_tags as $element) {
            $tagname = $element->getElementsByTagName('display');
            $rm->array[] = $tagname->item(0)->textContent;
          }
        }
      }
    }
    return $rm;
  }

  /**
   * Returns a pre-formatted string: <meta name="keywords"
   * content="..."/> based on the tags associated with the current
   * page.
   *
   * @param $fallback boolean Default is FALSE. If TRUE, include all
   * tags if there are no tags "sujet principal" associated with the
   * current URL.
   * @returns string.
   */
  public function meta_keywords($fallback = FALSE) {
    $url = $this->curPageUrl();

    $rm = $this->resourceMetas($url, $fallback);
    return $rm->meta_keywords();
  }

}

?>