<?php

require '/var/www/dom/fabula/commun3/head_folkso2.php';
require_once('folksoAuth.php');
require_once('folksoUser.php');
require_once('folksoSession.php');

$fa = new folksoAuth();
$fa->logout();

if ($sessionValid === true){
  $fks->killSession();
}

require '/var/www/dom/fabula/commun3/head_libs.php';

require '/var/www/dom/fabula/commun3/head_dtd.php';
echo ("<html>\n<head>");
require '/var/www/dom/fabula/commun3/head_meta.php';
require '/var/www/dom/fabula/commun3/head_css.php';
require '/var/www/dom/fabula/commun3/head_css_folkso.php';
require '/var/www/dom/fabula/commun3/head_javascript.php';
require '/var/www/dom/fabula/commun3/head_javascript_folkso.php';

echo ("</head>\n<body>");

require '/var/www/dom/fabula/commun3/html_start.php';
?>
<div id="colonnes_nouvelles">
<div id="colonnes-un">
<p>Vous n'êtes plus connecté(e) à Fabula.</p>
<p>Pour vous reconnecter, choisissez votre service parmi les suivants.</p>

<table width="500" border="0" cellpadding="2" cellspacing="2">
  <tr> 
    <td align="left" valign="top"> 
		<fieldset>
        <legend>Choisissez un service pour vous identifier</legend>
			&nbsp;&nbsp;<a href="/tags/login.php?provider=Google">Google</a><br /> 
			&nbsp;&nbsp;<a href="/tags/login.php?provider=Yahoo">Yahoo</a><br /> 
			&nbsp;&nbsp;<a href="/tags/login.php?provider=Facebook">Facebook</a><br />
			&nbsp;&nbsp;<a href="/tags/login.php?provider=Twitter">Twitter</a><br />
			&nbsp;&nbsp;<a href="/tags/login.php?provider=LinkedIn">LinkedIn</a><br /> 
      </fieldset> 
	</td> 
</tr>
</table>
<?php
require '/var/www/dom/fabula/commun3/foot.php';




