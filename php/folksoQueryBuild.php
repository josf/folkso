<?php
 /**
   *
   * @package Folkso
   * @subpackage Tagserv
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */

  /**
   * @package Folkso
   *
   * Build SQL queries based on the options given.
   *
   * An attempt at something like a DSL for writing SQL queries.
   *
   * The primary argument, $sql_arr, is an indexed array of
   * associative arrays (array of hashes in Perl). Each assoc array
   * has this form:
   *
   * array('type' => ...
   *       'sql' => 'SELECT ... etc. ')
   *
   * The 'type' field determines whether or not the text in the 'sql'
   * field will be included or not in the query that is returned. If
   * 'type' is 'common', then the string in 'sql' will always be
   * included.
   *
   * The second argument, $thing, is the resource or tag identifier or
   * name (url for resources, tag string for tags).  Since this data
   * can either be a number or a string, we can include specific SQL
   * strings, using either 'isnum' or 'notnum' as the 'type' value.
   *
   * array('type' => 'isnum', 'sql' => 'where id = <<<x>>>');
   *
   * The value of $thing is inserted into the returned SQL query text
   * every time the sequence <<<x>>> is found in the SQL strings.
   *
   * The $thing argument is common to all of these queries, but often
   * other more diverse parameters are needed as well. These must be
   * included in the third argument to the 'build' method,
   * $arg_arr. 
   * 
   * In this case, we define "variables" that can have arbitrary names
   * (except for 'x'). For a "variable" named 'limit', we would write
   * <<<limit>>> in the SQL. These "variables" can be used both as
   * variables within the SQL itself, and to determine whether a given
   * SQL string will be included or not.
   *
   * $arg_arr is an assoc array of assoc arrays (HoH) where the keys
   * in the primary array are the names of the "variables" used in the
   * SQL strings. The value corresponding to each key is another assoc
   * array containing two keys, 'func' and 'value'. 'func' can be a
   * function that must return a boolean and is called to determine
   * whether or not a given sequence is to be included. 'value' is the
   * value that will be inserted in the place of <<<variable_name>>>,
   * whatever 'variable_name' happens to be. 
   *
   * If the value of 'func' is undefined or false, no function is
   * called to determine whether or not an SQL sequence should be
   * included. If 'value' evaluates to TRUE, the sequence is included,
   * and otherwise it is not. 
   *
   * 
   */
class folksoQueryBuild {

  public $sql;

  public function build($sql_arr, $thing, $arg_arr ) {
    $sql = '';

    foreach ($sql_arr as $chunk_arr) {
      /** if (! is_string($chunk_arr['type'])) { MIGHT BE AN ARRAY NOW!
          trigger_error("Problem with SQL building: invalid type", 
          E_USER_ERROR);
          }**/

      if ($this->includep($chunk_arr['type'], $thing, $arg_arr)) {
        $sql = $this->concatSQL($sql, $chunk_arr, $thing, $arg_arr);
      }
    }
    $sql = trim($sql);
    $this->sql = $sql;
    return $sql;
  
  }


    private function includep ($item, $thing, $arg_arr) {
      switch ($item) {
      case 'common':
        return true;
        break;
      case 'isnum':
        if (is_numeric($thing)) {
          return true;
        }
        else {
          return false;
        }
        break;
      case 'notnum':
        if (! is_numeric($thing)) {
          return true;
        }
        else {
          return false;
        }
        break;
      default:


        if (is_array($item)) {
          switch ($item[0]){
          case 'AND':
            $incl = TRUE;
            foreach (array_slice($item, 1) as $part) {
              if (! $this->includep($part, $thing, $arg_arr)) {
                $incl = FALSE;
              }
            }
            return $incl;
          case 'OR':
            $incl = FALSE;
            foreach (array_slice($item, 1) as $part) {
              if ($this->includep($part, $thing, $arg_arr)) {
                $incl = TRUE;
              }
            }
            return $incl;
          default:
            trigger_error("Invalid SQLbuild operator",
                          E_USER_ERROR);
          }
        }

        if (! array_key_exists($item, $arg_arr)) {
          trigger_error("Undefined item in SQL query building: $item", 
                        E_USER_ERROR);
        }

        if ((! $arg_arr[$item]['func']) &&
                 ($arg_arr[$item]['value'])) {
          return TRUE;
        }
        elseif (is_callable($arg_arr[$item]['func']) &&
                call_user_func($arg_arr[$item]['func'],
                               $arg_arr[$item]['value'])) {
          return TRUE;
        }
        else {
          return FALSE;
        }
      }
    }

  public function concatSQL ($sql, $el_arr, $thing, $arg_arr) {
    return 
      $sql . " " . 
      $this->valRepl($el_arr['sql'], $thing, $arg_arr); 
  }

  public function valRepl ($str, $thing, $arg_arr) {
    if (strpos($str, '<<<') === FALSE) { // 0 could be first elt of string
      return $str; // do nothing
    }

    if (strpos($str, '>>>') === false) {
      trigger_error("Mismatched <<< >>> in SQL query template.",
                    E_USER_ERROR);
    }
    
    $pos = 0; $remaining = strlen($str);
    while ($pos < $remaining) {
      $start = strpos($str, '<<<', $pos);
      $end = strpos($str, '>>>', $pos) + 3;
      $first = substr($str, 0, $start);
      $last = substr($str, $end);
      $middle = substr($str, 
                       $start + 3, 
                       (strpos($str, '>>>', $pos) - $start) - 3);
      $new_middle = $this->evalMiddle($middle, $thing, $arg_arr);
      $str = $first . $new_middle . $last;
      $pos = strlen($first . $new_middle);
      $remaining = strlen($last);
    }
    return $str;
  }

private function evalMiddle($middle, $thing, $arg_arr) {
    if (($middle == 'x') || ($middle == 'X')) {
      return $thing;
    }

    if (array_key_exists($middle, $arg_arr)) {
      return $arg_arr[$middle]['value'];
    }
  }

  } // end of class


