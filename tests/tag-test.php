<?php
require_once('unit_tester.php');
require_once('reporter.php');
require('folksoTags.php');
require('dbinit.inc');
require('/var/www/tag.php');

class testOffolksotag extends  UnitTestCase {
 
  public $dbc;
  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. This way we can look at DB after the last
        test, too. Note that all other tests have no effect on DB
        state at end of tests.
    **/

    $lh = 'localhost';
    $td = 'tester_dude';
    $ty = 'testy';
    $tt = 'testostonomie';

     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc2 = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc3 =new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->fks = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
     $this->fks2 = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
     $this->fks3 = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
     $this->fks4 = new folksoSession(new folksoDBconnect($lh, $td, $ty, $tt));
  }

   function testHeadCheck () {
     $r = headCheckTag(new folksoQuery(array(),
                                       array('folksotag' => 'tagone'),
                                       array()),
                       $this->dbc,
                       $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'not creating Response object');
     $this->assertEqual(200, $r->status,
                        'Error returned by headcheck');
     $r->prepareHeaders();
     $this->assertEqual(count($r->headers), 3,
                        'Not getting 3 headers');
     $this->assertPattern('/Tagid/', $r->headers[2],
                          'X-Folkso-Tagid header not set');
     

     $r2 = headCheckTag(new folksoQuery(array(),
                                       array('folksotag' => 'emacs'),
                                       array()),
                        $this->dbc,
                        $this->fks);
     $this->assertIsA($r2, folksoResponse,
                      'Bad resource not returning folksoResponse');
     $this->assertEqual($r2->status, 404,
                        'Not getting 404 for incorrect tag');

   }

   function testGetTag() {
     $r = getTag(new folksoQuery(array(),
                                 array('folksotag' => 'tagone'),
                                 array()),
                 $this->dbc,
                 $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'not creating Response object');
     $this->assertEqual(200, $r->status,
                        'Error returned by getTag');

     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       'xml incorrect');

   }

   function testSinglePostTag() {
     $r = singlePostTag(new folksoQuery(array(),
                                 array('folksonewtag' => 'emacs'),
                                 array()),
                        $this->dbc,
                        $this->fks);
     $this->assertEqual($r->status, 403,
                        'Unknown user should provoke a 403 on singlePostTag:' . $r->status);

     $this->fks2->startSession('marcelp-2009-001', true);
     $r2 = singlePostTag(new folksoQuery(array(),
                                 array('folksonewtag' => 'emacs'),
                                 array()),
                        $this->dbc,
                        $this->fks2);



     $this->assertIsA($r2, folksoResponse,
                      'Problem with object creation');
     $this->assertEqual($r2->status,
                        201,
                        'Tag creation returning error: ' . $r->status);
     $t = getTag(new folksoQuery(array(),
                                 array('folksotag' => 'emacs'),
                                 array()),
                 new folksoDBconnect('localhost', 'tester_dude', 
                                     'testy', 'testostonomie'),
                 $this->fks);

     $this->assertEqual(200, $t->status,
                        'New tag not created: ' . $t->status . $t->status_message);
   $h = headCheckTag(new folksoQuery(array(),
                                       array('folksotag' => 'tagone'),
                                       array()),
                     new folksoDBconnect('localhost', 'tester_dude', 
                                         'testy', 'testostonomie'),
                     $this->fks);

   $this->assertEqual($h->status, 200,
                      'headcheck says the tag is still not there');
   }

   function testGetTagResources() {
     $r = getTagResources(new folksoQuery(array(),
                                          array('folksotag' => 'tagone'),
                                          array()),
                          $this->dbc,
                          $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'problem w/ object creation');
     $this->assertEqual(200, $r->status,
                        sprintf('getting error msg with getTagResources: %d %s ',
                                $r->status, $r->status_message));
     $this->assertPattern('/example.com/', $r->body(),
                          'Not finding url');

     $rbad = getTagResources(new folksoQuery(array(),
                                          array('folksotag' => 'emacs'),
                                          array()),
                             $this->dbc,
                             $this->fks2);

     $this->assertEqual(404, $rbad->status,
                        'bad tag should return 404');


     $rx = getTagResources(new folksoQuery(array(),
                                           array('folksotag' => 'tagone',
                                                 'folksodatatype' => 'xml'),
                                           array()),
                           $this->dbc3,
                           $this->fks3);
     $this->assertEqual(200, $rx->status,
                        'xml request failiing');
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($rx->body()),
                       'failed to load xml');
     
   }

   function testFancyResource() {
     $r = fancyResource(new folksoQuery(array(),
                                        array('folksotag' => 'tagone'),
                                        array()),
                        $this->dbc,
                        $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'problem w/ object creation');
     $this->assertEqual(200, $r->status,
                        sprintf('getting error msg with fancyResource: %d %s ',
                                $r->status, $r->status_message));

     $this->assertPattern('/http:\/\/example.com\/1/',
                          $r->body(),
                          'Not getting url back');
     $this->assertPattern('/tagtwo/',
                          $r->body(),
                          'Not getting tagtwo back');

     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       'failed to load xml');

   }

   function testRelatedTags () {
     $r = relatedTags(new folksoQuery(array(),
                                      array('folksotag' => 'tagone'),
                                      array()),
                      $this->dbc,
                      $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'This is not my beautiful folksoResponse object');
     $this->assertEqual($r->status, 204,
                        'There should not be any related tags: ' . $r->status . $r->body());
     $this->expectError();
     $baddb = relatedTags(new folksoQuery(array(),
                                          array('folksotag' => 'tagone'),
                                          array()),
                          new folksoDBconnect('localhost', 'hoohaa',
                                              'hoohaa', 'hoohaa'),
                          $this->fks2);
     $this->assertIsA($baddb, folksoResponse,
                      'Object creation problem with bad DB');
     $this->assertEqual($baddb->status, 500,
                        'Incorrect http status: ' . $baddb->status);


   }


   function testAutoCompleteTags() {
     $r = autoCompleteTags(new folksoQuery(array(),
                                           array('folksoautotag' => 't'),
                                           array()),
                           $this->dbc,
                           $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'problem w/ object creation');
     $this->assertEqual(200, $r->status,
                        sprintf('getting error msg with autocompleteTags: %d %s',
                                $r->status, $r->status_message));
   }

   function testTagMerge() {
     $this->fks->startSession('rambo-2009-001', true);
     $r = tagMerge(new folksoQuery(array(),
                                   array('folksotag' => 'tagone',
                                         'folksotarget' => 'tagtwo'),
                                   array()),
                   $this->dbc,
                   $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'Problem with object creation');
     $this->assertEqual($r->status, 403,
                        'Rimbaud does not get to merge tags');

     $this->fks2->startSession('marcelp-2009-001', true);
     $r2 = tagMerge(new folksoQuery(array(),
                                    array('folksotag' => 'tagone',
                                          'folksotarget' => 'tagtwo'),
                                    array()),
                    $this->dbc3,
                    $this->fks2);

     $this->assertEqual(204, $r2->status,
                        sprintf('tagmerge returns error: %d %s %s',
                                $r2->status, $r2->status_message, $r2->body()));
     $h = headCheckTag(new folksoQuery(array(),
                                       array('folksotag' => 'tagone'),
                                       array()),
                       new folksoDBconnect('localhost', 'tester_dude', 
                                           'testy', 'testostonomie'),
                       $this->fks2);

     $this->assertEqual(404, $h->status,
                        'tagone tag should not exist after merge');
     $rbad = tagMerge(new folksoQuery(array(),
                                      array('folksotag' => 'tagone',
                                            'folksotarget' => 'emacs'),
                                      array()),
                      $this->dbc2,
                      $this->fks2);
     $this->assertEqual(404, $rbad->status,
                        sprintf('fake tag should return 404: %s %s %s',
                                $rbad->status,
                                $rbad->status_message,
                                $rbad->body()));


   }

   function testDeleteTag() {
     $r = deleteTag(new folksoQuery(array(),
                                    array('folksotag' => 'tagone'),
                                    array()),
                    $this->dbc,
                    $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'problem with object creation');

     $this->assertEqual($r->status, 403,
                        'anonymous delete tag should return 403');
     $this->fks->startSession('vicktr-2009-001', true);
     $r2 = deleteTag(new folksoQuery(array(),
                                     array('folksotag' => 'tagone'),
                                     array()),
                     $this->dbc,
                     $this->fks);
     
     $this->assertEqual(204, $r2->status,
                        sprintf('deleteTag returning error %s %s %s',
                                $r2->status, 
                                $r2->status_message,
                                $r2->body()));

     $r2 = deleteTag(new folksoQuery(array(),
                                     array('folksotag' => 'bullshit'),
                                     array()),
                     $this->dbc2,
                     $this->fks);
     $this->assertIsA($r2, folksoResponse,
                      'problem with object creation on bad tag delete');
     $this->assertEqual($r2->status, 404,
                        'Not getting correct status (404) on bad tag delete: '
                        . $r2->status);
   }
   function testByAlpha(){
     $r = byalpha(new folksoQuery(array(),
                                  array('folksobyalpha' => 'ta'),
                                  array()),
                  $this->dbc,
                  $this->fks);

     $this->assertIsA($r, folksoResponse,
                      'problem with object creation');
     $this->assertEqual($r->status, 200,
                        'byalpha not returning 200');
     $this->assertPattern('/tagone/', $r->body(),
                          'Body odes not include tagone');
     $r2 = byalpha(new folksoQuery(array(),
                                   array('folksobyalpha' => 'zor'),
                                   array()),
                   $this->dbc2,
                   $this->fks2);
     $this->assertEqual(204, 
                        $r2->status,
                        sprintf('No corresponding tags should be a 204 %s %s %s',
                                $r2->status, $r2->status_message, $r2->body()));

   }

   function testRenameTag(){
     $r = renameTag(new folksoQuery(array(),
                                    array('folksonewname' => 'emacs',
                                          'folksotag' => 'tagone'),
                                    array()),
                    $this->dbc,
                    $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'Problem with object creation');
     $this->assertEqual($r->status, 403,
                        'Anonymous user should fail with 403: ' . $r->status);

     $this->fks->startSession('vicktr-2009-001', true);
     $r2 = renameTag(new folksoQuery(array(),
                                    array('folksonewname' => 'emacs',
                                          'folksotag' => 'tagone'),
                                    array()),
                     $this->dbc,
                     $this->fks);

     $this->assertEqual(204, $r2->status,
                        'tag rename should return 204');
     
     $h = headCheckTag(new folksoQuery(array(),
                                       array('folksotag' => 'emacs'),
                                       array()),
                       $this->dbc2,
                       $this->fks2);
     $this->assertEqual($h->status, 200,
                        'No tag with new tag name');
     $h2 = headCheckTag(new folksoQuery(array(),
                                        array('folksotag' => 'tagone'),
                                        array()),
                        $this->dbc3,
                        $this->fks3);
     $this->assertEqual($h2->status, 404,
                        'Old tag name is still present');
   }
   function testAllTags () {
     $r = allTags(new folksoQuery(array(),
                                  array(),
                                  array()),
                  $this->dbc,
                  $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'problem with object creation');
     $this->assertEqual(200, $r->status,
                        'all tags not returning a 200');
     $this->assertPattern('/tagone/',
                          $r->body(),
                          'Missing data');
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       'xml failed to load');
   }

}//end class

$test = &new testOffolksotag();
$test->run(new HtmlReporter());