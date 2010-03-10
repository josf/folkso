<?php


/**
 *
 * @package Folkso
 * @author Joseph Fahey
 * @copyright 2008-2010 Gnu Public Licence (GPL)
 * @subpackage Tagserv
 */

require_once('folksoTag.php');
require_once('folksoTags.php');
require_once('folksoSession.php');

/** 
 * When the tag's name or id is known, the field name "tag"
 * ("folksotag" if we maintain that system) will always be used. It
 * can be a multiple field (that is: "tag001, tag002, tag003...").
 *
 * The "tag" field should be able to accept either a numerical id or a
 * tag name. In this case the tag name is not necessarily normalized.
 *
 */


$srv = new folksoServer(array( 'methods' => 
                               array('POST', 'GET', 'HEAD', 'DELETE'),
                               'access_mode' => 'ALL'));

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('fancy'),
                                              'required_single' => array('tag')),
                                        'fancyResource'));
/*
 * Alias for simpler feed urls
 */
$srv->addResponseObj(new folksoResponder('get',
                                         array('required' => array('feed', 'tag')),
                                         'fancyResource'));

$srv->addResponseObj(new folksoResponder('get', 
                                        array('required_single' => 
                                              array('tag', 
                                                    'resources')),
                                        'getTagResources'));

$srv->addResponseObj(new folksoResponder('get', 
                                        array('required' => array('tag'),
                                              'exclude' => array('related')),
                                        'getTag'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('newtag')),
                                        'singlePostTag'));

$srv->addResponseObj(new folksoResponder('get', 
                                        array('required' => array('autotag')),
                                        'autoCompleteTags'));

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('related')),
                                        'relatedTags'));

$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('byalpha')),
                                        'byalpha'));

$srv->addResponseObj(new folksoResponder('head',
                                        array('required' => array('tag')),
                                        'headCheckTag'));
$srv->addResponseObj(new folksoResponder('get',
                                        array('required' => array('alltags')),
                                        'allTags'));

/**
 * Note that the "tag" field here refers to the resource that will be
 * deleted during the merge.
 */
$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('tag', 'target')),
                                        'tagMerge'));

$srv->addResponseObj(new folksoResponder('delete',
                                        array('required_single' => array('tag')),
                                        'deleteTag'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required' => array('delete'),
                                              'required_single' => array('tag')),
                                        'deleteTag'));

$srv->addResponseObj(new folksoResponder('post',
                                        array('required_single' => array('tag', 
                                                                         'newname')),
                                        'renameTag'));

$srv->Respond();

