<?php

class folksoIndexCache {

  var $cachedir;
  var $cache_prefix = 'folksoindex-';
  var $cache_suffix = '.cache';
  var $cache_file_limit;

  //private!
  var $dh;

  function folksoIndexCache ($dir, $cache_prefix = 'folksoindex-', 
                             $cache_suffix = '.cache', 
                             $file_limit = 100) {
    $this->cachedir = $dir;
    $this->cache_prefix = $cache_prefix;;
    $this->cache_suffix = $cache_suffix;
    $this->cache_file_limit = $cache_file_limit ? $cache_file_limit : 100;
   
    //make sure we have trailing '/'
    if (!(substr($this->$cachedir, -1) == '/')) {
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
    if (!$this->dh) {
      if (!($this->dh = opendir($this->cachedir))) {
        trigger_error("cannot open cache directory: $this->cachedir", E_USER_ERROR);
        return 0;
      }
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
      trigger_error("failed to open cachedir $this->cachedir", E_USER_ERROR);
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
    while ($afile = readdir( $this->dirhandle() )){
      echo "<p>afile is " . $afile . " and substr give ". substr($afile, 0, strlen($this->cache_prefix)). "</p>";

      if (($this->is_cache_file($afile)) and
          ($data = file_get_contents($this->cachedir . $afile))) {
        array_push( $an_array, $data);
        unlink($this->cachedir.$afile);
      }
      else {
        continue; // just here to say that we don't complain if we can't
                  // read the file. get it next time.
      }
    }
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

} //end of class



?>