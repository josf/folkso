<?php

  /**
   * All local, site-specific variables should be stored in a class
   * extending this one: database connection information, file
   * locations, including how links should be built.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */

require_once 'folksoDBconnect.php';
  /**
   * @package Folkso
   */
abstract class folksoLocal {

  public $db_server;
  public $db_user;
  public $db_password;
  public $db_database_name;

  /**
   * Right now assuming that this ends with a slash.
   */
  public $xsl_dir;



  /**
   * The web server's url. Set with setServerUrl() which will add the
   * 'http://' part if necessary
   */
  public $web_url;
 
  /**
   * The domain to be used for session cookies. Should be something
   * like '.example.com'
   */
  public $web_domain;


  /**
   * The path part of the uri where tag.php and resource.php are to
   * be accessed. 
   *
   * For example: '/folkso/' if tag.php is located at
   * '/folkso/tag.php'. 
   *
   * Must not include the hostname.
   *
   */
  public $server_web_path;

  /**
   * Path for access to publicly available tag.php & resource.php (GET
   * functions)
   */
  public $get_path;

  /**
   * Path for access to POST functions (tag.php, resource.php),
   * requiring authentication.
   */
  public $post_path;

  /**
   * For use when indexing new resources.
   *
   * Array containing strings that, when found in the new URI, prevent
   * that URI from being indexed.
   */
  public $visit_ignore_url;
 
  /**
   * Like $visit_ignore_url but used against page titles.
   */
  public $visit_ignore_title;

  /**
   * List of strings of allowed user agents. 'Mozilla' is already part
   * of the default but you can add more UAs here.
   */
  public $visit_valid_useragents;

  /**
   * Target for redirects. Do not use this variable for redirects, use
   * the loginPage() method instead to include a return path.
   */
  public $loginPage;

  /**
   * Where the scripts are.
   */
  public $javascript_path;

  /**
   * Simple setter function. 
   * 
   * @params string $path : the path part of the uri for your
   * tagserver.
   *
   * A trailing '/' will be added if not already present.
   */  
  public function set_server_web_path ($path) {
    if (substr($path, -1) != '/') {
      $path = $path . '/';
    }
    $this->server_web_path = $path;
  }

  /** 
   * Very simple getter function.
   */
  public function get_server_web_path () {
    return $this->server_web_path;
  }

  public function WebPathJS() { 
    $return = 
      "\n/** defined in folksoLocal.php **/\n"
    .   'if (!  document.hasOwnProperty("folksonomie")) {' . "\n"
    . "\tdocument.folksonomie = new Object();\n"
    . "}\n";
    $return .= 
      'document.folksonomie.getbase = "' . $this->get_path . '";' . "\n";

    $return .= 
      'document.folksonomie.postbase = "' . $this->post_path . '";'. "\n";

    $return .= 
      'document.folksonomie.webbase = "' . $this->server_web_path . '";'. "\n";

    return $return;
  }

  /**
   * Very minimal validation. We just make sure that the url starts
   * with http:// and add it if missing. We also remove trailing
   * slash.
   *
   * @param $url
   */
   public function setServerUrl ($url) {
     $new_url = '';
     if (substr($url, 0, 7) == 'http://') {
       $new_url = $url;
     }
     else {
       $new_url = 'http://' . $url;
     }
     if (substr($new_url, -1, 1) == '/') {
       $new_url = substr($new_url, 0, strlen($new_url) - 1);
     }
     $this->web_url = $new_url;
     return $this->web_url;
   }

   /**
    * Convenience method for producing DBconnect objects
    */
    public function locDBC () {
      return new folksoDBconnect($this->db_server,
                                 $this->db_user,
                                 $this->db_password,
                                 $this->db_database_name);
   
    }
    /**
     * Provides target for login redirect. Depending on the data
     * provided by $loginPage, tries to guess the correct URL.
     * 
     * Destination string part not implemented yet.
     *
     * @param $dest String Return URL (where to go after login)
     * @return String formatted url for login redirects
     */
     public function loginPage ($dest = null) {
       if (empty($this->loginPage)) {
         throw new insufficientDataException('Missing login page information in system');
       }

       $dest_part = '';
       if ($dest) {
         $dest_part = '?dest=' . $dest;
       }         

       if (substr($this->loginPage, 0, 4) == 'http') {
         return $this->loginPage;
       }
       elseif (strpos($this->loginPage, '/') !== false) {
           if (substr($this->loginPage, 0, 1) == '/')  {
             return $this->web_url . $this->loginPage;
           }
           else {
             return $this->web_url . '/' . $this->loginPage;
           }
         }
       else {
         return $this->web_url . '/' . $this->get_server_web_path() . $this->loginPage;
       } 
     }
    
  
  }
?>
