<?php

require_once 'folksoDBinteract.php';
require_once 'folksoDBconnect.php';
require_once 'folksoFabula.php';

$tagreq = strip_tags($_GET['tag']);

if ((strlen($tagreq) == 0) ||
    (strlen($tagreq) > 300)) {
  header('HTTP/1.1 404 Bad tag');
  header('Location: /404.php');
  exit();
}

if (! is_numeric($tagreq)) {
  header('HTTP/1.1 301 Moved permanently');
  header('Location: /tag/' . $tagreq);
  exit();
}
else {
  $loc = new folksoFabula();
  $i = new folksoDBinteract($loc->locDBC());
  $sql = sprintf("select tagnorm from tag where id = '%s'",
                 $i->dbescape($tagreq));
  $i->query($sql);
  if ($i->result_status == 'OK') {
    $row = $i->result->fetch_object();
    header('HTTP/1.1 301 Moved permanently');
    header('Location: /tag/' . $row->tagnorm);
    exit();
  }
  else {
  header('HTTP/1.1 404 Tag does not exist');
  header('Location: /404.php');
  exit();
  }
}
exit();
