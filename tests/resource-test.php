<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include('/var/www/resource.php');
include('dbinit.inc');

class testOfResource extends  UnitTestCase {
  public $dbc;

  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
    
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
                        'visitPage() not returning 202 on cache request');
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

}//end class

$test = &new testOfResource();
$test->run(new HtmlReporter());