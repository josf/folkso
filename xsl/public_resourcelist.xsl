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

      <xsl:element name="p">
        <xsl:attribute name="class">restitle_par</xsl:attribute>
        <xsl:element name="a">
          <xsl:attribute name="class">restitle</xsl:attribute>
          <xsl:attribute name="href">
            <xsl:value-of select="url"/>
          </xsl:attribute>
          <xsl:value-of select="title"/>
        </xsl:element>
      </xsl:element>

      <xsl:element name="p">
        <xsl:attribute name="class">resurl</xsl:attribute>
        <xsl:element name="a">
          <xsl:attribute name="href">
            <xsl:value-of select="url"/>
          </xsl:attribute>
          <xsl:attribute name="class">resurl</xsl:attribute>
          <xsl:value-of select="url"/>
        </xsl:element>
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

        </xsl:element>
  </xsl:template>

</xsl:stylesheet>
