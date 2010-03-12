<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="fab_html.xsl"/>
  <xsl:output method="xml"/>



  <xsl:template match="/">
    <xsl:element name="html">
      <xsl:attribute name="xmlns">http://www.w3.org/1999/xhtml</xsl:attribute>
      <xsl:call-template name="fabhead">
        <xsl:with-param name="pageTitle"
                        select="./tagpage/tag/tagtitle"/>
        <xsl:with-param name="tagnorm"
                        select="./tagpage/tag/tagnorm"/>
      </xsl:call-template>
      <xsl:element name="body">
        <xsl:element name="div">
          <xsl:attribute name="class">container</xsl:attribute>
          <xsl:call-template name="fab_docTop"/>

          <xsl:element name="div">
            <xsl:attribute name="id">container_1col</xsl:attribute>
            <xsl:apply-templates/>
          </xsl:element>
          <xsl:call-template name="fab_docBottom"/>
        </xsl:element>
      </xsl:element>
    </xsl:element>

  </xsl:template>


  <xsl:template match="tagpage">
    <xsl:element name="div">
      <xsl:attribute name="class">tagContent</xsl:attribute>

      <!-- title is inside the "tag" part, so we have to reach it from here  -->
      <xsl:element name="h1">
        <xsl:text>Ressources associées au tag :</xsl:text>
      </xsl:element>
      <xsl:element name="h2">
        <xsl:attribute name="class">tagtitle</xsl:attribute>
        <xsl:value-of select="./tag/tagtitle"/>
      </xsl:element>

      <xsl:element name="div">
        <xsl:attribute name="class">tagfeeds</xsl:attribute>

        <xsl:element name="a">
          <xsl:attribute name="href">
            <xsl:value-of select="concat('http://www.fabula.org/tag/',
                                          ./tag/tagnorm,
                                          '/feed/atom')"/>
          </xsl:attribute>
          <xsl:element name="img">
            <xsl:attribute name="src">http://www.fabula.org/tags/rssbutton.png</xsl:attribute>
          </xsl:element>
          <xsl:text>RSS</xsl:text>
        </xsl:element>
      </xsl:element>

      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <!-- related -->
  <xsl:template match="related">
    <xsl:element name="h2">
      <xsl:text>Tags voisins :</xsl:text>
    </xsl:element>
    <!-- related output is already html -->
    <xsl:copy-of select="./div"/>
  </xsl:template>

  <xsl:template match="tag">
    <xsl:apply-templates/>
  </xsl:template>
  
  <!-- already dealt with inside tagpage template -->
  <xsl:template match="tagtitle"/>
  <xsl:template match="created"/>
  <xsl:template match="tagid"/>
  <xsl:template match="tagnorm"/>

  <xsl:template match="resourcelist">
    <xsl:element name="ul">
      <xsl:attribute name="class">resourcelist</xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>
  
  <xsl:template match="resource">

    <xsl:element name="li">
      <xsl:attribute name="id">
        <xsl:text>res</xsl:text>
        <xsl:value-of select="./numid"/>
      </xsl:attribute>

      <xsl:element name="p">
        <xsl:attribute name="class">restitle_par</xsl:attribute>

        <xsl:element name="a">
          <xsl:attribute name="class">restitle</xsl:attribute>
          <xsl:attribute name="href">
            <xsl:value-of select="./url"/>
          </xsl:attribute>
          <xsl:value-of select="./title"/>
        </xsl:element>

      </xsl:element>
        
        <xsl:element name="p">
          <xsl:attribute name="class">currenttags</xsl:attribute>
          <xsl:call-template name="taglistFormat">
            <xsl:with-param name="remaining" select="./tags"/>
          </xsl:call-template>
        </xsl:element>


    </xsl:element>
  </xsl:template>

  <xsl:template name="taglistFormat">
    <xsl:param name="remaining"/>
    
    <xsl:choose>

      <!-- last element -->
      <xsl:when test="not(contains($remaining, ' - '))">
        <xsl:element name="a">
          <xsl:attribute name="class">innertag</xsl:attribute>
          <xsl:attribute name="href">
            <xsl:text>http://www.fabula.org/tag/</xsl:text>
            <xsl:value-of 
                select="substring-after($remaining, '::')"/>
          </xsl:attribute>
          <xsl:value-of 
              select="substring-after($remaining, '::')"/>
        </xsl:element>
      </xsl:when>

      <!-- all other elements, with recursive call -->
      <xsl:when test="contains($remaining, '- ')">
        <xsl:element name="a">
          <xsl:attribute name="class">innertag</xsl:attribute>
          <xsl:attribute name="href">
            <xsl:text>http://www.fabula.org/tag/</xsl:text>
            <xsl:value-of
                select="substring-after(substring-before($remaining, ' - '), '::')"/>
          </xsl:attribute>
          <xsl:value-of
              select="substring-before(substring-before($remaining, ' - '), '::')"/>
        </xsl:element>
        <xsl:text>,  </xsl:text>
        <xsl:call-template name="taglistFormat">
          <xsl:with-param name="remaining"
                          select="substring-after($remaining, ' - ')"/>
        </xsl:call-template>

      </xsl:when>
    </xsl:choose>
  </xsl:template>


</xsl:stylesheet>
