<?php
  /**
   * 
   * 
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   *
   */

require_once('folksoDBconnect.php');

/**
 * Interact with the database. A thin layer of abstraction between
 * scripts and the DB with some helpful functions.
 * @package Folkso
 */
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
  public $latest_query;
  public $result_array;

  /**
   * If the query returns more than one result set, the second and
   * following results sets are stored here.
   */
  public $additional_results;

  /**
   * Convenience interface.
   *
   * The number of rows affected by the last INSERT, UPDATE, REPLACE
   * or DELETE query. Shoud be identical to $mysqli->affected_rows.
   *
   * Note that on SELECTs, this is treated just like $db->num_rows so
   * it cannot be used to determine if the last operation was a SELECT
   * or not.
   */
  public $affected_rows;

  /**
   * From mysqli_result->num_rows
   */
  public $rowCount;

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
    $this->additional_results = array();
    $this->result_array = array();
    $this->db = $dbc->db_obj();
    $this->db->set_charset('utf8');

    if (! $this->db instanceof mysqli) {
      throw new dbConnectionException("DBinteract: connect failed, not a connection object.", 
                                      '', '');
    }

    if ( mysqli_connect_errno()) {
      //      $this->connect_error = 
      throw new dbConnectionException("Connect failed: ".
                                      mysqli_connect_error(),
                                      mysqli_connect_errno());
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
   * @return mysql error code or empty string if no error has occurred.
   */
   public function errorCode () {
     return isset($this->db->errno) ? $this->db->errno : null;
   }
  

  /**
   * Depending on the result of the query, sets $this->result_status
   * to 'DBERR' (error), 'NOROWS' (no error, but no result set
   * returned), 'OK' (results returned).
   *
   * $i->affected_rows is reset to zero before each query.
   *
   * @param string $query The complete query as it should be called,
   * with all the variables filled in.
   * @return 
   */
  public function query ($query) {
    $this->affected_rows = 0;
    $this->first_val = ''; // reset first_val for new query (just in case).
    $this->result = $this->db->query($query);
    $this->affected_rows = $this->db->affected_rows;
    $this->latest_query = $query;

    /** some SQL operations do not return result sets. In this case we bail. **/
    if ($this->result instanceof MySQLi_Result) {

      $this->rowCount = $this->result->num_rows;
    }

    if ($this->db->errno <> 0) {
      /*      $this->query_error = sprintf("Query error: %s Error code: %d Query: %s", 
                                   $this->db->error, 
                                   $this->db->errno, $query);
                                   $this->result_status = 'DBERR';*/
      throw new dbQueryException($this->db->errno, 
                                 $query,
                                 $this->db->error);
      return;
    }
    elseif ($this->result->num_rows == 0) {
      $this->result_status = 'NOROWS';
    }
    else {
      $this->result_status = 'OK';
    }

    /*    if (is_null($this->result) ||
        (! $this->result instanceof MySQLi_Result)) {
      return; // should maybe warn...
      }*/


  }
  /**
   * Multiquery compatible version of query(). Should especially be
   * useful for stored procedures
   */
  public function sp_query($query) {
    $this->latest_query = $query; // for possible debug
    $this->affected_rows = 0;
    $this->first_val = '';
    $this->result = null;

    if ($this->db->multi_query($query)) {
      do {
        /* store first result set */
        if ($result = $this->db->store_result()) {
          if (! $this->result) {
            $this->result = &$result;
            while($row = $result->fetch_object()) {
              $this->result_array[] = $row;
            }
          }
          else {
            $this->additional_results[] = $result;
          }
        }
      } while ($this->db->next_result());
    }


    if ($this->db->errno <> 0) {
      $this->query_error = sprintf("Query error: %s Error code: %d Query: %s", 
                                   $this->db->error, 
                                   $this->db->errno, $query);
      throw new dbQueryException($this->db->errno,
                                 $query,
                                 $this->db->error);
      /*ù      $this->result_status = 'DBERR';
        return;*/
    }

    if (! $this->result instanceof MySQLi_Result) {
      return;
    }

    /** First result set  **/
    if ($this->result->num_rows == 0) {
      $this->result_status = 'NOROWS';
      return;
    }
    elseif ($result->num_rows > 0) {
      $this->result_status = 'OK';
    }
    else {
      $this->result_status = 'INTERR';
    }
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
    $select = 'SELECT id FROM tag ';
    if (is_numeric($tagstring)) {
      $select .= " WHERE id = " . $tagstring;
    }
    else {
      $select .= "  WHERE tagnorm = normalize_tag('" .
                $this->db->real_escape_string($tagstring) .
        "') ";
    }
    $select .= ' limit 1';

    $this->presult = $this->db->query($select);

    if ($this->db->errno <> 0) {
      throw new dbQueryException($this->db->error . ' ' . $query,
                                 $this->db->errno);
    }
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

    if (is_numeric($url)) {
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

    if ($this->db->errno <> 0) {
      throw new dbQueryException($this->db->error . ' ' . $query,
                                 $this->db->errno);
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
   * @param  integer $id Must be a number.
   * @return string The url corresponding to the given id. 0 if no url is found. 
   */
  public function url_from_id ($id) {
    if (! is_numeric($id)) {
      trigger_error("url_from_id requires a number as argument, and not this: $id",
                    E_USER_ERROR);
    }
    $select = 'SELECT uri_raw FROM resource WHERE id = ' . $id;
    $this->presult = $this->db->query($select);
    if ($this->presult->num_rows > 0) {
      $row = $this->presult->fetch_object();
      return $row->uri_raw;
    }
    else {
      return 0;
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

}

?>