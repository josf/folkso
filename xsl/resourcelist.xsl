<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml"/>

  <xsl:template match="/">
    <xsl:apply-templates/>
  </xsl:template>

    
  <xsl:template match="tagtitle">
    <xsl:element name="h2">
      <xsl:attribute name="class">tagtitle</xsl:attribute>
      <xsl:value-of select="."/>
    </xsl:element>
  </xsl:template>


  <xsl:template match="resourcelist">

    <xsl:element name="ul">
        <xsl:attribute name="class">resourcelist</xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="resource">
    <xsl:element name="li">
      <xsl:attribute name="id">
        <xsl:value-of select="concat('res', numid)"/>
      </xsl:attribute>
      <xsl:element name="h4">
      <xsl:element name="a">
        <xsl:attribute name="class">restitle</xsl:attribute>
        <xsl:attribute name="href">
          <xsl:value-of select="url"/>
        </xsl:attribute>
        <xsl:value-of select="title"/>
      </xsl:element>
      </xsl:element>

      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="url"/>
        </xsl:attribute>
        <xsl:attribute name="class">resurl</xsl:attribute>
        <xsl:value-of select="url"/>
      </xsl:element>


        <xsl:element name="p">
          <xsl:element name="span">
          <xsl:attribute name="class">currenttags</xsl:attribute>
          <xsl:text> 
          </xsl:text>
          <xsl:value-of select="tags"/>
          <xsl:text> 
          </xsl:text>
          </xsl:element>
        </xsl:element>

        <xsl:element name="p">

          <xsl:element name="a">
            <xsl:attribute name="class">tocloud</xsl:attribute>
            <xsl:attribute name="href">
              <xsl:value-of select="concat('clouddemo.php?demouri=',
                                    numid)"/>
            </xsl:attribute>
            <xsl:text> Voir cloud </xsl:text>
          </xsl:element>
          <xsl:text>  
          </xsl:text>

          <xsl:element name="a">
            <xsl:attribute name="class">openiframe</xsl:attribute>
            <xsl:attribute name="href">#</xsl:attribute>
            <xsl:text>Voir page</xsl:text>
          </xsl:element>

          <xsl:element name="a">
            <xsl:attribute name="class">closeiframe</xsl:attribute>
            <xsl:attribute name="href">#</xsl:attribute>
            <xsl:text>Fermer</xsl:text>
          </xsl:element>

          <xsl:element name="a">
            <xsl:attribute name="class">editresource</xsl:attribute>
            <xsl:attribute name="href">#</xsl:attribute>
            <xsl:text>Editer</xsl:text>
          </xsl:element>

        </xsl:element>

        <xsl:element name="div">
          <xsl:attribute name="class">iframeholder</xsl:attribute>
          <xsl:element name="p">
          <!-- without this worthless paragraph, everything ends up
               inside of the iframeholder div for some reason -->
            <xsl:text>&#160;</xsl:text>
          </xsl:element>

        </xsl:element>

        <xsl:element name="div">
          <xsl:attribute name="class">details</xsl:attribute>

          <xsl:element name="div">
            <xsl:attribute name="class">tagger</xsl:attribute>
            <xsl:element name="span">
              <xsl:attribute name="class">infohead</xsl:attribute>
              <xsl:text>Ajouter un tag</xsl:text>
            </xsl:element>

            <xsl:element name="input">
              <xsl:attribute name="class">tagbox</xsl:attribute>
              <xsl:attribute name="type">text</xsl:attribute>
              <xsl:attribute name="maxlength">100</xsl:attribute>
              <xsl:attribute name="size">25</xsl:attribute>
            </xsl:element>
            
            <xsl:element name="span">
              <xsl:attribute name="class">infohead</xsl:attribute>
              <xsl:text> Metatag (facultatif) </xsl:text>
            </xsl:element>

            <xsl:element name="select">
              <xsl:attribute name="class">metatagbox</xsl:attribute>
              <xsl:attribute name="size">1</xsl:attribute>
              <!-- empty option to start with -->
              <xsl:element name="option">
              </xsl:element>
            </xsl:element>

            <xsl:element name="a">
              <xsl:attribute name="class">tagbutton</xsl:attribute>
              <xsl:attribute name="href">#</xsl:attribute>
              <xsl:text>Valider</xsl:text>
            </xsl:element>

          </xsl:element> <!-- end of div -->

          <xsl:element name="p">
            <xsl:text>Détails des tags déjà associés à cette ressource</xsl:text>
            <xsl:element name="a">
              <xsl:attribute  name="class">seetags</xsl:attribute>
              <xsl:attribute name="href">#</xsl:attribute>
              <xsl:text> Voir </xsl:text>
            </xsl:element>
            
            <xsl:element name="a">
              <xsl:attribute name="class">hidetags</xsl:attribute>
              <xsl:attribute name="href">#</xsl:attribute>
              <xsl:text> Cacher </xsl:text>
            </xsl:element>
          </xsl:element>

          <xsl:element name="div">
            <xsl:attribute name="class">emptytags</xsl:attribute>
          </xsl:element>
        </xsl:element>

    </xsl:element>
  </xsl:template>



</xsl:stylesheet>
