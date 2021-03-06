<?php

  /**
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */

require_once "folksoException.php";
  /**
   * A class for passing around database handles and connection information. 
   *
   * Essentially, this class wraps up database information and
   * possibly a database connection so that the connection is not made
   * until required.
   *
   * @package Folkso
   */
class folksoDBconnect {
  private $host;
  private $user;
  private $passwd;
  private $database;

  /**
   * This contains either nothing or a (reference to a) database
   * connection object (mysqli for the moment). The connection is only
   * made when necessary, but then is retained.
   *
   */
  private $dbx; 

  /**
   * Right now, this contains any connection error data. This should
   * probably go into an error handling system, but that hasn't been
   * written yet.
   *
   */
   public $dberr;

  function __construct ($host, $user, $passwd, $database){
    $this->host = $host;
    $this->user = $user;
    $this->passwd = $passwd;
    $this->database = $database;
  }

  /**
   * The key function. Returns either an existing database connection
   * object, or creates one if necessary.
   *
   * @return Database connection object.
   */
  public function db_obj () {
    if ((!empty($this->dbx)) &&
        ($this->dbx instanceof mysqli)) {
      $this->dberr = '';
      return $this->dbx;
    }
    else {
      try {
        $this->dbx = new mysqli($this->host, $this->user, 
                                $this->passwd, $this->database);
      }
      catch (Exception $e) {
        throw new dbConnectionException("Connect failed: " .
                                        mysqli_connect_error(),
                                        mysqli_connect_errno());
      }
      if ( mysqli_connect_errno()) {
        throw new dbConnectionException("Connect failed: " .
                                        mysqli_connect_error(),
                                        mysqli_connect_errno());
      }
    return $this->dbx;
    }
  }
} //end of class
?>
