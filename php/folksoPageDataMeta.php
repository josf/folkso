<?php

class folksoPageDataMeta extends folksoPageData {


  /**
   * An array of the meta data tags.
   */
  public $array;

  /**
   * Returns a formatted <meta name="keywords".../> tag.
   */
  public function meta_keywords() {
    return '<meta name="keywords" content="'
      . implode(' ', $this->array)
      . '"/>';
  }

  }

?>
