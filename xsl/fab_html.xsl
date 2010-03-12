<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml"/>


  <xsl:template name="fabhead">
    <xsl:param name="pageTitle"/>
    <xsl:element name="head">
      <xsl:element name="title">
        <xsl:text>Fabula - tag : </xsl:text>
        <xsl:value-of select="$pageTitle"/>
      </xsl:element>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
      <meta http-equiv="Content-Language" content="fr-FR"/>
      <meta name="url" content="http://www.fabula.org/equipe.php"/>
      <meta name="verify-v1" content="zDUx4LU7GN3oeJAXLECA+BLOsYqjbBiaZwASPrPTcTs="/>
      <meta http-equiv="PICS-Label" content='(PICS-1.1 "http://www.icra.org/ratingsv02.html" l gen true for "http://www.fabula.org" r (cb 1 lz 1 nz 1 oz 1 vz 1) "http://www.rsac.org/ratingsv01.html" l gen true for "http://www.fabula.org" r (n 0 s 0 v 0 l 0))'/>
        <meta name="revisit-after" content="7 days"/>
        <meta name="distribution" content="Global"/>
        <meta name="author" content="Équipe de recherche Fabula"/>
        <meta name="description" content="Fabula, actualités et ressources pour les études littéraires : revue, annonces de colloques et d'appels à contribution, parutions, comptes rendus critiques, forums et débats"/>
        <meta name="keywords" content="Fabula, colloques, revues, acta, fiction, bibliographies, livres, auteurs, actualités, littératures, critique, lettres, littéraires, chercheurs, literature, genres, auteur, réception, représentation, mimésis, mimesis, analysis, criticism, theory, textes, stylistique, narratologie, herméneutique"/>
        <link rel="search" type="application/opensearchdescription+xml" title="Fabula" href="http://www.fabula.org/opensearch_fabula.xml"/>

        <link rel="schema.DC" href="http://purl.org/dc/elements/1.1/"/>

        <meta name="DC.Type" content="Text"/>
        <meta name="DC.Format" content="text/html"/>
        <meta name="DC.Source" content="http://www.fabula.org"/>
        <meta name="DC.Rights" content="©Fabula.org, licence Creative Commons Paternité-Pas d'Utilisation Commerciale-Pas de Modification"/>
        <meta name="DC.Publisher" content="Équipe de recherche Fabula, École Normale Supérieure, 45 rue d'Ulm, 75230 Paris Cedex 05"/>
        <meta name="DC.Author" content="Équipe de recherche Fabula"/>
        <meta name="DC.Identifier" scheme="URI" content="http://www.fabula.org/equipe.php"/>
        <meta name="DC.Title" content="L'équipe"/>
        <meta name="DC.Description" lang="fr-FR" content="Fabula, actualités et ressources pour les études littéraires : revue, annonces de colloques et d'appels à contribution, parutions, comptes rendus critiques, forums et débats"/>
        <meta name="DC.Subject" lang="fr-FR" content="Fabula, colloques, revues, acta, fiction, bibliographies, livres, auteurs, actualités, littératures, critique, lettres, littéraires, chercheurs, literature, genres, auteur, réception, représentation, mimésis, mimesis, analysis, criticism, theory, textes, stylistique, narratologie, herméneutique"/>
        <meta name="DC.Language" scheme="RFC3066" content="fr-FR"/>
        <link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/template.css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="http://www.fabula.org/commun3/print.css" media="print"/>
        <link rel="shortcut icon" type="image/x-icon" href="http://www.fabula.org/favicon.ico"/>
        <link rel="alternate" type="application/rss+xml" title="A la une de fabula" href="http://www.fabula.org/rss/fabula.xml"/>

        <link rel="alternate" type="application/rss+xml" title="Les dernières annonces" href="http://www.fabula.org/rss/fabula1.xml"/>
        <link rel="alternate" type="application/rss+xml" title="Les dernières parutions" href="http://www.fabula.org/rss/fabula2.xml"/>

        <link rel="alternate" type="application/rss+xml" title="Points de vue et débats" href="http://www.fabula.org/rss/fabula4.xml"/>
        <link rel="alternate" type="application/rss+xml" title="Derniers articles de la revue Acta Fabula" href="http://www.fabula.org/lodel/acta/backend.php?format=rss092documents"/>
        <link rel="alternate" type="application/rss+xml" title="Derniers ouvrages en vente sur la librairie de Fabula" href="http://www.dessinoriginal.com/modules/feeder/rss.php?id_category=198&amp;ac=FABULA"/>
        <script LANGUAGE="JavaScript" TYPE="text/javascript">
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
          Nifty("div#bloc_news_vert");
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

        <style type="text/css">
          a.restitle, a.restitle:visited, a.restitle:link {font-size: 12pt;}
          h2.tagtitle { text-align: center; font-size: 20pt;}
          ul.resourcelist li {padding-bottom: 0.5em }
          p.resurl {display: none}
          a.innertag, a.innertag:visited, a.innertag:link {text-decoration: none; border: none; }
        </style>


      </xsl:element>
    </xsl:template>

    <xsl:template name="fab_docTop">
      <div id="titre_fab_imprimer">Fabula, la recherche en littérature (accueil)</div>
      <div id="container"/>

      <div id="header">
        <div id="titre">
          <img src="http://www.fabula.org/commun3/titre.gif" alt="Fabula, la recherche en littérature"/> 
        </div>
        <div id="chat">
          <img src="http://www.fabula.org/commun3/chat.gif" alt="Fabula, la recherche en littérature"/>
        </div>
        <div id="search">

          <!--   
               <form action="http://www.fabula.org/rechercher.php"/>
               <input type="text" maxlength="80" name="q" size="6" value="mot-clé" onfocus="if(this.value=='mot-clé')this.value='';"/>
               <input class="go" value="rechercher" name="" type="submit"/>
               </form>
          -->

          
          <form method="get" action="http://www.google.fr/search">
            <input type="text" maxlength="80" name="q" size="6" value="mot-clé" onfocus="if(this.value=='mot-clé')this.value='';"/>
            <input class="go" value="rechercher" name="" type="submit"/>
            <input type="hidden"  name="sitesearch" value="www.fabula.org"/>
          </form>
        </div>

        <div id="menu">

          <ul>
            <li><a href="http://www.fabula.org/suggestions.php">soumettre une annonce</a></li>
            <li><a href="http://www.fabula.org/contacter_fabula.php">contacts</a></li>
            <li><a href="http://www.fabula.org/lettre.php">s'abonner</a></li>

            <li><a href="http://www.fabula.org/partenaires.php">partenaires</a></li>
            <li><a href="http://www.fabula.org/equipe.php">&#201;quipe</a></li>

            <li><a href="http://www.fabula.org/faq.php">projet</a></li>
          </ul>
        </div>

        <div id="tabs-menu">

          <ul id="menu-haut">
            <li class="current">
              <a href="http://www.fabula.org" title="Retour à la page d'accueil">Accueil</a>
            </li>
            <li>
              <a href="http://www.fabula.org/actuinternet.php" title="L'actualité de l'internet des enseignants et chercheurs en littérature">Web&#160;littéraire</a>
            </li>

            <li>
              <a href="http://www.fabula.org/actudebats.php" title="Points de vue et débats">Débats</a>
            </li>

            <li>
              <a href="http://www.fabula.org/actualite.php"  title="Actualité des parutions ...">Parutions</a>
            </li>
            <li>
              <a href="http://www.fabula.org/actuappels.php"  title="Appels à contribution, offres de bourses et de postes ...">Appels et postes</a>
            </li>
            <li>
              <a href="http://www.fabula.org/calendrier.php" title="L'agenda des colloques et séminaires">Agenda</a>
            </li>
            <li>
              <a href="http://www.fabula.org/ressources.php" title="Annuaire des chercheurs, listes de diffusion et autres outils utiles">Ressources</a>
            </li>
            <li class="crea">
              <a href="http://www.fabula.org/colloques" title="Colloques publiés sur le site Fabula">Colloques</a>
            </li>
            <li class="crea">
              <a href="http://www.fabula.org/atelier.php" title="L'atelier de théorie littéraire de Fabula">Atelier</a>
            </li>

            <li class="crea">
              <a href="http://www.fabula.org/revue/" title="Acta : revue des parutions en théorie et critique littéraires"><i>Acta</i>
              </a>
            </li>
            <li class="crea">
              <a href="http://www.fabula.org/lht" title="Revue LHT : Littérature Histoire Théorie"><i>LHT</i></a>
            </li>
            <li  class="librairie">
              <a href="http://www.fabula.org/librairie.php" title="Librairie de Fabula">Librairie</a>
            </li>
          </ul>
        </div>
        </div>
    </xsl:template>

    <xsl:template name="fab_docBottom">

      <div id="bottom_1col">
      <div id="copyright">
        &#169; Tous les textes et documents disponibles sur ce site, sont, sauf mention contraire, protégés par une <a href="http://creativecommons.org/licenses/by-nc-nd/2.0/fr/">licence Creative Common</a><br/>(diffusion et reproduction libres avec l'obligation de citer l'auteur original et l'interdiction de toute modification et de toute utilisation commerciale sans autorisation préalable).
      </div>
      <br/>
      <div class="feed">
        <a href="http://www.fabula.org/rss/fabula1.xml">Fil d'information RSS</a>
      </div>
      </div>
    </xsl:template>


  </xsl:stylesheet>


