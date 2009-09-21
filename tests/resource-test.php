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