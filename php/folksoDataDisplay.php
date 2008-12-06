<?php

  /**
   * folksoDataDisplay formats the data returned from the
   * database. Each folksoDataDisplay object can have multiple display
   * formats: xhtml, xml, simple text.
   *
   * Use folksoDisplayFactory to produce these objects easily.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */
  /**
   * @package Folkso
   */
class folksoDataDisplay {

  /**
   * A list containing the different datastyles. (This could be done
   * in a more elegant way.) Each datastyle is an associative array
   * containing the basic fields for each display style: xhtml, text,
   * xml, or any other format that might be needed. Only one style is
   * activated at a time, and no style is activated on object
   * creation. (See $dd->activated.) The values of the selected
   * display style are then copied to the folksoDataDisplay object
   * itself, as $dd->lineformat, $dd->titleformat etc.
   */
  public $datastyles = array();

  /**
   * The name of the current data display style, typically 'xml',
   * 'xhtml', or 'text'.
   */
  public $type;

  /**
   * A formatting string for each line of output. Each occurence of
   * 'XXX' is replaced with the corresponding value.
   */
  public $lineformat;

  /**
   * Number of arguments per line (corresponds to
   * $dd->lineformat). The number of XXX's in the lineformat must be
   * the same as this value.
   */
  public $argsperline;
  public $titleformat;
  public $start;
  public $end;

  /**
   * Flag indicating that a style has been activated.
   */
  public $activated = false;

  function __construct ($arr) {
    $args = func_get_args();
    foreach ($args as $display) { 
      if ($this->check_display($display)) {
        $this->datastyles[] = $display;
      }
    }
  }

  /**
   * Select one of the styles in $dd->datastyles to be the current
   * style. Copies the values from the style into the corresponding
   * slots in the $dd object.
   */
  public function activate_style ($type) {
    $this->type = $type;
    $changed = false;
    foreach ($this->datastyles as $display) {
      if ($display['type'] == $type) {
        $this->lineformat = $display['lineformat'];
        $this->argsperline = $display['argsperline'];
        $this->titleformat = $display['titleformat'];
        $this->start = $display['start'];
        $this->end = $display['end'];
        $changed = true;
        break;
      }
    }
    if ($changed) {
      $this->activated = true;
      return $changed;
    }
    else {
      trigger_error(
                    "Failed to activate style: $type", 
                    E_USER_ERROR);
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

  /**
   * Inserts its arguments into the format string in $dd->lineformat
   * replacing the XXX's with the arguments. The number of arguments
   * must be the same as the number of XXX's.
   */
  public function line ($nothing) { // dummy argument, accepts multiple arguments
    if ($this->activated == false) {
      trigger_error("No display style activated yet.", E_USER_ERROR);
    }

    $args = func_get_args();
    $args = array_slice($args, 0, $this->argsperline);
    $outline = $this->lineformat; // use a copy, not the format itself

    for ($i = 0; $i < count($args); ++$i) {
      $offset = strpos($outline, 'XXX');
      if (!$offset) {
        trigger_error(
                      'Mismatch between arguments and template targets: too many arguments', 
                      E_USER_ERROR);
      }
      
      $outline = substr_replace($outline,
                                $this->eval_element($args[$i]),
                                $offset,
                                3);
    }
    if (strpos($outline, 'XXX')) {
      trigger_error("Template mismatch: not enough arguments: $outline", 
                    E_USER_ERROR);
    }
    return $outline;
  }


  private function eval_element ($thing) {
    if (is_string($thing)) {
      return $thing;
    }
    elseif (isset($thing[$this->type])) {
      return $thing[$this->type];
    }
    else {
      return $thing['default'];
    }
  }

  public function startform () {
    return $this->start;
  }

  public function endform () {
    return $this->end;
  }

  /**
   * Outputs the given argument using the $dd->titleformat string.
   */
  public function title ($arg) {
    $out = $this->titleformat;
    $offset = strpos($out, 'XXX');
    if ($offset == false) {
      trigger_error('Template mismatch in title', 
                    E_USER_WARNING);
    }
    $out = substr_replace($out, $arg, $offset, 3);
    return $out;
  }

}// end of class

?>