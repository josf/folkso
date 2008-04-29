<?php
require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
include('/usr/local/www/apache22/lib/jf/fk/folksoClient.php');


class testOffolksoClient extends  UnitTestCase {
  public $cl;
  public $pos;


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
    $this->assertEqual($this->cl->content_length, 0);
  }


  function testPost () {
    $fields = array('folksoStuff' => 'bob',
                    'folksoThing' => 'blender');
    $this->pos = new folksoClient('localhost', '/tag.php', 'POST');
    $this->pos->set_postfields($fields);
    $this->assertTrue(is_string($this->pos->postfields));
    $this->assertEqual($this->pos->postfields, 'folksoStuff=bob&folksoThing=blender');
    $this->assertEqual($this->pos->build_req(), 'localhost/tag.php');
    $this->assertEqual($this->pos->content_length(), 35);
  }

  function testRequest () {
    $this->cl->execute();
    $this->assertEqual(curl_getinfo($this->cl->ch, CURLINFO_HTTP_CODE), 200);
    $this->pos->execute();
    $this->assertEqual(curl_getinfo($this->pos->ch, CURLINFO_HTTP_CODE), 200);
  }

}


$test = &new testOffolksoClient();
$test->run(new HtmlReporter());


?>