<?php

namespace SMW\SPARQLStore;

/**
 * Virtuoso specific adjustments for GenericHttpDatabaseConnector
 *
 * Specific modifications of the SPARQL database implementation for Virtuoso.
 * In particular, Virtuoso does not support SPARQL Update but only the non-standard
 * SPARUL protocol that requires different syntax for update queries.
 * If future versions of Virtuoso support SPARQL Update, the standard SPARQL
 * database connector should work properly.
 *
 * Virtuoso uses the SPARQL query endpoint for updates as well. So both
 * $smwgSparqlUpdateEndpoint and $smwgSparqlQueryEndpoint should be something
 * like 'http://localhost:8890/sparql/'. $smwgSparqlDataEndpoint should be
 * left empty.
 *
 * A graph is always needed, i.e., $smwgSparqlDefaultGraph must be set to some
 * graph name (URI).
 *
 * Known limitations:
 * (might be fixed in recent Virtuoso versions, please let us know)
 *
 * - Data endpoint not tested: $smwgSparqlDataEndpoint should be left empty
 * - Numerical datatypes are not supported properly, and Virtuoso
 *   will miss query results when query conditions require number values.
 *   This also affects Type:Date properties since the use numerical values for
 *   querying.
 * - Some edit (insert) queries fail for unknown reasons, probably related to
 *   unusual/complex input data (e.g., using special characters in strings);
 *   errors will occur when trying to store such values on a page.
 * - Virtuoso stumbles over XSD dates with negative years, even if they have
 *   only four digits as per ISO. Trying to store such data will cause errors.
 *
 * @ingroup Sparql
 *
 * @license GNU GPL v2+
 * @since 1.7.1
 *
 * @author Markus Krötzsch
 */
class VirtuosoHttpDatabaseConnector extends GenericHttpDatabaseConnector {

	/**
	 * DELETE wrapper.
	 * The function declares the standard namespaces wiki, swivt, rdf, owl,
	 * rdfs, property, xsd, so these do not have to be included in
	 * $extraNamespaces.
	 *
	 * @param $deletePattern string CONSTRUCT pattern of tripples to delete
	 * @param $where string condition for data to delete
	 * @param $extraNamespaces array (associative) of namespaceId => namespaceUri
	 * @return boolean stating whether the operations succeeded
	 */
	public function delete( $deletePattern, $where, $extraNamespaces = array() ) {

		$sparql = self::getPrefixString( $extraNamespaces ) . "DELETE" .
			( ( $this->m_defaultGraph !== '' )? " FROM <{$this->m_defaultGraph}> " : '' ) .
			"{ $deletePattern } WHERE { $where }";

		return $this->doUpdate( $sparql );
	}

	/**
	 * INSERT DELETE wrapper.
	 * The function declares the standard namespaces wiki, swivt, rdf, owl,
	 * rdfs, property, xsd, so these do not have to be included in
	 * $extraNamespaces.
	 *
	 * @param $insertPattern string CONSTRUCT pattern of tripples to insert
	 * @param $deletePattern string CONSTRUCT pattern of tripples to delete
	 * @param $where string condition for data to delete
	 * @param $extraNamespaces array (associative) of namespaceId => namespaceUri
	 * @return boolean stating whether the operations succeeded
	 */
	public function insertDelete( $insertPattern, $deletePattern, $where, $extraNamespaces = array() ) {

		$sparql = self::getPrefixString( $extraNamespaces ) . "MODIFY" .
			( ( $this->m_defaultGraph !== '' )? " GRAPH <{$this->m_defaultGraph}> " : '' ) .
			"DELETE { $deletePattern } INSERT { $insertPattern } WHERE { $where }";

		return $this->doUpdate( $sparql );
	}

	/**
	 * INSERT DATA wrapper.
	 * The function declares the standard namespaces wiki, swivt, rdf, owl,
	 * rdfs, property, xsd, so these do not have to be included in
	 * $extraNamespaces.
	 *
	 * @param $triples string of triples to insert
	 * @param $extraNamespaces array (associative) of namespaceId => namespaceUri
	 * @return boolean stating whether the operations succeeded
	 */
	public function insertData( $triples, $extraNamespaces = array() ) {

		if ( $this->m_dataEndpoint !== '' ) {
			$turtle = self::getPrefixString( $extraNamespaces, false ) . $triples;
			return $this->doHttpPost( $turtle );
		}

		$sparql = self::getPrefixString( $extraNamespaces, true ) .
			"INSERT DATA " .
			( ( $this->m_defaultGraph !== '' )? "INTO GRAPH <{$this->m_defaultGraph}> " : '' ) .
			"{ $triples }";

		return $this->doUpdate( $sparql );
	}

	/**
	 * DELETE DATA wrapper.
	 * The function declares the standard namespaces wiki, swivt, rdf, owl,
	 * rdfs, property, xsd, so these do not have to be included in
	 * $extraNamespaces.
	 *
	 * @param $triples string of triples to delete
	 * @param $extraNamespaces array (associative) of namespaceId => namespaceUri
	 * @return boolean stating whether the operations succeeded
	 */
	public function deleteData( $triples, $extraNamespaces = array() ) {

		$sparql = self::getPrefixString( $extraNamespaces ) .
			"DELETE DATA " .
			( ( $this->m_defaultGraph !== '' )? "FROM GRAPH <{$this->m_defaultGraph}> " : '' ) .
			"{ $triples }";

		return $this->doUpdate( $sparql );
	}

	/**
	 * Execute a SPARQL update and return a boolean to indicate if the
	 * operations was successful.
	 *
	 * Virtuoso expects SPARQL updates to be posted using the "query"
	 * parameter (rather than "update").
	 *
	 * @param $sparql string with the complete SPARQL update query (INSERT or DELETE)
	 * @return boolean
	 */
	public function doUpdate( $sparql ) {

		if ( $this->m_updateEndpoint === '' ) {
			throw new BadHttpDatabaseResponseException( BadHttpDatabaseResponseException::ERROR_NOSERVICE, $sparql, 'not specified' );
		}

		$this->httpRequest->setOption( CURLOPT_URL, $this->m_updateEndpoint );
		$this->httpRequest->setOption( CURLOPT_POST, true );

		$parameterString = "query=" . urlencode( $sparql );

		$this->httpRequest->setOption( CURLOPT_POSTFIELDS, $parameterString );
		$this->httpRequest->execute();

		if ( $this->httpRequest->getLastErrorCode() == 0 ) {
			return true;
		}

		$this->mapHttpRequestError( $this->m_updateEndpoint, $sparql );
		return false;
	}

}

