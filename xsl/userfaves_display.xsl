<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml"
              omit-xml-declaration="yes"/>


  <xsl:template match="taglist">
    <xsl:element name="ul">
      <xsl:attribute name="class">favorite-tags</xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tag">
    <xsl:element name="li">
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="./link"/>
        </xsl:attribute>
      <xsl:value-of select="./display"/>
      </xsl:element>
    </xsl:element>
  </xsl:template>
</xsl:stylesheet>