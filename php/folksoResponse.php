<?php

/**
 * @package Folkso
 * @author Joseph Fahey
 * @copyright 2009 Gnu Public Licence (GPL)
 * @subpackage Tagserv
 */

/**
 * @package Folkso
 * 
 */
require_once('folksoTags.php');
require_once('folksoFabula.php');

class folksoResponse {

  public $status;
  public $statusMessage;
  private $errorDeclared;
  private $body;
  public $error_body;
  public $headers;
  public $redirect;
  private $loc;

  /**
   * Special headers added before header preparation.
   */
  public $preheaders;
  public $debug;

  private $httpStatus = array(200 => '0K', 
                              201 => 'Created',
                              202 => 'Accepted',
                              203 => 'Non-Authoritative Information',
                              204 => 'No Content',
                              205 => 'Reset content',
                              303 => 'See Also',
                              304 => 'Not Modified',
                              400 => 'Bad Request',
                              401 => 'Unauthorized',
                              403 => 'Forbidden',
                              404 => 'Not Found',
                              405 => 'Method Not Allowed',
                              406 => 'Not Acceptable',
                              409 => 'Conflict',
                              500 => 'Internal Server Error',
                              501 => 'Not Implemented'
                              );

  /**
   * Internal representation of output content type. One of 'xml',
   * 'text', 'html'...
   */
  private $outType;

  function __construct() {
    $this->errorDeclared = false;

  }

  /**
   * @return String The body to be returned.
   */
  public function body() {
    return $this->body;
  }

  /**
   * Append $str to response body, inserting a newline between
   * successive calls. (This mimics using php's print function when
   * outputting directly.)
   *
   * @param $str String
   */
  public function t ($str) {
    if ($this->body) {
      $this->body = $this->body . "\n" . $str;
    }
    else {
      $this->body = $str;
    }
  }

  public function deb($str) {
    if ($this->debug) {
      $this->debug = $this->debug . "\n" . $str;
    }
    else {
      $this->debug = $str;
    }
  }

  /**
   * Declare an error. Sets internal error status to 'TRUE'.
   *
   * @param $status Integer HTTP Error code
   * @param $message String (optional) Error message
   * @param $representation String Information for body
   */
  public function setError($status, $message = null, $representation = null) {
    if (! isset($this->httpStatus[$status])){
      trigger_error("Invalid HTTP status: $status",
                    e_user_warning);
    }
    $this->errorDeclared = true;
    $this->status = $status;
    $this->statusMessage 
      = $message ? $message : $this->httpStatus[$status];
    if ($representation){
      $this->errorBody($representation);
    }
  }

  public function dbError($status = 500, $message = null){
    $this->setError($status, $message ? $message : 'Database error');
  }

  public function dbConnectionError($error_info){
    $this->setError(500, "Database connection error");
    $this->errorBody($error_info);
  }

  public function dbQueryError($error_info){
    $this->setError(500, "Database query error");
    $this->errorBody($error_info);
    return $this;
  }

  /**
   * @param $user optional A folksoUser object
   */
   public function unAuthorized ($user = null) {
     $this->setError(403, "Forbidden");
     $this->setLoginRedirect();
     return $this;
   }
  

  public function setOk ($status, $message = null){
    if (! isset($this->httpStatus[$status])){
      trigger_error("Invalid HTTP status: $status",
                    e_user_warning);
    }
    $this->errorDeclared = false;
    $this->status = $status;
    $this->statusMessage = 
      $message ? $message : $this->httpStatus[$status];
  }

  /**
   * Translating true to true or false to false. Hmmm
   */
  public function isError() {
    if ($this->errorDeclared) {
      return true;
    }
    return $false;
  }

  public function setType($str) {
    if (! in_array($str, array('xml', 'text', 'html'))){
      trigger_error("Invalid output content type", e_user_warning);
      $str = 'text';
    }
    $this->outType = $str;
  }

  /**
   * Basic exception handling for the most common error cases, where
   * we simply want to return the right kind of HTTP error with the
   * appropriate information.
   * 
   * @param dbException $e
   */
   public function handleDBexception (dbException $e) {
     if ($e instanceof dbConnectionException) {
       $this->dbConnectionError($e->getMessage());
     }
     elseif ($e instanceof dbQueryException) {
       $this->dbQueryError($e->getMessage() . "\n\nQuery: \n" .
                           $e->sqlquery);
     }
     return $this;
   }
  

  /**
   * Returns the string for the HTTP content-type header but does not
   * set the header.
   *
   * @return String
   */  
  public function contentType () {
    $type = '';
    switch ($this->outType) {
    case 'xml': 
      $type =  'text/xml';
      break;
    case 'text':
      $type =  'text/text';
      break;
    case 'html':
      $type =  'text/html';
      break;
    default: 
      $type =  'text/text';
    }
   return 'Content-Type: ' . $type;
  }


  /**
   * In case of an error, we make sure that the old body is not
   * output.
   * 
   * @param $str String
   */
  public function errorBody($str) {
    $this->debug_body = $this->body;
    $this->body = $str;
  }

/**
 * Generic header adding function. No checking done. 
 *
 * Headers added this way will appear after the first two headers
 * (HTTP/1.1 and content-type).
 * 
 * @param $str
 */
 public function addHeader ($str) {
   $this->preheaders[] = $str;
 }

 /**
  * @param 
  */
 public function setRedirect ($url) {
   $this->redirect = $url;
 }
 
 /**
  * @param 
  */
  public function setLoginRedirect () {
    if (! $this->loc instanceof folksoLocal) {
      $this->loc = new folksoFabula();
    }
    $this->setRedirect($this->loc->loginPage());
   }
 

  /**
   * Prepares an array containing the HTTP headers to be sent. 
   * 
   */
  public function prepareHeaders () {
    $headers = array();
    $headers[] = 'HTTP/1.1 ' . $this->status . ' ' . $this->statusMessage;
    $headers[] = $this->contentType();
    if (count($this->preheaders) > 0) {
      $headers = array_merge($headers, $this->preheaders);
    }

    if ($this->redirect) {
      $headers[] = sprintf('Location: %s', $this->redirect);
    }
    
    $this->headers = $headers;
    return $headers;
  }

  /**
   * Final output function. Sets headers then prints body or
   * errorBody.
   */
  public function output () {
    $this->prepareHeaders();
    foreach ($this->headers as $head) {
      header($head);
    }

    if ($this->isError()){
      print $this->error_body;
    }
    /** Check for status codes that do not allow body **/
    elseif (in_array($this->status, 
                     array(204, 304))) {
      return;
    } 
    else {
      print $this->body;
    }
  }
}

?>