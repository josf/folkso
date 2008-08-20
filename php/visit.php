<?php

include('folksoClient.php');
include('folksoFabula.php');

visit_resource($page_titre);

function visit_resource ($page_titre) {
  $titre = stripslashes(stripslashes(stripslashes($page_titre)));
  $loc = new folksoFabula();
  $our_current_url = curPageURL(); //ridiculous var name to avoid namespace problems

  if (ua_ignore($_SERVER['HTTP_USER_AGENT'], $loc->visit_ignore_useragent)) {
    return;
  }

  if (ignore_check($our_current_url, $loc->visit_ignore_url)) {
    return;
  }

  // NB: fabula specific $page_titre
  if ($page_titre &&
      (ignore_check($page_titre, $loc->visit_ignore_title))) {
    return;
  }

  $fc = new folksoClient('localhost', '/commun3/folksonomie/resource.php', 'POST');
  $fc->set_postfields(array('folksovisit' => 1,
                            'folksores' => $our_current_url,
                            'folksourititle' => $titre ? $titre : ''));
  //print $fc->build_req();

  $fc->execute();
  // print $fc->query_resultcode();

}



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
 *
 * Note: returns false if the ua is valid and should _not_ be ignored,
 * and true if the ua _should_ be ignored.
 *
 * @return Boolean
 */
function ua_ignore($ua, $valid_uas) {
  $ua_list =
    array('Mozilla', 'MSIE', 'Opera', 
          'w3m', 'Safari','Links','Lynx');

  if (is_array($valid_uas)) {
    $ua_list = array_merge($ua_list, $valid_uas);
  }

  foreach ($ua_list as $valid) {
    if ((strpos(strtolower($ua), strtolower($valid))) > -1)  {
      return false; // false = do not ignore
    }
  }
  return true; //no matching ua found
}

function ignore_check($str, $ignore) {
  if (!is_array($ignore)) {
    return false;
  }
  foreach ($ignore as $pattern) {
    if ((strpos(strtolower($str), strtolower($pattern))) > -1) {
      return true;
    }
  }
  return false;
}

?>
