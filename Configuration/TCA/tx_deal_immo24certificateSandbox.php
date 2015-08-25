<?php

$dealTca = array(
  'ctrl' => array(
    'title' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_immo24certificateSandbox',
    'label' => 'ic_username',
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'cruser_id' => 'cruser_id',
    'default_sortby' => 'ORDER BY ic_desc',
    'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath( 'deal' ) . 'Resources/Public/Images/Marketplaces/Immo24/ext_icon.gif',
    'searchFields' => 'ic_id,ic_desc,ic_key,ic_secret,ic_expire,ic_username',
    'rootLevel' => 1,
  ),
  'interface' => array(
    'showRecordFieldList' => 'ic_id,ic_desc,ic_expire,ic_key,ic_secret,ic_username'
  ),
  'columns' => array(
    'ic_id' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_immo24certificate.ic_id',
      'config' => array(
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'ic_desc' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_immo24certificate.ic_desc',
      'config' => array(
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'ic_expire' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_immo24certificate.ic_expire',
      'config' => array(
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
        'default' => '0000-00-00 00:00:00',
      )
    ),
    'ic_key' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_immo24certificate.ic_key',
      'config' => array(
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'ic_secret' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_immo24certificate.ic_secret',
      'config' => array(
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'ic_username' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_immo24certificate.ic_username',
      'config' => array(
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
  ),
  'types' => array(
    '0' => array( 'showitem' => 'ic_id,ic_desc,ic_expire,ic_key,ic_secret,ic_username' )
  ),
  'palettes' => array(
    '0' => array( 'showitem' => '' ),
  )
);

return $dealTca;