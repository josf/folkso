<?php

/**
 *
 * @package Folkso
 * @author Joseph Fahey
 * @copyright 2010 Gnu Public Licence (GPL)
 * @subpackage Tagserv
 */

require_once "folksoServer.php";
require_once "folksoResource.php";
require_once "folksoUrlRewriteResource.php";

/**
 * Interface for adding new resources to the database.
 */


$srv = new folksoServer(array('methods' => array('POST'),
                              'access_mode' => 'LOCAL',
                              'rewrite' => new folksoUrlRewriteResource(),
                              'allow_anonymous_post' => true));

$srv->addResponseObj(new folksoResponder('post',
                                         array('required' => array('res')),
                                         'visitPage'));

$srv->Respond();
                                               