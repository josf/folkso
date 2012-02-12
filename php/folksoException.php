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

class dbException extends Exception { }

class dbConnectionException extends dbException { }
class dbQueryException extends dbException {
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

class dbSessionQueryException extends dbQueryException { }


/*
 * USER
 */
class userException extends Exception {}

class unknownUserException extends Exception {}
class badUseridException extends userException { }
class badUrlbaseException extends userException { }

/* USER/RIGHTS
 *
 */
class rightException extends userException { }
class rightAlreadyPresentException extends rightException { }
class rightInvalidException extends rightException { }

class folksoFacebookException extends Exception {}



/* USER/AUTHENTICATION
 * 
 * These should mostly concern details related to hybridauth.
 */
class authenticationException extends userException {
  function __construct ($msg = null, $code = null, $prev = null) {
    if ($prev instanceof Exception) {
      parent::__construct($msg, $prev->getCode(), $prev);
    }
    else {
      parent::__construct($msg);
    }
  }
}

class malformedIdentifierException extends authenticationException { }
class unknownServiceException extends authenticationException { }
class configurationException extends authenticationException {}
class failedAuthenticationException extends authenticationException {}