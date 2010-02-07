<?php

require_once "folksoPage.php";
require_once "fdent/fabelements.inc";

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

<script type="text/javascript">

  function setupLogin () {
  return function (ev) {
    ev.preventDefault();
    $(this).parent().append( fK.oid.providerList() );
  }
}

  $(document).ready(function()
                    {
                      fK.oid.logopath = "/logos/";
                      // setup according to login state
                      fK.cf.container = $("#folksocontrol");
                      if (fK.loginState) {
                        $(".fKLoginButton", fK.cf.container).hide();
                      }
                      else {
                        $(".fKTagbutton", fK.cf.container).hide();
                        $("input.fKTaginput", fK.cf.container).hide();
                        $(".fKLoginButton", fK.cf.container).click(setupLogin());
                      }

                    });



</script>

</head>
<body>
<h1>Test des fonctions</h1>
<?php

print $fp->tagbox();



?>

</body>
</html>