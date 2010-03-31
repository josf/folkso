<?php


  /**
   * Web service for accessing users tags.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009-2010 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */

require_once('folksoUserServ.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD', 'DELETE'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponder('get',
                                         array('required' => array('mytags')),
                                         'getMyTags'));
$srv->addResponseObj(new folksoResponder('get',
                                         array('required' => array('tag')),
                                         'getUserResByTag'));

$srv->addResponseObj(new folksoResponder('get',
                                         array('required' => array('check', 'fbuid')),
                                         'checkFBuserId'));
$srv->addResponseObj(new folksoResponder('get',
                                         array('required' =>
                                               array('fblogin')),
                                         'loginFBuser'));
                                               
$srv->Respond();
  




