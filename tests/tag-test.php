<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('dbinit.inc');
require_once('folksoTag.php');

class testOffolksotag extends  UnitTestCase {
 
  public $dbc;
  function setUp() {
    test_db_init(100);
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
     $this->assertIsA($r, 'folksoResponse',
                      'not creating Response object');
     $this->assertEqual(200, $r->status,
                        'Error returned by headcheck: ' . $r->status . " " .$r->statusMessage . " " . $r->error_body);
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
     $this->assertIsA($r2, 'folksoResponse',
                      'Bad resource not returning folksoResponse');
     $this->assertEqual($r2->status, 404,
                        'Not getting 404 for incorrect tag');

     $this->fks3->startSession('gustav-2010-001', true);
     $r3 = headCheckTag(new folksoQuery(array(), 
                                        array('folksotag' => 'tagtwo',
                                              'folksousertag' => '1'),
                                        array()),
                        $this->dbc3,
                        $this->fks3);
     $this->assertEqual($r3->status, 200,
                        'User tag option should return 200 here: ' . $r3->status 
                        . " " . $r3->statusMessage);



   }

   function testGetTag() {
     $r = getTag(new folksoQuery(array(),
                                 array('folksotag' => 'tagone'),
                                 array()),
                 $this->dbc,
                 $this->fks);
     $this->assertIsA($r, 'folksoResponse',
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
     $this->assertEqual($r->status, 401,
                        'Unknown user should provoke a 401 on singlePostTag:' . $r->status);

     $this->fks2->startSession('marcelp-2010-001', true);
     $r2 = singlePostTag(new folksoQuery(array(),
                                 array('folksonewtag' => 'emacs'),
                                 array()),
                        $this->dbc,
                        $this->fks2);



     $this->assertIsA($r2, 'folksoResponse',
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
     $this->assertIsA($r, 'folksoResponse',
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
     $this->assertIsA($r, 'folksoResponse',
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

     // checking with bad tag
     $rbad = fancyResource(new folksoQuery(array(), array(),
                                           array('folksotag' => 'blah'),
                                           array()),
                           $this->dbc, $this->fks);
     $this->assertEqual($rbad->status, 404, 
                        'Bad tag to fancyResource. Expecting 404, got: ' .
                        $rbad->status . ' ' . $rbad->statusMessage);

   }

   function testFancyListLength () {
     $r = fancyResource(new folksoQuery(array(),
                                        array('folksotag' => 'dyn1'),
                                        array()),
                        $this->dbc,
                        $this->fks);
     $this->assertEqual($r->status, 200,
                        'Should get 200 response for tag dyn1, not: '
                        . $r->status . ' ' . $r->statusMessage);
     $xml = new DOMDocument();
     $this->assertTrue($xml->loadXML($r->body()),
                       'bad xml');
     $xp = new DOMXpath($xml);
     $resources = $xp->query('//tag/resourcelist/resource');

     /*
      * There are 125 total resources tagged as dyn.
      */
     $this->assertEqual($resources->length, 50,
                        'There should be 50 resources tagged with dyn1 returned, not: '
                        . $resources->length);
     $rlimit = fancyResource(new folksoQuery(array(),
                                             array('folksotag' => 'dyn1',
                                                   'folksooffset' => '100'),
                                             array()),
                             $this->dbc,
                             $this->fks);
     $xml2 = new DOMDocument();
     $this->assertTrue($xml2->loadXML($rlimit->body()),
                      'bad xml in request with offset');
     $xp2  =  new DOMXpath($xml2);
     $offset = $xp2->query('//tag/resourcelist/resource');
     $this->assertEqual($offset->length, 25,
                        'Offset of 100 should have length of 25, not: '
                        . $offset->length);
     $off_param = $xp2->query('//tag/resourcelist');
     $offful = $off_param->item(0)->attributes->getNamedItem('offset')->nodeValue;
     $this->assertEqual($offful, 100,
                        'Not retrieving offset value from XML, getting: '
                        . $offful);

   }


   function testAtomFancyResource () {
     $q = new folksoQuery(array(),
                          array('folksotag' => 'tagone', 
                                'folksofeed' => 'atom'),
                          array());
     $q->applyOutput = 'atom';
     $r = fancyResource($q, $this->dbc, $this->fks);
     $this->assertIsA($r, 'folksoResponse', "Not getting fkResponse obj");
     $this->assertEqual($r->styleSheet, 'atom_fancyResource.xsl',
                        '$r->styleSheet should be atom_fancyResource.xsl, getting: '
                        . $r->styleSheet);

     $this->assertTrue(file_exists($r->loc->xsl_dir . $r->styleSheet),
                       "Stylesheet not found. This will cause trouble later on.");

     $this->assertEqual($r->status, 200, 
                        "Should get status of 200, not: " . $r->status 
                        . " with message ". $r->statusMessage);

     $this->assertEqual($r->contentType(), 'Content-Type: application/atom+xml',
                        'Incorrect content type: ' . $r->contentType());
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       'malformed xml on atom fancyResource request');


     $atom = $r->bodyXsltTransform();
     $this->assertTrue(is_string($atom), 
                       "result of atom xslt transform is not a string");
     $zzz = new DOMDocument();
     $this->assertTrue($zzz->loadXML($atom),
                       "malformed xml after atom xslt transformation");
     $this->assertPattern('/<feed/', 
                          $atom,
                          'atom output does not look like a feed');
     $this->assertPattern('/Atom/',
                          $atom,
                          'atom output does not look like it is an Atom feed '
                          .' (Did not find the string "Atom" in feed');
     $r->prepareHeaders();
     //     print $r->debug;

   }


   function testTagsToTaglist() {
     $in = 'Sometag::sometag - Another::another';
     $out = tagsToTaglist($in);
     $this->assertPattern('/^<ul><li><a/',
                          $out,
                          'Basic list formatting not working for tagsToTaglist().'
                          .' expected "<ul><li><a" at beginngin of stirng');
     $this->assertPattern('/>Sometag<\/a>/',
                          $out,
                          'tagsToList() not formatting display tag text: ' . $out);

     $this->assertPattern('/href=[^<>]+sometag/',
                          $out,
                          'tagsToList() ignoring tagnorm in href: ' . $out);

     $blocks = explode('-', $in);
     $this->assertEqual(count($blocks), 2);
     
     $tinyblocks = explode('::', $blocks[0]);
     $this->assertEqual(trim($tinyblocks[0]), 'Sometag');
     $this->assertEqual(trim($tinyblocks[1]), 'sometag');

     $tt = new folksoTagLink(trim($tinyblocks[1]));
     $this->assertEqual($tt->getLink(),
                        'http://localhost/tagview.php?tag=sometag');

     $simpleIn = 'Sometag::sometag';
     $simpleOut = tagsToTaglist($simpleIn);
     $this->assertPattern('/href=[^>]+=sometag">Sometag<\/a>/',
                          $simpleOut);



   }



   function testRelatedTags () {
     $r = relatedTags(new folksoQuery(array(),
                                      array('folksotag' => 'tagone'),
                                      array()),
                      $this->dbc,
                      $this->fks);
     $this->assertIsA($r, 'folksoResponse',
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
     $this->assertIsA($baddb, 'folksoResponse',
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
     $this->assertIsA($r, 'folksoResponse',
                      'problem w/ object creation');
     $this->assertEqual(200, $r->status,
                        sprintf('getting error msg with autocompleteTags: %d %s',
                                $r->status, $r->status_message));
   }

   function testTagMerge() {
     $this->fks->startSession('michl-2010-001', true);
     $r = tagMerge(new folksoQuery(array(),
                                   array('folksotag' => 'tagone',
                                         'folksotarget' => 'tagtwo'),
                                   array()),
                   $this->dbc,
                   $this->fks);
     $this->assertIsA($r, 'folksoResponse',
                      'Problem with object creation');
     $this->assertEqual($r->status, 403,
                        'Michel de Montaigne does not get to merge tags, should get 403 here, not: '
                        . $r->status);

     $this->fks2->startSession('marcelp-2010-001', true);
     $r2 = tagMerge(new folksoQuery(array(),
                                    array('folksotag' => 'tagone',
                                          'folksotarget' => 'tagtwo'),
                                    array()),
                    $this->dbc3,
                    $this->fks2);

     $this->assertEqual(204, $r2->status,
                        sprintf('tagmerge returns error: %d %s %s',
                                $r2->status, $r2->status_message, $r2->errorBody()));
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
     $this->assertIsA($r, 'folksoResponse',
                      'problem with object creation');

     $this->assertEqual($r->status, 401,
                        'anonymous delete tag should return 401');
     $this->fks->startSession('vicktr-2010-001', true);
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
     $this->assertIsA($r2, 'folksoResponse',
                      'problem with object creation on bad tag delete');
     $this->assertEqual($r2->status, 404,
                        'Not getting correct status (404) on bad tag delete: '
                        . $r2->status);
   }


   function testUserDeleteTag() {
     $r = userDeleteTag(new folksoQuery(array(),
                                        array('folksotag' => 'tagone'),
                                        array()),
                        $this->dbc,
                        $this->fks);
     $this->assertEqual($r->status, 401,
                        'Unknown user should provoke 401: ' . $r->status);
     $this->fks2->startSession('gustav-2010-001', true);
     $r2 = userDeleteTag(new folksoQuery(array(),
                                         array('folksotag' => 'tagone'),
                                         array()),
                         $this->dbc2,
                         $this->fks2);
     $this->assertEqual($r2->status, 204,
                        'Successful user tag delete should return 204: ' . $r2->status);
     $r3 = headCheckTag(new folksoQuery(array(), 
                                         array('folksotag' => 'tagone',
                                               'folksousertag' => '1'),
                                         array()),
                        $this->dbc3,
                        $this->fks2);
     $this->assertEqual($r3->status, 404,
                        "Tag should have been removed from user's tags. "
                        ." headCheckTag should return 404 here, not: " 
                        . $r3->status . " " . $r3->statusMessage);
                                         
                                        
   }
   function testByAlpha(){
     $r = byalpha(new folksoQuery(array(),
                                  array('folksobyalpha' => 'ta'),
                                  array()),
                  $this->dbc,
                  $this->fks);

     $this->assertIsA($r, 'folksoResponse',
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
     $this->assertIsA($r, 'folksoResponse',
                      'Problem with object creation');
     $this->assertEqual($r->status, 401,
                        'Anonymous user should fail with 401: ' . $r->status);

     $this->fks2->startSession('vicktr-2010-001', true);
     $r2 = renameTag(new folksoQuery(array(),
                                    array('folksonewname' => 'emacs',
                                          'folksotag' => 'tagone'),
                                    array()),
                     $this->dbc,
                     $this->fks2);

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

   function testTagPage () {
     $q = new folksoQuery(array(),
                          array('folksotag' => 'tagone',
                                'folksodatatype' => 'xml'),
                          array());
     $r = tagPage($q, $this->dbc, $this->fks);
     $this->assertEqual($r->status, 200,
                        "tagPage: expecting 200, got : " . $r->status . ' ' . $r->statusMessage);

     $resp = fancyResource($q, $this->dbc, $this->fks);
     $this->assertEqual($resp->status, 200, "fancy query not returning 200");
     //     print '<pre>fancy' . htmlentities($resp->body()) . '</pre>';

     $this->assertIsA($r, 'folksoResponse', 'Pb with object creation');
     $this->assertPattern('/<tagpage>/',
                          $r->body(),
                          'XML boilerplate missing from tagPage response');
     print '<pre><code>' . htmlentities($r->body()) . '</code></pre>';
     //     print '<pre><code> ' . htmlentities($r->bodyXsltTransform()) . '</pre></code>';
     $rbad = tagPage(new folksoQuery(array(), array(), array('folksotag' => 'blork',
                                                             'folksodatatype' => 'xml')),
                     $this->dbc, $this->fks);
     $this->assertEqual($rbad->status, 404, 
                        "Bad tag for tagPage. Expecting 404, got: "
                        . $rbad->status .  ' ' . $rbad->statusMessage);

          


   }

   function testAllTags () {
     $r = allTags(new folksoQuery(array(),
                                  array(),
                                  array()),
                  $this->dbc,
                  $this->fks);
     $this->assertIsA($r, 'folksoResponse',
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