<?php

require_once('folksoPageData.php');

class folksoPageDataMeta extends folksoPageData {

  /**
   * An array of the meta data tags.
   */
  public $array;

  /**
   * Returns a formatted <meta name="keywords".../> tag. If no tags
   * are in $rm->array, returns an empty string.
   */
  public function meta_keywords() {

    if (count($this->array) > 1) {
      return '<meta name="keywords" content="'
        . implode(' ', $this->array)
        . '"/>';
    }
    else {
      return '';
    }
  }
}

?>
