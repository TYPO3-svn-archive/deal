<?php

if (!defined('TYPO3_MODE'))
{
  die('Access denied.');
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
list( $main, $sub, $bugfix ) = explode('.', TYPO3_version);
$version = ( (int) $main ) * 1000000;
$version = $version + ( (int) $sub ) * 1000;
$version = $version + ( (int) $bugfix ) * 1;
$typo3Version = $version;
// Set TYPO3 version as integer (sample: 4.7.7 -> 4007007)

if ($typo3Version < 3000000)
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
        integer $this->typo3Version: ' . (int) $this->typo3Version . '
      </li>
    </ul>
      ';
  die($prompt);
}
// Set TYPO3 version
////////////////////////////////////////////////////////////////////////////
//
// Configuration by the extension manager

$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['deal']);

// Language for labels of static templates and page tsConfig
$beLanguage = $confArr['beLanguage'];
switch ($beLanguage)
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
switch (true)
{
  case( $beLanguage == 'de' ):
    // German
    t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'Deal [1] 5x');
    t3lib_extMgm::addStaticFile($_EXTKEY, 'static/marketplaces/ebay/samples/php/GetCategories/', 'Deal [dev] [1] Samples - ebay - Get Categories');
    t3lib_extMgm::addStaticFile($_EXTKEY, 'static/marketplaces/ebay/samples/php/GettingStarted_PHP_XML_XML/', 'Deal [dev] [1] Samples - ebay - Getting Started');
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
    t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'Deal [1]');
    t3lib_extMgm::addStaticFile($_EXTKEY, 'static/marketplaces/ebay/samples/php/GetCategories/', 'Deal [dev] [1] Samples - ebay - Get Categories');
    t3lib_extMgm::addStaticFile($_EXTKEY, 'static/marketplaces/ebay/samples/php/GettingStarted_PHP_XML_XML/', 'Deal [dev] [1] Samples - ebay - Getting Started');
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
////////////////////////////////////////////////////////////////////////////
//
// Plugin Configuration

t3lib_div::loadTCA('tt_content');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_piMarketplacesEbaySamplesPhpGettingstarted'] = 'layout,select_key,recursive,pages';
//$TCA['tt_content']['types']['list']['subtypes_addlist'][ $_EXTKEY . '_piMarketplacesEbaySamplesPhpGettingstarted' ]      = 'pi_flexform';
// Plugin ebay samples get categories
t3lib_extMgm::addPlugin(array(
  'LLL:EXT:deal/plugins/marketplaces/ebay/samples/php/GetCategories/locallang.xml:list_type_piMarketplacesEbaySamplesPhpGetCategories',
  $_EXTKEY . '_piMarketplacesEbaySamplesPhpGetCategories',
  t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
        ), 'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_piMarketplacesEbaySamplesPhpGetCategories', 'FILE:EXT:' . $_EXTKEY . '/plugins/marketplaces/ebay/samples/php/GetCategories/flexform.xml');
// Plugin ebay samples get categories
// Plugin ebay samples getting started
t3lib_extMgm::addPlugin(array(
  'LLL:EXT:deal/plugins/marketplaces/ebay/samples/php/GettingStarted_PHP_XML_XML/locallang.xml:list_type_piMarketplacesEbaySamplesPhpGettingstarted',
  $_EXTKEY . '_piMarketplacesEbaySamplesPhpGettingstarted',
  t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
        ), 'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_piMarketplacesEbaySamplesPhpGettingstarted', 'FILE:EXT:' . $_EXTKEY . '/plugins/marketplaces/ebay/samples/php/GettingStarted_PHP_XML_XML/flexform.xml');
// Plugin ebay samples getting started
// Plugin Configuration
////////////////////////////////////////////////////////////////////////////
//
// Add pagetree icons
// Case $beLanguage
switch (true)
{
  case( $beLanguage == 'de' ):
    // German
    $TCA['pages']['columns']['module']['config']['items'][] = array('Deal', 'deal', t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif');
    break;
  default:
    // English
    array('Deal', 'deal', t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif');
}
// Case $beLanguage

t3lib_SpriteManager::addTcaTypeIcon('pages', 'contains-deal', '../typo3conf/ext/deal/ext_icon.gif');
// Add pagetree icons
///////////////////////////////////////////////////////////
//
// Methods for backend workflows
//require_once(t3lib_extMgm::extPath($_EXTKEY).'lib/flexform/class.tx_deal_flexform.php');
require_once(t3lib_extMgm::extPath($_EXTKEY) . 'lib/userfunc/class.tx_deal_userfunc.php');
// Methods for backend workflows
///////////////////////////////////////////////////////////
//
// TCA for tx_deal_ebaycategories

$TCA['tx_deal_ebaycategories'] = array(
  'ctrl' => array(
    'title' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_ebaycategories',
    'label' => 'title',
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'cruser_id' => 'cruser_id',
    'default_sortby' => 'ORDER BY title',
    'delete' => 'deleted',
    'enablecolumns' => array(
      'disabled' => 'hidden',
    ),
    'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
    'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icons/marketplaces/ebay/ext_icon.gif',
    'searchFields' => 'title',
    'treeParentField' => 'uid_parent',
  ),
);
// Categories
///////////////////////////////////////////////////////////
//
// TCA for tx_deal_ebayshippingservicecode

$TCA['tx_deal_ebayshippingservicecode'] = array(
  'ctrl' => array(
    'title' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_ebayshippingservicecode',
    'label' => 'title',
    'label_alt' => 'code',
    'label_alt_force' => true,
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'cruser_id' => 'cruser_id',
    'default_sortby' => 'ORDER BY title',
    'delete' => 'deleted',
    'enablecolumns' => array(
      'disabled' => 'hidden',
    ),
    'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
    'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icons/marketplaces/ebay/ext_icon.gif',
    'searchFields' => 'title,key',
  ),
);
// Categories
///////////////////////////////////////////////////////////
//
// TCA for tx_quickshop_products

require_once(t3lib_extMgm::extPath($_EXTKEY) . 'ext_tables/tx_quickshop_products/marketplaces/ebay/ext_tables.php');
// TCA for tx_quickshop_products
?>