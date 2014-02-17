<?php

if( ! defined ( 'TYPO3_MODE' ) )
{
  die ( 'Access denied.' );
}

$cached = false;
//t3lib_extMgm::addPItoST43( $_EXTKEY, 'pi1/class.tx_caddy_pi1.php', '_pi1', 'list_type', $cached );
t3lib_extMgm::addPItoST43( $_EXTKEY, 'plugins/marketplaces/ebay/samples/php/GettingStarted_PHP_NV_XML/class.tx_deal_piMarketplacesEbaySamplesPhpGettingstarted.php', '_piMarketplacesEbaySamplesPhpGettingstarted', 'list_type', $cached );

?>