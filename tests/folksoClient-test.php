<?php
require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoClient.php');


class testOffolksoClient extends  UnitTestCase {
  public $cl;


  function testConstruction () {
    $this->cl = new folksoClient('localhost', '/tag.php', 'GET');
    $this->assertTrue($this->cl instanceof folksoClient);
    $this->assertEqual($this->cl->method, 'GET');

  }

  function testGetfields () {
    $fields = array('folksoStuff' => 'bob',
                    'folksoThing' => 'blender');
    $this->cl->set_getfields($fields);

    $this->assertTrue(is_string($this->cl->getfields));
    $this->assertEqual($this->cl->getfields, 'folksoStuff=bob&folksoThing=blender');
    $this->assertEqual($this->cl->build_req(), 'localhost/tag.php?folksoStuff=bob&folksoThing=blender');
    $this->cl->datastyle = 'b';
    $this->assertEqual($this->cl->build_req(), 'localhost/tag.php?folksoStuff=bob&folksoThing=blender&folksodatastyle=b');
  }
}


$test = &new testOffolksoClient();
$test->run(new HtmlReporter());


?>