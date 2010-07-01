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
   * Parse a query string into a simple "tree", where the keys are the
   * search keywords and the values are lists of words.
   *
   * If a keyword is present but has no arguments, it is associated
   * with an empty array.
   * 
   * @param $str
   * @return Assoc array of arrays. (HoA in Perl)
   */
   public function parseString ($str) {
     $filtered = $this->cleanQueryString($str);
     
     $currentKeyWord = '';
     $result;
     $pos;

     if (! $this->keywords->isKeyWord($filtered[0])) {
       $currentKeyWord = 'default:';
       $result['default:'] = array();
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
    * @return Array List of words from parse string.
    */
    public function cleanQueryString ($str) {
      $str = strip_tags(strtolower($str));

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
     * Generate a where clause from a parsed query string. All parts
     * are chained together with 'OR'.
     *
     * $column_equivs establishes the relationships between search
     * query keywords and table column names. The value passed in must
     * be an assoc array of query keywords. Their values can be either
     * strings (simply the name of the column in the query you want to
     * build) or arrays of column-name strings. In the second case,
     * each column will be compared.
     *
     * @param $tree
     * @param $column_equivs
     * @param folksoDBinteract $i 
     */
    public function whereClause ($tree, $column_equivs, folksoDBinteract $i) {
       
      $where_elements = array();
      foreach ($column_equivs as $kw => $val) {
        if (is_array($tree[$kw])) {

          /* Multiple columns in column equivs */
          if (is_array($val)) {
            foreach ($val as $col) {
              foreach ($tree[$kw] as $word) {
                array_push($where_elements, 
                           sprintf("%s = '%s'",
                                   $col, $i->dbescape($word)));
              }
            }
          }
          /* Single column name */
          else {
            foreach ($tree[$kw] as $word) {
              array_push($where_elements,
                         sprintf("%s = '%s'",
                                 $val, $i->dbescape($word)));
            }
          }
        }
      } 
  
      $sql = implode(' or ', $where_elements); 
      return $sql;
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
