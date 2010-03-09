<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml"/>

  <xsl:template match="/">
    <xsl:element name="feed">
      <xsl:attribute name="xmlns">http://www.w3.org/2005/Atom</xsl:attribute>
      <xsl:element name="author">
        <xsl:element name="name">www.fabula.org</xsl:element>
      </xsl:element>

      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tagid"/>

  <xsl:template name="feedId">
    <xsl:param name="shortdate"/>
    <xsl:param name="tagid"/>
    <xsl:value-of 
        select="concat('tag:fabula.org,', $shortdate, ':/tag/', $tagid)"/>
  </xsl:template>


  <xsl:template name="entryId">
    <xsl:param name="shortdate"/>
    <xsl:param name="resid"/>
    <xsl:value-of
        select="concat('tag:fabula.org,', $shortdate, ':/resource/', $resid)"/>
  </xsl:template>

  <xsl:template match="tag">      
    <xsl:element name="id">
      <xsl:call-template name="feedId">
        <xsl:with-param name="shortdate" 
                        select="substring-before(./created, 'T')"/>
        <xsl:with-param name="tagid" select="./tagid"/>
      </xsl:call-template>
    </xsl:element>
    <xsl:apply-templates/>
  </xsl:template>


  <xsl:template match="tagnorm">
      <xsl:element name="link">
        <xsl:attribute name="rel">self</xsl:attribute>
        <xsl:attribute name="href">
          <xsl:text>http://www.fabula.org/tag/</xsl:text>
          <xsl:value-of select="."/>
          <xsl:text>/atom</xsl:text>
        </xsl:attribute>
      </xsl:element>
  </xsl:template>

  <xsl:template match="created">
    <xsl:element name="updated">
      <xsl:value-of select="."/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tagtitle">
    <xsl:element name="title">
      <xsl:value-of select="."/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tags">
      <xsl:element name="summary">
        <xsl:text>Ressource taggée avec:</xsl:text>
        <xsl:apply-templates/>
      </xsl:element>
  </xsl:template>

  <xsl:template match="ul">
    <xsl:element name="ul">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="li">
    <xsl:element name="li">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>


  <xsl:template match="a">
    <xsl:element name="a">
      <xsl:attribute name="href">
        <xsl:value-of select="@href"/>
      </xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="title">  
      <xsl:element name="title">
        <xsl:apply-templates/>
      </xsl:element>
  </xsl:template>

  <xsl:template match="tagdate">
    <xsl:element name="updated">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>


  <xsl:template match="numid"/>
  <xsl:template match="url"/>

  <xsl:template match="resource">
    <xsl:element name="entry">

      <xsl:element name="id">
        <xsl:call-template name="entryId">
          <xsl:with-param name="shortdate" select="substring-before(./tagdate, 'T')"/>
          <xsl:with-param name="resid" select="./numid"/>
        </xsl:call-template>
      </xsl:element>

      <xsl:element name="link">
        <xsl:attribute name="href">
          <xsl:value-of select="./url"/>
        </xsl:attribute>
      </xsl:element>

      <xsl:element name="link">
        <xsl:attribute name="rel">tagcloud</xsl:attribute>
        <xsl:attribute name="type">text/html</xsl:attribute>
        <xsl:attribute name="href">
          <xsl:text>http://www.fabula.org/tags/resource.php?folksoclouduri=1&amp;folksores=</xsl:text>
          <xsl:value-of select="./url"/>
        </xsl:attribute>
      </xsl:element>
      
      <xsl:apply-templates/>

    </xsl:element>
  </xsl:template>
</xsl:stylesheet>
