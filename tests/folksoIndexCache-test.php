<?php
require_once('/usr/local/www/apache22/lib/simpletest/unit_tester.php');
require_once('/usr/local/www/apache22/lib/simpletest/reporter.php');
require_once('/usr/local/www/apache22/lib/jf/folksoIndexCache.php');

class testOffolksoIndexCache extends  UnitTestCase {

  var $ic;

  function testConstructor() {
    
    $this->ic = new folksoIndexCache('/tmp', '', '', '');

    /* test adding of trailing '/' */
    $this->assertTrue( $ic->cachedir = '/tmp/');
  }

  function testNewCacheFilename () {
    echo $this->ic->new_cache_filename();
    $result = 0;
    if (preg_match( '/^folksoindex-\d+\.cache$/', $this->ic->new_cache_filename())){
      $result = 1;
    }
      $this->assertTrue($result = 1);
  }

  function testWritingToCache () {
    $result1 = $this->ic->data_to_cache('');
    $this->assertFalse( $result1);

    $result2 = $this->ic->data_to_cache('sample data');
    $this->assertTrue(file_exists('/tmp/'.$result2));
    $contents = file_get_contents('/tmp/'.$result2);
    $this->assertTrue($contents = 'sample data');

  }

}

$test = &new testOffolksoIndexCache();
$test->run(new HtmlReporter());

?>