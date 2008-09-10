<?php
  /**
   * 
   * Alphabets for grouping letters.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
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
  public function lettergroup (letter) {
    $let = substring($letter, 0, 1);
    if (! is_string($let)) {
      return false;
    }

    /**   **/
    if (! isset($alphabet[$let])) {
      return array($let);
    }
    else {
      return array($alphabet[$let]);
    }
  }


  } 