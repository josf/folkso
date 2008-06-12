<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml"/>
  <xsl:template match="/">

    <xsl:element name="ul">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="resource">
    <xsl:element name="li">
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="url"/>
        </xsl:attribute>
        <xsl:value-of select="title"/>
      </xsl:element>

      <xsl:if test="tags">
        <xsl:element name="p">
          <xsl:attribute name="class">smalltaglist</xsl:attribute>
          <xsl:text>(</xsl:text>
          <xsl:value-of select="tags"/>
          <xsl:text>)</xsl:text>
        </xsl:element>

      </xsl:if>

    </xsl:element>
  </xsl:template>



</xsl:stylesheet>
