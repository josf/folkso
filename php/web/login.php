<?php

require '/var/www/dom/fabula/commun3/head_folkso2.php';
require_once('folksoDBconnect.php');
require_once('folksoDBinteract.php');
require_once('folksoAuth.php');
require_once('folksoUser.php');


$loc = new folksoFabula();
$dbc = $loc->locDBC();

$message = '';
$error = '';
$user_create = false;
$u = null; // will be the folksoUser object if all goes well
$authent_fail = false;
$non_retour = false;
$ajouter_service = false;


$dest_url = '/tags/mestags.php';
if ( isset($_GET['retour']) &&
     $_GET['retour']) {
  $dest_url = checkRetourUrl($_GET['retour']);
}

if (isset($_GET['nonretour']) && 
    ($_GET['nonretour'] == "true")) {
  $non_retour = true;
}

if ($sessionValid == true) {
  if (isset($_GET['ajouterservice']) &&
      ($_GET['ajouterservice'] == "true")) {
    $ajouter_service = true;
  }

  // check here if there is a redirect url. If not we send to home
  // page after a short delay 

  if (($non_retour === false) && ($ajouter_service === false)) {
    header('Location: ' . $dest_url);
    exit();
  }
  $message = "Vous êtes déjà connecté(e) sur Fabula. ";
}

require_once('/var/www/dom/fabula/commun3/hybridauth/Hybrid/Auth.php');

if ((isset( $_GET['provider']) && $_GET['provider'])
    && 
    (($sessionValid == false) || ($ajouter_service == true)))  {

  $provider = @ trim( strip_tags( $_GET['provider'] ));

  try {
    $fa = new folksoAuth($provider);
    $u = $fa->authenticate();
    $uProfile = $fa->profile;
    $fks->startSession($u);
  }
  catch (unknownUserException $ukE) { 
    // login is unknown, either a new user or a known user is adding a new service
    if ($ajouter_service == true){
      $u = $fks->userSession();

      try {
        $u->associateUserWithIdentifier($fa->secondaryIdentifierByService($provider),
                                        $provider);
        $service_added = true; 
        $message .= $fa->profile->identifier;
      }
      catch(unknownServiceException $uSE) {
        $error = "Erreur lors de la connexion.";
      }
    }
    else {
      // create a new user
      $user_create = true;
    }
  }
  catch (configurationException $confE) {
    /**
     * This exception seems to be thrown when the user refuses to
     * allow access to his account, so it should not really be a
     * configurationException. This works, though.
     */
    $error = "Echec de la connexion. Veuillez réessayer de vous connecter.";
    $errorObj = $confE;
    $authent_fail = true;
  }
  catch (failedAuthenticationException $failE) {
    $error = "Erreur d'authentification.";
    $authent_fail = true;
  }
} // _GET['provider'] not set


require '/var/www/dom/fabula/commun3/head_libs.php';

require '/var/www/dom/fabula/commun3/head_dtd.php';
echo ("<html>\n<head>");
require '/var/www/dom/fabula/commun3/head_meta.php';
require '/var/www/dom/fabula/commun3/head_css.php';
require '/var/www/dom/fabula/commun3/head_css_folkso.php';

?>
<style type="text/css">
  #compteDeja {
color: gray;
padding: 1em;
margin-bottom: 2em;
border: 1px gray dotted;

}

  ul#newUserForm {
    text-align: left;
  }

</style>


<?php


require '/var/www/dom/fabula/commun3/head_javascript.php';
require '/var/www/dom/fabula/commun3/head_javascript_folkso.php';
//echo '<script type="text/javascript" src="/tags/js/pageinit.js"></script>';
echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js"></script>';
echo '<script type="text/javascript" src="/tags/js/login.js"></script>';
echo ("</head>\n<body>");

require '/var/www/dom/fabula/commun3/html_start.php';
//require '/var/www/dom/fabula/commun3/n_top.php';

?>

<div id="colonnes_nouvelles">
<div id="colonnes-un">

<?php

  if ($error) {
    print "<div><strong>Erreur:</strong> $error</div>";
  }
if ($message) {
  print "<div>$message</div>";
}

// if not identified, present "selectionner service" menu
if ((! $provider) ||  // no provider (in $_GET) OR...
    ($fa
     && (count($fa->getConnectedProviders()) == 0)) || // ...provider but no Auth or connection
    $authent_fail) // ... authentication failed, start over
  {
?>

<table width="500" border="0" cellpadding="2" cellspacing="2">
  <tr> 
    <td align="left" valign="top"> 
		<fieldset>
        <legend>Choisissez un service pour vous identifier</legend>
			&nbsp;&nbsp;<a href="?provider=Google">Google</a><br /> 
			&nbsp;&nbsp;<a href="?provider=Yahoo">Yahoo</a><br /> 
			&nbsp;&nbsp;<a href="?provider=Facebook">Facebook</a><br />
			&nbsp;&nbsp;<a href="?provider=Twitter">Twitter</a><br />
			&nbsp;&nbsp;<a href="?provider=LinkedIn">LinkedIn</a><br /> 
      </fieldset> 
	</td> 
</tr>
</table>
</div>
</div>

<?php 
} // end of "not identified"
elseif ($service_added) {
?>
  <p>Opération réussie.</p>
<?php
}

elseif ($user_create) { // providers, but user unknown to Fabula : create account

?>

  <script type="text/javascript">
  fK = window.fK || {};
  fK.userIdentifier = "<?php echo $fa->profile->identifier; ?>";
  fK.userService    = "<?php echo $provider; ?>";
  </script>



      <p>Bonjour <?php echo $fa->profile->firstName; ?>.
     Vous pouvez maintenant créer votre compte.</p>
<p> Aucune rubrique n'est obligatoire. Si vous renseignez "Prénom" 
et "Nom de famille", une page personnelle publique vous sera créée, où 
vous pourriez afficher un mini-CV. Si 
vous ne le souhaitez pas, il suffit de les laisser vides.</p>
<p>Vous pourrez modifier ces informations à tout moment à partir 
de votre « Espace tags ».</p>

<p id="messageBox"></p>

<div id="compteDeja">
       <p><strong>Vous avez déjà un compte ?</strong></p>
       <p>Si vous avez déjà créé un compte chez nous avec un autre 
       service que <?php 
       echo $provider; 
     ?>, <a href="/auth/log.php">cliquez 
     ici</a> pour choisir le service souhaité. Ensuite, sur 
       votre page personnelle (Espace Tags), vous pourriez ajouter <?php
       echo $provider;
     ?> en vous cliquant sur "Gérer ses services d'identification".
</p>
</div>

<form action="" method="POST" name="newUserForm">
<ul id="newUserForm">
<li>
   <label for="firstNameInput">Prénom</label>
   <input id="firstNameInput" type="text" 
          maxlength="60" size="40" class="oblig"
          value="<?php echo $fa->profile->firstName; ?>"></input>
</li>
<li>
  <label for="lastNameInput">Nom de famille</label>
  <input id="lastNameInput" type="text"
         maxlength="60" size="40" class="oblig"
         value="<?php echo $fa->profile->lastName; ?>"></input>
</li>
<li>
  <label for="emailInput">Courrier électronique</label>
  <input id="emailInput" type="text"
         maxlength="80" size="50" class="oblig"
         value="<?php echo $fa->profile->email; ?>"></input>
</li>
<li>
  <label for="institutionInput">Institution</label>
  <input id="institutionInput" type="text" class="facul"
         maxlength="100" size="50"></input>
</li>
<li>
  <lable for="fonctionInput">Fonction</lable>
  <input id="fonctionInput" type="text" class="facul"
         maxlength="100" size="40"></input>
</li>
<li>
  <lable for="paysInput">Pays</lable>
  <input id="paysInput" type="text"
         maxlength="60" size="40" class="facul"
         value="<?php echo $fa->profile->country; ?>"></input>
</li>
</ul>
<button id="newUserFormButton" 
        name="newUserFormSubmit"
        type="submit">Créer</button>
</form>
<?php

}
elseif ($u instanceof folksoUser) {
?> 
  <p><strong>Vous êtes connecté(e) !</strong></p>

    <?php if ($non_retour === false) { ?>
  <p>Vous allez être redirigé(e) sur la page d'origine ou votre page personnelle en quelques secondes.</p> <?php // ' ?>


<script type="text/javascript">
    setTimeout(function() {
        window.location = "<?php echo $dest_url; ?>";
        },
      2000);
</script>
<?php
    } // end if non_retour
}
else {
?>
<p>Erreur logique. Ceci n'est pas de votre faute.</p> 

<p><?php echo $error; ?></p>
<?php
  if ($errorObj) {
    print "<p>". $errorObj->getMessage() . "</p>";
    print "<p>" .   $errorObj->getTraceAsString() . "</p>";
  }
}
require '/var/www/dom/fabula/commun3/foot.php';


/**
 * @param $rawUrl
 */
function checkRetourUrl ($rawUrl) {
  if ((preg_match('/http:\/\//', $rawUrl)) && // absolute url, so we check
      (! (preg_match('/http:\/\/(?:www\.)?fabula\.org/', $rawUrl)))) {
    return '';
  }
  return $rawUrl;
}
