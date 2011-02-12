<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="fab_html.xsl"/>
  <xsl:output method="xml"/>
<!--              doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"/> -->

<!--               doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
-->

  <xsl:template match="/">
    <xsl:element name="div">
      <xsl:attribute name="id">tagContainer</xsl:attribute>
      <xsl:element name="h1">
        <xsl:attribute name="id">tagtitle</xsl:attribute>
        <xsl:text>Tag : </xsl:text>
        <xsl:value-of select="tagpage/tag/tagtitle"/>
      </xsl:element>

      <!-- Feed(s) -->
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
            <xsl:attribute name="alt">Bouton RSS</xsl:attribute>
          </xsl:element>
          <xsl:text>RSS</xsl:text>
        </xsl:element>
      </xsl:element>

      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>






  <xsl:template match="related">
    <xsl:element name="div">
      <xsl:attribute name="id">relatedTags</xsl:attribute>

      <xsl:element name="h2">
        <xsl:text>Tags voisins :</xsl:text>
      </xsl:element>

      <xsl:copy-of select="./div"/>
    </xsl:element>
  </xsl:template>


  <xsl:template match="tag">
    <xsl:element name="div">
      <xsl:attribute name="id">tagResourceList</xsl:attribute>
      <xsl:attribute name="class">resourceList</xsl:attribute>

      <xsl:element name="h2">
        <xsl:text>Ressources associées : </xsl:text>
      </xsl:element>

  
      <xsl:apply-templates select="resourcelist"/>
    </xsl:element>
  </xsl:template>

  
  <!-- already dealt with inside tagpage template -->
  <xsl:template match="tagtitle"/>
  <xsl:template match="created"/>
  <xsl:template match="tagid"/>
  <xsl:template match="tagnorm"/>

  <xsl:template match="resourcelist">
      <xsl:call-template name="forwardBack">
        <xsl:with-param name="offset"
                        select="/tagpage/tag/resourcelist/@offset"/>
        <xsl:with-param name="reslength"
                        select="count(/tagpage/tag/resourcelist/resource)"/>
        <xsl:with-param name="tagnorm"
                        select="/tagpage/tag/tagnorm"/>
      </xsl:call-template>

    <xsl:element name="ul">
      <xsl:attribute name="class">resourcelist</xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>

      <xsl:call-template name="forwardBack">
        <xsl:with-param name="offset"
                        select="/tagpage/tag/resourcelist/@offset"/>
        <xsl:with-param name="reslength"
                        select="count(/tagpage/tag/resourcelist/resource)"/>
        <xsl:with-param name="tagnorm"
                        select="/tagpage/tag/tagnorm"/>
      </xsl:call-template>

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
              select="substring-before($remaining, '::')"/>
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


  <xsl:template name="forwardBack">
    <xsl:param name="offset"/>

    <!-- total number of resource items in current document -->
    <xsl:param name="reslength"/>

     <xsl:param name="tagnorm"/>
    <xsl:element name="div">
      <xsl:attribute name="class">prev-next</xsl:attribute>

      <!-- previous link -->
      <xsl:if test="$offset != 0">      
        <xsl:element name="a">
          <xsl:attribute name="class">previous-res</xsl:attribute>
          <xsl:attribute name="href">
            <xsl:choose>
              <xsl:when test="$offset &lt; 51">
                <xsl:value-of 
                    select="concat('http://www.fabula.org/tag/', $tagnorm)"/>
              </xsl:when>
              
              <xsl:when test="$offset &gt; 50">
                <xsl:value-of 
                    select="concat('http://www.fabula.org/tag/', $tagnorm, '/offset/', $offset - 50)"/>
              </xsl:when>
            </xsl:choose>
          </xsl:attribute>
          <xsl:text>précédentes &lt; &lt; &lt; </xsl:text>
        </xsl:element>
      </xsl:if>

      <xsl:if test="$reslength &gt; 48">
        <!-- if reslength is less than 50, we are already at the end -->
        <xsl:element name="a">
          <xsl:attribute name="class">next-res</xsl:attribute>
          <xsl:attribute name="href">
            <xsl:value-of
                select="concat('http://www.fabula.org/tag/', $tagnorm, '/offset/', $offset + 50)"/>
          </xsl:attribute>
          <xsl:text> &gt; &gt; &gt; suivantes </xsl:text>
        </xsl:element>
      </xsl:if>


    </xsl:element>
  </xsl:template>

</xsl:stylesheet>
