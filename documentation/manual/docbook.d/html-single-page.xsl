<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
				xmlns:fo="http://www.w3.org/1999/XSL/Format"
				version="1.0">

<xsl:import href="docbook-xsl/xhtml/docbook.xsl"/>
	<xsl:param name="make.valid.html">1</xsl:param>
	<xsl:param name="section.autolabel">1</xsl:param>
	<xsl:param name="generate.index">1</xsl:param>
	<xsl:param name="section.label.includes.component.label">1</xsl:param>
	<xsl:param name="admon.graphics">1</xsl:param>
	<xsl:param name="admon.style"/>
	<xsl:param name="html.stylesheet"/>
	<xsl:param name="header.rule">0</xsl:param>
	<xsl:param name="footer.rule">0</xsl:param>

<xsl:template name="user.header.navigation">
  <!-- stuff put here appears before the top navigation area -->
</xsl:template>

<xsl:template name="user.footer.navigation">
  <!-- stuff put here appears after the bottom navigation area -->
  <xsl:element name="div">
    <xsl:attribute name="class">revinfo</xsl:attribute>
    <xsl:value-of select="//pubdate[1]"/>
  </xsl:element>
</xsl:template>


</xsl:stylesheet>