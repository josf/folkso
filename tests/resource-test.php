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
    $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                     'testy', 'testostonomie');
  }

  function testIsHead () {
     $q = new folksoQuery(array(), 
                          array('folksores' => 'http://example.com/1'),
                          array());
     $cred = new folksoWsseCreds('zork');
     $r = isHead($q, $cred, $this->dbc);
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
     $r2 = isHead($q2, $cred, $this->dbc);
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
     $r = getTagsIds($q, $cred, $this->dbc);
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
     $r = getTagsIds($q, $cred, $this->dbc);
     $this->assertEqual($r->status, 200,
                        'getTagsIds (xml) returns error status');
     
     $this->assertPattern('/<taglist/',
                          $r->body(),
                          'Does not look like xml'
                          );
     

   }


}//end class

$test = &new testOfResource();
$test->run(new HtmlReporter());