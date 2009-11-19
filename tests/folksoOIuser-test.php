<?php
require_once('unit_tester.php');
require_once('reporter.php');
require('folksoTags.php');
require('folksoOIuser.php');
require('dbinit.inc');

class testOffolksoOIuser extends  UnitTestCase {
  public $dbc;

  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }

   function testfolksoOIuser () {
         $oi   = new folksoOIuser($this->dbc);
         $this->assertIsA($oi, folksoOIuser,
                               'object creation failed');
         $this->assertIsA($oi, folksoUser,
                          'object hierarchy problem. folksoOIusers should be folksoUser objs');
         $this->assertFalse($oi->exists('http://idont-exist.com'),
                            'bad oid url should return false');
         $this->assertFalse($oi->validateLoginId(''),
                            'empty login id should be false');
         $this->assertTrue($oi->validateLoginId('http://bob.com'),
                           'simple but valid url fails oid check');
         $this->assertFalse($oi->validateLoginId('bob.kom'),
                            'invalid oid url should fail');
   }
}//end class

$test = &new testOffolksoOIuser();
$test->run(new HtmlReporter());