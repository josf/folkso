<?php

  /**
   * Class for caching resource information to reduce database access.
   *
   * This was written with the assumption that losing a little bit of
   * data is no big deal.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */

  /**
   * @package Folkso
   */
class folksoIndexCache {
  public $cachedir;
  public  $cache_prefix = 'folksoindex-';
  public $cache_suffix = '.cache';
  public $cache_file_limit;

  //private!
  private $dh;

  /**
   * Prepares variables and initializes (if necessary) the cache
   * directory in /tmp. (/tmp is hardwired in at this point.)
   * 
   * Note that $cache_prefix and $cache_suffix are important since
   * they are used to determine if a file is a cachefile or not. 
   * (We won't try to read random files that might get written to our directory.)
   * 
   * @param $dir string The directory name for the file cache (inside /tmp).
   * @param $file_limit integer Number of files to collect before emptying cache. Default 100.
   * @param $cache_prefix string Prefix for cache files. Default: folksoindex-
   * @param $cache_suffix string Default: .cache
   */
  function __construct ($dir, 
                        $file_limit = 100, 
                        $cache_prefix = 'folksoindex-', 
                        $cache_suffix = '.cache')
                             {
    $this->cachedir = $dir;
    $this->cache_prefix = $cache_prefix;;
    $this->cache_suffix = $cache_suffix;
    $this->cache_file_limit = $file_limit;
   
    //make sure we have trailing '/'
    $slash = '/'; //no idea why these contorsions are necessary
    $subslasher = substr($this->cachedir, -1);
    if ($subslasher != $slash) {
      $this->cachedir = $this->cachedir . '/';
    }
    if (!is_dir($this->cachedir) &&
        (substr($this->cachedir, 0, 4) == '/tmp')) {
      mkdir($this->cachedir, 0700);
    }
  }

  function check_filecount () {
    $cache_counter = 0;
    while ( $this_file = readdir($this->dirhandle())) {
      ++$cache_counter;
    }
    $this->close_dirhandle();
    
    if ($cache_counter > $cache_file_limit) {
      return 1;
    }
    else {
      return 0;
    }
  }

  /**
   * returns either the currently open dirhandle, or a new one 
   */
  function dirhandle () {
    if (!($this->dh = opendir($this->cachedir))) {
      trigger_error("cannot open cache directory: $this->cachedir", E_USER_ERROR);
      return;
    }
    return $this->dh;
  }
  
  function close_dirhandle () {
    if ( $this->dh ) {
      closedir( $this->dh);
      unset( $this->dh);
    }
  }

  function data_to_cache ($string) {
    if (!(isset($string)) or ($string == '')) {
      return 0;
    }
    $cfilename = $this->new_cache_filename();
    
    if (!($handle = fopen($this->cachedir . $cfilename, 'w'))) {
      trigger_error("failed to open file ". $this->cachedir. $cfilename, E_USER_ERROR);
      return 0;
    }
    else {
      fwrite( $handle, $string);
      fclose($handle);
    return($cfilename); //needed mostly for testing purposes
    }
  } 
    
  private function new_cache_filename () {
    return 
      $this->cache_prefix . 
      sprintf('%09d', rand(0, 999999999)) . 
      $this->cache_suffix;
  }

  function retreive_cache () {
    /* 
     * This also deletes the cache files as they are retreived.

     As with rest of class, we do not try to understand what is in the
     files. If it is serialized data we have to unserialize it later,
     in the calling class. Which is better anyway, since they ought to
     know what to do with it down there.*/

    $an_array = array();
    $dh = $this->dirhandle();
    while ($afile = readdir( $dh )){
      if (($this->is_cache_file($afile)) and
          ($data = file_get_contents($this->cachedir . $afile))) {
        if (!(unlink($this->cachedir.$afile))) {
            trigger_error("cannot unlink $afile", E_USER_ERROR);
          }
        array_push( $an_array, $data);
      }
      else {
        continue; // just here to say that we don't complain if we can't
                  // read the file. get it next time.
      }
    }

    $this->close_dirhandle();
    return $an_array;
  }

 function is_cache_file ($file) { // a test
    if ((is_file( $this->cachedir.$file)) and
        (substr($file, 0, strlen($this->cache_prefix)) == $this->cache_prefix)) {
      return true;
    }
    else {
      return false;
    }
  }

  /** 
   * 
   * Returns true if cache is full, false if not yet full. Full is
   * defined by $ic->cache_file_limit.
   *
   * @return Boolean
   */
   function cache_check () {
    $acounter = 0;
    $dh = $this->dirhandle();
    while ( $file = readdir( $dh )) {
      if ($this->is_cache_file($file)) {
        ++$acounter;
      }
    }
    $this->close_dirhandle();
    
    if ( $acounter > $this->cache_file_limit ) {
      return true;
    }
    else {
      return false;
    }
  }
} //end of class

?>