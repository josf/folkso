<?php

require_once "folksoPage.php";
require_once "fdent/fabelements.inc";
require_once "/var/www/dom/fabula/www/tags/fdent/common.php";
require_once "fdent/fab_info.inc";
require_once "facebook.php";
require_once "folksoSession.php";
require_once "folksoFabula.php";
require_once "folksoFBuser.php";

$fp = new folksoPage();
$el = new fabelements();
$loc = new folksoFabula();

$dbc = $loc->locDBC();
$fks = new folksoSession($dbc);
$sessionValid = false;

if ($_COOKIE['folksosess']) {
  $fks->setSid($_COOKIE['folksosess']);
  if ($fks->checkSession($fks->sessionId)) {
    $sessionValid = true;
    $u = $fks->userSession();
  }
}

if (! $fks->sessionId
    || ($sessionValid === false)) {

  // FB
  $fbsec = fdent_fbSecret();
  $fb = new Facebook($fbsec['api_key'], $fbsec['secret']);

  $fb_uid = $fb->get_loggedin_user();
  $fbu = new folksoFBuser($dbc);

  if ($fb_uid) {
    try {
      $name = $fb->api_client->users_getInfo($fb_uid, 'name');
    }
    catch (FacebookRestClientException $e) {
      $fb->clear_cookie_state();
      unset($fb_uid);
    }
  }


  if ($fb_uid && $fbu->exists($fb_uid)) {
      $fbu->userFromLogin($fb_uid);
      $fks->startSession($fbu->userid);
	$u = $fbu;
      /* we are good to go */
  }
}
