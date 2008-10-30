<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml"/>

<!-- 
     Everything necessary for building the tagview url except the id
     that will be plugged in later.
-->
<xsl:param name="tagviewbase"/>
  <xsl:text>'/tagview.php?tag=</xsl:text>
</xsl:param>

<xsl:template match="/">
  <xsl:element name="div">
    <xsl:attribute name="class">tagcloud</xsl:attribute>
    <xsl:element name="ul">
      <xsl:attribute name="class">cloudlist</xsl:attribute>
    <xsl:attribute name="id">
      <xsl:value-of select="concat('tc', /tagcloud/@resource)"/>
    </xsl:attribute>
    <xsl:apply-templates/>
    </xsl:element>
  </xsl:element>
</xsl:template>

<xsl:template match="tag">
  <xsl:element name="li">
    
    <xsl:element name="a">
      <xsl:attribute name="href">
        <xsl:value-of select="concat($tagviewbase, ./numid)"/>
      </xsl:attribute>
      <xsl:attribute name="class">
        <xsl:value-of select="concat('cloudclass', ./weight)"/>
      </xsl:attribute>
      <xsl:value-of select="display"/>
    </xsl:element>


  </xsl:element>
</xsl:template>

</xsl:stylesheet>
