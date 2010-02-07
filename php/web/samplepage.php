<?php

require_once "folksoPage.php";

$fp = new folksoPage();

?>
<html>
<head>

<title>Une page au hasard</title>

<script type="text/javascript"
    src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.js"></script> 
<script type="text/javascript" src="js/jquery.autocomplete.js"> </script>
<script type="text/javascript" src="jquery.jqote.js"></script>
<script type="text/javascript" src="folksonomie.js"></script>
<script type="text/javascript" src="faboid.js"></script>

<?php // outputs a <script> elememnt
  print $fp->jsHolder($fp->fKjsLoginState('fK.loginState'));
?>

</head>
<body>
<h1>Test des fonctions</h1>
<?php

print $fp->tagbox();

?>

</body>
</html>