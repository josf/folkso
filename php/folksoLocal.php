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
   * The path part of the uri where tag.php and resource.php are to
   * be accessed. 
   *
   * For example: '/folkso/' if tag.php is located at
   * '/folkso/tag.php'. 
   *
   * Must not include the hostname.
   *
   */
  protected $server_web_path;

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