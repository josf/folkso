<?php
require_once('unit_tester.php');
require_once('reporter.php');
require('folksoTags.php');
require('folksoSearch.php');
require('dbinit.inc');

class testOffolksoSearch extends  UnitTestCase {
 
  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }

   function testSearchQueryParser () {

         $kw = new folksoSearchKeyWordSetUserAdmin();
         $this->assertIsA($kw, folksoSearchKeyWordSetUserAdmin,
                          "keyword class not correctly instantiated");
         $s = new folksoSearchQueryParser($kw);
         $this->assertIsA($s, folksoSearchQueryParser,
                          "ob prob");


   }

   function testParserPreparation () {
     $kw = new folksoSearchKeyWordSetUserAdmin();
     $s = new folksoSearchQueryParser($kw);
     
     $cleaned = $s->cleanQueryString('hey  there a everybody');;
     $this->assertTrue(is_array($cleaned), 'cleanQueryString not returning array');
     $this->assertEqual(count($cleaned), 3,
                        "cleaned should be 3 elements long, not: " . count($cleaned));
     $this->assertEqual($cleaned[0], 'hey',
                        'cleanQueryString incorrect: /' . implode("/ /", $cleaned) . '/'
                        . " elem 0 should be 'hey' " . $cleaned[0]);
     $this->assertEqual($cleaned[1], 'there',
                        'cleanQueryString incorrect: /' . implode("/ /", $cleaned) . '/'
                        . " elem 1 should be 'there' " . $cleaned[1]);
     $this->assertEqual($cleaned[2], 'everybody',
                        'cleanQueryString incorrect: /' . implode("/ /", $cleaned) . '/'
                        . " elem 2 should be 'everybody': " . $cleaned[2]);

    }
   

   function testSearchKeyWord () {
     $kw = new folksoSearchKeyWordSetUserAdmin();
     $this->assertIsA($kw, folksoSearchKeyWordSetUserAdmin,
                      "keyword class not correctly instantiated");
     $this->assertFalse($kw->isKeyWord("bob"),
                        "should return false for unregistered keyword");
     $this->assertTrue($kw->isKeyWord("name:"),
                       "isKeyWord should return true for name:");

     $this->assertTrue($kw->isNotStopWord("bob"),
                       "isNotStopWord should return true for 'bob'");
     
   }

   function testParser () {
     $kw = new folksoSearchKeyWordSetUserAdmin();
     $s = new folksoSearchQueryParser($kw);
    
     $test_query1 = 'name a something name: bob';
     $filt = $s->cleanQueryString($test_query1);
     $this->assertTrue(is_array($filt), "cleanQueryString not outputting array");
     $this->assertEqual(count($filt), 4,
                        "cleanQueryString not outputting enoug elements (should be 4) : "
                        . count($filt));


     $parsed = $s->parseString($test_query1);
     $this->assertTrue(isset($parsed['default:']),
                       "default keyword not being used on leading words");
     $this->assertEqual($parsed['default:'][1], 'something',
                        "Incorrect words in default group: " . $parsed[1] 
                        . " instead of 'something'");
     $this->assertEqual($parsed['name:'][0], 'bob',
                        "Not picking up bob in 'name:' sub array");


     $mich = $s->parseString('default: Michel');
     $this->assertEqual($mich['default:'][0], "michel", 
                        "Incorrect results for 'default: Michel':" 
                        . $mich['default:'][0]);
     print_r($mich);
   }

}//end class

$test = &new testOffolksoSearch();
$test->run(new HtmlReporter());