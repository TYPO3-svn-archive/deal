<?php

if( ! defined( 'TYPO3_MODE' ) )
{
  die( 'Access denied.' );
}



  ////////////////////////////////////////////////////////////////////////////
  //
  // TCA for tx_quickshop_products
  //
  // constants
  // TCA load
  // TCA ctrl
  // TCA columns
  // TCA interface
  // TCA palettes
  // TCA types


  ////////////////////////////////////////////////////////////////////////////
  //
  // TCA for tx_quickshop_products

  // constants
$int_div_position = 6;    // ..., 5. div[Images], 6. div[deal], 7. div[controlling], ...

  // TCA load
t3lib_div::loadTCA( 'tx_quickshop_products' );

  // TCA ctrl
$TCA['tx_quickshop_products']['ctrl']['tx_deal'] = array (
  'marketplaces' => array(
      'amazon'  => array(
          'enabled' => $confArr['amazonEnabled']
      ),
      'ebay'    => array(
          'enabled' => $confArr['ebayEnabled']
      )
  ),
  'manufacturer'    => array(
      'company' => $confArr['manufacturerCompany']
  )
);
  // TCA ctrl

  // TCA columns
$TCA['tx_quickshop_products']['columns']['tx_deal_layout'] = array (
  'exclude' => 0,
  'label'   => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_layout',
  'config'  => array (
    'type' => 'select',
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_layout_item_00',
        'layout_00',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_layout_item_01',
        'layout_01',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_layout_item_02',
        'layout_02',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_layout_item_03',
        'layout_03',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_layout_item_ts',
        'ts',
      ),
    ),
    'default' => 'ts',
  ),
);
$TCA['tx_quickshop_products']['columns']['tx_deal_quality'] = array (
  'exclude' => 0,
  'label'   => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_quality',
  'config'  => array (
    'type' => 'select',
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_quality_item_high',
        'high',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_quality_item_low',
        'low',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_quality_item_ts',
        'ts',
      ),
    ),
    'default' => 'ts',
  ),
);
$TCA['tx_quickshop_products']['columns']['tx_deal_pagelist'] = array (
  'exclude' => 0,
  'label'   => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_pagelist',
  'config'  => array (
    'type'      => 'input',
    'size'      => '40',
    'max'       => '256',
    'checkbox'  => '',
    'eval'      => 'trim',
  ),
);
$TCA['tx_quickshop_products']['columns']['tx_deal_updateswfxml'] = array (
  'exclude' => 0,
  'label'   => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_updateswfxml',
  'config'  => array (
    'type' => 'select',
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_updateswfxml_item_disabled',
        'disabled',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_updateswfxml_item_enabled',
        'enabled',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_updateswfxml_item_ts',
        'ts',
      ),
    ),
    'default' => 'ts',
  ),
);
$TCA['tx_quickshop_products']['columns']['tx_deal_swf_files'] = array (
  'exclude' => 0,
  'label'   => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_swf_files',
  'config' => array(
    'type'          => 'group',
    'internal_type' => 'file',
    'allowed'       => 'swf',
    'max_size'      => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
    'uploadfolder'  => 'uploads/tx_deal',
    'show_thumbs'   => '1',
    'size'          => '10',
    'maxitems'      => '999',
    'minitems'      => '0',
  ),
);
$TCA['tx_quickshop_products']['columns']['tx_deal_xml_file'] = array (
  'exclude' => 0,
  'label'   => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_xml_file',
  'config' => array(
    'type'          => 'group',
    'internal_type' => 'file',
    'allowed'       => 'xml',
    'max_size'      => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
    'uploadfolder'  => 'uploads/tx_deal',
    'show_thumbs'   => '1',
    'size'          => '1',
    'maxitems'      => '1',
    'minitems'      => '0',
  ),
);
$TCA['tx_quickshop_products']['columns']['tx_deal_fancybox'] = array (
  'exclude' => 0,
  'label'   => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_fancybox',
  'config'  => array (
    'type' => 'select',
    'items' => array(
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_fancybox_item_disabled',
        'disabled',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_fancybox_item_enabled',
        'enabled',
      ),
      array(
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_fancybox_item_ts',
        'ts',
      ),
    ),
    'default' => 'ts',
  ),
);
//$TCA['tx_quickshop_products']['columns']['tx_deal_evaluate'] = array (
//  'exclude' => 0,
//  'label'   => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_evaluate',
//  'config'  => array (
//    'type'      => 'user',
//    'userFunc'  => 'tx_deal_flexform->evaluate',
//  ),
//);
$TCA['tx_quickshop_products']['columns']['tx_deal_externalLinks'] = array (
  'exclude' => 0,
  'label'   => 'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_externalLinks',
  'config'  => array (
    'type'      => 'user',
    'userFunc'  => 'tx_deal_userfunc->promptExternalLinks',
  ),
);
  // TCA columns

  // TCA interface
$showRecordFieldList = $TCA['tx_quickshop_products']['interface']['showRecordFieldList'];
$showRecordFieldList = $showRecordFieldList
        . ',tx_deal_layout'
        . ',tx_deal_quality'
        . ',tx_deal_pagelist'
        . ',tx_deal_updateswfxml'
        . ',tx_deal_swf_files'
        . ',tx_deal_xml_file'
        . ',tx_deal_fancybox'
        . ',tx_deal_evaluate'
        . ',tx_deal_externalLinks'
        ;
$TCA['tx_quickshop_products']['interface']['showRecordFieldList'] = $showRecordFieldList;
  // TCA interface

  // TCA palettes
$TCA['tx_quickshop_products']['palettes']['tx_deal_fancybox']['showitem'] =
  'tx_deal_fancybox;LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_fancybox';
$TCA['tx_quickshop_products']['palettes']['tx_deal_fancybox']['canNotCollapse'] = 1;

$TCA['tx_quickshop_products']['palettes']['tx_deal_files']['showitem'] =
  'tx_deal_updateswfxml;LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_updateswfxml, --linebreak--,' .
  'tx_deal_xml_file;LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_xml_file, --linebreak--,' .
  'tx_deal_swf_files;LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_swf_files';
$TCA['tx_quickshop_products']['palettes']['tx_deal_files']['canNotCollapse'] = 1;

$TCA['tx_quickshop_products']['palettes']['tx_deal_quality']['showitem'] =
  'tx_deal_quality;LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_quality,' .
  'tx_deal_pagelist;LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_deal_pagelist';
$TCA['tx_quickshop_products']['palettes']['tx_deal_quality']['canNotCollapse'] = 1;
  // TCA palettes

  // TCA types
$str_showitem = $TCA['tx_quickshop_products']['types']['0']['showitem'];
$arr_showitem = explode( '--div--;', $str_showitem );
$arr_new_showitem = array( );
foreach( $arr_showitem as $key => $value )
{
  switch( true )
  {
    case($key < $int_div_position):
      $arr_new_showitem[$key] = $value;
      break;
    case($key == $int_div_position):
      $arr_new_showitem[$key] = '' .
        'LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:tcaLabel_tx_quickshop_products_div_tx_deal, ' .
          'tx_deal_layout,' .
          '--palette--;LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:palette_tx_deal_quality;tx_deal_quality,' .
          '--palette--;LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:palette_tx_deal_files;tx_deal_files,' .
          '--palette--;LLL:EXT:deal/ext_tables/tx_quickshop_products/locallang_db.xml:palette_tx_deal_fancybox;tx_deal_fancybox,' .
          'tx_deal_evaluate,' .
          'tx_deal_externalLinks,';
      $arr_new_showitem[$key + 1] = $value;
      break;
    case($key > $int_div_position):
    default:
      $arr_new_showitem[$key + 1] = $value;
      break;
  }
}
$str_showitem = implode( '--div--;', $arr_new_showitem );
$TCA['tx_quickshop_products']['types']['0']['showitem'] = $str_showitem;
unset( $int_div_position );
  // TCA types

  // TCA for tx_quickshop_products