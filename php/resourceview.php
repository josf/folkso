<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Resources par tag</title>


<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>

<?php 

require_once('folksoClient.php');
require_once('folksoFabula.php');
require_once('folksoAdmin.php');

$loc = new folksoFabula();
$fk = new folksoAdmin();


/** If basic authentication info is present, we send it back to the
    browser for use in ajax requests **/
print $fk->BasicAuthJSScript();

print "<script type='text/javascript'>\n\n";
print $loc->WebPathJS();
print "</script>\n";
?>
<script type="text/javascript" src="js/folkso.js"></script>
<script type="text/javascript" src="js/resview.js">
</script>

<link 
   rel="stylesheet" type="text/css" 
   href="http://www.fabula.org/commun3/template.css" 
   media="screen">
</link>

    <link 
        rel="stylesheet" type="text/css"
        href="jquery.autocomplete.css"
        media="screen">
    </link>

<link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/print.css" media="print"></link>
<link rel="shortcut icon" type="image/x-icon" href="http://www.fabula.org/favicon.ico"></link>
<link rel="stylesheet" type="text/css" href="editres.css" media="screen"></link>

<style type="text/css">
h2.tagtitle {font-size: 16pt; text-align: center}
.resourcelist a.resurl, 
.resourcelist a.resurl:visited, 
.resourcelist a.resurl:link {font-size: 10pt; font-weight: normal}

.tagname { font-weight: bold; font-size: 18pt}
#container { 
    background-color: white; 
    padding-top: 2em;
    padding-bottom: 3em;
    padding-left: 2em; padding-right: 2em;
}
a.restitle,  a:link.restitle,  a:visited.restitle { 
    font-size: 14pt; 
    font-color: orange;}

ul.resourcelist li { margin-bottom: 1em}
ul.resourcelist li a.tocloud, ul.resourcelist li a:visited.tocloud, ul.resourcelist li a:link.tocloud {
    font-size:10pt;}

ul.resourcelist a:link.editresource, 
ul.resourcelist a:visited.editresource {
    font-size: 10pt; 
    margin-left: 2em;
 }

a.closeiframe, a:link.closeiframe, a:visited.closeiframe {
    display: none;
}
a.closeedit, a:link.closeedit, a:visited.closeedit {
display:none;
  margin-left: 2em;
}

div.details {
display:none;
}

div.iframeholder {
    display: none;
}

#pagebottom {
    border-top: 2px solid grey;
}

</style>

</head>
<body>
  <div id="superscreen"> <!-- screen that appears with dialogue boxes -->
  <div id="superinfobox">
     <a id="closess" href="#">Fermer</a>
  </div>
  </div> <!-- end of superscreen -->

<div id="container">
<?php

   if ($_GET['tag']) {
     $tagthing = strip_tags(substr($_GET['tag'], 0, 255));

     $fc = new folksoClient('localhost', 
                            $loc->server_web_path . 'tag.php', 
                            'get');

     $fc->set_getfields(array('folksotag' => $tagthing, 'folksofancy' => '1'));
     $reslist = $fc->execute();

     if ($fc->query_resultcode() == 200) {
           $resources = new DOMDocument();
           $resources->loadXML($reslist);
           
           $xsl = new DOMDocument();
           $xsl->load($loc->xsl_dir . "resourcelist.xsl");

           $proc = new XsltProcessor();
           $xsl = $proc->importStylesheet($xsl);
           $form = $proc->transformToDoc($resources);
           print $form->saveXML();
     }
     else {
       print $fc->query_resultcode();
     }
   }
?>
<div id="pagebottom">
<form action="resourceview.php" method="get">
            <p>
              Entrer un uri ou un identifiant de resource déjà présente dans la base
            </p>
            <p>
              <input type="text" name="tagthing" maxlength="3" size="3"></input>
             </p>
             <p>
               <input type="submit" value="Submit"/>
             </p>
           </form>
           <p>
             Retour à : <a href="editresources.php">interface de taggage</a>,
             <a href="tagedit.php">édition des tags</a>
           </p>
</div>

</div>
</body>
</html>




