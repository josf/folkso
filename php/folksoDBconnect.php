<?php


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

  function db_obj () {
    if ((!empty($this->dbx)) &&
        ($this->dbx instanceof mysqli)) {
      $this->dberr = '';
      return $this->dbx;
    }
    else {
      $this->dbx = new mysqli($this->host, $this->user, $this->passwd, $this->database);

      // TODO : this should be handled by our own (future) error handling system
      if ( mysqli_connect_errno()) {
        $this->dberr = printf("Connect failed: %s\n", mysqli_connect_error());
        return ''; // or should this be false?
      }
      else {
        return $this->dbx;
      }
    }
  }

  } //end of class


?>