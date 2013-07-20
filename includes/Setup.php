<?php

/**
 * Global functions used for setting up the Semantic MediaWiki extension.
 *
 * @file SMW_Setup.php
 * @ingroup SMW
 */

/**
 * Function to switch on Semantic MediaWiki. This function must be called in
 * LocalSettings.php after including SMW_Settings.php. It is used to ensure
 * that required parameters for SMW are really provided explicitly. For
 * readability, this is the only global function that does not adhere to the
 * naming conventions.
 *
 * This function also sets up all autoloading, such that all SMW classes are
 * available as early on. Moreover, jobs and special pages are registered.
 *
 * @deprecated since 1.9, just set $smwgNamespace after the inclusion of SemanticMediaWiki.php
 *
 * @param mixed $namespace
 * @param boolean $complete
 *
 * @return true
 *
 * @codeCoverageIgnore
 */
function enableSemantics( $namespace = null, $complete = false ) {
	global $smwgNamespace;

	if ( !$complete && ( $smwgNamespace !== '' ) ) {
		// The dot tells that the domain is not complete. It will be completed
		// in the Export since we do not want to create a title object here when
		// it is not needed in many cases.
		$smwgNamespace = '.' . $namespace;
	} else {
		$smwgNamespace = $namespace;
	}

	return true;
}

/**
 * Register all SMW hooks with MediaWiki.
 *
 * @codeCoverageIgnore
 */
function smwfRegisterHooks() {
	global $wgHooks;

	$wgHooks['LoadExtensionSchemaUpdates'][] = 'SMWHooks::onSchemaUpdate';

	$wgHooks['ParserTestTables'][]    = 'SMWHooks::onParserTestTables';
	$wgHooks['AdminLinks'][]          = 'SMWHooks::addToAdminLinks';
	$wgHooks['PageSchemasRegisterHandlers'][] = 'SMWHooks::onPageSchemasRegistration';

	$wgHooks['ParserFirstCallInit'][] = 'SMW\DocumentationParserFunction::staticInit';
	$wgHooks['ParserFirstCallInit'][] = 'SMW\InfoParserFunction::staticInit';

	// Register wikipage that has been manually purged (?action=purge)
	$wgHooks['ArticlePurge'][] = 'SMWHooks::onArticlePurge';

	// Fetch some MediaWiki data for replication in SMW's store
	$wgHooks['ParserAfterTidy'][] = 'SMWHooks::onParserAfterTidy';

	// Update data after template change and at save
	$wgHooks['LinksUpdateConstructed'][] = 'SMWHooks::onLinksUpdateConstructed';

	// Delete annotations
	$wgHooks['ArticleDelete'][] = 'SMWHooks::onArticleDelete';

	// Move annotations
	$wgHooks['TitleMoveComplete'][] = 'SMWHooks::onTitleMoveComplete';

	// Additional special properties (modification date etc.)
	$wgHooks['NewRevisionFromEditComplete'][] = 'SMWHooks::onNewRevisionFromEditComplete';

	// Parsing [[link::syntax]] and resolves property annotations
	$wgHooks['InternalParseBeforeLinks'][] = 'SMWHooks::onInternalParseBeforeLinks';

	$wgHooks['OutputPageParserOutput'][] = 'SMWFactbox::onOutputPageParserOutput'; // copy some data for later Factbox display
	$wgHooks['ArticleFromTitle'][] = 'SMWHooks::onArticleFromTitle'; // special implementations for property/type articles
	$wgHooks['ParserFirstCallInit'][] = 'SMWHooks::onParserFirstCallInit';

	// Alter the structured navigation links in SkinTemplates
	$wgHooks['SkinTemplateNavigation'][] = 'SMWHooks::onSkinTemplateNavigation';

	//UnitTests
	$wgHooks['UnitTestsList'][] = 'SMWHooks::registerUnitTests';
	$wgHooks['ResourceLoaderTestModules'][] = 'SMWHooks::registerQUnitTests';

	// Statistics
	$wgHooks['SpecialStatsAddExtra'][] = 'SMWHooks::onSpecialStatsAddExtra';

	// User preference
	$wgHooks['GetPreferences'][] = 'SMWHooks::onGetPreferences';

	// Add changes to the output page
	$wgHooks['BeforePageDisplay'][] = 'SMWHooks::onBeforePageDisplay';
	$wgHooks['TitleIsAlwaysKnown'][] = 'SMWHooks::onTitleIsAlwaysKnown';
	$wgHooks['BeforeDisplayNoArticleText'][] = 'SMWHooks::onBeforeDisplayNoArticleText';

	// ResourceLoader
	$wgHooks['ResourceLoaderGetConfigVars'][] = 'SMWHooks::onResourceLoaderGetConfigVars';

	if ( $GLOBALS['smwgToolboxBrowseLink'] ) {
		$wgHooks['SkinTemplateToolboxEnd'][] = 'SMWHooks::showBrowseLink';
	}

	$wgHooks['SkinAfterContent'][] = 'SMWFactbox::onSkinAfterContent'; // draw Factbox below categories

	$wgHooks['ExtensionTypes'][] = 'SMWHooks::addSemanticExtensionType';
}

/**
 * Register all SMW classes with the MediaWiki autoloader.
 *
 * @codeCoverageIgnore
 */
function smwfRegisterClasses() {
	global $smwgIP, $wgAutoloadClasses, $wgJobClasses;

	$wgAutoloadClasses['SMWHooks']                  = $smwgIP . 'SemanticMediaWiki.hooks.php';

	$incDir = $smwgIP . 'includes/';
	$wgAutoloadClasses['SMW\FormatFactory']       	= $incDir . 'FormatFactory.php';
	$wgAutoloadClasses['SMW\Highlighter']           = $incDir . 'Highlighter.php';
	$wgAutoloadClasses['SMW\ParameterInput']        = $incDir . 'ParameterInput.php';
	$wgAutoloadClasses['SMWFactbox']                = $incDir . 'Factbox.php';
	$wgAutoloadClasses['SMWInfolink']               = $incDir . 'SMW_Infolink.php';
	$wgAutoloadClasses['SMWOutputs']                = $incDir . 'SMW_Outputs.php';
	$wgAutoloadClasses['SMW\ParserTextProcessor']   = $incDir . 'ParserTextProcessor.php';
	$wgAutoloadClasses['SMWSemanticData']           = $incDir . 'SMW_SemanticData.php';
	$wgAutoloadClasses['SMW\SemanticData']          = $incDir . 'SMW_SemanticData.php'; // 1.9
	$wgAutoloadClasses['SMWPageLister']             = $incDir . 'SMW_PageLister.php';

	$wgAutoloadClasses['SMWDataValueFactory']       = $incDir . 'DataValueFactory.php';
	$wgAutoloadClasses['SMW\DataValueFactory']      = $incDir . 'DataValueFactory.php';

	$wgAutoloadClasses['SMWParseData']              = $incDir . 'SMW_ParseData.php';
	$wgAutoloadClasses['SMW\IParserData']           = $incDir . 'ParserData.php';
	$wgAutoloadClasses['SMW\ParserData']            = $incDir . 'ParserData.php';
	$wgAutoloadClasses['SMW\PropertyDisparityDetector']  = $incDir . 'PropertyDisparityDetector.php';

	$wgAutoloadClasses['SMW\PropertyAnnotationComplementor']  = $incDir . 'PropertyAnnotationComplementor.php';

	$wgAutoloadClasses['SMW\Subobject']             = $incDir . 'Subobject.php';
	$wgAutoloadClasses['SMW\RecurringEvents']       = $incDir . 'RecurringEvents.php';

	$wgAutoloadClasses['SMW\Settings']              = $incDir . 'Settings.php';

	$wgAutoloadClasses['SMW\CacheHandler']          = $incDir . '/cache/CacheHandler.php';
	$wgAutoloadClasses['SMW\ResultCacheMapper']     = $incDir . '/cache/ResultCacheMapper.php';
	$wgAutoloadClasses['SMW\CacheIdGenerator']      = $incDir . '/cache/CacheIdGenerator.php';

	// Utilities
	$wgAutoloadClasses['SMW\NamespaceExaminer']         = $incDir . '/utilities/NamespaceExaminer.php';
	$wgAutoloadClasses['SMW\Profiler']                  = $incDir . '/utilities/Profiler.php';
	$wgAutoloadClasses['SMW\IdGenerator']               = $incDir . '/utilities/HashIdGenerator.php';
	$wgAutoloadClasses['SMW\HashIdGenerator']           = $incDir . '/utilities/HashIdGenerator.php';
	$wgAutoloadClasses['SMW\Accessor']                  = $incDir . '/utilities/ArrayAccessor.php';
	$wgAutoloadClasses['SMW\Arrayable']                 = $incDir . '/utilities/ArrayAccessor.php';
	$wgAutoloadClasses['SMW\ArrayAccessor']             = $incDir . '/utilities/ArrayAccessor.php';
	$wgAutoloadClasses['SMW\MessageReporter']           = $incDir . '/utilities/MessageReporter.php';
	$wgAutoloadClasses['SMW\ObservableMessageReporter'] = $incDir . '/utilities/MessageReporter.php';
	$wgAutoloadClasses['SMW\RedirectBuilder']           = $incDir . '/utilities/RedirectBuilder.php';

	$wgAutoloadClasses['SMW\Publisher']                 = $incDir . '/utilities/ObserverInterfaceProvider.php';
	$wgAutoloadClasses['SMW\Subject']                   = $incDir . '/utilities/ObserverInterfaceProvider.php';
	$wgAutoloadClasses['SMW\Observer']                  = $incDir . '/utilities/ObserverInterfaceProvider.php';
	$wgAutoloadClasses['SMW\Subscriber']                = $incDir . '/utilities/ObserverInterfaceProvider.php';

	// Formatters
	$wgAutoloadClasses['SMW\ArrayFormatter']               = $incDir . 'formatters/ArrayFormatter.php';
	$wgAutoloadClasses['SMW\ParserParameterFormatter']     = $incDir . 'formatters/ParserParameterFormatter.php';
	$wgAutoloadClasses['SMW\MessageFormatter']             = $incDir . 'formatters/MessageFormatter.php';
	$wgAutoloadClasses['SMW\TableFormatter']               = $incDir . 'formatters/TableFormatter.php';
	$wgAutoloadClasses['SMW\ParameterFormatterFactory']    = $incDir . 'formatters/ParameterFormatterFactory.php';
	$wgAutoloadClasses['SMW\ApiQueryResultFormatter']      = $incDir . 'formatters/ApiQueryResultFormatter.php';
	$wgAutoloadClasses['SMW\ApiRequestParameterFormatter'] = $incDir . 'formatters/ApiRequestParameterFormatter.php';

	// Exceptions
	$wgAutoloadClasses['SMW\InvalidStoreException']        = $incDir . '/exceptions/InvalidStoreException.php';
	$wgAutoloadClasses['SMW\InvalidSemanticDataException'] = $incDir . '/exceptions/InvalidSemanticDataException.php';
	$wgAutoloadClasses['SMW\InvalidNamespaceException']    = $incDir . '/exceptions/InvalidNamespaceException.php';
	$wgAutoloadClasses['SMW\InvalidPropertyException']     = $incDir . '/exceptions/InvalidPropertyException.php';
	$wgAutoloadClasses['SMW\InvalidResultException']       = $incDir . '/exceptions/InvalidResultException.php';
	$wgAutoloadClasses['SMW\DataItemException']            = $incDir . '/exceptions/DataItemException.php'; // 1.9
	$wgAutoloadClasses['SMWDataItemException']             = $incDir . '/exceptions/DataItemException.php';
	$wgAutoloadClasses['SMW\InvalidSettingsArgumentException']   = $incDir . '/exceptions/InvalidSettingsArgumentException.php';
	$wgAutoloadClasses['SMW\InvalidPredefinedPropertyException'] = $incDir . '/exceptions/InvalidPredefinedPropertyException.php';

	// Query pages
	$wgAutoloadClasses['SMW\QueryPage']                 = $incDir . '/querypages/QueryPage.php';
	$wgAutoloadClasses['SMW\WantedPropertiesQueryPage'] = $incDir . '/querypages/WantedPropertiesQueryPage.php';
	$wgAutoloadClasses['SMW\UnusedPropertiesQueryPage'] = $incDir . '/querypages/UnusedPropertiesQueryPage.php';
	$wgAutoloadClasses['SMW\PropertiesQueryPage']       = $incDir . '/querypages/PropertiesQueryPage.php';


	// Article pages
	$apDir = $smwgIP . 'includes/articlepages/';
	$wgAutoloadClasses['SMWOrderedListPage']        = $apDir . 'SMW_OrderedListPage.php';
	$wgAutoloadClasses['SMWPropertyPage']           = $apDir . 'SMW_PropertyPage.php';
	$wgAutoloadClasses['SMW\ConceptPage']           = $apDir . 'ConceptPage.php';

	// Printers
	$qpDir = $smwgIP . 'includes/queryprinters/';
	$wgAutoloadClasses['SMWExportPrinter']          = $qpDir . 'FileExportPrinter.php';
	$wgAutoloadClasses['SMW\FileExportPrinter']     = $qpDir . 'FileExportPrinter.php';
	$wgAutoloadClasses['SMW\ExportPrinter']         = $qpDir . 'ExportPrinter.php';
	$wgAutoloadClasses['SMWIResultPrinter']         = $qpDir . 'SMW_IResultPrinter.php';
	$wgAutoloadClasses['SMWTableResultPrinter']     = $qpDir . 'TableResultPrinter.php';
	$wgAutoloadClasses['SMW\TableResultPrinter']    = $qpDir . 'TableResultPrinter.php'; // 1.9
	$wgAutoloadClasses['SMWCategoryResultPrinter']  = $qpDir . 'SMW_QP_Category.php';
	$wgAutoloadClasses['SMWEmbeddedResultPrinter']  = $qpDir . 'SMW_QP_Embedded.php';
	$wgAutoloadClasses['SMWCsvResultPrinter']       = $qpDir . 'CsvResultPrinter.php';
	$wgAutoloadClasses['SMW\CsvResultPrinter']      = $qpDir . 'CsvResultPrinter.php'; // 1.9
	$wgAutoloadClasses['SMWDSVResultPrinter']       = $qpDir . 'SMW_QP_DSV.php';
	$wgAutoloadClasses['SMWRDFResultPrinter']       = $qpDir . 'SMW_QP_RDF.php';
	$wgAutoloadClasses['SMWResultPrinter']          = $qpDir . 'ResultPrinter.php';
	$wgAutoloadClasses['SMW\ResultPrinter']         = $qpDir . 'ResultPrinter.php'; // 1.9
	$wgAutoloadClasses['SMW\ApiResultPrinter']      = $qpDir . 'ApiResultPrinter.php';
	$wgAutoloadClasses['SMWListResultPrinter']      = $qpDir . 'ListResultPrinter.php';
	$wgAutoloadClasses['SMW\ListResultPrinter']     = $qpDir . 'ListResultPrinter.php';
	$wgAutoloadClasses['SMW\FeedResultPrinter']     = $qpDir . 'FeedResultPrinter.php'; // 1.9
	$wgAutoloadClasses['SMWJSONResultPrinter']      = $qpDir . 'JSONResultPrinter.php';
	$wgAutoloadClasses['SMW\JsonResultPrinter']     = $qpDir . 'JsonResultPrinter.php'; // 1.9
	$wgAutoloadClasses['SMWAggregatablePrinter']    = $qpDir . 'AggregatablePrinter.php';
	$wgAutoloadClasses['SMW\AggregatablePrinter']   = $qpDir . 'AggregatablePrinter.php'; // 1.9

	// Data items
	$diDir = $smwgIP . 'includes/dataitems/';
	$wgAutoloadClasses['SMWDataItem']               = $diDir . 'SMW_DataItem.php';
	$wgAutoloadClasses['SMWDIProperty']             = $diDir . 'SMW_DI_Property.php';
	$wgAutoloadClasses['SMW\DIProperty']            = $diDir . 'SMW_DI_Property.php'; // 1.9
	$wgAutoloadClasses['SMWDIBoolean']              = $diDir . 'SMW_DI_Bool.php';
	$wgAutoloadClasses['SMWDINumber']               = $diDir . 'SMW_DI_Number.php';
	$wgAutoloadClasses['SMWDIBlob']                 = $diDir . 'SMW_DI_Blob.php';
	$wgAutoloadClasses['SMWDIString']               = $diDir . 'SMW_DI_String.php';
	$wgAutoloadClasses['SMWStringLengthException']  = $diDir . 'SMW_DI_String.php';
	$wgAutoloadClasses['SMWDIUri']                  = $diDir . 'SMW_DI_URI.php';
	$wgAutoloadClasses['SMWDIWikiPage']             = $diDir . 'SMW_DI_WikiPage.php';
	$wgAutoloadClasses['SMW\DIWikiPage']            = $diDir . 'SMW_DI_WikiPage.php'; // 1.9
	$wgAutoloadClasses['SMWDITime']                 = $diDir . 'SMW_DI_Time.php';
	$wgAutoloadClasses['SMWDIError']                = $diDir . 'SMW_DI_Error.php';
	$wgAutoloadClasses['SMWDIGeoCoord']             = $diDir . 'SMW_DI_GeoCoord.php';
	$wgAutoloadClasses['SMWContainerSemanticData']  = $diDir . 'SMW_DI_Container.php';
	$wgAutoloadClasses['SMWDIContainer']            = $diDir . 'SMW_DI_Container.php';
	$wgAutoloadClasses['SMWDISerializer']           = $diDir . 'DISerializer.php';
	$wgAutoloadClasses['SMW\DISerializer']          = $diDir . 'DISerializer.php'; // 1.9
	$wgAutoloadClasses['SMWDIConcept']              = $diDir . 'DIConcept.php';
	$wgAutoloadClasses['SMW\DIConcept']             = $diDir . 'DIConcept.php'; // 1.9

	// Datavalues
	$dvDir = $smwgIP . 'includes/datavalues/';
	$wgAutoloadClasses['SMWDataValue']              = $dvDir . 'SMW_DataValue.php';
	$wgAutoloadClasses['SMWRecordValue']            = $dvDir . 'SMW_DV_Record.php';
	$wgAutoloadClasses['SMWErrorValue']             = $dvDir . 'SMW_DV_Error.php';
	$wgAutoloadClasses['SMWStringValue']            = $dvDir . 'SMW_DV_String.php';
	$wgAutoloadClasses['SMWWikiPageValue']          = $dvDir . 'SMW_DV_WikiPage.php';
	$wgAutoloadClasses['SMW\WikiPageValue']         = $dvDir . 'SMW_DV_WikiPage.php'; // 1.9
	$wgAutoloadClasses['SMWPropertyValue']          = $dvDir . 'SMW_DV_Property.php';
	$wgAutoloadClasses['SMWURIValue']               = $dvDir . 'SMW_DV_URI.php';
	$wgAutoloadClasses['SMWTypesValue']             = $dvDir . 'SMW_DV_Types.php';
	$wgAutoloadClasses['SMWPropertyListValue']      = $dvDir . 'SMW_DV_PropertyList.php';
	$wgAutoloadClasses['SMWNumberValue']            = $dvDir . 'SMW_DV_Number.php';
	$wgAutoloadClasses['SMWTemperatureValue']       = $dvDir . 'SMW_DV_Temperature.php';
	$wgAutoloadClasses['SMWQuantityValue']          = $dvDir . 'SMW_DV_Quantity.php';
	$wgAutoloadClasses['SMWTimeValue']              = $dvDir . 'SMW_DV_Time.php';
	$wgAutoloadClasses['SMWBoolValue']              = $dvDir . 'SMW_DV_Bool.php';
	$wgAutoloadClasses['SMWConceptValue']           = $dvDir . 'SMW_DV_Concept.php';
	$wgAutoloadClasses['SMWImportValue']            = $dvDir . 'SMW_DV_Import.php';

	// Export
	$expDir = $smwgIP . 'includes/export/';
	$wgAutoloadClasses['SMWExporter']               = $expDir . 'SMW_Exporter.php';
	$wgAutoloadClasses['SMWExpData']                = $expDir . 'SMW_Exp_Data.php';
	$wgAutoloadClasses['SMWExpElement']             = $expDir . 'SMW_Exp_Element.php';
	$wgAutoloadClasses['SMWExpLiteral']             = $expDir . 'SMW_Exp_Element.php';
	$wgAutoloadClasses['SMWExpResource']            = $expDir . 'SMW_Exp_Element.php';
	$wgAutoloadClasses['SMWExpNsResource']          = $expDir . 'SMW_Exp_Element.php';
	$wgAutoloadClasses['SMWExportController']       = $expDir . 'SMW_ExportController.php';
	$wgAutoloadClasses['SMWSerializer']	            = $expDir . 'SMW_Serializer.php';
	$wgAutoloadClasses['SMWRDFXMLSerializer']       = $expDir . 'SMW_Serializer_RDFXML.php';
	$wgAutoloadClasses['SMWTurtleSerializer']       = $expDir . 'SMW_Serializer_Turtle.php';

	// Param classes
	$parDir = $smwgIP . 'includes/params/';
	$wgAutoloadClasses['SMWParamFormat']            = $parDir . 'SMW_ParamFormat.php';
	$wgAutoloadClasses['SMWParamSource']            = $parDir . 'SMW_ParamSource.php';

	// Parser hooks
	$phDir = $smwgIP . 'includes/parserhooks/';
	$wgAutoloadClasses['SMW\InfoParserFunction']    = $phDir . 'InfoParserFunction.php';
	$wgAutoloadClasses['SMW\ConceptParserFunction'] = $phDir . 'ConceptParserFunction.php';
	$wgAutoloadClasses['SMW\DeclareParserFunction'] = $phDir . 'DeclareParserFunction.php';
	$wgAutoloadClasses['SMW\SetParserFunction']     = $phDir . 'SetParserFunction.php';
	$wgAutoloadClasses['SMW\AskParserFunction']     = $phDir . 'AskParserFunction.php';
	$wgAutoloadClasses['SMW\ShowParserFunction']    = $phDir . 'ShowParserFunction.php';
	$wgAutoloadClasses['SMW\ConceptParserFunction'] = $phDir . 'ConceptParserFunction.php';
	$wgAutoloadClasses['SMW\SubobjectParserFunction']       = $phDir . 'SubobjectParserFunction.php';
	$wgAutoloadClasses['SMW\RecurringEventsParserFunction'] = $phDir . 'RecurringEventsParserFunction.php';
	$wgAutoloadClasses['SMW\DocumentationParserFunction']   = $phDir . 'DocumentationParserFunction.php';
	$wgAutoloadClasses['SMW\ParserFunctionFactory']         = $phDir . 'ParserFunctionFactory.php';

	// Query related classes
	$qeDir = $smwgIP . 'includes/query/';
	$wgAutoloadClasses['SMW\QueryData']             = $qeDir . 'QueryData.php';
	$wgAutoloadClasses['SMWQueryProcessor']         = $qeDir . 'SMW_QueryProcessor.php';
	$wgAutoloadClasses['SMWQueryParser']            = $qeDir . 'SMW_QueryParser.php';
	$wgAutoloadClasses['SMWQueryLanguage']          = $qeDir . 'SMW_QueryLanguage.php';
	$wgAutoloadClasses['SMWQuery']                  = $qeDir . 'SMW_Query.php';
	$wgAutoloadClasses['SMWPrintRequest']           = $qeDir . 'SMW_PrintRequest.php';
	$wgAutoloadClasses['SMWThingDescription']       = $qeDir . 'SMW_Description.php';
	$wgAutoloadClasses['SMWClassDescription']       = $qeDir . 'SMW_Description.php';
	$wgAutoloadClasses['SMWConceptDescription']     = $qeDir . 'SMW_Description.php';
	$wgAutoloadClasses['SMWNamespaceDescription']   = $qeDir . 'SMW_Description.php';
	$wgAutoloadClasses['SMWValueDescription']       = $qeDir . 'SMW_Description.php';
	$wgAutoloadClasses['SMWConjunction']            = $qeDir . 'SMW_Description.php';
	$wgAutoloadClasses['SMWDisjunction']            = $qeDir . 'SMW_Description.php';
	$wgAutoloadClasses['SMWSomeProperty']           = $qeDir . 'SMW_Description.php';

	// Stores & queries
	$wgAutoloadClasses['SMWSparqlDatabase']         = $smwgIP . 'includes/sparql/SMW_SparqlDatabase.php';
	$wgAutoloadClasses['SMWSparqlDatabase4Store']   = $smwgIP . 'includes/sparql/SMW_SparqlDatabase4Store.php';
	$wgAutoloadClasses['SMWSparqlDatabaseVirtuoso'] = $smwgIP . 'includes/sparql/SMW_SparqlDatabaseVirtuoso.php';
	$wgAutoloadClasses['SMWSparqlDatabaseError']    = $smwgIP . 'includes/sparql/SMW_SparqlDatabase.php';
	$wgAutoloadClasses['SMWSparqlResultWrapper']    = $smwgIP . 'includes/sparql/SMW_SparqlResultWrapper.php';
	$wgAutoloadClasses['SMWSparqlResultParser']     = $smwgIP . 'includes/sparql/SMW_SparqlResultParser.php';

	$stoDir = $smwgIP . 'includes/storage/';

	$wgAutoloadClasses['SMW\Store\PropertyStatisticsRebuilder']	= $stoDir . 'PropertyStatisticsRebuilder.php';
	$wgAutoloadClasses['SMW\Store\PropertyStatisticsStore']     = $stoDir . 'PropertyStatisticsStore.php';
	$wgAutoloadClasses['SMW\StoreFactory']			= $stoDir . 'StoreFactory.php';
	$wgAutoloadClasses['SMW\Store\Collectible']     = $stoDir . 'Collectible.php';
	$wgAutoloadClasses['SMW\Store\Collector']       = $stoDir . 'Collector.php';

	$wgAutoloadClasses['SMWQueryResult']            = $stoDir . 'SMW_QueryResult.php';
	$wgAutoloadClasses['SMWResultArray']            = $stoDir . 'SMW_ResultArray.php';
	$wgAutoloadClasses['SMWStore']                  = $stoDir . 'SMW_Store.php';
	$wgAutoloadClasses['SMW\Store']                 = $stoDir . 'SMW_Store.php';
	$wgAutoloadClasses['SMWStringCondition']        = $stoDir . 'SMW_Store.php';
	$wgAutoloadClasses['SMWRequestOptions']         = $stoDir . 'SMW_RequestOptions.php';
	$wgAutoloadClasses['SMWSparqlStore']            = $stoDir . 'SMW_SparqlStore.php';
	$wgAutoloadClasses['SMWSparqlStoreQueryEngine'] = $stoDir . 'SMW_SparqlStoreQueryEngine.php';
	$wgAutoloadClasses['SMWSQLHelpers']             = $stoDir . 'SMW_SQLHelpers.php';

	//SQLStore (since 1.8)
	$stoDirSQL = $smwgIP . 'includes/storage/SQLStore/';
	$wgAutoloadClasses['SMW\SQLStore\PropertyStatisticsTable']				= $stoDirSQL . 'PropertyStatisticsTable.php';
	$wgAutoloadClasses['SMW\SQLStore\SimplePropertyStatisticsRebuilder']	= $stoDirSQL . 'SimplePropertyStatisticsRebuilder.php';
	$wgAutoloadClasses['SMW\SQLStore\StatisticsCollector']                  = $stoDirSQL . 'StatisticsCollector.php';
	$wgAutoloadClasses['SMW\SQLStore\WantedPropertiesCollector']            = $stoDirSQL . 'WantedPropertiesCollector.php';
	$wgAutoloadClasses['SMW\SQLStore\UnusedPropertiesCollector']            = $stoDirSQL . 'UnusedPropertiesCollector.php';
	$wgAutoloadClasses['SMW\SQLStore\PropertiesCollector']                  = $stoDirSQL . 'PropertiesCollector.php';

	$wgAutoloadClasses['SMW\SQLStore\PropertyTableDefinitionBuilder']       = $stoDirSQL . 'PropertyTableDefinitionBuilder.php';

	$wgAutoloadClasses['SMWSQLStore3Table']                = $stoDirSQL . 'SMW_SQLStore3Table.php'; // Please fix me ...
	$wgAutoloadClasses['SMW\SQLStore\TableDefinition']     = $stoDirSQL . 'SMW_SQLStore3Table.php';

	$wgAutoloadClasses['SMWSQLStore3']                     = $stoDirSQL . 'SMW_SQLStore3.php';
	$wgAutoloadClasses['SMWSql3StubSemanticData']          = $stoDirSQL . 'SMW_Sql3StubSemanticData.php';
	$wgAutoloadClasses['SMWSql3SmwIds']                    = $stoDirSQL . 'SMW_Sql3SmwIds.php';
	$wgAutoloadClasses['SMWSQLStore3Readers']              = $stoDirSQL . 'SMW_SQLStore3_Readers.php';
	$wgAutoloadClasses['SMWSQLStore3QueryEngine']          = $stoDirSQL . 'SMW_SQLStore3_Queries.php';
	$wgAutoloadClasses['SMWSQLStore3Query']                = $stoDirSQL . 'SMW_SQLStore3_Queries.php';
	$wgAutoloadClasses['SMWSQLStore3Writers']              = $stoDirSQL . 'SMW_SQLStore3_Writers.php';
	$wgAutoloadClasses['SMWSQLStore3SpecialPageHandlers']  = $stoDirSQL . 'SMW_SQLStore3_SpecialPageHandlers.php';
	$wgAutoloadClasses['SMWSQLStore3SetupHandlers']        = $stoDirSQL . 'SMW_SQLStore3_SetupHandlers.php';
	$wgAutoloadClasses['SMWDataItemHandler']              = $stoDirSQL . 'SMW_DataItemHandler.php';
	$wgAutoloadClasses['SMWDIHandlerBoolean']             = $stoDirSQL . 'SMW_DIHandler_Bool.php';
	$wgAutoloadClasses['SMWDIHandlerNumber']              = $stoDirSQL . 'SMW_DIHandler_Number.php';
	$wgAutoloadClasses['SMWDIHandlerBlob']                = $stoDirSQL . 'SMW_DIHandler_Blob.php';
	$wgAutoloadClasses['SMWDIHandlerUri']                 = $stoDirSQL . 'SMW_DIHandler_URI.php';
	$wgAutoloadClasses['SMWDIHandlerWikiPage']            = $stoDirSQL . 'SMW_DIHandler_WikiPage.php';
	$wgAutoloadClasses['SMWDIHandlerTime']                = $stoDirSQL . 'SMW_DIHandler_Time.php';
	$wgAutoloadClasses['SMWDIHandlerConcept']             = $stoDirSQL . 'SMW_DIHandler_Concept.php';
	$wgAutoloadClasses['SMWDIHandlerGeoCoord']            = $stoDirSQL . 'SMW_DIHandler_GeoCoord.php';

	// Special pages and closely related helper classes
	$specDir = $smwgIP . 'includes/specials/';
	$wgAutoloadClasses['SMW\SpecialPage']               = $specDir . 'SpecialPage.php';
	$wgAutoloadClasses['SMW\SpecialSemanticStatistics'] = $specDir . 'SpecialSemanticStatistics.php';
	$wgAutoloadClasses['SMW\SpecialConcepts']           = $specDir . 'SpecialConcepts.php';
	$wgAutoloadClasses['SMW\SpecialWantedProperties']   = $specDir . 'SpecialWantedProperties.php';
	$wgAutoloadClasses['SMW\SpecialUnusedProperties']   = $specDir . 'SpecialUnusedProperties.php';
	$wgAutoloadClasses['SMW\SpecialProperties']         = $specDir . 'SpecialProperties.php';

	$wgAutoloadClasses['SMWAskPage']                    = $specDir . 'SMW_SpecialAsk.php';
	$wgAutoloadClasses['SMWQueryUIHelper']              = $specDir . 'SMW_QueryUIHelper.php';
	$wgAutoloadClasses['SMWQueryUI']                    = $specDir . 'SMW_QueryUI.php';
	$wgAutoloadClasses['SMWQueryCreatorPage']           = $specDir . 'SMW_SpecialQueryCreator.php';
	$wgAutoloadClasses['SMWQuerySpecialPage']           = $specDir . 'SMW_QuerySpecialPage.php';
	$wgAutoloadClasses['SMWSpecialBrowse']              = $specDir . 'SMW_SpecialBrowse.php';
	$wgAutoloadClasses['SMWPageProperty']               = $specDir . 'SMW_SpecialPageProperty.php';
	$wgAutoloadClasses['SMWSearchByProperty']           = $specDir . 'SMW_SpecialSearchByProperty.php';
	$wgAutoloadClasses['SMWURIResolver']                = $specDir . 'SMW_SpecialURIResolver.php';
	$wgAutoloadClasses['SMWAdmin']                      = $specDir . 'SMW_SpecialSMWAdmin.php';
	$wgAutoloadClasses['SMWSpecialOWLExport']           = $specDir . 'SMW_SpecialOWLExport.php';
	$wgAutoloadClasses['SMWSpecialTypes']               = $specDir . 'SMW_SpecialTypes.php';

	// Special pages and closely related helper classes
	$testsDir = $smwgIP . 'tests/phpunit/';
	$wgAutoloadClasses['SMW\Test\ResultPrinterTestCase']         = $testsDir . 'QueryPrinterRegistryTestCase.php';
	$wgAutoloadClasses['SMW\Test\QueryPrinterRegistryTestCase']  = $testsDir . 'QueryPrinterRegistryTestCase.php';
	$wgAutoloadClasses['SMW\Test\QueryPrinterTestCase']          = $testsDir . 'QueryPrinterTestCase.php';
	$wgAutoloadClasses['SMW\Tests\DataItemTest']                 = $testsDir . 'includes/dataitems/DataItemTest.php';
	$wgAutoloadClasses['SMW\Test\SemanticMediaWikiTestCase']     = $testsDir . 'SemanticMediaWikiTestCase.php';
	$wgAutoloadClasses['SMW\Test\ParserTestCase']                = $testsDir . 'ParserTestCase.php';
	$wgAutoloadClasses['SMW\Test\ApiTestCase']                   = $testsDir . 'ApiTestCase.php';
	$wgAutoloadClasses['SMW\Test\MockSuperUser']                 = $testsDir . 'MockSuperUser.php';
	$wgAutoloadClasses['SMW\Test\MockObjectBuilder']             = $testsDir . 'MockObjectBuilder.php';
	$wgAutoloadClasses['SMW\Test\SpecialPageTestCase']           = $testsDir . 'SpecialPageTestCase.php';
	$wgAutoloadClasses['SMW\Test\CompatibilityTestCase']         = $testsDir . 'CompatibilityTestCase.php';

	// Jobs
	$wgJobClasses['SMWUpdateJob']       = 'SMWUpdateJob';
	$wgAutoloadClasses['SMWUpdateJob']  = $smwgIP . 'includes/jobs/SMW_UpdateJob.php';
	$wgAutoloadClasses['SMW\UpdateJob']  = $smwgIP . 'includes/jobs/SMW_UpdateJob.php'; // 1.9
	$wgJobClasses['SMWRefreshJob']      = 'SMWRefreshJob';
	$wgAutoloadClasses['SMWRefreshJob'] = $smwgIP . 'includes/jobs/SMW_RefreshJob.php';

	$wgJobClasses['SMW\PropertySubjectsUpdateDispatcherJob']      = 'SMW\PropertySubjectsUpdateDispatcherJob';
	$wgAutoloadClasses['SMW\PropertySubjectsUpdateDispatcherJob'] = $smwgIP . 'includes/jobs/PropertySubjectsUpdateDispatcherJob.php';

	// Store migration job class
	$wgJobClasses['SMWMigrationJob']          = 'SMW\MigrationJob';
	$wgAutoloadClasses['SMW\MigrationJob']    = $smwgIP . 'includes/jobs/MigrationJob.php';

	// API modules
	$wgAutoloadClasses['SMW\ApiBase']    = $smwgIP . 'includes/api/ApiBase.php';
	$wgAutoloadClasses['SMW\ApiQuery']   = $smwgIP . 'includes/api/ApiQuery.php';
	$wgAutoloadClasses['SMW\ApiAsk']     = $smwgIP . 'includes/api/ApiAsk.php';
	$wgAutoloadClasses['SMW\ApiAskArgs'] = $smwgIP . 'includes/api/ApiAskArgs.php';
	$wgAutoloadClasses['SMW\ApiInfo']    = $smwgIP . 'includes/api/ApiInfo.php';

	// Maintenance scripts
	$wgAutoloadClasses['SMWSetupScript'] = $smwgIP . 'maintenance/SMW_setup.php';

	// Other extensions
	$wgAutoloadClasses['SMWPageSchemas'] = $smwgIP . 'includes/SMW_PageSchemas.php';
}

/**
 * Register all SMW special pages with MediaWiki.
 *
 * @codeCoverageIgnore
 */
function smwfRegisterSpecialPages() {
	$specials = array(
		'Ask' => array(
			'page' => 'SMWAskPage',
			'group' => 'smw_group'
		),
		'Browse' => array(
			'page' =>  'SMWSpecialBrowse',
			'group' => 'smw_group'
		),
		'PageProperty' => array(
			'page' =>  'SMWPageProperty',
			'group' => 'smw_group'
		),
		'SearchByProperty' => array(
			'page' => 'SMWSearchByProperty',
			'group' => 'smw_group'
		),
		'SMWAdmin' => array(
			'page' => 'SMWAdmin',
			'group' => 'smw_group'
		),
		'SemanticStatistics' => array(
			'page' => 'SMW\SpecialSemanticStatistics',
			'group' => 'wiki'
		),
		'Concepts' => array(
			'page' => 'SMW\SpecialConcepts',
			'group' => 'pages'
		),
		'ExportRDF' => array(
			'page' => 'SMWSpecialOWLExport',
			'group' => 'smw_group'
		),
		'Types' => array(
			'page' => 'SMWSpecialTypes',
			'group' => 'pages'
		),
		'URIResolver' => array(
			'page' => 'SMWURIResolver'
		),
		'Properties' => array(
			'page' => 'SMW\SpecialProperties',
			'group' => 'pages'
		),
		'UnusedProperties' => array(
			'page' => 'SMW\SpecialUnusedProperties',
			'group' => 'maintenance'
		),
		'WantedProperties' => array(
			'page' => 'SMW\SpecialWantedProperties',
			'group' => 'maintenance'
		),
	);

	// Register data
	foreach ( $specials as $special => $page ) {
		$GLOBALS['wgSpecialPages'][$special] = $page['page'];

		if ( isset( $page['group'] ) ) {
			$GLOBALS['wgSpecialPageGroups'][$special] = $page['group'];
		}
	}

	//	$wgSpecialPages['QueryCreator']             = 'SMWQueryCreatorPage';
	//	$wgSpecialPageGroups['QueryCreator']        = 'smw_group';
}

/**
 * Do the actual intialisation of the extension. This is just a delayed init
 * that makes sure MediaWiki is set up properly before we add our stuff.
 *
 * The main things this function does are: register all hooks, set up extension
 * credits, and init some globals that are not for configuration settings.
 *
 * @codeCoverageIgnore
 */
function smwfSetupExtension() {
	wfProfileIn( 'smwfSetupExtension (SMW)' );
	global $smwgScriptPath, $smwgMasterStore, $smwgIQRunningNumber;

	$smwgMasterStore = null;
	$smwgIQRunningNumber = 0;

	wfProfileOut( 'smwfSetupExtension (SMW)' );
	return true;
}

/**********************************************/
/***** namespace settings                 *****/
/**********************************************/

/**
 * Init the additional namespaces used by Semantic MediaWiki.
 *
 * @codeCoverageIgnore
 */
function smwfInitNamespaces() {
	global $smwgNamespaceIndex, $wgExtraNamespaces, $wgNamespaceAliases, $wgNamespacesWithSubpages, $wgLanguageCode, $smwgContLang;

	if ( !isset( $smwgNamespaceIndex ) ) {
		$smwgNamespaceIndex = 100;
	}
	// 100 and 101 used to be occupied by SMW's now obsolete namespaces "Relation" and "Relation_Talk"
	define( 'SMW_NS_PROPERTY',       $smwgNamespaceIndex + 2 );
	define( 'SMW_NS_PROPERTY_TALK',  $smwgNamespaceIndex + 3 );
	define( 'SMW_NS_TYPE',           $smwgNamespaceIndex + 4 );
	define( 'SMW_NS_TYPE_TALK',      $smwgNamespaceIndex + 5 );
	// 106 and 107 are occupied by the Semantic Forms, we define them here to offer some (easy but useful) support to SF
	define( 'SF_NS_FORM',            $smwgNamespaceIndex + 6 );
	define( 'SF_NS_FORM_TALK',       $smwgNamespaceIndex + 7 );
	define( 'SMW_NS_CONCEPT',        $smwgNamespaceIndex + 8 );
	define( 'SMW_NS_CONCEPT_TALK',   $smwgNamespaceIndex + 9 );

	smwfInitContentLanguage( $wgLanguageCode );

	// Register namespace identifiers
	if ( !is_array( $wgExtraNamespaces ) ) {
		$wgExtraNamespaces = array();
	}
	$wgExtraNamespaces = $wgExtraNamespaces + $smwgContLang->getNamespaces();
	$wgNamespaceAliases = $wgNamespaceAliases + $smwgContLang->getNamespaceAliases();

	// Support subpages only for talk pages by default
	$wgNamespacesWithSubpages = $wgNamespacesWithSubpages + array(
				SMW_NS_PROPERTY_TALK => true,
				SMW_NS_TYPE_TALK => true
	);

	// not modified for Semantic MediaWiki
	/* $wgNamespacesToBeSearchedDefault = array(
		NS_MAIN           => true,
		);
	*/
}

/**********************************************/
/***** language settings                  *****/
/**********************************************/

/**
 * Initialise a global language object for content language. This must happen
 * early on, even before user language is known, to determine labels for
 * additional namespaces. In contrast, messages can be initialised much later
 * when they are actually needed.
 *
 * @codeCoverageIgnore
 */
function smwfInitContentLanguage( $langcode ) {
	global $smwgIP, $smwgContLang;

	if ( !empty( $smwgContLang ) ) {
		return;
	}
	wfProfileIn( 'smwfInitContentLanguage (SMW)' );

	$smwContLangFile = 'SMW_Language' . str_replace( '-', '_', ucfirst( $langcode ) );
	$smwContLangClass = 'SMWLanguage' . str_replace( '-', '_', ucfirst( $langcode ) );

	if ( file_exists( $smwgIP . 'languages/' . $smwContLangFile . '.php' ) ) {
		include_once( $smwgIP . 'languages/' . $smwContLangFile . '.php' );
	}

	// Fallback if language not supported.
	if ( !class_exists( $smwContLangClass ) ) {
		include_once( $smwgIP . 'languages/SMW_LanguageEn.php' );
		$smwContLangClass = 'SMWLanguageEn';
	}

	$smwgContLang = new $smwContLangClass();

	wfProfileOut( 'smwfInitContentLanguage (SMW)' );
}
