<?php
  /**
   *
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @package Folkso
   * @subpackage webinterface
   */
  /**
   * Metadata information pertaining to a given resource. These
   * objects are to be part of a folksoPageData object.
   * @package Folkso
   */
class folksoPageDataMeta  {

  /**
   * An array of the meta keyword tags. Usually this means only the
   * 'Sujet principal' tags.
   */
  public $keywords;

  /**
   * An array of all the tags associated with a given resource.
   */
  public $alltags;

  public $e13;

  public function __construct() {
    $this->array = array();
  }

  /**
   * Utility method for adding new keywords.
   *
   * @param $word string
   */
  public function add_principal_keyword($word) {
    if (is_string($word)) {
      $this->keywords[] = $word;
    }
  }

  /**
   * Utility method for adding new tags to $mt->alltags.
   *
   * @param $word string.
   */
  public function add_all_tags($word) {
    if (is_string($word)) {
      $this->alltags[] = $word;
    }
  }

  /**
   * Returns a formatted <meta name="keywords".../> tag. If no tags
   * are in $rm->array, returns an empty string.
   */
  public function meta_keywords() {

    if (count($this->keywords) > 0) {
      return '<meta name="keywords" content="'
        . implode(' ', array_map(array($this, 
                                       'quote_escape'), 
                                 $this->keywords))
        . '"/>';
    }
    else {
      return '';
    }
  }

  public function  quote_escape ($str) {
      return preg_replace('/"/', '\\"', $str);
    }

  /**
   * Returns a comma-separated list of keyword tags.
   */
  public function meta_textlist() {
    if (count($this->keywords) > 0) {
      return implode(', ', $this->keywords);
    }
  }

  /**
   * @return string Comma separated list of tags.
   */
  public function meta_description_textlist ()  {
    if (count($this->alltags) > 0) {
      return implode(', ', $this->alltags);
    }
  }

  /**
   * Acces to EAN-13 DC.Identifier function. 
   * 
   * This is just here for coherence.
   */
  public function ean13_dc_identifiers() {
    if ($this->e13 instanceof folksoEanList) {
      return $this->e13->ean13_dc_metalist();
    }
  }
}

?>