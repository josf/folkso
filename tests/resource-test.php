<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include('/var/www/resource.php');
include('dbinit.inc');

class testOfResource extends  UnitTestCase {
  public $dbc;
  public $dbc2;
  public $dbc3;
  public $cred;

  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc2 = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->dbc3 =new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
     $this->cred = new folksoWsseCreds('zork');    
  }

  function testIsHead () {
     $q = new folksoQuery(array(), 
                          array('folksores' => 'http://example.com/1'),
                          array());
     $cred = new folksoWsseCreds('zork');
     $dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                'testy', 'testostonomie');
     $r = isHead($q, $cred, $dbc);
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
     $r2 = isHead($q2, $cred, $dbc);
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
     $cred = new folksoWsseCreds('zork');
     $dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                'testy', 'testostonomie');
     $r = getTagsIds($q, $cred, $dbc);
     $this->assertIsA($r, folksoResponse, 
                'getTagsIds() does not return a folksoResponse object');
     $this->assertEqual($r->status, 200, 
                        'getTagsIds returns incorrect status');
     $this->assertTrue(strlen($r->body()) > 1, 
                       'Not returning any body');
     $this->assertPattern('/tagone/',
                          $r->body(),
                          '"tagone" tag not found in response body');

     $r2 = getTagsIds(new folksoQuery(array(),
                                      array('folksores' => 'http://example.com/1',
                                            'folksodatatype' => 'xml'),
                                      array()),
                      new folksoWsseCreds('zork'),
                      $this->dbc);
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
     $cred = new folksoWsseCreds('zork');
     $dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                'testy', 'testostonomie');
     $r = getTagsIds($q, $cred, $dbc);
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
     $cred = new folksoWsseCreds('zork');
     $dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                'testy', 'testostonomie');
     $r = tagCloudLocalPop($q, $cred, $dbc);
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
     $r2 = tagCloudLocalPop($q2, $cred, $dbc2);
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
     $r3 = tagCloudLocalPop($q3, $cred, $dbc3); 
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
     $cred = new folksoWsseCreds('zork');
     $r = visitPage($q,  $cred, $dbc);
     $this->assertIsA($r, folksoResponse, 
                      "visitPage() not returning a folksoResponse object");
     $this->assertEqual($r->status, 202, 
                        'visitPage() not returning 202 on cache request. Warning: this might depend on the state of the cache.');
     for ($i = 0; $i < 6; ++$i ) {
       visitPage($q, $cred, new folksoDBconnect('localhost', 'tester_dude',
                                                    'testy', 'testostonomie'));
     }
     $is = isHead(new folksoQuery(array(), 
                                  array('folksores' => 'http://newone.com'),
                                  array()),
                  $cred,
                  new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie'));

     $this->assertEqual($is->status, 200,
                        "isHead() not reporting creation of new resource by visitPage()");
   }

   function testAddResourced () {
     $cred = new folksoWsseCreds('zork');
     $r = addResource(new folksoQuery(array(),
                                      array('folksonewtitle' => 'New new!',
                                            'folksores' => 'http://newone.com'),
                                      array()),
                      $cred,
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie')
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
                  $cred,
                  new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie'));

     $this->assertEqual($is->status, 200,
                        "isHead() not reporting creation of new resource by addResource()");

   }

   function testTagResource() {
     $cred = new folksoWsseCreds('zork');
     $r = tagResource(new folksoQuery(array(),
                                      array('folksores' => 'http://example.com/4',
                                            'folksotag' => 'tagone'),
                                      array()),
                      $cred,
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie')
                      );
     $this->assertIsA($r, folksoResponse, "tagResource not returning Response object");
     $this->assertEqual($r->status, 200,
                        "tagResource throws error");
     $r2 = getTagsIds(new folksoQuery(array(),
                                  array('folksores' => 'http://example.com/4'),
                                  array()),
                      $cred,
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie')
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
                      $cred,
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie')
                      );
     $this->assertEqual($r3->status, 404,
                        "Tagging unknown resource should return 404");

     // Unknown tag
     $r4 = tagResource(new folksoQuery(array(),
                                       array('folksores' => 'http://example.com/4',
                                             'folksotag' => 'pistonengine'),
                                       array()),
                       $cred,
                       new folksoDBconnect('localhost', 'tester_dude',
                                           'testy', 'testostonomie')
                       );
     $this->assertEqual($r4->status, 404,
                        "Tagging with unknown tag should return 404");
     $this->assertEqual($r4->statusMessage,
                        "Tag does not exist",
                        "Bad tag not appearing as such");
   }
   function testUnTag () {
     $cred = new folksoWsseCreds('zork');
     $r = unTag(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1',
                                      'folksotag' => 'tagone',
                                      'folksodelete' => 1),
                                array()),
                $cred,
                new folksoDBconnect('localhost', 'tester_dude',
                                    'testy', 'testostonomie'));

     $this->assertIsA($r, folksoResponse,
                      'unTag not return a folksoResponse object');
     $this->assertEqual($r->status, 200,
                        'Error code on unTag request');
     $h = getTagsIds(new folksoQuery(array(),
                                     array('folksores' => 'http://example.com/1'),
                                     array()),
                     $cred,
                     new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'));
     $this->assertEqual($h->status, 200, 
                        'getTagsIds not returning a 200. Must be a problem');
     $this->assertNoPattern('/tagone/',
                             $h->body(),
                             'removed tag still present in returned tags');
   }

   function testRmRes() {
     $cred = new folksoWsseCreds('zork');

     $h1 = isHead(new folksoQuery(array(),
                                  array('folksores' => 'http://example.com/1'),
                                  array()),
                     $cred,
                     new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'));
     $this->assertEqual($h1->status, 200,
                        'example.com/1 not present acorrding to isHead. Test pb.');

     $r = rmRes(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1'),
                                array()),
                $cred,
                new folksoDBconnect('localhost', 'tester_dude',
                                    'testy', 'testostonomie'));
     $this->assertIsA($r, folksoResponse,
                      'rmRes does not return a folksoResponse object');
     $this->assertEqual($r->status, 200, 
                        'rmRes returns error code' 
                        . $r->status . $r->statusMessage . $r->body());
     $h = isHead(new folksoQuery(array(),
                                  array('folksores' => 'http://example.com/1'),
                                  array()),
                     $cred,
                     new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'));

     $this->assertEqual($h->status, 404, 
                        'Removed resource still present in DB' . $r->status);
   }

   function testResEans() {
     $r = resEans(new folksoQuery(array(),
                                  array('folksores' => 'http://example.com/1'),
                                  array()),
                  $this->cred,
                  $this->dbc);
     $this->assertIsA($r, folksoResponse,
                      'Problem with object creation');
     $this->assertEqual($r->status, 404,
                        'Should not be any ean13 data yet');
     $r2 = assocEan13(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1',
                                      'folksoean13' => '1234567890123'),
                                array()),
                $this->cred,
                new folksoDBconnect('localhost', 'tester_dude',
                                    'testy', 'testostonomie'));

     $r2bis = assocEan13(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/2',
                                      'folksoean13' => '1234567890123'),
                                array()),
                $this->cred,
                new folksoDBconnect('localhost', 'tester_dude',
                                    'testy', 'testostonomie'));
     $this->assertEqual($r2->status, 200,
                        'Failed to associate ean13 to resource, following test may fail');
     $cl = getTagsIds(new folksoQuery(array(),
                                      array('folksores' => 'http://example.com/2',
                                            'folksoean13' => 1,
                                            'folksodatatype' => 'xml'),
                                      array()),
                      $this->cred,
                      new folksoDBconnect('localhost', 'tester_dude',
                                                'testy', 'testostonomie'));               
     $this->assertEqual($cl->status, 200,
                        'Ean data not there');

     $r3 = resEans(new folksoQuery(array(),
                                   array('folksores' => 'http://example.com/1'),
                                   array()),
                   $this->cred, 
                   $this->dbc2);
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
     $cred = new folksoWsseCreds('zork');
     $r = assocEan13(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1',
                                      'folksoean13' => '1234567890123'),
                                array()),
                $cred,
                new folksoDBconnect('localhost', 'tester_dude',
                                    'testy', 'testostonomie'));
     $this->assertIsA($r, folksoResponse,
                      'assocEan13 does not return a folksoResponse object');
     $this->assertEqual($r->status, 200,
                        'assocEan13 returns error : ' . $r->status . $r->body());

     $cl = getTagsIds(new folksoQuery(array(),
                                            array('folksores' => 1,
                                                  'folksoean13' => 1,
                                                  'folksodatatype' => 'xml'),
                                            array()),
                            $cred,
                            new folksoDBconnect('localhost', 'tester_dude',
                                                'testy', 'testostonomie'));                 
     $this->assertPattern('/ean13/i',
                          $cl->body(),
                          'Did not find "ean13" in xml response');
       
     $this->assertPattern('/1234567890123/',
                          $cl->body(),
                          'Did not find ean13 data'. $cl->body());
   }

   function testModify() {
     $cred = new folksoWsseCreds('zork'); 
     /** setup oldean **/
     $su = assocEan13(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1',
                                      'folksoean13' => '1234567890123'),
                                      array()),
                      $cred,
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'));
     /** do the deed **/
     $r = modifyEan13(new folksoQuery(array(),
                                     array('folksores' => 'http://example.com/1',
                                           'folksooldean13' => '1234567890123',
                                           'folksonewean13' => '1111111111111'),
                                     array()),
                     $cred,
                     new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'));
     $this->assertIsA($r, folksoResponse,
                      'not returning folksoReponse object');
     $this->assertEqual($r->status, 200,
                        'Error message on ean13 modification' . $r->status . $r->status_message . $r->body());
     
     $cl = getTagsIds(new folksoQuery(array(),
                                            array('folksores' => 1,
                                                  'folksoean13' => 1,
                                                  'folksodatatype' => 'xml'),
                                            array()),
                            $cred,
                            new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'));
     $this->assertEqual(200, $cl->status,
                        'Problem getting resource data back.');
     $this->assertPattern('/111111111/', $cl->body(),
                          'Not finding new ean13');
     $this->assertNoPattern('/1234567/', $cl->body(),
                            'Still finding old ean13');

}


   function testDeleteEan13 () {
     $cred = new folksoWsseCreds('zork'); 
     /** setup oldean **/
     $su = assocEan13(new folksoQuery(array(),
                                array('folksores' => 'http://example.com/1',
                                      'folksoean13' => '1234567890123'),
                                      array()),
                      $cred,
                      new folksoDBconnect('localhost', 'tester_dude',
                                          'testy', 'testostonomie'));
     /** do the deed **/
     $r = deleteEan13(new folksoQuery(array(),
                                     array('folksores' => 'http://example.com/1',
                                           'folksoean13' => '1234567890123'),
                                     array()),
                     $cred,
                     new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'));
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
                            $cred,
                            new folksoDBconnect('localhost', 'tester_dude',
                                         'testy', 'testostonomie'));
     $this->assertNoPattern('/1234567/', $cl->body(),
                            'Still finding old ean13');

   }


}//end class

$test = &new testOfResource();
$test->run(new HtmlReporter());