<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009-2010 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */

require_once 'folksoResponse.php';
require_once 'folksoSession.php';
require_once 'folksoUserServ.php';

  /**
   * This interaction system allows us to just ask for data without
   * worrying whether it is on the same server. When the page calling
   * a function is on the same machine as the tag server, it uses
   * folksoOnServer.  If an HTTP request is needed, it would be
   * handled by another class. In both cases, a folksoResponse object
   * is returned.
   *
   * Ideally, this setup would replace all use of folksoClient.
   *
   */


abstract class folksoInteract {

  abstract public function userDataReq(folksoUser $u);
  abstract public function userFavoriteTags(folksoUser $u);
}


class folksoOnServer extends folksoInteract {
  public $dbc;

  /**
   * An empty folksoSession object to use whenever we don't really
   * need any session data.
   */
  public $dummy_fks;

  public function __construct (folksoDBconnect $dbc) {
    $this->dbc = $dbc;
    $this->dummy_fks = new folksoSession($this->dbc);
  }

  /**
   * Calls getUserData in folksoUserServ.php
   * 
   * @param folksoUser $u This should be a valid user.
   */
  public function userDataReq(folksoUser $u) {
    $r = getUserData(new folksoQuery(array(), array(), 
                                     array('folksouid' => $u->userid)),
                     $this->dbc, $this->dummy_fks);
    return $r;
  }

  /**
   * @param folksoUser $u
   */
  public function userFavoriteTags (folksoUser $u) {
    $r = getFavoriteTags(new folksoQuery(array(), array(),
                                         array('folksouid' => $u->userid)),
                         $this->dbc, $this->dummy_fks);
    return $r;
   }
  

}