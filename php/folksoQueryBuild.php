<?php

class folksoQueryBuild {

  public $sql;

  public function build($sql_arr, $thing, $arg_arr ) {
    $sql = '';

    foreach ($sql_arr as $chunk_arr) {
      switch ($chunk_arr['type']) {
      case 'common':
        $sql .= " " . $chunk_arr['sql'];
        break;
      case 'isnum':
        if (is_numeric($thing)) {
          $sql .= " " . $chunk_arr['sql'];
        }
        break;
      case 'notnum':
        if (! is_numeric($thing)) {
          $sql .= " " . $chunk_arr['sql'];
        }
        break;
      default:
        if ((is_array($arg_arr)) &&
            (array_key_exists($chunk_arr['type'], $arg_arr))) {
          $type = $chunk_arr['type']; // to avoid some extra typing 


          /** If no test function is given, we just test for
           non-nullness of the value  **/
          if (! $arg_arr[$type]) {
            if ($arg_arr[$type]['value']) {
              $sql .= " " . $chunk_arr['sql'];
            }
          }
          /** functions must return booleans **/
          elseif (is_callable($arg_arr[$type])) {
            if  (call_user_func($arg_arr[$type]['func'], 
                                $arg_arr[$type]['value'])){
              $sql .= " " . $chunk_arr['sql'];
            }
          }
          else {
            trigger_error("Something is awry in the arguments for this SQL query: $type",
                          E_USER_ERROR);

          }
        }
      }
    }
    $sql = trim($sql);
    $this->sql = $sql;
    return $sql;

  }
  public function valRepl ($str, $thing, $arg_arr) {
    if (strpos($str, '<<<') === FALSE) { // 0 could be first elt of string
      return $str; // do nothing
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

public function evalMiddle($middle, $thing, $arg_arr) {
    if (($middle == 'x') || ($middle == 'X')) {
      return $thing;
    }

    if (array_key_exists($middle, $arg_arr)) {
      return $arg_arr[$middle]['value'];
    }
  }

  } // end of class


