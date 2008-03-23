<?php

class folksoIndexCache {

  var $cachedir;
  var $cache_prefix = 'folksoindex-';
  var $cache_suffix = '.cache';
  var $cache_file_limit;

  function folksoIndexCache ($dir, $cache_prefix, $cache_suffix, $file_limit) {
    $this->cachedir = $dir;
    $this->cache_prefix = $cache_prefix;;
    $this->cache_suffix = $cache_suffix;
     $this->cache_file_limit = $cache_file_limit ? $cache_file_limit : 100;
      

  }




?>