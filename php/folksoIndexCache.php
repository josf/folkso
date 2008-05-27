<?php

  /**
   * Class for caching resource information to reduce database access.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */

class folksoIndexCache {

  var $cachedir;
  var $cache_prefix = 'folksoindex-';
  var $cache_suffix = '.cache';
  var $cache_file_limit;

  //private!
  var $dh;

  function folksoIndexCache ($dir, $file_limit = 100, $cache_prefix = 'folksoindex-', 
                             $cache_suffix = '.cache')
                             {
    $this->cachedir = $dir;
    $this->cache_prefix = $cache_prefix;;
    $this->cache_suffix = $cache_suffix;
    $this->cache_file_limit = $file_limit ? $file_limit : 4;
   
    //make sure we have trailing '/'
    $slash = '/'; //no idea why these contorsions are necessary
    $subslasher = substr($this->cachedir, -1);
    if ($subslasher != $slash) {
      $this->cachedir = $this->cachedir . '/';
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

  function dirhandle () {
    /* returns either the currently open dirhandle, or a new one */

    if (!($this->dh = opendir($this->cachedir))) {
      trigger_error("cannot open cache directory: $this->cachedir", E_USER_ERROR);
      return 0;
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
    
  function new_cache_filename () {
    return $this->cache_prefix . sprintf('%09d', rand(0, 999999999)) . $this->cache_suffix;
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


  function cache_check () {
    /* true if cache is full, false if empty */
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