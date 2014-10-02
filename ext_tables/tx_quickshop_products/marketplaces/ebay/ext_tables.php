<?php

if ( !defined( 'TYPO3_MODE' ) )
{
  die( 'Access denied.' );
}



/* * ************************************************************************
 *
 * Index
 *
 * constants
 * TCA load
 * TCA ctrl
 * TCA columns
 * TCA interface
 * TCA palettes
 * TCA types
 * ************************************************************************ */

// Configuration by the extension manager
$confArr = unserialize( $GLOBALS[ 'TYPO3_CONF_VARS' ][ 'EXT' ][ 'extConf' ][ 'deal' ] );

// values of the ebay marketplace
$ebayMarketplace = $confArr[ 'ebayMarketplace' ];
if ( empty( $ebayMarketplace ) )
{
  $ebayMarketplace = 'US - EBAY-US/USD (0)';
}
list( $country, $ebayCode ) = explode( ' - ', $ebayMarketplace );
list( $globalId, $currencyAndSiteid) = explode( '/', $ebayCode );
list( $currency, $siteId) = explode( ' ', $currencyAndSiteid );
$ebayMarketplaceCountry = $country;
$ebayMarketplaceCurrency = $currency;
$ebayMarketplaceGlobalId = $globalId;
$ebayMarketplaceSiteId = trim( $siteId, '()' );
// values of the ebay marketplace

$SiteID = sprintf( '%03d', $ebayMarketplaceSiteId );
$tx_deal_ebaycategories = 'tx_deal_ebaycategories_' . $SiteID;
$tx_deal_ebayshippingservicecode = 'tx_deal_ebayshippingservicecode_' . $SiteID;
$tx_quickshop_products_mm_tx_deal_ebaycategories = 'tx_quickshop_products_mm_tx_deal_ebaycategories_' . $SiteID;
$tx_quickshop_products_mm_tx_deal_ebayshippingservicecode = 'tx_quickshop_products_mm_tx_deal_ebayshippingservicecode_' . $SiteID;
t3lib_div::loadTCA( $tx_deal_ebaycategories );
if ( empty( $TCA[ $tx_deal_ebaycategories ] ) )
{
  $tx_deal_ebaycategories = 'tx_deal_ebaycategories_000';
  $tx_deal_ebayshippingservicecode = 'tx_deal_ebayshippingservicecode_000';
  $tx_quickshop_products_mm_tx_deal_ebaycategories = 'tx_quickshop_products_mm_tx_deal_ebaycategories_000';
  $tx_quickshop_products_mm_tx_deal_ebayshippingservicecode = 'tx_quickshop_products_mm_tx_deal_ebayshippingservicecode_000';
}

// constants
$int_div_position = 6;    // ..., 5. div[Images], 6. div[deal], 7. div[controlling], ...
// TCA load
t3lib_div::loadTCA( 'tx_quickshop_products' );

// TCA ctrl
$TCA[ 'tx_quickshop_products' ][ 'ctrl' ][ 'tx_deal' ] = array(
  'marketplaces' => array(
    'ebay' => array(
      'environment' => array(
        'key' => $confArr[ 'ebayEnvironment' ], // sandbox, production
        'production' => array(
          'token' => $confArr[ 'ebayProductionToken' ]  // obligate for production environment
        ),
        'sandbox' => array(
          'token' => $confArr[ 'ebaySandboxToken' ]     // obligate for sandbox environment
        )
      ),
      'fields' => array(
        'description' => array(
          'datasheet' => 'datasheet', // optional (must be a TYPO3 table)
          'description' => 'description', // obligate
          'short' => 'short', // optional
        ),
        'ean' => 'ean', // optional
        'filter' => array(
          'category' => 'category', // optional
          'dimension' => 'dimension', // optional
          'material' => 'material', // optional
        ),
        'gross' => 'price', // obligate
        'pictures' => 'image', // optional
        'sku' => 'sku', // obligate: sku or uid
        'title' => 'title', // obligate
      ),
      'manufacturer' => array(
        'company' => $confArr[ 'manufacturerCompany' ]
      ),
      'marketplace' => $confArr[ 'ebayMarketplace' ],
      'paypal' => array(
        'production' => array(
          'email' => $confArr[ 'paypalProductionEmail' ]  // optional
        ),
        'sandbox' => array(
          'email' => $confArr[ 'paypalSandboxEmail' ]  // optional
        )
      ),
    ),
  )
);
// TCA ctrl
// TCA columns
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayaction' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayaction',
  'config' => array(
    'type' => 'select',
    'items' => array(
//      array(
//        '',
//        'donothing',
//      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayaction_item_enableupdate',
        'enableupdate',
      ),
//      array(
//        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayaction_item_offeragain',
//        'offeragain',
//      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayaction_item_delete',
        'delete',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayaction_item_disable',
        'disable',
      ),
    ),
    'default' => 'enableupdate'
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebaycategoryid' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaycategoryid',
  'config' => array(
    'type' => 'select',
    'size' => 1,
    'minitems' => 0,
    'maxitems' => 2,
    'foreign_table' => $tx_deal_ebaycategories,
//    'foreign_table_where' => 'AND tx_deal_ebaycategories.pid=###CURRENT_PID### ORDER BY tx_deal_ebaycategories.uid',
    'form_type' => 'user',
    'userFunc' => 'tx_cpstcatree->getTree',
    'treeView' => 1,
    'expandable' => 1,
    'expandFirst' => 0,
    'expandAll' => 0,
    'MM' => $tx_quickshop_products_mm_tx_deal_ebaycategories,
  )
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayconditionid' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayconditionid',
  'config' => array(
    'type' => 'select',
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayconditionid_item_1000',
        '1000',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayconditionid_item_1500',
        '1500',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayconditionid_item_2000',
        '2000',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayconditionid_item_2500',
        '2500',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayconditionid_item_3000',
        '3000',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayconditionid_item_7000',
        '7000',
      ),
    ),
    'default' => '1000'
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebaydispatchtimemax' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax',
  'config' => array(
    'type' => 'select',
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax_item_atonce',
        '0',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax_item_day_1',
        '1',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax_item_days_2',
        '2',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax_item_days_3',
        '3',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax_item_days_4',
        '4',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax_item_week_1',
        '5',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax_item_weeks_2',
        '10',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax_item_weeks_3',
        '15',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax_item_weeks_4',
        '20',
      ),
    ),
    'default' => '2'
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayexternalLinks' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayexternalLinks',
  'config' => array(
    'type' => 'user',
    'userFunc' => 'tx_deal_userfunc->promptExternalLinks',
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayitemid' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayitemid',
  'config' => array(
    'type' => 'input',
    'size' => '30',
    'readOnly' => '1',
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayitemstatus' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayitemstatus',
  'config' => array(
    'type' => 'input',
    'size' => '90',
    'pass_content' => true,
    'readOnly' => '1',
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebaylistingduration' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration',
  'config' => array(
    'type' => 'select',
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_1',
        'Days_1',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_3',
        'Days_3',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_5',
        'Days_5',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_7',
        'Days_7',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_10',
        'Days_10',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_14',
        'Days_14',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_21',
        'Days_21',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_30',
        'Days_30',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_60',
        'Days_60',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_90',
        'Days_90',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_days_120',
        'Days_120',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration_gtc',
        'GTC',
      ),
    ),
    'default' => 'Days_30',
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebaylocation' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylocation',
  'config' => array(
    'type' => 'input',
    'size' => '30',
    'max' => '60',
    // #i0015, #i0016, 141002, dwildt, 1+, 1-
    'eval' => 'required,trim',
  //'eval' => 'trim',
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebaymode' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaymode',
  'config' => array(
    'type' => 'select',
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaymode_item_off',
        'off',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaymode_item_live',
        'live',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaymode_item_test',
        'test',
      ),
    ),
    'default' => 'off',
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayquantity' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayquantity',
  'config' => array(
    'type' => 'input',
    'size' => '5',
    'max' => '5',
    'eval' => 'int',
    'default' => 1
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebaypaymentmethods' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaypaymentmethods',
  'config' => array(
    'type' => 'select',
    'size' => 3,
    // #i0015, #i0016, 141002, dwildt, 1+, 1-
    'minitems' => 1,
    //'minitems' => 0,
    'maxitems' => 3,
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaypaymentmethods_item_cashonpickup',
        'CashOnPickup',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaypaymentmethods_item_paypal',
        'PayPal',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaypaymentmethods_item_paymentseedescription',
        'PaymentSeeDescription',
      ),
    ),
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebaypaymentmethodsdescription' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaypaymentmethodsdescription',
  'config' => array(
    'type' => 'text',
    'cols' => '20',
    'rows' => '3',
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebaylog' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylog',
  'config' => array(
    'type' => 'text',
    'cols' => '50',
    'rows' => '10',
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayreturnsacceptoption' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayreturnsacceptoption',
  'config' => array(
    'type' => 'select',
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayreturnsacceptoption_item_returnsaccepted',
        'ReturnsAccepted',
      ),
    ),
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayreturnpolicydescription' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayreturnpolicydescription',
  'config' => array(
    'type' => 'text',
    'cols' => '30',
    'rows' => '5',
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayshippingserviceadditionalcosts' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayshippingserviceadditionalcosts',
  'config' => array(
    'type' => 'input',
    'size' => '10',
    'max' => '10',
    // #i0015, #i0016, 141002, dwildt, 1+, 1-
    'eval' => 'required,double2,nospace',
    //'eval' => 'double2,nospace',
    'default' => '0.00',
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayshippingservicecode' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayshippingservicecode',
  'config' => array(
    'type' => 'select',
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayshippingservicecode_item_null',
        null,
      ),
    ),
    'size' => 1,
    'minitems' => 0,
    'maxitems' => 1,
    'foreign_table' => $tx_deal_ebayshippingservicecode,
    //'foreign_table_where' => 'AND tx_deal_ebayshippingservicecode.pid=###CURRENT_PID### ORDER BY tx_deal_ebayshippingservicecode.uid',
    'foreign_table_where' => 'ORDER BY ' . $tx_deal_ebayshippingservicecode . '.title',
    'MM' => $tx_quickshop_products_mm_tx_deal_ebayshippingservicecode,
  ),
);
$TCA[ 'tx_quickshop_products' ][ 'columns' ][ 'tx_deal_ebayshippingservicecosts' ] = array(
  'exclude' => 1,
  'label' => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayshippingservicecosts',
  'config' => array(
    'type' => 'input',
    'size' => '10',
    'max' => '10',
    // #i0015, #i0016, 141002, dwildt, 1+, 1-
    'eval' => 'required,double2,nospace',
  //'eval' => 'double2,nospace',
  ),
);
// TCA columns
// TCA interface
$showRecordFieldList = $TCA[ 'tx_quickshop_products' ][ 'interface' ][ 'showRecordFieldList' ];
$showRecordFieldList = $showRecordFieldList
        . 'tx_deal_ebayaction,'
        . 'tx_deal_ebaycategoryid,'
        . 'tx_deal_ebayconditionid,'
        . 'tx_deal_ebaydispatchtimemax,'
        . 'tx_deal_ebayexternalLinks,'
        . 'tx_deal_ebayitemid,'
        . 'tx_deal_ebayitemstatus,'
        . 'tx_deal_ebaylistingduration,'
        . 'tx_deal_ebaylocation,'
        . 'tx_deal_ebaymode,'
        . 'tx_deal_ebaypaymentmethods'
        . 'tx_deal_ebaypaymentmethodsdescription'
        . 'tx_deal_ebayquantity,'
        . 'tx_deal_ebaylog,'
        . 'tx_deal_ebayreturnsacceptoption,'
        . 'tx_deal_ebayreturnpolicydescription,'
        . 'tx_deal_ebayshippingserviceadditionalcosts,'
        . 'tx_deal_ebayshippingservicecode,'
        . 'tx_deal_ebayshippingservicecosts,'
;
$TCA[ 'tx_quickshop_products' ][ 'interface' ][ 'showRecordFieldList' ] = $showRecordFieldList;
// TCA interface
// TCA palettes
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebaymodeaction' ][ 'canNotCollapse' ] = 1;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebaymodeaction' ][ 'showitem' ] = ''
        . 'tx_deal_ebayaction;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayaction,'
        . 'tx_deal_ebaymode;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaymode'
;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebayitem' ][ 'canNotCollapse' ] = 1;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebayitem' ][ 'showitem' ] = ''
        . 'tx_deal_ebayitemid;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayitemid,'
        . 'tx_deal_ebayconditionid;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayconditionid,'
        . 'tx_deal_ebayquantity;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayquantity, --linebreak--,'
        . 'tx_deal_ebaycategoryid;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaycategoryid'
;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebaylengthoftime' ][ 'canNotCollapse' ] = 1;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebaylengthoftime' ][ 'showitem' ] = ''
        . 'tx_deal_ebaydispatchtimemax;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaydispatchtimemax,,'
        . 'tx_deal_ebaylistingduration;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylistingduration,'
;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebaypaymentmethods' ][ 'canNotCollapse' ] = 1;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebaypaymentmethods' ][ 'showitem' ] = ''
        . 'tx_deal_ebaypaymentmethods;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaypaymentmethods,'
        . 'tx_deal_ebaypaymentmethodsdescription;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaypaymentmethodsdescription'
;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebayreturnpolicy' ][ 'canNotCollapse' ] = 1;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebayreturnpolicy' ][ 'showitem' ] = ''
        . 'tx_deal_ebayreturnsacceptoption;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayreturnsacceptoption,'
        . 'tx_deal_ebayreturnpolicydescription;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayreturnpolicydescription'
;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebayshipping' ][ 'canNotCollapse' ] = 1;
$TCA[ 'tx_quickshop_products' ][ 'palettes' ][ 'tx_deal_ebayshipping' ][ 'showitem' ] = ''
        . 'tx_deal_ebayshippingservicecode;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayshippingservicecode, --linebreak--,'
        . 'tx_deal_ebayshippingservicecosts;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayshippingservicecosts,'
        . 'tx_deal_ebayshippingserviceadditionalcosts;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebayshippingserviceadditionalcosts,'
        . 'tx_deal_ebaylocation;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_deal_ebaylocation'
;
// TCA palettes
// TCA types
$str_showitem = $TCA[ 'tx_quickshop_products' ][ 'types' ][ '0' ][ 'showitem' ];
$arr_showitem = explode( '--div--;', $str_showitem );
$arr_new_showitem = array();
foreach ( $arr_showitem as $key => $value )
{
  switch ( true )
  {
    case($key < $int_div_position):
      $arr_new_showitem[ $key ] = $value;
      break;
    case($key == $int_div_position):
      $arr_new_showitem[ $key ] = ''
              . 'LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:tx_quickshop_products_div_tx_deal_ebay, '
              . 'tx_deal_ebayitemstatus,'
              . '--palette--;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:palette_tx_deal_ebaymodeaction;tx_deal_ebaymodeaction,'
              . '--palette--;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:palette_tx_deal_ebayitem;tx_deal_ebayitem,'
              . '--palette--;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:palette_tx_deal_ebayshipping;tx_deal_ebayshipping,'
              . '--palette--;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:palette_tx_deal_ebaylengthoftime;tx_deal_ebaylengthoftime,'
              . '--palette--;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:palette_tx_deal_ebaypaymentmethods;tx_deal_ebaypaymentmethods,'
              . '--palette--;LLL:EXT:deal/ext_tables/tx_quickshop_products/marketplaces/ebay/locallang_db.xml:palette_tx_deal_ebayreturnpolicy;tx_deal_ebayreturnpolicy,'
              . 'tx_deal_ebaylog,'
              . 'tx_deal_ebayexternalLinks,'
      ;
      $arr_new_showitem[ $key + 1 ] = $value;
      break;
    case($key > $int_div_position):
    default:
      $arr_new_showitem[ $key + 1 ] = $value;
      break;
  }
}
$str_showitem = implode( '--div--;', $arr_new_showitem );
$TCA[ 'tx_quickshop_products' ][ 'types' ][ '0' ][ 'showitem' ] = $str_showitem;

// #i0017, 141002, dwildt, 4+;
$TCA[ 'tx_quickshop_products' ][ 'types' ][ '0' ][ 'subtype_value_field' ] = 'tx_deal_ebaymode';
$TCA[ 'tx_quickshop_products' ][ 'types' ][ '0' ][ 'subtypes_excludelist' ] = array(
  'off' => ''
        . 'tx_deal_ebayaction,'
        . 'tx_deal_ebaycategoryid,'
        . 'tx_deal_ebayconditionid,'
        . 'tx_deal_ebaydispatchtimemax,'
        . 'tx_deal_ebayexternalLinks,'
        . 'tx_deal_ebayitemid,'
        . 'tx_deal_ebayitemstatus,'
        . 'tx_deal_ebaylistingduration,'
        . 'tx_deal_ebaylocation,'
        // . 'tx_deal_ebaymode,'
        . 'tx_deal_ebaypaymentmethods'
        . 'tx_deal_ebaypaymentmethodsdescription'
        . 'tx_deal_ebayquantity,'
        . 'tx_deal_ebaylog,'
        . 'tx_deal_ebayreturnsacceptoption,'
        . 'tx_deal_ebayreturnpolicydescription,'
        . 'tx_deal_ebayshippingserviceadditionalcosts,'
        . 'tx_deal_ebayshippingservicecode,'
        . 'tx_deal_ebayshippingservicecosts,'
;
);

unset( $int_div_position );
// TCA types

  // TCA for tx_quickshop_products