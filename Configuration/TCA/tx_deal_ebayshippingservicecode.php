<?php

$dealTca = array(
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
    'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath( 'deal' ) . 'Resources/Public/Images/Marketplaces/Immo24/ext_icon.gif',
    'searchFields' => 'title,key',
    'rootLevel' => 1,
  ),
  'interface' => array(
    'showRecordFieldList' => 'hidden,title,code'
  ),
	'types' => array(
		'1' => array('showitem' => 'hidden,title,code'),
	),
	'palettes' => array(
		'1' => array(),
	),
  'columns' => array(
    'hidden' => array(
      'exclude' => 1,
      'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config' => array(
        'type' => 'check',
        'default' => '0'
      )
    ),
    'title' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_ebayshippingservicecode.title',
      'config' => array(
        'type' => 'input',
        'size' => '60',
        'eval' => 'required',
      )
    ),
    'code' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_ebayshippingservicecode.code',
      'config' => array(
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      ),
    ),
  ),
  'types' => array(
    '0' => array( 'showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, code' )
  ),
  'palettes' => array(
    '1' => array( 'showitem' => '' ),
  )
);

return $dealTca;