<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" omit-xml-declaration="yes"/>

<xsl:param name="tagviewbase"/>

<xsl:variable name="popmax">
      <xsl:value-of select="/taglist/tag[1]/popularity"/>
</xsl:variable>

<xsl:variable name="popmin">
  <xsl:value-of select="/taglist/tag[last()]/popularity"/>
</xsl:variable>

<xsl:template match="/">
  <xsl:element name="div">
    <xsl:attribute name="class">tagcloud relatedtags</xsl:attribute>
    <xsl:element name="ul">
      <xsl:attribute name="class">cloudlist</xsl:attribute>
      <xsl:for-each select="/taglist/tag">
        <xsl:sort select="display"/>
        <xsl:call-template name="tagtpl"/>
      </xsl:for-each>
    </xsl:element>
  </xsl:element>
</xsl:template>


<xsl:template name="tagtpl">
  <xsl:element name="li">
    <xsl:attribute name="class">
      <xsl:choose>
        <xsl:when test="popularity - $popmin > ($popmax - $popmin) * .80">cloudclass5</xsl:when>
        <xsl:when test="popularity - $popmin > ($popmax - $popmin) * .6">cloudclass4</xsl:when>
        <xsl:when test="popularity - $popmin > ($popmax - $popmin) * .4">cloudclass3</xsl:when>
                <xsl:when test="popularity - $popmin > ($popmax - $popmin) * .2">cloudclass2</xsl:when>
        <xsl:otherwise>cloudclass1</xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
    <xsl:element name="a">
      <xsl:attribute name="href">
        <xsl:value-of select="concat($tagviewbase, tagnorm)"/>
      </xsl:attribute>
      <xsl:value-of select="display"/>
    </xsl:element>
  </xsl:element>
</xsl:template>

</xsl:stylesheet>