<?php

  /**
   * Interact with the database. A thin layer of abstraction between
   * scripts and the DB with some helpful functions.
   * 
   * 
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   *
   */

require_once('folksoDBconnect.php');

class folksoDBinteract {

  /**
   * The database connection object.
   */
  public $db;

  /**
   * Once a query has been executed ($i->query()), the result is
   * available here.
   */
  public $result;
  public $connect_error= '';
  public $query_error = '';
  public $result_status;
  
  /**
   * When $this->first_val() is called, it stores the result here for
   * future calls.
   */
  private $first_val;

  /**
   * The tagp and resourcep existence tests don't use the standard
   * $result variable so that they do not interfer with the rest of
   * the variables (error, result_status, etc.)
   */
  private $presult;

  /**
   * On object creation, the caller should check error status to make
   * sure the connection succeeded.
   *
   * @param folksoDBconnect $dbc
   */
  public function __construct (folksoDBconnect $dbc) {
    $this->db = $dbc->db_obj();
    $this->db->set_charset('utf8');
        if ( mysqli_connect_errno()) {
      //      $this->connect_error = 
        die(sprintf("Connect failed: %s Error code: %s", 
                    mysqli_connect_error(), 
                    mysqli_connect_errno()));
        } 
  }

  /**
   * Simple check to see if everything is ok. If this returns false,
   * the caller should then
   *
   * @return boolean True on error, false on no error.
   */
  public function db_error () {
    if (($this->connect_error) ||
        ($this->query_error)) {
      return true;
    }
    return false;
  }

  /**
   * Returns either query error or connection error.
   *
   * @uses connect_error
   * @uses query_error
   * @return error string
   */

  public function error_info () {
    if ($this->query_error) {
      return $this->query_error;
    }
    if ($this->connect_error) {
      return $this->connect_error;
    }
  }

  /**
   * Depending on the result of the query, sets $this->result_status
   * to 'DBERR' (error), 'NOROWS' (no error, but no result set
   * returned), 'OK' (results returned).
   *
   * @param string $query The complete query as it should be called,
   * with all the variables filled in.
   * @return 
   */
  public function query ($query) {
    $this->first_val = ''; // reset first_val for new query (just in case).
    $this->result = $this->db->query($query);

    if ($this->db->errno <> 0) {
      $this->query_error = sprintf("Query error: %s Error code: %d Query: %s", 
                                   $this->db->error, 
                                   $this->db->errno, $query);
      $this->result_status = 'DBERR';
      return;
    }
    elseif ($this->result->num_rows == 0) {
      $this->result_status = 'NOROWS';
    }
    else {
      $this->result_status = 'OK';
    }
  }

  /**
   * Query using a prepared statement to avoid base64 injection
   * attacks. This should be used for all database queries that do not
   * use stored procedures, instead of the standard $i->query() method.
   *
   * The number of parameters and the number of '?' in the SQL will be
   * compared and a fatal error will be signaled if they do not match.
   *
   * @param $query string The SQL statement to execute, including the appropriate ?
   * 
   * @param $params array The parameters, in order, to be inserted
   * into the prepared statement.
   *
   */
  public function pquery ($query, $params) {
    




  }

  
  /**
   * 
   * @returns the db result set. The caller can then use the classic
   * while (fetchobject->result) on this.
   */
  public function result () {
    if ($this->result_status == 'OK'){
      return $this->result;
    }
  }

  /**
   * Utiliity function to test the existence of a tag. 
   *
   * @param string $tagstring A tag name or id.
   * @return boolean
   * 
   */
  public function tagp ($tagstring) {
    $select = '';
    if (is_numeric($tagstring)) {
      $select = "SELECT id FROM tag
                 WHERE id = " . $tagstring .
                 " LIMIT 1";
    }
    else {
      $select = 
        "SELECT id FROM tag
                  WHERE tagnorm = normalize_tag('" .
        $this->db->real_escape_string($tagstring) .
        "') 
         LIMIT 1";
    }
    $this->presult = $this->db->query($select);
    if ($this->presult->num_rows > 0) {
      $this->presult->free();
      return true;
    }
    else { 
      return false;
    }
  }


  /**
   * Utiliity function to test the presence of a resource in the DB.
   *
   * @param string $url A tag name or an id.
   * @return boolean
   * 
   */
  public function resourcep ($url) {
    $select = '';

    if (preg_match('/^\d+$/', $url)) {
      $select = "SELECT id FROM resource
                 WHERE id = " .
        $this->db->real_escape_string($url) . 
        "  LIMIT 1";
    }
    else {
      $select = "SELECT id FROM resource 
                  WHERE uri_normal = url_whack('" .
        $this->db->real_escape_string($url) .
        "')
                 LIMIT 1";
    }
    $this->presult = $this->db->query($select);
    if ($this->presult->num_rows > 0) {
      $this->presult->free();
      return true;
    }
    else {
      return false;
    }

  }

  /**
   * Use the db handle's quoting mechanism. 
   *
   * @param string $str
   */
  public function dbquote ($str) {
    return $this->db->real_escape_string($str);
  }

  /**
   * Use the db handle's quoting mechanisme.
   *
   * @param string $str
   *
   * Use this and not dbquote, which will be changed into a real
   * quoting function soon.
   */
  public function dbescape ($str) {
    return $this->db->real_escape_string($str);
  }

  /**
   * @param string $name The name of the column holding the single
   * result that we want.
   *
   */
  public function first_val ($name) {
    if ($this->first_val) {
      return $this->first_val;
    }
    elseif ($this->result_status == 'OK') {
      $row = $this->result->fetch_object();
      return $row->$name;
    }
    else {
      return false;
    }
  }

  /**
   * Allow the statement handle and the connection handle to be
   * destroyed.
   */
  public function done () {
       if ($this->result instanceof mysqli_result) {
      $this->result->free();
      }
    $this->db->close();
  }
}

/**
 * Count the number of times the character $char occurs in $string.
 * 
 * Returns 0 even if the initial $string is ''.
 */
private function char_count ($string, $char) {
  $count = 0;
  $str = $string;

  while ((strlen($str) > 0) &&
         (strpos($str, $char))) {
    ++$count;
    $str = substr($str, strpos($str, $char) + 1);
  }
  return $count;
}

?>