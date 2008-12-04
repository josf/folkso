<?php


class folksoPageDataMeta  {

  /**
   * An array of the meta data tags.
   */
  public $array;

  public __construct() {
    $this->array = array();
  }

  /**
   * Utility method for adding new keywords.
   *
   * @param $word string
   */
  public add_keyword($word) {
    if (is_string($word)) {
      $this->array[] = $word;
    }
  }

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

  /**
   * Returns a comma-separated list of tags.
   */
  public function meta_textlist() {
    if (count($this->array) > 1) {
      return implode(', ', $this->array);
    }
  }
}

?>
