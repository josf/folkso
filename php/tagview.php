<?php

require_once('folksoPage.php');

$page = new folksoPage();

$tagreq = $_GET['tag'];

print $tagreq;

print strlen($tagreq);

if ((strlen($tagreq == 0)) ||
    (strlen($tagreq) > 300)) {
  die("Requête malformée, tag impossible");
}

$page->public_tag_resource_list($tagreq);


?>