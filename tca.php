<?php

//echo 'GET';
//var_export( $_GET, false);

if (!defined('TYPO3_MODE'))
  die('Access denied.');

// tx_deal_ebaycategories
$TCA['tx_deal_ebaycategories'] = array(
  'ctrl' => $TCA['tx_deal_ebaycategories']['ctrl'],
  'interface' => array(
    'showRecordFieldList' => 'hidden,title,uid_parent'
  ),
  'feInterface' => $TCA['tx_deal_ebaycategories']['feInterface'],
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
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_ebaycategories.title',
      'config' => array(
        'type' => 'input',
        'size' => '30',
        'eval' => 'required',
      )
    ),
    'uid_parent' => array(
      'exclude' => 0,
      'label' => 'LLL:EXT:deal/locallang_db.xml:tx_deal_ebaycategories.uid_parent',
      'config' => array(
        'type' => 'select',
        'size' => 1,
        'minitems' => 0,
        'maxitems' => 2,
        'trueMaxItems' => 1,
        'foreign_table' => 'tx_deal_ebaycategories',
        'foreign_table_where' => 'AND tx_deal_ebaycategories.pid=###CURRENT_PID### ORDER BY tx_deal_ebaycategories.title',
        'form_type' => 'user',
        'userFunc' => 'tx_cpstcatree->getTree',
        'treeView' => 1,
        'expandable' => 1,
        'expandFirst' => 0,
        'expandAll' => 0,
      ),
    ),
  ),
  'types' => array(
    '0' => array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, uid_parent')
  ),
  'palettes' => array(
    '1' => array('showitem' => ''),
  )
);
// tx_deal_ebaycategories

// tx_deal_ebayshippingservicecode
$TCA['tx_deal_ebayshippingservicecode'] = array(
  'ctrl' => $TCA['tx_deal_ebayshippingservicecode']['ctrl'],
  'interface' => array(
    'showRecordFieldList' => 'hidden,title,code'
  ),
  'feInterface' => $TCA['tx_deal_ebayshippingservicecode']['feInterface'],
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
    '0' => array('showitem' => 'hidden;;1;;1-1-1, title;;;;2-2-2, code')
  ),
  'palettes' => array(
    '1' => array('showitem' => ''),
  )
);
// tx_deal_ebayshippingservicecode
?>