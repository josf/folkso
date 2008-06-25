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
   */
  public $server_web_path;
  



  }

?>