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
                        select="concat('Fabula. Folksonomie ressource : ', 
                                ./resource/title)"/>
        <xsl:with-param name="atomfeedp"/>
      </xsl:call-template>
      <xsl:element name="body">
        <xsl:element name="div">
          <xsl:attribute name="class">container</xsl:attribute>
          <xsl:call-template name="fab_docTop"/>

          <xsl:element name="div">
            <xsl:attribute name="id">container_1col</xsl:attribute>
            
            <xsl:call-template name="resourceTitle">
              <xsl:with-param name="url"
                              select="/resource/url"/>
              <xsl:with-param name="norm"
                              select="/resource/normurl"/>
              <xsl:with-param name="resid"
                              select="/resource/resid"/>
              <xsl:with-param name="title"
                              select="/resource/title"/>
            </xsl:call-template>


            <xsl:apply-templates/>
          </xsl:element>

          <xsl:call-template name="fab_docBottom"/>
        </xsl:element>

      </xsl:element>
    </xsl:element>
  </xsl:template>

  <xsl:template match="resource">
    <xsl:apply-templates/>
  </xsl:template>


  <xsl:template name="resourceTitle">
    <xsl:param name="url"/>
    <xsl:param name="norm"/>
    <xsl:param name="resid"/>
    <xsl:param name="title"/>

    <xsl:element name="h1">
      <xsl:text>Folksonomie resource : </xsl:text>
    </xsl:element>

    <xsl:element name="h2">
      <xsl:attribute name="class">tagtitle</xsl:attribute>
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="$url"/>
        </xsl:attribute>
        <xsl:value-of select="$title"/>
      </xsl:element>
    </xsl:element>

    <xsl:element name="p">
      <xsl:attribute name="class">lien-visible</xsl:attribute>
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="$url"/>
        </xsl:attribute>
        <xsl:value-of select="$url"/>
      </xsl:element>
    </xsl:element>

    <xsl:element name="p">
      <xsl:attribute name="class">ressource-explication</xsl:attribute>
      <xsl:text>Une "ressource" est une page cataloguée dans la folksonomie de Fabula.</xsl:text>
    </xsl:element>




  </xsl:template>

  <xsl:template match="url"/>
  <xsl:template match="normurl"/>
  <xsl:template match="resid"/>
  <xsl:template match="title"/>

  <xsl:template match="tagcloud">
    <xsl:element name="div">
      <xsl:attribute name="class">tagcloud</xsl:attribute>
      <xsl:element name="ul">
        <xsl:attribute name="class">cloudlist</xsl:attribute>
        <xsl:apply-templates/>
      </xsl:element>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tag">
    <xsl:element name="li">

      <xsl:element name="a">
        <xsl:attribute name="class">
          <xsl:value-of select="concat('cloudclass', ./weight)"/>
        </xsl:attribute>
        <xsl:attribute name="href">
          <xsl:value-of select="concat('http://www.fabula.org/tag/', ./tagnorm)"/>
        </xsl:attribute>
        <xsl:value-of select="./display"/>
      </xsl:element>

    </xsl:element>
  </xsl:template>

</xsl:stylesheet>