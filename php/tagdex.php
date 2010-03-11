<?php

require_once "folksoTag.php";
require_once "folksoTags.php";
require_once "folksoUrlRewriteTag.php";


$srv = new folksoServer(array( 'methods' => 
                               array('POST', 'GET', 'HEAD', 'DELETE'),
                               'access_mode' => 'ALL',
                               'rewrite' => new  folksoUrlRewriteTag));

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

$srv->Respond($_GET['folksohuge']);
print $_GET['folksohuge'];

