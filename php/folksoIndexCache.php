<?php

class folksoIndexCache {

  var $cachedir;
  var $cache_prefix = 'folksoindex-';
  var $cache_suffix = '.cache';
  var $cache_file_limit;

  //private!
  var $dirhandle;

  function folksoIndexCache ($dir, $cache_prefix, $cache_suffix, $file_limit) {
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
    if (!($cachedir_handle = opendir($this->cachedir))){
      trigger_error("cannot open cache directory: $this->cachedir", E_USER_ERROR);
      return 0;
    }
    $this->dirhandle = $cachedir_handle;
    return $this->dirhandle;
  }
  
  function close_dirhandle () {
    closedir( $this->dirhandle);
  }

  function data_to_cache ($string) {
    if (!(isset($string)) or ($string == '')) {
      return 0;
    }
    $cfilename = $this->new_cache_filename();
    $handle = fopen($this->cachedir . $cfilename, 'w');
    fwrite($string, $handle);
    fclose($handle);
    return($cfilename);

  } 
    
  function new_cache_filename () {
    return $this->cache_prefix . sprintf('%09d', rand(0, 999999999)) . $this->cache_suffix;
  }

  }

?>