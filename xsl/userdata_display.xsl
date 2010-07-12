<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml"
              omit-xml-declaration="yes"/>

  <xsl:template match="user">
    <xsl:element name="div">
      <xsl:attribute name="class">user-data</xsl:attribute>

      <xsl:element name="h3">
        <xsl:value-of select="concat(./firstname, ' ', ./lastname)"/>
      </xsl:element>

      <xsl:element name="dl">
        <xsl:attribute name="class">user-data-list</xsl:attribute>

        <xsl:call-template name="dlItem">
          <xsl:with-param name="headingText">Fonction : </xsl:with-param>
          <xsl:with-param name="infoText"
                          select="./fonction"/>
        </xsl:call-template>


        <xsl:call-template name="dlItem">
          <xsl:with-param name="headingText">Institution : </xsl:with-param>
          <xsl:with-param name="infoText"
                          select="./institution"/>
        </xsl:call-template>

        <xsl:call-template name="dlItem">
          <xsl:with-param name="headingText">Pays : </xsl:with-param>
          <xsl:with-param name="infoText"
                          select="./pays"/>
        </xsl:call-template>

      </xsl:element>


      <xsl:element name="p">
        <xsl:attribute name="class">tagcount</xsl:attribute>
        <xsl:value-of select="concat(./firstname, ' ', ./lastname)"/>
        <xsl:text> </xsl:text>
        <xsl:text>
          a appliqué 
        </xsl:text>
        <xsl:text> </xsl:text>
        <xsl:value-of select="./tagcount"/>
        <xsl:text> tags.</xsl:text>
      </xsl:element>


    </xsl:element>
  </xsl:template>


  <xsl:template name="titleDt">
    <!-- puts headings into the dt.heading class -->
    <xsl:param name="headingText"/>
    <xsl:element name="dt">
      <xsl:value-of select="$headingText"/>
    </xsl:element>
  </xsl:template>

  <xsl:template name="dlItem">
    <xsl:param name="headingText"/>
    <xsl:param name="infoText"/>
    <xsl:if test="string-length($infoText) &gt; 0">
      <xsl:call-template name="titleDt">
        <xsl:with-param name="headingText"
                        select="$headingText"/>
      </xsl:call-template>
      <xsl:element name="dd">
        <xsl:value-of select="$infoText"/>
      </xsl:element>
    </xsl:if>

  </xsl:template>

</xsl:stylesheet>

