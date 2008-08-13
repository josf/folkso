<?php

include('folksoClient.php');
include('folksoFabula.php');

$loc = new folksoFabula();

$our_current_url = curPageURL(); //ridiculous var name to avoid namespace problems


if (ua_check($_SERVER['HTTP_USER_AGENT'], $loc->visit_ignore_useragent)) {

}


if (ignore_check($our_current_url, $loc->visit_ignore_url)) {
  exit();
}

// NB: fabula specific $page_titre
if ($page_titre &&
    (ignore_check($page_titre, $loc->visit_ignore_title))) {
  exit();
}

$fc = new folksoClient('localhost', '/commun3/folksonomie/resource.php', 'POST');
$fc->set_postfields(array('folksovisituri' => $our_current_url,
                          'folksourititle' => $page_titre ? $page_titre : ''));
//print $fc->build_req();

$fc->execute();
//print $fc->query_resultcode();

function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

/**
 * Check user agents against list of strings. Standard list is used
 * first, then optional site specific list.
 */
function ua_check($ua, $valid_uas) {

  $hard_list =
    array('Mozilla', 'MSIE', 'Opera', 
          'w3m', 'Safari','Links','Lynx');

  foreach (array_merge($hard_list, $valid_uas) as $valid) {
    if (strpos($ua, $valid)) {
      return true;
    }
  }
  return false; //no matching ua found
}

function ignore_check($str, $ignore) {
  if (!is_array($ignore)) {
    return true;
  }

  foreach ($ignore as $pattern) {
    if (strpos($str, $pattern)) {
      return false;
    }
  }
  return true;
}

?>