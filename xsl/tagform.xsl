<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml"/>
  <xsl:template match="/">

    <xsl:element name="ul">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tag">
    <xsl:element name="li">
        <xsl:element name="form">
          <xsl:attribute name="action">/tag.php</xsl:attribute>
          <xsl:attribute name="method">post</xsl:attribute>
      <xsl:element name="p">
        <xsl:element name="span">
          <xsl:attribute name="class">tagname</xsl:attribute>
          <xsl:value-of select="display"/>
        </xsl:element>
      </xsl:element>
      <xsl:element name="p">
        <xsl:text> Supprimer ce tag : </xsl:text>

        <xsl:element name="button">
          <xsl:attribute name="type">submit</xsl:attribute>
          <xsl:attribute name="name">folksodelete</xsl:attribute>
          <xsl:attribute name="value">
            <xsl:value-of select="numid"/>
          </xsl:attribute>
          Suppression
        </xsl:element>
      </xsl:element>
      <xsl:element name="p">
        <xsl:text> 
          Fusionner ce tag avec : (inscrire le nom ou l'identifiant du tag
          vers lequel vous souhaitez faire migrer les données de
        </xsl:text>
        <xsl:element name="span">
          <xsl:attribute name="class">tagname</xsl:attribute>
          <xsl:value-of select="display"/>
        </xsl:element>
        <xsl:text>, qui sera définitivement supprimé)</xsl:text>

        <xsl:element name="input">
          <xsl:attribute name="name">folksotarget</xsl:attribute>
          <xsl:attribute name="type">text</xsl:attribute>
          <xsl:attribute name="maxlength">255</xsl:attribute>
          <xsl:attribute name="size">30</xsl:attribute>
        </xsl:element>
        <xsl:element name="button">
          <xsl:attribute name="type">submit</xsl:attribute>
          <xsl:attribute name="value">
            <xsl:value-of select="numid"/>
          </xsl:attribute>
          <xsl:attribute name="name">folksosource</xsl:attribute>
          Fusionner
        </xsl:element>
      </xsl:element>
    </xsl:element>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>