<?php

class folksoQueryBuild {

  public $sql;

  public function build($sql_arr, $thing, $arg_arr ) {
    $sql = '';

    for ($sql_arr as $chunk_arr) {
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
      $this->sql = $sql;
      return $sql;
    }



  }



  }