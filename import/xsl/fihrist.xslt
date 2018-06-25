<!-- available fields are defined in solr/biblio/conf/schema.xml -->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
	xmlns:tei="http://www.tei-c.org/ns/1.0">
    <xsl:output method="xml" indent="yes" encoding="utf-8"/>
    <xsl:param name="institution">SOAS, University of London</xsl:param>
    <xsl:param name="collection">FIHRIST</xsl:param>
    <xsl:template match="tei:teiHeader">
		<add>
			<doc>
                <!-- ID -->
                <field name="id">
                    <xsl:value-of select="tei:fileDesc/tei:publicationStmt/tei:idno[@type='msID']" />
                </field>

                <!-- RECORDTYPE -->
                <field name="recordtype">fihrist</field>

                <!-- ALLFIELDS -->
                <field name="allfields">
                    <xsl:value-of select="normalize-space(string(.))"/>
                </field>

                <!-- INSTITUTION -->
                <field name="institution">
                    <xsl:value-of select="$institution" />
                </field>

                <!-- COLLECTION -->
                <field name="collection">
                    <xsl:value-of select="$collection" />
                </field>

				<!-- PUBLISHER -->
                <xsl:if test="tei:fileDesc/tei:publicationStmt/tei:publisher">
                    <field name="publisher">
                        <xsl:value-of select="tei:fileDesc/tei:publicationStmt/tei:publisher"/>
                    </field>
                </xsl:if>
				
                <!-- LANGUAGE -->
<!--                 <xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem/tei:textLang">
                    <field name="language">
                        <xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem/tei:textLang"/>
                    </field>
                </xsl:if> -->

                <!-- AUTHOR -->
                <xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem/tei:author/tei:persName">
					<field name="author">
						<xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem/tei:author/tei:persName"/>
					</field>
				</xsl:if>

                <!-- TITLE -->
                <xsl:if test="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem">
					<field name="title">
						<xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem/tei:title"/>
					</field>
					
					<field name="title_full">
						<xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem/tei:title"/>
					</field>
					
					<field name="title_short">
						<xsl:value-of select="tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem/tei:title"/>
					</field>
					
					<field name="title_sort">
						<xsl:value-of select="php:function('VuFind::stripArticles', string(tei:fileDesc/tei:sourceDesc/tei:msDesc/tei:msContents/tei:msItem/tei:title))"/>
					</field>
                </xsl:if>

			</doc>
		</add>
    </xsl:template>
</xsl:stylesheet>
