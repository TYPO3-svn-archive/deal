<?php

  ////////////////////////////////////////////////////
  //
  // INDEX
  //
  // Plugins
  // SC_OPTIONS



if( ! defined ( 'TYPO3_MODE' ) )
{
  die ( 'Access denied.' );
}



  ////////////////////////////////////////////////////
  //
  // Plugins: Extending TypoScript from static template uid=43 to set up userdefined tag

$cached = false;
t3lib_extMgm::addPItoST43( $_EXTKEY, 'plugins/marketplaces/ebay/dev/GetCategories/class.tx_deal_piMarketplacesEbaySamplesPhpGetCategories.php', '_piMarketplacesEbaySamplesPhpGetCategories', 'list_type', $cached );
t3lib_extMgm::addPItoST43( $_EXTKEY, 'plugins/marketplaces/ebay/dev/GettingStarted_PHP_XML_XML/class.tx_deal_piMarketplacesEbaySamplesPhpGettingstarted.php', '_piMarketplacesEbaySamplesPhpGettingstarted', 'list_type', $cached );
  // Plugins: Extending TypoScript from static template uid=43 to set up userdefined tag



  ////////////////////////////////////////////////////
  //
  // SC_OPTIONS

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:deal/lib/tcemainprocdm/class.tx_deal_tcemainprocdmbeforestart.php:tx_deal_tcemainprocdmbeforestart';
//$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:deal/lib/tcemainprocdm/class.tx_deal_tcemainprocdmfieldarray.php:tx_deal_tcemainprocdmfieldarray';
  // SC_OPTIONS

?>