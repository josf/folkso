<?php

  /**
   * All local, site-specific variables should be stored in a class
   * extending this one: database connection information, file
   * locations, including how links should be built.
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

  }
?>
