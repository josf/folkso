<?php

  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2008-2010 Gnu Public Licence (GPL)
   * @subpackage webinterface
   */
require_once "/var/www/dom/fabula/commun3/head_folkso.php"; 


if ($fks->status()) {
  $loggedIn = true;
  $user = $fks->userSession();
  if ($user->checkUserRight('folkso', 'redac')) {
    $hasRights = true;
  }
}


  /**
   * Simple login page for the web interface.
   */

$fab = new FabElements();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head><title>Folksonomie, gestion de tags et de ressources : se loguer</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Language" content="fr-FR"/>
<meta name="url" content="http://www.fabula.org/actualites/article35785.php"/>
<link rel="shortcut icon" type="image/x-icon" href="http://www.fabula.org/favicon.ico"/>
<link rel="alternate" type="application/rss+xml" title="A la une de fabula" href="http://www.fabula.org/rss/fabula.xml"/>
<link rel="alternate" type="application/rss+xml" title="Les dernières annonces" href="http://www.fabula.org/rss/fabula1.xml"/>
<link rel="alternate" type="application/rss+xml" title="Les dernières parutions" href="http://www.fabula.org/rss/fabula2.xml"/>
<link rel="alternate" type="application/rss+xml" title="Points de vue et débats" href="http://www.fabula.org/rss/fabula4.xml"/>
<link rel="alternate" type="application/rss+xml" title="Web littéraire" href="http://www.fabula.org/rss/fabula5.xml"/>
<link rel="alternate" type="application/rss+xml" title="Derniers articles de la revue Acta Fabula" href="http://www.fabula.org/lodel/acta/backend.php?format=rss092documents"/>
<meta name="verify-v1" content="zDUx4LU7GN3oeJAXLECA+BLOsYqjbBiaZwASPrPTcTs="/>
<meta http-equiv="PICS-Label" content='(PICS-1.1 "http://www.icra.org/ratingsv02.html" l gen true for "http://www.fabula.org" r (cb 1 lz 1 nz 1 oz 1 vz 1) "http://www.rsac.org/ratingsv01.html" l gen true for "http://www.fabula.org" r (n 0 s 0 v 0 l 0))'>
<meta name="revisit-after" content="7 days"/>
<meta name="distribution" content="Global"/>
<meta name="viewport" content="user-scalable=no, width=device-width" />
<meta name="author" content="Marielle Macé"/>
<meta name="description" content="Philip Stewart, L'Invention du sentiment: roman et économie affective au XVIIIe siècle Oxford: Voltaire Foundation, coll. "SVEC", 2010, viii+250 p. ISBN 978-0-7294-0991-9 £60 / €75 / $105 Présentation de l'éditeur: Comment s'est créé le concept psychologique et esthétique du ‘sentiment' [...]"/>
<meta name="keywords" content="Stewart,Invention,sentiment,économie,affective,XVIIIe"/>
<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/"/>
<meta name="DC.Type" content="Text"/>
<meta name="DC.Format" content="text/html"/>
<meta name="DC.Source" content="http://www.voltaire.ox.ac.uk"/>
<meta name="DC.Rights" content="©Fabula.org, licence Creative Commons Paternité-Pas d'Utilisation Commerciale-Pas de Modification"/>
<meta name="DC.Publisher" content="Équipe de recherche Fabula, École Normale Supérieure, 45 rue d'Ulm, 75230 Paris Cedex 05"/>
<meta name="DC.Author" content="Marielle Macé"/>
<meta name="DC.Identifier" scheme="URI" content="http://www.fabula.org/actualites/article35785.php"/>
<meta name="DC.Title" content="Ph. Stewart, L'Invention du sentiment: roman et économie affective au XVIIIe s."/>
<meta name="DC.Description" lang="fr-FR" content="Philip Stewart, L'Invention du sentiment: roman et économie affective au XVIIIe siècle Oxford: Voltaire Foundation, coll. "SVEC", 2010, viii+250 p. ISBN 978-0-7294-0991-9 £60 / €75 / $105 Présentation de l'éditeur: Comment s'est créé le concept psychologique et esthétique du ‘sentiment' [...]"/>
<meta name="DC.Subject" lang="fr-FR" content="Stewart,Invention,sentiment,économie,affective,XVIIIe"/>
<meta name="DC.Language" scheme="RFC3066" content="fr-FR"/>
<meta name="DC.Identifier" scheme="ISBN" content="9780729409919"/>
<meta name="DC.Contributor" content="Voltaire Foundation"/>
<meta name="DC.contributor" content="Lyn Roberts"/>
<meta name="DC.Relation.isPartOf"  content="Actualité Fabula, section Parution livre"/>
<meta name="DC.Date" scheme="W3CDTF" content="2010-2-08"/>
<link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/template.css" media="screen"/>
<link rel="stylesheet" href="/tags/css/jquery.autocomplete.css" media="screen"/>

<link rel="apple-touch-icon" href="http://www.fabula.org/apple-touch-icon.png"/>
<link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/print.css" media="print"/><script LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
function openform(url) {
var titre = ""
var url = url + "?url=" +document.location + "&titre=" + titre
var NS = (document.layers) ? true : false;
var IE = (document.all) ? true : false;
if(NS) {
window.open(url,"","scrollbars=no,menubar=no,personalbar=no,width=500,height=310,screenX=220,screenY=0");
} else  {
window.open(url,"","scrollbars=no,menubar=no,personalbar=no,width=500,height=280,left=220,top=0");
}
}
//-->
</script>
<script type="text/javascript" src="http://www.fabula.org/commun3/niftycube.js"></script>
<script type="text/javascript">
window.onload=function(){
Nifty("div#bloc_news_int");
Nifty("div#bloc_gris");
Nifty("div#bloc_vente_acta");
Nifty("div#bloc_vente_news");
Nifty("div#bloc_vente_liste");
Nifty("div#bloc_vente_liste_news");
Nifty("div#bloc_news_vert")
Nifty("div#bloc_orange");
Nifty("div#bloc_folkso");
}
</script>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-83179-1");
pageTracker._initData();
pageTracker._trackPageview();
</script>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.js"></script>
  <?php print $fp->facebookJSinit(); ?>
<script type="text/javascript" src="/tags/js/jquery.folksodeps.js"></script>
<script type="text/javascript" src="/tags/js/folksonomie.min.js"></script> 
<!-- <script type="text/javascript" src="/tags/js/folksonomie.js"></script> -->
<script type="text/javascript" src="/tags/js/pageinit.js"></script>
<script type="text/javascript">
  fK.cf.facebookReload = false;

</script>
<?php
  print $fp->jsHolder($fp->fKjsLoginState('fK.loginStatus') 
                      . $fp->fbJsVars());
?>
</head>
<body><div id="titre_fab_imprimer"/>Fabula, la recherche en littérature ()</div>
<div id="container"/>
<div id="header"/>
  <div id="titre"/><img src="http://www.fabula.org/commun3/titre.gif" alt="Fabula, la recherche en littérature"/> </div>
  <div id="chat"/><img src="http://www.fabula.org/commun3/chat.gif" alt="Fabula, la recherche en littérature"/></div>
  <div id="search"/>
<form action="http://www.fabula.org/rechercher.php" id="cse-search-box">
  <div>
    <input type="hidden" name="cx" value="002521762660777107752:bbqtm2ziagc" />
    <input type="hidden" name="cof" value="FORID:10" />
    <input type="hidden" name="ie" value="UTF-8" />
    <input type="text" size="6" maxlength="80" name="q" value="mot-clé" onfocus="if(this.value=='mot-clé')this.value='';"/>
    <input type="submit" name="sa" value="Rechercher" />
  </div>
</form>
  </div>
  <div id=menu>
    <ul>
      <li><a href="http://www.fabula.org/suggestions.php"/>soumettre une annonce</a></li>
      <li><a href="http://www.fabula.org/contacter_fabula.php"/>contacts</a></li>
      <li><a href="http://www.fabula.org/lettre.php"/>s'abonner</a></li>
      <li><a href="http://www.fabula.org/partenaires.php"/>partenaires</a></li>
      <li><a href="http://www.fabula.org/equipe.php"/>&eacute;quipe</a></li>
      <li><a href="http://www.fabula.org/faq.php"/>projet</a></li>
    </ul>
  </div>
  <div id="tabs-menu"/>
     <ul id="menu-haut"/>
      <li><a href="http://www.fabula.org" title="Retour à la page d'accueil"/>Accueil</a></li>
      <li><a href="http://www.fabula.org/actuinternet.php" title="L'actualité de l'internet des enseignants et chercheurs en littérature"/>Web&nbsp;littéraire</a></li>
      <li><a href="http://www.fabula.org/actudebats.php" title="Points de vue et débats"/>Débats</a></li>
      <li><a href="http://www.fabula.org/actualite.php"  title="Actualité des parutions ..."/>Parutions</a></li>
      <li><a href="http://www.fabula.org/actuappels.php"  title="Appels à contribution, offres de bourses et de postes ..."/>Appels et postes</a></li>
      <li><a href="http://www.fabula.org/calendrier.php" title="L'agenda des colloques et séminaires"/>Agenda</a></li>
      <li><a href="http://www.fabula.org/annuaire" title="Annuaire des chercheurs"/>Annuaire</a></li>
      <li class="crea"><a href="http://www.fabula.org/revue/" title="Acta : revue des parutions en théorie et critique littéraires"/><i>Acta</i></a></li>
      <li class="crea"><a href="http://www.fabula.org/lht" title="Revue LHT : Littérature Histoire Théorie"/><i>LHT</i></a></li>
        <li class="crea"><a href="http://www.fabula.org/atelier.php" title="L'atelier de théorie littéraire de Fabula"/>Atelier</a></li>
       
       <li class="librairie"><a href="http://www.fabula.org/cours.php" title="Cours en ligne"/>Cours</a></li>
        <li class="librairie"><a href="http://www.fabula.org/colloques" title="Colloques publiés sur le site Fabula"/>Colloques</a></li>
     </ul>
  </div>
</div><div id="colonnes_nouvelles"/>
<div id="colonnes-un"/>

<?php

if (! $loggedIn ) {
?>

<h1>Utilisateur non logué</h1>
<p>Veuillez vous loguer sur <a href="adminlogin.php">
la page de login de la gestion de tags</a>.
</p>

<?php 

}

elseif ( $hasRights ) {
?>
  <h1>Vous êtes loggué(e).</h1>

<p>Vous pouvez vous rendre sur les pages de gestion de tags.</p>
<ul>
    <li>
    <a href="http://www.fabula.org/tags/admin/editresources.php">
    Interface de taggage 
    </a> : tagger les pages du site.
    </li>
    <li>
    <a href="http://www.fabula.org/tags/admin/tagedit.php">
    Edition des tags
    </a> : modifier, fusionner, supprimer les tags du site.
    </li>
    <li>
    <a href="http://fabula.org/tags/uadmin.php">
    Gestion des utilisateurs
    </a>
    </li>
</ul>
    
<?php
}

else { // logged in but insufficient rights

?>
<h1>Droits insuffisants</h1>
    <p>Nous sommes désolés, mais vous ne disposez pas des droits nécessaires
    pour accéder à l'interface de gestion des tags. Veuillez nous en excuser si 
    vous pensez qu'il s'agit d'une erreur.</p>
<?php
}

print $fab->footHtml();






