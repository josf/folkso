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
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc2 = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->db3 = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->cred = new folksoWsseCreds('zork');
  }

   function testHeadCheck () {
     $r = headCheckTag(new folksoQuery(array(),
                                       array('folksotag' => 'tagone'),
                                       array()),
                       $this->cred,
                       $this->dbc);
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
                       $this->cred,
                       $this->dbc);
     $this->assertIsA($r2, folksoResponse,
                      'Bad resource not returning folksoResponse');
     $this->assertEqual($r2->status, 404,
                        'Not getting 404 for incorrect tag');

   }

   function testGetTag() {
     $r = getTag(new folksoQuery(array(),
                                 array('folksotag' => 'tagone'),
                                 array()),
                 $this->cred,
                 $this->dbc);
     $this->assertIsA($r, folksoResponse,
                      'not creating Response object');
     $this->assertEqual(200, $r->status,
                        'Error returned by getTag');

   }

   function testSinglePostTag() {
     $r = singlePostTag(new folksoQuery(array(),
                                 array('folksonewtag' => 'emacs'),
                                 array()),
                 $this->cred,
                 $this->dbc);

     $this->assertIsA($r, folksoResponse,
                      'Problem with object creation');
     $this->assertEqual($r->status,
                        201,
                        'Tag creation returning error: ' . $r->status);
     $t = getTag(new folksoQuery(array(),
                                 array('folksotag' => 'emacs'),
                                 array()),
                 $this->cred,
                 new folksoDBconnect('localhost', 'tester_dude', 
                                     'testy', 'testostonomie'));

     $this->assertEqual(200, $t->status,
                        'New tag not created: ' . $t->status . $t->status_message);
   $h = headCheckTag(new folksoQuery(array(),
                                       array('folksotag' => 'tagone'),
                                       array()),
                       $this->cred,
                     new folksoDBconnect('localhost', 'tester_dude', 
                                         'testy', 'testostonomie'));

   $this->assertEqual($h->status, 200,
                      'headcheck says the tag is still not there');
   }

   function testGetTagResources() {
     $r = getTagResources(new folksoQuery(array(),
                                          array('folksotag' => 'tagone'),
                                          array()),
                          $this->cred,
                          $this->dbc);
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
                          $this->cred,
                          $this->dbc);

     $this->assertEqual(404, $rbad->status,
                        'bad tag should return 404');
   }

   function testFancyResource() {
     $r = fancyResource(new folksoQuery(array(),
                                        array('folksotag' => 'tagone'),
                                        array()),
                        $this->cred,
                        $this->dbc);
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

   }

   function testAutoCompleteTags() {
     $r = autoCompleteTags(new folksoQuery(array(),
                                           array('folksoautotag' => 't'),
                                           array()),
                           $this->cred,
                           $this->dbc);
     $this->assertIsA($r, folksoResponse,
                      'problem w/ object creation');
     $this->assertEqual(200, $r->status,
                        sprintf('getting error msg with autocompleteTags: %d %s',
                                $r->status, $r->status_message));
   }

   function testTagMerge() {
     $r = tagMerge(new folksoQuery(array(),
                                   array('folksotag' => 'tagone',
                                         'folksotarget' => 'tagtwo'),
                                   array()),
                   $this->cred,
                   $this->dbc);
     $this->assertIsA($r, folksoResponse,
                      'Problem with object creation');
     $this->assertEqual(204, $r->status,
                        sprintf('tagmerge returns error: %d %s %s',
                                $r->status, $r->status_message, $r->body()));
     $h = headCheckTag(new folksoQuery(array(),
                                       array('folksotag' => 'tagone'),
                                       array()),
                       $this->cred,
                       new folksoDBconnect('localhost', 'tester_dude', 
                                         'testy', 'testostonomie'));

     $this->assertEqual(404, $h->status,
                        'tagone tag should not exist after merge');
     $rbad = tagMerge(new folksoQuery(array(),
                                      array('folksotag' => 'tagone',
                                            'folksotarget' => 'emacs'),
                                      array()),
                      $this->cred,
                      $this->dbc2);
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
                    $this->cred, 
                    $this->dbc);
     $this->assertIsA($r, folksoResponse,
                      'problem with object creation');
     $this->assertEqual(204, $r->status,
                        sprintf('deleteTag returning error %s %s %s',
                                $r->status, 
                                $r->status_message,
                                $r->body()));
   }
   function testByAlpha(){
     $r = byalpha(new folksoQuery(array(),
                                  array('folksobyalpha' => 'ta'),
                                  array()),
                  $this->cred,
                  $this->dbc);

   }

}//end class

$test = &new testOffolksotag();
$test->run(new HtmlReporter());