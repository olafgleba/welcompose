<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
				xmlns:fo="http://www.w3.org/1999/XSL/Format"
				version="1.0">

<xsl:import href="docbook-xsl/xhtml/chunk.xsl"/>
	<xsl:param name="use.extensions">0</xsl:param>
	<xsl:param name="use.id.as.filename">1</xsl:param>
	<xsl:param name="base.dir">./</xsl:param>
	<xsl:param name="chunk.fast">1</xsl:param>
	<xsl:param name="make.valid.html">1</xsl:param>
	<xsl:param name="section.autolabel">1</xsl:param>
	<xsl:param name="generate.index">1</xsl:param>
	<xsl:param name="section.label.includes.component.label">1</xsl:param>
	<xsl:param name="chunker.output.indent">yes</xsl:param>
	<xsl:param name="chunker.output.encoding">UTF-8</xsl:param>
	<xsl:param name="chunk.first.sections">0</xsl:param>
	<xsl:param name="chunk.tocs.and.lots">0</xsl:param>
	<xsl:param name="html.extra.head.links">1</xsl:param>
	<xsl:param name="generate.manifest">1</xsl:param>
	<xsl:param name="admon.graphics">1</xsl:param>
	<xsl:param name="admon.style"/>
	<xsl:param name="html.stylesheet">styles/manual_html_chunk.css styles/print_html_chunk.css</xsl:param>
	<xsl:param name="header.rule">0</xsl:param>
	<xsl:param name="footer.rule">0</xsl:param>

<xsl:template name="user.header.navigation">
  <!-- stuff put here appears before the top navigation area -->
</xsl:template>

<!-- customized top navigation area -->
<xsl:template name="header.navigation">
  <xsl:param name="prev" select="/foo"/>
  <xsl:param name="next" select="/foo"/>
  <xsl:param name="nav.context"/>

  <xsl:variable name="home" select="/*[1]"/>
  <xsl:variable name="up" select="parent::*"/>

  <xsl:variable name="row1" select="$navig.showtitles != 0"/>
  <xsl:variable name="row2" select="count($prev) &gt; 0 or (count($up) &gt; 0 and generate-id($up) != generate-id($home) and $navig.showtitles != 0) or count($next) &gt; 0"/>
  <xsl:if test="$suppress.navigation = '0' and $suppress.header.navigation = '0'">
    <div class="navheader">
      <xsl:if test="$row1 or $row2">
        <table width="100%" summary="Navigation header">
          <xsl:if test="$row1">
            <tr>
              <th colspan="3">
                <xsl:choose> 
                  <xsl:when test="count($up) &gt; 0 and generate-id($up) != generate-id($home) and $navig.showtitles != 0">
                    <span class="breadcrumb_second">
					<xsl:apply-templates select="$up" mode="object.title.markup"/>
					</span>
                  </xsl:when>
                  <xsl:otherwise></xsl:otherwise>
                </xsl:choose>
                <xsl:choose>
					<xsl:when test="count($up) &gt; 0 and generate-id($up) != generate-id($home) and $navig.showtitles != 0">
						<span class="breadcrumb"> > </span>
						<span class="breadcrumb_first">
						<xsl:apply-templates select="." mode="object.title.markup"/>
						</span>
                  	</xsl:when>
                  <xsl:otherwise>
                  	    <span class="breadcrumb_first_on">
						<xsl:apply-templates select="." mode="object.title.markup"/>
						</span>
                  </xsl:otherwise>
                </xsl:choose>
              </th>
            </tr>
          </xsl:if>

          <xsl:if test="$row2">
            <tr>
              <td class="navprevious">
                <xsl:if test="count($prev)&gt;0"><a title="zurück" accesskey="p">
                    <xsl:attribute name="href">
                      <xsl:call-template name="href.target">
                        <xsl:with-param name="object" select="$prev"/>
                      </xsl:call-template>
                    </xsl:attribute></a>
                </xsl:if>
              </td>
              <th class="navobjecttitle">
			  </th>
              <td class="navnext">
                <xsl:if test="count($next)&gt;0"><a title="weiter" accesskey="n">
                    <xsl:attribute name="href">
                      <xsl:call-template name="href.target">
                        <xsl:with-param name="object" select="$next"/>
                      </xsl:call-template>
                    </xsl:attribute></a>
                </xsl:if>
              </td>
            </tr>
          </xsl:if>
        </table>
      </xsl:if>
      <xsl:if test="$header.rule != 0">
        <hr/>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>
<!-- eof customized top navigation area -->


<!-- customized footer navigation area -->
<xsl:template name="footer.navigation">
  <xsl:param name="prev" select="/foo"/>
  <xsl:param name="next" select="/foo"/>
  <xsl:param name="nav.context"/>

  <xsl:variable name="home" select="/*[1]"/>
  <xsl:variable name="up" select="parent::*"/>

  <xsl:variable name="row1" select="count($prev) &gt; 0 or count($up) &gt; 0
	 or count($next) &gt; 0"/>
  <xsl:variable name="row2" select="($prev and $navig.showtitles != 0) or (generate-id($home) != generate-id(.) or $nav.context = 'toc') or ($chunk.tocs.and.lots != 0 and $nav.context != 'toc') or ($next and $navig.showtitles != 0)"/>
  <xsl:if test="$suppress.navigation = '0' and $suppress.footer.navigation = '0'">
    <div class="navfooter">
      <xsl:if test="$footer.rule != 0">
        <hr/>
      </xsl:if>

      <xsl:if test="$row1 or $row2">
        <table width="100%" summary="Navigation footer">
          <xsl:if test="$row1">
            <tr>
              <td class="navfooterprevious">
                <xsl:if test="count($prev)&gt;0">
                  <a accesskey="p">
                    <xsl:attribute name="href">
                      <xsl:call-template name="href.target">
                        <xsl:with-param name="object" select="$prev"/>
                      </xsl:call-template>
                    </xsl:attribute>
                  </a>
                </xsl:if>
              </td>
              <td class="navfootercenter">
                <xsl:choose>
                  <xsl:when test="count($up)&gt;0 and generate-id($up) != generate-id($home)">
                    <a accesskey="u">
                      <xsl:attribute name="href">
                        <xsl:call-template name="href.target">
                          <xsl:with-param name="object" select="$up"/>
                        </xsl:call-template>
                      </xsl:attribute>
                      <xsl:call-template name="navig.content">
                        <xsl:with-param name="direction" select="'up'"/>
                      </xsl:call-template>
                    </a>
                  </xsl:when>
                  <xsl:otherwise></xsl:otherwise>
                </xsl:choose>
              </td>
              <td class="navfooternext">
                <xsl:if test="count($next)&gt;0">
                  <a accesskey="n">
                    <xsl:attribute name="href">
                      <xsl:call-template name="href.target">
                        <xsl:with-param name="object" select="$next"/>
                      </xsl:call-template>
                    </xsl:attribute>
                  </a>
                </xsl:if>
              </td>
            </tr>
          </xsl:if>

          <xsl:if test="$row2">
            <tr>
              <td class="navfootersecprevious">
                <xsl:if test="$navig.showtitles != 0">
                  <xsl:apply-templates select="$prev" mode="object.title.markup"/>
                </xsl:if>
              </td>
              <td class="navfooterseccenter">
                <xsl:choose>
                  <xsl:when test="$home != . or $nav.context = 'toc'">
                    <a accesskey="h">
                      <xsl:attribute name="href">
                        <xsl:call-template name="href.target">
                          <xsl:with-param name="object" select="$home"/>
                        </xsl:call-template>
                      </xsl:attribute>
                      <xsl:call-template name="navig.content">
                        <xsl:with-param name="direction" select="'home'"/>
                      </xsl:call-template>
                    </a>
                    <xsl:if test="$chunk.tocs.and.lots != 0 and $nav.context != 'toc'">
                      <xsl:text>&#160;|&#160;</xsl:text>
                    </xsl:if>
                  </xsl:when>
                  <xsl:otherwise></xsl:otherwise>
                </xsl:choose>

                <xsl:if test="$chunk.tocs.and.lots != 0 and $nav.context != 'toc'">
                  <a accesskey="t">
                    <xsl:attribute name="href">
                      <xsl:apply-templates select="/*[1]" mode="recursive-chunk-filename">
                        <xsl:with-param name="recursive" select="true()"/>
                      </xsl:apply-templates>
                      <xsl:text>-toc</xsl:text>
                      <xsl:value-of select="$html.ext"/>
                    </xsl:attribute>
                    <xsl:call-template name="gentext">
                      <xsl:with-param name="key" select="'nav-toc'"/>
                    </xsl:call-template>
                  </a>
                </xsl:if>
              </td>
              <td class="navfootersecnext">
                <xsl:if test="$navig.showtitles != 0">
                  <xsl:apply-templates select="$next" mode="object.title.markup"/>
                </xsl:if>
              </td>
            </tr>
          </xsl:if>
        </table>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>
<!-- eof customized footer navigation area -->


<xsl:template name="user.footer.navigation">
  <!-- stuff put here appears after the bottom navigation area -->
  <xsl:element name="div">
    <xsl:attribute name="class">revinfo</xsl:attribute>
    <xsl:value-of select="//pubdate[1]"/>
  </xsl:element>
</xsl:template>


</xsl:stylesheet>