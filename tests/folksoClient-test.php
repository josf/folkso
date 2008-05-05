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
    $this->cl->execute();
    $bb = new folksoClient('localhost', '/tag.php', 'GET');
    $bb->set_getfields(array('folksoB' => 'bébé'));
    $this->assertEqual($bb->getfields, 'folksoB='.urlencode('bébé'));
    $this->assertEqual($this->cl->query_resultcode(), 200);
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

print "starting here>>";
    print http_digest_parse('Digest username="guest", realm="Restricted area", nonce="481e29a45ed2a", uri="/phpexamp.php", response="ef83bc4581ca2ff2b7eefba5f87d4aaf", opaque="cdce8a5c95a1427d74df7acbf41c9ce0", qop=auth, nc=00000001, cnonce="b0c4985b224014dc"');    
print "yo";




// function to parse the http auth header
function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();

    $raw = explode(',', $txt);
    $auth = array();
    foreach ($raw as $rr) {
      $key = '';
      $val = '';

      $rr = trim($rr);
      if(strpos($rr,'=') !== false) {
        print "<p>RR is $rr</p>";
        $lhs = substr($rr,0,strpos($rr,'='));
        $rhs = substr($rr,strpos($rr,'=')+1);
        $lhs = trim($lhs);
        $rhs = trim($rhs);


        print "<p>lhs is $lhs, rhs is $rhs</p>";


        print "<p>Leftmost char : " . substr($rhs, 0, 1) . "</p>";

        if ((substr($rhs, 0, 1) == substr($rhs, -1, 1)) &&
            ((substr($rhs, 0, 1) == '"') ||
             (substr($rhs, 0, 1) == "'"))) {
          $val = substr($rhs, 1, (strlen($rhs) - 2));
          print "<p>val is $val</p>";
        }
        else {
          $val = $rhs;
        }

        // avoiding the 'Digest firstparam="' part
        if (strpos($lhs, ' ') !== false) {
          $key = $lhs;
        }
        else {
          $key = substr($lhs, (strpos($lhs, ' ') + 1));
        }
        $auth[$key] = $val;
      }
    }
    return implode('|', $auth);
}
      

$test = &new testOffolksoClient();
$test->run(new HtmlReporter());


?>