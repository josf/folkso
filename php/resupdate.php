<?php


  /**
   * Web interface for updating basic information about a resource by
   * repolling that resource.
   *
   * These functions are separated from resource.php because they use
   * the Curl libraries that do not usually need to be called.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */

require_once('folksoUrl.php');
require_once('folksoQuery.php');

$srv = new folksoServer(array('methods' => array('POST'),
                              'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponse('post',
                                        array('required' => array('res')),
                                        'reload'));


$srv->Respond();

function reload (folksoQuery $q, folksoWsseCreds $cred, folksoDBconnect $dbc) {



}


?>