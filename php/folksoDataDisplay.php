<?php


class folksoDataDisplay {

  public $datastyles = array();
  public $type;
  public $lineformat;
  public $argsperline;
  public $titleformat;
  public $start;
  public $end;
  public $activated = false;

  function __constructor ($arr) {
    $args = func_get_args();
    foreach ($args as $display) { 
      if (check_display($display)) {
        $this->datastyles[] = $display;
      }
    }
  }

  function activate_style ($type) {
    foreach ($this->datastyles as $display) {
      if ($display['type'] == $type) {
        $this->type = $type;
        $this->lineformat = $display['lineformat'];
        $this->argsperline = $display['argsperline'];
        $this->titleformat = $display['titleformat'];
        $this->start = $display['start'];
        $this->end = $display['end'];
      }
      $this->activated = true;
      break;
    }
  }


  /**
   * Note: only the first error will be signaled, since the check
   * immediately returns false.
   */
  function check_display($arr) {
    if (!is_array($arr)) {
      return false;
    }
    $requireds = array('type', 'argsperline', 'lineformat');
    foreach ($requireds as $problem)  {
      if (!isset($arr[$problem])) {
        trigger_error("$problem is not set in display arr", E_USER_WARNING);
        return false;
      }
    }
  }

  function line ($nothing) {
    if ($this->activated == false) {
      trigger_error("No display style activated yet.", E_USER_ERROR);
    }

    $args = func_get_args();
    $args = array_slice($args, 0, $this->argsperline);
    
    

  }

}// end of class

?>