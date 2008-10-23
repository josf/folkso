<?php
require_once('unit_tester.php');
require_once('reporter.php');

require_once('folksoDBinteract.php');

class testOffolksoDBinteract extends  UnitTestCase {





}


$test = &new testOffolksoDBinteract();
$test->run(new HtmlReporter());