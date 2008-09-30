<?php

  /**
   * A class that regroups the functions that might be called from a
   * page: presentation, certain database interaction, tag clouds etc.
   * Ideally, a page should be able to include this library alone in
   * order to access all the tag functionalities.
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
                           $this->loc->server_web_path . 'resource.php',
                           'POST');
    $fc->set_postfields(array('folksovisit' => 1,
                              'folksores' => $our_current_url,
                              'folksourititle' => $title ? $title : ''));
    //print $fc->build_req();

    $fc->execute();
    // print $fc->query_resultcode();
  }


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
private function get_cloud() {
  $url = $this->curPageURL();  /* we use our own variable to retreive
                                  a cached version if possible */

  $fc = new folksoClient('localhost', 
                         $this->loc->server_web_path . 'resource.php',
                         'GET');
  $fc->set_getfields(array('folksoclouduri' => 1,
                           'folksores' => $url));

  $result = $fc->execute();
  return array('status' => $fc->query_resultcode(),
               'result' => $result);
}

public function cloud() {
  $r = $this->get_cloud();
  if ($r['status'] = 200) {
    return $r['result'];
  }
  else {
    return;
  }
}


  }

?>