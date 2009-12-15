<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoSession.php');
include('folksoTags.php');
include('/var/www/resource.php');
include('dbinit.inc');

class testOfResource extends  UnitTestCase {
  public $dbc;
  public $dbc2;
  public $dbc3;
  public $fks;

  function setUp() {
    test_db_init();
    $lh = 'localhost';
    $td = 'tester_dude';
    $ty = 'testy';
    $tt = 'testostonomie';
    /** not using teardown because this function does a truncate
        before starting. **/
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
  function testTheTest () {
    $this->assertIsA($this->fks, folksoSession,
                     'Not creating session object for tests');
    $this->assertIsA($this->fks->dbc, folksoDBconnect,
                     'Session DBconnect object not present');

  }


  function testIsHead () {
     $q = new folksoQuery(array(), 
                          array('folksores' => 'http://example.com/1'),
                          array());
     $dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                'testy', 'testostonomie');
     $r = isHead($q, $dbc, $this->fks);
     $r->prepareHeaders();
     $this->assertIsA($r, folksoResponse, 
                'getTagsIds() does not return a folksoResponse object');
     $this->assertEqual($r->status, 200, 
                        'getTagsIds returns incorrect status');
     $this->assertNull($r->body(), 'Head method should not return body');
     $this->assertPattern('/HTTP/',
                          $r->headers[0],
                          'Not returning HTTP in first header line'
                          );

     $q2 = new folksoQuery(array(),
                           array('folksores' => 'http://zorkfest.org'),
                           array());
     $r2 = isHead($q2, $this->dbc2, $this->fks2);
     $r2->prepareHeaders();

     $this->assertIsA($r2, folksoResponse,
                      'isHead 404 not returning folksoResponse object');
     $this->assertEqual($r2->status, 404,
                        'Not returning 404 on non-existant resource: ' .$r->status );
  }


   function testGetTagsIds_text () {
     $q = new folksoQuery(array(), 
                          array('folksores' => 'http://example.com/1'),
                          array());
     $dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                'testy', 'testostonomie');
     $r = getTagsIds($q, $dbc, $this->fks);
     $this->assertIsA($r, folksoResponse, 
                'getTagsIds() does not return a folksoResponse object');
     $this->assertEqual($r->status, 200, 
                        'getTagsIds returns incorrect status: ' . $r->status);
     $this->assertTrue(strlen($r->body()) > 1, 
                       'Not returning any body');
     $this->assertPattern('/tagone/',
                          $r->body(),
                          '"tagone" tag not found in response body');

     $r2 = getTagsIds(new folksoQuery(array(),
                                      array('folksores' => 'http://example.com/1',
                                            'folksodatatype' => 'xml'),
                                      array()),
                      $this->dbc2,
                      $this->fks2);
     $this->assertEqual($r2->status, 200,
                        'getTagsIds returns error with xml datatype');
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r2->body()),
                       'failed to load xml');
   }

   function testGetTagsIds_xml () {
     $q = new folksoQuery(array(), 
                          array('folksores' => 'http://example.com/1',
                                'folksodatatype' => 'text/xml'),
                          array());
     $dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                'testy', 'testostonomie');
     $r = getTagsIds($q, $dbc, $this->fks);
     $this->assertEqual($r->status, 200,
                        'getTagsIds (xml) returns error status');
     
     $this->assertPattern('/<taglist/',
                          $r->body(),
                          'Does not look like xml'
                          );
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r->body()),
                       'xml failed to load');
     

   }
   function testTagCloudLocalPop () {
     $q = new folksoQuery(array(),
                          array('folksoclouduri' => 1,
                                'folksores' => 'http://example.com/1'),
                          array());
     $dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                'testy', 'testostonomie');
     $r = tagCloudLocalPop($q, $dbc, $this->fks);
     $this->assertIsA($r, folksoResponse, 
                      'tagCloudLocalPop is not returning a folksoResponse object');
     $this->assertEqual($r->status, 406, 
                        'Incorrect status returned by tagCloudLocalPop');
     $q2 = new folksoQuery(array(),
                           array('folksoclouduri' => 1,
                                 'folksodatatype' => 'xml',
                                 'folksores' => 'http://example.com/1'),
                           array());
     $dbc2 = new folksoDBconnect('localhost', 'tester_dude',
                                 'testy', 'testostonomie');
     $r2 = tagCloudLocalPop($q2, $dbc2, $this->fks2);
     $this->assertIsA($r2, folksoResponse,
                      "tagCloudLocalPop is not returning a Response object (xml request)");
     $this->assertEqual($r2->status, 200,
                        'Incorrect http status for tagCloudLocalPop xml request: ' . $r2->status);

     $this->assertTrue(strlen($r2->body()) > 100, 
                       "No body in xml request");
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r2->body()),
                       'failed to load xml: ' . $r2->body());

     $dbc3 = new folksoDBconnect('localhost', 'tester_dude',
                                 'testy', 'testostonomie');
     $q3 = new folksoQuery(array(),
                           array('folksoclouduri' => 1,
                                 'folksores' => 'http://not-here-at-all.com'),
                           array());
     $r3 = tagCloudLocalPop($q3, $dbc3, $this->fks3); 
     $this->assertIsA($r3, folksoResponse,
                      'tagCloudPop not returning Response object (non-exist request)');
     $this->assertEqual($r3->status, 404,
                        'tagCloudLocalPop not returning 404');
   }

   function testVisitPage () {
     $dbc = new folksoDBconnect('localhost', 'tester_dude',
                                'testy', 'testostonomie');
     $q = new folksoQuery(array(),
                          array('folksores' => 'http://newone.com',
                                'folksovisit' => 1,
                                ),
                          array());
     $r = visitPage($q,  $dbc, $this->fks);
     $this->assertIsA($r, folksoResponse, 
                      "visitPage() not returning a folksoResponse object");
     $this->assertEqual($r->status, 202, 
                        'visitPage() not returning 202 on cache request. Warning: this might depend on the state of the cache.');
     for ($i = 0; $i < 6; ++$i ) {
       visitPage($q, new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'),
                 $this->fks2);
     }
     $is = isHead(new folksoQuery(array(), 
                                  array('folksores' => 'http://newone.com'),
                                  array()),
                  new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie'),
                  $this->fks3);

     $this->assertEqual($is->status, 200,
                        "isHead() not reporting creation of new resource by visitPage()");
   }

   function testAddResourced () {
     $r = addResource(new folksoQuery(array(),
                                      array('folksonewtitle' => 'New new!',
                                            'folksores' => 'http://newone.com'),
                                      array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                      $this->fks
                      );
     $this->assertIsA($r, folksoResponse,
                      'addResource() not returning folksoResponse object');
     $this->assertEqual($r->status, 201, 
                        'addResource() not returning 201');
     $this->assertNotEqual($r->status, 500, 
                           'addResource() returning 500 - DB error');
     $is = isHead(new folksoQuery(array(), 
                                  array('folksores' => 'http://newone.com'),
                                  array()),
                  new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie'),
                  $this->fks2);

     $this->assertEqual($is->status, 200,
                        "isHead() not reporting creation of new resource by addResource()");

   }

   function testTagResource() {
     $sid = $this->fks->startSession('marcelp-2009-001', true);
     $this->assertTrue($this->fks->validateSid($sid),
                       'Producing invalid sid');
     $this->assertTrue($this->fks->checkSession($sid),
                       'Session not considered valid');
     $r = tagResource(new folksoQuery(array(),
                                      array('folksores' => 'http://example.com/4',
                                            'folksotag' => 'tagone'),
                                      array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                      $this->fks
                      );
     $this->assertIsA($r, folksoResponse, "tagResource not returning Response object");
     $this->assertEqual($r->status, 200,
                        "tagResource throws error");
     $r2 = getTagsIds(new folksoQuery(array(),
                                  array('folksores' => 'http://example.com/4'),
                                  array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                      $this->fks2
                      );
     $this->assertEqual($r2->status, 200, 
                        'tagResource failed to tag example.com/4, getTagsIds not returning 200');
     $this->assertNotEqual($r2->status, 204,
                           'getTagsIds returning 204: resource is there but untagged');

     // Unknown resource
     $r3 = tagResource(new folksoQuery(array(),
                                      array('folksores' => 'http://bobworld.com/4',
                                            'folksotag' => 'tagone'),
                                      array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                       $this->fks
                      );
     $this->assertEqual($r3->status, 404,
                        "Tagging unknown resource should return 404: " . $r3->status);

     // Unknown tag
     $r4 = tagResource(new folksoQuery(array(),
                                       array('folksores' => 'http://example.com/4',
                                             'folksotag' => 'pistonengine'),
                                       array()),
                       new folksoDBconnect('localhost', 'tester_dude',
                                           'testy', 'testostonomie'),
                       $this->fks
                       );
     $this->assertEqual($r4->status, 404,
                        "Tagging with unknown tag should return 404");
     $this->assertEqual($r4->statusMessage,
                        "Tag does not exist",
                        "Bad tag not appearing as such");

   }

   function testTagResourceAgain () {
     $sid = $this->fks->startSession('michl-2009-001', true);
     $this->assertTrue($this->fks->validateSid($sid),
                       'Invalid sid');
     $r = tagResource(new folksoQuery(array(),
                                      array('folksores' => 'http://example.com/1',
                                            'folksotag' => 'tagtwo'),
                                      array()),
                      $this->dbc,
                      $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'Not even getting a response object back');
     $this->assertEqual($r->status, 403,
                        'Not getting 403 back on unauthorized user');

   }

   function testUnTag () {
     $sid = $this->fks->startSession('gustav-2009-001', true);
     $r = unTag(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1',
                                      'folksotag' => 'tagone',
                                      'folksodelete' => 1),
                                array()),
                new folksoDBconnect('localhost', 'tester_dude',
                                    'testy', 'testostonomie'),
                $this->fks);

     $this->assertIsA($r, folksoResponse,
                      'unTag not return a folksoResponse object');
     $this->assertEqual($r->status, 200,
                        'Error code on unTag request: ' . $r->status);
     $h = getTagsIds(new folksoQuery(array(),
                                     array('folksores' => 'http://example.com/1'),
                                     array()),
                     new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'),
                     $this->fks2);
     $this->assertEqual($h->status, 200, 
                        'getTagsIds not returning a 200. Must be a problem');
     $this->assertNoPattern('/tagone/',
                             $h->body(),
                             'removed tag still present in returned tags');
   }

   function testRmRes() {

     $h1 = isHead(new folksoQuery(array(),
                                  array('folksores' => 'http://example.com/1'),
                                  array()),
                     new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'),
                  $this->fks);
     $this->assertEqual($h1->status, 200,
                        'example.com/1 not present acorrding to isHead. Test pb.');
     $this->fks->startSession('vicktr-2009-001', true);
     $r = rmRes(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1'),
                                array()),
                new folksoDBconnect('localhost', 'tester_dude',
                                    'testy', 'testostonomie'),
                $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'rmRes does not return a folksoResponse object');
     $this->assertEqual($r->status, 200, 
                        'rmRes returns error code' 
                        . $r->status . $r->statusMessage . $r->body());
     $h = isHead(new folksoQuery(array(),
                                  array('folksores' => 'http://example.com/1'),
                                  array()),
                 new folksoDBconnect('localhost', 'tester_dude',
                                     'testy', 'testostonomie'),
                 $this->fks3);

     $this->assertEqual($h->status, 404, 
                        'Removed resource still present in DB' . $r->status);
   }

   function testResEans() {
     $r = resEans(new folksoQuery(array(),
                                  array('folksores' => 'http://example.com/1'),
                                  array()),
                  $this->dbc,
                  $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'Problem with object creation');
     $this->assertEqual($r->status, 404,
                        'Should not be any ean13 data yet');
     $this->fks2->startSession('marcelp-2009-001', true);
     $r2 = assocEan13(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1',
                                      'folksoean13' => '1234567890123'),
                                array()),
                new folksoDBconnect('localhost', 'tester_dude',
                                    'testy', 'testostonomie'),
                      $this->fks2);
     $this->fks3->startSession('marcelp-2009-001', true);
     $r2bis = assocEan13(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/2',
                                      'folksoean13' => '1234567890123'),
                                array()),
                         new folksoDBconnect('localhost', 'tester_dude',
                                    'testy', 'testostonomie'),
                         $this->fks3);
     $this->assertEqual($r2->status, 200,
                        'Failed to associate ean13 to resource, following test may fail');
     $cl = getTagsIds(new folksoQuery(array(),
                                      array('folksores' => 'http://example.com/2',
                                            'folksoean13' => 1,
                                            'folksodatatype' => 'xml'),
                                      array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                      $this->fks);               

     $this->assertEqual($cl->status, 200,
                        'Ean data not there');

     $r3 = resEans(new folksoQuery(array(),
                                   array('folksores' => 'http://example.com/1'),
                                   array()),
                   $this->dbc2,
                   $this->fks2);
     $this->assertIsA($r3, folksoResponse,
                      'This is not my beautiful folksoResponse object');
     $this->assertEqual($r3->status, 200,
                        'ean13 related resources not showing up ' . $r3->status . " ". 
                        $r3->body());
     $xxx = new DOMDocument();
     $this->assertTrue($xxx->loadXML($r3->body()),
                       'xml does not load');
   }



   function testAssocEan13 () {
     $this->fks->startSession('marcelp-2009-001', true);
     $r = assocEan13(new folksoQuery(array(),
                                     array('folksores' => 'http://example.com/1',
                                      'folksoean13' => '1234567890123'),
                                     array()),
                     new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'),
                     $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'assocEan13 does not return a folksoResponse object');
     $this->assertEqual($r->status, 200,
                        'assocEan13 returns error : ' . $r->status . $r->body());

     $cl = getTagsIds(new folksoQuery(array(),
                                            array('folksores' => 1,
                                                  'folksoean13' => 1,
                                                  'folksodatatype' => 'xml'),
                                            array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                      $this->fks2);
     $this->assertPattern('/ean13/i',
                          $cl->body(),
                          'Did not find "ean13" in xml response');
       
     $this->assertPattern('/1234567890123/',
                          $cl->body(),
                          'Did not find ean13 data'. $cl->body());
   }

   function testModify() {
     /** setup oldean **/
     $this->fks->startSession('marcelp-2009-001', true);
     $su = assocEan13(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1',
                                      'folksoean13' => '1234567890123'),
                                      array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                      $this->fks);

     $this->assertEqual($su->status,
                        200,
                        'Could not associate ean13 with example/1');

     $cl = getTagsIds(new folksoQuery(array(),
                                            array('folksores' => 1,
                                                  'folksoean13' => 1,
                                                  'folksodatatype' => 'xml'),
                                            array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                      $this->fks2);

     $this->assertPattern('/ean13/i',
                          $cl->body(),
                          'Did not find "ean13" in xml response');
       
     $this->assertPattern('/1234567890123/',
                          $cl->body(),
                          'Did not find ean13 data'. $cl->body());


     /** do the deed **/
     $r = modifyEan13(new folksoQuery(array(),
                                     array('folksores' => 'http://example.com/1',
                                           'folksooldean13' => '1234567890123',
                                           'folksonewean13' => '1111111111111'),
                                     array()),
                     new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'),
                      $this->fks);
     $this->assertIsA($r, folksoResponse,
                      'not returning folksoReponse object');
     $this->assertEqual($r->status, 200,
                        'Error message on ean13 modification' . $r->status . $r->status_message . $r->body());
     
     $cl = getTagsIds(new folksoQuery(array(),
                                      array('folksores' => 1,
                                            'folksoean13' => 1,
                                            'folksodatatype' => 'xml'),
                                      array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                      $this->fks3);
     $this->assertEqual(200, $cl->status,
                        'Problem getting resource data back.');
     $this->assertPattern('/111111111/', $cl->body(),
                          'Not finding new ean13');
     $this->assertNoPattern('/1234567/', $cl->body(),
                            'Still finding old ean13');

}


   function testDeleteEan13 () {
     /** setup oldean **/
     $this->fks->startSession('marcelp-2009-001', true);
     $su = assocEan13(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1',
                                      'folksoean13' => '1234567890123'),
                                      array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                      $this->fks);
     /** do the deed **/
     $r = deleteEan13(new folksoQuery(array(),
                                     array('folksores' => 'http://example.com/1',
                                           'folksoean13' => '1234567890123'),
                                     array()),
                     new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'),
                      $this->fks2);

     $this->assertIsA($r, folksoResponse,
                      'not returning folksoReponse object');
     $this->assertEqual(200, $r->status,
                        sprintf('Error returned on ean deletion %d %s <br/>%s',
                                $r->status, $r->status_message, $r->body())
                        );

     $cl = getTagsIds(new folksoQuery(array(),
                                      array('folksores' => 1,
                                            'folksoean13' => 1,
                                            'folksodatatype' => 'xml'),
                                      array()),
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'),
                      $this->fks3);
     $this->assertNoPattern('/1234567/', $cl->body(),
                            'Still finding old ean13');

   }


}//end class

$test = &new testOfResource();
$test->run(new HtmlReporter());