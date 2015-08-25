<?php

////////////////////////////////////////////////////
//
// INDEX
//
// Plugins
// SC_OPTIONS



if ( !defined( 'TYPO3_MODE' ) )
{
  die( 'Access denied.' );
}




Tx_Extbase_Utility_Extension::configurePlugin(
        'Netzmacher.' . $_EXTKEY, 'Pi1', array(
  'Immo24' => 'plugin, submit'
        ), array(
  'Immo24' => 'plugin, submit'
        )
);

////////////////////////////////////////////////////
//
// Plugins: Extending TypoScript from static template uid=43 to set up userdefined tag

$cached = false;
t3lib_extMgm::addPItoST43(
        $_EXTKEY
        , 'plugins/marketplaces/ebay/dev/GetCategories/class.tx_deal_piMarketplacesEbaySamplesPhpGetCategories.php'
        , '_piMarketplacesEbaySamplesPhpGetCategories'
        , 'list_type'
        , $cached
);
t3lib_extMgm::addPItoST43(
        $_EXTKEY
        , 'plugins/marketplaces/ebay/dev/GettingStarted_PHP_XML_XML/class.tx_deal_piMarketplacesEbaySamplesPhpGettingstarted.php'
        , '_piMarketplacesEbaySamplesPhpGettingstarted'
        , 'list_type'
        , $cached
);
// Plugins: Extending TypoScript from static template uid=43 to set up userdefined tag
////////////////////////////////////////////////////
//
// SC_OPTIONS

$GLOBALS [ 'TYPO3_CONF_VARS' ][ 'SC_OPTIONS' ][ 't3lib/class.t3lib_tcemain.php' ][ 'processDatamapClass' ][] = 'EXT:deal/lib/tcemainprocdm/class.tx_deal_tcemainprocdmbeforestart.php:tx_deal_tcemainprocdmbeforestart';
//$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:deal/lib/tcemainprocdm/class.tx_deal_tcemainprocdmfieldarray.php:tx_deal_tcemainprocdmfieldarray';
//$GLOBALS[ 'TYPO3_CONF_VARS' ][ 'SC_OPTIONS' ][ 'scheduler' ][ 'tasks' ][ 'Netzmacher\\Deal\\Scheduler\\Immo24CleanupExecute' ] = array(
//  'extension' => $_EXTKEY,
//  'title' => 'LLL:EXT:' . $_EXTKEY . '/Classes/Scheduler/locallang.xlf:immo24.export.name',
//  'description' => 'LLL:EXT:' . $_EXTKEY . '/Classes/Scheduler/locallang.xlf:immo24.cleanup.description',
//  'additionalFields' => 'Netzmacher\\Deal\\Scheduler\\Immo24CleanupFieldProvider'
//);
$GLOBALS[ 'TYPO3_CONF_VARS' ][ 'SC_OPTIONS' ][ 'scheduler' ][ 'tasks' ][ 'Netzmacher\\Deal\\Scheduler\\Immo24TaskExecute' ] = array(
  'extension' => $_EXTKEY,
  'title' => 'LLL:EXT:' . $_EXTKEY . '/Classes/Scheduler/locallang.xlf:immo24.export.name',
  'description' => 'LLL:EXT:' . $_EXTKEY . '/Classes/Scheduler/locallang.xlf:immo24.export.description',
  'additionalFields' => 'Netzmacher\\Deal\\Scheduler\\Immo24TaskFieldProvider'
);
$GLOBALS[ 'TYPO3_CONF_VARS' ][ 'SC_OPTIONS' ][ 'scheduler' ][ 'tasks' ][ 'Netzmacher\\Deal\\Scheduler\\TestTaskExecute' ] = array(
  'extension' => $_EXTKEY,
  'title' => 'LLL:EXT:' . $_EXTKEY . '/Classes/Scheduler/locallang.xlf:test.name',
  'description' => 'LLL:EXT:' . $_EXTKEY . '/Classes/Scheduler/locallang.xlf:test.description',
  'additionalFields' => 'Netzmacher\\Deal\\Scheduler\\TestFieldProvider'
);
// SC_OPTIONS