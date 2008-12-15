<?php
  /**
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */

require_once('folksoClient.php');
require_once('folksoFabula.php');
require_once('folksoPageData.php');
require_once('folksoPageDataMeta.php');

/**
 * A class that regroups the functions that might be called from a
 * page: presentation, certain database interaction, tag clouds etc.
 * Ideally, a page should be able to include this library alone in
 * order to access all the tag and resource functionalities.
 *
 * @package Folkso
 */
class folksoPage {

  /**
   * folksoPageData object. All of the available information about the
   * current resource.
   */
  public $pdata;

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

  public function __construct($url = '') {
    $this->loc = new folksoFabula();
    if ($url) {
      $this->url = $url;
    }
    else {
      $this->url = $this->curPageURL();
    }
    $this->pdata = new folksoPageData($this->url);
  }

  /**
   * Wrapper function for producing the contents of a <meta> keyword
   * tag. If no tags are associated with the current page (or it is
   * not indexed), this function returns false. Otherwise, it provides
   * a string containing a comma separated list of keywords.
   *
   * @return string or boolean
   * @param $url string This is really for testing only and is not meant to be used.
   */
  public function keyword_list($url = '') {
    $mt = $this->pdata->prepareMetaData($url);

    if ($this->pdata->ptags->is_valid()) {
      return $mt->meta_textlist();
    }
    else {
      return false; // no tags or no resource.
    }
  }

  /**
   * Wrapper for $mt->meta_keywords(). 
   * Returns a complete <meta
   * name="keywords"...> element based on tags marked as "Sujet
   * principal".
   * 
   * @param $url optional default '' The url or id of the resource in question.
   */
  public function meta_keywords($url = '') {
    $mt = $this->pdata->prepareMetaData($url); /** this is probably
                                                  cached, no need to
                                                  worry **/

    if ($this->pdata->ptags->is_valid()) {
      return $mt->meta_keywords();
    }
  }

  /**
   * Return the current page's tag cloud.
   * 
   * Wrapper function for $p->format_cloud(). If no data is found, or
   * there is an error of some kind cloud() silently does nothing.
   *
   * @param $max_tags int The maximum number of tags to be included in
   * the cloud. Default is 0, which means that no limit will be
   * applied.
   */
  public function basic_cloud($url = '', $max_tags = 0) {
    $cloud = $this->pdata->prepareCloud($url, $max_tags);
    return $cloud->html;
  }


  /**
   * Returns a string consting of a comma separated list of all the
   * tags associated with the given resource.
   */
  public function DC_description_list($url = ''){
    $mt = $this->pdata->prepareMetaData($url ? $url : $this->url);

    if ($this->pdata->ptags->is_valid()) {
      return $mt->meta_description_textlist();
    }
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
    $title = stripslashes(stripslashes(stripslashes($page_title)));
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
    $fc->execute();
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
   * This looks deprecated...
   * 
   * @param $tag Either a tag name or a tag id.
   * @returns folksPageData
   */
  public function public_tag_resource_list($tag) {

    /* $r is a folksoPageData object*/
    $r = $this->resource_list($tag);
  
    if ($r->is_valid()) {
    
      $taglist_xml = new DOMDocument();
      $taglist_xml->loadXML($r->xml);

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
    $r = new folksoPageData($fc->query_resultcode());
    $r->xml = $result;
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
   * Returns a pre-formatted string: <meta name="keywords"
   * content="..."/> based on the tags associated with the current
   * page.
   *
   * @param $fallback boolean Default is FALSE. If TRUE, include all
   * tags if there are no tags "sujet principal" associated with the
   * current URL.
   * @param $max integer Maximum number of tags to include. Defaults to 20. 0 means no limit.
   *
   * @returns string.
   */

}

?>