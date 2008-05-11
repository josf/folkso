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

  function __construct ($arr) {
    $args = func_get_args();
    foreach ($args as $display) { 
      if ($this->check_display($display)) {
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
      return true;
    }
  }


  /**
   * Note: only the first error will be signaled, since the check
   * immediately returns false.
   */
  function check_display($arr) {
    if (!is_array($arr)) {
      trigger_error('This is not my beautiful array', E_USER_WARNING);
      return false;
    }
    $requireds = array('type', 'argsperline', 'lineformat');
    foreach ($requireds as $problem)  {
      if (!isset($arr[$problem])) {
        trigger_error("$problem is not set in display arr", E_USER_WARNING);
        return false;
      }
    }
    return true;
  }

  function line ($nothing) { // dummy argument, accepts multiple arguments
    if ($this->activated == false) {
      trigger_error("No display style activated yet.", E_USER_ERROR);
    }

    $args = func_get_args();
    $args = array_slice($args, 0, $this->argsperline);
    $outline = $this->lineformat; // use a copy, not the format

    for ($i = 0; $i < count($args); ++$i) {
      $offset = strpos($outline, 'XXX');
      if (!$offset) {
        trigger_error('Mismatch between arguments and template targets: too many arguments', E_USER_ERROR);
      }
      
      $outline = substr_replace($outline,
                                $args[$i],
                                $offset,
                                3);
    }
    if (strpos($outline, 'XXX')) {
      trigger_error('Template mismatch: not enough arguments', E_USER_ERROR);
    }
    return $outline;
  }


  function startform () {
    return $this->start;
  }

  function endform () {
    return $this->end;
  }

  function title ($arg) {
    $out = $this->titleformat;
    $offset = strpos($out, 'XXX');
    if ($offset == false) {
      trigger_error('Template mismatch in title', E_USER_WARNING);
    }
    $out = substr_replace($out, $arg, $offset, 3);
    return $out;
  }

}// end of class

?>