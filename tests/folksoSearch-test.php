<?php
require_once('unit_tester.php');
require_once('reporter.php');
require('folksoTags.php');
require('folksoSearch.php');
require('dbinit.inc');

class testOffolksoSearch extends  UnitTestCase {
 
  function setUp() {
    test_db_init(1);
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

     $this->assertTrue($kw->isKeyWord("fname:"),
                       "isKeyWord should return true for fname:");
     $this->assertTrue($kw->isKeyWord("default:"),
                       "isKeyWord should return true for default:");

     $this->assertTrue($kw->isNotStopWord("bob"),
                       "isNotStopWord should return true for 'bob'");

     $this->assertTrue($kw->isSpecial("and:"),
                       "and: should appear as special");
     
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

     $mich2 = $s->parseString('Michel');
     $this->assertEqual($mich2['default:'][0], "michel",
                        "Incorrect results for implied default, expecting michel: " .
                        $mich2['default:'][0]);

     $complex = $s->parseString('montaigne fname: michel');
     $this->assertEqual($complex['default:'][0], 'montaigne',
                        "Not finding default montaigne");
     $this->assertEqual($complex['fname:'][0], 'michel',
                        "Not finding fname: keyword args");

     //and 
     $ander = $s->parseString('and: bob fred');
     $this->assertEqual($ander['default:'][0], 'bob',
                        "Default array does not contain first element");
     $this->assertTrue(array_key_exists('and:', $ander),
                       "and: array key not present");
   }


   function testWhereClause () {
     $kw = new folksoSearchKeyWordSetUserAdmin();
     $s = new folksoSearchQueryParser($kw);
     $col_eq = array('default:' => array('ud.lastname','ud.firstname'),
              'lname:' => 'ud.lastname',
              'fname:' => 'ud.firstname',
              'uid:' => 'u.userid');
     $i = new folksoDBinteract($this->dbc);

     $test_query1 = 'smith fname: bob';
     $parsed = $s->parseString($test_query1);
     $this->assertTrue(array_key_exists('fname:', $parsed),
                       "No fname: array key");
     $this->assertTrue(array_key_exists('default:', $parsed),
                       "No default: array key");

     $where = $s->whereClause($parsed,
                              $col_eq,
                              $i);
     $this->assertTrue(strlen($where) > 1,
                       "Where clause is empty");
     $this->assertPattern('/ud\.firstname/',
                          $where,
                          "Did not find ud.firstname in where clause");

     
     // and 
     $qu2 = 'and: fname: bob fname: fred';
     $parse2 = $s->parseString($qu2);
     $this->assertEqual($s->whereClause($parse2, $col_eq, $i),
                        "ud.firstname = 'bob' and ud.firstname = 'fred'",
                        "expecting 'ud.firstname = bob and ud.firstname = fred', got " . $s->whereClause($parse2,
                                                                           $col_eq,
                                                                           $i));

   }

   function testWhereClauseSomeMore () {
     $kw = new folksoSearchKeyWordSetUserAdmin();
     $s = new folksoSearchQueryParser($kw);
     $col_eq = array('default:' => array('ud.lastname','ud.firstname'),
              'lname:' => 'ud.lastname',
              'fname:' => 'ud.firstname',
              'uid:' => 'u.userid');
     $i = new folksoDBinteract($this->dbc);

     $qu1 = $s->parseString('fname: Hugo');
     $this->assertTrue(array_key_exists('fname:', $qu1),
                       "No fname: key");

     $where = $s->whereClause($qu1, $col_eq, $i);
     $this->assertTrue(strlen($where) > 1,
                       "where clause is empty");
     $this->assertEqual($where, "ud.firstname = 'hugo'",
                        "Incorrect where clause, expecting ud.firstname = 'Hugo', ".
                        " got " . $where);

     $qu2 = $s->parseString('flaubert fname: michel');
     


     $qu3 = $s->parseString('and: flaubert michel');
     $this->assertTrue(array_key_exists('and:', $qu3),
                       "and: keyword not showing up in where clause");
     $this->assertPattern('/\sand\s/i', $s->whereClause($qu3, $col_eq, $i),
                          "and not showing up in where clause");
   }

}//end class

$test = &new testOffolksoSearch();
$test->run(new HtmlReporter());