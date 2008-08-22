<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml"/>
  <xsl:template match="/">

    <xsl:element name="ul">
      <xsl:attribute name="class">taglist</xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tag">
    <xsl:element name="li">
      <xsl:attribute name="class">tagentry</xsl:attribute>
      <xsl:attribute name="id">
        <xsl:value-of select="concat('tagid', numid)"/>
      </xsl:attribute>
      <!-- Name of tag -->
      <xsl:element name="p">
        <xsl:element name="a">
          <xsl:attribute name="class">tagname</xsl:attribute>
        <xsl:attribute name="href">
        <xsl:value-of select="concat('resourceview.php?tag=',
                                     numid)"/>
        </xsl:attribute>
          <xsl:value-of select="display"/>
        </xsl:element>

        <!-- popularity -->
        <xsl:element name="span">
          <xsl:attribute name="class">tagpopularity</xsl:attribute>
          <xsl:text> (</xsl:text>
          <xsl:value-of select="popularity"/>
          <xsl:text> ressources)</xsl:text>
        </xsl:element>

      </xsl:element>

      <!-- Rename -->
      <xsl:element name="form">
        <xsl:attribute name="class">rename</xsl:attribute>
        <xsl:attribute name="action">tag.php</xsl:attribute>
        <xsl:attribute name="method">post</xsl:attribute>
        <xsl:element name="p">
          <xsl:text> Modifier ce tag : </xsl:text>
            <xsl:element name="input">
              <xsl:attribute name="type">text</xsl:attribute>
              <xsl:attribute name="maxlength">255</xsl:attribute>
              <xsl:attribute name="size">30</xsl:attribute>
              <xsl:attribute name="name">folksonewname</xsl:attribute>
            </xsl:element>

            <xsl:element name="input">
              <xsl:attribute name="type">hidden</xsl:attribute>
              <xsl:attribute name="value">
                <xsl:value-of select="numid"/>
              </xsl:attribute>
              <xsl:attribute name="name">folksotag</xsl:attribute>
            </xsl:element>

            <xsl:element name="input">
              <xsl:attribute name="type">submit</xsl:attribute>
              <xsl:attribute name="value">Modifier</xsl:attribute>
            </xsl:element>
          </xsl:element>
      </xsl:element>

    <!-- Delete -->
      <xsl:element name="form">
        <xsl:attribute name="class">delete</xsl:attribute>
          <xsl:attribute name="action">tag.php</xsl:attribute>
          <xsl:attribute name="method">post</xsl:attribute>
          <xsl:element name="p">
            <xsl:text>Supprimer ce tag : </xsl:text>

            <xsl:element name="input">
              <xsl:attribute name="type">hidden</xsl:attribute>
              <xsl:attribute name="name">folksodelete</xsl:attribute>
            </xsl:element>

            <xsl:element name="button">
              <xsl:attribute name="class">delete</xsl:attribute>
              <xsl:attribute name="type">submit</xsl:attribute>
              <xsl:attribute name="name">folksotag</xsl:attribute>
              <xsl:attribute name="value">
                <xsl:value-of select="numid"/>
              </xsl:attribute>
              Suppression
            </xsl:element>
          </xsl:element>
      </xsl:element>

      <!-- fusionner -->
      <xsl:element name="form">
        <xsl:attribute name="class">merge</xsl:attribute>
          <xsl:attribute name="action">tag.php</xsl:attribute>
          <xsl:attribute name="method">post</xsl:attribute>
      <xsl:element name="p">
        <xsl:text> 
          Fusionner ce tag avec :
        </xsl:text>

        <xsl:element name="input">
          <xsl:attribute name="class">fusionbox</xsl:attribute>
          <xsl:attribute name="name">folksotarget</xsl:attribute>
          <xsl:attribute name="type">text</xsl:attribute>
          <xsl:attribute name="maxlength">255</xsl:attribute>
          <xsl:attribute name="size">30</xsl:attribute>
        </xsl:element>
        <xsl:element name="button">
          <xsl:attribute name="type">submit</xsl:attribute>
          <xsl:attribute name="value">
            <xsl:value-of select="numid"/>
          </xsl:attribute>
          <xsl:attribute name="name">folksotag</xsl:attribute>
          Fusionner
        </xsl:element>
      </xsl:element>
      </xsl:element>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>
