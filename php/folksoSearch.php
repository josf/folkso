
<?php


/**
 *
 * @package Folkso
 * @author Joseph Fahey
 * @copyright 2010 Gnu Public Licence (GPL)
 * @subpackage Tagserv
 */

  /**
   * Parsing a search query
   */
class folksoSearchQueryParser {

  public $keywords;
  public $tree;
  /**
   * @param folksoSearchKeyWordSet $fkKeys
   */
   public function __construct (folksoSearchKeyWordSet $fkKeys) {
       $this->keywords = $fkKeys;
       
       
  
   }

  /**
   * @param $str
   */
   public function parseString ($str) {
     $filtered = $this->cleanQueryString($str);
     
     $currentKeyWord = '';
     $result;
     $pos;

     if (! $this->keywords->isKeyWord($filtered[0])) {
       $currentKeyWord = 'default:';
     }

     foreach ($filtered as $word) {
       if ($this->keywords->isKeyWord($word)) {
         $currentKeyWord = $word;
         if (! is_array($result[$currentKeyWord])) {
           $result[$currentKeyWord] = array();
         }
       }
       else {
         $result[$currentKeyWord][] = $word;
       }
     }
     $this->tree = $result;
     return $this->tree;
   }

   /**
    * @param $str
    */
    public function cleanQueryString ($str) {
      $str = strtolower($str);
      $raw = array_map('trim', explode(' ', $str));
      $filtered = array_filter($raw, array($this->keywords, 'isWord'));

      /*
       * array_values necessary here, otherwise php preserves the
       * original indexes and we end up with empty items in the list.
       */
      $filtered = array_values(
                               array_filter($filtered, 
                                            array($this->keywords, 
                                                  'isNotStopWord')));

      return $filtered;
    }
   

    /**
     * Makes sure there is at least one keyword field in parse result $tree.
     */
    public function isValidTree ($tree = null) {
      $tree = $tree ? $tree : $this->tree;
      if (! is_array($tree)) {
        return false;
      }
      if (count($tree) > 0)
        return true;
      }
  }


abstract class folksoSearchKeyWordSet {

  public $keywords;

  /**
   * Ignore these. Assoc array structured like $this->keywords.
   */
  public $stopwords;

  public $minWordLength;

  public function isKeyWord($word) {
    if (array_key_exists($word, $this->keywords)) {
      return true;
    }
    return false;
  }

  /**
   * @param $word
   */
   public function isNotStopWord ($word) {
     if (array_key_exists($word, $this->stopwords)) {
       return false;
     }
     return true;
   }
  
   /**
    * @param $str
    */
   public function isWord ($str) {
     if (is_string($str) &&
         (strlen($str) > $this->minWordLength)) {
       return true;
     }
     return false;
   }


}

class folksoSearchKeyWordSetUserAdmin extends folksoSearchKeyWordSet {
  public $keywords;
  public $stopwords;

  /**
   * @param 
   */
   public function __construct () {
     $this->keywords = array('name:' => null, 'uid:' => null,
                             'fname:' => null, 'lname:' => null,
                             'recent:' => null, 'default:' => null);
     $this->stopwords = array();
     $this->minWordLength = 2;
   }
}
