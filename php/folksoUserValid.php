<?php

  /**
   * Abstract class for user information to be passed to
   * folksoResponse by folksoServer.
   *
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008 Gnu Public Licence (GPL)
   */


abstract class folksoUserValid {
  abstract public function parse_auth_header ($header = '');
  
  abstract public function validateAuth ($header = '');
  public function checkUsername ($user = '') {
    if (empty($user)) {
      $user = $this->username;
    }

    if (($user == 'folksy') ||
        ($user == 'bob')) {
      return true;
    }
    else {
      return false;
    }
  }

  abstract public function Validate();

  abstract protected function getUserPasswd ($user);

  abstract public function getUsername ();
  abstract public function tag_create_access ();
  abstract public function tag_admin_access ();
  abstract public function userid ();

  }



?>