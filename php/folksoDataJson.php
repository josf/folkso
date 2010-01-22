<?php
  /**
   * 
   *
   * @package Folkso
   * @subpackage
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   */

  /**
   * @package Folkso
   */
class folksoDataJson extends folksoDataDisplay {
  public $datastyles = array();
  public $type;
  public $lineformat;
  public $argsperline;
  public $titleformat;
  public $start;
  public $end;
  private $linetemplate;
  private $linecount;

  /**
   * @param $linetemplate Array List of field names to be used with line(). 
   */
  public function __construct($linetemplate){
    if (is_array($linetemplate)) {
      $this->linetemplate = $linetemplate;
    }
    else {
      $this->linetemplate = func_get_args();
    }
    $this->type = 'json';
  }
  
  public function activate_style ($type) {}
  public function check_display($arr) {}
  
  public function line ($firstarg) {
    $args = func_get_args();
    print_r($this->linetemplate);
    if (count($args) > count($this->linetemplate)) {
      trigger_error('Argument mismatch (JSON): not enough args. Args: '
                    . implode(' ', $args) 
                    . " template: "
                    . implode(' ', $this->linetemplate),
                    E_USER_WARNING);
      $useargs = array_slice($args, 0, count($this->linetemplate));
      $usetemplate = $this->linetemplate;
    }
    elseif (count($this->linetemplate) > count($args)) {
      $usetemplate = array_slice($this->linetemplate, 0, count($args));
      $useargs = $args;
    }
    else {
      $usetemplate = $this->linetemplate;
      $useargs = $args;
      
    }
    $ret = json_encode(array_combine($usetemplate, $useargs));
    if (is_null($ret)) {
      trigger_error('Array mismatch (JSON)', E_USER_ERROR);
    }
    /**
     * We keep track of the lines so that commas get added between lines.
     */
    if ($this->linecount > 0) {
      $ret = ',' . $ret;
    }
    ++$this->linecount;
    return $ret;
 } 

  public function startform () {
    $this->linecount = 0;
    return "[";
  }
  public  function endform () {
    $this->linecount = 0;
    return "]";
  }
  public function title ($arg) {
    return "";
  }
}
