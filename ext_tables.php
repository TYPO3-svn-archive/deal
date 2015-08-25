<?php

if ( !defined( 'TYPO3_MODE' ) )
{
  die( 'Access denied.' );
}



/* * ************************************************************************
 * INDEX
 *
 * Set TYPO3 version
 * Configuration by the extension manager
 *    Localization support
 * Enables the Include Static Templates
 * Plugin Configuration
 * Add pagetree icons
 * Methods for backend workflows
 * TCA for tx_deal_ebaycategories
 * TCA for tx_deal_ebayshippingservicecode
 * TCA for tx_quickshop_products
 * ************************************************************************ */

//
// Set TYPO3 version
// Set TYPO3 version as integer (sample: 4.7.7 -> 4007007)
list( $main, $sub, $bugfix ) = explode( '.', TYPO3_version );
$version = ( ( int ) $main ) * 1000000;
$version = $version + ( ( int ) $sub ) * 1000;
$version = $version + ( ( int ) $bugfix ) * 1;
$typo3Version = $version;
// Set TYPO3 version as integer (sample: 4.7.7 -> 4007007)

if ( $typo3Version < 3000000 )
{
  $prompt = '<h1>ERROR</h1>
    <h2>Unproper TYPO3 version</h2>
    <ul>
      <li>
        TYPO3 version is smaller than 3.0.0
      </li>
      <li>
        constant TYPO3_version: ' . TYPO3_version . '
      </li>
      <li>
        integer $this->typo3Version: ' . ( int ) $this->typo3Version . '
      </li>
    </ul>
      ';
  die( $prompt );
}
// Set TYPO3 version
//
////////////////////////////////////////////////////////////////////////////
//
// Configuration by the extension manager

$confArr = unserialize( $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'EXT' ][ 'extConf' ][ 'deal' ] );

// Language for labels of static templates and page tsConfig
$beLanguage = $confArr[ 'beLanguage' ];
switch ( $beLanguage )
{
  case( 'German'):
    $beLanguage = 'de';
    break;
  default:
    $beLanguage = 'default';
}
// Language for labels of static templates and page tsConfig
// Configuration by the extension manager
////////////////////////////////////////////////////////////////////////////
//
// Enables the Include Static Templates
// Case $beLanguage
switch ( true )
{
  case( $beLanguage == 'de' ):
    // German
    t3lib_extMgm::addStaticFile( $_EXTKEY, 'Configuration/TypoScript/', 'Deal [1]' );
    t3lib_extMgm::addStaticFile( $_EXTKEY, 'Configuration/TypoScript/Marketplace/ebay/dev/GetCategories/', 'Deal [1] [DEV] ebay Kategorien' );
    t3lib_extMgm::addStaticFile( $_EXTKEY, 'Configuration/TypoScript/Marketplace/ebay/dev/GettingStarted_PHP_XML_XML/', 'Deal [1] [DEV] ebay 3 Artikel' );
//    switch( true )
//    {
//      case( $typo3Version < 4007000 ):
//        t3lib_extMgm::addStaticFile($_EXTKEY,'static/typo3/4.6/', '+Flip it!: Basis fuer TYPO3 < 4.7 (einbinden!)');
//        break;
//      default:
//        t3lib_extMgm::addStaticFile($_EXTKEY,'static/typo3/4.6/', '+Flip it!: Basis fuer TYPO3 < 4.7 (NICHT einbinden!)');
//        break;
//    }
    break;
  default:
    // English
    t3lib_extMgm::addStaticFile( $_EXTKEY, 'Configuration/TypoScript/', 'Deal [1]' );
    t3lib_extMgm::addStaticFile( $_EXTKEY, 'Configuration/TypoScript/Marketplace/ebay/dev/GetCategories/', 'Deal [1] [DEV] ebay categories' );
    t3lib_extMgm::addStaticFile( $_EXTKEY, 'Configuration/TypoScript/Marketplace/ebay/dev/GettingStarted_PHP_XML_XML/', 'Deal [1] [DEV] ebay 3 items' );
//    switch( true )
//    {
//      case( $typo3Version < 4007000 ):
//        t3lib_extMgm::addStaticFile($_EXTKEY,'static/typo3/4.6/', '+Flip it!: Basis for TYPO3 < 4.7 (obligate!)');
//        break;
//      default:
//        t3lib_extMgm::addStaticFile($_EXTKEY,'static/typo3/4.6/', '+Flip it!: Basis for TYPO3 < 4.7 (don\'t use it!)');
//        break;
//    }
    break;
}
// Case $beLanguage
// Enables the Include Static Templates
//

/**
 * Include Plugins
 */
// Pi1
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        $_EXTKEY
        , 'Pi1'
        , 'LLL:EXT:deal/Resources/Private/Language/Marketplaces/Immo24/locallang_mod.xlf:pluginPi1Title'
        , t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Images/Marketplaces/Immo24/ext_icon.gif'
);

/**
 * Include Flexform
 */
// Pi1
$TCA[ 'tt_content' ][ 'types' ][ 'list' ][ 'subtypes_addlist' ][ 'deal_pi1' ] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        'deal_pi1'
        , 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/FlexformPi1.xml'
);

/**
 * Include UserFuncs
 */
if ( TYPO3_MODE === 'BE' )
{
  $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath( $_EXTKEY );
  // ContentElementWizard for Pi1
  $TBE_MODULES_EXT[ 'xMOD_db_new_content_el' ][ 'addElClasses' ][ 'Netzmacher\Deal\Utility\Hook\ContentElementWizard' ] = $extPath . 'Classes/Utility/Hook/ContentElementWizard.php';
}

////////////////////////////////////////////////////////////////////////////
//
// Plugin Configuration
t3lib_div::loadTCA( 'tt_content' );

$TCA[ 'tt_content' ][ 'types' ][ 'list' ][ 'subtypes_excludelist' ][ $_EXTKEY . '_piMarketplacesEbaySamplesPhpGettingstarted' ] = 'layout,select_key,recursive,pages';
//$TCA['tt_content']['types']['list']['subtypes_addlist'][ $_EXTKEY . '_piMarketplacesEbaySamplesPhpGettingstarted' ]      = 'pi_flexform';
// Plugin ebay samples get categories
t3lib_extMgm::addPlugin( array(
  'LLL:EXT:deal/plugins/marketplaces/ebay/dev/GetCategories/locallang.xml:list_type_piMarketplacesEbaySamplesPhpGetCategories',
  $_EXTKEY . '_piMarketplacesEbaySamplesPhpGetCategories',
  t3lib_extMgm::extRelPath( $_EXTKEY ) . 'ext_icon.gif'
        ), 'list_type' );
t3lib_extMgm::addPiFlexFormValue( $_EXTKEY . '_piMarketplacesEbaySamplesPhpGetCategories', 'FILE:EXT:' . $_EXTKEY . '/plugins/marketplaces/ebay/dev/GetCategories/flexform.xml' );
// Plugin ebay samples get categories
// Plugin ebay samples getting started
t3lib_extMgm::addPlugin( array(
  'LLL:EXT:deal/plugins/marketplaces/ebay/dev/GettingStarted_PHP_XML_XML/locallang.xml:list_type_piMarketplacesEbaySamplesPhpGettingstarted',
  $_EXTKEY . '_piMarketplacesEbaySamplesPhpGettingstarted',
  t3lib_extMgm::extRelPath( $_EXTKEY ) . 'ext_icon.gif'
        ), 'list_type' );
t3lib_extMgm::addPiFlexFormValue( $_EXTKEY . '_piMarketplacesEbaySamplesPhpGettingstarted', 'FILE:EXT:' . $_EXTKEY . '/plugins/marketplaces/ebay/dev/GettingStarted_PHP_XML_XML/flexform.xml' );
// Plugin ebay samples getting started
// Plugin Configuration
//
////////////////////////////////////////////////////////////////////////////
//
// Add pagetree icons
// Case $beLanguage
switch ( true )
{
  case( $beLanguage == 'de' ):
    // German
    $TCA[ 'pages' ][ 'columns' ][ 'module' ][ 'config' ][ 'items' ][] = array( 'Deal!', 'deal', t3lib_extMgm::extRelPath( $_EXTKEY ) . 'ext_icon.gif' );
    $TCA[ 'pages' ][ 'columns' ][ 'module' ][ 'config' ][ 'items' ][] = array(
      'Deal! ebay', 'dealebay', t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Images/Marketplaces/ebay/ext_icon.gif'
    );
    $TCA[ 'pages' ][ 'columns' ][ 'module' ][ 'config' ][ 'items' ][] = array(
      'Deal! immobilienscout24', 'dealimmo24', t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Images/Marketplaces/Immo24/ext_icon.gif'
    );
    break;
  default:
    // English
    $TCA[ 'pages' ][ 'columns' ][ 'module' ][ 'config' ][ 'items' ][] = array( 'Deal!', 'deal', t3lib_extMgm::extRelPath( $_EXTKEY ) . 'ext_icon.gif' );
    $TCA[ 'pages' ][ 'columns' ][ 'module' ][ 'config' ][ 'items' ][] = array(
      'Deal! ebay', 'dealebay', t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Images/Marketplaces/ebay/ext_icon.gif'
    );
    $TCA[ 'pages' ][ 'columns' ][ 'module' ][ 'config' ][ 'items' ][] = array(
      'Deal! immobilienscout24', 'dealimmo24', t3lib_extMgm::extRelPath( $_EXTKEY ) . 'Resources/Public/Images/Marketplaces/Immo24/ext_icon.gif'
    );
}
// Case $beLanguage

t3lib_SpriteManager::addTcaTypeIcon( 'pages', 'contains-deal', '../typo3conf/ext/deal/ext_icon.gif' );
t3lib_SpriteManager::addTcaTypeIcon( 'pages', 'contains-dealebay', '../typo3conf/ext/deal/Resources/Public/Images/Marketplaces/ebay/ext_icon.gif' );
t3lib_SpriteManager::addTcaTypeIcon( 'pages', 'contains-dealimmo24', '../typo3conf/ext/deal/Resources/Public/Images/Marketplaces/Immo24/ext_icon.gif' );
// Add pagetree icons
///////////////////////////////////////////////////////////
//
// Methods for backend workflows
//require_once(t3lib_extMgm::extPath($_EXTKEY).'lib/flexform/class.tx_deal_flexform.php');
require_once(t3lib_extMgm::extPath( $_EXTKEY ) . 'lib/userfunc/class.tx_deal_userfunc.php');
// Methods for backend workflows
//
///////////////////////////////////////////////////////////
//
// TCA for tx_quickshop_products

require_once(t3lib_extMgm::extPath( $_EXTKEY ) . 'Configuration/TCA/tx_quickshop_products/Marketplace/ebay/ext_tables.php');
// TCA for tx_quickshop_products