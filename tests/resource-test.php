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

   function testGet () {
     $q = new folksoQuery(array(), 
                          array('folksores' => 'http://example.com/1'),
                          array());
     $cred = new folksoWsseCreds('zork');
     $r = getTagsIds($q, $cred, $this->dbc);
     $this->assertIsA($r, folksoResponse, 
                'getTagsIds() does not return a folksoResponse object');
     print $r->body();

   }
}//end class

$test = &new testOfResource();
$test->run(new HtmlReporter());