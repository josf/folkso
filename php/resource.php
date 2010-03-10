<?php


  /**
   * Web service for accessing, creating and modifying tag information
   * about resources (URLs).
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */


require_once('folksoTags.php');
require_once('folksoResource.php');

$srv = new folksoServer(array( 'methods' => array('POST', 'GET', 'HEAD', 'DELETE'),
                               'access_mode' => 'ALL'));
$srv->addResponseObj(new folksoResponder('head', 
                                        array('required' => array('res')),
                                        'isHead'));

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('clouduri', 'res')),
                                        'tagCloudLocalPop'));

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('res'),
                                              'exclude' => 
                                              array('clouduri', 'visit', 'note', 'ean13list')),
                                        'getTagsIds'));


$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('res', 'ean13list'),
                                              'exclude' => array('delete', 'clouduri', 'note')),
                                        'resEans'));
                                              
$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('res', 'tag'),
                                              'required' => array('delete'),
                                              'exclude' => array('meta', 'newresource', 'ean13')),
                                        'unTag'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('res', 'tag'),
                                              'exclude' => array('delete', 'newresource')),
                                        'tagResource'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('res'),
                                              'required' => array('visit'),
                                              'exclude' => array('tag', 'note')),
                                        'visitPage'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('res', 'newtitle'),
                                              'exclude' => array('note', 'delete')),
                                        'addResource'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('res', 'ean13'),
                                              'exclude' => array('newean13', 'oldean13', 'note', 'meta', 'tag', 'delete')),
                                        'assocEan13'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('res', 'newean13', 'oldean13'),
                                              'exclude' => array('ean13', 'tag', 'delete')),
                                        'modifyEan13'));
$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('delete', 'res', 'ean13'),
                                              'exclude' => array('tag', 'note')),
                                        'deleteEan13'));


$srv->addResponseObj(new folksoResponder('delete',
                                        array('required' => array('res', 'tag')),
                                        'unTag'));

$srv->addResponseObj(new folksoResponder('delete',
                                        array('required_single' => array('res'),
                                              'exclude' => array('tag')),
                                        'rmRes'));
$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('res', 'delete'),
                                              'exclude' => array('tag')),
                                        'rmRes'));
$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('res', 'note'),
                                              'exclude' => array('tag', 'delete')),
                                        'addNote'));

$srv->addResponseObj(new folksoResponder('get',
                                       array('required_single' => array('res', 'note'),
                                             'exclude' => array('tag', 'delete')),
                                       'getNotes'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('note', 'delete'),
                                              'exclude' => array('res', 'tag')),
                                        'rmNote'));

$srv->Respond();
