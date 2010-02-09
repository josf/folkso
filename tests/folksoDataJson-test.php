<?php
require_once('unit_tester.php');
require_once('reporter.php');
require_once('folksoTags.php');
require_once('folksoDataJson.php');
require_once('dbinit.inc');

class testOffolksoDataJson extends  UnitTestCase {
 
  function setUp() {
    test_db_init();
    /** not using teardown because this function does a truncate
        before starting. **/
     $this->dbc = new folksoDBconnect('localhost', 'tester_dude', 
                                      'testy', 'testostonomie');
  }

   function testBasic () {
     $dj   = new folksoDataJson(array('this', 'that'));
     $this->assertIsA($dj, folksoDataJson,
                      'object creation failed');
     $this->assertIsA($dj, folksoDataDisplay,
                      'inheritance failing');
     
     $line = $dj->line('Hiii', 'Haaa');
     $this->assertEqual($line,
                        '{"this":"Hiii","that":"Haaa"}',
                        'Line not what it should be: ' . $line);
     $total = $dj->startform();
     $total .= $line . ',';
     $total .= $dj->line('Zook', 'Zonk');
     $total .= $dj->endform();
     $dec = json_decode($total);
     $this->assertTrue($dec,
                       'Decoded json should not be false');
     
     $this->assertEqual($dec[0]->this,
                        'Hiii',
                        'Not getting first value back from json: ' 
                        . $total . "\n"
                        . var_dump($dec));
     $nat = json_encode(array( array('hoop' => 'Hoo',
                                     'hork' => 'Zorp'),
                               array('hoop' => 'Honk',
                                     'hork' => 'Zoupe')
                               ));
     print $nat;
                                     

   }
}//end class

$test = &new testOffolksoDataJson();
$test->run(new HtmlReporter());