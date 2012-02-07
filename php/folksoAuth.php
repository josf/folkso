<?php
/**
 * Wrapper around hybridauth to see if a user is who they say they
 * are.
 *
 * Specifically, this library is useful when a user does not yet
 * have a session.
 *
 * @package Folkso
 * @author Joseph Fahey
 * @copyright 2009-2010 Gnu Public Licence (GPL)
 * @subpackage Tagserv
 */

require_once('folksoFabula.php');
require_once('folksoUser.php');

/* sorry, these are hardcoded right now. */


require_once('/var/www/dom/fabula/commun3/hybridauth/Hybrid/Auth.php');

class folksoAuth {

  private $config;

  /**
   * hybridauth Auth object
   */
  private $Auth;

  /**
   * The name of the current provider (ie. Google, Yahoo, etc.)
   */
  private $provider;

  /**
   * Hybridauth user profile
   */
  public $profile;

  /**
   * Hybridauth adapter object
   */
  private $adapter;



  /** 
   * folksoUser object
   */
  private $user;

  /**
   * @param $provider
   */
  public function __construct ($provider = null) {
    $loc = new folksoFabula();
    try {
      // authConfig = location of the hybridauth config.php file
      $this->Auth = new Hybrid_Auth($loc->authConfig);
    }
    catch (Exception $e) {
      throw new configurationException("Problem with authentication system installation");
    }

    // set up user object with DB 
    $dbc = $loc->locDBC();
    $this->user = new folksoUser($dbc);

    if ($provider) {
      $this->setProvider($provider);
    }

  }


  /**
   * @param String Provider name
   */
  public function validateProvider($provider) {
    if (array_key_exists($provider,
                         Hybrid_Auth::$config['providers'])) {
      return true;
    }
    return false;
  }


  /**
   *
   * @param $provider String Provider name
   */
  public function setProvider ($provider) {
    if ($this->validateProvider($provider)) {
      $this->provider = $provider;
    }
    else {
      throw new unknownServiceException('Incorrect provider name');
    }
  }



  /**
   * @param 
   *
   * Throws lots of different errors. Better catch them or do something.
   */
  public function authenticate () {
    if (! $this->provider) {
      throw new unknownServiceException("No provider provided");
    }

    try {
      $this->adapter = $this->Auth->authenticate($this->provider);
      $this->profile = $this->adapter->getUserProfile();
      if ($this->profile->identifier) {
        $this->user->userFromLogin($this->profile->identifier);
      }
      else {
        print "WTF";
      }
      return $user;
    }
    catch (unknownUserException $ukE) {
      throw 
        new unknownUserException("Authentification successful but user does not exist");
    }
    catch (Exception $e) {

      switch ($e->getCode()) {
      
        // unspecified error
      case 0 : throw $e; break;
        // hybridauth config error
      case 1 : throw new configurationException($e->getMessage()); break;
        // provider config error
      case 2 : throw new configurationException('Provider not properly configured '
                                                . $e->getMessage());
        break;
        // unknown or disabled provider
      case 3 : throw new unknownServiceException($e->getMessage); break;
        // missing provider application credentials
      case 4 : throw new configurationException('Missing provider credentials ' 
                                                . $e->getMessage(),
                                                $e); 
        break;
      case 5 : throw new failedAuthenticationException('Canceled by user or refused by provider '
                                                       . $e->getMessage(),
                                                       $e);
        break;
      case 6 : throw new failedAuthenticationException('User probably not connected, should retry '
                                                       . $e->getMessage(),
                                                       $e);
        break;
      case 7 : throw new failedAuthenticationException('User not connected to provider '
                                                       . $e->getMessage(),
                                                       $e);

      }
    }
  }
}