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
      <!-- change class depending on whether this tag has resources or not -->
      <xsl:attribute name="class">
        <xsl:text>tagentry </xsl:text>
        <xsl:choose>
          <xsl:when test="./popularity = 0">
            <xsl:text>nores</xsl:text>
          </xsl:when>
          <xsl:otherwise>
            <xsl:text>res</xsl:text>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>

      <xsl:attribute name="id">
        <xsl:value-of select="concat('tagid', numid)"/>
      </xsl:attribute>
      <!-- Name of tag -->
      <xsl:element name="p">

        <xsl:element name="input">
          <xsl:attribute name="type">checkbox</xsl:attribute>
          <xsl:attribute name="class">fusioncheck</xsl:attribute>
          <xsl:attribute name="disabled">disabled</xsl:attribute>
        </xsl:element>

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
          <xsl:text> ressources) </xsl:text>
        </xsl:element>

      <xsl:element name="a">
        <xsl:attribute name="href">#</xsl:attribute>
        <xsl:attribute name="class">edit</xsl:attribute>
        <xsl:text>Editer</xsl:text>
      </xsl:element>
      </xsl:element>
      <!-- commands -->
      <xsl:element name="div">
        <xsl:attribute name="class">tagcommands</xsl:attribute>

      <!-- Rename -->
      <xsl:element name="form">
        <xsl:attribute name="class">rename</xsl:attribute>
        <xsl:attribute name="action">tag.php</xsl:attribute>
        <xsl:attribute name="method">post</xsl:attribute>
        <xsl:element name="p">
          <xsl:text> Modifier : </xsl:text>
            <xsl:element name="input">
              <xsl:attribute name="class">renamebox</xsl:attribute>
              <xsl:attribute name="type">text</xsl:attribute>
              <xsl:attribute name="maxlength">255</xsl:attribute>
              <xsl:attribute name="size">20</xsl:attribute>
              <xsl:attribute name="name">folksonewname</xsl:attribute>
              <xsl:attribute name="value"> 
                <xsl:value-of select="display"/>
              </xsl:attribute>

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
              <xsl:attribute name="class">renamebutton</xsl:attribute>
            </xsl:element>
          </xsl:element>
      </xsl:element>

    <!-- Delete -->
      <xsl:element name="form">
        <xsl:attribute name="class">delete</xsl:attribute>
          <xsl:attribute name="action">tag.php</xsl:attribute>
          <xsl:attribute name="method">post</xsl:attribute>
          <xsl:element name="p">
            <xsl:text>Supprimer : </xsl:text>

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
          Fusionner avec (le tag "</xsl:text>
        <xsl:value-of select="display"/>
        <xsl:text>" sera supprimé) : </xsl:text>
        <xsl:element name="input">
          <xsl:attribute name="class">fusionbox</xsl:attribute>
          <xsl:attribute name="name">folksotarget</xsl:attribute>
          <xsl:attribute name="type">text</xsl:attribute>
          <xsl:attribute name="maxlength">255</xsl:attribute>
          <xsl:attribute name="size">20</xsl:attribute>
        </xsl:element>
        <xsl:element name="button">
          <xsl:attribute name="type">submit</xsl:attribute>
          <xsl:attribute name="value">
            <xsl:value-of select="numid"/>
          </xsl:attribute>
          <xsl:attribute name="class">fusionbutton</xsl:attribute>
          <xsl:attribute name="name">folksotag</xsl:attribute>
          Fusionner
        </xsl:element>
      </xsl:element>
      </xsl:element>

<xsl:element name="div">
  <xsl:attribute name="class">multifusion</xsl:attribute>
  <xsl:element name="h4">
    <xsl:text>Fusion Multiple</xsl:text>
  </xsl:element>

  <xsl:element name="p">
    <xsl:text>Sélectionner sur la page les tags à fusionner avec </xsl:text>
    <xsl:element name="em">
      <xsl:value-of select="display"/>
    </xsl:element>
    <xsl:text>. Les autres tags seront supprimés au profit de celui-ci.</xsl:text>
  </xsl:element>

        <xsl:element name="p">
          <xsl:attribute name="class">multifusionvictims</xsl:attribute>
        </xsl:element>

        <xsl:element name="p">
      <xsl:element name="a">
        <xsl:attribute name="class">multifusionbutton</xsl:attribute>
        <xsl:attribute name="href">#</xsl:attribute>
        <xsl:text>
          Multi-fusion
        </xsl:text>
      </xsl:element>
        </xsl:element>
</xsl:element>

    <!-- close box -->
    <xsl:element name="p">
      <xsl:element name="a">
        <xsl:attribute name="href">#</xsl:attribute>
        <xsl:attribute name="class">closeeditbox</xsl:attribute>
        <xsl:text>Fermer</xsl:text>
      </xsl:element>
    </xsl:element>

    </xsl:element>  <!-- end of commands div -->

    </xsl:element>
  </xsl:template>

</xsl:stylesheet>
