<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoUserQuery.php');
require_once('dbinit.inc');

class testOffolksoUserQuery extends  UnitTestCase {
 
  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }

   function testUserQuery () {
         $uq   = new folksoUserQuery();
         $this->assertIsA($uq, folksoUserQuery,
                               'object creation failed');
         
         $sql = $uq->resourcesByTag('emacs', 'downhome-2009-001');
         $this->assertTrue(is_string($sql),
                           'resourceByTag did not return string');
         $this->assertTrue((strlen($sql) > 50),
                           'resourcesByTag returned a suspiciously short string');
         $this->assertPattern('/downhome/', $sql,
                              'userid not inserted into sql query by resourcesByTag. '.$sql);
         $this->assertPattern('/normalize_tag/', $sql,
                              'conditional query building not working with alphabetical tag');

   }


   function testAddSubscription () {
     $uq = new folksoUserQuery();
     $sql = $uq->addSubscriptionSQL('dyn9', 'rambo-2010-001');
     $this->assertPattern("/normalize_tag\('dyn9'\)/", $sql,
                          "Did not find normalize_tag('dyn9') in sql");

     print $sql;


   }
}//end class

$test = &new testOffolksoUserQuery();
$test->run(new HtmlReporter());