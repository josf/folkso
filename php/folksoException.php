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

class badSidException extends Exception { }
class insufficientDataException extends Exception { }

class dbConnectionException extends Exception { }
class dbQueryException extends Exception {
  public $sqlcode;
  public $sqlquery;

  /**
   * PHP 5.3.0 introduces a 3rd argument for __construct. Change if nec.
   */

  public function __construct ($sqlcode,
                               $sqlquery,
                               $message = null, 
                               $code = 0 
                               ) {
    $this->sqlcode = $sqlcode;
    $this->sqlquery = $sqlquery;
    parent::__construct($message, $code);
  }

 }



