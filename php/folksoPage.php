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
include_once('folksoPageData.php');

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
 * Print the current page's tag cloud.
 * 
 * Wrapper function for $p->format_cloud(). If no data is found, or
 * there is an error of some kind cloud() silently does nothing.
 */
 public function basic_cloud() {
   $cloud = $this->format_cloud();
   if (($cloud['status'] == 200) ||
       ($cloud['status'] == 304)) {
     print $cloud['html'];
   }
 }

public function getTaglist($res) {
  $fc = new folksoClient('localhost',
                         'resource.php',
                         'GET');
  $fc->set_getfields(array('folksores' => $res,
                           'folksodatatype' => 'xml'));

  $result = $fc->execute();
  return array('status' => $fc->query_resultcode(),
               'result' => $result);

}

public function resourceMetas($url = NULL) {

  $r = $this->getTaglist($url ? $url : $this->curPageUrl());
  $ret = array('status' => $r['status']);

  if (($r['status'] == 200) ||
      ($r['status'] == 304)) {
    
    $metas_xml = new DOMDocument();
    $metas_xml->loadXML($r['result']);

    $xpath = new DOMXpath($metas_xml);

    //$tag_princ is a DOMNodelist object
    $tag_princ = $xpath->query('//taglist/tag[metatag="Sujet principal"]');

    $principal_tags = array();
    if ($tag_princ->length > 0) {
      foreach ($tag_princ as $element) {
        $tagname = $element->getElementsByTagName('display');
        $principal_tags[] = $tagname->item(0)->textContent;
      }
      $ret['result'] = implode(' ', $principal_tags);
      $ret['array'] = $principal_tags;
      $ret['meta_keywords'] = 
        '<meta name="keywords" content="'
        . $ret['result']
        . '"/>';
    }
  }
  return $ret;
}

}

?>