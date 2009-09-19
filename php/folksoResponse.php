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

class folksoResponse {

  public $status;
  public $statusMessage;
  private $errorDeclared;
  private $body;
  public $error_body;
  public $headers;

  private $httpStatus = array(200 => '0K', 
                              201 => 'Created',
                              202 => 'Accepted',
                              203 => 'Non-Authoritative Information',
                              204 => 'No Content',
                              205 => 'Reset content',
                              304 => 'Not Modified',
                              400 => 'Bad Request',
                              401 => 'Unauthorized',
                              403 => 'Forbidden',
                              404 => 'Not Found',
                              405 => 'Method Not Allowed',
                              406 => 'Not Acceptable',
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

  /**
   * Declare an error. Sets internal error status to 'TRUE'.
   *
   * @param $status Integer HTTP Error code
   * @param $message String (optional) Error message
   */
  public function setError($status, $message = null) {
    if (! isset($this->httpStatus[$status])){
      trigger_error("Invalid HTTP status: $status",
                    e_user_warning);
    }
    $this->errorDeclared = true;
    $this->status = $status;
    $this->statusMessage 
      = $message ? $message : $this->httpStatus[$status];
  }

  public function dbError($status = 500, $message = null){
    $this->setError($status, $message ? $message : 'Database error');
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
  }

  public function setType($str) {
    if (! in_array($str, array('xml', 'text', 'html'))){
      trigger_error("Invalid output content type", e_user_warning);
      $str = 'text';
    }
    $this->outType = $str;
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
   * Prepares an array containing the HTTP headers to be sent. 
   * 
   */
  public function prepareHeaders () {
    $headers = array();
    $headers[] = 'HTTP/1.1 ' . $this->status . ' ' $this->statusMessage;
    $headers[] = $this->contentType();

    return $headers;
  }
}