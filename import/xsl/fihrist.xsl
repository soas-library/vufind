<!-- available fields are defined in solr/biblio/conf/schema.xml -->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:tei="http://www.tei-c.org/ns/1.0"
    xmlns:php="http://php.net/xsl"
    xmlns:xlink="http://www.w3.org/2001/XMLSchema-instance">
    <xsl:output method="xml" indent="yes" encoding="utf-8"/>
    <xsl:param name="institution">SOAS, University of London</xsl:param>
    <xsl:param name="collection">FIHRIST</xsl:param>
    <xsl:template match="tei">
		<add>
			<doc>
				<field name="title">
					<xsl:value-of select="//teiHeader/fileDesc/titleStmt"/>
				</field>
			</doc>
		</add>
    </xsl:template>
</xsl:stylesheet>
