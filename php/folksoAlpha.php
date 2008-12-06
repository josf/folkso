<?php
  /**
   * 
   * Alphabets for grouping letters.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */
  /**
   * @package Folkso
   */
class folksoAlpha {

  public $alphabet = array(
                           "a" => array("a", "à", "ä", "â", "æ"),
                           "c" => array("c", "ç"),
                           "e" => array("e", "é", "è", "ê", "ë"),
                           "i" => array("i", "î", "ï"),
                           "o" => array("o", "ô", "ö"),
                           "u" => array("u", "ü", "û", "ù"));

  /**
   * For the given letter, returns an array containing either the same
   * letter, or all of the letters that should be searched at the same
   * time.
   *
   * Returns 'false' on bad input (not a string).
   */
  public function lettergroup ($letter) {
    $let = substr($letter, 0, 1);
    if (! is_string($let)) {
      return false;
    }

    /**   **/
    if (! array_key_exists($let, $this->alphabet)) {
      return array($let);
    }
    else {
      return $this->alphabet[$let];
    }
  }


  /**
   * Returns a string like "(column like 'a%') or (column like 'b%')
   * or ..."
   *
   * @param letters Array 
   * @param column string. The name of the SQL column we are comparing with.
   * @returns string.
   */
  public function SQLgroup ($letters, $column) {
    if (count($letters) == 1) {
      return "$column LIKE '" . $letters[0] . "%'";
    }
    elseif (count($letters) > 1) {
      return 
        "($column LIKE '"
        . implode("%') OR ($column LIKE '", $letters)
        . "%')";
    }
    else {
      trigger_error("Invalid arguments in SQLgroup(). Need an array with at least one letter",
                    E_USER_WARNING);
    }
  }

  } 
