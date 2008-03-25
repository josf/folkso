<?php
require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
require_once('/usr/local/www/apache22/lib/jf/folksoIndexCache.php');

class testOffolksoIndexCache extends  UnitTestCase {

  var $ic;

  function testConstructor() {

    $this->ic = new folksoIndexCache('/tmp/cachetest');
    $other = new folksoIndexCache('/tmp/cachetest/');
    /* test adding of trailing '/' */
    $this->assertTrue(is_object($this->ic));
    $this->assertEqual( $this->ic->cachedir, '/tmp/cachetest/');
    $this->assertEqual( $other->cachedir, '/tmp/cachetest/');
  }

  function testNewCacheFilename () {
    $this->assertTrue(preg_match( 
                                 '/^folksoindex\-\d+\.cache$/', 
                                 $this->ic->new_cache_filename()));
  }

  function testWriting_to_cachedir () {
    $hand = fopen($this->ic->cachedir . "testfile", 'w');
    $this->assertTrue(fwrite($hand, "Hello"));
    fclose($hand);
  }

  function testWritingToCache () {
    $result1 = $this->ic->data_to_cache('');
    $this->assertFalse( $result1);

    $result2 = $this->ic->data_to_cache('sample data');
    $this->assertTrue(file_exists('/tmp/cachetest/'.$result2));
    $contents = file_get_contents('/tmp/cachetest/'.$result2);
    $this->assertEqual($contents, 'sample data');

  }

  function testRetreiving_data_from_ache () {
    $data = array("abb", "acc", "add");
    foreach ($data as $thang)  {
      $this->ic->data_to_cache($thang);
    }
    $this->assertFalse($this->ic->cache_check() );
    $new_data = $this->ic->retreive_cache();
    $ok = 1;
    foreach ($data as $old_thang) {
      print "checking $old_thang";
      if (!( in_array($old_thang, $new_data))) {
        print "$old_thang not here";
          $ok = 0;
        }
    }
    print sizeof($new_data);
    $this->assertTrue(is_array($new_data));
    $this->assertEqual( $ok, 1); 
    } 
  function testIs_cache_file () {
    $fi  = 'folksoindex-123.cache';
    $ha = fopen($this->ic->cachedir . $fi, w);
    fwrite($ha, 'stuff');
    fclose($ha);

    $this->assertTrue($this->ic->is_cache_file($fi));
    $this->assertFalse($this->ic->is_cache_file('banana'));
    $this->assertFalse($this->ic->is_cache_file('folksoindex-444.cache'));

  }

}

$test = &new testOffolksoIndexCache();
$test->run(new HtmlReporter());

?>