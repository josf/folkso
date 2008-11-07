<?php


include_once('folksoClient.php');
include_once('folksoFabula.php');
include_once('folksoPage.php');

class folksoPageData {

  /**
   *  String HTML version of whatever is asked for.
   */
  public $html;

  /**
   * Result status from the http request.
   */
  public $status;

  /**
   * Title 
   */
  public $title;

  /**
   * Raw xml data.
   */
  public $xml; 


  public function _construct($status = '', $html = '', $title = '') {
    $this->status = $status;
    $this->html = $html;
    $this->title = $title;
  }

  /**
   * Test whether the request was successful (result status 200 or
   * 304). If $p->status is NULL issues a warning but returns false
   * all the same.
   *
   * @returns boolean
   */
  public function is_valid() {
    if (($this->status) && 
        (($this->status == 200) ||
         ($this->status == 304))) {
      return true;
    }
    else {
      if (! $this->status) {
        trigger_error('is_valid: no result status yet.', E_USER_WARNING);
      }
      return false;
    }
  }

}