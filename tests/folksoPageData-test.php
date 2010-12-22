<?php
require_once('unit_tester.php');
require_once('reporter.php');
include('folksoTags.php');
include('folksoPageData.php');

class testOffolksoPageData extends  UnitTestCase {

  function testBasic () {

    $pg = new folksoPageData(20634);
    $this->assertIsA($pg, 'folksoPageData',
                     'Problem with object construction');

    $pg->prepareMetaData();
    $this->assertIsA($pg->mt, 'folksoPageDataMeta',
                     'Did not create a folksoPageDataMeta object');
    $this->assertIsA($pg->ptags, 'folksoPagetags',
                     'Did not create a folksoPagetags object');

    $pg->prepareCloud();
    $this->assertIsA($pg->cloud, 'folksoCloud',
                     'Could not build folksoCloud object');
    $this->assertTrue(strlen($this->cloud) > 100,
                      'Cloud has less than 100 chars of data');


   }
}//end class

$test = &new testOffolksoPageData();
$test->run(new HtmlReporter());