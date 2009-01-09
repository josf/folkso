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
      <xsl:attribute name="class">resitem</xsl:attribute>
      <xsl:element name="h4">
      <xsl:element name="a">
        <xsl:attribute name="class">restitle</xsl:attribute>
        <xsl:attribute name="href">
          <xsl:value-of select="url"/>
        </xsl:attribute>
        <xsl:value-of select="title"/>
      </xsl:element>
      </xsl:element>

      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:value-of select="url"/>
        </xsl:attribute>
        <xsl:attribute name="class">resurl</xsl:attribute>
        <xsl:value-of select="url"/>
      </xsl:element>


        <xsl:element name="p">
          <xsl:attribute name="class">currenttags</xsl:attribute>
          <xsl:call-template name="divide-tags">
            <xsl:with-param name="original">
            <xsl:value-of select="tags"/>
            </xsl:with-param>
          </xsl:call-template>
        </xsl:element>

        <xsl:element name="p">
          <xsl:element name="a">
            <xsl:attribute name="class">openiframe</xsl:attribute>
            <xsl:attribute name="href">#</xsl:attribute>
            <xsl:text> Voir page </xsl:text>
          </xsl:element>

          <xsl:element name="a">
            <xsl:attribute name="class">closeiframe</xsl:attribute>
            <xsl:attribute name="href">#</xsl:attribute>
            <xsl:text> Fermer </xsl:text>
          </xsl:element>

          <xsl:element name="a">
            <xsl:attribute name="class">editresource</xsl:attribute>
            <xsl:attribute name="href">#</xsl:attribute>
            <xsl:text> Editer </xsl:text>
          </xsl:element>

          <xsl:element name="a">
            <xsl:attribute name="class">closeedit</xsl:attribute>
            <xsl:attribute name="href">#</xsl:attribute>
            <xsl:text> Cacher </xsl:text>
          </xsl:element>

        </xsl:element>

        <xsl:element name="div">
          <xsl:attribute name="class">iframeholder</xsl:attribute>
          <xsl:element name="p">
          <!-- without this worthless paragraph, everything ends up
               inside of the iframeholder div for some reason -->
            <xsl:text>&#160;</xsl:text>
          </xsl:element>

        </xsl:element>

        <xsl:element name="div">
          <xsl:attribute name="class">details</xsl:attribute>

          <xsl:element name="div">
            <xsl:attribute name="class">tagger</xsl:attribute>
            <xsl:element name="span">
              <xsl:attribute name="class">infohead</xsl:attribute>
              <xsl:text>Ajouter un tag</xsl:text>
            </xsl:element>

            <xsl:element name="input">
              <xsl:attribute name="class">tagbox</xsl:attribute>
              <xsl:attribute name="type">text</xsl:attribute>
              <xsl:attribute name="maxlength">100</xsl:attribute>
              <xsl:attribute name="size">25</xsl:attribute>
            </xsl:element>
            
            <xsl:element name="span">
              <xsl:attribute name="class">infohead</xsl:attribute>
              <xsl:text> Metatag (facultatif) </xsl:text>
            </xsl:element>

            <xsl:element name="select">
              <xsl:attribute name="class">metatagbox</xsl:attribute>
              <xsl:attribute name="size">1</xsl:attribute>
              <!-- empty option to start with -->
              <xsl:element name="option">
              </xsl:element>
            </xsl:element>

            <xsl:element name="a">
              <xsl:attribute name="class">tagbutton</xsl:attribute>
              <xsl:attribute name="href">#</xsl:attribute>
              <xsl:text>Valider</xsl:text>
            </xsl:element>

          </xsl:element> <!-- end of div -->

          <xsl:element name="p">
            <xsl:text>Détails des tags déjà associés à cette ressource</xsl:text>
            <xsl:element name="a">
              <xsl:attribute  name="class">seetags</xsl:attribute>
              <xsl:attribute name="href">#</xsl:attribute>
              <xsl:text> Voir </xsl:text>
            </xsl:element>
            
            <xsl:element name="a">
              <xsl:attribute name="class">hidetags</xsl:attribute>
              <xsl:attribute name="href">#</xsl:attribute>
              <xsl:text> Cacher </xsl:text>
            </xsl:element>
          </xsl:element>

          <xsl:element name="div">
            <xsl:attribute name="class">emptytags</xsl:attribute>
          </xsl:element>
        </xsl:element>

    </xsl:element>
  </xsl:template>

  <xsl:template name="divide-tags">
    <xsl:param name="original"/>
    <xsl:param name="separator" select="' - '"/>
    <xsl:choose>
      <xsl:when test="contains($original, $separator)">
        <xsl:element name="a">

          <xsl:attribute name="href">
            <xsl:text>tagview.php?tag=</xsl:text>
            <xsl:call-template name="extract-tag-id">
              <xsl:with-param name="rawstring">
                <xsl:value-of select="substring-before($original, $separator)"/>
              </xsl:with-param>
            </xsl:call-template>

          </xsl:attribute>
          <xsl:attribute name="class">innertag</xsl:attribute>
          <xsl:call-template name="extract-tag-name">
            <xsl:with-param name="rawstring">
              <xsl:value-of select="substring-before($original, $separator)"/>
            </xsl:with-param>
          </xsl:call-template>
        </xsl:element>
        <!-- add some text before the next element -->
        <xsl:text> - </xsl:text>


        <!-- recursive template call on rest of string -->
        <xsl:call-template name="divide-tags">
          <xsl:with-param name="original">
            <xsl:value-of select="substring-after($original, $separator)"/>
          </xsl:with-param>
        </xsl:call-template>

      </xsl:when>

      <!-- last or unique tagname -->
      <xsl:when test="string-length($original) &gt; 0">

        <xsl:element name="a">
          <xsl:attribute name="href">
            <xsl:text>tagview.php?tag=</xsl:text>
            <xsl:call-template name="extract-tag-id">
              <xsl:with-param name="rawstring">
                <xsl:value-of select="$original"/>
              </xsl:with-param>
            </xsl:call-template>
          </xsl:attribute>

          <xsl:attribute name="class">innertag</xsl:attribute>
          <xsl:call-template name="extract-tag-name">
            <xsl:with-param name="rawstring">
              <xsl:value-of select="$original"/>
            </xsl:with-param>
          </xsl:call-template>

        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <!-- do nothing, no string to do anything with -->
      </xsl:otherwise>

    </xsl:choose>
  </xsl:template>

  <xsl:template name="extract-tag-name">
    <xsl:param name="rawstring"/>
    <xsl:param name="sep" select="'::'"/>
    <xsl:value-of select="substring-before($rawstring, $sep)"/>
  </xsl:template>

  <xsl:template name="extract-tag-id">
    <xsl:param name="rawstring"/>
    <xsl:param name="sep" select="'::'"/>
    <xsl:value-of select="substring-after($rawstring, $sep)"/>
  </xsl:template>


</xsl:stylesheet>
