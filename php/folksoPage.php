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
require_once('folksoTagRes.php');
require_once('folksoRelatedTags.php');
require_once('folksoSession.php');
/**
 * A class that regroups the functions that might be called from a
 * page: presentation, certain database interaction, tag clouds etc.
 * Ideally, a page should be able to include this library alone in
 * order to access all the tag and resource functionalities.
 *
 * Most of the data display functions are written in such a way that
 * if they are called once, the data retreived and formatted is
 * cached. This means that, at least for now, you cannot have more
 * than one type of cloud (basic, popularity, date) per folksoPage
 * object. (This could be changed in future versions if necessary.) 
 * 
 * @package Folkso
 */
class folksoPage {

  /**
   * folksoPageData object. All of the available information about the
   * current resource. Including EAN-13 related information.
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

  /**
   * Tag Resource object for displaying lists of resources.
   *
   * This goes beyond the typical resource oriented use of this class
   * to allow us to build resource lists around a single tag while
   * taking advantage of all our existing code. Sorry if this is too
   * much of a hack as regards OO practices. Maybe someday it will
   * move on to its own package, but for now I would like folksoPage
   * be the only necessary interface.
   *
   */
  public $tr;

  /**
   * The title of the page, when page is a tag page, ie. one tag and a
   * list of resources.
   */
  public $title;

  /**
   * folksoDBconnect object
   */
  public $dbc;

  /**
   * folksoSession object
   */
  public $session;

  /**
   * folksoUser object
   */
  public $user;


  /**
   * @param $url string optional, defaults to ''.  
   * @param $cookie_arg string optional For testing, supplying arbitrary cookie
   *
   * For most purposes (besides testing) there is no need to use this
   * parameter. If empty, the URL of the current page is used.
   */
  public function __construct($url = '', $cookie_arg = null) {
    $this->loc = new folksoFabula();
    if ($url) {
      $this->url = $url;
    }
    else {
      $this->url = $this->curPageURL();
    }
    $this->pdata = new folksoPageData($this->url);
    
    $cookie = $_COOKIE['folksosess'] ? $_COOKIE['folksosess'] : $cookie_arg;
    if ($cookie) {
      $this->dbc = $this->loc->locDBC();
      $this->session = new folksoSession($this->dbc);

      if ($this->session->validateSid($cookie)) {
        $this->session->setSid($cookie);
        if ($this->session->status()) {
          $this->user = $this->session->userSession();
        }
      }
    }
  }


  /**
   * @param $content
   */
   public function jsHolder ($content) {
     $begin = '<script type="text/javascript">' . "\n";
     $end = "\n" . '</script>';
     if (is_string($content) && (strlen($content) > 0)) {
       return $begin . $content . $end;
     }
     elseif (is_array($content)) {
       return $begin . implode("\n", $content) . $end;
     }
     return false;
   }
  

   /**
    * Returns javascript string to indicate current login status to
    * folksonomie.js
    *
    * @param $var String optional The variable to use instead of fK.myfab.loginsStatus
    */
    public function fKjsLoginState ($var = null) {
      $varname = $var ? $var : 'fK.myfab.loginStatus';
      if ($this->user) {
        return $varname . " = true;";
      }
      return $varname . " = false;";
    }
   

    /**
     * @param 
     */
     public function tagbox () {
       return '<div id="folksocontrol">'
         . '<a href="#" class="fKLoginButton">Identifiez-vous via OpenID</a><br/>'
         . '<a href="' . $this->loc->account_creation_url 
         . '">Connectez-vous pour la première fois</a>'
         . '<input type="text" class="fKTaginput" length="20"></input>'
         . '<a href="#" class="fKTagbutton">Tagger</a>'
         . '</div>';
    
     }
    
     /**
      * Access to facebook connect code (javascript and xml), if this
      * variable exists in fKLocal->snippets->facebookLoginCode
      */
     public function facebookLoginCode() {
       if ($this->loc->snippets['facebookLoginCode']) {
         return $this->loc->snippets['facebookLoginCode'];
       }
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


  public function ean13_dc_identifier($url = '') {
    $this->pdata->prepareMetaData($url);
    return $this->pdata->e13->ean13_dc_metalist();
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


  public function popularity_cloud($url = '', $max_tags = 0) {
    $cloud = $this->pdata->prepareCloud($url, $max_tags, 'bypop');
    return $cloud->html;
  }

  public function date_cloud($url = '', $max_tags = 0) {
    $cloud = $this->pdata->prepareCloud($url, $max_tags, 'bydate');
    return $cloud->html;
  }

  /**
   * Delete the current cloud object ($page->pdata->cloud). This is to
   * be able to reload new kinds of clouds.
   */
  public function cloud_reset(){
    $this->pdata->cloud = null;
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

    $fc = new folksoClient($this->loc->web_url,
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

    $fc = new folksoClient($this->loc->web_url,
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


  private function title() {
    if (($this->pdata->cloud instanceof folksoCloud) &&
        ($this->pdata->cloud->is_valid())) {
      return $this->pdata->cloud->getTitle();
    }
  }

  /**
   * Tag resource list.
   * 
   * Presentation function. Everything formatted and configured,
   * including the title and error messages. Everything is returned as
   * one big string.
   */
  public function TagResources ($tag = '') {
    if (empty($tag)) {
      $tag = $this->url;
    }

    if (! $this->tr instanceof folksoTagRes) {
      $this->tr = new folksoTagRes($this->loc, $tag);
    }

    $html = '';
    $this->tr->getData($url);

    if ($this->tr->is_valid()) {
//      $html .= '<h2 class="tagtitle">' . $this->tr->title() . "</h2>\n";
      $html .= $this->tr->resList();
    }
    elseif ($this->tr->status == 204) {
      $html .= '<h2 class="tagtitle">' . $this->tr->title() . "</h2>\n";
      $html .= '<p>Aucune ressource n\'est associée à ce tag.</p>';
    }
    elseif ($this->tr->status == 404) {
      $html .= '<h2 class="tagtitle">Tag non trouvé</h2>';
      $html .= '<p>Le tag demandé ne semble pas exister.</p>';
    }
    else {
      $html .= '<h2 class="tagtitle">Erreur</h2>';
      $html .= '<p>Il y a eu une erreur quelque part.</p>';
    }
    $this->title = $this->tr->title();
    return $html;
  }

  /**
   * @param $tag
   * @param String HTML formatted tag cloud of related tags
   */
   public function RelatedTags ($tag = null) {
     if (! $tag) {
       $tag = strip_tags(substr($_GET['tag'], 0, 255));
     }
     
     $rt = new folksoRelatedTags($this->loc, $tag);
     return $rt->buildCloud();
   }
  

   /**
    * Convience access to folksoLocal->javascript_path
    */
    public function javascript_path () {
      return $this->loc->javascript_path;
    }
   


}

?>